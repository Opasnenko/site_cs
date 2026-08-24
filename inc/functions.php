<?php
require_once __DIR__ . '/language.php';
require_once __DIR__ . '/../ajax/addons/geo/geo_helper.php';
function db_connect($db_host, $db_db, $db_user, $db_pass) {
	try {
		$pdo = new PDO(
			"mysql:host=" . $db_host . ";dbname=" . $db_db,
			$db_user,
			htmlspecialchars_decode($db_pass, ENT_QUOTES)
		);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e) {
		global $conf;

		if(isset($conf->secret)) {
			$file = get_log_file_name("pdo_errors");

			file_put_contents(
				$_SERVER["DOCUMENT_ROOT"] . "/logs/" . $file,
				"[" . date("Y-m-d H:i:s") . " | " . $db_host . " | " . $db_db . "] : [" . $e->getMessage() . "]\r\n",
				FILE_APPEND
			);
		}

		return false;
	}

	return $pdo;
}

function db_get_info($pdo = null, $what = null, $from = null, $where = null, $limit = null) {
	if(empty($pdo) || empty($what) || empty($from)) {
		return false;
	}

	$what = explode(' ', $what);
	$what_str = '';
	for($i = 0; $i < count($what); $i++) {
		$what_str .= '`'.clean($what[$i], null).'`,';
	}
	$what_str = substr($what_str, 0, -1);

	$from_str = clean($from, null);

	$where_str = '';
	if(preg_match("/([a-zA-Z0-9\"]{1,50}) ?(!=|=|<|>) ?([a-zA-Z0-9\"]{1,50})/", $where)) {
		$where_str = 'WHERE '.$where;
	}

	$limit_str = '';
	$limit = clean($limit, "int");
	if(!empty($limit)) {
		$limit_str = 'LIMIT '.$limit;
	}

	$STH = $pdo->query("SELECT $what_str FROM `$from_str` $where_str $limit_str");
	$STH->execute();
	$row = $STH->fetchAll();

	return $row;
}

function set_prefix($prefix, $table) {
	if(!empty($prefix)) {
		$table = $prefix.'_'.$table;
	}
	return $table;
}

function get_ai($pdo, $table, $column = 'id') {
	$STH = $pdo->query("SELECT $column FROM $table ORDER BY $column DESC LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	return $row->$column + 1;
}

function set_names($pdo, $code = 0) {
	switch($code):
		case "1":
			$pdo->exec("set names utf8");
		break;
		case "2":
			$pdo->exec("set names latin1");
		break;
		case "3":
			$pdo->exec("set names utf8mb4");
		break;
	endswitch;
	
	return true;
}

function check_table($table, $pdo) {
	$table = check($table, null);

	$STH = $pdo->prepare("SHOW TABLES LIKE :table");
	$STH->execute(array(":table" => "%".$table."%"));
	$row = $STH->fetchAll();
	$count = count($row);
	for($i = 0; $i < $count; $i++) {
		if(isset($row[0][$i]) && $table == $row[0][$i]) {
			return true;
		}
	}
	return false;
}

function check_column($table, $pdo, $column) {
	$table = check($table, null);

	$STH = $pdo->query("SHOW COLUMNS FROM $table");
	$STH->execute();
	$row = $STH->fetchAll();
	for($i = 0; $i < count($row); $i++) {
		if(isset($row[$i]['Field']) && ($row[$i]['Field'] == $column)) {
			return true;
		}
	}
	return false;
}

function get_rows_count($pdo, $table, $where) {
	$STH = $pdo->query("SELECT COUNT(*) as count FROM `$table` WHERE ".$where);
	$STH->setFetchMode(PDO::FETCH_ASSOC);
	$row = $STH->fetch();
	if(empty($row['count'])) {
		$row['count'] = 0;
	}
	return $row['count'];
}

function service_log($log, $admin, $server, $pdo, $service = 0) {
	$file = get_log_file_name("services_log");

	if(file_exists($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $file)) {
		$i = "a";
	} else {
		$i = "w";
	}

	if(isset($_SESSION['id']) and isset($_SESSION['login'])) {
		$user = $_SESSION['login'] . ' - ' . $_SESSION['id'];
	} else {
		$user = 'Админ Центр';
	}

	$STH = $pdo->prepare("SELECT name FROM servers WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $server]);
	$server = $STH->fetch();

	$STH = $pdo->prepare("SELECT name FROM admins WHERE id=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':id' => $admin]);
	$admin = $STH->fetch();

	if($service === 0) {
		$service = 'Все';
	} else {
		$STH = $pdo->prepare("SELECT name FROM services WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $service]);
		$service = $STH->fetch()->name;
	}

	$context = "Пользователь: " . $user . " | Сервер: " . $server->name . " | Идентификатор: " . $admin->name . " | Услуга: " . $service;

	$error_file = fopen($_SERVER['DOCUMENT_ROOT'] . "/logs/" . $file, $i);
	fwrite(
		$error_file,
		"[" . date("Y-m-d H:i:s")
		. " | " . $context
		. "] : [" . $log . "] \r\n"
	);
	fclose($error_file);

	if(function_exists('pb_slog_write')) {
		pb_slog_write('info', 'service', $log, '', 0, $context);
	}
}

function log_error($error) {
	$file = get_log_file_name("error_log");

	if(isset($_SESSION['login']) and isset($_SESSION['id'])) {
		$user = $_SESSION['login']." - ".$_SESSION['id'];
	} else {
		$user = "Гость";
	}

	if(file_exists($_SERVER['DOCUMENT_ROOT']."/logs/".$file)) {
		$i = "a";
	} else {
		$i = "w";
	}

	$error_file = fopen($_SERVER['DOCUMENT_ROOT']."/logs/".$file, $i);
	fwrite($error_file, "[".date("Y-m-d H:i:s")." | ".$_SERVER["REMOTE_ADDR"]." | ".$user."] : [".$error."] \r\n");
	fclose($error_file);

	if(function_exists('pb_slog_write')) {
		pb_slog_write('error', 'app', $error);
	}
}

function write_log($log) {
	$file = get_log_file_name("log");

	if(isset($_SESSION['login']) and isset($_SESSION['id'])) {
		$user = $_SESSION['login']." - ".$_SESSION['id'];
	} else {
		$user = "Гость";
	}

	if(file_exists($_SERVER['DOCUMENT_ROOT']."/logs/".$file)) {
		$i = "a";
	} else {
		$i = "w";
	}

	$log_file = fopen($_SERVER['DOCUMENT_ROOT']."/logs/".$file, $i);
	fwrite($log_file, "[".date("Y-m-d H:i:s")." | ".$_SERVER["REMOTE_ADDR"]." | ".$user."] : [".$log."] \r\n");
	fclose($log_file);

	if(function_exists('pb_slog_write')) {
		pb_slog_write('info', 'app', $log);
	}
}

function ValidateInt($variable) {
	if(is_numeric($variable) && $variable > 0 && (int)($variable) == $variable) {
		return true;
	} else {
		return false;
	}
}

function ValidateNameForUrl($variable) {
	if(preg_match("/[\||\'|\<|\>|\[|\]|\"|\!|\?|\$|\@|\#|\%|\^|\/|\\\|\&|\~|\*|\{|\}|\+|\:|\.|\,|\;|\`|\=|\(|\)|\§|\°]/", $variable)) {
		return true;
	} else {
		return false;
	}
}

function ValidateLetterAndNum($variable) {
	if(preg_match("/^[a-z\d]{1}[a-z\d\s]*[a-z\d]{1}$/i", $variable)) {
		return true;
	} else {
		return false;
	}
}

	function clean($text = null, $params = null) {
		if(empty($text)) {
			return $text;
		}

		$text = stripslashes($text);
		$text = htmlspecialchars($text, ENT_QUOTES);
		$text = trim($text);

		switch($params) {
			case "int":
				$text = preg_replace("/[^0-9]+/", "", $text);
			break;
			case "float":
				$text = str_replace(",", ".", $text);
				$text = preg_replace("/[^0-9.]/", "", $text);
				$text = (float)$text;
				$text = round($text, 2);
			break;
		}

		return $text;
	}

function check($variable, $param) {
	if(isset($variable)) {
		$variable = clean($variable, $param);
		if($variable == '') {
			unset($variable);
		}
	}
	if(isset($variable)) {
		return $variable;
	} else {
		return null;
	}
}

function checkJs($variable, $param = null) {
	if(isset($variable)) {
		$variable = clean($variable, $param);
		if(isset($variable) and $variable == '') {
			unset($variable);
		}
		if(isset($variable) and $variable == 'undefined') {
			unset($variable);
		}
	}
	if(isset($variable)) {
		return $variable;
	} else {
		return null;
	}
}

function checkStart($variable) {
	if(isset($variable)) {
		$variable = clean($variable, "int");
		if($variable == '' and $variable != 0) {
			unset($variable);
		}
		if($variable == "undefined") {
			$variable = 0;
		}
		return $variable;
	} else {
		return null;
	}
}

function clean_name($name) {
	$name = trim(str_replace('#', "", $name));
	$name = trim(str_replace('/', "", $name));
	$name = trim(str_replace(':', "", $name));
	if(mb_strlen($name, 'UTF-8') > 230) {
		$name = substr($name, 230);
	}
	return $name;
}

function get_month($i, $type = 1) {
	if($type == 1) {
		$months = array(1  => 'января',
		                2  => 'февраля',
		                3  => 'марта',
		                4  => 'апреля',
		                5  => 'мая',
		                6  => 'июня',
		                7  => 'июля',
		                8  => 'августа',
		                9  => 'сентября',
		                10 => 'октября',
		                11 => 'ноября',
		                12 => 'декабря');
	} elseif($type == 2) {
		$months = array(1  => 'январь',
		                2  => 'февраль',
		                3  => 'март',
		                4  => 'апрель',
		                5  => 'май',
		                6  => 'июнь',
		                7  => 'июль',
		                8  => 'август',
		                9  => 'сентябрь',
		                10 => 'октябрь',
		                11 => 'ноябрь',
		                12 => 'декабрь');
	} else {
		$months = array(1 => '01', 2 => '02', 3 => '03', 4 => '04', 5 => '05', 6 => '06', 7 => '07', 8 => '08', 9 => '09', 10 => '10', 11 => '11', 12 => '12');
	}
	return $months[$i];
}

function expand_date($date, $type = 1) {
	if(clean($date, "int") == $date) {
		$time = $date;
	} else {
		$time = strtotime($date);
	}

	$month = get_month(date('n', $time), 1);
	$day = date('j', $time);
	$year = date('Y', $time);
	$hour = date('H', $time);
	$min = date('i', $time);

	if($type == 0) {
		return "$hour:$min";
	}
	if($type == 1) {
		return "$day $month $year г, $hour:$min";
	}
	if($type == 2) {
		return "$day $month $year г";
	}
	if($type == 3) {
		return "$day $month $year";
	}
	if($type == 4) {
		if($day < 10) {
			$day = "0".$day;
		}
		$month = get_month(date('n', $time), 3);
		$year = substr($year, 2);
		return "$day.$month.$year";
	}
	if($type == 5) {
		$dtnew['day'] = date('j', $time);
		$dtnew['year'] = date('Y', $time);
		$dtnew['hour'] = date('G', $time);
		$dtnew['min'] = date('i', $time);
		$dtnew['month'] = get_month(date('n', $time), 1);
		$dtnew['month2'] = get_month(date('n', $time), 2);
		$dtnew['month3'] = get_month(date('n', $time), 3);
		return $dtnew;
	}
	if($type == 6) {
		return "$day $month в $hour:$min";
	}

	$yesterday = strtotime('yesterday');

	if($type == 7) {
		$dif = time() - $time;
		if($dif < 59) {
			if($dif < 15) {
				return "Только что";
			} else {
				return $dif . " сек. назад";
			}
		} elseif($dif / 60 > 1 and $dif / 60 < 59) {
			return round($dif / 60) . " мин. назад";
		} elseif($dif / 3600 > 1 and $dif / 3600 < 23) {
			return round($dif / 3600) . " час. назад";
		} elseif($time > $yesterday && $time < ($yesterday + 24 * 3600)) {
			return "Вчера в $hour:$min";
		} elseif($time > ($yesterday - 24 * 3600) && $time < $yesterday) {
			return "Позавчера в $hour:$min";
		} else {
			return "$day $month $year г, $hour:$min";
		}
	}
	if($type == 8) {
		$dtnew['short'] = "$hour:$min";
		$dtnew['full'] = "$day $month $year г";
		return $dtnew;
	}

	return $date;
}

function expand_seconds($time) {
	if($time < 59) {
		$time = $time."сек.";
	} elseif($time / 60 > 1 and $time / 60 < 59) {
		$time = round($time / 60)." мин.";
	} elseif($time / 3600 > 1 and $time / 3600 < 23) {
		$time = round($time / 3600)." час.";
	} elseif($time / 86400 > 1 and $time / 86400 < 7) {
		$time = round($time / 86400)." сут.";
	} elseif($time / 86400 / 7 > 1 and $time < 60 * 60 * 24 * 365) {
		$time = round($time / 86400 / 7)." нед.";
	} elseif($time > 60 * 60 * 24 * 365) {
		$time = round($time / 60 / 60 / 24 / 365)." лет.";
	}

	return $time;
}

function expand_seconds2($seconds, $type = null) {
	if($seconds == 0) {
		return "Навсегда";
	}
	$days = (int)($seconds / (24 * 3600));
	$seconds -= $days * 24 * 3600;
	$hours = (int)($seconds / 3600);
	$seconds -= $hours * 3600;
	$minutes = (int)($seconds / 60);
	$seconds -= $minutes * 60;

	if($days != 0) {
		$days = $days." суток ";
	} else {
		$days = '';
	}
	if($hours != 0) {
		$hours = $hours." час. ";
	} else {
		$hours = '';
	}
	if($minutes != 0) {
		$minutes = $minutes." мин. ";
	} else {
		$minutes = '';
	}
	if($seconds != 0) {
		$seconds = $seconds." сек.";
	} else {
		$seconds = '';
	}
	if($type == 2) {
		return "{$days}{$hours}{$minutes}";
	} else {
		return "{$days}{$hours}{$minutes}{$seconds}";
	}
}

function diff_date($date1, $date2 = null) {
	$diff = array();

	if(!$date2) {
		$cd = getdate();
		$date2 = $cd['year'].'-'.$cd['mon'].'-'.$cd['mday'].' '.$cd['hours'].':'.$cd['minutes'].':'.$cd['seconds'];
	}

	$pattern = '/(\d+)-(\d+)-(\d+)(\s+(\d+):(\d+):(\d+))?/';
	preg_match($pattern, $date1, $matches);
	$d1 = array((int)$matches[1], (int)$matches[2], (int)$matches[3], (int)$matches[5], (int)$matches[6], (int)$matches[7]);
	preg_match($pattern, $date2, $matches);
	$d2 = array((int)$matches[1], (int)$matches[2], (int)$matches[3], (int)$matches[5], (int)$matches[6], (int)$matches[7]);

	for($i = 0; $i < count($d2); $i++) {
		if($d2[$i] > $d1[$i])
			break;
		if($d2[$i] < $d1[$i]) {
			$t = $d1;
			$d1 = $d2;
			$d2 = $t;
			break;
		}
	}

	$md1 = array(31, $d1[0] % 4 || (!($d1[0] % 100) && $d1[0] % 400) ? 28 : 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	$md2 = array(31, $d2[0] % 4 || (!($d2[0] % 100) && $d2[0] % 400) ? 28 : 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	$min_v = array(null, 1, 1, 0, 0, 0);
	$max_v = array(null, 12, $d2[1] == 1 ? $md2[11] : $md2[$d2[1] - 2], 23, 59, 59);
	for($i = 5; $i >= 0; $i--) {
		if($d2[$i] < $min_v[$i]) {
			$d2[$i - 1]--;
			$d2[$i] = $max_v[$i];
		}
		$diff[$i] = $d2[$i] - $d1[$i];
		if($diff[$i] < 0) {
			$d2[$i - 1]--;
			$i == 2 ? $diff[$i] += $md1[$d1[1] - 1] : $diff[$i] += $max_v[$i] - $min_v[$i] + 1;
		}
	}

	return $diff;
}

function getPhrase($number, $titles) {
	$cases = array(2, 0, 1, 1, 1, 2);

	return $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
}

function translit($url) {
	$transsimvol = array("А" => "a",
	                     "Б" => "b",
	                     "В" => "v",
	                     "Г" => "g",
	                     "Д" => "d",
	                     "Е" => "e",
	                     "Ё" => "e",
	                     "Ж" => "zh",
	                     "З" => "z",
	                     "И" => "i",
	                     "Й" => "y",
	                     "К" => "k",
	                     "Л" => "l",
	                     "М" => "m",
	                     "Н" => "n",
	                     "О" => "o",
	                     "П" => "p",
	                     "Р" => "r",
	                     "С" => "s",
	                     "Т" => "t",
	                     "У" => "u",
	                     "Ф" => "f",
	                     "Х" => "h",
	                     "Ц" => "ts",
	                     "Ч" => "ch",
	                     "Ш" => "sh",
	                     "Щ" => "sch",
	                     "Ъ" => "",
	                     "Ы" => "y",
	                     "Ь" => "",
	                     "Э" => "e",
	                     "Ю" => "yu",
	                     "Я" => "ya",
	                     " " => "_",
	                     "а" => "a",
	                     "б" => "b",
	                     "в" => "v",
	                     "г" => "g",
	                     "д" => "d",
	                     "е" => "e",
	                     "ё" => "e",
	                     "ж" => "j",
	                     "з" => "z",
	                     "и" => "i",
	                     "й" => "y",
	                     "к" => "k",
	                     "л" => "l",
	                     "м" => "m",
	                     "н" => "n",
	                     "о" => "o",
	                     "п" => "p",
	                     "р" => "r",
	                     "с" => "s",
	                     "т" => "t",
	                     "у" => "u",
	                     "ф" => "f",
	                     "х" => "h",
	                     "ц" => "ts",
	                     "ч" => "ch",
	                     "ш" => "sh",
	                     "щ" => "sch",
	                     "ъ" => "",
	                     "ы" => "y",
	                     "ь" => "",
	                     "э" => "e",
	                     "ю" => "yu",
	                     "я" => "ya");
	return strtr($url, $transsimvol);
}

function get_file_name() {
	$filename = substr(basename($_SERVER['PHP_SELF']), 0, strrpos(basename($_SERVER['PHP_SELF']), '.'));
	return $filename;
}

function get_ip() {
	$serverVars = array("HTTP_X_FORWARDED_FOR",
	                    "HTTP_X_FORWARDED",
	                    "HTTP_FORWARDED_FOR",
	                    "HTTP_FORWARDED",
	                    "HTTP_VIA",
	                    "HTTP_X_COMING_FROM",
	                    "HTTP_COMING_FROM",
	                    "HTTP_CLIENT_IP",
	                    "HTTP_XROXY_CONNECTION",
	                    "HTTP_PROXY_CONNECTION",
	                    "HTTP_USERAGENT_VIA");
	foreach($serverVars as $serverVar) {
		if(!empty($_SERVER) && !empty($_SERVER[$serverVar])) {
			$proxyIP = $_SERVER[$serverVar];
		} elseif(!empty($_ENV) && isset($_ENV[$serverVar])) {
			$proxyIP = $_ENV[$serverVar];
		} elseif(@getenv($serverVar)) {
			$proxyIP = getenv($serverVar);
		}
	}
	if(!empty($proxyIP)) {
		$isIP = preg_match('|^([0-9]{1,3}\.){3,3}[0-9]{1,3}|', $proxyIP, $regs);
		if(isset($regs[0])) {
			$long = ip2long($regs[0]);
			if($isIP && (sizeof($regs) > 0) && $long != -1 && $long !== false) {
				if(filter_var($regs[0], FILTER_VALIDATE_IP)) {
					return clean($regs[0], null);
				}
			}
		}
	}
	if(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
		return clean($_SERVER['REMOTE_ADDR'], null);
	} else {
		return '127.0.0.1';
	}
}

function get_groups($pdo) {
	$STH = $pdo->query("SELECT * FROM users__groups");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		$users_groups[$row->id]['name'] = $row->name;
		$users_groups[$row->id]['color'] = $row->color;
		$users_groups[$row->id]['rights'] = $row->rights;
		$users_groups[$row->id]['id'] = $row->id;
		$users_groups[$row->id]['is_default'] = isset($row->is_default) ? (int) $row->is_default : 0;
	}
	$users_groups[0]['name'] = "Гость";
	$users_groups[0]['color'] = "#CCCCCC";
	$users_groups[0]['rights'] = "0";
	$users_groups[0]['id'] = "0";
	$users_groups[0]['is_default'] = 0;
	return $users_groups;
}

function users_groups() {
	global $users_groups;

	if(empty($users_groups)) {
		$users_groups = get_groups(pdo());
	}

	return $users_groups;
}

function pb_group_flags_map() {
	return [
		'h' => ['label' => 'Публичные модераторские права (не вход в /admin)', 'class' => 'admin'],
		'a' => ['label' => 'обычные права пользователя', 'class' => ''],
		'm' => ['label' => 'деньги и баланс пользователей', 'class' => ''],
		'c' => ['label' => 'персональная скидка', 'class' => ''],
		'd' => ['label' => 'чат и события', 'class' => ''],
		'y' => ['label' => 'стены пользователей', 'class' => ''],
		'b' => ['label' => 'создание новостей', 'class' => ''],
		'q' => ['label' => 'редактирование новостей', 'class' => ''],
		'f' => ['label' => 'редактирование пользователей', 'class' => ''],
		'n' => ['label' => 'выдача любых групп', 'class' => ''],
		'g' => ['label' => 'удаление пользователей', 'class' => ''],
		'p' => ['label' => 'рассмотрение тикетов', 'class' => ''],
		'l' => ['label' => 'удаление тикетов', 'class' => ''],
		't' => ['label' => 'форумы и разделы', 'class' => ''],
		'w' => ['label' => 'создание тем', 'class' => ''],
		'e' => ['label' => 'редактирование тем', 'class' => ''],
		'r' => ['label' => 'сообщения форума', 'class' => ''],
		'i' => ['label' => 'заявки на разбан', 'class' => ''],
		'o' => ['label' => 'удаление заявок на разбан', 'class' => ''],
		'k' => ['label' => 'рассмотрение жалоб', 'class' => ''],
		'u' => ['label' => 'удаление жалоб', 'class' => ''],
		's' => ['label' => 'rcon: действия над игроками', 'class' => ''],
		'v' => ['label' => 'rcon: управление сервером', 'class' => ''],
		'j' => ['label' => 'администраторы сервера', 'class' => ''],
		'z' => ['label' => 'временный бан', 'class' => 'danger'],
		'x' => ['label' => 'вечный бан', 'class' => 'danger'],
	];
}

function pb_group_rights_letters($rights) {
	$letters = [];
	foreach(str_split((string) $rights) as $ch) {
		if(ctype_alpha($ch)) {
			$lower = strtolower($ch);
			if(!in_array($lower, $letters, true)) {
				$letters[] = $lower;
			}
		}
	}
	return $letters;
}

function pb_groups_manager_ensure_schema($pdo) {
	static $done = false;
	if($done || empty($pdo)) {
		return;
	}
	$done = true;

	try {
		$pdo->exec("CREATE TABLE IF NOT EXISTS `users__allowed_groups` (
			`user_id` int NOT NULL,
			`group_id` int NOT NULL,
			`source` varchar(20) NOT NULL DEFAULT 'admin',
			`granted_at` datetime NOT NULL,
			PRIMARY KEY (`user_id`, `group_id`),
			KEY `group_id` (`group_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$columnExists = function($table, $column) use ($pdo) {
			$check = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE " . $pdo->quote($column));
			return $check && $check->fetch();
		};

		if(!$columnExists('users__allowed_groups', 'source')) {
			$pdo->exec("ALTER TABLE `users__allowed_groups` ADD COLUMN `source` varchar(20) NOT NULL DEFAULT 'admin'");
		}

		$hadIsDefault = $columnExists('users__groups', 'is_default');
		if(!$hadIsDefault) {
			$pdo->exec("ALTER TABLE `users__groups` ADD COLUMN `is_default` tinyint(1) NOT NULL DEFAULT '0'");
		}

		if(!$hadIsDefault) {
			$STH = $pdo->query("SELECT COUNT(*) FROM `users__groups` WHERE `is_default`=1");
			if((int) $STH->fetchColumn() === 0) {
				$STH = $pdo->query("SELECT `stand_rights` FROM `config__secondary` LIMIT 1");
				$standRights = (int) $STH->fetchColumn();
				if($standRights > 0) {
					$pdo->prepare("UPDATE `users__groups` SET `is_default`=1 WHERE `id`=:id")->execute([':id' => $standRights]);
				}
			}
		}
	} catch(Throwable $e) {
		if(function_exists('write_log')) {
			write_log('[Groups manager] ensure_schema: ' . $e->getMessage());
		}
	}
}

function pb_groups_manager_selectable_ids($pdo, $userId) {
	pb_groups_manager_ensure_schema($pdo);

	$ids = [];

	$STH = $pdo->query("SELECT `id` FROM `users__groups` WHERE `is_default`=1");
	$STH->setFetchMode(PDO::FETCH_COLUMN, 0);
	foreach($STH->fetchAll() as $id) {
		$ids[(int) $id] = true;
	}

	$userId = (int) $userId;
	if($userId > 0) {
		$STH = $pdo->prepare("SELECT `group_id` FROM `users__allowed_groups` WHERE `user_id`=:user_id");
		$STH->execute([':user_id' => $userId]);
		$STH->setFetchMode(PDO::FETCH_COLUMN, 0);
		foreach($STH->fetchAll() as $id) {
			$ids[(int) $id] = true;
		}

		$now = date('Y-m-d H:i:s');
		$STH = $pdo->prepare("SELECT `services`.`users_group` FROM `admins__services`
			LEFT JOIN `admins` ON `admins`.`id` = `admins__services`.`admin_id`
			LEFT JOIN `services` ON `services`.`id` = `admins__services`.`service`
			WHERE `admins`.`user_id`=:user_id AND `admins`.`active`='1'
				AND `services`.`users_group`!=0
				AND (`admins__services`.`ending_date`=:no_end OR `admins__services`.`ending_date`>=:now)");
		$STH->execute([':user_id' => $userId, ':no_end' => '0000-00-00 00:00:00', ':now' => $now]);
		$STH->setFetchMode(PDO::FETCH_COLUMN, 0);
		foreach($STH->fetchAll() as $id) {
			$ids[(int) $id] = true;
		}
	}

	return array_keys($ids);
}

function pb_groups_manager_user_allowed_ids($pdo, $userId) {
	pb_groups_manager_ensure_schema($pdo);

	$ids = [];
	$STH = $pdo->prepare("SELECT `group_id` FROM `users__allowed_groups` WHERE `user_id`=:user_id");
	$STH->execute([':user_id' => (int) $userId]);
	$STH->setFetchMode(PDO::FETCH_COLUMN, 0);
	foreach($STH->fetchAll() as $id) {
		$ids[] = (int) $id;
	}
	return $ids;
}

function pb_groups_manager_user_allowed_sources($pdo, $userId) {
	pb_groups_manager_ensure_schema($pdo);

	$sources = [];
	$STH = $pdo->prepare("SELECT `group_id`, `source` FROM `users__allowed_groups` WHERE `user_id`=:user_id");
	$STH->execute([':user_id' => (int) $userId]);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	foreach($STH->fetchAll() as $row) {
		$sources[(int) $row->group_id] = $row->source;
	}
	return $sources;
}

function pb_groups_manager_set_allowed_ids($pdo, $userId, array $groupIds) {
	pb_groups_manager_ensure_schema($pdo);

	$userId = (int) $userId;
	if($userId <= 0) {
		return false;
	}

	$groupIds = array_values(array_unique(array_map('intval', $groupIds)));

	$pdo->beginTransaction();
	try {
		$STH = $pdo->prepare("DELETE FROM `users__allowed_groups` WHERE `user_id`=:user_id AND `source`='admin'");
		$STH->execute([':user_id' => $userId]);

		if(!empty($groupIds)) {
			$STH = $pdo->prepare("INSERT INTO `users__allowed_groups` (`user_id`, `group_id`, `source`, `granted_at`) VALUES (:user_id, :group_id, 'admin', :granted_at)
				ON DUPLICATE KEY UPDATE `source`=IF(`source`='service', 'service', 'admin')");
			foreach($groupIds as $groupId) {
				if($groupId <= 0) {
					continue;
				}
				$STH->execute([':user_id' => $userId, ':group_id' => $groupId, ':granted_at' => date('Y-m-d H:i:s')]);
			}
		}

		$pdo->commit();
		return true;
	} catch(Throwable $e) {
		$pdo->rollBack();
		if(function_exists('write_log')) {
			write_log('[Groups manager] set_allowed_ids: ' . $e->getMessage());
		}
		return false;
	}
}

function pb_groups_manager_grant_from_service($pdo, $userId, $groupId) {
	pb_groups_manager_ensure_schema($pdo);

	$userId = (int) $userId;
	$groupId = (int) $groupId;
	if($userId <= 0 || $groupId <= 0) {
		return false;
	}

	try {
		$STH = $pdo->prepare("INSERT INTO `users__allowed_groups` (`user_id`, `group_id`, `source`, `granted_at`) VALUES (:user_id, :group_id, 'service', :granted_at)
			ON DUPLICATE KEY UPDATE `granted_at`=:granted_at");
		$STH->execute([':user_id' => $userId, ':group_id' => $groupId, ':granted_at' => date('Y-m-d H:i:s')]);
		return true;
	} catch(Throwable $e) {
		if(function_exists('write_log')) {
			write_log('[Groups manager] grant_from_service: ' . $e->getMessage());
		}
		return false;
	}
}

function pb_groups_manager_revoke_service_group($pdo, $userId, $groupId) {
	pb_groups_manager_ensure_schema($pdo);

	$userId = (int) $userId;
	$groupId = (int) $groupId;
	if($userId <= 0 || $groupId <= 0) {
		return false;
	}

	try {
		$STH = $pdo->prepare("DELETE FROM `users__allowed_groups` WHERE `user_id`=:user_id AND `group_id`=:group_id AND `source`='service'");
		$STH->execute([':user_id' => $userId, ':group_id' => $groupId]);
		return true;
	} catch(Throwable $e) {
		if(function_exists('write_log')) {
			write_log('[Groups manager] revoke_service_group: ' . $e->getMessage());
		}
		return false;
	}
}

function pb_theme_ensure_schema($pdo) {
	static $done = false;
	if($done || empty($pdo)) {
		return;
	}
	$done = true;

	try {
		$columnExists = function($table, $column) use ($pdo) {
			$check = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE " . $pdo->quote($column));
			return $check && $check->fetch();
		};

		if(!$columnExists('config__secondary', 'theme_accent_from')) {
			$pdo->exec("ALTER TABLE `config__secondary` ADD COLUMN `theme_accent_from` varchar(7) NOT NULL DEFAULT '#4fea9f'");
		}
		if(!$columnExists('config__secondary', 'theme_accent_to')) {
			$pdo->exec("ALTER TABLE `config__secondary` ADD COLUMN `theme_accent_to` varchar(7) NOT NULL DEFAULT '#38644f'");
		}
		if(!$columnExists('config__secondary', 'aria_widget_visible')) {
			$pdo->exec("ALTER TABLE `config__secondary` ADD COLUMN `aria_widget_visible` tinyint(1) NOT NULL DEFAULT '1'");
		}
		if(!$columnExists('config__secondary', 'admin_theme')) {
			$pdo->exec("ALTER TABLE `config__secondary` ADD COLUMN `admin_theme` varchar(10) NOT NULL DEFAULT 'light'");
		}
		if(!$columnExists('config__secondary', 'page_default_image')) {
			$pdo->exec("ALTER TABLE `config__secondary` ADD COLUMN `page_default_image` varchar(255) NOT NULL DEFAULT 'files/miniatures/pbgame_ui.jpg'");
		}
		if(!$columnExists('config__secondary', 'admin_warns_limit')) {
			$pdo->exec("ALTER TABLE `config__secondary` ADD COLUMN `admin_warns_limit` int NOT NULL DEFAULT '3'");
		}
		if(!$columnExists('config__secondary', 'admin_warns_buyout')) {
			$pdo->exec("ALTER TABLE `config__secondary` ADD COLUMN `admin_warns_buyout` tinyint(1) NOT NULL DEFAULT '0'");
		}
		if(!$columnExists('config__secondary', 'admin_warns_price')) {
			$pdo->exec("ALTER TABLE `config__secondary` ADD COLUMN `admin_warns_price` float NOT NULL DEFAULT '0'");
		}
		if(!$columnExists('config__secondary', 'admin_unblock_price')) {
			$pdo->exec("ALTER TABLE `config__secondary` ADD COLUMN `admin_unblock_price` float NOT NULL DEFAULT '100'");
		}

		$pdo->exec("CREATE TABLE IF NOT EXISTS `admins__warns` (
			`id` int NOT NULL AUTO_INCREMENT,
			`admin_id` int NOT NULL DEFAULT '0',
			`user_id` int NOT NULL DEFAULT '0',
			`server` int NOT NULL DEFAULT '0',
			`reason` varchar(500) NOT NULL DEFAULT '',
			`issued_by` varchar(120) NOT NULL DEFAULT '',
			`issued_uid` int NOT NULL DEFAULT '0',
			`active` tinyint(1) NOT NULL DEFAULT '1',
			`removed_by` varchar(120) NOT NULL DEFAULT '',
			`removed_reason` varchar(500) NOT NULL DEFAULT '',
			`removed_at` int NOT NULL DEFAULT '0',
			`created_at` int NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `admin_id` (`admin_id`),
			KEY `user_id` (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$pdo->exec("CREATE TABLE IF NOT EXISTS `admin__profiles` (
			`admin_uid` int NOT NULL,
			`avatar` varchar(255) NOT NULL DEFAULT '',
			`updated_at` int NOT NULL DEFAULT '0',
			PRIMARY KEY (`admin_uid`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		$pdo->exec("CREATE TABLE IF NOT EXISTS `admin__logins` (
			`id` int NOT NULL AUTO_INCREMENT,
			`admin_uid` int NOT NULL DEFAULT '0',
			`login` varchar(120) NOT NULL DEFAULT '',
			`ip` varchar(45) NOT NULL DEFAULT '',
			`country` varchar(80) NOT NULL DEFAULT '',
			`country_iso` varchar(2) NOT NULL DEFAULT '',
			`city` varchar(80) NOT NULL DEFAULT '',
			`session_id` varchar(64) NOT NULL DEFAULT '',
			`user_agent` varchar(255) NOT NULL DEFAULT '',
			`created_at` int NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `created_at` (`created_at`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		if(!$columnExists('admin__logins', 'country_iso')) {
			$pdo->exec("ALTER TABLE `admin__logins` ADD COLUMN `country_iso` varchar(2) NOT NULL DEFAULT '' AFTER `country`");
		}
	} catch(Throwable $e) {
		if(function_exists('write_log')) {
			write_log('[Theme] ensure_schema: ' . $e->getMessage());
		}
	}
}

function pb_admin_login_log_add($pdo, $adminUid, $login) {
	if(empty($pdo) || !($pdo instanceof PDO)) {
		return false;
	}
	pb_theme_ensure_schema($pdo);

	$ip = function_exists('get_ip') ? (string) get_ip() : '';
	$country = '';
	$countryIso = '';
	$city = '';

	$geoHelper = $_SERVER['DOCUMENT_ROOT'] . '/ajax/addons/geo/geo_helper.php';
	if(!function_exists('pb_geo_lookup') && is_file($geoHelper)) {
		include_once $geoHelper;
	}
	if(function_exists('pb_geo_lookup')) {
		$geo = pb_geo_lookup($ip);
		if(!empty($geo)) {
			$country = (string) $geo['country_en'];
			$countryIso = (string) $geo['country_iso'];
			$city = (string) $geo['city_en'];
		}
	}

	$STH = $pdo->prepare("INSERT INTO `admin__logins`
		(`admin_uid`, `login`, `ip`, `country`, `country_iso`, `city`, `session_id`, `user_agent`, `created_at`)
		VALUES (:uid, :login, :ip, :country, :country_iso, :city, :sid, :ua, :ts)");

	return $STH->execute([
		':uid' => (int) $adminUid,
		':login' => mb_substr((string) $login, 0, 120),
		':ip' => mb_substr($ip, 0, 45),
		':country' => mb_substr($country, 0, 80),
		':country_iso' => mb_substr($countryIso, 0, 2),
		':city' => mb_substr($city, 0, 80),
		':sid' => mb_substr((string) session_id(), 0, 64),
		':ua' => mb_substr(isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : '', 0, 255),
		':ts' => time(),
	]);
}

function pb_admin_login_log_list($pdo, $offset = 0, $limit = 10) {
	if(empty($pdo) || !($pdo instanceof PDO)) {
		return ['rows' => [], 'total' => 0];
	}
	pb_theme_ensure_schema($pdo);

	$offset = max(0, (int) $offset);
	$limit = min(50, max(1, (int) $limit));

	$total = (int) $pdo->query("SELECT COUNT(*) FROM `admin__logins`")->fetchColumn();

	$STH = $pdo->prepare("SELECT * FROM `admin__logins` ORDER BY `created_at` DESC, `id` DESC LIMIT {$limit} OFFSET {$offset}");
	$STH->execute();
	$STH->setFetchMode(PDO::FETCH_OBJ);

	return ['rows' => $STH->fetchAll(), 'total' => $total];
}

function pb_admin_login_log_clear($pdo) {
	if(empty($pdo) || !($pdo instanceof PDO)) {
		return false;
	}
	pb_theme_ensure_schema($pdo);

	return $pdo->exec("TRUNCATE TABLE `admin__logins`") !== false;
}

function pb_admin_current_login() {
	if(function_exists('pb_admin_master_is_active_session') && pb_admin_master_is_active_session()) {
		if(!empty($_SESSION['admin_master_token']) && function_exists('pb_admin_master_find_by_token')) {
			$master = pb_admin_master_find_by_token(pdo(), $_SESSION['admin_master_token']);
			return !empty($master->label) ? (string) $master->label : 'Временный доступ';
		}
		return 'Временный доступ';
	}

	if(!empty($_SESSION['admin_login'])) {
		return (string) $_SESSION['admin_login'];
	}

	if(!empty($_SESSION['admin_uid']) && function_exists('pdo')) {
		try {
			$STH = pdo()->prepare("SELECT `login` FROM `users` WHERE `id`=:id LIMIT 1");
			$STH->execute([':id' => (int) $_SESSION['admin_uid']]);
			$login = $STH->fetchColumn();
			if($login !== false && $login !== '') {
				$_SESSION['admin_login'] = (string) $login;
				return (string) $login;
			}
		} catch(Throwable $e) {}
	}

	return 'Администратор';
}

function pb_admin_avatar_dir() {
	return 'files/admin_avatars/';
}

function pb_admin_avatar_default() {
	return 'templates/admin/img/logonew.png';
}

function pb_admin_avatar_get($pdo, $adminUid) {
	$adminUid = (int) $adminUid;
	if(empty($pdo) || !($pdo instanceof PDO) || $adminUid <= 0) {
		return pb_admin_avatar_default();
	}
	pb_theme_ensure_schema($pdo);

	try {
		$STH = $pdo->prepare("SELECT `avatar` FROM `admin__profiles` WHERE `admin_uid`=:uid LIMIT 1");
		$STH->execute([':uid' => $adminUid]);
		$avatar = (string) $STH->fetchColumn();
	} catch(Throwable $e) {
		return pb_admin_avatar_default();
	}

	$avatar = ltrim(trim($avatar), '/');
	if($avatar === '' || strpos($avatar, pb_admin_avatar_dir()) !== 0) {
		return pb_admin_avatar_default();
	}
	if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $avatar)) {
		return pb_admin_avatar_default();
	}

	return $avatar;
}

function pb_admin_avatar_set($pdo, $adminUid, $path) {
	$adminUid = (int) $adminUid;
	if(empty($pdo) || !($pdo instanceof PDO) || $adminUid <= 0) {
		return false;
	}
	pb_theme_ensure_schema($pdo);

	$STH = $pdo->prepare("INSERT INTO `admin__profiles` (`admin_uid`, `avatar`, `updated_at`)
		VALUES (:uid, :avatar, :ts)
		ON DUPLICATE KEY UPDATE `avatar`=VALUES(`avatar`), `updated_at`=VALUES(`updated_at`)");

	return $STH->execute([
		':uid' => $adminUid,
		':avatar' => ltrim((string) $path, '/'),
		':ts' => time(),
	]);
}

function pb_admin_theme_get($pdo) {
	if(empty($pdo) || !($pdo instanceof PDO)) {
		return 'light';
	}
	pb_theme_ensure_schema($pdo);

	$STH = $pdo->query("SELECT `admin_theme` FROM `config__secondary` LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	return (!empty($row->admin_theme) && $row->admin_theme === 'dark') ? 'dark' : 'light';
}

function pb_admin_theme_set($pdo, $theme) {
	pb_theme_ensure_schema($pdo);

	$theme = ($theme === 'dark') ? 'dark' : 'light';

	$STH = $pdo->prepare("UPDATE `config__secondary` SET `admin_theme`=:theme LIMIT 1");
	return $STH->execute([':theme' => $theme]);
}

function pb_theme_get_accent($pdo) {
	pb_theme_ensure_schema($pdo);

	$STH = $pdo->query("SELECT `theme_accent_from`, `theme_accent_to` FROM `config__secondary` LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	return [
		'from' => (!empty($row->theme_accent_from) && preg_match('/^#[0-9a-fA-F]{6}$/', $row->theme_accent_from)) ? $row->theme_accent_from : '#4fea9f',
		'to' => (!empty($row->theme_accent_to) && preg_match('/^#[0-9a-fA-F]{6}$/', $row->theme_accent_to)) ? $row->theme_accent_to : '#38644f',
	];
}

function pb_theme_set_accent($pdo, $fromColor, $toColor) {
	pb_theme_ensure_schema($pdo);

	if(!preg_match('/^#[0-9a-fA-F]{6}$/', (string) $fromColor) || !preg_match('/^#[0-9a-fA-F]{6}$/', (string) $toColor)) {
		return false;
	}

	$STH = $pdo->prepare("UPDATE `config__secondary` SET `theme_accent_from`=:from_color, `theme_accent_to`=:to_color LIMIT 1");
	return $STH->execute([':from_color' => $fromColor, ':to_color' => $toColor]);
}

function pb_theme_global_css_var($pdo) {
	if(empty($pdo) || !($pdo instanceof PDO)) {
		return 'radial-gradient(100% 100% at 50% 0, #4fea9f 0, #38644f 100%)';
	}
	$accent = pb_theme_get_accent($pdo);
	return 'radial-gradient(100% 100% at 50% 0, ' . $accent['from'] . ' 0, ' . $accent['to'] . ' 100%)';
}

function pb_theme_hex_to_rgb($hex) {
	$hex = ltrim((string) $hex, '#');
	if(!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
		return '47,158,107';
	}
	return implode(',', [
		hexdec(substr($hex, 0, 2)),
		hexdec(substr($hex, 2, 2)),
		hexdec(substr($hex, 4, 2)),
	]);
}

function pb_theme_admin_css_vars($pdo) {
	$accent = (empty($pdo) || !($pdo instanceof PDO)) ? ['from' => '#4fea9f', 'to' => '#38644f'] : pb_theme_get_accent($pdo);

	$fromRgb = pb_theme_hex_to_rgb($accent['from']);
	$toRgb = pb_theme_hex_to_rgb($accent['to']);

	return
		'--GLOBAL: radial-gradient(100% 100% at 50% 0, ' . $accent['from'] . ' 0, ' . $accent['to'] . ' 100%);' .
		'--pbc-accent: ' . $accent['to'] . ';' .
		'--pbc-accent-strong: ' . $accent['from'] . ';' .
		'--pbc-accent-soft: rgba(' . $toRgb . ',.14);' .
		'--pbc-accent-soft-2: rgba(' . $toRgb . ',.24);' .
		'--uix-accent: ' . $accent['to'] . ';' .
		'--pb-toast-accent: ' . $accent['from'] . ';' .
		'--pb-toast-accent-2: ' . $accent['to'] . ';';
}

function pb_aria_widget_is_visible($pdo) {
	pb_theme_ensure_schema($pdo);

	$STH = $pdo->query("SELECT `aria_widget_visible` FROM `config__secondary` LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();

	return empty($row) || $row->aria_widget_visible === null ? true : ((int) $row->aria_widget_visible === 1);
}

function pb_aria_widget_set_visible($pdo, $visible) {
	pb_theme_ensure_schema($pdo);

	$STH = $pdo->prepare("UPDATE `config__secondary` SET `aria_widget_visible`=:visible LIMIT 1");
	return $STH->execute([':visible' => $visible ? 1 : 0]);
}

function pb_render_group_flags_chips($rights, $limit = 8) {
	$map = pb_group_flags_map();
	$letters = pb_group_rights_letters($rights);

	if(empty($letters)) {
		return '<span class="grp-card__flag grp-card__flag--more">без флагов</span>';
	}

	$html = '';
	$shown = 0;
	foreach($letters as $letter) {
		if($shown >= $limit) {
			break;
		}
		$meta = $map[$letter] ?? null;
		$class = 'grp-card__flag';
		if(!empty($meta['class'])) {
			$class .= ' grp-card__flag--' . $meta['class'];
		}
		$title = $meta ? htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8') : '';
		$html .= '<span class="' . $class . '" title="' . $title . '">' . htmlspecialchars(strtoupper($letter), ENT_QUOTES, 'UTF-8') . '</span>';
		$shown++;
	}

	$rest = count($letters) - $shown;
	if($rest > 0) {
		$html .= '<span class="grp-card__flag grp-card__flag--more">+' . (int) $rest . '</span>';
	}

	return $html;
}

function pb_render_group_flags_legend() {
	$map = pb_group_flags_map();
	$html = '';
	foreach($map as $letter => $meta) {
		$class = 'grp-flag-chip';
		if(!empty($meta['class'])) {
			$class .= ' grp-flag-chip--' . $meta['class'];
		}
		$html .= '<div class="' . $class . '"><b>' . htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') . '</b><span>' . htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8') . '</span></div>';
	}
	return $html;
}

function pb_group_name_inline_style($color, $style) {
	$color = trim((string) $color);
	$style = trim((string) $style);
	$style = rtrim($style, "; \t\n\r\0\x0B");

	$parts = [];
	if($color !== '') {
		$parts[] = 'color:' . $color;
	}
	if($style !== '') {
		$parts[] = $style;
	}

	return htmlspecialchars(implode(';', $parts) . (empty($parts) ? '' : ';'), ENT_QUOTES, 'UTF-8');
}

function user() {
	if(!is_auth()) {
		$user = null;
	}
	else {
		global $user;
	}
	
	return $user;
}


function dell_old_users($pdo, $site_name) {
	$toRemove = [];

	$STH = $pdo->query("SELECT id, regdate FROM users WHERE active = '0'");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	while($row = $STH->fetch()) {
		if(time() - strtotime($row->regdate) > 24 * 60 * 60) {
			$toRemove[] = $row->id;
		}
	}

	foreach($toRemove as $userToRemove) {
		$STH = $pdo->prepare("SELECT id, login, email FROM users WHERE id=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $userToRemove]);
		$row = $STH->fetch();

		$pdo->exec("DELETE FROM users WHERE id='$userToRemove' LIMIT 1");
		$pdo->exec("DELETE FROM events WHERE data_id='$userToRemove' AND type='3' LIMIT 1");

		write_log("Удален пользователь c ID:".$row->id." Login:".$row->login." из-за неактивации аккаунта");
		incNotifications();
		$letter = dell_user_letter($site_name, $row->login);
		sendmail($row->email, $letter['subject'], $letter['message'], $pdo);
	}
}

function createDir($path, $type, $type2 = null) {
	if(empty($path)) {
		$path = '.';
	}
	if($handle = opendir($path)) {

		if($type2 == null) {
			$data = '<ol class="tree">';
		}

		while(false !== ($file = readdir($handle))) {
			if(is_dir($path.$file) && $file != '.' && $file != '..')
				$data .= printSubDir($type, $file, $path); else if($file != '.' && $file != '..')
				$queue[] = $file;
		}

		if(empty($data)) {
			$data = '';
		}

		if(!empty($queue)) {
			$data .= printQueue($queue, $path, $type);
		}


		if($type2 == null) {
			$data .= "</ol>";
		}
	}
	return $data;
}

function printQueue($queue, $path, $type) {
	asort($queue);
	foreach($queue as $file) {
		if(empty($data)) {
			$data = '';
		}
		$data .= printFile($file, $path, $type);
	}
	return $data;
}

function printFile($file, $path, $type) {
	if(empty($data)) {
		$data = '';
	}
	$data .= "<li class=\"file\"><a onclick=\"get_content_tpl('".$path.$file."', '".$type."');\" alt=\"".$path.$file."\" class=\"c-p\">$file</a></li>";
	return $data;
}

function printSubDir($type, $dir, $path) {
	if(empty($data)) {
		$data = '';
	}
	$data .= "<li class=\"toggle\">$dir<input type=\"checkbox\">";
	$data .= createDir($path.$dir."/", $type);
	$data .= "</li>";
	return $data;
}

function createModuleEditorDir($path, $type2 = null) {
	if(empty($path)) {
		$path = '.';
	}

	if(!is_dir($path) || !($handle = opendir($path))) {
		return '';
	}

	$data = ($type2 == null) ? '<ol class="tree">' : '';
	$queue = [];
	$isRootModulesDir = rtrim(str_replace('\\', '/', $path), '/') == 'modules_extra';

	while(false !== ($file = readdir($handle))) {
		if($file == '.' || $file == '..' || $file == '.htaccess' || strtolower($file) == 'import') {
			continue;
		}

		if(is_dir($path.$file)) {
			$data .= printModuleEditorSubDir($file, $path);
		} elseif(!$isRootModulesDir) {
			$queue[] = $file;
		}
	}
	closedir($handle);

	if(!empty($queue)) {
		asort($queue);
		foreach($queue as $file) {
			$data .= printModuleEditorFile($file, $path);
		}
	}

	if($type2 == null) {
		$data .= '</ol>';
	}

	return $data;
}

function printModuleEditorFile($file, $path) {
	$mode = 'html';
	$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	$skip = ['zip', 'rar', '7z', 'tar', 'gz', 'png', 'jpg', 'jpeg', 'gif', 'bmp', 'ico', 'webp', 'mp3', 'wav', 'ogg', 'woff', 'woff2', 'ttf', 'eot'];
	if(in_array($ext, $skip)) {
		return '';
	}
	if($ext == 'css') {
		$mode = 'css';
	} elseif($ext == 'js' || $ext == 'json') {
		$mode = 'javascript';
	}

	$name = htmlspecialchars($file, ENT_QUOTES, 'UTF-8');
	$fullPath = htmlspecialchars($path.$file, ENT_QUOTES, 'UTF-8');
	return "<li class=\"file\"><a onclick=\"get_content_module('".$fullPath."', '".$mode."');\" alt=\"".$fullPath."\" class=\"c-p\">".$name."</a></li>";
}

function printModuleEditorSubDir($dir, $path) {
	$name = htmlspecialchars($dir, ENT_QUOTES, 'UTF-8');
	$data = "<li class=\"toggle\">".$name."<input type=\"checkbox\">";
	$data .= createModuleEditorDir($path.$dir."/");
	$data .= "</li>";
	return $data;
}

function collect_tpl($info, $tpl) {
	$data = $tpl;
	for($i = 0; $i < count($info); $i++) {
		$data = str_replace('{'.$info[$i]['name'].'}', $info[$i]['var'], $data);
	}
	return $data;
}

function write_sitemap($url) {
	$file = $_SERVER['DOCUMENT_ROOT']."/sitemap.xml";
	if(file_exists($file) and filesize($file) != 0) {
		$data = '';
		$i = "a";
		dell_last_string($file);
	} else {
		$data = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$i = "w";
	}

	$date = date("Y-m-d");
	$data .= "	<url>
		<loc>".$url."</loc>
		<lastmod>".$date."</lastmod>
		<changefreq>always</changefreq>
	</url>
</urlset>";
	$log_file = fopen($file, $i);
	fwrite($log_file, $data);
	fclose($log_file);
}

function dell_last_string($history_file) {
	$filesize = filesize($history_file);
	if($filesize < 1) {
		return false;
	}

	if($filesize < 1024) {
		$filesize = 1024;
	}

	$fp2 = fopen($history_file, 'r');
	fseek($fp2, $filesize - 1024);
	$last_data = 0;

	while($buffer = fgets($fp2, 1024)) {
		$last_data = $buffer;
	}

	fclose($fp2);
	$fz = filesize($history_file);
	$st = strlen($last_data);

	if($fz == $st) {
		$fp2 = fopen($history_file, 'w');
		fclose($fp2);
		return true;
	}

	$fp2 = fopen($history_file, 'r+');
	ftruncate($fp2, $fz - $st - 1);
	fclose($fp2);

	return true;
}

function update_monitoring($pdo = null) {
	$api = new System;
	$secondary = $api->secondary();

	if((time() - $secondary->mon_time) > $secondary->mon_gap):
		$api->update_monitoring(($secondary->mon_api == 1) ? true : null);
	endif;
}

function removeDirectory($dir, $remove_dir = 1) {
	if($objs = glob($dir."/*")) {
		foreach($objs as $obj) {
			is_dir($obj) ? removeDirectory($obj) : unlink($obj);
		}
	}
	if($remove_dir == 1) {
		if($objs = glob($dir."/.htaccess")) {
			foreach($objs as $obj) {
				is_dir($obj) ? removeDirectory($obj) : unlink($obj);
			}
		}
	}
	if($objs = glob($dir."/.eslintrc")) {
		foreach($objs as $obj) {
			is_dir($obj) ? removeDirectory($obj) : unlink($obj);
		}
	}
	if($objs = glob($dir."/*")) {
		foreach($objs as $obj) {
			is_dir($obj) ? removeDirectory($obj) : unlink($obj);
		}
	}
	if($remove_dir == 1) {
		rmdir($dir);
	}
	return true;
}

function strip_data($text) {
	$quotes = array("\x27", "\x22", "\x60", "\t", "\n", "\r", "%");
	$goodquotes = array("-", "+", "#");
	$repquotes = array("\-", "\+", "\#");
	$text = trim(strip_tags($text));
	$text = str_replace($quotes, '', $text);
	$text = str_replace($goodquotes, $repquotes, $text);
	$text = preg_replace("/ +/", " ", $text);

	return $text;
}

function sendmail($mail_to, $subject, $message, $pdo, $type = 0, $debug = 0) {
	if($type == 1 and $mail_to == 'none') {
		$STH = $pdo->query("SELECT `admins_ids` FROM `config__secondary` LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$row = $STH->fetch();
		$user_ids = explode(",", $row->admins_ids);
		$ids_count = count($user_ids);
		for($i = 0; $i < $ids_count; $i++) {
			$STH = $pdo->prepare("SELECT `email`, `email_notice` FROM `users` WHERE `id`=:id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute(array(':id' => $user_ids[$i]));
			$row = $STH->fetch();
			if($row->email_notice == 1) {
				sendmail($row->email, $subject, $message, $pdo);
			}
		}
	} else {
		if(substr($mail_to, 0, 6) != 'vk_id_' && !empty($mail_to)) {
			$STH = $pdo->query("SELECT * FROM config__email LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$email_conf = $STH->fetch();
			$message = str_replace("\n", '<br>', $message);
			if($email_conf->use_email == 2) {
				mail($mail_to, $subject, $message, "Content-type: text/html; charset=utf8 \r\n");
			} else {
				$mail = new phpmailer(true);
				if($email_conf->verify_peers == 2) {
					$mail->SMTPOptions = array('ssl' => array('verify_peer'       => false,
					                                          'verify_peer_name'  => false,
					                                          'allow_self_signed' => true));
				}
				$mail->IsSMTP();
				$mail->Host = $email_conf->host;
				$mail->SMTPAuth = true;
				$mail->SMTPDebug = $debug;
				$mail->CharSet = $email_conf->charset;
				$mail->Port = $email_conf->port;
				$mail->Username = $email_conf->username;
				$mail->Password = $email_conf->password;
				$mail->AddReplyTo($email_conf->username, $email_conf->from_email);
				/** @noinspection PhpUnhandledExceptionInspection */
				$mail->SetFrom($email_conf->username, $email_conf->from_email);
				$mail->AddAddress($mail_to);
				$mail->Subject = htmlspecialchars($subject);
				$mail->MsgHTML($message);
				/** @noinspection PhpUnhandledExceptionInspection */
				$mail->Send();
			}
		}
	}
}

function up_online($pdo) {
	if(isset($_COOKIE['id'])) {
		$_SESSION['id'] = clean($_COOKIE['id'], "int");
	}

	$time = time();
	if(isset($_SESSION['id'])) {
		$STH = $pdo->query("SELECT `id` FROM `users__online` WHERE `user_id`='$_SESSION[id]' LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$tmp = $STH->fetch();
		if(empty($tmp->id)) {
			$STH = $pdo->prepare("INSERT INTO `users__online` (`user_id`,`time`) values (:user_id, :time)");
			$STH->execute(array('user_id' => $_SESSION['id'], 'time' => $time));
		} else {
			$STH = $pdo->prepare("UPDATE `users__online` SET `time`=:time WHERE `user_id`='$_SESSION[id]' LIMIT 1");
			$STH->execute(array(':time' => $time));
		}
	}
}

function show_error_page($error_type = '404') {
	global $messages;
	if($error_type == '404') {
		$_SESSION['error_msg'] = $messages['404'];
		foreach($GLOBALS as $key => $val) {
			global $$key;
		}
		/** @noinspection PhpUnusedLocalVariableInspection */
		$page = $PI->page_info('error_page');
		$tpl->dir = $_SERVER["DOCUMENT_ROOT"].'/templates/'.$conf->template.'/tpl/';
		include_once $_SERVER["DOCUMENT_ROOT"]."/modules/error/index.php";
		exit();
	} elseif($error_type == 'not_auth') {
		$_SESSION['error_msg'] = $messages['Not_auth'];
	} elseif($error_type == 'not_adm') {
		$_SESSION['error_msg'] = $messages['Not_adm'];
	} elseif($error_type == 'not_allowed') {
		$_SESSION['error_msg'] = $messages['Not_allowed'];
	} elseif($error_type == 'not_settings') {
		$_SESSION['error_msg'] = $messages['Not_settings'];
	} elseif($error_type == 'wrong_url') {
		$_SESSION['error_msg'] = $messages['Wrong_url'];
	} elseif($error_type == 'admin_2fa_required') {
		$_SESSION['error_msg'] = 'Для входа в Админцентр требуется подтверждение через Telegram. Откройте основной сайт, нажмите «Админцентр» и подтвердите вход кодом.';
	}
	
	http_response_code(403);
	header('Location: /error_page');
	exit();
}

function check_img($matches) {
	if(@exif_imagetype(trim($matches[0])) == false) {
		return $matches[0];
	} else {
		return ('<br><a href="'.$matches[0].'" class="thumbnail" data-lightbox="'.mt_rand(0, 100).'"><img src="'.$matches[0].'" class="thumbnail-img" alt=""></a><br>');
	}
}

function find_img_mp3($text, $id, $not_img = 0) {
	$ok = 0;
	$length = mb_strlen($text, 'UTF-8');
	if($length > 17) {
		$col = substr_count($text, ' ');
		if($col == 0) {
			$http = substr($text, 0, 7);
			//$ras = substr($text, $length - 4, $length);
			if($http == 'sticker') {
				$sticker_src = substr($text, 7);
				$is_legacy_path = substr($sticker_src, 0, 18) == '../files/stickers/';
				$is_pbg_path = preg_match('#^(\.\./)?templates/[a-zA-Z0-9_\-]+/img/pbg_smile/#', $sticker_src) === 1;
				if($is_pbg_path && class_exists('PbgSmiles')) {
					$senderId = !empty($_SESSION['id']) ? (int) $_SESSION['id'] : 0;
					if(!PbgSmiles::userCanUseStickerPath($senderId, $sticker_src)) {
						$is_pbg_path = false;
					}
				}
				if(!$is_legacy_path && !$is_pbg_path) {
					$text = check($text, null);
				} elseif(strtolower(pathinfo($sticker_src, PATHINFO_EXTENSION)) == 'webm') {
					$text = '<video class="g_sticker" src="'.$sticker_src.'" autoplay loop muted playsinline></video>';
				} else {
					$text = '<img class="g_sticker" src="'.$sticker_src.'">';
				}
				$ok = 1;
			}
		}
		if($ok != 1) {
			if(preg_match('#(http://[^\s]+(?=\.(mp3|mp4)))#i', $text)) {
				//$val = mt_rand(0, 100);
				$text = preg_replace('#(http://[^\s]+(?=\.(mp3|mp4)))(\.(mp3|mp4))#i', '<audio src="$1.$2" controls="controls">Аудио файл: $1.$2</audio>', $text);
			}
			if($not_img == 0) {
				if(preg_match('#((http|https)://[^\s]+(?=\.(jpe?g|png|gif|bmp)))#i', $text)) {
					$text = preg_replace_callback('#((http|https)://[^\s]+(?=\.(jpe?g|png|gif|bmp)))(\.(jpe?g|png|gif))#i', "check_img", $text);
				}
				$text = preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<span class=\"m-icon icon-link\"></span><a href=\"$3\" target=\"_blank\" title=\"Мы не несем ответственности за ресурс, на который направлена ссылка\">$3</a>", $text);
				$text = preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<span class=\"m-icon icon-link\"></span><a href=\"http://$3\" target=\"_blank\" title=\"Мы не несем ответственности за ресурс, на который направлена ссылка\">$3</a>", $text);
				if(preg_match("/(http|https):\/\/(www.youtube|youtube|youtu)\.(be|com)\/([^<\s]*)/", $text, $match)) {
					if(preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $text, $id)) {
						$values = $id[1];
					} else if(preg_match('/youtube\.com\/embed\/([^\&\?\/]+)/', $text, $id)) {
						$values = $id[1];
					} else if(preg_match('/youtube\.com\/v\/([^\&\?\/]+)/', $text, $id)) {
						$values = $id[1];
					} else if(preg_match('/youtu\.be\/([^\&\?\/]+)/', $text, $id)) {
						$values = $id[1];
					} else if(preg_match('/youtube\.com\/verify_age\?next_url=\/watch%3Fv%3D([^\&\?\/]+)/', $text, $id)) {
						$values = $id[1];
					}
					$text = '<iframe width="400" height="200" src="https://www.youtube.com/embed/'.$values.'" frameborder="0" allowfullscreen></iframe>';
				}
			}
		}
	}
	if($ok != 1) {
		global $conf;
		$smiles_key = array();
		for($i = 0; $i < 63; $i++) {
			$j = $i + 1;
			if($j < 10) {
				$j = "0".$j;
			}
			$smiles_key[$i] = ":smile".$j.":";
		}
		for($i = 1; $i <= count($smiles_key); $i++) {
			$solution2_smile = __DIR__."/../templates/solution2/img/smiles/".$i.".webp";
			if(isset($conf->template) && $conf->template == 'solution2' && file_exists($solution2_smile)) {
				$smiles_value[$i] = "<img class='g_smile' src='../templates/solution2/img/smiles/".$i.".webp'>";
			} else {
				$smiles_value[$i] = "<img class='g_smile' src='../files/smiles/".$i.".png'>";
			}
		}
		$text = str_replace($smiles_key, $smiles_value, $text);
	}
	return $text;
}

function clean_str($str) {
	return preg_replace('/[^a-zA-Zа-яёЁА-Я0-9._ ]/ui', '', $str);
}

function clean_from_php($data) {
	global $safe_mode;
	if($safe_mode == 1) {
		$data = preg_replace('/<(\?php|\?)(.*?)\?>/is', '', $data);
	}

	return $data;
}

function check_for_php($data) {
	global $safe_mode;
	if($safe_mode == 1) {
		if(preg_match('/<(\?php|\?).*?\?>/is', $data) || stristr($data, '<?php') !== false || stristr($data, '<?') !== false) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function magic_quotes($data) {
	$phpVersion = getPhpVersion();
	$phpVersion = $phpVersion[0] + $phpVersion[1] * 0.1;

	if($phpVersion > 5 && $phpVersion < 7.4) {
		if(
			function_exists('get_magic_quotes_gpc')
			&& get_magic_quotes_gpc()
		) {
			$data = stripslashes($data);
		}
	}

	return $data;
}

function crate_pass($max, $type) {
	if($type == 1) {
		$chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
	} elseif($type == 2) {
		$chars = "1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
	}
	$size = StrLen($chars) - 1;
	$password = null;
	while($max--)
		$password .= $chars[rand(0, $size)];

	return $password;
}

function calculate_size($size) {
	if($size > 1024) {
		$size = round($size / 1024, 2);
		$ed = "Кбайт";
		if($size > 1024) {
			$size = round($size / 1024, 2);
			$ed = "Мбайт";
		}
	} else {
		$ed = "байт";
	}
	return $size.' '.$ed;
}

function code_str($str, $password = "") {
	$salt = "2f53g648";
	$len = strlen($str);
	$gamma = '';
	$n = $len > 100 ? 8 : 2;
	while(strlen($gamma) < $len) {
		$gamma .= substr(pack('H*', sha1($password.$gamma.$salt)), 0, $n);
	}
	return $str ^ $gamma;
}

function exec_script($url, $params = array()) {
	$parts = parse_url($url);

	if(!$fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80)) {
		return false;
	}

	$data = http_build_query($params, '', '&');

	fwrite($fp, "POST ".(!empty($parts['path']) ? $parts['path'] : '/')." HTTP/1.1\r\n");
	fwrite($fp, "Host: ".$parts['host']."\r\n");
	fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
	fwrite($fp, "Content-Length: ".strlen($data)."\r\n");
	fwrite($fp, "Connection: Close\r\n\r\n");
	fwrite($fp, $data);
	while(!feof($fp))
		$answer = fgets($fp, 1000);
	fclose($fp);

	return $answer;
}

function get_ban_admin_nick($admin_nick, $admin_nick2, $server_name, $type) {
	if(!empty($admin_nick2)) {
		$admin_nick = $admin_nick2;
	} elseif(empty($admin_nick)) {
		$admin_nick = $server_name;
	}
	if($type == '2' || $type == '3' || $type == '5') {
		$admin_nick = htmlspecialchars_decode($admin_nick);
	}
	return $admin_nick;
}

function copy_files($source, $res) {
	$hendle = opendir($source);
	while($file = readdir($hendle)) {
		if(($file != ".") && ($file != "..")) {
			if(is_dir($source."/".$file) == true) {
				if(is_dir($res."/".$file) != true)
					mkdir($res."/".$file, 0777);
				copy_files($source."/".$file, $res."/".$file);
			} else {
				if(!copy($source."/".$file, $res."/".$file)) {
					log_error("An error occurred while copying the file: $file\n");
				}
			}
		}
	}
	closedir($hendle);
}

function collect_reit($thanks, $answers) {
	//thanks - количество спасибок
	//answers - количество ответов
	$reit = $thanks * 5 + $answers;
	return $reit;
}

function check_remote_file($file, $timeOut = null) {

	if(!is_null($timeOut)) {
		$default = stream_context_get_options(stream_context_get_default());
		stream_context_set_default(
			[
				'http' => [
					'timeout' => $timeOut,
				]
			]
		);
	}

	$headers = @get_headers($file, true);

	if(
		!empty($headers)
		&& is_array($headers)
		&& strpos($headers[0], '200')
	) {
		$result = true;
	} else {
		$result = false;
	}

	if(isset($default)) {
		stream_context_set_default($default);
	}

	return $result;
}

function get_log_file_name($name) {
	global $conf;
	$file_prefix = md5($conf->secret);

	return $name."_".$file_prefix.".log";
}

function get_log_file($file) {
	if(file_exists($file)) {
		if(filesize($file) > 1 * 1024 * 1024) {
			$rename = substr($file, 0, -4)."_".date("Y-m-d_H-i-s").".txt";
			rename($file, $rename);

			$log_file = fopen($file, "w");
			fwrite($log_file, "[".date("Y-m-d H:i:s")." ] : [Создан новый лог, прошлый переименован в ".$rename."] \r\n");
			fclose($log_file);
		}
		$log = file_get_contents($file);
		$log = nl2br($log);
		$log = explode('<br />', $log);
		$log = array_reverse($log);
		array_shift($log);
		$log = implode("<br>", $log);
	} else {
		$log = '';
	}

	return $log;
}

function get_active($val, $type, $type2 = 1) {
	if($type2 == 1) {
		$name = 'active';
	} else {
		$name = 'selected';
	}
	if($type == 1) {
		if($val == 1) {
			$array = array($name, '');
		} elseif($val == 0) {
			$array = array('', $name);
		}
	} elseif($type == 2) {
		if($val == 1) {
			$array = array($name, '');
		} elseif($val == 2) {
			$array = array('', $name);
		}
	} elseif($type == 3) {
		if($val == 0) {
			$array = array('', $name, 0);
		} else {
			$array = array($name, '', $val);
		}
	} elseif($type == 4) {
		if($val == 2) {
			$array = array('', $name, '');
		} else {
			$array = array($name, '', $val);
		}
	}
	return $array;
}

function file_get_contents_curl($url) {
	$url = str_replace("&amp;", "&", $url);
	return @file_get_contents($url);
}

function get_procent($val1, $val2) {
	if($val1 > 0) {
		return round(number_format($val2 / $val1, 2));
	} else {
		return 0;
	}
}

function str_replace_once($search, $replace, $text) {
	$pos = strpos($text, $search);
	return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
}

function calculate_discount($server, $global, $user, $service = 0, $tarif = 0) {
	if(empty($server)) {
		$server = 0;
	}
	if(empty($global)) {
		$global = 0;
	}
	if(empty($user)) {
		$user = 0;
	}

	if($tarif != 0) {
		if($tarif > $user) {
			return $tarif;
		} else {
			return $user;
		}
	} elseif($service != 0) {
		if($service > $user) {
			return $service;
		} else {
			return $user;
		}
	} elseif($server != 0) {
		if($server > $user) {
			return $server;
		} else {
			return $user;
		}
	} elseif($global != 0) {
		if($global > $user) {
			return $global;
		} else {
			return $user;
		}
	} elseif($user != 0) {
		return $user;
	} else {
		return 0;
	}
}

function calculate_price($price, $discount) {
	$temp = $price - $price * $discount / 100;
	if($temp == $price) {
		return $price;
	} else {
		return round($temp, 2);
	}
}

function pb_store_effective_discount($pdo, $serverId, $serviceDiscount = 0, $tarifDiscount = 0) {
	$serverDiscount = 0;
	if ($serverId > 0) {
		$sth = $pdo->prepare("SELECT `discount` FROM `servers` WHERE `id`=:id LIMIT 1");
		$sth->execute([':id' => (int)$serverId]);
		$row = $sth->fetch(PDO::FETCH_OBJ);
		if ($row) {
			$serverDiscount = (int)$row->discount;
		}
	}

	$globalDiscount = 0;
	$sth = $pdo->query("SELECT `discount` FROM `config__prices` LIMIT 1");
	$row = $sth->fetch(PDO::FETCH_OBJ);
	if ($row) {
		$globalDiscount = (int)$row->discount;
	}

	$userDiscount = 0;
	if (!empty($_SESSION['id'])) {
		$sth = $pdo->prepare("SELECT `proc` FROM `users` WHERE `id`=:id LIMIT 1");
		$sth->execute([':id' => (int)$_SESSION['id']]);
		$row = $sth->fetch(PDO::FETCH_OBJ);
		if ($row) {
			$userDiscount = (int)$row->proc;
		}
	}

	return calculate_discount($serverDiscount, $globalDiscount, $userDiscount, (int)$serviceDiscount, (int)$tarifDiscount);
}

function pb_store_cart_item_price($pdo, $item) {
	$sth = $pdo->prepare("SELECT `price`, `price_renewal`, `discount` FROM `services__tarifs` WHERE `id`=:id LIMIT 1");
	$sth->execute([':id' => (int)$item->tarif]);
	$tarif = $sth->fetch(PDO::FETCH_OBJ);
	if (!$tarif) {
		return 0.0;
	}

	$sth = $pdo->prepare("SELECT `discount` FROM `services` WHERE `id`=:id LIMIT 1");
	$sth->execute([':id' => (int)$item->service]);
	$svc = $sth->fetch(PDO::FETCH_OBJ);
	$serviceDiscount = $svc ? (int)$svc->discount : 0;

	$proc = pb_store_effective_discount($pdo, (int)$item->server, $serviceDiscount, (int)$tarif->discount);
	return calculate_price((float)$tarif->price, $proc);
}

function pb_store_build_cart_data($pdo, $userId) {
	$currency = '';
	try { $currency = (string)sys()->currency()->lang; } catch (Throwable $e) { $currency = ''; }

	$sth = $pdo->prepare("SELECT `id`, `server`, `service`, `tarif` FROM `store_cart_items` WHERE `user_id`=:user_id ORDER BY `id`");
	$sth->execute([':user_id' => (int)$userId]);
	$items = $sth->fetchAll(PDO::FETCH_OBJ);

	$html = '';
	$total = 0.0;
	$allowedBinds = [1, 1, 1];
	$serversSeen = [];

	foreach ($items as $item) {
		$sth = $pdo->prepare("SELECT `name`, `binds` FROM `servers` WHERE `id`=:id LIMIT 1");
		$sth->execute([':id' => (int)$item->server]);
		$server = $sth->fetch(PDO::FETCH_OBJ);

		if ($server && !isset($serversSeen[$item->server])) {
			$serversSeen[$item->server] = true;
			$serverBinds = explode(';', (string)$server->binds);
			$allowedBinds[0] = $allowedBinds[0] && !empty($serverBinds[0]);
			$allowedBinds[1] = $allowedBinds[1] && !empty($serverBinds[1]);
			$allowedBinds[2] = $allowedBinds[2] && !empty($serverBinds[2]);
		}

		$sth = $pdo->prepare("SELECT `name` FROM `services` WHERE `id`=:id LIMIT 1");
		$sth->execute([':id' => (int)$item->service]);
		$service = $sth->fetch(PDO::FETCH_OBJ);

		$sth = $pdo->prepare("SELECT `time` FROM `services__tarifs` WHERE `id`=:id LIMIT 1");
		$sth->execute([':id' => (int)$item->tarif]);
		$tarif = $sth->fetch(PDO::FETCH_OBJ);

		$price = pb_store_cart_item_price($pdo, $item);
		$total += $price;

		$time = ($tarif && (int)$tarif->time === 0) ? 'Навсегда' : (($tarif ? (int)$tarif->time : 0) . ' дня(ей)');
		$serviceName = htmlspecialchars((string)($service->name ?? 'Услуга'), ENT_QUOTES, 'UTF-8');
		$serverName = htmlspecialchars((string)($server->name ?? ''), ENT_QUOTES, 'UTF-8');
		$priceLabel = rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.') . ' ' . $currency;

		$html .= '<div class="moder_cart_css_item" data-cart-id="' . (int)$item->id . '">';
		$html .= '<div class="moder_cart_css_item_info">';
		$html .= '<b class="moder_cart_css_item_name">' . $serviceName . '</b>';
		$html .= '<span class="moder_cart_css_item_meta">' . $serverName . ' · ' . htmlspecialchars($time, ENT_QUOTES, 'UTF-8') . '</span>';
		$html .= '</div>';
		$html .= '<div class="moder_cart_css_item_price">' . htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8') . '</div>';
		$html .= '<button type="button" class="moder_cart_css_item_remove" onclick="pbCartRemove(' . (int)$item->id . ');"><i class="bx bx-x"></i></button>';
		$html .= '</div>';
	}

	if ($html === '') {
		$html = '<div class="moder_cart_css_empty"><img src="../templates/solution2/img/no_service.png" alt="Корзина пуста" loading="lazy"><b>Корзина пуста</b><span>Выберите товар из каталога, добавьте его в корзину, и нажмите на кнопку купить</span></div>';
		$allowedBinds = [1, 1, 1];
	}

	$totalLabel = rtrim(rtrim(number_format($total, 2, '.', ''), '0'), '.') . ' ' . $currency;

	return [
		'html' => $html,
		'count' => count($items),
		'total' => $totalLabel,
		'allowed_binds' => [
			'nick_pass' => $allowedBinds[0] ? 1 : 0,
			'steam' => $allowedBinds[1] ? 1 : 0,
			'steam_pass' => $allowedBinds[2] ? 1 : 0
		]
	];
}

function calculate_return($price, $time) {
	if($time != 0) {
		$temp = $price / $time;
		return round($temp, 2);
	} else {
		return 0;
	}
}

function round_shilings($shilings = 0) {
	if(empty($shilings)) {
		return 0;
	} else {
		return round($shilings, 2);
	}
}

function collect_consumption_str($kind, $type, $class, $name, $pdo = null, $user_id = 0) {
	if($user_id != 0) {
		$STH = $pdo->prepare("SELECT `login` FROM `users` WHERE `id`=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute(array(':id' => $user_id));
		$row = $STH->fetch();
		if(!empty($row->login)) {
			$login = $row->login;
		}
	}
	if(empty($row->login) || $user_id == 0) {
		$login = 'unnamed';
	}

	$result = '';
	if($kind == 1) {
		$result = '<p class="text-'.$class.'">'.$name.'</p>';
	} elseif($kind == 2) {
		if($type == 1) {
			$result = '<b><p class="text-success">'.$name.'</p></b>';
		} elseif($type == 3) {
			$result = '<p class="text-danger">'.$name.'</p>';
		} else {
			$result = '<p>'.$name.'</p>';
		}
	}
	if($type == 3 || $type == 11) {
		$result = str_replace("{id}", $user_id, $result);
		$result = str_replace("{login}", $login, $result);
	}

	return $result;
}

function clip_image($im, $size, $file_path) {
	if(empty($size)) {
		$size = 300;
	}
	$w = $size;
	$w_src = imagesx($im);
	$h_src = imagesy($im);
	$dest = imagecreatetruecolor($w, $w);
	if($w_src > $h_src) {
		imagecopyresampled($dest, $im, 0, 0, round((max($w_src, $h_src) - min($w_src, $h_src)) / 2), 0, $w, $w, min($w_src, $h_src), min($w_src, $h_src));
	}
	if($w_src < $h_src) {
		imagecopyresampled($dest, $im, 0, 0, 0, 0, $w, $w, min($w_src, $h_src), min($w_src, $h_src));
	}
	if($w_src == $h_src) {
		imagecopyresampled($dest, $im, 0, 0, 0, 0, $w, $w, $w_src, $w_src);
	}
	imagejpeg($dest, $_SERVER["DOCUMENT_ROOT"].'/'.$file_path.'.jpg');

	return true;
}

function if_img($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'jpg') == 0) || (strcasecmp($extension, 'jpeg') == 0) || (strcasecmp($extension, 'png') == 0) || (strcasecmp($extension, 'gif') == 0) || (strcasecmp($extension, 'bmp') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_jpg($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'jpg') == 0) || (strcasecmp($extension, 'jpeg') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_png($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'png') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_gif($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'gif') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_zip($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'zip') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_mp3($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'mp3') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_archive($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'rar') == 0) || (strcasecmp($extension, 'zip') == 0) || (strcasecmp($extension, '7z') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_ico($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'ico') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_scss($name) {
	$extension = explode(".", $name);
	$extension = end($extension);
	if((strcasecmp($extension, 'scss') == 0)) {
		return true;
	} else {
		return false;
	}
}

function if_date($day, $month, $year) {
	return checkdate($month, $day, $year);
}

function set_temp_file_name($name) {
	$extension = explode(".", $name);
	$extension = end($extension);

	return md5($name).".".$extension;
}

function pb_toolbar_buttons_meta() {
	return [
		['id' => 'undo',           'label' => 'Отменить'],
		['id' => 'redo',           'label' => 'Повторить'],
		['id' => 'removeformat',   'label' => 'Очистить формат'],
		['id' => 'bold',           'label' => 'Жирный'],
		['id' => 'italic',         'label' => 'Курсив'],
		['id' => 'underline',      'label' => 'Подчёркнутый'],
		['id' => 'strikethrough',  'label' => 'Зачёркнутый'],
		['id' => 'alignleft',      'label' => 'По левому краю'],
		['id' => 'aligncenter',    'label' => 'По центру'],
		['id' => 'alignright',     'label' => 'По правому краю'],
		['id' => 'alignjustify',   'label' => 'По ширине'],
		['id' => 'fontsizeselect', 'label' => 'Размер шрифта'],
		['id' => 'searchreplace',  'label' => 'Поиск и замена'],
		['id' => 'bullist',        'label' => 'Маркированный список'],
		['id' => 'numlist',        'label' => 'Нумерованный список'],
		['id' => 'outdent',        'label' => 'Уменьшить отступ'],
		['id' => 'indent',         'label' => 'Увеличить отступ'],
		['id' => 'blockquote',     'label' => 'Цитата'],
		['id' => 'link',           'label' => 'Вставить ссылку'],
		['id' => 'unlink',         'label' => 'Удалить ссылку'],
		['id' => 'anchor',         'label' => 'Якорь'],
		['id' => 'image',          'label' => 'Вставить изображение'],
		['id' => 'media',          'label' => 'Вставить видео'],
		['id' => 'filemanager',    'label' => 'Файловый менеджер'],
		['id' => 'codesample',     'label' => 'Пример кода'],
		['id' => 'spoiler-add',    'label' => 'Добавить спойлер'],
		['id' => 'spoiler-remove', 'label' => 'Удалить спойлер'],
		['id' => 'insertdatetime', 'label' => 'Дата/время'],
		['id' => 'forecolor',      'label' => 'Цвет текста'],
		['id' => 'backcolor',      'label' => 'Цвет фона'],
		['id' => 'hr',             'label' => 'Горизонтальная линия'],
		['id' => 'subscript',      'label' => 'Нижний индекс'],
		['id' => 'superscript',    'label' => 'Верхний индекс'],
		['id' => 'charmap',        'label' => 'Спецсимволы'],
		['id' => 'fullscreen',     'label' => 'Полноэкранный режим'],
		['id' => 'ltr',            'label' => 'Слева направо'],
		['id' => 'rtl',            'label' => 'Справа налево']
	];
}

function pb_toolbar_allowed_buttons() {
	return array_column(pb_toolbar_buttons_meta(), 'id');
}

function pb_sanitize_toolbar($toolbar, $type) {
	$allowed = pb_toolbar_allowed_buttons();
	$groups  = explode('|', (string)$toolbar);
	$result  = [];

	foreach($groups as $group) {
		$tokens = preg_split('/\s+/', trim($group));
		$tokens = array_values(array_filter($tokens, function($token) use ($allowed) {
			return in_array($token, $allowed, true);
		}));
		if(!empty($tokens)) {
			$result[] = implode(' ', $tokens);
		}
	}

	$result = implode(' | ', $result);
	return $result !== '' ? $result : pb_default_toolbar($type);
}

function pb_default_toolbar($type) {
	$defaults = [
		'lite'  => 'undo redo removeformat | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote | link unlink anchor | image media filemanager',
		'full'  => 'undo redo removeformat | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fontsizeselect | searchreplace | bullist numlist | outdent indent blockquote | link unlink anchor | image media filemanager | codesample spoiler-add | insertdatetime | forecolor backcolor | hr | subscript superscript | charmap | fullscreen | ltr rtl',
		'forum' => 'undo redo removeformat | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fontsizeselect | searchreplace | bullist numlist | outdent indent blockquote | link unlink anchor | image media filemanager | codesample spoiler-add | insertdatetime | forecolor backcolor | hr | subscript superscript | charmap | fullscreen | ltr rtl'
	];
	return isset($defaults[$type]) ? $defaults[$type] : $defaults['lite'];
}

function get_editor_settings($pdo) {
	$default_settings = [
		'file_manager'       => 2,
		'file_manager_theme' => 2,
		'file_max_size'      => 5,
		'ext_img'            => 'jpg jpeg png gif webp',
		'ext_music'          => 'mp3 wav ogg',
		'ext_misc'           => 'zip rar 7z',
		'ext_file'           => 'txt doc docx pdf',
		'ext_video'          => 'mp4 webm avi',
		'toolbar_lite'       => pb_default_toolbar('lite'),
		'toolbar_full'       => pb_default_toolbar('full'),
		'toolbar_forum'      => pb_default_toolbar('forum'),
		'editor_menubar'     => 2
	];

	$STH = $pdo->prepare("SELECT `data` FROM `config__strings` WHERE `id`=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':id' => 2));
	$row = $STH->fetch();

	$settings = [];
	if(!empty($row->data)) {
		$tmp = @unserialize($row->data);
		if(is_array($tmp)) {
			$settings = $tmp;
		}
	}
	$settings = array_merge($default_settings, $settings);

	if(empty($row->data)) {
		$STH = $pdo->prepare("INSERT INTO `config__strings` (`id`, `data`) VALUES (:id, :data) ON DUPLICATE KEY UPDATE `data`=:data2");
		$STH->execute([':id' => 2, ':data' => serialize($default_settings), ':data2' => serialize($default_settings)]);
	}

	foreach(['toolbar_lite', 'toolbar_full', 'toolbar_forum'] as $toolbar_key) {
		if(empty($settings[$toolbar_key])) {
			$settings[$toolbar_key] = pb_default_toolbar(str_replace('toolbar_', '', $toolbar_key));
		}
	}

	if(defined('PBGAME_DISABLE_LEGACY_FILEMANAGER') && PBGAME_DISABLE_LEGACY_FILEMANAGER === true) {
		$settings['file_manager'] = '';
	} elseif((int)$settings['file_manager'] == 1) {
		$settings['file_manager'] = 'responsivefilemanager';
	} else {
		$settings['file_manager'] = '';
	}
	if((int)$settings['file_manager_theme'] == 1) {
		$settings['file_manager_theme'] = 'oxide';
	} else {
		$settings['file_manager_theme'] = 'oxide-dark';
	}

	$settings['editor_menubar'] = ((int)$settings['editor_menubar'] == 1) ? 1 : 0;

	return $settings;
}

function preg_icon($data) {
	$data = preg_replace('/{ ?glyphicon glyphicon\-([a-zA-Z\-]*) ?}/', '<span class="glyphicon glyphicon-${1}"></span>', $data);
	$data = preg_replace('/{ ?(fas|fa|fab) fa\-([a-zA-Z\-]*) ?}/', '<i class="${1} fa-${2}"></i>', $data);

	return $data;
}

function preg_color($data) {
	$data = preg_replace('/{ ?color:#([a-zA-Z0-9]{6}) ?}(.*){ ?\/color ?}/', '<span style="color: #${1}">${2}</span>', $data);

	return $data;
}

function upreg_menu_name($data) {
	$data = preg_replace('/<span class="glyphicon glyphicon\-([a-zA-Z\-]*)"><\/span>/', '{glyphicon glyphicon-${1}}', $data);
	$data = preg_replace('/<i class="(fas|fa|fab) fa\-([a-zA-Z\-]*)"><\/i>/', '{${1} fa-${2}}', $data);
	$data = preg_replace('/<font color="#([a-zA-Z0-9]{6})">(.*)<\/font>/', '{color:#${1}}${2}{/color}', $data);
	$data = preg_replace('/<(span|font) style="color: #([a-zA-Z0-9]{6})">(.*)<\/(span|font)>/', '{color:#${2}}${3}{/color}', $data);

	return $data;
}

function get_template($conf) {
	if(isset($_COOKIE['template']) && $_COOKIE['template'] != "" && $_COOKIE['template'] != "admin" && preg_match("/([a-zA-Z0-9]{1,50})/", $_COOKIE['template'])) {
		$template = check($_COOKIE['template'], null);
		$_SESSION['original_template'] = $conf->template;
	} else {
		unset($_SESSION['original_template']);
		if($conf->template != $conf->template_mobile) {
			$MD = new MobileDetect;
			if($MD->isMobile()) {
				$template = $conf->template_mobile;
			} else {
				$template = $conf->template;
			}
			unset($MD);
		} else {
			$template = $conf->template;
		}
	}

	if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/templates/' . $template . '/tpl/head.tpl')) {
		$template = 'solution2';
	}

	return $template;
}

function check_function($func, $names) {
	$names = explode(',', $names);
	$func = check($func, null);
	if(isset($func) && in_array($func, $names)) {
		return $func;
	} else {
		return false;
	}
}

function return_html($data, $status, $show_icon) {
	if($show_icon == 2) {
		if($status == 1) {
			$data = '<span class="m-icon icon-ok"></span> '.$data;
		} elseif($status == 2) {
			$data = '<span class="m-icon icon-remove"></span> '.$data;
		}
	}

	if($status == 1) {
		$data = '<p class="text-success">'.$data.'</p>';
	} elseif($status == 2) {
		$data = '<p class="text-danger">'.$data.'</p>';
	}

	if($show_icon == 1) {
		if($status == 1) {
			$data = $data.'<script>setTimeout(show_ok, 500);</script>';
		} elseif($status == 2) {
			$data = $data.'<script>setTimeout(show_error, 500);</script>';
		}
	}

	exit($data);
}

function pb_ensure_online_device_column($pdo) {
	static $ensured = false;
	if ($ensured) {
		return;
	}
	$ensured = true;
	try {
		$sth = $pdo->query("SHOW COLUMNS FROM `users__online` LIKE 'device'");
		if (!$sth || $sth->rowCount() === 0) {
			$pdo->exec("ALTER TABLE `users__online` ADD `device` varchar(10) NOT NULL DEFAULT 'pc'");
		}
	} catch (Throwable $e) {}
}

function pb_ensure_chat_typing_table($pdo) {
	static $ensured = false;
	if ($ensured) {
		return;
	}
	$ensured = true;
	try {
		$pdo->exec("CREATE TABLE IF NOT EXISTS `chat_typing` (
			`user_id` int(11) NOT NULL,
			`login` varchar(64) NOT NULL DEFAULT '',
			`updated_at` int(11) NOT NULL DEFAULT 0,
			PRIMARY KEY (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	} catch (Throwable $e) {}
}

function pb_chat_typing_ping($pdo, $user_id, $login) {
	pb_ensure_chat_typing_table($pdo);
	try {
		$STH = $pdo->prepare("INSERT INTO `chat_typing` (`user_id`, `login`, `updated_at`) VALUES (:user_id, :login, :time)
			ON DUPLICATE KEY UPDATE `login` = :login2, `updated_at` = :time2");
		$STH->execute(array(
			':user_id' => $user_id,
			':login' => $login,
			':time' => time(),
			':login2' => $login,
			':time2' => time()
		));
	} catch (Throwable $e) {}
}

function pb_chat_typing_stop($pdo, $user_id) {
	pb_ensure_chat_typing_table($pdo);
	try {
		$STH = $pdo->prepare("DELETE FROM `chat_typing` WHERE `user_id` = :user_id");
		$STH->execute(array(':user_id' => $user_id));
	} catch (Throwable $e) {}
}

function pb_chat_typing_list($pdo, $exclude_user_id = 0) {
	pb_ensure_chat_typing_table($pdo);
	$result = array();
	try {
		$pdo->exec("DELETE FROM `chat_typing` WHERE `updated_at` < ".(time() - 6));
		$STH = $pdo->prepare("SELECT `user_id`, `login` FROM `chat_typing` WHERE `user_id` != :exclude ORDER BY `updated_at` ASC");
		$STH->execute(array(':exclude' => $exclude_user_id));
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while ($row = $STH->fetch()) {
			$result[] = array('user_id' => $row->user_id, 'login' => $row->login);
		}
	} catch (Throwable $e) {}
	return $result;
}

function pb_ensure_chat_moderators_table($pdo) {
	static $ensured = false;
	if ($ensured) {
		return;
	}
	$ensured = true;
	try {
		$pdo->exec("CREATE TABLE IF NOT EXISTS `chat_moderators` (
			`user_id` int(11) NOT NULL,
			`granted_by` int(11) NOT NULL DEFAULT 0,
			`created_at` int(11) NOT NULL DEFAULT 0,
			PRIMARY KEY (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	} catch (Throwable $e) {}
}

function pb_chat_is_moderator($pdo, $user_id) {
	if (empty($user_id)) {
		return false;
	}
	pb_ensure_chat_moderators_table($pdo);
	try {
		$STH = $pdo->prepare("SELECT `user_id` FROM `chat_moderators` WHERE `user_id` = :user_id LIMIT 1");
		$STH->execute(array(':user_id' => $user_id));
		return (bool)$STH->fetch();
	} catch (Throwable $e) {
		return false;
	}
}

function pb_chat_moderators_list($pdo) {
	pb_ensure_chat_moderators_table($pdo);
	$result = array();
	try {
		$STH = $pdo->query("SELECT `user_id` FROM `chat_moderators`");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		while ($row = $STH->fetch()) {
			$result[] = (int)$row->user_id;
		}
	} catch (Throwable $e) {}
	return $result;
}

function pb_chat_grant_moderator($pdo, $user_id, $granted_by) {
	pb_ensure_chat_moderators_table($pdo);
	try {
		$STH = $pdo->prepare("INSERT INTO `chat_moderators` (`user_id`, `granted_by`, `created_at`) VALUES (:user_id, :granted_by, :time)
			ON DUPLICATE KEY UPDATE `granted_by` = :granted_by2, `created_at` = :time2");
		$STH->execute(array(
			':user_id' => $user_id,
			':granted_by' => $granted_by,
			':time' => time(),
			':granted_by2' => $granted_by,
			':time2' => time()
		));
		return true;
	} catch (Throwable $e) {
		return false;
	}
}

function pb_chat_revoke_moderator($pdo, $user_id) {
	pb_ensure_chat_moderators_table($pdo);
	try {
		$STH = $pdo->prepare("DELETE FROM `chat_moderators` WHERE `user_id` = :user_id");
		$STH->execute(array(':user_id' => $user_id));
		return true;
	} catch (Throwable $e) {
		return false;
	}
}

function pb_chat_can_manage($pdo, $user_id, $user_rights) {
	if (is_worthy("d", $user_rights)) {
		return true;
	}
	if (function_exists('pb_can_moderate') && pb_can_moderate()) {
		return true;
	}
	return pb_chat_is_moderator($pdo, $user_id);
}

/* ==================== Блокировки в чате ==================== */

function pb_ensure_chat_bans_table($pdo) {
	static $ensured = false;
	if ($ensured) {
		return;
	}
	$ensured = true;
	try {
		$pdo->exec("CREATE TABLE IF NOT EXISTS `chat_bans` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`user_id` int(11) NOT NULL,
			`banned_by` int(11) NOT NULL DEFAULT 0,
			`reason` varchar(255) NOT NULL DEFAULT '',
			`created_at` int(11) NOT NULL DEFAULT 0,
			`expires_at` int(11) NOT NULL DEFAULT 0,
			`active` tinyint(1) NOT NULL DEFAULT 1,
			PRIMARY KEY (`id`),
			KEY `user_id` (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	} catch (Throwable $e) {}
}

function pb_chat_ban_user($pdo, $user_id, $banned_by, $reason, $expires_at) {
	pb_ensure_chat_bans_table($pdo);
	try {
		$pdo->prepare("UPDATE `chat_bans` SET `active` = 0 WHERE `user_id` = :user_id AND `active` = 1")
			->execute(array(':user_id' => $user_id));
		$STH = $pdo->prepare("INSERT INTO `chat_bans` (`user_id`, `banned_by`, `reason`, `created_at`, `expires_at`, `active`) VALUES (:user_id, :banned_by, :reason, :created_at, :expires_at, 1)");
		$STH->execute(array(
			':user_id' => $user_id,
			':banned_by' => $banned_by,
			':reason' => $reason,
			':created_at' => time(),
			':expires_at' => $expires_at
		));
		return true;
	} catch (Throwable $e) {
		return false;
	}
}

function pb_chat_unban_user($pdo, $ban_id) {
	pb_ensure_chat_bans_table($pdo);
	try {
		$STH = $pdo->prepare("UPDATE `chat_bans` SET `active` = 0 WHERE `id` = :id");
		$STH->execute(array(':id' => $ban_id));
		return true;
	} catch (Throwable $e) {
		return false;
	}
}

function pb_chat_get_ban_info($pdo, $user_id) {
	if (empty($user_id)) {
		return null;
	}
	pb_ensure_chat_bans_table($pdo);
	try {
		$STH = $pdo->prepare("SELECT `id`, `reason`, `expires_at` FROM `chat_bans` WHERE `user_id` = :user_id AND `active` = 1 ORDER BY `id` DESC LIMIT 1");
		$STH->execute(array(':user_id' => $user_id));
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			return null;
		}
		if ((int)$row['expires_at'] > 0 && (int)$row['expires_at'] < time()) {
			$pdo->prepare("UPDATE `chat_bans` SET `active` = 0 WHERE `user_id` = :user_id AND `active` = 1")
				->execute(array(':user_id' => $user_id));
			return null;
		}
		return array(
			'id' => (int)$row['id'],
			'reason' => $row['reason'],
			'expires_at' => (int)$row['expires_at']
		);
	} catch (Throwable $e) {
		return null;
	}
}

function pb_chat_is_banned($pdo, $user_id) {
	return pb_chat_get_ban_info($pdo, $user_id) !== null;
}

function pb_chat_bans_list($pdo, $active_only = true) {
	pb_ensure_chat_bans_table($pdo);
	$result = array();
	try {
		$sql = "SELECT `chat_bans`.*, `users`.`login` FROM `chat_bans` LEFT JOIN `users` ON `chat_bans`.`user_id` = `users`.`id`";
		if ($active_only) {
			$sql .= " WHERE `chat_bans`.`active` = 1";
		}
		$sql .= " ORDER BY `chat_bans`.`id` DESC LIMIT 200";
		$STH = $pdo->query($sql);
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while ($row = $STH->fetch()) {
			$result[] = $row;
		}
	} catch (Throwable $e) {}
	return $result;
}

/* ==================== Закреплённое сообщение чата ==================== */

function pb_ensure_chat_pinned_table($pdo) {
	static $ensured = false;
	if ($ensured) {
		return;
	}
	$ensured = true;
	try {
		$pdo->exec("CREATE TABLE IF NOT EXISTS `chat_pinned` (
			`id` int(11) NOT NULL DEFAULT 1,
			`message_id` int(11) NOT NULL DEFAULT 0,
			`pinned_by` int(11) NOT NULL DEFAULT 0,
			`pinned_at` int(11) NOT NULL DEFAULT 0,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	} catch (Throwable $e) {}
}

function pb_chat_pin_message($pdo, $message_id, $pinned_by) {
	pb_ensure_chat_pinned_table($pdo);
	try {
		$STH = $pdo->prepare("INSERT INTO `chat_pinned` (`id`, `message_id`, `pinned_by`, `pinned_at`) VALUES (1, :message_id, :pinned_by, :time)
			ON DUPLICATE KEY UPDATE `message_id` = :message_id2, `pinned_by` = :pinned_by2, `pinned_at` = :time2");
		$STH->execute(array(
			':message_id' => $message_id,
			':pinned_by' => $pinned_by,
			':time' => time(),
			':message_id2' => $message_id,
			':pinned_by2' => $pinned_by,
			':time2' => time()
		));
		return true;
	} catch (Throwable $e) {
		return false;
	}
}

function pb_chat_unpin_message($pdo) {
	pb_ensure_chat_pinned_table($pdo);
	try {
		$pdo->exec("DELETE FROM `chat_pinned` WHERE `id` = 1");
		return true;
	} catch (Throwable $e) {
		return false;
	}
}

function pb_chat_get_pinned($pdo) {
	pb_ensure_chat_pinned_table($pdo);
	try {
		$STH = $pdo->query("SELECT `chat_pinned`.`message_id`, `chat_pinned`.`pinned_by`, `chat`.`user_id`, `chat`.`message_text`, `chat`.`message_date`, `users`.`login`
			FROM `chat_pinned`
			INNER JOIN `chat` ON `chat`.`id` = `chat_pinned`.`message_id`
			LEFT JOIN `users` ON `users`.`id` = `chat`.`user_id`
			WHERE `chat_pinned`.`id` = 1 LIMIT 1");
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			return null;
		}
		return $row;
	} catch (Throwable $e) {
		return null;
	}
}

/* ==================== Архив удалённых сообщений чата ==================== */

function pb_ensure_chat_deleted_table($pdo) {
	static $ensured = false;
	if ($ensured) {
		return;
	}
	$ensured = true;
	try {
		$pdo->exec("CREATE TABLE IF NOT EXISTS `chat_deleted` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`original_id` int(11) NOT NULL,
			`user_id` int(11) NOT NULL,
			`message_text` mediumtext,
			`message_date` datetime NOT NULL,
			`deleted_by` int(11) NOT NULL DEFAULT 0,
			`deleted_at` int(11) NOT NULL DEFAULT 0,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	} catch (Throwable $e) {}
}

function pb_chat_archive_message($pdo, $chat_id, $deleted_by) {
	pb_ensure_chat_deleted_table($pdo);
	try {
		$STH = $pdo->prepare("SELECT `id`, `user_id`, `message_text`, `message_date` FROM `chat` WHERE `id` = :id LIMIT 1");
		$STH->execute(array(':id' => $chat_id));
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			return false;
		}
		$STH = $pdo->prepare("INSERT INTO `chat_deleted` (`original_id`, `user_id`, `message_text`, `message_date`, `deleted_by`, `deleted_at`) VALUES (:original_id, :user_id, :message_text, :message_date, :deleted_by, :deleted_at)");
		$STH->execute(array(
			':original_id' => $row['id'],
			':user_id' => $row['user_id'],
			':message_text' => $row['message_text'],
			':message_date' => $row['message_date'],
			':deleted_by' => $deleted_by,
			':deleted_at' => time()
		));
		return true;
	} catch (Throwable $e) {
		return false;
	}
}

function pb_chat_deleted_list($pdo) {
	pb_ensure_chat_deleted_table($pdo);
	$result = array();
	try {
		$sql = "SELECT `chat_deleted`.*, `u1`.`login` AS `author_login`, `u2`.`login` AS `deleted_by_login`
			FROM `chat_deleted`
			LEFT JOIN `users` `u1` ON `chat_deleted`.`user_id` = `u1`.`id`
			LEFT JOIN `users` `u2` ON `chat_deleted`.`deleted_by` = `u2`.`id`
			ORDER BY `chat_deleted`.`id` DESC LIMIT 200";
		$STH = $pdo->query($sql);
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		while ($row = $STH->fetch()) {
			$result[] = $row;
		}
	} catch (Throwable $e) {}
	return $result;
}

function pb_chat_deleted_wipe($pdo) {
	pb_ensure_chat_deleted_table($pdo);
	try {
		$pdo->exec("TRUNCATE TABLE `chat_deleted`");
		return true;
	} catch (Throwable $e) {
		return false;
	}
}

function pb_chat_clear_all($pdo, $cleared_by) {
	pb_ensure_chat_deleted_table($pdo);
	try {
		$STH = $pdo->query("SELECT `id`, `user_id`, `message_text`, `message_date` FROM `chat` ORDER BY `id` ASC");
		$STH->setFetchMode(PDO::FETCH_ASSOC);
		$insert = $pdo->prepare("INSERT INTO `chat_deleted` (`original_id`, `user_id`, `message_text`, `message_date`, `deleted_by`, `deleted_at`) VALUES (:original_id, :user_id, :message_text, :message_date, :deleted_by, :deleted_at)");
		$now = time();
		while ($row = $STH->fetch()) {
			$insert->execute(array(
				':original_id' => $row['id'],
				':user_id' => $row['user_id'],
				':message_text' => $row['message_text'],
				':message_date' => $row['message_date'],
				':deleted_by' => $cleared_by,
				':deleted_at' => $now
			));
		}
		$pdo->exec("TRUNCATE TABLE `chat`");
		return true;
	} catch (Throwable $e) {
		return false;
	}
}

/* ==================== Правила чата ==================== */

function pb_ensure_chat_rules_table($pdo) {
	static $ensured = false;
	if ($ensured) {
		return;
	}
	$ensured = true;
	try {
		$pdo->exec("CREATE TABLE IF NOT EXISTS `chat_rules` (
			`id` int(11) NOT NULL DEFAULT 1,
			`content` mediumtext,
			`updated_at` int(11) NOT NULL DEFAULT 0,
			`updated_by` int(11) NOT NULL DEFAULT 0,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	} catch (Throwable $e) {}
}

function pb_chat_rules_default() {
	return "Уважайте других участников чата, оскорбления и провокации запрещены.\n"
		. "Запрещён спам, флуд и реклама сторонних ресурсов.\n"
		. "Запрещена нецензурная лексика и разжигание конфликтов.\n"
		. "Запрещена отправка изображений и файлов, нарушающих законодательство или правила проекта.\n"
		. "Администрация вправе удалять сообщения и ограничивать доступ к чату без предупреждения.";
}

function pb_chat_rules_get($pdo) {
	pb_ensure_chat_rules_table($pdo);
	try {
		$STH = $pdo->query("SELECT `content` FROM `chat_rules` WHERE `id` = 1 LIMIT 1");
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		if ($row && $row['content'] !== null && $row['content'] !== '') {
			return $row['content'];
		}
	} catch (Throwable $e) {}
	return pb_chat_rules_default();
}

function pb_chat_rules_set($pdo, $content, $updated_by) {
	pb_ensure_chat_rules_table($pdo);
	try {
		$STH = $pdo->prepare("INSERT INTO `chat_rules` (`id`, `content`, `updated_at`, `updated_by`) VALUES (1, :content, :time, :updated_by)
			ON DUPLICATE KEY UPDATE `content` = :content2, `updated_at` = :time2, `updated_by` = :updated_by2");
		$STH->execute(array(
			':content' => $content,
			':time' => time(),
			':updated_by' => $updated_by,
			':content2' => $content,
			':time2' => time(),
			':updated_by2' => $updated_by
		));
		return true;
	} catch (Throwable $e) {
		return false;
	}
}

function pb_detect_device_type($userAgent = null) {
	$ua = $userAgent !== null ? $userAgent : ($_SERVER['HTTP_USER_AGENT'] ?? '');
	if (empty($ua)) {
		return 'pc';
	}

	if (preg_match('/iPhone|iPad|iPod/i', $ua)) {
		return 'ios';
	}
	if (preg_match('/Android/i', $ua)) {
		return 'android';
	}

	return 'pc';
}

function is_auth() {
	if(isset($_SESSION['id'])) {
		return true;
	} else {
		return false;
	}
}

/*
	====================================================================
	НЕЗАВИСИМАЯ АВТОРИЗАЦИЯ В АДМИНЦЕНТР (PBGame CMS)
	--------------------------------------------------------------------
	Вход в админку теперь отдельный и НЕ связан с сессией пользователя
	сайта. На /admin показывается своя форма входа; логин/пароль сверяются
	с учётной записью админа (по умолчанию — пользователь ID 1, «Создатель»).
	При успехе создаётся отдельная админ-сессия (ключи admin_*), при этом
	пользовательская сессия сайта НЕ создаётся.

	Быть залогиненным на сайте не нужно и не влияет на доступ в админку.
	====================================================================
*/

// Сколько живёт админ-сессия без активности (сек). 3 часа.
function pb_admin_session_ttl() {
	return 3 * 60 * 60;
}

// Стабильный отпечаток админ-сессии, привязанный к браузеру (+ IP при ip_protect).
function pb_admin_session_fingerprint() {
	global $conf, $salt;

	// Соль берём из config (в некоторых контекстах глобальная $salt может быть не задана).
	$s = '';
	if (isset($conf->salt) && $conf->salt !== '') {
		$s = (string)$conf->salt;
	} elseif (isset($salt)) {
		$s = (string)$salt;
	}

	$browser = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : 'undefined';

	// Отпечаток привязан к браузеру и соли. IP НЕ используем: за прокси/CDN он
	// «плавает» между запросами и ломал бы сессию (вход -> сразу разлогин).
	return hash('sha256', 'pbadmin|' . $s . '|' . md5($browser));
}

// Создать/продлить админ-сессию для указанного пользователя-админа.
function pb_admin_session_start($userId, $rights) {
	$now = time();
	$_SESSION['admin']         = 1;
	$_SESSION['admin_uid']     = (int)$userId;
	$_SESSION['admin_rights']  = (string)$rights;
	$_SESSION['admin_cache']   = pb_admin_session_fingerprint();
	$_SESSION['admin_started'] = $now;
	$_SESSION['admin_until']   = $now + pb_admin_session_ttl();
	$_SESSION['rights']        = (string)$rights; // чтобы is_worthy() работал в панели

	$adminLogin = (string)$userId;
	if (function_exists('pdo')) {
		try {
			$loginRow = pdo()->prepare('SELECT `login` FROM `users` WHERE `id`=:id LIMIT 1');
			$loginRow->execute([':id' => (int)$userId]);
			$foundLogin = $loginRow->fetchColumn();
			if ($foundLogin !== false && $foundLogin !== '') {
				$adminLogin = (string)$foundLogin;
			}
		} catch (Throwable $e) {}
	}
	$_SESSION['admin_login'] = $adminLogin;

	if (function_exists('pb_admin_login_log_add') && function_exists('pdo')) {
		try {
			pb_admin_login_log_add(pdo(), (int)$userId, $adminLogin);
		} catch (Throwable $e) {
			if (function_exists('write_log')) {
				write_log('[AdminLoginLog] ' . $e->getMessage());
			}
		}
	}

	if (class_exists('TelegramNotifier')) {
		try {
			TelegramNotifier::notifyAdminLogin($adminLogin, function_exists('get_ip') ? get_ip() : '');
		} catch (Throwable $e) {
			if (function_exists('write_log')) {
				write_log('Telegram admin login notify error: ' . $e->getMessage());
			}
		}
	}
}

// Полностью завершить админ-сессию (обычную или временную master).
function pb_admin_session_end() {
	unset(
		$_SESSION['admin'], $_SESSION['admin_uid'], $_SESSION['admin_rights'],
		$_SESSION['admin_cache'], $_SESSION['admin_started'], $_SESSION['admin_until'],
		$_SESSION['admin_login'], $_SESSION['dev_mode'],
		$_SESSION['admin_master'], $_SESSION['admin_master_id'], $_SESSION['admin_master_token'],
		$_SESSION['admin_master_pages'], $_SESSION['admin_master_hard_until']
	);
	// Если пользователь при этом не залогинен на сайте — снять и rights.
	if (empty($_SESSION['id'])) {
		unset($_SESSION['rights']);
	}
}

// Проверить и «оживить» админ-сессию на текущем запросе. Возвращает bool.
// Если сессия временная (master, см. «Удалённый доступ» ниже) — делегирует
// её собственной проверке с более коротким TTL и сверкой белого списка страниц.
function pb_admin_session_check() {
	if (!empty($_SESSION['admin_master'])) {
		return function_exists('pb_admin_master_session_check') ? pb_admin_master_session_check() : false;
	}

	if (empty($_SESSION['admin']) || empty($_SESSION['admin_cache']) || empty($_SESSION['admin_until'])) {
		return false;
	}

	$now = time();
	if ((int)$_SESSION['admin_until'] <= $now) {
		pb_admin_session_end();
		return false;
	}

	if (!hash_equals(pb_admin_session_fingerprint(), (string)$_SESSION['admin_cache'])) {
		pb_admin_session_end();
		return false;
	}

	// Продлеваем окно активности и восстанавливаем rights для is_worthy().
	$_SESSION['admin_until'] = $now + pb_admin_session_ttl();
	if (isset($_SESSION['admin_rights'])) {
		$_SESSION['rights'] = (string)$_SESSION['admin_rights'];
	}
	return true;
}

/*
	Аутентификация админа по логину/паролю учётной записи (без входа на сайт).
	Требует: аккаунт существует, активен, состоит в группе с правом 'h'.
	Возвращает ['status'=>bool, 'message'=>string].
*/
function pb_admin_authenticate($pdo, $login, $password) {
	global $conf, $users_groups;

	$login = trim((string)$login);
	$password = (string)$password;

	if ($login === '' || $password === '') {
		return ['status' => false, 'message' => 'Введите логин и пароль администратора.'];
	}

	$salt = isset($conf->salt) ? $conf->salt : (isset($GLOBALS['salt']) ? $GLOBALS['salt'] : '');
	$encoded = null;
	if (class_exists('Users')) {
		$U = new Users($pdo);
		$encoded = $U->convert_password($password, $salt);
	}

	$STH = $pdo->prepare('SELECT `id`, `login`, `password`, `rights`, `active`, `dell` FROM `users` WHERE (`login`=:l OR `email`=:l) LIMIT 1');
	$STH->execute([':l' => $login]);
	$user = $STH->fetch(PDO::FETCH_OBJ);

	if (empty($user->id) || $encoded === null || !hash_equals((string)$user->password, (string)$encoded)) {
		return ['status' => false, 'message' => 'Неверный логин или пароль администратора.'];
	}
	if ((int)$user->active !== 1 || (int)$user->dell === 1) {
		return ['status' => false, 'message' => 'Учётная запись неактивна.'];
	}

	// Проверяем право 'h' у группы пользователя (доступ в Админцентр).
	$groups = is_array($users_groups) ? $users_groups : get_groups($pdo);
	$groupRights = isset($groups[$user->rights]['rights']) ? (string)$groups[$user->rights]['rights'] : '';
	if (stripos($groupRights, 'h') === false) {
		return ['status' => false, 'message' => 'У этой учётной записи нет доступа в Админцентр.'];
	}

	pb_admin_session_start($user->id, $groupRights);

	if (function_exists('write_log')) {
		write_log('Вход в Админцентр (независимый): ID ' . (int)$user->id . ' login=' . $user->login);
	}
	return ['status' => true, 'message' => 'Добро пожаловать в Админцентр.'];
}

/*
	is_admin(): доступ в админку даёт ТОЛЬКО независимая админ-сессия.
	Даже если user1 залогинен на сайте — в админку он всё равно должен войти
	отдельно (через /admin, логин+пароль). Сессия сайта на это не влияет.
*/
function is_admin() {
	return pb_admin_session_check();
}

/*
	pb_can_moderate(): право выполнять админ-действия на ПУБЛИЧНОЙ части
	(верификация из профиля, часть редактора пользователя, кнопка «Админ-центр»
	в мини-профиле и т.п.). В отличие от is_admin(), НЕ требует отдельного входа
	в /admin — достаточно быть залогиненным на сайте пользователем, чья группа
	имеет право доступа в Админцентр (флаг 'h'). Саму панель /admin это не
	открывает — туда по-прежнему только через независимую админ-сессию.
	$flag — можно уточнить требуемый флаг группы (по умолчанию 'h').
*/
function pb_can_moderate($flag = 'h') {
	if(pb_admin_session_check()) {
		return true;
	}
	if(is_auth() && is_worthy($flag)) {
		return true;
	}
	return false;
}

/*
	====================================================================
	ВРЕМЕННЫЙ ОГРАНИЧЕННЫЙ ДОСТУП В АДМИНКУ («Удалённый доступ» / master)
	--------------------------------------------------------------------
	Отдельная, полностью независимая от учёток users сущность: владелец
	создаёт ссылку-токен со сроком действия и списком разрешённых страниц
	/admin/* (по их page.name). Переход по ссылке требует подтверждения,
	после чего создаётся master-сессия — это тоже валидная admin-сессия
	(is_admin() возвращает true), но с доп. флагом admin_master=1 и белым
	списком страниц, который сверяется в engine.php при каждом запросе.
	====================================================================
*/

function pb_admin_master_ensure_schema($pdo = null) {
	static $done = false;
	if($done || empty($pdo)) {
		return;
	}
	$done = true;

	try {
		$pdo->exec("CREATE TABLE IF NOT EXISTS `admin_master_access` (
			`id` int NOT NULL AUTO_INCREMENT,
			`token` varchar(64) NOT NULL,
			`label` varchar(120) NOT NULL DEFAULT '',
			`pages` text NOT NULL,
			`created_by` int NOT NULL DEFAULT '0',
			`created_at` datetime NOT NULL,
			`expires_at` datetime NOT NULL,
			`active` tinyint(1) NOT NULL DEFAULT '1',
			`last_used_at` datetime NULL DEFAULT NULL,
			`last_ip` varchar(64) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`),
			UNIQUE KEY `token` (`token`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	} catch (Throwable $e) {
		if(function_exists('write_log')) {
			write_log('pb_admin_master_ensure_schema: ' . $e->getMessage());
		}
	}
}

// Сколько живёт master-сессия без активности (сек) — короче обычной, 45 минут.
function pb_admin_master_session_ttl() {
	return 45 * 60;
}

function pb_admin_master_generate_token() {
	return bin2hex(random_bytes(24));
}

function pb_admin_master_create($pdo, $label, array $pages, $minutes) {
	pb_admin_master_ensure_schema($pdo);

	$label = trim((string) $label);
	$pages = array_values(array_unique(array_filter(array_map('trim', $pages))));
	$minutes = (int) $minutes;

	if(empty($pages)) {
		return ['status' => false, 'message' => 'Выберите хотя бы один раздел админки.'];
	}
	if($minutes <= 0) {
		$minutes = 60;
	}
	if($minutes > 43200) { // 30 дней максимум
		$minutes = 43200;
	}

	$token = pb_admin_master_generate_token();
	$now = date('Y-m-d H:i:s');
	$expires = date('Y-m-d H:i:s', time() + $minutes * 60);

	$STH = $pdo->prepare("INSERT INTO `admin_master_access`
		(`token`, `label`, `pages`, `created_by`, `created_at`, `expires_at`, `active`)
		VALUES (:token, :label, :pages, :created_by, :created_at, :expires_at, '1')");
	$STH->execute([
		':token' => $token,
		':label' => $label !== '' ? $label : 'Без названия',
		':pages' => implode(',', $pages),
		':created_by' => (int) ($_SESSION['admin_uid'] ?? 0),
		':created_at' => $now,
		':expires_at' => $expires,
	]);

	return ['status' => true, 'token' => $token, 'id' => (int) $pdo->lastInsertId()];
}

function pb_admin_master_list($pdo) {
	pb_admin_master_ensure_schema($pdo);
	$STH = $pdo->query("SELECT * FROM `admin_master_access` ORDER BY `id` DESC");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	return $STH->fetchAll();
}

function pb_admin_master_revoke($pdo, $id) {
	pb_admin_master_ensure_schema($pdo);
	$STH = $pdo->prepare("UPDATE `admin_master_access` SET `active`='0' WHERE `id`=:id LIMIT 1");
	return $STH->execute([':id' => (int) $id]);
}

function pb_admin_master_delete($pdo, $id) {
	pb_admin_master_ensure_schema($pdo);
	$STH = $pdo->prepare("DELETE FROM `admin_master_access` WHERE `id`=:id LIMIT 1");
	return $STH->execute([':id' => (int) $id]);
}

function pb_admin_master_find_by_token($pdo, $token) {
	pb_admin_master_ensure_schema($pdo);
	$token = trim((string) $token);
	if($token === '') {
		return null;
	}
	$STH = $pdo->prepare("SELECT * FROM `admin_master_access` WHERE `token`=:token LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':token' => $token]);
	$row = $STH->fetch();
	return $row ?: null;
}

// Живая ли ещё запись доступа (активна, не отозвана, не истекла по expires_at).
function pb_admin_master_record_is_live($record) {
	if(empty($record)) {
		return false;
	}
	if((int) $record->active !== 1) {
		return false;
	}
	if(strtotime($record->expires_at) <= time()) {
		return false;
	}
	return true;
}

function pb_admin_master_session_start($record) {
	$now = time();
	$ttl = pb_admin_master_session_ttl();
	$hardExpiresAt = strtotime($record->expires_at);
	$slidingUntil = min($now + $ttl, $hardExpiresAt);

	$_SESSION['admin']              = 1;
	$_SESSION['admin_master']       = 1;
	$_SESSION['admin_master_id']    = (int) $record->id;
	$_SESSION['admin_master_token'] = (string) $record->token;
	$_SESSION['admin_master_pages'] = (string) $record->pages;
	$_SESSION['admin_master_hard_until'] = $hardExpiresAt;
	$_SESSION['admin_cache']        = pb_admin_session_fingerprint();
	$_SESSION['admin_started']      = $now;
	$_SESSION['admin_until']        = $slidingUntil;
	$_SESSION['admin_uid']          = 0;
	$_SESSION['admin_rights']       = '';

	if(function_exists('pdo')) {
		try {
			$STH = pdo()->prepare("UPDATE `admin_master_access` SET `last_used_at`=:now, `last_ip`=:ip WHERE `id`=:id LIMIT 1");
			$STH->execute([
				':now' => date('Y-m-d H:i:s'),
				':ip' => function_exists('get_ip') ? (string) get_ip() : '',
				':id' => (int) $record->id,
			]);
		} catch (Throwable $e) {}
	}

	if(function_exists('write_log')) {
		write_log('Вход по временному доступу «Удалённый доступ»: id=' . (int) $record->id . ' label=' . $record->label);
	}
}

// Проверить и «оживить» master-сессию. При провале — полностью её завершает.
function pb_admin_master_session_check() {
	if(empty($_SESSION['admin_master'])) {
		return false;
	}
	if(empty($_SESSION['admin']) || empty($_SESSION['admin_cache']) || empty($_SESSION['admin_until'])) {
		pb_admin_session_end();
		return false;
	}

	$now = time();
	if((int) $_SESSION['admin_until'] <= $now) {
		pb_admin_session_end();
		return false;
	}
	if(!empty($_SESSION['admin_master_hard_until']) && (int) $_SESSION['admin_master_hard_until'] <= $now) {
		pb_admin_session_end();
		return false;
	}
	if(!hash_equals(pb_admin_session_fingerprint(), (string) $_SESSION['admin_cache'])) {
		pb_admin_session_end();
		return false;
	}

	// Доступ мог быть отозван/удалён владельцем — сверяем с БД.
	if(function_exists('pdo') && !empty($_SESSION['admin_master_id'])) {
		$record = pb_admin_master_find_by_token(pdo(), $_SESSION['admin_master_token'] ?? '');
		if(!pb_admin_master_record_is_live($record)) {
			pb_admin_session_end();
			return false;
		}
		$_SESSION['admin_master_pages'] = (string) $record->pages;
	}

	$ttl = pb_admin_master_session_ttl();
	$hardUntil = (int) ($_SESSION['admin_master_hard_until'] ?? 0);
	$_SESSION['admin_until'] = $hardUntil > 0 ? min($now + $ttl, $hardUntil) : ($now + $ttl);

	return true;
}

function pb_admin_master_allowed_pages() {
	if(empty($_SESSION['admin_master']) || empty($_SESSION['admin_master_pages'])) {
		return [];
	}
	return array_values(array_filter(array_map('trim', explode(',', (string) $_SESSION['admin_master_pages']))));
}

// Разрешена ли текущей (если это master-сессия) сессии данная страница по её page.name.
// Для обычной владельческой сессии — всегда true (полный доступ, как раньше).
function pb_admin_master_page_allowed($pageName) {
	if(empty($_SESSION['admin_master'])) {
		return true;
	}
	return in_array((string) $pageName, pb_admin_master_allowed_pages(), true);
}

function pb_admin_master_is_active_session() {
	return !empty($_SESSION['admin_master']);
}

// URL первой доступной master-сессии страницы (без ведущего /), для редиректа
// после входа — чтобы не упираться в /admin/dev_center, которого может не быть в списке.
// Порядок — как выбрано владельцем при создании доступа (первая существующая и активная).
function pb_admin_master_first_allowed_url($pdo) {
	$pages = pb_admin_master_allowed_pages();
	if(empty($pages)) {
		return '';
	}

	try {
		$STH = $pdo->prepare("SELECT `url` FROM `pages` WHERE `name`=:name AND `active`='1' LIMIT 1");
		foreach($pages as $pageName) {
			$STH->execute([':name' => $pageName]);
			$url = $STH->fetchColumn();
			if($url !== false) {
				return ltrim((string) $url, '/');
			}
		}
	} catch (Throwable $e) {}

	return '';
}

/*
	Режим технических работ (config.off == 1).
	Шаблон main.tpl вызывает эту функцию, чтобы решить, показывать ли заглушку
	off_site.tpl. Функция возвращает true для тех, кого пропускаем МИМО заглушки
	(видят обычный сайт) — это администраторы. Все остальные посетители видят
	страницу техработ.

	Раньше эта функция отсутствовала в движке, из-за чего при включении техработ
	main.tpl падал с "Call to undefined function" → Internal Server Error.
*/
if(!function_exists('pb_site_off_bypass_allowed')) {
	function pb_site_off_bypass_allowed() {
		return is_admin();
	}
}

function is_admin_id($id = 0) {
	global $main_admins;

	$admins = is_array($main_admins) ? $main_admins : [];

	if($id === 0) {
		if(!is_auth()) {
			return false;
		}

		if(in_array($_SESSION['id'], $admins)) {
			return true;
		} else {
			return false;
		}
	} else {
		if(in_array($id, $admins)) {
			return true;
		} else {
			return false;
		}
	}
}

function is_worthy($access, $group = 0) {
	global $users_groups;

	if($group == 0 && array_key_exists('rights', $_SESSION)) {
		$group = $_SESSION['rights'];
	}

	if(strripos($users_groups[$group]['rights'], $access) !== false) {
		return true;
	} else {
		return false;
	}
}

function is_worthy_specifically($access, $refinement, $group = 0) {
	global $users_groups;

	if($group == 0 && array_key_exists('rights', $_SESSION)) {
		$group = $_SESSION['rights'];
	}

	if(preg_match('/'.$access.'([:0-9]*)[a-z]?/is', $users_groups[$group]['rights'], $matches)) {
		if($matches[1] == '') {
			return true;
		} else {
			$rights = explode(":", $matches[1]);
			if(in_array($refinement, $rights)) {
				return true;
			}
		}
	}

	return false;
}

function get_specifically_worthy($access, $group = 0) {
	global $users_groups;

	if($group == 0 && array_key_exists('rights', $_SESSION)) {
		$group = $_SESSION['rights'];
	}

	if(preg_match('/'.$access.'([:0-9]*)[a-z]?/is', $users_groups[$group]['rights'], $matches)) {
		if($matches[1] == '') {
			return true;
		} else {
			$rights = explode(":", $matches[1]);
			if(!is_array($rights)) {
				$rights = array(0 => $rights);
			}
			return $rights;
		}
	}

	return false;
}

function validateCaptcha($gRecaptchaResponse) {
	require_once __DIR__ . '/classes/ReCaptcha/loader.php';

	if(configs()->captcha == 1) {
		global $host;

		$recaptcha = new \ReCaptcha\ReCaptcha(configs()->captcha_secret);

		$resp = $recaptcha->setExpectedHostname($host)
			->verify($gRecaptchaResponse);
		if ($resp->isSuccess()) {
			return true;
		} else {
			return false;
		}
	}

	return true;
}

function isOnBlackList($pdo, $who, $whom)
{
	$STH = $pdo->prepare(
		"SELECT id FROM users__black_list WHERE whom=:whom AND who=:who LIMIT 1"
	);
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':who' => $who, ':whom' => $whom]);
	$row = $STH->fetch();

	return empty($row->id) ? false : $row->id;
}

function isOnMyBlacklist($pdo, $userId)
{
	$result = false;

	if(is_auth()) {
		$result = isOnBlackList($pdo, $_SESSION['id'], $userId);
	}

	return $result;
}

function isOnHisBlacklist($pdo, $userId)
{
	$result = false;

	if(is_auth()) {
		$result = isOnBlackList($pdo, $userId, $_SESSION['id']);
	}

	return $result;
}


function isSomeKeyInArrayExists($keys, $array) {
	if(!is_array($keys)) {
		$keys = [$keys];
	}

	foreach($keys as $key) {
		if(array_key_exists($key, $array)) {
			return $key;
		}
	}

	return false;
}

if(!function_exists('array_column')) {
	function array_column(array $input, $columnKey, $indexKey = null)
	{
		$array = [];
		foreach($input as $value) {
			if(!array_key_exists($columnKey, $value)) {
				return false;
			}
			if(is_null($indexKey)) {
				$array[] = $value[$columnKey];
			} else {
				if(!array_key_exists($indexKey, $value)) {
					return false;
				}
				if(!is_scalar($value[$indexKey])) {
					return false;
				}
				$array[$value[$indexKey]] = $value[$columnKey];
			}
		}
		return $array;
	}
}

function pb_update_server_display_name($name) {
	$name = trim((string)$name);

	if($name === 'Основной сервер' || mb_strtolower($name, 'UTF-8') === 'основной сервер' || $name === 'PBGame UA' || $name === 'PBG FI') {
		return 'PBG DE';
	}

	return $name;
}

function get_update_servers($pdo, $server_id = null) {
	$sth = $pdo->query("SELECT * FROM `config__updates` WHERE 1");
	
	$servers = "";
	
	if($sth->rowCount()) {
		global $conf;
		
		if(isset($server_id))
			$conf->update_server = $server_id;
		
		$sth->setFetchMode(PDO::FETCH_OBJ);
		$servers = "<option value='0' " . (($conf->update_server <= 0) ? "selected" : "") . " disabled>- выбрать -</option>";
		
		while($row = $sth->fetch()) {
			$server_id_value = (int)$row->id;
			$server_name = htmlspecialchars(pb_update_server_display_name($row->name), ENT_QUOTES, 'UTF-8');
			$servers .= "<option value='{$server_id_value}' ".(($conf->update_server == $server_id_value) ? "selected" : "").">{$server_name}</option>";
		}
	}
	else {
		$servers = "<option value='0' selected disabled>- нет доступных -</option>";
	}
	
	return $servers;
}

function set_update_server($pdo, $server_id) {
	return $pdo->query("UPDATE `config` SET `update_server`='{$server_id}' WHERE 1");
}

function check_update_server($pdo, $server_id) {
	$sth = $pdo->query("SELECT * FROM `config__updates` WHERE `id`='{$server_id}'");
	$sth->setFetchMode(PDO::FETCH_OBJ);
	$row = $sth->fetch();
	
	return is_valid_site("https://" . $row->url);
}

function is_valid_site($domain = "google.com") {
	if(!filter_var($domain, FILTER_VALIDATE_URL)):
		return false;
	endif;
	
	$curlInit = curl_init($domain);
	curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curlInit, CURLOPT_HEADER, true);
	curl_setopt($curlInit, CURLOPT_NOBODY, true);
	curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($curlInit);
	curl_close($curlInit);
	
	if($response):
		return true;
	endif;
	
	return false;
}

function get_update_url($pdo) {
	global $conf;
	
	$sth = $pdo->query("SELECT * FROM `config__updates` WHERE `id`='{$conf->update_server}'");
	$sth->setFetchMode(PDO::FETCH_OBJ);
	$row = $sth->fetch();
	
	return $row->url;
}

function check_update_version($pdo, $version) {
	$remoteVersions = curl_get_process(['website' => "https://". get_update_url($pdo) ."/api?type=updates",'data' => '&product=gamecms']);
	$remoteVersions = json_decode(gzdecode($remoteVersions), true);
	
	for($i = 0; $i < sizeof($remoteVersions); $i++) {
		if($version == $remoteVersions[$i]['version']) {
			$index = $i;
			break;
		}
	}
	
	if(isset($remoteVersions[$index + 1]['version']) && $remoteVersions[$index]['version'] < $remoteVersions[$index + 1]['version']) {
		return ['status' => '0', 'versions' => $remoteVersions, 'index' => $index];
	}
	
	return ['status' => '1', 'versions' => $remoteVersions, 'index' => $index];
}

/*
	new
*/
function curl($site, $postfiels) {
	$ch = curl_init($site);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postfiels);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 8);
	$result = curl_exec($ch);
	curl_close($ch);

	return $result;
}
/*
	new
*/

function curl_get_process($data = []) {
	$ch = curl_init($data['website']);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data['data']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$result = curl_exec($ch);
	curl_close($ch);
	
	return $result;
}

function download_file($data = []) {
	/*
		temp - дирекция, в которую будет загружаться файл.
		url - ссылка на файл, который нужно загрузить.
		file - название файла с расширением.
	*/
	if(isset($data['temp'])) {
		if(!file_exists("{$_SERVER['DOCUMENT_ROOT']}/{$data['temp']}")) {
			mkdir("{$_SERVER['DOCUMENT_ROOT']}/{$data['temp']}");
		}
	}
	
	$cInit = curl_init($data['url']);
	$fOpen = fopen("{$_SERVER['DOCUMENT_ROOT']}/{$data['temp']}/{$data['file']}", "wb");
	curl_setopt($cInit, CURLOPT_FILE, $fOpen);
	curl_setopt($cInit, CURLOPT_HEADER, 0);
	curl_exec($cInit);
	curl_close($cInit);
	
	/*
		status - отправляется статус закрытия файла (сохранение).
		file - отправляется дирекция с конечным файлом.
	*/
	return ['status' => fclose($fOpen), 'file' => "{$_SERVER['DOCUMENT_ROOT']}/{$data['temp']}/{$data['file']}"];
}

function importSqlFile($pdo, $sqlFile, $data = []) {
    $logDir = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__), '/\\') . '/logs';
    if(!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/install_errors.txt';

    $logInstallError = function($message) use ($logFile) {
        @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $message . "\r\n", FILE_APPEND | LOCK_EX);
    };

    try {
        if(!is_file($sqlFile) || !is_readable($sqlFile)) {
            $logInstallError('SQL file is not readable: ' . $sqlFile);
            return false;
        }

        try {
            $pdo->setAttribute(\PDO::MYSQL_ATTR_LOCAL_INFILE, true);
        } catch (\Throwable $e) {}

        $sql = file_get_contents($sqlFile);
        if($sql === false || trim($sql) === '') {
            $logInstallError('SQL file is empty: ' . $sqlFile);
            return false;
        }

        $replaceData = [
            'project' => ($data['project'] ?? ''),
            'salt' => ($data['salt'] ?? ''),
            'code' => ($data['code'] ?? ''),
            'host' => ($data['host'] ?? 'localhost'),
            'admin_gate_login' => ($data['admin_gate_login'] ?? ''),
            'admin_gate_password_hash' => ($data['admin_gate_password_hash'] ?? '')
        ];

        foreach($replaceData as $replaceKey => $replaceValue) {
            $replaceValue = (string)$replaceValue;
            $sql = str_replace("'<<" . $replaceKey . ">>'", $pdo->quote($replaceValue), $sql);
            $sql = str_replace('<<' . $replaceKey . '>>', $replaceValue, $sql);
        }

        $statements = [];
        $statement = '';
        $length = strlen($sql);
        $quote = null;
        $escaped = false;
        $lineComment = false;
        $blockComment = false;

        for($i = 0; $i < $length; $i++) {
            $ch = $sql[$i];
            $next = ($i + 1 < $length) ? $sql[$i + 1] : '';

            if($lineComment) {
                if($ch === "\n") {
                    $lineComment = false;
                    $statement .= $ch;
                }
                continue;
            }

            if($blockComment) {
                if($ch === '*' && $next === '/') {
                    $blockComment = false;
                    $i++;
                }
                continue;
            }

            if($quote === null) {
                if($ch === '-' && $next === '-') {
                    $prev = ($i > 0) ? $sql[$i - 1] : "\n";
                    $after = ($i + 2 < $length) ? $sql[$i + 2] : '';
                    if(($prev === "\n" || $prev === "\r") && ($after === ' ' || $after === "\t" || $after === "\r" || $after === "\n")) {
                        $lineComment = true;
                        $i++;
                        continue;
                    }
                }
                if($ch === '#' && ($i === 0 || $sql[$i - 1] === "\n" || $sql[$i - 1] === "\r")) {
                    $lineComment = true;
                    continue;
                }
                if($ch === '/' && $next === '*') {
                    $blockComment = true;
                    $i++;
                    continue;
                }
                if($ch === "'" || $ch === '"' || $ch === '`') {
                    $quote = $ch;
                    $escaped = false;
                    $statement .= $ch;
                    continue;
                }
                if($ch === ';') {
                    $trimmed = trim($statement);
                    if($trimmed !== '') {
                        $statements[] = $trimmed;
                    }
                    $statement = '';
                    continue;
                }
                $statement .= $ch;
                continue;
            }

            $statement .= $ch;

            if($quote === '`') {
                if($ch === '`') {
                    $quote = null;
                }
                continue;
            }

            if($escaped) {
                $escaped = false;
                continue;
            }
            if($ch === '\\') {
                $escaped = true;
                continue;
            }
            if($ch === $quote) {
                $quote = null;
            }
        }

        $trimmed = trim($statement);
        if($trimmed !== '') {
            $statements[] = $trimmed;
        }

        foreach($statements as $index => $query) {
            $query = trim($query);
            if($query === '') {
                continue;
            }
            try {
                $pdo->exec($query);
            } catch (\PDOException $e) {
                $preview = preg_replace('/\s+/', ' ', $query);
                if(strlen($preview) > 900) {
                    $preview = substr($preview, 0, 900) . '...';
                }
                $logInstallError('SQL import failed at statement #' . ($index + 1) . ': ' . $e->getMessage() . ' | Query: ' . $preview);
                return false;
            }
        }
    } catch (\Throwable $e) {
        $logInstallError('SQL import exception: ' . $e->getMessage());
        return false;
    }

    return true;
}

function isNeedHidePlayerId()
{
	global $conf;

	if($conf->hide_players_id == 1 || $conf->hide_players_id == 3) {
		return true;
	} else {
		return false;
	}
}

function isNeedHideAdminId()
{
	global $conf;

	if($conf->hide_players_id == 1 || $conf->hide_players_id == 2) {
		return true;
	} else {
		return false;
	}
}

function hidePlayerId($id)
{
	global $messages;

	if(
		(
			is_worthy('i')
			|| is_worthy('k')
			|| is_worthy('s')
			|| is_worthy('j')
		) || !SteamIDOperations::ValidateSteamID($id)
	) {
		return $id;
	} else {
		return $messages['isHidden'];
	}
}

if(
	!function_exists('random_bytes')
	|| !function_exists('random_int')
) {
	include_once __DIR__ . '/classes/Random/random.php';
}

function isStringLengthLess($string, $length)
{
	if(mb_strlen($string, 'UTF-8') < $length) {
		return true;
	} else {
		return false;
	}
}

function isStringLengthMore($string, $length)
{
	return !isStringLengthLess($string, $length);
}

function getPhpVersion()
{
	if(phpversion()) {
		$phpVersion = explode('.', phpversion());
	} else {
		$phpVersion = explode('.', PHP_VERSION);
	}

	return $phpVersion;
}

function pdo()
{
	global $pdo;

	return empty($pdo) ? new stdClass() : $pdo;
}

function configs()
{
	global $conf;

	return empty($conf) ? new stdClass() : $conf;
}

function page()
{
	global $page;

	return empty($page) ? new stdClass() : $page;
}

function tpl()
{
	global $tpl;

	if(empty($tpl)) {
		$tpl = new Template;
	}

	return $tpl;
}

function isRightToken()
{

	if(configs()->token == 1 && $_SESSION['token'] != $_POST['token']) {
		return false;
	} else {
		return true;
	}
}

function isPostRequest()
{
	if(empty($_POST) || !array_key_exists('phpaction', $_POST)) {
		return false;
	} else {
		return true;
	}
}

function token()
{
	global $token;

	return empty($token) ? '' : $token;
}

function isRightAdminToken()
{
	return isRightToken();
}

function admin_token()
{
	return token();
}

function HTMLPurifier()
{
	require_once __DIR__ . '/classes/HTMLPurifier/HTMLPurifier/Bootstrap.php';
	require_once __DIR__ . '/classes/HTMLPurifier/HTMLPurifier.autoload.php';

	$config = HTMLPurifier_Config::createDefault();
	$config->set('HTML.SafeIframe', true);
	$config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');

	$HTMLDefinition = $config->getHTMLDefinition(true);
	$HTMLDefinition->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
	$HTMLDefinition->addAttribute('iframe', 'allowfullscreen', 'Enum#allowfullscreen');

	foreach(['audio', 'video'] as $item) {
		$$item = $HTMLDefinition->addElement(
			$item,
			'Block',
			'Flow',
			'Common',
			['src*' => 'URI', 'controls' => 'CDATA']
		);

		$$item->excludes = [$item => true];
	}

	return new HTMLPurifier($config);
}

function incNotifications()
{
	include_once __DIR__ . '/notifications.php';
}

if (!function_exists('queue_after_response')) {
	function queue_after_response(callable $task)
	{
		register_shutdown_function(function () use ($task) {
			if (function_exists('fastcgi_finish_request')) {
				@fastcgi_finish_request();
			}
			try {
				$task();
			} catch (Throwable $e) {
				if (function_exists('write_log')) {
					write_log('Deferred task error: ' . $e->getMessage());
				}
			}
		});
	}
}

function dd($data)
{
	echo '<style>html, body { background: rgb(30, 30, 30); color: rgb(240, 240, 240); }</style>'
		. '<pre>'
		. var_export($data, true)
		. '</pre>';

	die;
}

function isMobile()
{
	if((new MobileDetect())->isMobile()) {
		return true;
	} else {
		return false;
	}
}

function get_user_status($id_user = null) {
	if(empty($id_user)):
		return null;
	endif;

	return pdo()->query("SELECT * FROM `users` WHERE `id`='{$id_user}'")->fetch(PDO::FETCH_OBJ)->status_message;
}

function SourceQuery() {
	global $SourceQuery;

	return isset($SourceQuery) ? $SourceQuery : null;
}

function trading() {
	global $Playground;

	return isset($Playground) ? $Playground : (new Playground(pdo(), configs()));
}

function usr($uid = null) {
	$uid = clean($uid, "int");

	if(empty($uid)):
		return null;
	endif;

	$sth = pdo()->query("SELECT * FROM `users` WHERE `id`='$uid' LIMIT 1");

	if(!$sth->rowCount()):
		return null;
	endif;

	return $sth->fetch(PDO::FETCH_OBJ);
}

function pb_visitors_ensure_schema() {
	static $ensured = false;
	if($ensured) {
		return;
	}
	$ensured = true;

	try {
		pdo()->exec("CREATE TABLE IF NOT EXISTS `users__visitors` (
			`id` int NOT NULL AUTO_INCREMENT,
			`profile_id` int NOT NULL,
			`visitor_id` int NOT NULL,
			`visited_at` datetime NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `uniq_profile_visitor` (`profile_id`, `visitor_id`),
			KEY `profile_id` (`profile_id`),
			KEY `visited_at` (`visited_at`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	} catch (Throwable $e) {
		if(function_exists('write_log')) {
			write_log('pb_visitors_ensure_schema: ' . $e->getMessage());
		}
	}
}

function pb_visitors_reset_daily() {
	$markerFile = $_SERVER['DOCUMENT_ROOT'] . '/tmp/visitors_reset_date.txt';
	$today = date('Y-m-d');

	$lastReset = is_file($markerFile) ? trim((string) @file_get_contents($markerFile)) : '';
	if($lastReset === $today) {
		return;
	}

	// Кто-то другой мог уже выполнить сброс за сегодня между чтением и
	// записью метки — берём эксклюзивную блокировку на файл, чтобы два
	// параллельных запроса не гонялись за TRUNCATE одновременно.
	$handle = @fopen($markerFile, 'c+');
	if(!$handle) {
		return;
	}

	if(!flock($handle, LOCK_EX | LOCK_NB)) {
		fclose($handle);
		return;
	}

	rewind($handle);
	$lastReset = trim((string) stream_get_contents($handle));
	if($lastReset !== $today) {
		try {
			pb_visitors_ensure_schema();
			pdo()->exec("TRUNCATE TABLE `users__visitors`");

			ftruncate($handle, 0);
			rewind($handle);
			fwrite($handle, $today);
		} catch (Throwable $e) {
			if(function_exists('write_log')) {
				write_log('pb_visitors_reset_daily: ' . $e->getMessage());
			}
		}
	}

	flock($handle, LOCK_UN);
	fclose($handle);
}

function pb_track_profile_visit($profileId, $visitorId) {
	$profileId = (int) $profileId;
	$visitorId = (int) $visitorId;

	if($profileId <= 0 || $visitorId <= 0 || $profileId === $visitorId) {
		return;
	}

	if(function_exists('pb_premium_is_incognito') && pb_premium_is_incognito($visitorId)) {
		return;
	}

	pb_visitors_ensure_schema();
	pb_visitors_reset_daily();

	try {
		$sth = pdo()->prepare("INSERT INTO `users__visitors` (`profile_id`, `visitor_id`, `visited_at`)
			VALUES (:profile_id, :visitor_id, :visited_at)
			ON DUPLICATE KEY UPDATE `visited_at` = VALUES(`visited_at`)");
		$sth->execute([
			':profile_id' => $profileId,
			':visitor_id' => $visitorId,
			':visited_at' => date('Y-m-d H:i:s'),
		]);
	} catch (Throwable $e) {
		if(function_exists('write_log')) {
			write_log('pb_track_profile_visit: ' . $e->getMessage());
		}
	}
}

function pb_get_profile_visitors($profileId, $limit = 4) {
	$profileId = (int) $profileId;
	$limit = max(1, (int) $limit);

	if($profileId <= 0) {
		return [];
	}

	pb_visitors_ensure_schema();
	pb_visitors_reset_daily();

	try {
		$sth = pdo()->prepare("SELECT v.`visitor_id`, v.`visited_at`, u.`login`, u.`avatar`, u.`rights`
			FROM `users__visitors` v
			LEFT JOIN `users` u ON u.`id` = v.`visitor_id`
			WHERE v.`profile_id` = :profile_id AND u.`id` IS NOT NULL
			ORDER BY v.`visited_at` DESC
			LIMIT " . $limit);
		$sth->execute([':profile_id' => $profileId]);
		return $sth->fetchAll(PDO::FETCH_OBJ);
	} catch (Throwable $e) {
		if(function_exists('write_log')) {
			write_log('pb_get_profile_visitors: ' . $e->getMessage());
		}
		return [];
	}
}

function convert_avatar($uid = null, $trading = true) {
	$udata = usr($uid);

	if(empty($udata)):
		return "/" . pb_user_default_avatar();
	endif;

	if(function_exists('pb_premium_profile_avatar')) {
		$premiumAvatar = pb_premium_profile_avatar($uid);
		if(!empty($premiumAvatar)) {
			return $premiumAvatar;
		}
	}

	if($trading):
		$playground = new Playground(pdo(), configs());
		$avatar = $playground->get_resource_active(2, $uid);

		if(isset($avatar)):
			return "/files/playground/$avatar";
		endif;
	endif;

	$storedAvatar = trim((string)$udata->avatar);
	if($storedAvatar === '' || $storedAvatar === 'files/avatars/no_avatar.jpg') {
		$storedAvatar = pb_user_default_avatar();
	}

	return "/" . ltrim($storedAvatar, '/');
}


if(!function_exists('pb_ensure_user_avatar_settings')) {
	function pb_ensure_user_avatar_settings($pdo = null) {
		if($pdo === null && function_exists('pdo')) {
			$pdo = pdo();
		}
		if(!$pdo instanceof PDO) {
			return;
		}
		try {
			$columns = [
				'user_default_avatar' => "ALTER TABLE `config__secondary` ADD COLUMN `user_default_avatar` varchar(255) NOT NULL DEFAULT 'files/avatars/no_avatar.jpg'",
				'user_avatar_max_mb' => "ALTER TABLE `config__secondary` ADD COLUMN `user_avatar_max_mb` int NOT NULL DEFAULT '2'",
				'user_avatar_allow_gif' => "ALTER TABLE `config__secondary` ADD COLUMN `user_avatar_allow_gif` int NOT NULL DEFAULT '2'",
				'user_avatar_locked' => "ALTER TABLE `config__secondary` ADD COLUMN `user_avatar_locked` int NOT NULL DEFAULT '0'"
			];
			foreach($columns as $column => $sql) {
				$check = $pdo->query("SHOW COLUMNS FROM `config__secondary` LIKE '" . $column . "'");
				if(!$check || !$check->fetch(PDO::FETCH_ASSOC)) {
					$pdo->exec($sql);
				}
			}
			$pdo->exec("UPDATE `config__secondary` SET `user_default_avatar`='files/avatars/no_avatar.jpg' WHERE `user_default_avatar`='' OR `user_default_avatar` IS NULL");
			$pdo->exec("UPDATE `config__secondary` SET `user_avatar_max_mb`='2' WHERE `user_avatar_max_mb` IS NULL OR `user_avatar_max_mb`<1");
			$pdo->exec("UPDATE `config__secondary` SET `user_avatar_allow_gif`='2' WHERE `user_avatar_allow_gif` IS NULL OR `user_avatar_allow_gif` NOT IN (1,2)");
			$pdo->exec("UPDATE `config__secondary` SET `user_avatar_locked`='0' WHERE `user_avatar_locked` IS NULL OR `user_avatar_locked` NOT IN (0,1)");
		} catch(Throwable $e) {
			if(function_exists('write_log')) {
				write_log('pb_ensure_user_avatar_settings: ' . $e->getMessage());
			}
		}
	}
}

if(!function_exists('pb_get_user_avatar_settings')) {
	function pb_get_user_avatar_settings($pdo = null) {
		if($pdo === null && function_exists('pdo')) {
			$pdo = pdo();
		}
		$defaults = [
			'user_default_avatar' => 'files/avatars/no_avatar.jpg',
			'user_avatar_max_mb' => 2,
			'user_avatar_allow_gif' => 2,
			'user_avatar_locked' => 0
		];
		if(!$pdo instanceof PDO) {
			return (object)$defaults;
		}
		pb_ensure_user_avatar_settings($pdo);
		try {
			$sth = $pdo->query("SELECT `user_default_avatar`, `user_avatar_max_mb`, `user_avatar_allow_gif`, `user_avatar_locked` FROM `config__secondary` LIMIT 1");
			$row = $sth ? $sth->fetch(PDO::FETCH_OBJ) : null;
			if(!$row) {
				return (object)$defaults;
			}
			$row->user_default_avatar = trim((string)$row->user_default_avatar) ?: $defaults['user_default_avatar'];
			$row->user_avatar_max_mb = max(1, (int)$row->user_avatar_max_mb);
			$row->user_avatar_allow_gif = ((int)$row->user_avatar_allow_gif === 1) ? 1 : 2;
			$row->user_avatar_locked = ((int)$row->user_avatar_locked === 1) ? 1 : 0;
			return $row;
		} catch(Throwable $e) {
			if(function_exists('write_log')) {
				write_log('pb_get_user_avatar_settings: ' . $e->getMessage());
			}
			return (object)$defaults;
		}
	}
}

if(!function_exists('pb_user_default_avatar')) {
	function pb_user_default_avatar($pdo = null) {
		$settings = pb_get_user_avatar_settings($pdo);
		$avatar = trim((string)$settings->user_default_avatar);
		if($avatar === '') {
			$avatar = 'files/avatars/no_avatar.jpg';
		}
		return ltrim($avatar, '/');
	}
}

if(!function_exists('pb_admin_warns_limit')) {
	function pb_admin_warns_limit($pdo = null) {
		if(empty($pdo) && function_exists('pdo')) {
			$pdo = pdo();
		}
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return 3;
		}

		try {
			pb_theme_ensure_schema($pdo);
			$STH = $pdo->query("SELECT `admin_warns_limit` FROM `config__secondary` LIMIT 1");
			$limit = (int) $STH->fetchColumn();
		} catch(Throwable $e) {
			return 3;
		}

		return $limit > 0 ? $limit : 3;
	}
}

if(!function_exists('pb_admin_warns_limit_set')) {
	function pb_admin_warns_limit_set($pdo, $limit) {
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return false;
		}
		pb_theme_ensure_schema($pdo);

		$limit = max(1, min(50, (int) $limit));
		$STH = $pdo->prepare("UPDATE `config__secondary` SET `admin_warns_limit`=:limit LIMIT 1");
		return $STH->execute([':limit' => $limit]);
	}
}

if(!function_exists('pb_admin_warns_buyout')) {
	/**
	 * Настройки платного снятия выговора: ['enabled'=>bool, 'price'=>float].
	 */
	function pb_admin_warns_buyout($pdo = null) {
		if(empty($pdo) && function_exists('pdo')) {
			$pdo = pdo();
		}
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return ['enabled' => false, 'price' => 0];
		}

		try {
			pb_theme_ensure_schema($pdo);
			$STH = $pdo->query("SELECT `admin_warns_buyout`, `admin_warns_price` FROM `config__secondary` LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
		} catch(Throwable $e) {
			return ['enabled' => false, 'price' => 0];
		}

		return [
			'enabled' => !empty($row) && (int) $row->admin_warns_buyout === 1,
			'price' => !empty($row) ? (float) $row->admin_warns_price : 0,
		];
	}
}

if(!function_exists('pb_admin_warns_buyout_set')) {
	function pb_admin_warns_buyout_set($pdo, $enabled, $price) {
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return false;
		}
		pb_theme_ensure_schema($pdo);

		$price = max(0, (float) $price);
		$STH = $pdo->prepare("UPDATE `config__secondary` SET `admin_warns_buyout`=:enabled, `admin_warns_price`=:price LIMIT 1");

		return $STH->execute([
			':enabled' => !empty($enabled) ? 1 : 0,
			':price' => $price,
		]);
	}
}

if(!function_exists('pb_admin_warn_buy_removal')) {
	/**
	 * Снятие выговора пользователем за баланс. Снимает самый старый активный
	 * выговор, чтобы нельзя было выборочно гасить свежие нарушения.
	 */
	function pb_admin_warn_buy_removal($pdo, $userId, $warnId = 0) {
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return ['status' => false, 'message' => 'Нет подключения к базе'];
		}
		pb_theme_ensure_schema($pdo);

		$userId = (int) $userId;
		if($userId <= 0) {
			return ['status' => false, 'message' => 'Требуется авторизация'];
		}

		$settings = pb_admin_warns_buyout($pdo);
		if(empty($settings['enabled'])) {
			return ['status' => false, 'message' => 'Снятие выговоров сейчас недоступно'];
		}

		$price = (float) $settings['price'];
		if($price <= 0) {
			return ['status' => false, 'message' => 'Цена снятия не установлена'];
		}

		$warnId = (int) $warnId;
		if($warnId > 0) {
			$STH = $pdo->prepare("SELECT * FROM `admins__warns` WHERE `id`=:id AND `user_id`=:uid AND `active`='1' LIMIT 1");
			$STH->execute([':id' => $warnId, ':uid' => $userId]);
		} else {
			$STH = $pdo->prepare("SELECT * FROM `admins__warns` WHERE `user_id`=:uid AND `active`='1' ORDER BY `created_at` ASC, `id` ASC LIMIT 1");
			$STH->execute([':uid' => $userId]);
		}
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$warn = $STH->fetch();

		if(empty($warn->id)) {
			return ['status' => false, 'message' => 'Активных выговоров нет'];
		}

		$STH = $pdo->prepare("SELECT `shilings` FROM `users` WHERE `id`=:id LIMIT 1");
		$STH->execute([':id' => $userId]);
		$balance = (float) $STH->fetchColumn();

		if($balance < $price) {
			return ['status' => false, 'message' => 'Недостаточно средств на балансе'];
		}

		try {
			$pdo->beginTransaction();

			$STH = $pdo->prepare("UPDATE `users` SET `shilings`=`shilings`-:price WHERE `id`=:id AND `shilings`>=:check LIMIT 1");
			$STH->execute([':price' => $price, ':id' => $userId, ':check' => $price]);

			if($STH->rowCount() < 1) {
				$pdo->rollBack();
				return ['status' => false, 'message' => 'Не удалось списать средства'];
			}

			$STH = $pdo->prepare("UPDATE `admins__warns` SET `active`='0', `removed_by`='Выкуплен пользователем', `removed_reason`=:reason, `removed_at`=:at WHERE `id`=:id AND `active`='1' LIMIT 1");
			$STH->execute([
				':reason' => 'Снятие выкуплено за ' . $price,
				':at' => time(),
				':id' => (int) $warn->id,
			]);

			if($STH->rowCount() < 1) {
				$pdo->rollBack();
				return ['status' => false, 'message' => 'Выговор уже снят'];
			}

			$pdo->commit();
		} catch(Throwable $e) {
			if($pdo->inTransaction()) {
				$pdo->rollBack();
			}
			write_log('[AdminWarn] buyout: ' . $e->getMessage());
			return ['status' => false, 'message' => 'Ошибка при снятии выговора'];
		}

		$left = pb_admin_warns_count_user($pdo, $userId);
		$limit = pb_admin_warns_limit($pdo);

		try {
			if(function_exists('send_noty')) {
				send_noty($pdo, 'Вы сняли выговор за ' . $price . '. Осталось активных: ' . $left . '/' . $limit, $userId, 2);
			}
		} catch(Throwable $e) {}

		write_log('Выкуплено снятие выговора #' . $warn->id . ' пользователем ' . $userId . ' за ' . $price);

		return [
			'status' => true,
			'message' => 'Выговор снят',
			'left' => $left,
			'limit' => $limit,
			'price' => $price,
		];
	}
}

if(!function_exists('pb_admin_warns_count')) {
	function pb_admin_warns_count($pdo, $adminId) {
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return 0;
		}
		pb_theme_ensure_schema($pdo);

		$STH = $pdo->prepare("SELECT COUNT(*) FROM `admins__warns` WHERE `admin_id`=:id AND `active`='1'");
		$STH->execute([':id' => (int) $adminId]);
		return (int) $STH->fetchColumn();
	}
}

if(!function_exists('pb_admin_warns_count_user')) {
	function pb_admin_warns_count_user($pdo, $userId) {
		if(empty($pdo) || !($pdo instanceof PDO) || empty($userId)) {
			return 0;
		}
		pb_theme_ensure_schema($pdo);

		$STH = $pdo->prepare("SELECT COUNT(*) FROM `admins__warns` WHERE `user_id`=:id AND `active`='1'");
		$STH->execute([':id' => (int) $userId]);
		return (int) $STH->fetchColumn();
	}
}

if(!function_exists('pb_admin_warns_list')) {
	function pb_admin_warns_list($pdo, $params = []) {
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return [];
		}
		pb_theme_ensure_schema($pdo);

		$where = [];
		$bind = [];

		if(!empty($params['admin_id'])) {
			$where[] = '`admin_id`=:admin_id';
			$bind[':admin_id'] = (int) $params['admin_id'];
		}
		if(!empty($params['user_id'])) {
			$where[] = '`user_id`=:user_id';
			$bind[':user_id'] = (int) $params['user_id'];
		}
		if(isset($params['active'])) {
			$where[] = '`active`=:active';
			$bind[':active'] = (int) $params['active'];
		}

		$sql = "SELECT * FROM `admins__warns`";
		if(!empty($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$sql .= ' ORDER BY `created_at` DESC, `id` DESC LIMIT 100';

		$STH = $pdo->prepare($sql);
		$STH->execute($bind);
		$STH->setFetchMode(PDO::FETCH_OBJ);

		return $STH->fetchAll();
	}
}

if(!function_exists('pb_admin_warn_add')) {
	/**
	 * Выдать выговор. При достижении лимита привилегия снимается автоматически.
	 * Возвращает ['status'=>bool, 'message'=>string, 'count'=>int, 'revoked'=>bool].
	 */
	function pb_admin_warn_add($pdo, $adminId, $reason, $issuedBy = '', $issuedUid = 0) {
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return ['status' => false, 'message' => 'Нет подключения к базе'];
		}
		pb_theme_ensure_schema($pdo);

		$adminId = (int) $adminId;
		$reason = trim((string) $reason);

		if($adminId <= 0) {
			return ['status' => false, 'message' => 'Не указан администратор'];
		}
		if($reason === '') {
			return ['status' => false, 'message' => 'Укажите причину выговора'];
		}

		$STH = $pdo->prepare("SELECT `id`, `name`, `user_id`, `server`, `active` FROM `admins` WHERE `id`=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $adminId]);
		$admin = $STH->fetch();

		if(empty($admin->id)) {
			return ['status' => false, 'message' => 'Администратор не найден'];
		}

		$limitBefore = pb_admin_warns_limit($pdo);
		$countBefore = pb_admin_warns_count($pdo, $adminId);

		if($countBefore >= $limitBefore) {
			return [
				'status' => false,
				'message' => 'Лимит исчерпан (' . $countBefore . '/' . $limitBefore . '). Снимите выговор или увеличьте лимит.',
				'count' => $countBefore,
				'limit' => $limitBefore,
			];
		}

		$STH = $pdo->prepare("INSERT INTO `admins__warns`
			(`admin_id`, `user_id`, `server`, `reason`, `issued_by`, `issued_uid`, `active`, `created_at`)
			VALUES (:admin_id, :user_id, :server, :reason, :issued_by, :issued_uid, '1', :created_at)");

		$ok = $STH->execute([
			':admin_id' => $adminId,
			':user_id' => (int) $admin->user_id,
			':server' => (int) $admin->server,
			':reason' => mb_substr($reason, 0, 500),
			':issued_by' => mb_substr((string) $issuedBy, 0, 120),
			':issued_uid' => (int) $issuedUid,
			':created_at' => time(),
		]);

		if(!$ok) {
			return ['status' => false, 'message' => 'Не удалось сохранить выговор'];
		}

		$count = pb_admin_warns_count($pdo, $adminId);
		$limit = pb_admin_warns_limit($pdo);
		$revoked = false;

		if($count >= $limit && (int) $admin->active === 1) {
			try {
				$pdo->prepare("UPDATE `admins` SET `active`='2' WHERE `id`=:id LIMIT 1")->execute([':id' => $adminId]);
				$revoked = true;
			} catch(Throwable $e) {
				write_log('[AdminWarn] revoke: ' . $e->getMessage());
			}
		}

		if(!empty($admin->user_id)) {
			$message = 'Вам вынесен выговор (' . $count . '/' . $limit . '). Причина: ' . mb_substr($reason, 0, 200);
			if($revoked) {
				$message .= ' Достигнут лимит выговоров — привилегия снята.';
			}
			try {
				if(function_exists('send_noty')) {
					send_noty($pdo, $message, (int) $admin->user_id, 2);
				} else {
					$NTH = $pdo->prepare("INSERT INTO `notifications` (`message`, `date`, `user_id`, `type`) VALUES (:message, :date, :user_id, :type)");
					$NTH->execute([':message' => $message, ':date' => date('Y-m-d H:i:s'), ':user_id' => (int) $admin->user_id, ':type' => 2]);
				}
			} catch(Throwable $e) {
				write_log('[AdminWarn] notify: ' . $e->getMessage());
			}
		}

		write_log('Выговор администратору ' . $admin->name . ' (' . $count . '/' . $limit . '): ' . $reason);

		return [
			'status' => true,
			'message' => $revoked
				? ('Выговор выдан (' . $count . '/' . $limit . '). Привилегия снята.')
				: ('Выговор выдан (' . $count . '/' . $limit . ')'),
			'count' => $count,
			'limit' => $limit,
			'revoked' => $revoked,
		];
	}
}

if(!function_exists('pb_admin_warn_remove')) {
	function pb_admin_warn_remove($pdo, $warnId, $removedBy = '', $removedReason = '') {
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return ['status' => false, 'message' => 'Нет подключения к базе'];
		}
		pb_theme_ensure_schema($pdo);

		$warnId = (int) $warnId;
		$STH = $pdo->prepare("SELECT * FROM `admins__warns` WHERE `id`=:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':id' => $warnId]);
		$warn = $STH->fetch();

		if(empty($warn->id)) {
			return ['status' => false, 'message' => 'Выговор не найден'];
		}
		if((int) $warn->active !== 1) {
			return ['status' => false, 'message' => 'Выговор уже снят'];
		}

		$STH = $pdo->prepare("UPDATE `admins__warns` SET `active`='0', `removed_by`=:by, `removed_reason`=:reason, `removed_at`=:at WHERE `id`=:id LIMIT 1");
		$ok = $STH->execute([
			':by' => mb_substr((string) $removedBy, 0, 120),
			':reason' => mb_substr((string) $removedReason, 0, 500),
			':at' => time(),
			':id' => $warnId,
		]);

		if(!$ok) {
			return ['status' => false, 'message' => 'Не удалось снять выговор'];
		}

		if(!empty($warn->user_id)) {
			$count = pb_admin_warns_count($pdo, (int) $warn->admin_id);
			$limit = pb_admin_warns_limit($pdo);
			$message = 'С вас снят выговор. Текущее количество: ' . $count . '/' . $limit;
			try {
				if(function_exists('send_noty')) {
					send_noty($pdo, $message, (int) $warn->user_id, 2);
				}
			} catch(Throwable $e) {}
		}

		write_log('Снят выговор #' . $warnId);

		return ['status' => true, 'message' => 'Выговор снят', 'count' => pb_admin_warns_count($pdo, (int) $warn->admin_id)];
	}
}

if(!function_exists('pb_admin_unblock_price')) {
	function pb_admin_unblock_price($pdo = null) {
		if(empty($pdo) && function_exists('pdo')) {
			$pdo = pdo();
		}
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return 100;
		}

		try {
			pb_theme_ensure_schema($pdo);
			$STH = $pdo->query("SELECT `admin_unblock_price` FROM `config__secondary` LIMIT 1");
			$price = (float) $STH->fetchColumn();
		} catch(Throwable $e) {
			return 100;
		}

		return $price >= 0 ? $price : 100;
	}
}

if(!function_exists('pb_admin_unblock_price_set')) {
	function pb_admin_unblock_price_set($pdo, $price) {
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return false;
		}
		pb_theme_ensure_schema($pdo);

		$price = max(0, (float) $price);
		$STH = $pdo->prepare("UPDATE `config__secondary` SET `admin_unblock_price`=:price LIMIT 1");
		return $STH->execute([':price' => $price]);
	}
}

if(!function_exists('pb_currency_label')) {
	function pb_currency_label() {
		try {
			$c = sys()->currency();
			return !empty($c->lang) ? (string) $c->lang : '';
		} catch(Throwable $e) {
			return '';
		}
	}
}

if(!function_exists('pb_admin_warns_clear')) {
	/**
	 * Снять все активные выговоры администратора разом.
	 */
	function pb_admin_warns_clear($pdo, $adminId, $removedBy = '', $reason = '') {
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return ['status' => false, 'message' => 'Нет подключения к базе'];
		}
		pb_theme_ensure_schema($pdo);

		$adminId = (int) $adminId;
		if($adminId <= 0) {
			return ['status' => false, 'message' => 'Не указан администратор'];
		}

		$before = pb_admin_warns_count($pdo, $adminId);
		if($before === 0) {
			return ['status' => false, 'message' => 'Активных выговоров нет'];
		}

		$STH = $pdo->prepare("UPDATE `admins__warns` SET `active`='0', `removed_by`=:by, `removed_reason`=:reason, `removed_at`=:at WHERE `admin_id`=:id AND `active`='1'");
		$ok = $STH->execute([
			':by' => mb_substr((string) $removedBy, 0, 120),
			':reason' => mb_substr($reason !== '' ? (string) $reason : 'Массовая очистка выговоров', 0, 500),
			':at' => time(),
			':id' => $adminId,
		]);

		if(!$ok) {
			return ['status' => false, 'message' => 'Не удалось очистить выговоры'];
		}

		$STH = $pdo->prepare("SELECT `user_id` FROM `admins` WHERE `id`=:id LIMIT 1");
		$STH->execute([':id' => $adminId]);
		$userId = (int) $STH->fetchColumn();

		if($userId > 0) {
			try {
				if(function_exists('send_noty')) {
					send_noty($pdo, 'Все ваши выговоры сняты (' . $before . ' шт.).', $userId, 2);
				}
			} catch(Throwable $e) {}
		}

		write_log('Очищены выговоры администратора #' . $adminId . ' (' . $before . ' шт.)');

		return ['status' => true, 'message' => 'Снято выговоров: ' . $before, 'cleared' => $before];
	}
}

if(!function_exists('pb_admin_warns_purge')) {
	/**
	 * Полностью удалить записи выговоров из базы (без следа в истории).
	 * $adminId — по записи админа, $userId — по пользователю сайта.
	 */
	function pb_admin_warns_purge($pdo, $adminId = 0, $userId = 0) {
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return ['status' => false, 'message' => 'Нет подключения к базе'];
		}
		pb_theme_ensure_schema($pdo);

		$adminId = (int) $adminId;
		$userId = (int) $userId;

		if($adminId <= 0 && $userId <= 0) {
			return ['status' => false, 'message' => 'Не указан администратор'];
		}

		if($adminId > 0) {
			$STH = $pdo->prepare("SELECT COUNT(*) FROM `admins__warns` WHERE `admin_id`=:id");
			$STH->execute([':id' => $adminId]);
		} else {
			$STH = $pdo->prepare("SELECT COUNT(*) FROM `admins__warns` WHERE `user_id`=:id");
			$STH->execute([':id' => $userId]);
		}
		$total = (int) $STH->fetchColumn();

		if($total === 0) {
			return ['status' => false, 'message' => 'Записей о выговорах нет'];
		}

		try {
			if($adminId > 0) {
				$STH = $pdo->prepare("DELETE FROM `admins__warns` WHERE `admin_id`=:id");
				$STH->execute([':id' => $adminId]);
			} else {
				$STH = $pdo->prepare("DELETE FROM `admins__warns` WHERE `user_id`=:id");
				$STH->execute([':id' => $userId]);
			}
		} catch(Throwable $e) {
			write_log('[AdminWarn] purge: ' . $e->getMessage());
			return ['status' => false, 'message' => 'Не удалось удалить записи'];
		}

		write_log('Полностью удалены выговоры (' . $total . ' шт.) admin_id=' . $adminId . ' user_id=' . $userId);

		return ['status' => true, 'message' => 'Удалено записей: ' . $total, 'purged' => $total];
	}
}

if(!function_exists('pb_page_default_image')) {
	function pb_page_default_image($pdo = null) {
		static $cached = null;
		if($cached !== null) {
			return $cached;
		}

		$fallback = 'files/miniatures/pbgame_ui.jpg';
		if(empty($pdo) && function_exists('pdo')) {
			$pdo = pdo();
		}
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return $fallback;
		}

		try {
			pb_theme_ensure_schema($pdo);
			$STH = $pdo->query("SELECT `page_default_image` FROM `config__secondary` LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$row = $STH->fetch();
			$image = !empty($row->page_default_image) ? ltrim(trim((string)$row->page_default_image), '/') : '';
		} catch(Throwable $e) {
			$image = '';
		}

		if($image === '' || !file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $image)) {
			$image = $fallback;
		}

		$cached = $image;
		return $cached;
	}
}

if(!function_exists('pb_page_default_image_set')) {
	function pb_page_default_image_set($pdo, $path) {
		if(empty($pdo) || !($pdo instanceof PDO)) {
			return false;
		}
		pb_theme_ensure_schema($pdo);

		$STH = $pdo->prepare("UPDATE `config__secondary` SET `page_default_image`=:image LIMIT 1");
		return $STH->execute([':image' => ltrim((string)$path, '/')]);
	}
}

if(!function_exists('pb_is_system_avatar')) {
	function pb_is_system_avatar($avatar, $pdo = null) {
		$avatar = ltrim(trim((string)$avatar), '/');
		if($avatar === '' || $avatar === 'files/avatars/no_avatar.jpg') {
			return true;
		}
		return $avatar === pb_user_default_avatar($pdo);
	}
}

function generation_name($name = null) {
	if(empty($name)):
		return md5(date("YmdHis") . time());
	endif;

	return (md5(date("YmdHis") . time() . $name) . '_' . $name);
}

function file_uploads($dir = null, $file = null) {
	if(empty($dir) || empty($file)):
		return ['alert' => 'error', 'message' => 'Не указаны параметры'];
	endif;

	if(0 < $file['error']):
		return ['alert' => 'error', 'message' => 'Ошибка файла', 'code' => $file['error']];
	endif;

	$name = generation_name($file['name']);
	$full_dir = "$dir/$name";

	if(!move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $full_dir)):
		return false;
	endif;

	return ['alert' => 'success', 'name' => $name, 'full_dir' => $full_dir];
}


function pb_public_asset($path, $default = '') {
    $path = trim((string)$path);
    if ($path === '') {
        return $default;
    }
    if (preg_match('~^(https?:)?//~i', $path)) {
        return $path;
    }
    if ($path[0] !== '/') {
        $path = '/' . ltrim($path, '/');
    }
    return $path;
}

function pb_file_exists_public($path) {
    $path = trim((string)$path);
    if ($path === '' || preg_match('~^(https?:)?//~i', $path)) {
        return false;
    }
    $urlPath = parse_url($path, PHP_URL_PATH);
    if (!$urlPath) {
        $urlPath = $path;
    }
    $file = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . '/' . ltrim($urlPath, '/');
    return $file !== '/' && is_file($file);
}

function pb_playground_resource_query($uid, array $executors = [], array $categoryIds = [], array $categoryCodes = [], array $categoryNames = []) {
    $uid = clean($uid, 'int');
    if (empty($uid)) {
        return null;
    }

    $where = ["pp.`uid`=:uid", "pp.`enable`='1'"];
    $params = [':uid' => $uid];
    $or = [];

    foreach ($executors as $i => $executor) {
        $executor = trim((string)$executor);
        if ($executor === '') {
            continue;
        }
        $key = ':executor_' . $i;
        $or[] = "p.`executor`=" . $key;
        $params[$key] = $executor;
    }

    $ids = [];
    foreach ($categoryIds as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $ids[] = $id;
        }
    }
    if (!empty($ids)) {
        $or[] = 'pp.`category` IN (' . implode(',', array_unique($ids)) . ')';
        $or[] = 'p.`id_category` IN (' . implode(',', array_unique($ids)) . ')';
    }

    foreach ($categoryCodes as $i => $code) {
        $code = trim((string)$code);
        if ($code === '') {
            continue;
        }
        $key = ':code_' . $i;
        $or[] = 'LOWER(c.`code_name`)=LOWER(' . $key . ')';
        $params[$key] = $code;
    }

    foreach ($categoryNames as $i => $name) {
        $name = trim((string)$name);
        if ($name === '') {
            continue;
        }
        $key = ':name_' . $i;
        $or[] = 'LOWER(c.`name`) LIKE LOWER(' . $key . ')';
        $params[$key] = '%' . $name . '%';
    }

    if (empty($or)) {
        return null;
    }
    $where[] = '(' . implode(' OR ', $or) . ')';

    try {
        $sql = "SELECT p.`resource` FROM `playground__purchases` pp "
             . "LEFT JOIN `playground__product` p ON p.`id`=pp.`pid` "
             . "LEFT JOIN `playground__category` c ON c.`id`=pp.`category` "
             . "WHERE " . implode(' AND ', $where) . " "
             . "ORDER BY pp.`id` DESC LIMIT 1";
        $sth = pdo()->prepare($sql);
        $sth->execute($params);
        $row = $sth->fetch(PDO::FETCH_OBJ);
        if (!empty($row) && !empty($row->resource)) {
            return ltrim((string)$row->resource, '/');
        }
    } catch (Throwable $e) {
        return null;
    }
    return null;
}

function get_user_playground_resource($uid = null, $category = null) {
    $uid = clean($uid, 'int');
    $category = clean($category, 'int');
    if (empty($uid) || empty($category)) {
        return null;
    }

    $executorMap = [
        1 => ['user_background', 'profile_background', 'background'],
        2 => ['user_avatar', 'profile_avatar', 'avatar'],
        3 => ['user_frame', 'profile_frame', 'frame']
    ];
    $codeMap = [
        1 => ['background', 'cover', 'profile_background'],
        2 => ['avatar', 'profile_avatar'],
        3 => ['frame', 'profile_frame', 'frames']
    ];
    $nameMap = [
        1 => ['фон', 'background', 'cover'],
        2 => ['аватар', 'avatar'],
        3 => ['рам', 'frame']
    ];

    $resource = pb_playground_resource_query(
        $uid,
        $executorMap[$category] ?? [],
        [$category],
        $codeMap[$category] ?? [],
        $nameMap[$category] ?? []
    );
    if (!empty($resource)) {
        return $resource;
    }

    try {
        $playground = new Playground(pdo(), configs());
        $resource = $playground->get_resource_active($category, $uid);
        if (!empty($resource)) {
            return ltrim((string)$resource, '/');
        }
    } catch (Throwable $e) {
        return null;
    }
    return null;
}

function get_user_avatar_src($uid = null, $fallback = null, $skipPremium = false) {
    $uid = clean($uid, 'int');
    $fallback = trim((string)$fallback);
    if ($fallback === '') {
        $fallback = function_exists('pb_user_default_avatar') ? '/' . ltrim(pb_user_default_avatar(), '/') : '/files/avatars/no_avatar.jpg';
    }
    $safeFallback = function_exists('pb_user_default_avatar') ? '/' . ltrim(pb_user_default_avatar(), '/') : '/files/avatars/no_avatar.jpg';

    if (empty($uid)) {
        return pb_public_asset($fallback, $safeFallback);
    }

    if (!$skipPremium && function_exists('pb_premium_profile_avatar')) {
        $premiumAvatar = pb_premium_profile_avatar($uid);
        if (!empty($premiumAvatar)) {
            return pb_public_asset($premiumAvatar, pb_public_asset($fallback, $safeFallback));
        }
    }

    $resource = get_user_playground_resource($uid, 2);
    if (!empty($resource)) {
        return pb_public_asset('/files/playground/' . $resource, pb_public_asset($fallback, $safeFallback));
    }

    $user = usr($uid);
    if (!empty($user) && !empty($user->avatar)) {
        $userAvatar = ltrim(trim((string)$user->avatar), '/');
        if(function_exists('pb_is_system_avatar') && pb_is_system_avatar($userAvatar)) {
            $userAvatar = function_exists('pb_user_default_avatar') ? pb_user_default_avatar() : 'files/avatars/no_avatar.jpg';
        }
        return pb_public_asset('/' . ltrim($userAvatar, '/'), pb_public_asset($fallback, $safeFallback));
    }
    return pb_public_asset($fallback, $safeFallback);
}

function get_user_frame($uid = null) {
    $uid = clean($uid, 'int');
    if (empty($uid)) {
        return '';
    }

    if (function_exists('pb_premium_profile_frame')) {
        $premiumFrame = pb_premium_profile_frame($uid);
        if (!empty($premiumFrame)) {
            return $premiumFrame;
        }
    }

    // Robust lookup: users can have legacy marketplace categories or custom category names.
    // We check category id/code/name, product executor/name and old Playground API.
    try {
        $sql = "SELECT p.`resource` FROM `playground__purchases` pp "
             . "LEFT JOIN `playground__product` p ON p.`id`=pp.`pid` "
             . "LEFT JOIN `playground__category` c1 ON c1.`id`=pp.`category` "
             . "LEFT JOIN `playground__category` c2 ON c2.`id`=p.`id_category` "
             . "WHERE pp.`uid`=:uid AND pp.`enable`='1' AND p.`resource` IS NOT NULL AND p.`resource`<>'' AND ("
             . "pp.`category`=3 OR p.`id_category`=3 "
             . "OR LOWER(COALESCE(c1.`code_name`,'')) LIKE '%frame%' "
             . "OR LOWER(COALESCE(c2.`code_name`,'')) LIKE '%frame%' "
             . "OR LOWER(COALESCE(c1.`code_name`,'')) LIKE '%ram%' "
             . "OR LOWER(COALESCE(c2.`code_name`,'')) LIKE '%ram%' "
             . "OR LOWER(COALESCE(c1.`name`,'')) LIKE '%рам%' "
             . "OR LOWER(COALESCE(c2.`name`,'')) LIKE '%рам%' "
             . "OR LOWER(COALESCE(c1.`name`,'')) LIKE '%frame%' "
             . "OR LOWER(COALESCE(c2.`name`,'')) LIKE '%frame%' "
             . "OR LOWER(COALESCE(p.`executor`,'')) LIKE '%frame%' "
             . "OR LOWER(COALESCE(p.`executor`,'')) LIKE '%ram%' "
             . "OR LOWER(COALESCE(p.`name`,'')) LIKE '%рам%' "
             . "OR LOWER(COALESCE(p.`name`,'')) LIKE '%frame%' "
             . "OR LOWER(COALESCE(p.`name`,'')) LIKE '%рамка%' "
             . ") ORDER BY pp.`id` DESC LIMIT 1";
        $sth = pdo()->prepare($sql);
        $sth->execute([':uid' => $uid]);
        $row = $sth->fetch(PDO::FETCH_OBJ);
        if (!empty($row) && !empty($row->resource)) {
            return pb_public_asset('/files/playground/' . ltrim((string)$row->resource, '/'));
        }
    } catch (Throwable $e) {}

    try {
        $playground = new Playground(pdo(), configs());
        $resource = $playground->get_resource_active(3, $uid);
        if (!empty($resource)) {
            return pb_public_asset('/files/playground/' . ltrim((string)$resource, '/'));
        }
    } catch (Throwable $e) {}

    return '';
}


function render_user_avatar($uid = null, $fallback = null, $imgClass = '', $wrapperClass = 'pb-avatar-wrap', $attrs = '') {
    $uid = clean($uid, 'int');
    $src = htmlspecialchars(get_user_avatar_src($uid, $fallback), ENT_QUOTES, 'UTF-8');
    $frame = get_user_frame($uid);

    $imgClass = trim((string)$imgClass);
    $wrapperClass = trim((string)$wrapperClass);
    if ($wrapperClass === '') {
        $wrapperClass = 'pb-avatar-wrap';
    }
    if (strpos(' ' . $wrapperClass . ' ', ' pb-avatar-wrap ') === false) {
        $wrapperClass = 'pb-avatar-wrap ' . $wrapperClass;
    }
    $attrs = trim((string)$attrs);

    // Locked geometry. One wrapper, one avatar, one optional frame.
    // This prevents old CSS from moving the frame separately in profile/friends/chat/header.
    $probe = ' ' . $wrapperClass . ' ' . $imgClass . ' ';
    $wrap = 52; $avatar = 40; $radius = 11;

    if (strpos($probe, 'pb-avatar-profile-main-wrap') !== false || strpos($probe, 'cs16-main-avatar-img') !== false) {
        $wrap = 154; $avatar = 132; $radius = 19;
    } elseif (strpos($probe, 'pb-avatar-profile-idcard-wrap') !== false) {
        $wrap = 120; $avatar = 104; $radius = 22;
    } elseif (strpos($probe, 'pb-avatar-profile-card-wrap') !== false || strpos($probe, 'img_personal_card') !== false) {
        $wrap = 74; $avatar = 62; $radius = 13;
    } elseif (strpos($probe, 'pb-avatar-head-wrap') !== false || strpos($probe, 'pb-avatar-header-wrap') !== false || strpos($probe, 'img_head') !== false || strpos($probe, 'img_top_menu') !== false) {
        $wrap = 40; $avatar = 34; $radius = 10;
    } elseif (strpos($probe, 'pb-avatar-chat-wrap') !== false || strpos($probe, 'chat_img') !== false) {
        $wrap = 46; $avatar = 38; $radius = 11;
    } elseif (strpos($probe, 'pb-avatar-friend') !== false || strpos($probe, 'friend_avatar') !== false || strpos($probe, 'cs16_friend_img') !== false) {
        $wrap = 48; $avatar = 40; $radius = 10;
    } elseif (strpos($probe, 'pb-avatar-top-wrap') !== false || strpos($probe, 'img_top_user') !== false || strpos($probe, 'img_donators') !== false) {
        $wrap = 54; $avatar = 44; $radius = 12;
    } elseif (strpos($probe, 'pb-avatar-tiny-wrap') !== false || strpos($probe, 'pb-avatar-online-wrap') !== false || strpos($probe, 'user_o_img') !== false || strpos($probe, 'user_online_img') !== false) {
        $wrap = 46; $avatar = 38; $radius = 11;
    } elseif (strpos($probe, 'pb-visitor-avatar-wrap') !== false) {
        $wrap = 34; $avatar = 34; $radius = 50;
    } elseif (strpos($probe, 'img_forum_user') !== false) {
        $wrap = 55; $avatar = 45; $radius = 11;
    } elseif (strpos($probe, 'forum_img_user') !== false) {
        $wrap = 43; $avatar = 35; $radius = 50;
    }

    $wrapStyle = 'position:relative!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;overflow:visible!important;line-height:0!important;width:' . $wrap . 'px!important;height:' . $wrap . 'px!important;min-width:' . $wrap . 'px!important;min-height:' . $wrap . 'px!important;max-width:' . $wrap . 'px!important;max-height:' . $wrap . 'px!important;flex:0 0 ' . $wrap . 'px!important;margin:0!important;padding:0!important;vertical-align:middle!important;border:0!important;background:transparent!important;transform:none!important;box-sizing:border-box!important;';
    $avatarStyle = 'position:relative!important;z-index:1!important;display:block!important;width:' . $avatar . 'px!important;height:' . $avatar . 'px!important;min-width:' . $avatar . 'px!important;min-height:' . $avatar . 'px!important;max-width:' . $avatar . 'px!important;max-height:' . $avatar . 'px!important;object-fit:cover!important;border-radius:' . $radius . 'px!important;transform:none!important;margin:0!important;padding:0!important;border:0!important;box-sizing:border-box!important;';
    $overlayStyle = 'position:absolute!important;z-index:3!important;inset:0!important;width:100%!important;height:100%!important;display:block!important;pointer-events:none!important;overflow:visible!important;transform:none!important;margin:0!important;padding:0!important;border:0!important;background:transparent!important;box-sizing:border-box!important;';
    $frameStyle = 'position:absolute!important;z-index:3!important;inset:0!important;width:100%!important;height:100%!important;min-width:100%!important;min-height:100%!important;max-width:none!important;max-height:none!important;object-fit:contain!important;display:block!important;transform:none!important;margin:0!important;padding:0!important;border:0!important;background:transparent!important;box-shadow:none!important;box-sizing:border-box!important;pointer-events:none!important;';

    $html = '<span class="' . htmlspecialchars($wrapperClass, ENT_QUOTES, 'UTF-8') . '" style="' . $wrapStyle . '">';
    $html .= '<img src="' . $src . '" class="' . htmlspecialchars($imgClass, ENT_QUOTES, 'UTF-8') . '" style="' . $avatarStyle . '" ' . $attrs . '>';
    if (!empty($frame)) {
        $html .= '<span class="pb-avatar-frame-overlay" style="' . $overlayStyle . '"><img class="pb-avatar-frame-img" src="' . htmlspecialchars($frame, ENT_QUOTES, 'UTF-8') . '" alt="" style="' . $frameStyle . '"></span>';
    }
    if (function_exists('pb_premium_avatar_badge_html')) {
        $premiumBadge = pb_premium_avatar_badge_html($uid);
        if (!empty($premiumBadge)) {
            $html .= $premiumBadge;
        }
    }
    $html .= '</span>';
    return $html;
}


function get_user_cover($uid = null) {
    $uid = clean($uid, "int");
    $default_cover = "/files/cover/pbgame_ui.jpg";

    if(empty($uid)):
        return $default_cover;
    endif;

    $resource = get_user_playground_resource($uid, 1);
    if (!empty($resource)) {
        return pb_public_asset('/files/playground/' . $resource, $default_cover);
    }

    $STH = pdo()->prepare("SELECT `cover` FROM `users` WHERE `id` = :id LIMIT 1");
    $STH->execute([':id' => $uid]);
    $row = $STH->fetch(PDO::FETCH_OBJ);
    $cover = trim((string)($row->cover ?? ''));

    if($cover !== '' && $cover !== '0'):
        $cover_path = parse_url($cover, PHP_URL_PATH);
        $cover_file = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . '/' . ltrim((string)$cover_path, '/');
        if($cover_path && $cover_file !== '/' && is_file($cover_file)) {
            return pb_public_asset($cover, $default_cover);
        }
    endif;

    return $default_cover;
}

// Фон пользователя, купленный на площадке (market/playground), для мест
// вроде топ-карточек. В отличие от get_user_cover() не падает на users.cover
// и не спрашивает PREMIUM — только реальная активная покупка category=1.
// Поддерживает видео (mp4/webm) так же, как статичные изображения.
function get_user_market_background($uid = null) {
    $uid = clean($uid, 'int');
    if (empty($uid)) {
        return '';
    }

    $resource = get_user_playground_resource($uid, 1);
    if (empty($resource)) {
        return '';
    }

    return pb_public_asset('/files/playground/' . $resource);
}

// HTML-блок фона карточки: <img>/<video> на весь родитель (position:absolute,
// inset:0) под указанным классом-обёрткой. Если у пользователя нет market-фона,
// подставляется дефолтная картинка проекта.
function render_user_market_background_html($uid = null, $wrapClass = 'pbg_top_card_bg') {
    $wrapClass = htmlspecialchars((string) $wrapClass, ENT_QUOTES, 'UTF-8');
    $bg = get_user_market_background($uid);

    if ($bg === '') {
        $defaultSrc = htmlspecialchars('/templates/solution2/img/back_profile.png', ENT_QUOTES, 'UTF-8');
        return '<div class="' . $wrapClass . '" style="background-image:url(' . $defaultSrc . ')"></div>';
    }

    $src = htmlspecialchars($bg, ENT_QUOTES, 'UTF-8');
    $ext = strtolower(pathinfo(parse_url($bg, PHP_URL_PATH) ?: $bg, PATHINFO_EXTENSION));

    if ($ext === 'mp4' || $ext === 'webm') {
        return '<video class="' . $wrapClass . '" autoplay muted loop playsinline preload="metadata"><source src="' . $src . '" type="video/' . $ext . '"></video>';
    }

    return '<div class="' . $wrapClass . '" style="background-image:url(' . $src . ')"></div>';
}

// Фон карточки пользователя, купленный на площадке (market).
// Возвращает HTML c <video>/<img> и затемняющим оверлеем — та же логика,
// что и на странице профиля ({profile_bg_html}). Если фон не куплен или
// площадка не установлена, возвращает дефолтный фон.
function pb_user_card_bg_html($uid = null, $default = '../templates/solution2/img/back_profile.png') {
	$uid = clean($uid, 'int');
	$default = htmlspecialchars((string) $default, ENT_QUOTES, 'UTF-8');
	$fallback = '<div class="mst-adm-bg" style="background-image:url(' . $default . ');"></div><div class="mst-adm-bg-overlay"></div>';

	if(empty($uid)) {
		return $fallback;
	}

	try {
		$resource = get_user_playground_resource($uid, 1);
	} catch(Throwable $e) {
		return $fallback;
	}

	if(empty($resource)) {
		return $fallback;
	}

	$resource = ltrim(str_replace('..', '', (string) $resource), '/');
	$file = htmlspecialchars('/files/playground/' . $resource, ENT_QUOTES, 'UTF-8');
	$ext  = strtolower(pathinfo($resource, PATHINFO_EXTENSION));

	if($ext === 'mp4' || $ext === 'webm') {
		return '<video class="mst-adm-bg mst-adm-bg-video" autoplay muted loop playsinline preload="none" poster="' . $default . '"><source src="' . $file . '" type="video/' . $ext . '"></video><div class="mst-adm-bg-overlay"></div>';
	}

	return '<div class="mst-adm-bg" style="background-image:url(' . $file . ');"></div><div class="mst-adm-bg-overlay"></div>';
}

function sys() {
	global $system;

	if(empty($system)):
		return (new System);
	endif;

	return $system;
}

function result($arr) {
	exit(json_encode($arr));
}

function getLimit($attr)
{
	if(
		in_array($attr,
		    [
			    'bans_lim',
			    'muts_lim',
			    'users_lim',
			    'bans_lim2',
			    'news_lim',
			    'stats_lim',
			    'complaints_lim'
		    ]
		)
	) {
		return pdo()
			->query("SELECT $attr FROM config__secondary LIMIT 1")
			->fetch(PDO::FETCH_OBJ)
			->$attr;
	} else {
		return 30;
	}
}

function getPageParam($paramName, $type = 'int')
{
	if(array_key_exists($paramName, $_GET)) {
		return clean($_GET[$paramName], $type);
	} else {
		return 0;
	}
}

function getPageStartPosition($number, $limit)
{
	if($number) {
		return ($number - 1) * $limit;
	} else {
		return 0;
	}
}

function resetIfPaginationIncorrect($number, $limit, $count, $to)
{
	if(($number * $limit - $count) > $limit) {
		header('Location: ' . $to);
		exit();
	}
}

function getLibAssets($libNames)
{
	$libs = [
		'timepicker' => '<link rel="stylesheet" href="../templates/admin/css/timepicker.css">'
			. '<script src="../templates/admin/js/timepicker/timepicker.js"></script>'
			. '<script src="../templates/admin/js/timepicker/jquery-ui-timepicker-addon.js"></script>'
			. '<script src="../templates/admin/js/timepicker/jquery-ui-timepicker-addon-i18n.min.js"></script>'
			. '<script src="../templates/admin/js/timepicker/jquery-ui-sliderAccess.js"></script>',
		'ckeditor'   => '<script src="../modules/editors/ckeditor/ckeditor.js"></script>',
		'dropzone'   => '<script src="../templates/admin/js/dropzone.js"></script>'
			. '<link rel="stylesheet" href="../templates/admin/css/dropzone.css">',
		'codemirror' => '<link rel="stylesheet" href="../modules/editors/editor/codemirror.css?v={cache}">'
			. '<link rel="stylesheet" href="../modules/editors/editor/fullscreen.css?v={cache}">'
			. '<link rel="stylesheet" href="../modules/editors/editor/simplescrollbars.css?v={cache}">'
			. '<link rel="stylesheet" href="../modules/editors/editor/monokai.css?v={cache}">'
			. '<link rel="stylesheet" href="../modules/editors/editor/dialog.css?v={cache}">'
			. '<script src="../modules/editors/editor/codemirror.js?v={cache}"></script>'
			. '<script src="../modules/editors/editor/xml.js?v={cache}"></script>'
			. '<script src="../modules/editors/editor/javascript.js?v={cache}"></script>'
			. '<script src="../modules/editors/editor/css.js?v={cache}"></script>'
			. '<script src="../modules/editors/editor/htmlmixed.js?v={cache}"></script>'
			. '<script src="../modules/editors/editor/simplescrollbars.js?v={cache}"></script>'
			. '<script src="../modules/editors/editor/fullscreen.js?v={cache}"></script>'
			. '<script src="../modules/editors/editor/searchcursor.js?v={cache}"></script>'
			. '<script src="../modules/editors/editor/search.js?v={cache}"></script>'
			. '<script src="../modules/editors/editor/dialog.js?v={cache}"></script>',
		'fixedWidth' => '<style>body {min-width: 1200px !important;} .wapper{min-width: 1200px !important;}</style>',
		'tinymce' => '<script src="../modules/editors/tinymce/tinymce.min.js"></script>',
		'gstatic' => '<script src="https://www.gstatic.com/charts/loader.js"></script>',
		'tabs' => '<link rel="stylesheet" href="../templates/{template}/css/tabs.css">',
		'modernizr' => '<script src="../templates/{template}/js/modernizr.js"></script>',
		'jplayer'   => '<script src="../ajax/sound/jquery.jplayer.min.js"></script>',
		'farbtastic'   => '<script src="../templates/admin/js/farbtastic.js"></script>'
			. '<link rel="stylesheet" href="../templates/admin/css/farbtastic.css">'
	];

	$libsStr = '';

	if(!is_array($libNames)) {
		$libNames = [$libNames];
	}

	if(is_array($libNames)) {
		foreach($libNames as $item) {
			if(array_key_exists($item, $libs)) {
				$libsStr .= $libs[$item];
			}
		}
	}
	
	return $libsStr;
}

function uuid4()
{
	return sprintf(
		'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand(0, 0xffff),
		mt_rand(0, 0xffff),
		mt_rand(0, 0xffff),
		mt_rand(0, 0x0fff) | 0x4000,
		mt_rand(0, 0x3fff) | 0x8000,
		mt_rand(0, 0xffff),
		mt_rand(0, 0xffff),
		mt_rand(0, 0xffff)
	);
}

function isModuleActive($moduleName)
{
	$STH = pdo()->prepare("SELECT active FROM modules WHERE name=:name LIMIT 1");
	$STH->execute([':name' => $moduleName]);

	return $STH->fetchColumn() == 1;
}

function isPageActive($pageNameOrUrl)
{
	static $pageCache = [];

	$pageNameOrUrl = trim((string) $pageNameOrUrl);
	if($pageNameOrUrl === '') {
		return false;
	}

	if(array_key_exists($pageNameOrUrl, $pageCache)) {
		return $pageCache[$pageNameOrUrl];
	}

	$STH = pdo()->prepare("SELECT active FROM pages WHERE name = :value OR url = :value LIMIT 1");
	$STH->execute([':value' => $pageNameOrUrl]);
	$pageCache[$pageNameOrUrl] = ((int) $STH->fetchColumn() === 1);

	return $pageCache[$pageNameOrUrl];
}

function renderModuleWidgets($location, array $context = [])
{
	static $activeModules = null;

	$location = preg_replace('/[^a-z0-9_\-]/i', '', (string) $location);
	if(empty($location)) {
		return '';
	}

	if($activeModules === null) {
		$activeModules = [];
		$STH = pdo()->query("SELECT name FROM modules WHERE active = '1'");
		if($STH) {
			$activeModules = $STH->fetchAll(PDO::FETCH_COLUMN);
		}
	}

	$html = '';
	$documentRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');

	foreach($activeModules as $moduleName) {
		$moduleName = preg_replace('/[^a-z0-9_\-]/i', '', trim((string) $moduleName));
		if($moduleName === '') {
			continue;
		}

		$candidates = [
			$documentRoot . '/modules_extra/' . $moduleName . '/widgets/' . $location . '.php',
			$documentRoot . '/modules/' . $moduleName . '/widgets/' . $location . '.php'
		];

		foreach($candidates as $widgetFile) {
			if(!is_file($widgetFile)) {
				continue;
			}

			$widgetContext = $context;
			extract($context, EXTR_SKIP);
			ob_start();
			try {
				include $widgetFile;
				$html .= trim((string) ob_get_clean());
			} catch(Throwable $e) {
				if(ob_get_level()) {
					ob_end_clean();
				}
				if(function_exists('write_log')) {
					write_log('Module widget error [' . $moduleName . ':' . $location . ']: ' . $e->getMessage());
				}
			}
			break;
		}
	}

	return $html;
}



function pb_ensure_admin_utility_pages($pdo = null)
{
	static $ensured = false;
	if($ensured) {
		return;
	}
	$ensured = true;

	if($pdo === null && function_exists('pdo')) {
		$pdo = pdo();
	}
	if(!$pdo instanceof PDO) {
		return;
	}

	$pages = [
		[
			'file' => 'modules/admin/general.php',
			'url' => 'admin/general',
			'name' => 'admin_general',
			'title' => 'Основные',
			'description' => 'Основные настройки PBGame CMS: название сайта, главный администратор, часовой пояс, валюта, скидка и очистка движка.',
			'keywords' => 'pbgame cms, основные настройки, название сайта, часовой пояс, валюта, скидка',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/logs.php',
			'url' => 'admin/logs',
			'name' => 'admin_logs',
			'title' => 'Логи и диагностика',
			'description' => 'Системный журнал PBGame CMS: ошибки, события, платежи, блокировки IP и диагностика совместимости сервера.',
			'keywords' => 'pbgame cms, логи, ошибки, диагностика, блокировки ip',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/dev_center.php',
			'url' => 'admin/dev_center',
			'name' => 'admin_dev_center',
			'title' => 'Центр разработчика',
			'description' => 'Единый центр служебных настроек PBGame CMS: режим разработчика, капча, очистка, миграция и сервисные параметры.',
			'keywords' => 'pbgame cms, центр разработчика, dev center, миграция, капча',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/dev_ssh.php',
			'url' => 'admin/dev_ssh',
			'name' => 'admin_dev_ssh',
			'title' => 'SSH | Терминал',
			'description' => 'Внутренний терминал PBGame CMS для сервисных команд по версии, страницам, модулям и пользователям.',
			'keywords' => 'pbgame cms, ssh, терминал, dev terminal',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/dev_migration.php',
			'url' => 'admin/dev_migration',
			'name' => 'admin_dev_migration',
			'title' => 'Миграция данных',
			'description' => 'Отдельная страница Центра разработчика для анализа источника, dry-run сценария и запуска миграции данных PBGame CMS.',
			'keywords' => 'pbgame cms, миграция, перенос данных, dev migration',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/telegram.php',
			'url' => 'admin/telegram',
			'name' => 'admin_telegram',
			'title' => 'Telegram',
			'description' => 'Страница интеграции Telegram в PBGame CMS.',
			'keywords' => 'pbgame cms, telegram, телеграм',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/geo.php',
			'url' => 'admin/geo',
			'name' => 'admin_geo',
			'title' => 'Геопозиция',
			'description' => 'Статистика геолокации пользователей PBGame CMS: страны и города, откуда приходят посетители.',
			'keywords' => 'pbgame cms, геолокация, геопозиция, страны, статистика, sypexgeo',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/seo.php',
			'url' => 'admin/seo',
			'name' => 'admin_seo',
			'title' => 'Индексация SEO',
			'description' => 'Технический SEO-аудит страниц PBGame CMS: meta title/description/robots, sitemap.xml, robots.txt и проверка индексации в поисковых системах.',
			'keywords' => 'pbgame cms, seo, индексация, sitemap, robots.txt, meta теги, поисковая оптимизация',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/modules_catalog.php',
			'url' => 'admin/modules_catalog',
			'name' => 'admin_modules_catalog',
			'title' => 'Каталог модулей',
			'description' => 'Каталог доступных модулей PBGame CMS для выбора и будущей установки.',
			'keywords' => 'pbgame cms, каталог модулей, модули',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/premium.php',
			'url' => 'admin/premium',
			'name' => 'admin_premium',
			'title' => 'PREMIUM подписка',
			'description' => 'Управление тарифами PREMIUM подписки PBGame CMS: цены, сроки действия, привилегии, привязка к серверам и стикер-паки.',
			'keywords' => 'pbgame cms, premium, подписка, тарифы',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/migration.php',
			'url' => 'admin/migration',
			'name' => 'admin_migration',
			'title' => 'Миграция',
			'description' => 'Страница подготовки миграции существующей базы данных с GameCMS в PBGame CMS.',
			'keywords' => 'pbgame cms, миграция, gamecms, импорт',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/docs.php',
			'url' => 'admin/docs',
			'name' => 'admin_docs',
			'title' => 'Документация движка',
			'description' => 'Справочник по переменным, шаблонам, market-рамкам, верификации и подключению модулей PBGame CMS.',
			'keywords' => 'pbgame cms, документация, переменные, шаблоны, модули',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/cs_install.php',
			'url' => 'admin/cs_install',
			'name' => 'admin_cs_install',
			'title' => 'Установщик',
			'description' => 'Установка баз данных игровых компонентов (CS Bans и др.) через визард с проверкой соединения.',
			'keywords' => 'pbgame cms, установщик, cs bans, база данных, amx',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/remote_access.php',
			'url' => 'admin/remote_access',
			'name' => 'admin_remote_access',
			'title' => 'Удалённый доступ',
			'description' => 'Временный ограниченный доступ в Админцентр для мастеров — по ссылке, со сроком действия и списком разрешённых разделов.',
			'keywords' => 'pbgame cms, удалённый доступ, временный доступ, мастер, ограниченный доступ',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
		[
			'file' => 'modules/admin/remote_login.php',
			'url' => 'admin/remote',
			'name' => 'admin_remote_login',
			'title' => 'Временный доступ',
			'description' => 'Вход по ссылке временного ограниченного доступа в Админцентр.',
			'keywords' => 'pbgame cms, временный доступ',
			'kind' => 1,
			'image' => 'files/miniatures/pbgame_ui.jpg',
			'robots' => 0,
			'privacy' => 0,
			'type' => 2,
			'active' => 1,
			'module' => 0,
			'page' => 0,
			'class' => 0,
		],
	];

	$select = $pdo->prepare("SELECT `id`, `active` FROM `pages` WHERE `url`=:url OR `name`=:name LIMIT 1");
	$insert = $pdo->prepare(
		"INSERT INTO `pages`(`file`, `url`, `name`, `title`, `description`, `keywords`, `kind`, `image`, `robots`, `privacy`, `type`, `active`, `module`, `page`, `class`)
		 VALUES (:file, :url, :name, :title, :description, :keywords, :kind, :image, :robots, :privacy, :type, :active, :module, :page, :class)"
	);
	$update = $pdo->prepare(
		"UPDATE `pages` SET `file`=:file, `url`=:url, `name`=:name, `title`=:title, `description`=:description, `keywords`=:keywords, `kind`=:kind, `image`=:image, `robots`=:robots, `privacy`=:privacy, `type`=:type, `active`=:active, `module`=:module, `page`=:page, `class`=:class WHERE `id`=:id LIMIT 1"
	);

	foreach($pages as $page) {
		$select->execute([':url' => $page['url'], ':name' => $page['name']]);
		$existing = $select->fetch(PDO::FETCH_ASSOC);
		if($existing && !empty($existing['id'])) {
			$payload = $page;
			$payload['id'] = (int) $existing['id'];
			$update->execute($payload);
			continue;
		}
		$insert->execute($page);
	}
}

function isEmailIntroduced($email) {
	if(substr($email, 0, 6) != 'vk_id_' && !empty($email)) {
		return true;
	} else {
		return false;
	}
}

function removeOldFiles($dir, $days) {
	$files = glob($dir . '/*');
	$now   = time();

	foreach ($files as $file) {
		if (is_file($file)) {
			if ($now - filemtime($file) >= 60 * 60 * 24 * $days) {
				unlink($file);
			}
		}
	}
}

function findAndReplace($find, $replace, $path)
{
	foreach(glob($path, GLOB_BRACE) as $file) {
		$fileData = file_get_contents($file);

		if(stristr($fileData, $find) !== false) {
			file_put_contents($file, str_replace($find, $replace, $fileData));
		}
	}
}

function getNameLike($name)
{
	if(mb_strlen($name, 'UTF-8') < 3) {
		return $name;
	} else {
		return "%" . strip_data($name) . "%";
	}
}

function redirect($url = '/', $ajax = true) {
	if($ajax) {
		echo '<script>window.location.href = "'.$url.'";</script>';
	}
	else {
		header("Location: $url");
	}
	
	die;
}

function addons() {
	return new Addons;
}


/*
 * PBGame Admin Gate: отдельный логин/пароль перед Админцентром.
 * Если данные ещё не заданы, старый вход продолжает работать.
 */
function pb_admin_gate_safe_equals($known, $user)
{
    if (function_exists('hash_equals')) {
        return hash_equals((string) $known, (string) $user);
    }
    return (string) $known === (string) $user;
}

function pb_admin_gate_ensure_schema($pdo)
{
    static $done = false;
    if ($done || empty($pdo)) {
        return;
    }
    $done = true;

    try {
        $columns = array();
        $sth = $pdo->query("SHOW COLUMNS FROM `config`");
        if ($sth) {
            while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $columns[$row['Field']] = true;
            }
        }

        $alter = array();
        if (!isset($columns['admin_gate_login'])) {
            $alter[] = "ADD COLUMN `admin_gate_login` varchar(191) NOT NULL DEFAULT ''";
        }
        if (!isset($columns['admin_gate_password_hash'])) {
            $alter[] = "ADD COLUMN `admin_gate_password_hash` varchar(255) NOT NULL DEFAULT ''";
        }
        if (!empty($alter)) {
            $pdo->exec("ALTER TABLE `config` " . implode(', ', $alter));
        }
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_admin_gate_ensure_schema: ' . $e->getMessage());
        }
    }
}

function pb_mon_key_ensure_schema($pdo)
{
    static $done = false;
    if ($done || empty($pdo)) {
        return;
    }
    $done = true;

    try {
        $sth = $pdo->query("SHOW COLUMNS FROM `config__secondary` LIKE 'mon_key'");
        $row = $sth ? $sth->fetch(PDO::FETCH_ASSOC) : false;
        if ($row && stripos($row['Type'], 'varchar(255)') === false) {
            $pdo->exec("ALTER TABLE `config__secondary` MODIFY `mon_key` varchar(255) NOT NULL DEFAULT ''");
        }
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_mon_key_ensure_schema: ' . $e->getMessage());
        }
    }
}

function pb_admin_gate_get_config($pdo)
{
    pb_admin_gate_ensure_schema($pdo);

    $data = array('login' => '', 'password_hash' => '');
    try {
        $sth = $pdo->query("SELECT `admin_gate_login`, `admin_gate_password_hash` FROM `config` LIMIT 1");
        $row = $sth ? $sth->fetch(PDO::FETCH_ASSOC) : false;
        if ($row) {
            $data['login'] = trim((string) $row['admin_gate_login']);
            $data['password_hash'] = trim((string) $row['admin_gate_password_hash']);
        }
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_admin_gate_get_config: ' . $e->getMessage());
        }
    }
    return $data;
}

function pb_admin_gate_configured($pdo)
{
    $config = pb_admin_gate_get_config($pdo);
    return ($config['login'] !== '' && $config['password_hash'] !== '');
}

function pb_admin_gate_session_ttl()
{
    return 3600;
}

function pb_admin_gate_verify($pdo, $login, $password)
{
    $config = pb_admin_gate_get_config($pdo);
    $login = trim((string) $login);
    $password = (string) $password;

    if ($config['login'] === '' || $config['password_hash'] === '') {
        return false;
    }
    if (!pb_admin_gate_safe_equals($config['login'], $login)) {
        return false;
    }
    return function_exists('password_verify') && password_verify($password, $config['password_hash']);
}

function pb_admin_gate_mark_verified($pdo, $userId, $ttl = null)
{
    global $SC, $conf, $dev_mode;

    if ($ttl === null) {
        $ttl = pb_admin_gate_session_ttl();
    }
    $ttl = max(60, min((int) $ttl, pb_admin_gate_session_ttl()));
    $now = time();

    $_SESSION['pb_admin_gate_uid'] = (int) $userId;
    $_SESSION['pb_admin_gate_started'] = $now;
    $_SESSION['pb_admin_gate_until'] = $now + $ttl;

    $config = pb_admin_gate_get_config($pdo);
    if (isset($SC) && is_object($SC) && method_exists($SC, 'get_admin_cache') && $config['password_hash'] !== '') {
        if (isset($conf) && is_object($conf) && isset($conf->ip_protect) && (int) $conf->ip_protect === 1) {
            $SC->admin_ip = get_ip();
        }
        $_SESSION['admin'] = 1;
        $_SESSION['admin_cache'] = $SC->get_admin_cache($config['password_hash']);
        $_SESSION['dev_mode'] = isset($dev_mode) ? $dev_mode : 0;
    }
}

function pb_admin_gate_login($pdo, $login, $password)
{
    if (!is_auth() || !is_worthy('h')) {
        return array('status' => false, 'message' => 'У Вас нет доступа к Админцентру.');
    }
    if (!pb_admin_gate_configured($pdo)) {
        return array('status' => false, 'message' => 'Логин и пароль Админцентра ещё не настроены.');
    }
    if (trim((string) $login) === '' || (string) $password === '') {
        return array('status' => false, 'message' => 'Введите логин и пароль Админцентра.');
    }
    if (!pb_admin_gate_verify($pdo, $login, $password)) {
        return array('status' => false, 'message' => 'Неверный логин или пароль Админцентра.');
    }

    pb_admin_gate_mark_verified($pdo, (int) $_SESSION['id']);
    if (function_exists('write_log')) {
        write_log('Вход в Админцентр через дополнительный пароль: ID ' . (int) $_SESSION['id']);
    }
    return array('status' => true, 'message' => 'Вход подтверждён.');
}

function pb_admin_gate_session_until($pdo = null)
{
    if (empty($_SESSION['pb_admin_gate_until'])) {
        return 0;
    }
    if ($pdo !== null && function_exists('pb_admin_gate_configured') && !pb_admin_gate_configured($pdo)) {
        return 0;
    }
    if (isset($_SESSION['pb_admin_gate_uid'], $_SESSION['id']) && (int) $_SESSION['pb_admin_gate_uid'] !== (int) $_SESSION['id']) {
        return 0;
    }

    $now = time();
    $ttl = pb_admin_gate_session_ttl();
    $until = (int) $_SESSION['pb_admin_gate_until'];
    if ($until <= $now) {
        pb_admin_gate_reset_session();
        return 0;
    }

    if (empty($_SESSION['pb_admin_gate_started']) || (int) $_SESSION['pb_admin_gate_started'] <= 0 || (int) $_SESSION['pb_admin_gate_started'] > $now) {
        if ($until > $now + $ttl) {
            $_SESSION['pb_admin_gate_started'] = $now;
            $_SESSION['pb_admin_gate_until'] = $now + $ttl;
            return (int) $_SESSION['pb_admin_gate_until'];
        }
        $_SESSION['pb_admin_gate_started'] = max(1, $until - $ttl);
    }

    $started = (int) $_SESSION['pb_admin_gate_started'];
    $maxUntil = $started + $ttl;
    if ($until > $maxUntil) {
        $_SESSION['pb_admin_gate_until'] = $maxUntil;
        $until = $maxUntil;
    }
    if ($until <= $now) {
        pb_admin_gate_reset_session();
        return 0;
    }
    return $until;
}

function pb_admin_gate_admin_session_cache_valid($pdo, $sc = null)
{
    global $SC;
    if ($sc === null) {
        $sc = isset($SC) ? $SC : null;
    }
    if (!$sc || !method_exists($sc, 'get_admin_cache') || empty($_SESSION['admin']) || empty($_SESSION['admin_cache'])) {
        return false;
    }
    $config = pb_admin_gate_get_config($pdo);
    if ($config['password_hash'] === '') {
        return false;
    }
    return pb_admin_gate_safe_equals($sc->get_admin_cache($config['password_hash']), (string) $_SESSION['admin_cache']);
}

function pb_admin_gate_session_valid($pdo)
{
    if (!pb_admin_gate_configured($pdo)) {
        return true;
    }
    if (!is_auth() || !is_worthy('h')) {
        pb_admin_gate_reset_session();
        return false;
    }
    if (empty($_SESSION['pb_admin_gate_uid']) || empty($_SESSION['pb_admin_gate_until'])) {
        return false;
    }
    if ((int) $_SESSION['pb_admin_gate_uid'] !== (int) $_SESSION['id']) {
        pb_admin_gate_reset_session();
        return false;
    }
    if (pb_admin_gate_session_until($pdo) <= time()) {
        pb_admin_gate_reset_session();
        return false;
    }
    if (!pb_admin_gate_admin_session_cache_valid($pdo)) {
        pb_admin_gate_reset_session();
        return false;
    }
    return true;
}

function pb_admin_gate_required($pdo)
{
    if (!pb_admin_gate_configured($pdo)) {
        return false;
    }
    if (!is_auth() || !is_worthy('h')) {
        return false;
    }
    return !pb_admin_gate_session_valid($pdo);
}

function pb_admin_gate_confirmation_required($pdo)
{
    return pb_admin_gate_required($pdo);
}

function pb_admin_current_session_until($pdo = null)
{
    // Независимая админ-сессия: её срок жизни — основной источник для таймера в панели.
    if (!empty($_SESSION['admin_until']) && (int) $_SESSION['admin_until'] > time()) {
        return (int) $_SESSION['admin_until'];
    }

    $until = array();
    $gateUntil = function_exists('pb_admin_gate_session_until') ? pb_admin_gate_session_until($pdo) : 0;
    if ($gateUntil > time()) {
        $until[] = $gateUntil;
    }
    if (!empty($_SESSION['pb_admin_2fa_until'])) {
        $use2faUntil = true;
        if ($pdo !== null && function_exists('pb_admin_2fa_is_feature_ready')) {
            $use2faUntil = pb_admin_2fa_is_feature_ready($pdo);
        }
        if ($use2faUntil && (int) $_SESSION['pb_admin_2fa_until'] > time()) {
            $until[] = (int) $_SESSION['pb_admin_2fa_until'];
        }
    }
    if (empty($until)) {
        return 0;
    }
    return min($until);
}

function pb_admin_gate_save_credentials($pdo, $login, $password)
{
    pb_admin_gate_ensure_schema($pdo);
    $login = trim((string) $login);
    $password = (string) $password;

    if ($login === '') {
        return array('status' => 2, 'data' => 'Укажите логин Админцентра.');
    }
    $loginLen = function_exists('mb_strlen') ? mb_strlen($login, 'UTF-8') : strlen($login);
    $passwordLen = function_exists('mb_strlen') ? mb_strlen($password, 'UTF-8') : strlen($password);
    if ($loginLen < 3 || $loginLen > 64) {
        return array('status' => 2, 'data' => 'Логин Админцентра должен быть от 3 до 64 символов.');
    }
    if (preg_match('/[\x00-\x1F\x7F]/u', $login)) {
        return array('status' => 2, 'data' => 'Логин Админцентра содержит недопустимые символы.');
    }
    if ($passwordLen < 12) {
        return array('status' => 2, 'data' => 'Пароль Админцентра должен быть минимум 12 символов.');
    }
    if (!function_exists('password_hash')) {
        return array('status' => 2, 'data' => 'На сервере недоступна функция password_hash. Нужен PHP 5.5+ или новее.');
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if (!$hash) {
        return array('status' => 2, 'data' => 'Не удалось подготовить пароль Админцентра.');
    }
    try {
        $sth = $pdo->prepare("UPDATE `config` SET `admin_gate_login`=:login, `admin_gate_password_hash`=:password_hash LIMIT 1");
        $sth->execute(array(':login' => $login, ':password_hash' => $hash));
        pb_admin_gate_mark_verified($pdo, isset($_SESSION['id']) ? (int) $_SESSION['id'] : 0);
        return array('status' => 1, 'data' => 'Доступ к Админцентру сохранён.');
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_admin_gate_save_credentials: ' . $e->getMessage());
        }
        return array('status' => 2, 'data' => 'Ошибка сохранения доступа к Админцентру.');
    }
}

function pb_admin_gate_clear_credentials($pdo)
{
    pb_admin_gate_ensure_schema($pdo);
    try {
        $pdo->exec("UPDATE `config` SET `admin_gate_login`='', `admin_gate_password_hash`='' LIMIT 1");
        pb_admin_gate_reset_session();
        return array('status' => 1, 'data' => 'Дополнительный вход в Админцентр отключён.');
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_admin_gate_clear_credentials: ' . $e->getMessage());
        }
        return array('status' => 2, 'data' => 'Не удалось отключить дополнительный вход.');
    }
}

function pb_admin_gate_reset_session()
{
    unset($_SESSION['pb_admin_gate_uid'], $_SESSION['pb_admin_gate_started'], $_SESSION['pb_admin_gate_until']);
    unset($_SESSION['admin'], $_SESSION['admin_cache'], $_SESSION['dev_mode']);
}

function pb_admin_2fa_ensure_schema($pdo)
{
    static $done = false;

    if ($done || empty($pdo)) {
        return;
    }

    $done = true;

    try {
        $configColumns = [];
        $sth = $pdo->query("SHOW COLUMNS FROM `config__secondary`");
        if ($sth) {
            while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $configColumns[$row['Field']] = true;
            }
        }

        $configAlter = [];
        if (!isset($configColumns['tg_admin_2fa_enabled'])) {
            $configAlter[] = "ADD COLUMN `tg_admin_2fa_enabled` int NOT NULL DEFAULT '2'";
        }
        if (!isset($configColumns['tg_admin_2fa_bot_username'])) {
            $configAlter[] = "ADD COLUMN `tg_admin_2fa_bot_username` varchar(128) NOT NULL DEFAULT ''";
        }
        if (!isset($configColumns['tg_admin_2fa_bot_token'])) {
            $configAlter[] = "ADD COLUMN `tg_admin_2fa_bot_token` varchar(191) NOT NULL DEFAULT ''";
        }
        if (!isset($configColumns['tg_admin_2fa_code_ttl'])) {
            $configAlter[] = "ADD COLUMN `tg_admin_2fa_code_ttl` int NOT NULL DEFAULT '300'";
        }
        if (!isset($configColumns['tg_admin_2fa_session_ttl'])) {
            $configAlter[] = "ADD COLUMN `tg_admin_2fa_session_ttl` int NOT NULL DEFAULT '1800'";
        }

        if (!empty($configAlter)) {
            $pdo->exec("ALTER TABLE `config__secondary` " . implode(', ', $configAlter));
        }

        $userColumns = [];
        $sth = $pdo->query("SHOW COLUMNS FROM `users`");
        if ($sth) {
            while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $userColumns[$row['Field']] = true;
            }
        }

        $userAlter = [];
        if (!isset($userColumns['tg_admin_2fa_chat_id'])) {
            $userAlter[] = "ADD COLUMN `tg_admin_2fa_chat_id` varchar(64) NOT NULL DEFAULT ''";
        }
        if (!isset($userColumns['tg_admin_2fa_username'])) {
            $userAlter[] = "ADD COLUMN `tg_admin_2fa_username` varchar(128) NOT NULL DEFAULT ''";
        }
        if (!isset($userColumns['tg_admin_2fa_bind_code'])) {
            $userAlter[] = "ADD COLUMN `tg_admin_2fa_bind_code` varchar(96) NOT NULL DEFAULT ''";
        }
        if (!isset($userColumns['tg_admin_2fa_bind_expires'])) {
            $userAlter[] = "ADD COLUMN `tg_admin_2fa_bind_expires` int NOT NULL DEFAULT '0'";
        }
        if (!isset($userColumns['tg_admin_2fa_login_code'])) {
            $userAlter[] = "ADD COLUMN `tg_admin_2fa_login_code` varchar(16) NOT NULL DEFAULT ''";
        }
        if (!isset($userColumns['tg_admin_2fa_login_expires'])) {
            $userAlter[] = "ADD COLUMN `tg_admin_2fa_login_expires` int NOT NULL DEFAULT '0'";
        }
        if (!isset($userColumns['tg_admin_2fa_unbind_code'])) {
            $userAlter[] = "ADD COLUMN `tg_admin_2fa_unbind_code` varchar(16) NOT NULL DEFAULT ''";
        }
        if (!isset($userColumns['tg_admin_2fa_unbind_expires'])) {
            $userAlter[] = "ADD COLUMN `tg_admin_2fa_unbind_expires` int NOT NULL DEFAULT '0'";
        }

        if (!empty($userAlter)) {
            $pdo->exec("ALTER TABLE `users` " . implode(', ', $userAlter));
        }
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_admin_2fa_ensure_schema: ' . $e->getMessage());
        }
    }
}

function pb_admin_2fa_get_config($pdo)
{
    pb_admin_2fa_ensure_schema($pdo);

    $defaults = [
        'enabled' => 2,
        'bot_username' => '',
        'bot_token' => '',
        'code_ttl' => 300,
        'session_ttl' => 1800,
    ];

    try {
        $sth = $pdo->query("SELECT `tg_admin_2fa_enabled`, `tg_admin_2fa_bot_username`, `tg_admin_2fa_bot_token`, `tg_admin_2fa_code_ttl`, `tg_admin_2fa_session_ttl` FROM `config__secondary` LIMIT 1");
        $row = $sth ? $sth->fetch(PDO::FETCH_ASSOC) : false;
        if (!$row) {
            return $defaults;
        }

        $defaults['enabled'] = (int) $row['tg_admin_2fa_enabled'];
        $defaults['bot_username'] = trim((string) $row['tg_admin_2fa_bot_username']);
        $defaults['bot_username'] = ltrim($defaults['bot_username'], '@');
        $defaults['bot_token'] = trim((string) $row['tg_admin_2fa_bot_token']);
        $defaults['code_ttl'] = max(60, (int) $row['tg_admin_2fa_code_ttl']);
        $defaults['session_ttl'] = max(300, (int) $row['tg_admin_2fa_session_ttl']);
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_admin_2fa_get_config: ' . $e->getMessage());
        }
    }

    return $defaults;
}

function pb_admin_2fa_is_feature_ready($pdo)
{
    $config = pb_admin_2fa_get_config($pdo);
    return ((int) $config['enabled'] === 1) && $config['bot_username'] !== '' && $config['bot_token'] !== '';
}

function pb_admin_2fa_is_enabled_raw($pdo)
{
    $config = pb_admin_2fa_get_config($pdo);
    return (int) $config['enabled'] === 1;
}

function pb_admin_2fa_user_binding($pdo, $userId)
{
    pb_admin_2fa_ensure_schema($pdo);

    $data = [
        'chat_id' => '',
        'username' => '',
        'bind_code' => '',
        'bind_expires' => 0,
        'login_code' => '',
        'login_expires' => 0,
        'unbind_code' => '',
        'unbind_expires' => 0,
    ];

    try {
        $sth = $pdo->prepare("SELECT `tg_admin_2fa_chat_id`, `tg_admin_2fa_username`, `tg_admin_2fa_bind_code`, `tg_admin_2fa_bind_expires`, `tg_admin_2fa_login_code`, `tg_admin_2fa_login_expires`, `tg_admin_2fa_unbind_code`, `tg_admin_2fa_unbind_expires` FROM `users` WHERE `id`=:id LIMIT 1");
        $sth->execute([':id' => (int) $userId]);
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $data['chat_id'] = trim((string) $row['tg_admin_2fa_chat_id']);
            $data['username'] = trim((string) $row['tg_admin_2fa_username']);
            $data['bind_code'] = trim((string) $row['tg_admin_2fa_bind_code']);
            $data['bind_expires'] = (int) $row['tg_admin_2fa_bind_expires'];
            $data['login_code'] = trim((string) $row['tg_admin_2fa_login_code']);
            $data['login_expires'] = (int) $row['tg_admin_2fa_login_expires'];
            $data['unbind_code'] = trim((string) $row['tg_admin_2fa_unbind_code']);
            $data['unbind_expires'] = (int) $row['tg_admin_2fa_unbind_expires'];
        }
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_admin_2fa_user_binding: ' . $e->getMessage());
        }
    }

    return $data;
}

function pb_admin_2fa_user_is_bound($pdo, $userId)
{
    $binding = pb_admin_2fa_user_binding($pdo, $userId);
    return $binding['chat_id'] !== '';
}

function pb_admin_2fa_random_token($length = 24)
{
    try {
        return bin2hex(random_bytes(max(8, (int) ceil($length / 2))));
    } catch (Exception $e) {
        return md5(uniqid((string) mt_rand(), true));
    }
}

function pb_admin_2fa_random_code()
{
    try {
        return (string) random_int(100000, 999999);
    } catch (Exception $e) {
        return (string) mt_rand(100000, 999999);
    }
}

function pb_admin_2fa_bot_link($botUsername, $payload)
{
    $botUsername = ltrim(trim((string) $botUsername), '@');
    return 'https://t.me/' . $botUsername . '?start=' . rawurlencode($payload);
}

function pb_admin_2fa_telegram_request($botToken, $method, array $params = [])
{
    $botToken = trim((string) $botToken);
    if ($botToken === '') {
        return [
            'ok' => false,
            'description' => 'Не указан токен Telegram-бота.',
        ];
    }

    $url = 'https://api.telegram.org/bot' . $botToken . '/' . $method;
    $response = false;
    $error = '';

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($params),
                'timeout' => 20,
            ]
        ]);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            $error = 'На хостинге не удалось выполнить запрос к Telegram API.';
        }
    }

    if ($response === false || $error) {
        return [
            'ok' => false,
            'description' => $error ? $error : 'Не удалось выполнить запрос к Telegram API.',
        ];
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'description' => 'Telegram API вернул некорректный ответ.',
        ];
    }

    return $decoded;
}

function pb_admin_2fa_start_binding($pdo, $userId)
{
    $config = pb_admin_2fa_get_config($pdo);
    if ($config['bot_username'] === '' || $config['bot_token'] === '') {
        return [
            'status' => 2,
            'message' => 'Сначала настройте Telegram-бота в разделе «Безопасность».',
        ];
    }

    $bindCode = pb_admin_2fa_random_token(24);
    $expires = time() + 900;

    $sth = $pdo->prepare("UPDATE `users` SET `tg_admin_2fa_bind_code`=:code, `tg_admin_2fa_bind_expires`=:expires WHERE `id`=:id LIMIT 1");
    $sth->execute([
        ':code' => $bindCode,
        ':expires' => $expires,
        ':id' => (int) $userId,
    ]);

    return [
        'status' => 1,
        'bind_code' => $bindCode,
        'bot_link' => pb_admin_2fa_bot_link($config['bot_username'], 'bind_' . $bindCode),
        'message' => 'Откройте бота и нажмите Start. После этого привязка завершится автоматически.' ,
        'expires' => $expires,
    ];
}

function pb_admin_2fa_verify_binding($pdo, $userId)
{
    $config = pb_admin_2fa_get_config($pdo);
    $binding = pb_admin_2fa_user_binding($pdo, $userId);

    if ($binding['bind_code'] === '' || $binding['bind_expires'] < time()) {
        return [
            'status' => 2,
            'message' => 'Код привязки истёк. Сгенерируйте новый.',
        ];
    }

    $updates = pb_admin_2fa_telegram_request($config['bot_token'], 'getUpdates', ['limit' => 100, 'timeout' => 0]);
    if (empty($updates['ok'])) {
        return [
            'status' => 2,
            'message' => 'Не удалось получить ответ от Telegram: ' . (isset($updates['description']) ? $updates['description'] : 'неизвестная ошибка'),
        ];
    }

    $targetPayload = 'bind_' . $binding['bind_code'];
    $matched = null;
    if (!empty($updates['result']) && is_array($updates['result'])) {
        $updatesList = array_reverse($updates['result']);
        foreach ($updatesList as $update) {
            if (empty($update['message']['text']) || empty($update['message']['chat']['id'])) {
                continue;
            }

            $text = trim((string) $update['message']['text']);
            $date = isset($update['message']['date']) ? (int) $update['message']['date'] : 0;
            if ($date < (time() - 3600)) {
                continue;
            }

            if ($text === '/start ' . $targetPayload || $text === $targetPayload || mb_stripos($text, $targetPayload) !== false) {
                $matched = $update['message'];
                break;
            }
        }
    }

    if (!$matched) {
        return [
            'status' => 2,
            'message' => 'Привязка ещё не подтверждена. Нажмите Start у бота и повторите проверку.',
        ];
    }

    $chatId = (string) $matched['chat']['id'];
    $telegramUsername = '';
    if (!empty($matched['from']['username'])) {
        $telegramUsername = '@' . trim((string) $matched['from']['username']);
    } elseif (!empty($matched['from']['first_name'])) {
        $telegramUsername = trim((string) $matched['from']['first_name']);
    }

    $sth = $pdo->prepare("UPDATE `users` SET `tg_admin_2fa_chat_id`=:chat_id, `tg_admin_2fa_username`=:username, `tg_admin_2fa_bind_code`='', `tg_admin_2fa_bind_expires`='0' WHERE `id`=:id LIMIT 1");
    $sth->execute([
        ':chat_id' => $chatId,
        ':username' => $telegramUsername,
        ':id' => (int) $userId,
    ]);

    pb_admin_2fa_telegram_request($config['bot_token'], 'sendMessage', [
        'chat_id' => $chatId,
        'text' => 'Telegram успешно привязан к входу в Админцентр сайта «' . pb_tg_reg_site_name() . '».',
    ]);

    return [
        'status' => 1,
        'message' => 'Telegram успешно привязан.',
        'chat_id' => $chatId,
        'username' => $telegramUsername,
        'public_telegram' => pb_tg_reg_public_username($telegramUsername),
    ];
}


function pb_admin_2fa_start_unbind($pdo, $userId, $siteName = '')
{
    $config = pb_admin_2fa_get_config($pdo);
    $binding = pb_admin_2fa_user_binding($pdo, $userId);

    if ($binding['chat_id'] === '') {
        return [
            'status' => 2,
            'message' => 'Telegram ещё не привязан.',
        ];
    }

    $code = pb_admin_2fa_random_code();
    $expires = time() + (int) $config['code_ttl'];

    $sth = $pdo->prepare("UPDATE `users` SET `tg_admin_2fa_unbind_code`=:code, `tg_admin_2fa_unbind_expires`=:expires WHERE `id`=:id LIMIT 1");
    $sth->execute([
        ':code' => $code,
        ':expires' => $expires,
        ':id' => (int) $userId,
    ]);

    $text = "Код подтверждения для отвязки Telegram";
    if ($siteName !== '') {
        $text .= " на сайте «" . $siteName . "»";
    }
    $text .= ": " . $code . "
Код действует " . max(1, (int) floor($config['code_ttl'] / 60)) . " мин.";

    $result = pb_admin_2fa_telegram_request($config['bot_token'], 'sendMessage', [
        'chat_id' => $binding['chat_id'],
        'text' => $text,
    ]);

    if (empty($result['ok'])) {
        return [
            'status' => 2,
            'message' => 'Не удалось отправить код в Telegram: ' . (isset($result['description']) ? $result['description'] : 'неизвестная ошибка'),
        ];
    }

    return [
        'status' => 1,
        'message' => 'Код подтверждения отправлен в Telegram. Введите его ниже, чтобы отвязать аккаунт.',
        'expires' => $expires,
    ];
}

function pb_admin_2fa_confirm_unbind($pdo, $userId, $code)
{
    $binding = pb_admin_2fa_user_binding($pdo, $userId);
    $code = trim((string) $code);

    if ($binding['chat_id'] === '') {
        return [
            'status' => 2,
            'message' => 'Telegram уже отвязан.',
        ];
    }

    if ($code === '' || $binding['unbind_code'] === '' || $binding['unbind_expires'] < time()) {
        return [
            'status' => 2,
            'message' => 'Код истёк или ещё не был отправлен.',
        ];
    }

    if ($code !== trim((string) $binding['unbind_code'])) {
        return [
            'status' => 2,
            'message' => 'Введён неверный код.',
        ];
    }

    return pb_admin_2fa_unbind($pdo, $userId);
}

function pb_admin_2fa_unbind($pdo, $userId)
{
    $sth = $pdo->prepare("UPDATE `users` SET `tg_admin_2fa_chat_id`='', `tg_admin_2fa_username`='', `tg_admin_2fa_bind_code`='', `tg_admin_2fa_bind_expires`='0', `tg_admin_2fa_login_code`='', `tg_admin_2fa_login_expires`='0', `tg_admin_2fa_unbind_code`='', `tg_admin_2fa_unbind_expires`='0' WHERE `id`=:id LIMIT 1");
    $sth->execute([':id' => (int) $userId]);
    unset($_SESSION['pb_admin_2fa_until'], $_SESSION['pb_admin_2fa_uid']);

    return [
        'status' => 1,
        'message' => 'Привязка Telegram удалена.',
    ];
}

function pb_admin_2fa_send_login_code($pdo, $userId, $siteName = '')
{
    $config = pb_admin_2fa_get_config($pdo);
    $binding = pb_admin_2fa_user_binding($pdo, $userId);

    if ((int) $config['enabled'] !== 1) {
        return [
            'status' => 2,
            'message' => 'Двухэтапная защита для Админцентра сейчас выключена.',
        ];
    }

    if ($binding['chat_id'] === '') {
        return [
            'status' => 2,
            'message' => 'Сначала привяжите Telegram в настройках аккаунта.',
        ];
    }

    $code = pb_admin_2fa_random_code();
    $expires = time() + (int) $config['code_ttl'];

    $sth = $pdo->prepare("UPDATE `users` SET `tg_admin_2fa_login_code`=:code, `tg_admin_2fa_login_expires`=:expires WHERE `id`=:id LIMIT 1");
    $sth->execute([
        ':code' => $code,
        ':expires' => $expires,
        ':id' => (int) $userId,
    ]);

    $text = "Код входа в Админцентр";
    if ($siteName !== '') {
        $text .= " «" . $siteName . "»";
    }
    $text .= ": " . $code . "
Код действует " . max(1, (int) floor($config['code_ttl'] / 60)) . " мин.";

    $result = pb_admin_2fa_telegram_request($config['bot_token'], 'sendMessage', [
        'chat_id' => $binding['chat_id'],
        'text' => $text,
    ]);

    if (empty($result['ok'])) {
        return [
            'status' => 2,
            'message' => 'Не удалось отправить код в Telegram: ' . (isset($result['description']) ? $result['description'] : 'неизвестная ошибка'),
        ];
    }

    return [
        'status' => 1,
        'message' => 'Код отправлен в Telegram. Проверьте бот и введите его ниже.',
        'expires' => $expires,
    ];
}

function pb_admin_2fa_verify_login_code($pdo, $userId, $code)
{
    global $SC, $conf, $dev_mode;

    $config = pb_admin_2fa_get_config($pdo);
    $binding = pb_admin_2fa_user_binding($pdo, $userId);
    $code = trim((string) $code);

    if ($code === '' || $binding['login_code'] === '' || $binding['login_expires'] < time()) {
        return [
            'status' => 2,
            'message' => 'Код истёк или ещё не был отправлен.',
        ];
    }

    if ($code !== trim((string) $binding['login_code'])) {
        return [
            'status' => 2,
            'message' => 'Введён неверный код.',
        ];
    }

    $sth = $pdo->prepare("UPDATE `users` SET `tg_admin_2fa_login_code`='', `tg_admin_2fa_login_expires`='0' WHERE `id`=:id LIMIT 1");
    $sth->execute([':id' => (int) $userId]);

    if ($conf->ip_protect == 1) {
        $SC->admin_ip = get_ip();
    }

    $_SESSION['admin'] = 1;
    $_SESSION['admin_cache'] = $SC->get_admin_cache($conf->password);
    $_SESSION['dev_mode'] = $dev_mode;
    $_SESSION['pb_admin_2fa_until'] = time() + 3600;
    $_SESSION['pb_admin_2fa_uid'] = (int) $userId;

    return [
        'status' => 1,
        'message' => 'Вход подтверждён. Перенаправляем в Админцентр.',
        'redirect' => '../admin',
    ];
}

function pb_admin_2fa_session_valid($pdo)
{
    if (!pb_admin_2fa_is_feature_ready($pdo)) {
        return true;
    }

    if (!is_auth() || !is_worthy('h')) {
        pb_admin_2fa_reset_session();
        return false;
    }

    if (empty($_SESSION['pb_admin_2fa_until']) || empty($_SESSION['pb_admin_2fa_uid'])) {
        return false;
    }

    if ((int) $_SESSION['pb_admin_2fa_uid'] !== (int) $_SESSION['id']) {
        pb_admin_2fa_reset_session();
        return false;
    }

    if ((int) $_SESSION['pb_admin_2fa_until'] < time()) {
        pb_admin_2fa_reset_session();
        return false;
    }

    return true;
}

function pb_admin_2fa_admin_access_allowed($pdo)
{
    if (function_exists('pb_admin_gate_configured') && pb_admin_gate_configured($pdo) && !pb_admin_gate_session_valid($pdo)) {
        return false;
    }

    if (!pb_admin_2fa_is_feature_ready($pdo)) {
        return true;
    }

    if (!is_auth() || !is_worthy('h')) {
        return false;
    }

    if (is_admin_id() && !pb_admin_2fa_user_is_bound($pdo, $_SESSION['id'])) {
        return true;
    }

    return is_admin() && pb_admin_2fa_session_valid($pdo);
}

function pb_admin_2fa_confirmation_required($pdo)
{
    if (!pb_admin_2fa_is_feature_ready($pdo)) {
        return false;
    }

    if (!is_auth() || !is_worthy('h')) {
        return false;
    }

    if (is_admin_id() && !pb_admin_2fa_user_is_bound($pdo, $_SESSION['id'])) {
        return false;
    }

    return !pb_admin_2fa_admin_access_allowed($pdo);
}

function pb_admin_2fa_reset_session()
{
    unset($_SESSION['pb_admin_2fa_until'], $_SESSION['pb_admin_2fa_uid']);
}


function pb_tg_reg_ensure_schema($pdo)
{
    static $done = false;

    if ($done || empty($pdo)) {
        return;
    }

    $done = true;

    try {
        $configColumns = [];
        $sth = $pdo->query("SHOW COLUMNS FROM `config__secondary`");
        if ($sth) {
            while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $configColumns[$row['Field']] = true;
            }
        }

        $configAlter = [];
        if (!isset($configColumns['tg_reg_mode'])) {
            $configAlter[] = "ADD COLUMN `tg_reg_mode` int NOT NULL DEFAULT '0'";
        }
        if (!isset($configColumns['tg_reg_apply_to'])) {
            $configAlter[] = "ADD COLUMN `tg_reg_apply_to` int NOT NULL DEFAULT '0'";
        }
        if (!isset($configColumns['tg_reg_bot_username'])) {
            $configAlter[] = "ADD COLUMN `tg_reg_bot_username` varchar(128) NOT NULL DEFAULT ''";
        }
        if (!isset($configColumns['tg_reg_bot_token'])) {
            $configAlter[] = "ADD COLUMN `tg_reg_bot_token` varchar(191) NOT NULL DEFAULT ''";
        }
        if (!isset($configColumns['tg_reg_hint_text'])) {
            $configAlter[] = "ADD COLUMN `tg_reg_hint_text` varchar(500) NOT NULL DEFAULT ''";
        }

        if (!empty($configAlter)) {
            $pdo->exec("ALTER TABLE `config__secondary` " . implode(', ', $configAlter));
        }

        $userColumns = [];
        $sth = $pdo->query("SHOW COLUMNS FROM `users`");
        if ($sth) {
            while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $userColumns[$row['Field']] = true;
            }
        }

        $userAlter = [];
        if (!isset($userColumns['tg_reg_chat_id'])) {
            $userAlter[] = "ADD COLUMN `tg_reg_chat_id` varchar(64) NOT NULL DEFAULT ''";
        }
        if (!isset($userColumns['tg_reg_username'])) {
            $userAlter[] = "ADD COLUMN `tg_reg_username` varchar(128) NOT NULL DEFAULT ''";
        }
        if (!isset($userColumns['tg_reg_bind_code'])) {
            $userAlter[] = "ADD COLUMN `tg_reg_bind_code` varchar(96) NOT NULL DEFAULT ''";
        }
        if (!isset($userColumns['tg_reg_bind_expires'])) {
            $userAlter[] = "ADD COLUMN `tg_reg_bind_expires` int NOT NULL DEFAULT '0'";
        }
        if (!isset($userColumns['tg_reg_unbind_code'])) {
            $userAlter[] = "ADD COLUMN `tg_reg_unbind_code` varchar(16) NOT NULL DEFAULT ''";
        }
        if (!isset($userColumns['tg_reg_unbind_expires'])) {
            $userAlter[] = "ADD COLUMN `tg_reg_unbind_expires` int NOT NULL DEFAULT '0'";
        }
        if (!isset($userColumns['tg_reg_required'])) {
            $userAlter[] = "ADD COLUMN `tg_reg_required` int NOT NULL DEFAULT '0'";
        }
        if (!isset($userColumns['tg_reg_source'])) {
            $userAlter[] = "ADD COLUMN `tg_reg_source` varchar(16) NOT NULL DEFAULT ''";
        }

        if (!empty($userAlter)) {
            $pdo->exec("ALTER TABLE `users` " . implode(', ', $userAlter));
        }
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_tg_reg_ensure_schema: ' . $e->getMessage());
        }
    }
}


function pb_profile_status_ensure_schema($pdo)
{
    static $done = false;

    if ($done || empty($pdo)) {
        return;
    }

    $done = true;

    try {
        $userColumns = [];
        $sth = $pdo->query("SHOW COLUMNS FROM `users`");
        if ($sth) {
            while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $userColumns[$row['Field']] = true;
            }
        }

        $userAlter = [];
        if (!isset($userColumns['profile_status'])) {
            $userAlter[] = "ADD COLUMN `profile_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''";
        }

        if (!empty($userAlter)) {
            $pdo->exec("ALTER TABLE `users` " . implode(', ', $userAlter));
        }

        if (isset($userColumns['profile_status'])) {
            $pdo->exec("ALTER TABLE `users` MODIFY `profile_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''");
        }
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_profile_status_ensure_schema: ' . $e->getMessage());
        }
    }
}

function pb_verification_ensure_schema($pdo)
{
    static $done = false;
    if ($done || empty($pdo)) {
        return;
    }
    $done = true;

    try {
        $cols = [];
        $sth = $pdo->query("SHOW COLUMNS FROM `config__secondary`");
        if ($sth) {
            while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $cols[$row['Field']] = true;
            }
        }
        $alter = [];
        if (!isset($cols['very_title'])) {
            $alter[] = "ADD COLUMN `very_title` varchar(255) NOT NULL DEFAULT ''";
        }
        if (!isset($cols['very_text'])) {
            $alter[] = "ADD COLUMN `very_text` text NOT NULL";
        }
        if (!isset($cols['very_img'])) {
            $alter[] = "ADD COLUMN `very_img` varchar(255) NOT NULL DEFAULT ''";
        }
        if (!empty($alter)) {
            $pdo->exec("ALTER TABLE `config__secondary` " . implode(', ', $alter));
        }
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_verification_ensure_schema: ' . $e->getMessage());
        }
    }
}

function pb_verification_get_config($pdo)
{
    pb_verification_ensure_schema($pdo);

    $defaults = [
        'very_title' => 'Верификация аккаунта',
        'very_text'  => 'Подайте заявку, и после одобрения администрацией возле вашего логина появится знак подтверждённого профиля.',
        'very_img'   => '',
    ];

    $sth = $pdo->query("SELECT `very_title`, `very_text`, `very_img` FROM `config__secondary` LIMIT 1");
    if (!$sth) {
        return $defaults;
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return $defaults;
    }

    foreach ($defaults as $key => $def) {
        if (!isset($row[$key]) || $row[$key] === '') {
            $row[$key] = $def;
        }
    }

    return $row;
}

function pb_tg_reg_get_config($pdo)
{
    pb_tg_reg_ensure_schema($pdo);

    $defaults = [
        'mode' => 0,
        'apply_to' => 0,
        'bot_username' => '',
        'bot_token' => '',
        'hint_text' => 'Привяжите Telegram, чтобы быстрее восстанавливать доступ и получать важные уведомления по аккаунту.',
    ];

    try {
        $sth = $pdo->query("SELECT `tg_reg_mode`, `tg_reg_apply_to`, `tg_reg_bot_username`, `tg_reg_bot_token`, `tg_reg_hint_text` FROM `config__secondary` LIMIT 1");
        $row = $sth ? $sth->fetch(PDO::FETCH_ASSOC) : false;
        if (!$row) {
            return $defaults;
        }

        $defaults['mode'] = max(0, min(2, (int) $row['tg_reg_mode']));
        $defaults['apply_to'] = max(0, min(2, (int) $row['tg_reg_apply_to']));
        $defaults['bot_username'] = ltrim(trim((string) $row['tg_reg_bot_username']), '@');
        $defaults['bot_token'] = trim((string) $row['tg_reg_bot_token']);
        $defaults['hint_text'] = trim((string) $row['tg_reg_hint_text']);
        if ($defaults['hint_text'] === '') {
            $defaults['hint_text'] = 'Привяжите Telegram, чтобы быстрее восстанавливать доступ и получать важные уведомления по аккаунту.';
        }
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_tg_reg_get_config: ' . $e->getMessage());
        }
    }

    return $defaults;
}

function pb_tg_reg_is_feature_ready($pdo)
{
    $config = pb_tg_reg_get_config($pdo);
    return ($config['mode'] > 0 && $config['bot_username'] !== '' && $config['bot_token'] !== '');
}

function pb_tg_reg_should_apply_to_source(array $config, $source)
{
    $source = trim((string) $source);
    if ($source === '') {
        return false;
    }

    if ((int) $config['apply_to'] === 0) {
        return in_array($source, ['email', 'steam'], true);
    }

    if ((int) $config['apply_to'] === 1) {
        return $source === 'email';
    }

    if ((int) $config['apply_to'] === 2) {
        return $source === 'steam';
    }

    return false;
}

function pb_tg_reg_matches_admin_bot($pdo)
{
    if (!function_exists('pb_admin_2fa_get_config')) {
        return false;
    }

    $config = pb_tg_reg_get_config($pdo);
    $admin = pb_admin_2fa_get_config($pdo);

    return ($config['bot_username'] !== '' && $config['bot_token'] !== ''
        && $config['bot_username'] === $admin['bot_username']
        && $config['bot_token'] === $admin['bot_token']);
}

function pb_tg_reg_site_name()
{
    if (function_exists('configs')) {
        try {
            $name = trim((string) configs()->name);
            if ($name !== '') {
                return $name;
            }
        } catch (Exception $e) {
        }
    }

    global $conf;
    if (isset($conf->name)) {
        $name = trim((string) $conf->name);
        if ($name !== '') {
            return $name;
        }
    }

    return 'PBGame CMS';
}

function pb_tg_reg_public_username($telegramUsername)
{
    $telegramUsername = ltrim(trim((string) $telegramUsername), '@');
    if ($telegramUsername === '') {
        return '';
    }

    return mb_substr($telegramUsername, 0, 32);
}

function pb_tg_reg_sync_public_telegram($pdo, $userId, $telegramUsername)
{
    $publicTelegram = pb_tg_reg_public_username($telegramUsername);
    if ($publicTelegram === '') {
        return;
    }

    try {
        $pdo->prepare("UPDATE `users` SET `telegram`=:telegram WHERE `id`=:id LIMIT 1")
            ->execute([
                ':telegram' => $publicTelegram,
                ':id' => (int) $userId,
            ]);
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_tg_reg_sync_public_telegram: ' . $e->getMessage());
        }
    }
}

function pb_tg_reg_user_binding($pdo, $userId)
{
    pb_tg_reg_ensure_schema($pdo);

    $data = [
        'chat_id' => '',
        'username' => '',
        'bind_code' => '',
        'bind_expires' => 0,
        'unbind_code' => '',
        'unbind_expires' => 0,
        'required' => 0,
        'source' => '',
    ];

    try {
        $sth = $pdo->prepare("SELECT `tg_reg_chat_id`, `tg_reg_username`, `tg_reg_bind_code`, `tg_reg_bind_expires`, `tg_reg_unbind_code`, `tg_reg_unbind_expires`, `tg_reg_required`, `tg_reg_source`, `tg_admin_2fa_chat_id`, `tg_admin_2fa_username` FROM `users` WHERE `id`=:id LIMIT 1");
        $sth->execute([':id' => (int) $userId]);
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $data['chat_id'] = trim((string) $row['tg_reg_chat_id']);
            $data['username'] = trim((string) $row['tg_reg_username']);
            $data['bind_code'] = trim((string) $row['tg_reg_bind_code']);
            $data['bind_expires'] = (int) $row['tg_reg_bind_expires'];
            $data['unbind_code'] = trim((string) $row['tg_reg_unbind_code']);
            $data['unbind_expires'] = (int) $row['tg_reg_unbind_expires'];
            $data['required'] = (int) $row['tg_reg_required'];
            $data['source'] = trim((string) $row['tg_reg_source']);

            if ($data['chat_id'] === '' && pb_tg_reg_matches_admin_bot($pdo) && !empty($row['tg_admin_2fa_chat_id'])) {
                $data['chat_id'] = trim((string) $row['tg_admin_2fa_chat_id']);
                $data['username'] = trim((string) $row['tg_admin_2fa_username']);
                $pdo->prepare("UPDATE `users` SET `tg_reg_chat_id`=:chat_id, `tg_reg_username`=:username WHERE `id`=:id LIMIT 1")
                    ->execute([
                        ':chat_id' => $data['chat_id'],
                        ':username' => $data['username'],
                        ':id' => (int) $userId,
                    ]);
                pb_tg_reg_sync_public_telegram($pdo, $userId, $data['username']);
            }
        }
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_tg_reg_user_binding: ' . $e->getMessage());
        }
    }

    return $data;
}

function pb_tg_reg_user_is_bound($pdo, $userId)
{
    $binding = pb_tg_reg_user_binding($pdo, $userId);
    return $binding['chat_id'] !== '';
}

function pb_tg_reg_mark_new_user($pdo, $userId, $source)
{
    pb_tg_reg_ensure_schema($pdo);

    $source = trim((string) $source);
    if ($source === '') {
        $source = 'email';
    }

    $config = pb_tg_reg_get_config($pdo);
    $required = 0;
    if ((int) $config['mode'] === 2 && pb_tg_reg_should_apply_to_source($config, $source)) {
        $required = 1;
    }

    try {
        $pdo->prepare("UPDATE `users` SET `tg_reg_source`=:source, `tg_reg_required`=:required WHERE `id`=:id LIMIT 1")
            ->execute([
                ':source' => $source,
                ':required' => $required,
                ':id' => (int) $userId,
            ]);
    } catch (Exception $e) {
        if (function_exists('write_log')) {
            write_log('pb_tg_reg_mark_new_user: ' . $e->getMessage());
        }
    }
}

function pb_tg_reg_registration_redirect_url($pdo, $userId, $fullSiteHost)
{
    $config = pb_tg_reg_get_config($pdo);
    if ((int) $config['mode'] <= 0 || !pb_tg_reg_is_feature_ready($pdo)) {
        return '';
    }

    $binding = pb_tg_reg_user_binding($pdo, $userId);
    if ($binding['chat_id'] !== '') {
        return '';
    }

    if (!pb_tg_reg_should_apply_to_source($config, $binding['source'])) {
        return '';
    }

    $suffix = ((int) $config['mode'] === 2)
        ? 'settings/?telegram_bind_required=1#telegram-registration-box'
        : 'settings/?telegram_bind_prompt=1#telegram-registration-box';

    return rtrim((string) $fullSiteHost, '/') . '/' . $suffix;
}

function pb_tg_reg_registration_redirect_response($pdo, $userId, $fullSiteHost)
{
    $url = pb_tg_reg_registration_redirect_url($pdo, $userId, $fullSiteHost);
    if ($url === '') {
        return '';
    }

    return '<script>window.location.href=' . json_encode($url) . ';</script>';
}

function pb_tg_reg_start_binding($pdo, $userId)
{
    $config = pb_tg_reg_get_config($pdo);
    if ($config['bot_username'] === '' || $config['bot_token'] === '') {
        return [
            'status' => 2,
            'message' => 'Владелец сайта ещё не настроил Telegram-бота для регистрации.',
        ];
    }

    $bindCode = pb_admin_2fa_random_token(24);
    $expires = time() + 900;

    $sth = $pdo->prepare("UPDATE `users` SET `tg_reg_bind_code`=:code, `tg_reg_bind_expires`=:expires WHERE `id`=:id LIMIT 1");
    $sth->execute([
        ':code' => $bindCode,
        ':expires' => $expires,
        ':id' => (int) $userId,
    ]);

    return [
        'status' => 1,
        'bind_code' => $bindCode,
        'bot_link' => pb_admin_2fa_bot_link($config['bot_username'], 'bind_' . $bindCode),
        'message' => 'Откройте бота и нажмите Start. После этого привязка завершится автоматически.',
        'expires' => $expires,
    ];
}

function pb_tg_reg_verify_binding($pdo, $userId)
{
    $config = pb_tg_reg_get_config($pdo);
    $binding = pb_tg_reg_user_binding($pdo, $userId);

    if ($binding['bind_code'] === '' || $binding['bind_expires'] < time()) {
        return [
            'status' => 2,
            'message' => 'Код привязки истёк. Сгенерируйте новый.',
        ];
    }

    $updates = pb_admin_2fa_telegram_request($config['bot_token'], 'getUpdates', ['limit' => 100, 'timeout' => 0]);
    if (empty($updates['ok'])) {
        return [
            'status' => 2,
            'message' => 'Не удалось получить ответ от Telegram: ' . (isset($updates['description']) ? $updates['description'] : 'неизвестная ошибка'),
        ];
    }

    $targetPayload = 'bind_' . $binding['bind_code'];
    $matched = null;
    if (!empty($updates['result']) && is_array($updates['result'])) {
        foreach (array_reverse($updates['result']) as $update) {
            if (empty($update['message']['text']) || empty($update['message']['chat']['id'])) {
                continue;
            }

            $text = trim((string) $update['message']['text']);
            $date = isset($update['message']['date']) ? (int) $update['message']['date'] : 0;
            if ($date < (time() - 3600)) {
                continue;
            }

            if ($text === '/start ' . $targetPayload || $text === $targetPayload || mb_stripos($text, $targetPayload) !== false) {
                $matched = $update['message'];
                break;
            }
        }
    }

    if (!$matched) {
        return [
            'status' => 2,
            'message' => 'Привязка ещё не подтверждена. Нажмите Start у бота и повторите проверку.',
        ];
    }

    $chatId = (string) $matched['chat']['id'];
    $telegramUsername = '';
    if (!empty($matched['from']['username'])) {
        $telegramUsername = '@' . trim((string) $matched['from']['username']);
    } elseif (!empty($matched['from']['first_name'])) {
        $telegramUsername = trim((string) $matched['from']['first_name']);
    }

    $sth = $pdo->prepare("UPDATE `users` SET `tg_reg_chat_id`=:chat_id, `tg_reg_username`=:username, `tg_reg_bind_code`='', `tg_reg_bind_expires`='0' WHERE `id`=:id LIMIT 1");
    $sth->execute([
        ':chat_id' => $chatId,
        ':username' => $telegramUsername,
        ':id' => (int) $userId,
    ]);

    if (pb_tg_reg_matches_admin_bot($pdo)) {
        $pdo->prepare("UPDATE `users` SET `tg_admin_2fa_chat_id`=IF(`tg_admin_2fa_chat_id`='', :chat_id, `tg_admin_2fa_chat_id`), `tg_admin_2fa_username`=IF(`tg_admin_2fa_username`='', :username, `tg_admin_2fa_username`) WHERE `id`=:id LIMIT 1")
            ->execute([
                ':chat_id' => $chatId,
                ':username' => $telegramUsername,
                ':id' => (int) $userId,
            ]);
    }

    pb_tg_reg_sync_public_telegram($pdo, $userId, $telegramUsername);

    pb_admin_2fa_telegram_request($config['bot_token'], 'sendMessage', [
        'chat_id' => $chatId,
        'text' => 'Telegram успешно привязан к аккаунту на сайте «' . pb_tg_reg_site_name() . '».',
    ]);

    return [
        'status' => 1,
        'message' => 'Telegram успешно привязан.',
        'chat_id' => $chatId,
        'username' => $telegramUsername,
        'public_telegram' => pb_tg_reg_public_username($telegramUsername),
    ];
}

function pb_tg_reg_start_unbind($pdo, $userId, $siteName = '')
{
    $config = pb_tg_reg_get_config($pdo);
    $binding = pb_tg_reg_user_binding($pdo, $userId);

    if ($binding['chat_id'] === '') {
        return [
            'status' => 2,
            'message' => 'Telegram ещё не привязан.',
        ];
    }

    $code = pb_admin_2fa_random_code();
    $expires = time() + 300;

    $sth = $pdo->prepare("UPDATE `users` SET `tg_reg_unbind_code`=:code, `tg_reg_unbind_expires`=:expires WHERE `id`=:id LIMIT 1");
    $sth->execute([
        ':code' => $code,
        ':expires' => $expires,
        ':id' => (int) $userId,
    ]);

    $text = "Код подтверждения для отвязки Telegram";
    if ($siteName !== '') {
        $text .= " на сайте «" . $siteName . "»";
    }
    $text .= ": " . $code . "\nКод действует 5 мин.";

    $result = pb_admin_2fa_telegram_request($config['bot_token'], 'sendMessage', [
        'chat_id' => $binding['chat_id'],
        'text' => $text,
    ]);

    if (empty($result['ok'])) {
        return [
            'status' => 2,
            'message' => 'Не удалось отправить код в Telegram: ' . (isset($result['description']) ? $result['description'] : 'неизвестная ошибка'),
        ];
    }

    return [
        'status' => 1,
        'message' => 'Код подтверждения отправлен в Telegram. Введите его ниже, чтобы отвязать аккаунт.',
        'expires' => $expires,
    ];
}

function pb_tg_reg_confirm_unbind($pdo, $userId, $code)
{
    $binding = pb_tg_reg_user_binding($pdo, $userId);
    $code = trim((string) $code);

    if ($binding['chat_id'] === '') {
        return [
            'status' => 2,
            'message' => 'Telegram ещё не привязан.',
        ];
    }

    if ($binding['unbind_code'] === '' || $binding['unbind_expires'] < time()) {
        return [
            'status' => 2,
            'message' => 'Код отвязки истёк. Запросите новый.',
        ];
    }

    if ($code === '' || $code !== $binding['unbind_code']) {
        return [
            'status' => 2,
            'message' => 'Неверный код подтверждения.',
        ];
    }

    $pdo->prepare("UPDATE `users` SET `tg_reg_chat_id`='', `tg_reg_username`='', `tg_reg_bind_code`='', `tg_reg_bind_expires`='0', `tg_reg_unbind_code`='', `tg_reg_unbind_expires`='0' WHERE `id`=:id LIMIT 1")
        ->execute([':id' => (int) $userId]);

    return [
        'status' => 1,
        'message' => 'Telegram успешно отвязан.',
    ];
}

function pb_tg_reg_gate_required_user($pdo, $user, $fullSiteHost)
{
    if (empty($user) || empty($user->id) || (php_sapi_name() === 'cli')) {
        return;
    }

    $config = pb_tg_reg_get_config($pdo);
    if ((int) $config['mode'] !== 2 || !pb_tg_reg_is_feature_ready($pdo)) {
        return;
    }

    if (empty($user->tg_reg_required) || pb_tg_reg_user_is_bound($pdo, (int) $user->id)) {
        return;
    }

    if (!empty($_SERVER['REQUEST_METHOD']) && strtoupper((string) $_SERVER['REQUEST_METHOD']) !== 'GET') {
        return;
    }

    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    $exemptFragments = [
        '/settings',
        '/ajax/',
        '/admin',
        '/admin-confirm',
        '/install',
        '/cron',
        '/exit',
    ];
    foreach ($exemptFragments as $fragment) {
        if ($uri !== '' && strpos($uri, $fragment) !== false) {
            return;
        }
    }

    header('Location: ' . rtrim((string) $fullSiteHost, '/') . '/settings/?telegram_bind_required=1#telegram-registration-box');
    exit();
}



function pb_dev_terminal_default_data()
{
    return [
        'boot_text' => '',
        'help_text' => '',
        'notes' => ''
    ];
}

function pb_dev_terminal_load_data()
{
    static $cached = null;
    if($cached !== null) {
        return $cached;
    }

    $defaults = pb_dev_terminal_default_data();
    $externalFile = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/inc/configs/dev_console_registry.php';

    if(file_exists($externalFile)) {
        $external = include $externalFile;
        if(is_array($external) && isset($external['__terminal']) && is_array($external['__terminal'])) {
            $cached = array_merge($defaults, $external['__terminal']);
            foreach($cached as $key => $value) {
                $cached[$key] = trim((string) $value);
            }
            return $cached;
        }
    }

    $cached = $defaults;
    return $cached;
}

function pb_dev_terminal_save_data($data)
{
    return false;
}

function pb_dev_console_registry()
{
    $registry = [
		'help' => [
			'title' => 'Список команд',
			'description' => 'Показывает доступные внутренние команды PBGame CMS.',
			'aliases' => ['команды', 'справка', '?'],
			'handler' => 'pb_dev_console_cmd_help'
		],
		'version' => [
			'title' => 'Версия движка',
			'description' => 'Показывает версию PBGame CMS, шаблон, сайт и PHP.',
			'aliases' => ['версия'],
			'handler' => 'pb_dev_console_cmd_version'
		],
		'pages' => [
			'title' => 'Страницы',
			'description' => 'Показывает сводку по страницам движка.',
			'aliases' => ['страницы'],
			'handler' => 'pb_dev_console_cmd_pages'
		],
		'pages-list' => [
			'title' => 'Список страниц',
			'description' => 'Показывает список активных страниц. Можно указать лимит: pages-list 20',
			'aliases' => ['pages_list', 'страницы-список'],
			'handler' => 'pb_dev_console_cmd_pages_list'
		],
		'modules' => [
			'title' => 'Модули',
			'description' => 'Показывает сводку по установленным модулям.',
			'aliases' => ['модули'],
			'handler' => 'pb_dev_console_cmd_modules'
		],
		'modules-list' => [
			'title' => 'Список модулей',
			'description' => 'Показывает список модулей. Можно указать лимит: modules-list 20',
			'aliases' => ['modules_list', 'модули-список'],
			'handler' => 'pb_dev_console_cmd_modules_list'
		],
		'users' => [
			'title' => 'Пользователи',
			'description' => 'Показывает общую сводку по пользователям и группам.',
			'aliases' => ['пользователи'],
			'handler' => 'pb_dev_console_cmd_users'
		],
		'system' => [
			'title' => 'Система',
			'description' => 'Показывает основные системные параметры PBGame CMS.',
			'aliases' => ['система'],
			'handler' => 'pb_dev_console_cmd_system'
		],
		'doctor' => [
			'title' => 'Doctor',
			'description' => 'Проверяет частые проблемы: PHP, таблицы, папки, шаблон, режимы и логи.',
			'aliases' => ['check', 'диагностика'],
			'handler' => 'pb_dev_console_cmd_doctor'
		],
	];

    $externalFile = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/inc/configs/dev_console_registry.php';
    if(file_exists($externalFile)) {
        $external = include $externalFile;
        if(is_array($external)) {
            foreach($external as $commandKey => $meta) {
                if($commandKey === '__terminal') {
                    continue;
                }
                if(!is_array($meta)) {
                    continue;
                }
                $commandKey = trim((string) $commandKey);
                if($commandKey === '') {
                    continue;
                }
                if(!isset($meta['handler']) || trim((string) $meta['handler']) === '') {
                    continue;
                }
                if(!isset($meta['aliases']) || !is_array($meta['aliases'])) {
                    $meta['aliases'] = [];
                }
                $registry[$commandKey] = $meta;
            }
        }
    }

	return $registry;
}

function pb_dev_console_normalize_command($command)
{
	$command = trim((string) $command);
	if($command === '') {
		return '';
	}
	if(function_exists('mb_strtolower')) {
		return mb_strtolower($command, 'UTF-8');
	}
	return strtolower($command);
}

function pb_dev_console_limit($value, $default = 10, $min = 1, $max = 50)
{
	$value = (int) $value;
	if($value <= 0) {
		$value = $default;
	}
	if($value < $min) {
		$value = $min;
	}
	if($value > $max) {
		$value = $max;
	}
	return $value;
}

function pb_dev_console_help_text()
{
	$lines = [
		'Доступные команды PBGame CMS:',
		''
	];
	foreach(pb_dev_console_registry() as $key => $meta) {
		$aliases = '';
		if(!empty($meta['aliases'])) {
			$aliases = ' | алиасы: ' . implode(', ', $meta['aliases']);
		}
		$lines[] = str_pad($key, 14, ' ') . ' - ' . $meta['description'] . $aliases;
	}
	$lines[] = '';
	$lines[] = 'Пример: pages-list 10';

    $terminalData = pb_dev_terminal_load_data();
    if(!empty($terminalData['help_text'])) {
        $lines[] = '';
        $lines[] = 'Дополнительно:';
        $lines[] = trim((string) $terminalData['help_text']);
    }
	return implode("\n", $lines);
}

function pb_dev_console_boot_text($pdo)
{
	$siteName = '';
	$template = '';
	$timeZone = '';
	global $conf;
	if(isset($conf) && is_object($conf)) {
		$siteName = isset($conf->name) ? (string) $conf->name : '';
		$template = isset($conf->template) ? (string) $conf->template : '';
		$timeZone = isset($conf->time_zone) ? (string) $conf->time_zone : '';
	}

	$version = 'unknown';
	try {
		$row = $pdo->query("SELECT `version` FROM `config__secondary` LIMIT 1")->fetch(PDO::FETCH_ASSOC);
		if($row && isset($row['version'])) {
			$version = (string) $row['version'];
		}
	} catch(Throwable $e) {}

	$activePages = 0;
	$totalModules = 0;
	$activeModules = 0;
	$totalUsers = 0;
	try {
		$row = $pdo->query("SELECT COUNT(*) AS total FROM `pages` WHERE `active`='1'")->fetch(PDO::FETCH_ASSOC);
		$activePages = (int) ($row['total'] ?? 0);
	} catch(Throwable $e) {}
	try {
		$row = $pdo->query("SELECT COUNT(*) AS total, SUM(CASE WHEN `active`='1' THEN 1 ELSE 0 END) AS active_count FROM `modules`")->fetch(PDO::FETCH_ASSOC);
		$totalModules = (int) ($row['total'] ?? 0);
		$activeModules = (int) ($row['active_count'] ?? 0);
	} catch(Throwable $e) {}
	try {
		$row = $pdo->query("SELECT COUNT(*) AS total FROM `users`")->fetch(PDO::FETCH_ASSOC);
		$totalUsers = (int) ($row['total'] ?? 0);
	} catch(Throwable $e) {}

	$lines = [
		'PBGame CMS Terminal',
		'-------------------',
		'Сайт: ' . $siteName,
		'Версия движка: ' . $version,
		'Template: ' . $template,
		'PHP: ' . PHP_VERSION,
		'Часовой пояс: ' . $timeZone,
		'',
		'Активных страниц: ' . $activePages,
		'Активных модулей: ' . $activeModules . ' из ' . $totalModules,
		'Пользователей: ' . $totalUsers,
		'',
		'Готово. Введите команду или напишите help для списка.'
	];

    $terminalData = pb_dev_terminal_load_data();
    if(!empty($terminalData['boot_text'])) {
        $lines[] = '';
        $lines[] = trim((string) $terminalData['boot_text']);
    }
	return implode("\n", $lines);
}

function pb_dev_console_execute($pdo, $rawCommand)
{
	$rawCommand = trim((string) $rawCommand);
	if($rawCommand === '') {
		return [
			'status' => 2,
			'title' => 'Пустая команда',
			'output' => 'Введите команду PBGame CMS.'
		];
	}

	if(preg_match('/[;&|`><]/u', $rawCommand) || strpos($rawCommand, '$(') !== false) {
		return [
			'status' => 2,
			'title' => 'Команда отклонена',
			'output' => 'Разрешены только внутренние команды PBGame CMS.'
		];
	}

	$parts = preg_split('/\s+/u', $rawCommand);
	$commandKey = pb_dev_console_normalize_command(array_shift($parts));
	$registry = pb_dev_console_registry();
	$resolved = null;

	foreach($registry as $key => $meta) {
		if($commandKey === pb_dev_console_normalize_command($key)) {
			$resolved = $key;
			break;
		}
		if(!empty($meta['aliases'])) {
			foreach($meta['aliases'] as $alias) {
				if($commandKey === pb_dev_console_normalize_command($alias)) {
					$resolved = $key;
					break 2;
				}
			}
		}
	}

	if($resolved === null) {
		return [
			'status' => 2,
			'title' => 'Команда не найдена',
			'output' => 'Команда "' . $rawCommand . '" не найдена. Напишите help для списка доступных команд.'
		];
	}

	$handler = $registry[$resolved]['handler'];
	if(!function_exists($handler)) {
		return [
			'status' => 2,
			'title' => 'Команда недоступна',
			'output' => 'Обработчик команды временно недоступен: ' . $resolved
		];
	}

	try {
		$result = call_user_func($handler, $pdo, $parts, $rawCommand, $resolved);
		if(!is_array($result)) {
			$result = ['status' => 1, 'title' => $registry[$resolved]['title'], 'output' => (string) $result];
		}
		if(!isset($result['status'])) {
			$result['status'] = 1;
		}
		if(empty($result['title'])) {
			$result['title'] = $registry[$resolved]['title'];
		}
		if(!isset($result['output'])) {
			$result['output'] = '';
		}
		return $result;
	} catch(Throwable $e) {
		if(function_exists('write_log')) {
			write_log('pb_dev_console_execute: ' . $e->getMessage());
		}
		return [
			'status' => 2,
			'title' => 'Ошибка выполнения',
			'output' => 'PBGame Terminal не смог выполнить команду. ' . $e->getMessage()
		];
	}
}

function pb_dev_console_cmd_help($pdo, $args)
{
	return [
		'status' => 1,
		'title' => 'Список команд',
		'output' => pb_dev_console_help_text()
	];
}

function pb_dev_console_cmd_version($pdo, $args)
{
	global $conf;
	$version = 'unknown';
	try {
		$row = $pdo->query("SELECT `version` FROM `config__secondary` LIMIT 1")->fetch(PDO::FETCH_ASSOC);
		if($row && isset($row['version'])) {
			$version = (string) $row['version'];
		}
	} catch(Throwable $e) {}
	$siteName = (isset($conf) && is_object($conf) && isset($conf->name)) ? $conf->name : '';
	$template = (isset($conf) && is_object($conf) && isset($conf->template)) ? $conf->template : '';
	$timeZone = (isset($conf) && is_object($conf) && isset($conf->time_zone)) ? $conf->time_zone : '';
	$lines = [
		'Движок: PBGame CMS',
		'Version: ' . $version,
		'Сайт: ' . $siteName,
		'Шаблон: ' . $template,
		'Часовой пояс: ' . $timeZone,
		'PHP: ' . PHP_VERSION,
	];
	return ['status' => 1, 'title' => 'Версия движка', 'output' => implode("\n", $lines)];
}

function pb_dev_console_cmd_pages($pdo, $args)
{
	$row = $pdo->query("SELECT COUNT(*) AS total, SUM(CASE WHEN `active`='1' THEN 1 ELSE 0 END) AS active_count, SUM(CASE WHEN `active`!='1' THEN 1 ELSE 0 END) AS inactive_count FROM `pages`")->fetch(PDO::FETCH_ASSOC);
	$lines = [
		'Всего страниц: ' . (int) ($row['total'] ?? 0),
		'Активных: ' . (int) ($row['active_count'] ?? 0),
		'Неактивных: ' . (int) ($row['inactive_count'] ?? 0),
		'',
		'Подсказка: pages-list 10'
	];
	return ['status' => 1, 'title' => 'Страницы', 'output' => implode("\n", $lines)];
}

function pb_dev_console_cmd_pages_list($pdo, $args)
{
	$limit = pb_dev_console_limit(isset($args[0]) ? $args[0] : 10, 10, 1, 50);
	$sth = $pdo->prepare("SELECT `title`, `name`, `url` FROM `pages` WHERE `active`='1' ORDER BY `id` DESC LIMIT :limit");
	$sth->bindValue(':limit', $limit, PDO::PARAM_INT);
	$sth->execute();
	$rows = $sth->fetchAll(PDO::FETCH_ASSOC);
	if(!$rows) {
		return ['status' => 1, 'title' => 'Список страниц', 'output' => 'Активные страницы не найдены.'];
	}
	$lines = ['Активные страницы:'];
	foreach($rows as $row) {
		$title = trim((string) $row['title']) !== '' ? $row['title'] : $row['name'];
		$lines[] = '- ' . $title . ' | /' . trim((string) $row['url'], '/');
	}
	return ['status' => 1, 'title' => 'Список страниц', 'output' => implode("\n", $lines)];
}

function pb_dev_console_cmd_modules($pdo, $args)
{
	$row = $pdo->query("SELECT COUNT(*) AS total, SUM(CASE WHEN `active`='1' THEN 1 ELSE 0 END) AS active_count, SUM(CASE WHEN `active`!='1' THEN 1 ELSE 0 END) AS disabled_count FROM `modules`")->fetch(PDO::FETCH_ASSOC);
	$lines = [
		'Всего модулей: ' . (int) ($row['total'] ?? 0),
		'Активных модулей: ' . (int) ($row['active_count'] ?? 0),
		'Выключенных модулей: ' . (int) ($row['disabled_count'] ?? 0),
		'',
		'Подсказка: modules-list 10'
	];
	return ['status' => 1, 'title' => 'Модули', 'output' => implode("\n", $lines)];
}

function pb_dev_console_cmd_modules_list($pdo, $args)
{
	$limit = pb_dev_console_limit(isset($args[0]) ? $args[0] : 10, 10, 1, 50);
	$sth = $pdo->prepare("SELECT `name`, `active` FROM `modules` ORDER BY `id` DESC LIMIT :limit");
	$sth->bindValue(':limit', $limit, PDO::PARAM_INT);
	$sth->execute();
	$rows = $sth->fetchAll(PDO::FETCH_ASSOC);
	if(!$rows) {
		return ['status' => 1, 'title' => 'Список модулей', 'output' => 'Модули не найдены.'];
	}
	$lines = ['Модули:'];
	foreach($rows as $row) {
		$status = ((int) $row['active'] === 1) ? 'включен' : 'выключен';
		$lines[] = '- ' . $row['name'] . ' [' . $status . ']';
	}
	return ['status' => 1, 'title' => 'Список модулей', 'output' => implode("\n", $lines)];
}

function pb_dev_console_cmd_users($pdo, $args)
{
	$users = $pdo->query("SELECT COUNT(*) AS total FROM `users`")->fetch(PDO::FETCH_ASSOC);
	$groups = $pdo->query("SELECT COUNT(*) AS total FROM `users__groups`")->fetch(PDO::FETCH_ASSOC);
	$online = 0;
	$admins = 0;
	try {
		$row = $pdo->query("SELECT COUNT(*) AS total FROM `users__online`")->fetch(PDO::FETCH_ASSOC);
		$online = (int) ($row['total'] ?? 0);
	} catch(Throwable $e) {}
	try {
		$row = $pdo->query("SELECT COUNT(*) AS total FROM `admins`")->fetch(PDO::FETCH_ASSOC);
		$admins = (int) ($row['total'] ?? 0);
	} catch(Throwable $e) {}
	$lines = [
		'Всего пользователей: ' . (int) ($users['total'] ?? 0),
		'Сейчас онлайн: ' . $online,
		'Администраторов: ' . $admins,
		'Групп пользователей: ' . (int) ($groups['total'] ?? 0),
	];
	return ['status' => 1, 'title' => 'Пользователи', 'output' => implode("\n", $lines)];
}

function pb_dev_console_cmd_system($pdo, $args)
{
	global $conf;
	$siteName = (isset($conf) && is_object($conf) && isset($conf->name)) ? $conf->name : '';
	$template = (isset($conf) && is_object($conf) && isset($conf->template)) ? $conf->template : '';
	$timeZone = (isset($conf) && is_object($conf) && isset($conf->time_zone)) ? $conf->time_zone : '';
	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	$root = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
	$zip = extension_loaded('zip') ? 'включено' : 'выключено';
	$mb = extension_loaded('mbstring') ? 'включено' : 'выключено';
	$lines = [
		'Сайт: ' . $siteName,
		'Домен: ' . $host,
		'Корень проекта: ' . $root,
		'Шаблон: ' . $template,
		'Часовой пояс: ' . $timeZone,
		'ZIP: ' . $zip,
		'mbstring: ' . $mb,
		'PHP: ' . PHP_VERSION,
	];
	return ['status' => 1, 'title' => 'Система', 'output' => implode("\n", $lines)];
}

function pb_dev_console_cmd_doctor($pdo, $args)
{
	global $conf, $safe_mode, $dev_mode;

	$warnings = 0;
	$root = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') : realpath(__DIR__ . '/..');
	$template = (isset($conf) && is_object($conf) && isset($conf->template)) ? (string) $conf->template : '';
	$engineVersion = 'unknown';

	try {
		$row = $pdo->query("SELECT `version` FROM `config__secondary` LIMIT 1")->fetch(PDO::FETCH_ASSOC);
		if($row && isset($row['version'])) {
			$engineVersion = (string) $row['version'];
		}
	} catch(Throwable $e) {
		$warnings++;
	}

	$line = function($ok, $name, $value = '') use (&$warnings) {
		if(!$ok) {
			$warnings++;
		}
		return ($ok ? '[OK] ' : '[!!] ') . $name . ($value !== '' ? ': ' . $value : '');
	};

	$tableExists = function($table) use ($pdo) {
		try {
			$st = $pdo->prepare("SHOW TABLES LIKE :table");
			$st->execute([':table' => $table]);
			return (bool) $st->fetchColumn();
		} catch(Throwable $e) {
			return false;
		}
	};

	$lines = [
		'PBGame Doctor',
		'-------------',
		$line(version_compare(PHP_VERSION, '7.4.0', '>='), 'PHP', PHP_VERSION),
		$line(extension_loaded('pdo_mysql'), 'pdo_mysql', extension_loaded('pdo_mysql') ? 'включено' : 'выключено'),
		$line(extension_loaded('mbstring'), 'mbstring', extension_loaded('mbstring') ? 'включено' : 'выключено'),
		$line(extension_loaded('zip'), 'zip', extension_loaded('zip') ? 'включено' : 'выключено'),
		$line($engineVersion !== 'unknown', 'Версия движка', $engineVersion),
		$line($template !== '', 'Активный шаблон', $template),
		$line($template !== '' && is_dir($root . '/templates/' . $template), 'Папка шаблона', 'templates/' . $template),
		$line($template !== '' && file_exists($root . '/templates/' . $template . '/tpl/main.tpl'), 'main.tpl', 'templates/' . $template . '/tpl/main.tpl'),
		$line(isset($safe_mode) && (int) $safe_mode !== 1, 'Safe mode', (isset($safe_mode) && (int) $safe_mode === 1) ? 'ON' : 'OFF'),
		$line(isset($dev_mode) && (int) $dev_mode === 1, 'Dev mode', (isset($dev_mode) && (int) $dev_mode === 1) ? 'ON' : 'OFF'),
		$line(isset($conf->caching) && (int) $conf->caching !== 1, 'Cache mode для диагностики', (isset($conf->caching) && (int) $conf->caching === 1) ? 'ON' : 'OFF'),
		''
	];

	foreach(['config', 'config__secondary', 'users', 'pages', 'modules'] as $table) {
		$lines[] = $line($tableExists($table), 'Таблица ' . $table);
	}

	$lines[] = '';
	foreach(['cache', 'logs', 'files', 'templates', 'inc/configs'] as $dir) {
		$path = $root . '/' . $dir;
		$lines[] = $line(is_dir($path), 'Папка ' . $dir, is_dir($path) ? (is_writable($path) ? 'write OK' : 'нет записи') : 'нет');
	}

	$errorFiles = glob($root . '/logs/*error*.txt') ?: [];
	$errorLogs = glob($root . '/logs/error_log*.log') ?: [];
	$errorCount = count($errorFiles) + count($errorLogs);
	$lines[] = '';
	$lines[] = $line($errorCount === 0, 'Файлы ошибок', $errorCount ? ('найдено: ' . $errorCount . ', команда: errors 30') : 'не найдены');

	$lines[] = '';
	$lines[] = $warnings ? ('Итог: есть предупреждения (' . $warnings . ').') : 'Итог: критичных проблем не найдено.';
	$lines[] = 'Полезные команды: errors 30, cache-clear --yes, update-status, system';

	return ['status' => $warnings ? 2 : 1, 'title' => 'Doctor', 'output' => implode("\n", $lines)];
}

/* PBGame Store modern schema and helpers */
function pb_store_ensure_schema($pdo) {
	static $done = false;
	if($done || empty($pdo)) {
		return;
	}
	$done = true;

	try {
		$columns = [];
		$STH = $pdo->query("SHOW COLUMNS FROM `services`");
		if($STH) {
			while($row = $STH->fetch(PDO::FETCH_ASSOC)) {
				$columns[$row['Field']] = true;
			}
		}

		$alter = [];
		if(!isset($columns['image'])) {
			$alter[] = "ADD COLUMN `image` varchar(255) NOT NULL DEFAULT '' AFTER `discount`";
		}
		if(!isset($columns['description_builder'])) {
			$alter[] = "ADD COLUMN `description_builder` longtext NULL AFTER `image`";
		}

		if(!empty($alter)) {
			$pdo->exec("ALTER TABLE `services` " . implode(', ', $alter));
		}

		$pdo->exec("CREATE TABLE IF NOT EXISTS `store_cart_items` (
			`id` int NOT NULL AUTO_INCREMENT,
			`user_id` int NOT NULL,
			`server` int NOT NULL,
			`service` int NOT NULL,
			`tarif` int NOT NULL,
			`bind_type` int NOT NULL DEFAULT '2',
			`nick` varchar(32) NOT NULL DEFAULT '',
			`pass` varchar(32) NOT NULL DEFAULT '',
			`steam_id` varchar(32) NOT NULL DEFAULT '',
			`gift_user_id` int NOT NULL DEFAULT '0',
			`added_date` datetime NOT NULL,
			PRIMARY KEY (`id`),
			KEY `user_id` (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
	} catch(Exception $e) {
		if(function_exists('write_log')) {
			write_log('pb_store_ensure_schema: ' . $e->getMessage());
		}
	}
}

function pb_store_clean_hex_color($color, $default = '#7c5cff') {
	$color = trim((string)$color);
	if(preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
		return $color;
	}
	return $default;
}

function pb_store_clean_icon($icon, $default = 'bx bx-check') {
	$icon = trim((string)$icon);
	if($icon === '') {
		return $default;
	}
	$icon = preg_replace('/[^a-zA-Z0-9 _-]/', '', $icon);
	return mb_substr($icon, 0, 80, 'UTF-8');
}

function pb_store_normalize_description_builder($json) {
	$json = trim((string)$json);
	$variants = [
		$json,
		html_entity_decode($json, ENT_QUOTES, 'UTF-8'),
		stripslashes($json),
		stripslashes(html_entity_decode($json, ENT_QUOTES, 'UTF-8'))
	];
	$data = [];
	foreach($variants as $variant) {
		$variant = trim((string)$variant);
		if($variant === '') {
			continue;
		}
		$decoded = json_decode($variant, true);
		if(is_array($decoded)) {
			$data = $decoded;
			break;
		}
	}
	if(!is_array($data)) {
		$data = [];
	}

	$result = [
		'titleColor' => pb_store_clean_hex_color($data['titleColor'] ?? '#7c5cff'),
		'blocks' => []
	];

	$blocks = $data['blocks'] ?? [];
	if(!is_array($blocks)) {
		$blocks = [];
	}

	foreach($blocks as $block) {
		if(!is_array($block)) {
			continue;
		}
		$title = trim((string)($block['title'] ?? ''));
		if($title === '') {
			continue;
		}
		$items = [];
		$rawItems = $block['items'] ?? [];
		if(is_array($rawItems)) {
			foreach($rawItems as $item) {
				if(!is_array($item)) {
					continue;
				}
				$text = trim((string)($item['text'] ?? ''));
				$image = trim((string)($item['image'] ?? ''));
				if($text === '' && $image === '') {
					continue;
				}
				$items[] = [
					'text' => mb_substr($text, 0, 500, 'UTF-8'),
					'icon' => pb_store_clean_icon($item['icon'] ?? 'bx bx-check'),
					'color' => pb_store_clean_hex_color($item['color'] ?? '#7c5cff'),
					'image' => mb_substr(preg_replace('/[^a-zA-Z0-9_\\.\\-\\/]/', '', $image), 0, 255, 'UTF-8')
				];
			}
		}
		$result['blocks'][] = [
			'title' => mb_substr($title, 0, 120, 'UTF-8'),
			'icon' => pb_store_clean_icon($block['icon'] ?? 'bx bx-wrench'),
			'color' => pb_store_clean_hex_color($block['color'] ?? '#7c5cff'),
			'items' => $items
		];
	}

	return $result;
}

function pb_store_render_description_builder($json) {
	$data = pb_store_normalize_description_builder($json);
	if(empty($data['blocks'])) {
		return '<div class="moder_store_css_60" style="padding:16px 18px;border:1px solid rgba(74,206,142,.22);border-radius:14px;background:rgba(45,45,54,.92);color:#b7bad0;font-weight:700;">Описание услуги пока не заполнено.</div>';
	}

	$rootColor = htmlspecialchars($data['titleColor'], ENT_QUOTES, 'UTF-8');
	$html = '<div class="moder_store_css_54" style="--moder_store_css_accent:' . $rootColor . ';display:grid!important;gap:14px!important;">';
	foreach($data['blocks'] as $block) {
		$color = htmlspecialchars($block['color'], ENT_QUOTES, 'UTF-8');
		$html .= '<section class="moder_store_css_55" style="--moder_store_css_block_accent:' . $color . ';display:block!important;padding:14px!important;border:1px solid ' . $color . '!important;border-radius:16px!important;background:radial-gradient(circle at 0 0, rgba(74,206,142,.13), transparent 42%), #10131d!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.04),0 12px 26px rgba(0,0,0,.16)!important;">';
		$html .= '<h4 class="moder_store_css_56" style="display:flex!important;align-items:center!important;gap:10px!important;margin:0 0 12px!important;color:' . $color . '!important;font-size:14px!important;font-weight:900!important;letter-spacing:.08em!important;line-height:1.25!important;text-transform:uppercase!important;white-space:normal!important;"><i class="' . htmlspecialchars($block['icon'], ENT_QUOTES, 'UTF-8') . '" style="display:inline-flex!important;align-items:center!important;justify-content:center!important;width:28px!important;height:28px!important;flex:0 0 28px!important;border-radius:10px!important;background:rgba(0,0,0,.22)!important;color:' . $color . '!important;font-size:16px!important;"></i><span style="color:' . $color . '!important;font-weight:900!important;white-space:normal!important;">' . htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8') . '</span></h4>';
		if(!empty($block['items'])) {
			$html .= '<div class="moder_store_css_57" style="display:grid!important;gap:10px!important;">';
			foreach($block['items'] as $item) {
				$itemColor = htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8');
				$html .= '<div class="moder_store_css_58" style="--moder_store_css_item_accent:' . $itemColor . ';display:grid!important;grid-template-columns:auto 1fr!important;gap:8px 10px!important;align-items:center!important;padding:11px 12px!important;border:1px solid rgba(255,255,255,.055)!important;border-left:3px solid ' . $itemColor . '!important;border-radius:12px!important;background:#1c202b!important;color:#fff!important;font-weight:700!important;">';
				$html .= '<i class="moder_store_css_59 ' . htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') . '" style="color:' . $itemColor . '!important;font-size:16px!important;"></i>';
				$html .= '<span class="moder_store_css_61" style="min-width:0!important;color:#fff!important;font-weight:700!important;white-space:normal!important;">' . htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8') . '</span>';
				if($item['image'] !== '') {
					$html .= '<img class="moder_store_css_62" src="../' . htmlspecialchars(ltrim($item['image'], '/'), ENT_QUOTES, 'UTF-8') . '" alt="" style="grid-column:1/-1!important;width:100%!important;max-width:100%!important;max-height:260px!important;object-fit:cover!important;border-radius:12px!important;border:1px solid rgba(255,255,255,.06)!important;">';
				}
				$html .= '</div>';
			}
			$html .= '</div>';
		}
		$html .= '</section>';
	}
	$html .= '</div>';

	return $html;
}

function pb_store_upload_image($file, $prefix = 'service') {
	if(empty($file) || empty($file['name'])) {
		return ['status' => 2, 'message' => 'Выберите изображение.'];
	}
	if(!empty($file['error'])) {
		return ['status' => 2, 'message' => 'Ошибка загрузки файла.'];
	}
	if((int)$file['size'] > 10 * 1024 * 1024) {
		return ['status' => 2, 'message' => 'Максимальный размер изображения: 10 MiB.'];
	}
	if(!if_img($file['name'])) {
		return ['status' => 2, 'message' => 'Изображение должно быть в формате JPG, JPEG, PNG, GIF или BMP.'];
	}

	$dir = $_SERVER['DOCUMENT_ROOT'] . '/files/services/';
	if(!is_dir($dir)) {
		@mkdir($dir, 0777, true);
	}

	$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
	if($ext === 'jpeg') {
		$ext = 'jpg';
	}
	$name = preg_replace('/[^a-zA-Z0-9_-]/', '', $prefix) . '_' . date('YmdHis') . '_' . rand(1000, 9999) . '.' . $ext;
	$target = $dir . $name;
	if(!move_uploaded_file($file['tmp_name'], $target)) {
		return ['status' => 2, 'message' => 'Не удалось сохранить изображение.'];
	}

	return ['status' => 1, 'path' => 'files/services/' . $name];
}

function pb_admin_quick_access_defaults() {
	return array(
		array('label' => 'Сброс кэша', 'type' => 'action', 'value' => 'dell_cache', 'icon' => 'refresh'),
		array('label' => 'Модули', 'type' => 'link', 'value' => 'admin/modules', 'icon' => 'inbox'),
		array('label' => 'Шаблоны', 'type' => 'link', 'value' => 'admin/template', 'icon' => 'leaf')
	);
}

function pb_admin_quick_access_allowed_actions() {
	return array(
		'dell_cache' => 'Сброс кэша',
		'dell_old_bans' => 'Очистка старых банов',
		'dell_old_tickets' => 'Очистка старых заявок',
		'dell_all_chat_messages' => 'Очистка всего чата',
		'dell_all_bid_tickets' => 'Удаление всех тикетов',
		'dell_all_bid_bans' => 'Удаление всех заявок на разбан',
		'dell_all_bid_complains' => 'Удаление всех жалоб'
	);
}

function pb_admin_quick_access_catalog() {
	return array(
		'custom' => array('title' => 'Своя ссылка', 'type' => 'link', 'value' => '', 'icon' => 'link', 'group' => 'Другое'),
		'admin' => array('title' => 'Главная админки', 'type' => 'link', 'value' => 'admin', 'icon' => 'home', 'group' => 'Основное'),

		'dev_center' => array('title' => 'Центр разработчика', 'type' => 'link', 'value' => 'admin/dev_center', 'icon' => 'wrench', 'group' => 'Основные'),
		'telegram' => array('title' => 'Telegram', 'type' => 'link', 'value' => 'admin/telegram', 'icon' => 'send', 'group' => 'Основные'),
		'docs' => array('title' => 'Документация', 'type' => 'link', 'value' => 'admin/docs', 'icon' => 'book', 'group' => 'Основные'),
		'dev_ssh' => array('title' => 'SSH | Терминал', 'type' => 'link', 'value' => 'admin/dev_ssh', 'icon' => 'blackboard', 'group' => 'Основные'),
		'dev_migration' => array('title' => 'Миграция данных', 'type' => 'link', 'value' => 'admin/dev_migration', 'icon' => 'transfer', 'group' => 'Основные'),
		'dev_terminal_data' => array('title' => 'Терминал | Данные', 'type' => 'link', 'value' => 'admin/dev_terminal_data', 'icon' => 'tasks', 'group' => 'Основные'),

		'widgets' => array('title' => 'Виджеты', 'type' => 'link', 'value' => 'admin/widgets', 'icon' => 'th', 'group' => 'Настройки'),
		'security' => array('title' => 'Безопасность', 'type' => 'link', 'value' => 'admin/security', 'icon' => 'lock', 'group' => 'Настройки'),
		'registration' => array('title' => 'Регистрация', 'type' => 'link', 'value' => 'admin/registration', 'icon' => 'pencil', 'group' => 'Настройки'),
		'email_settings' => array('title' => 'Настройка почты', 'type' => 'link', 'value' => 'admin/email_settings', 'icon' => 'envelope', 'group' => 'Настройки'),
		'logs' => array('title' => 'Логи | Блокировки', 'type' => 'link', 'value' => 'admin/logs', 'icon' => 'align-justify', 'group' => 'Настройки'),

		'servers' => array('title' => 'Настройка серверов', 'type' => 'link', 'value' => 'admin/servers', 'icon' => 'hdd', 'group' => 'Коммерция'),
		'payments' => array('title' => 'Платёжные системы', 'type' => 'link', 'value' => 'admin/payments', 'icon' => 'credit-card', 'group' => 'Коммерция'),
		'bank' => array('title' => 'Монетизация', 'type' => 'link', 'value' => 'admin/bank', 'icon' => 'piggy-bank', 'group' => 'Коммерция'),
		'store' => array('title' => 'Настройка услуг', 'type' => 'link', 'value' => 'admin/store', 'icon' => 'barcode', 'group' => 'Коммерция'),
		'playground' => array('title' => 'Торговая площадка', 'type' => 'link', 'value' => 'admin/playground', 'icon' => 'shopping-cart', 'group' => 'Коммерция'),

		'users_groups' => array('title' => 'Группы пользователей', 'type' => 'link', 'value' => 'admin/users_groups', 'icon' => 'fire', 'group' => 'Пользователи'),
		'users' => array('title' => 'Настройка пользователей', 'type' => 'link', 'value' => 'admin/users', 'icon' => 'user', 'group' => 'Пользователи'),
		'verifications' => array('title' => 'Верификация пользователей', 'type' => 'link', 'value' => 'admin/verifications', 'icon' => 'check', 'group' => 'Пользователи'),
		'admins' => array('title' => 'Администраторы', 'type' => 'link', 'value' => 'admin/admins', 'icon' => 'queen', 'group' => 'Пользователи'),

		'news' => array('title' => 'Настройка новостей', 'type' => 'link', 'value' => 'admin/news', 'icon' => 'folder-close', 'group' => 'Контент'),
		'forum_settings' => array('title' => 'Настройка форума', 'type' => 'link', 'value' => 'admin/forum_settings', 'icon' => 'text-background', 'group' => 'Контент'),
		'page_editor' => array('title' => 'Редактор страниц', 'type' => 'link', 'value' => 'admin/page_editor', 'icon' => 'file', 'group' => 'Контент'),
		'menu_editor' => array('title' => 'Редактор меню', 'type' => 'link', 'value' => 'admin/menu_editor', 'icon' => 'list', 'group' => 'Контент'),
		'template' => array('title' => 'Редактор шаблонов', 'type' => 'link', 'value' => 'admin/template', 'icon' => 'leaf', 'group' => 'Контент'),
		'prefixes' => array('title' => 'Префиксы', 'type' => 'link', 'value' => 'admin/prefixes', 'icon' => 'tags', 'group' => 'Контент'),

		'modules' => array('title' => 'Модули', 'type' => 'link', 'value' => 'admin/modules', 'icon' => 'inbox', 'group' => 'Дополнения'),
		'cases' => array('title' => 'Кейсы', 'type' => 'link', 'value' => 'admin/cases', 'icon' => 'gift', 'group' => 'Дополнения'),
		'sortition' => array('title' => 'Розыгрыш', 'type' => 'link', 'value' => 'admin/sortition', 'icon' => 'random', 'group' => 'Дополнения'),
		'activity_rewards' => array('title' => 'Activity Rewards', 'type' => 'link', 'value' => 'admin/activity_rewards', 'icon' => 'star', 'group' => 'Дополнения'),
		'rcon_shop' => array('title' => 'RCON-магазин', 'type' => 'link', 'value' => 'admin/rcon_shop', 'icon' => 'console', 'group' => 'Дополнения'),
		'digital_store' => array('title' => 'Цифровой магазин', 'type' => 'link', 'value' => 'admin/digital_store', 'icon' => 'th-large', 'group' => 'Дополнения'),
		'market' => array('title' => 'Маркет', 'type' => 'link', 'value' => 'admin/market', 'icon' => 'transfer', 'group' => 'Дополнения'),
		'amxx' => array('title' => 'AMXX Плагины', 'type' => 'link', 'value' => 'https://pbgame.top/amxx', 'icon' => 'cloud-download', 'group' => 'Дополнения'),

		'cache' => array('title' => 'Сброс кэша', 'type' => 'action', 'value' => 'dell_cache', 'icon' => 'refresh', 'group' => 'Быстрые действия'),
		'old_bans' => array('title' => 'Очистка старых банов', 'type' => 'action', 'value' => 'dell_old_bans', 'icon' => 'trash', 'group' => 'Быстрые действия'),
		'old_tickets' => array('title' => 'Очистка старых заявок', 'type' => 'action', 'value' => 'dell_old_tickets', 'icon' => 'folder-close', 'group' => 'Быстрые действия'),
		'all_chat' => array('title' => 'Очистка всего чата', 'type' => 'action', 'value' => 'dell_all_chat_messages', 'icon' => 'comment', 'group' => 'Опасные действия'),
		'all_tickets' => array('title' => 'Удаление всех тикетов', 'type' => 'action', 'value' => 'dell_all_bid_tickets', 'icon' => 'inbox', 'group' => 'Опасные действия'),
		'all_bans' => array('title' => 'Удаление всех заявок на разбан', 'type' => 'action', 'value' => 'dell_all_bid_bans', 'icon' => 'ban-circle', 'group' => 'Опасные действия'),
		'all_complains' => array('title' => 'Удаление всех жалоб', 'type' => 'action', 'value' => 'dell_all_bid_complains', 'icon' => 'warning-sign', 'group' => 'Опасные действия')
	);
}

function pb_admin_quick_access_catalog_match($type, $value) {
	foreach(pb_admin_quick_access_catalog() as $item) {
		if(isset($item['type'], $item['value']) && $item['type'] === $type && $item['value'] === $value) {
			return $item;
		}
	}
	return null;
}

function pb_admin_quick_access_pdo($pdo = null) {
	if($pdo instanceof PDO) {
		return $pdo;
	}
	if(function_exists('pdo')) {
		$pdo = pdo();
		if($pdo instanceof PDO) {
			return $pdo;
		}
	}
	return null;
}

function pb_admin_quick_access_ensure_schema($pdo = null) {
	$pdo = pb_admin_quick_access_pdo($pdo);
	if(!$pdo instanceof PDO) {
		return false;
	}

	try {
		$pdo->exec("
			CREATE TABLE IF NOT EXISTS `admin_quick_access_settings` (
				`setting_key` varchar(64) NOT NULL,
				`items_json` mediumtext NOT NULL,
				`updated_at` datetime NOT NULL,
				PRIMARY KEY (`setting_key`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
		");
		return true;
	} catch(Exception $e) {
		if(function_exists('write_log')) {
			write_log('pb_admin_quick_access_ensure_schema: ' . $e->getMessage());
		}
	}

	return false;
}

function pb_admin_quick_access_cut($text, $length) {
	if(function_exists('mb_substr')) {
		return mb_substr($text, 0, $length, 'UTF-8');
	}
	return substr($text, 0, $length);
}

function pb_admin_quick_access_sanitize_icon($icon) {
	$icon = preg_replace('/[^a-z0-9_-]/i', '', (string) $icon);
	return $icon !== '' ? substr($icon, 0, 32) : 'link';
}

function pb_admin_quick_access_is_safe_url($url) {
	$url = trim((string) $url);
	if($url === '' || strlen($url) > 255) {
		return false;
	}
	if(preg_match('/^\s*(javascript|data|vbscript):/i', $url)) {
		return false;
	}
	if(preg_match('/^https?:\/\//i', $url)) {
		return true;
	}
	return preg_match('/^(\/|\.\/|\.\.\/|[a-z0-9_\/?#=&.\-]+$)/i', $url) === 1;
}

function pb_admin_quick_access_sanitize_items($items, $maxItems = 16) {
	if(!is_array($items)) {
		return pb_admin_quick_access_defaults();
	}
	$maxItems = (int) $maxItems;
	if($maxItems < 1) {
		$maxItems = 16;
	}

	$allowedActions = pb_admin_quick_access_allowed_actions();
	$result = array();

	foreach($items as $item) {
		if(!is_array($item)) {
			continue;
		}

		$label = trim(strip_tags((string) (isset($item['label']) ? $item['label'] : '')));
		$type = trim((string) (isset($item['type']) ? $item['type'] : 'link'));
		$value = trim((string) (isset($item['value']) ? $item['value'] : ''));
		$icon = pb_admin_quick_access_sanitize_icon(isset($item['icon']) ? $item['icon'] : 'link');

		if($label === '') {
			$label = 'Быстрый доступ';
		}
		$label = pb_admin_quick_access_cut($label, 40);

		if($type === 'action') {
			if(!isset($allowedActions[$value])) {
				continue;
			}
		} else {
			$type = 'link';
			if(!pb_admin_quick_access_is_safe_url($value)) {
				continue;
			}
		}

		$catalogItem = pb_admin_quick_access_catalog_match($type, $value);
		if(is_array($catalogItem)) {
			$label = $catalogItem['title'];
			$icon = $catalogItem['icon'];
		}

		$result[] = array(
			'label' => $label,
			'type' => $type,
			'value' => $value,
			'icon' => $icon
		);

		if(count($result) >= $maxItems) {
			break;
		}
	}

	return count($result) ? $result : pb_admin_quick_access_defaults();
}

function pb_admin_quick_access_get($pdo = null, $key = 'global', $maxItems = 16) {
	$pdo = pb_admin_quick_access_pdo($pdo);
	$key = (string) $key !== '' ? (string) $key : 'global';
	if(!$pdo instanceof PDO || !pb_admin_quick_access_ensure_schema($pdo)) {
		return pb_admin_quick_access_defaults();
	}

	try {
		$sth = $pdo->prepare("SELECT `items_json` FROM `admin_quick_access_settings` WHERE `setting_key`=:setting_key LIMIT 1");
		$sth->execute(array(':setting_key' => $key));
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		if(!$row || empty($row['items_json'])) {
			return pb_admin_quick_access_defaults();
		}

		$data = json_decode((string) $row['items_json'], true);
		return is_array($data) ? pb_admin_quick_access_sanitize_items($data, $maxItems) : pb_admin_quick_access_defaults();
	} catch(Exception $e) {
		if(function_exists('write_log')) {
			write_log('pb_admin_quick_access_get: ' . $e->getMessage());
		}
	}

	return pb_admin_quick_access_defaults();
}

function pb_admin_quick_access_save(array $items, $pdo = null, $key = 'global', $maxItems = 16) {
	$pdo = pb_admin_quick_access_pdo($pdo);
	$key = (string) $key !== '' ? (string) $key : 'global';
	if(!$pdo instanceof PDO || !pb_admin_quick_access_ensure_schema($pdo)) {
		return false;
	}

	try {
		$data = json_encode(pb_admin_quick_access_sanitize_items($items, $maxItems), JSON_UNESCAPED_UNICODE);
		$sth = $pdo->prepare("
			INSERT INTO `admin_quick_access_settings` (`setting_key`, `items_json`, `updated_at`)
			VALUES (:setting_key, :items_json, NOW())
			ON DUPLICATE KEY UPDATE `items_json`=VALUES(`items_json`), `updated_at`=VALUES(`updated_at`)
		");
		return $sth->execute(array(':setting_key' => $key, ':items_json' => $data));
	} catch(Exception $e) {
		if(function_exists('write_log')) {
			write_log('pb_admin_quick_access_save: ' . $e->getMessage());
		}
	}

	return false;
}

function pb_admin_quick_access_href($value, $siteHost = '/') {
	$value = trim((string) $value);
	if(preg_match('/^https?:\/\//i', $value)) {
		return $value;
	}
	if(strpos($value, '/') === 0 || strpos($value, './') === 0 || strpos($value, '../') === 0) {
		return $value;
	}
	return rtrim((string) $siteHost, '/') . '/' . ltrim($value, '/');
}

function pb_stickers_ensure_schema($pdo = null) {
	if($pdo === null && function_exists('pdo')) {
		$pdo = pdo();
	}
	if(!$pdo instanceof PDO) {
		return false;
	}

	try {
		$check = $pdo->query("SHOW COLUMNS FROM `stickers` LIKE 'price'");
		if(!$check || !$check->fetch(PDO::FETCH_ASSOC)) {
			$pdo->exec("ALTER TABLE `stickers` ADD COLUMN `price` float NOT NULL DEFAULT '0' AFTER `name`");
		}

		$pdo->exec("
			CREATE TABLE IF NOT EXISTS `users__stickers` (
				`id` int NOT NULL AUTO_INCREMENT,
				`user_id` int NOT NULL,
				`sticker_id` int NOT NULL,
				`date` datetime NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `user_sticker` (`user_id`,`sticker_id`),
				KEY `sticker_id` (`sticker_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
		");

		return true;
	} catch(Exception $e) {
		if(function_exists('write_log')) {
			write_log('pb_stickers_ensure_schema: ' . $e->getMessage());
		}
	}

	return false;
}

function pb_stickers_user_packs($userId, $pdo = null) {
	if($pdo === null && function_exists('pdo')) {
		$pdo = pdo();
	}
	if(!$pdo instanceof PDO || !pb_stickers_ensure_schema($pdo)) {
		return array();
	}

	$userId = (int) $userId;
	if($userId <= 0) {
		return array();
	}

	try {
		$sth = $pdo->prepare("SELECT `sticker_id` FROM `users__stickers` WHERE `user_id`=:user_id");
		$sth->execute(array(':user_id' => $userId));
		$ids = array();
		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$ids[(int) $row['sticker_id']] = true;
		}
		return $ids;
	} catch(Exception $e) {
		if(function_exists('write_log')) {
			write_log('pb_stickers_user_packs: ' . $e->getMessage());
		}
	}

	return array();
}

function pb_stickers_user_has_legacy_all($userId, $pdo = null) {
	if($pdo === null && function_exists('pdo')) {
		$pdo = pdo();
	}
	if(!$pdo instanceof PDO) {
		return false;
	}

	$userId = (int) $userId;
	if($userId <= 0) {
		return false;
	}

	try {
		$sth = $pdo->prepare("SELECT `stickers` FROM `users` WHERE `id`=:id LIMIT 1");
		$sth->execute(array(':id' => $userId));
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		return !empty($row['stickers']);
	} catch(Exception $e) {
		if(function_exists('write_log')) {
			write_log('pb_stickers_user_has_legacy_all: ' . $e->getMessage());
		}
	}

	return false;
}

function pb_server_card_can_manage() {
	if(!is_auth()) {
		return false;
	}
	try {
		global $users_groups, $pdo;
		$groupId = isset($_SESSION['rights']) ? (int)$_SESSION['rights'] : 0;
		$groups = isset($users_groups) ? $users_groups : (function_exists('get_groups') && isset($pdo) ? get_groups($pdo) : []);
		$groupName = '';
		foreach($groups as $group) {
			if(isset($group['id']) && (int)$group['id'] === $groupId) {
				$groupName = trim((string)$group['name']);
				break;
			}
		}
		$groupNameLower = function_exists('mb_strtolower') ? mb_strtolower($groupName, 'UTF-8') : strtolower($groupName);
		return (function_exists('is_admin_id') && is_admin_id()) || in_array($groupNameLower, ['создатель', 'владелец', 'creator', 'owner'], true);
	} catch(Throwable $e) {
		return false;
	}
}

function pb_server_card_ensure_default_rcon_commands($pdo, $serverId) {
	try {
		if(!$pdo->query("SHOW TABLES LIKE 'servers__commands'")->fetchColumn() || !$pdo->query("SHOW TABLES LIKE 'servers__commands_params'")->fetchColumn()) {
			return;
		}
		$cnt = $pdo->prepare("SELECT COUNT(*) FROM `servers__commands` WHERE `server_id`=:sid AND `category`='3'");
		$cnt->execute([':sid' => $serverId]);
		if((int)$cnt->fetchColumn() !== 0) {
			return;
		}
		$serverType = '1';
		try {
			$st = $pdo->prepare("SELECT `type` FROM `servers` WHERE `id`=:sid LIMIT 1");
			$st->execute([':sid' => $serverId]);
			$row = $st->fetch(PDO::FETCH_OBJ);
			if(!empty($row->type)) {
				$serverType = (string)$row->type;
			}
		} catch(Throwable $e) {}
		$prefix = in_array($serverType, ['0', '1', '2', '3', '5'], true) ? 'amx' : 'sm';
		$defs = [
			['status', 'status', 'Игроки онлайн', []],
			['say', $prefix . '_say', 'Сообщение в чат', ['text' => 'Текст сообщения']],
			['map', $prefix . '_map', 'Сменить карту', ['map' => 'Карта']],
			['reload_admins', $prefix . '_reloadadmins', 'Перезагрузка списка админов', []],
		];
		foreach($defs as $def) {
			$ins = $pdo->prepare("INSERT INTO `servers__commands` (`server_id`, `command`, `title`, `slug`, `category`) VALUES (:sid, :cmd, :title, :slug, '3')");
			$ins->execute([':sid' => $serverId, ':cmd' => $def[1], ':title' => $def[2], ':slug' => $def[0]]);
			$commandId = (int)$pdo->lastInsertId();
			foreach($def[3] as $name => $title) {
				$param = $pdo->prepare("INSERT INTO `servers__commands_params` (`command_id`, `name`, `title`) VALUES (:cid, :name, :title)");
				$param->execute([':cid' => $commandId, ':name' => $name, ':title' => $title]);
			}
		}
	} catch(Throwable $e) {}
}

function pb_server_card_context($serverId, $rconEnabled) {
	global $pdo;

	$serverId = (int)$serverId;
	$rconEnabled = ((string)$rconEnabled === '1');
	$canManage = $rconEnabled ? pb_server_card_can_manage() : false;

	if($rconEnabled && $canManage && isset($pdo)) {
		pb_server_card_ensure_default_rcon_commands($pdo, $serverId);
	}

	$serverCommands = new ServerCommands();
	$commands = $serverCommands->getServerManagementCommands($serverId);
	foreach($commands as $command) {
		$command->params = json_encode(
			$serverCommands->getCommandParams($command->id),
			JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP
		);
	}

	return (object)[
		'commands' => $commands,
		'showManagement' => $canManage && $rconEnabled && count($commands) > 0,
	];
}

function pb_days_left_label($endTimestamp) {
	$secondsLeft = (int)$endTimestamp - time();
	if($secondsLeft <= 0) {
		return 'Срок истек';
	}

	$daysLeft = (int)ceil($secondsLeft / 86400);
	if($daysLeft >= 1) {
		return $daysLeft . ' ' . getPhrase($daysLeft, ['сутки', 'суток', 'суток']);
	}

	$hoursLeft = (int)ceil($secondsLeft / 3600);
	if($hoursLeft >= 1) {
		return $hoursLeft . ' ' . getPhrase($hoursLeft, ['час', 'часа', 'часов']);
	}

	$minutesLeft = max(1, (int)ceil($secondsLeft / 60));
	return $minutesLeft . ' ' . getPhrase($minutesLeft, ['минута', 'минуты', 'минут']);
}

function pb_server_switcher_dropdown($pdo, $baseUrl, $currentServerId) {
	$STH = $pdo->query("SELECT `id`, `name` FROM `servers` WHERE `type`!=0 and `type`!=1 ORDER BY `trim`");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$servers = $STH->fetchAll();

	return pb_render_server_dropdown($servers, $baseUrl, $currentServerId);
}

function pb_stats_server_switcher_dropdown($pdo, $baseUrl, $currentServerId) {
	$STH = $pdo->query("SELECT `id`, `name` FROM `servers` WHERE `st_type`!=0 ORDER BY `trim`");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$servers = $STH->fetchAll();

	return pb_render_server_dropdown($servers, $baseUrl, $currentServerId);
}

function pb_admins_server_switcher_dropdown($pdo, $baseUrl, $currentServerId) {
	$STH = $pdo->query("SELECT `id`, `name` FROM `servers` WHERE `type`!=0 AND `united`='0' ORDER BY `trim`");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$servers = $STH->fetchAll();

	return pb_render_server_dropdown($servers, $baseUrl, $currentServerId);
}

function pb_render_server_dropdown($servers, $baseUrl, $currentServerId) {
	if(empty($servers)) {
		return '';
	}

	$currentServerId = (int)$currentServerId;
	$activeServer = null;
	foreach($servers as $server) {
		if((int)$server->id === $currentServerId) {
			$activeServer = $server;
			break;
		}
	}
	if($activeServer === null) {
		$activeServer = $servers[0];
	}

	$uid = 'pb-server-dropdown-' . (int)$activeServer->id . '-' . substr(md5($baseUrl), 0, 6);

	$html = '<div class="pb-server-dropdown" id="' . $uid . '">';
	$html .= '<button type="button" class="pb-server-dropdown__trigger" aria-haspopup="true" aria-expanded="false">';
	$html .= '<span class="pb-server-dropdown__icon"><i class="bx bx-server"></i></span>';
	$html .= '<span class="pb-server-dropdown__text">';
	$html .= '<span class="pb-server-dropdown__eyebrow">Сервер</span>';
	$html .= '<span class="pb-server-dropdown__label">' . htmlspecialchars($activeServer->name, ENT_QUOTES, 'UTF-8') . '</span>';
	$html .= '</span>';
	$html .= '<span class="pb-server-dropdown__chevron"><i class="bx bx-chevron-down"></i></span>';
	$html .= '</button>';
	$html .= '<div class="pb-server-dropdown__menu">';
	$html .= '<div class="pb-server-dropdown__menu-title">Выберите сервер</div>';
	foreach($servers as $server) {
		$isActive = ((int)$server->id === (int)$activeServer->id);
		$html .= '<a class="pb-server-dropdown__item' . ($isActive ? ' is-active' : '') . '" href="' . $baseUrl . '?server=' . (int)$server->id . '">';
		$html .= htmlspecialchars($server->name, ENT_QUOTES, 'UTF-8');
		if($isActive) {
			$html .= '<i class="bx bx-check pb-server-dropdown__check"></i>';
		}
		$html .= '</a>';
	}
	$html .= '</div>';
	$html .= '</div>';
	$html .= '<script>(function(){'
		. 'var w=document.getElementById(' . json_encode($uid) . ');if(!w)return;'
		. 'var t=w.querySelector(".pb-server-dropdown__trigger");var m=w.querySelector(".pb-server-dropdown__menu");'
		. 'if(!t||!m)return;'
		. 'function close(){m.classList.remove("is-open");t.setAttribute("aria-expanded","false");document.removeEventListener("click",onDocClick,true);}'
		. 'function onDocClick(e){if(!w.contains(e.target)){close();}}'
		. 't.addEventListener("click",function(e){e.stopPropagation();var open=m.classList.toggle("is-open");t.setAttribute("aria-expanded",open?"true":"false");if(open){document.addEventListener("click",onDocClick,true);}else{document.removeEventListener("click",onDocClick,true);}});'
		. '})();</script>';

	return $html;
}

function pb_premium_ensure_schema($pdo)
{
	static $done = false;
	if($done || empty($pdo)) {
		return;
	}
	$done = true;

	try {
		$pdo->exec("CREATE TABLE IF NOT EXISTS `premium__settings` (
			`setting_key` varchar(64) NOT NULL,
			`setting_value` varchar(255) NOT NULL DEFAULT '',
			PRIMARY KEY (`setting_key`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$pdo->exec("CREATE TABLE IF NOT EXISTS `premium__tariffs` (
			`id` int NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL DEFAULT '',
			`description` text NOT NULL,
			`price` decimal(10,2) NOT NULL DEFAULT '0.00',
			`duration_days` int NOT NULL DEFAULT '30',
			`discount_percent` int NOT NULL DEFAULT '0',
			`sort` int NOT NULL DEFAULT '0',
			`active` tinyint(1) NOT NULL DEFAULT '1',
			`features` text NOT NULL,
			`badge_icon` varchar(255) NOT NULL DEFAULT '',
			`service_tarif_id` int NOT NULL DEFAULT '0',
			`is_featured` tinyint(1) NOT NULL DEFAULT '0',
			`created_at` datetime NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$pdo->exec("CREATE TABLE IF NOT EXISTS `premium__subscriptions` (
			`id` int NOT NULL AUTO_INCREMENT,
			`user_id` int NOT NULL,
			`tariff_id` int NOT NULL,
			`started_at` datetime NOT NULL,
			`expires_at` datetime NOT NULL,
			`active` tinyint(1) NOT NULL DEFAULT '1',
			`created_at` datetime NOT NULL,
			PRIMARY KEY (`id`),
			KEY `user_id` (`user_id`),
			KEY `active` (`active`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$pdo->exec("CREATE TABLE IF NOT EXISTS `premium__tariff_stickerpacks` (
			`tariff_id` int NOT NULL,
			`pack_id` int NOT NULL,
			PRIMARY KEY (`tariff_id`, `pack_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$pdo->exec("CREATE TABLE IF NOT EXISTS `premium__user_settings` (
			`user_id` int NOT NULL,
			`hide_profile` tinyint(1) NOT NULL DEFAULT '0',
			`hide_online_status` tinyint(1) NOT NULL DEFAULT '0',
			`avatar_badge` varchar(255) NOT NULL DEFAULT '',
			`frame_resource` varchar(255) NOT NULL DEFAULT '',
			`background_resource` varchar(255) NOT NULL DEFAULT '',
			`avatar_resource` varchar(255) NOT NULL DEFAULT '',
			`site_background` varchar(255) NOT NULL DEFAULT '',
			`chat_color` varchar(9) NOT NULL DEFAULT '',
			`sound_resource` varchar(255) NOT NULL DEFAULT '',
			`incognito` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$pdo->exec("CREATE TABLE IF NOT EXISTS `premium__resource_library` (
			`id` int NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL DEFAULT '',
			`resource_type` varchar(20) NOT NULL,
			`resource_path` varchar(255) NOT NULL DEFAULT '',
			`created_at` datetime NOT NULL,
			PRIMARY KEY (`id`),
			KEY `resource_type` (`resource_type`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$pdo->exec("CREATE TABLE IF NOT EXISTS `premium__tariff_resources` (
			`tariff_id` int NOT NULL,
			`library_id` int NOT NULL,
			PRIMARY KEY (`tariff_id`, `library_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$pdo->exec("CREATE TABLE IF NOT EXISTS `premium__custom_perks` (
			`id` int NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL DEFAULT '',
			`description` varchar(500) NOT NULL DEFAULT '',
			`sort` int NOT NULL DEFAULT '0',
			`active` tinyint(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$pdo->exec("CREATE TABLE IF NOT EXISTS `premium__tariff_custom_perks` (
			`tariff_id` int NOT NULL,
			`perk_id` int NOT NULL,
			PRIMARY KEY (`tariff_id`, `perk_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$columnExists = function($table, $column) use ($pdo) {
			$check = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE " . $pdo->quote($column));
			return $check && $check->fetch();
		};

		if($columnExists('premium__tariff_stickerpacks', 'stickerpack_id') && !$columnExists('premium__tariff_stickerpacks', 'pack_id')) {
			$pdo->exec("ALTER TABLE `premium__tariff_stickerpacks` CHANGE `stickerpack_id` `pack_id` int NOT NULL");
		}
		if(!$columnExists('premium__tariff_stickerpacks', 'pack_id') && !$columnExists('premium__tariff_stickerpacks', 'stickerpack_id')) {
			$pdo->exec("ALTER TABLE `premium__tariff_stickerpacks` ADD COLUMN `pack_id` int NOT NULL");
		}

		if($columnExists('premium__tariffs', 'login_color') && !$columnExists('premium__tariffs', 'login_style_keys')) {
			$pdo->exec("ALTER TABLE `premium__tariffs` CHANGE `login_color` `login_style_keys` text NOT NULL");
		}
		if(!$columnExists('premium__tariffs', 'service_tarif_id')) {
			$pdo->exec("ALTER TABLE `premium__tariffs` ADD COLUMN `service_tarif_id` int NOT NULL DEFAULT '0'");
		}
		if(!$columnExists('premium__tariffs', 'is_featured')) {
			$pdo->exec("ALTER TABLE `premium__tariffs` ADD COLUMN `is_featured` tinyint(1) NOT NULL DEFAULT '0'");
		}

		foreach(['frame_resource', 'background_resource', 'avatar_resource', 'login_badge_icon'] as $legacyCol) {
			if($columnExists('premium__tariffs', $legacyCol)) {
				$pdo->exec("ALTER TABLE `premium__tariffs` MODIFY `{$legacyCol}` varchar(255) NOT NULL DEFAULT ''");
			}
		}
		if($columnExists('premium__tariffs', 'login_style_keys')) {
			$pdo->exec("ALTER TABLE `premium__tariffs` MODIFY `login_style_keys` text NULL");
		}

		if($columnExists('premium__user_settings', 'login_color') && !$columnExists('premium__user_settings', 'login_style_key')) {
			$pdo->exec("ALTER TABLE `premium__user_settings` CHANGE `login_color` `login_style_key` varchar(64) NOT NULL DEFAULT ''");
		}
		if($columnExists('premium__user_settings', 'hide_active_match')) {
			$pdo->exec("ALTER TABLE `premium__user_settings` DROP COLUMN `hide_active_match`");
		}
		if($columnExists('premium__user_settings', 'login_style_key')) {
			$pdo->exec("ALTER TABLE `premium__user_settings` DROP COLUMN `login_style_key`");
		}
		if($columnExists('premium__user_settings', 'login_badge')) {
			$pdo->exec("ALTER TABLE `premium__user_settings` DROP COLUMN `login_badge`");
		}
		if(!$columnExists('premium__user_settings', 'site_background')) {
			$pdo->exec("ALTER TABLE `premium__user_settings` ADD COLUMN `site_background` varchar(255) NOT NULL DEFAULT ''");
		}
		if(!$columnExists('premium__user_settings', 'chat_color')) {
			$pdo->exec("ALTER TABLE `premium__user_settings` ADD COLUMN `chat_color` varchar(9) NOT NULL DEFAULT ''");
		}
		if(!$columnExists('premium__user_settings', 'sound_resource')) {
			$pdo->exec("ALTER TABLE `premium__user_settings` ADD COLUMN `sound_resource` varchar(255) NOT NULL DEFAULT ''");
		}
		if(!$columnExists('premium__user_settings', 'video_resource')) {
			$pdo->exec("ALTER TABLE `premium__user_settings` ADD COLUMN `video_resource` varchar(255) NOT NULL DEFAULT ''");
		}
		if(!$columnExists('premium__user_settings', 'incognito')) {
			$pdo->exec("ALTER TABLE `premium__user_settings` ADD COLUMN `incognito` tinyint(1) NOT NULL DEFAULT '0'");
		}

		if($columnExists('premium__tariff_resources', 'resource_path')) {
			$oldRows = $pdo->query("SELECT * FROM `premium__tariff_resources`")->fetchAll(PDO::FETCH_OBJ);
			$pdo->exec("DROP TABLE `premium__tariff_resources`");
			$pdo->exec("CREATE TABLE IF NOT EXISTS `premium__tariff_resources` (
				`tariff_id` int NOT NULL,
				`library_id` int NOT NULL,
				PRIMARY KEY (`tariff_id`, `library_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

			foreach($oldRows as $oldRow) {
				$insertLib = $pdo->prepare("INSERT INTO `premium__resource_library` (`name`, `resource_type`, `resource_path`, `created_at`) VALUES (:name, :type, :path, NOW())");
				$insertLib->execute([
					':name' => basename($oldRow->resource_path),
					':type' => $oldRow->resource_type,
					':path' => $oldRow->resource_path,
				]);
				$libraryId = (int) $pdo->lastInsertId();
				$pdo->prepare("INSERT IGNORE INTO `premium__tariff_resources` (`tariff_id`, `library_id`) VALUES (:tariff_id, :library_id)")
					->execute([':tariff_id' => (int) $oldRow->tariff_id, ':library_id' => $libraryId]);
			}
		}

		$libraryScanDirs = [
			'frame' => 'frame',
			'avatar' => 'avatar',
			'background' => 'backgrounds',
			'site_background' => 'site_backgrounds',
			'badge' => 'icon',
			'sound' => 'sounds',
			'video' => 'video',
		];
		$libraryAllowedExt = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'mp4', 'webm', 'mp3', 'ogg', 'mov'];
		$knownPaths = [];
		foreach($pdo->query("SELECT `resource_type`, `resource_path` FROM `premium__resource_library`")->fetchAll(PDO::FETCH_ASSOC) as $knownRow) {
			$knownPaths[$knownRow['resource_type'] . '|' . $knownRow['resource_path']] = true;
		}
		foreach($libraryScanDirs as $type => $subdir) {
			$fullDir = dirname(__DIR__) . '/templates/solution2/img/premium/' . $subdir;
			if(!is_dir($fullDir)) {
				continue;
			}
			foreach(scandir($fullDir) as $file) {
				if($file === '.' || $file === '..') {
					continue;
				}
				if(!is_file($fullDir . '/' . $file)) {
					continue;
				}
				$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				if(!in_array($ext, $libraryAllowedExt, true)) {
					continue;
				}
				$resourcePath = $subdir . '/' . $file;
				if(isset($knownPaths[$type . '|' . $resourcePath])) {
					continue;
				}
				$niceName = pathinfo($file, PATHINFO_FILENAME);
				$niceName = preg_replace('/^\d{9,}_/', '', $niceName);
				$niceName = trim(str_replace(['-', '_'], ' ', $niceName));
				if($niceName === '') {
					$niceName = pathinfo($file, PATHINFO_FILENAME);
				}
				$pdo->prepare("INSERT INTO `premium__resource_library` (`name`, `resource_type`, `resource_path`, `created_at`) VALUES (:name, :type, :path, NOW())")
					->execute([':name' => $niceName, ':type' => $type, ':path' => $resourcePath]);
				$knownPaths[$type . '|' . $resourcePath] = true;
			}
		}

		$staleRows = $pdo->query("SELECT `id`, `resource_type`, `resource_path` FROM `premium__resource_library`")->fetchAll(PDO::FETCH_OBJ);
		foreach($staleRows as $staleRow) {
			if(!isset($libraryScanDirs[$staleRow->resource_type])) {
				continue;
			}
			$fullPath = dirname(__DIR__) . '/templates/solution2/img/premium/' . $staleRow->resource_path;
			if(!is_file($fullPath)) {
				$pdo->prepare("DELETE FROM `premium__tariff_resources` WHERE `library_id`=:id")->execute([':id' => (int) $staleRow->id]);
				$pdo->prepare("DELETE FROM `premium__resource_library` WHERE `id`=:id LIMIT 1")->execute([':id' => (int) $staleRow->id]);
			}
		}

		$pdo->exec("DROP TABLE IF EXISTS `premium__servers`");
		$pdo->exec("DROP TABLE IF EXISTS `premium__stickerpacks`");

		$exists = $pdo->prepare("SELECT `id` FROM `pages` WHERE `url`='premium' LIMIT 1");
		$exists->execute();
		if(!$exists->fetch(PDO::FETCH_OBJ)) {
			$pdo->prepare("INSERT INTO `pages`(`file`,`url`,`name`,`title`,`description`,`keywords`,`kind`,`image`,`robots`,`privacy`,`type`,`active`,`module`,`page`,`class`) VALUES (:file,:url,:name,:title,:description,:keywords,'1','files/miniatures/pbgame_ui.jpg','0','0','1','1','0','0','0')")->execute([
				':file' => 'modules/premium/index.php',
				':url' => 'premium',
				':name' => 'premium',
				':title' => 'PREMIUM подписка',
				':description' => 'Оформление PREMIUM подписки и управление её привилегиями.',
				':keywords' => 'premium, подписка, тариф',
			]);
		}
	} catch (Exception $e) {
		if (function_exists('write_log')) {
			write_log('pb_premium_ensure_schema: ' . $e->getMessage());
		}
	}
}

function premium()
{
	static $instance = null;
	if($instance === null) {
		pb_premium_ensure_schema(pdo());
		$instance = new Premium(pdo());
	}
	return $instance;
}

function pb_premium_is_premium($userId)
{
	try {
		return premium()->is_premium($userId);
	} catch (Throwable $e) {
		return false;
	}
}

function pb_premium_profile_frame($userId)
{
	try {
		return premium()->get_active_frame($userId);
	} catch (Throwable $e) {
		return '';
	}
}

function pb_premium_profile_background($userId)
{
	try {
		return premium()->get_active_background($userId);
	} catch (Throwable $e) {
		return '';
	}
}

function pb_premium_profile_avatar($userId)
{
	try {
		return premium()->get_active_avatar($userId);
	} catch (Throwable $e) {
		return '';
	}
}

function pb_premium_avatar_badge_html($userId)
{
	try {
		return premium()->render_avatar_badge($userId);
	} catch (Throwable $e) {
		return '';
	}
}

function pb_premium_login_badge_html($userId)
{
	try {
		return premium()->render_login_badge($userId);
	} catch (Throwable $e) {
		return '';
	}
}

function pb_premium_is_profile_hidden_from($ownerId, $viewerId)
{
	try {
		return premium()->is_profile_hidden_from($ownerId, $viewerId);
	} catch (Throwable $e) {
		return false;
	}
}

function pb_premium_is_online_hidden($userId)
{
	try {
		return premium()->is_online_hidden($userId);
	} catch (Throwable $e) {
		return false;
	}
}

function pb_premium_is_incognito($userId)
{
	try {
		return premium()->is_incognito($userId);
	} catch (Throwable $e) {
		return false;
	}
}

function pb_premium_profile_sound($userId)
{
	try {
		return premium()->get_active_sound($userId);
	} catch (Throwable $e) {
		return '';
	}
}

function pb_premium_profile_video($userId)
{
	try {
		return premium()->get_active_profile_video($userId);
	} catch (Throwable $e) {
		return '';
	}
}

function pb_premium_tpl_blocks()
{
	static $blocks = null;
	if($blocks !== null) {
		return $blocks;
	}

	$path = __DIR__ . '/../templates/solution2/tpl/premium/panel.tpl';
	$blocks = [];
	$raw = file_exists($path) ? file_get_contents($path) : '';
	if(preg_match_all('/<!--\s*BLOCK:([a-zA-Z0-9_]+)\s*-->(.*?)<!--\s*\/BLOCK\s*-->/s', $raw, $matches, PREG_SET_ORDER)) {
		foreach($matches as $match) {
			$blocks[$match[1]] = trim($match[2], "\n");
		}
	}

	return $blocks;
}

function pb_premium_tpl($name, $vars = [])
{
	$blocks = pb_premium_tpl_blocks();
	if(empty($blocks[$name])) {
		return '';
	}

	$find = [];
	$replace = [];
	foreach($vars as $key => $value) {
		$find[] = '{' . $key . '}';
		$replace[] = $value;
	}

	return str_replace($find, $replace, $blocks[$name]);
}

function pb_premium_render_profile_music_widget($userId)
{
	$src = pb_premium_profile_sound($userId);
	if(empty($src)) {
		return '';
	}

	$name = '';
	try {
		$tariff = premium()->get_active_tariff($userId);
		$settings = premium()->get_user_settings($userId);
		$resourcePath = !empty($settings->sound_resource) ? $settings->sound_resource : (!empty($tariff->resources_sound[0]) ? $tariff->resources_sound[0]->resource_path : '');
		foreach(($tariff->resources_sound ?? []) as $item) {
			if($item->resource_path === $resourcePath) {
				$name = $item->name;
				break;
			}
		}
	} catch (Throwable $e) {}

	if($name === '') {
		$name = 'Трек профиля';
	}

	return pb_premium_tpl('profile_music_widget', [
		'src' => htmlspecialchars($src, ENT_QUOTES, 'UTF-8'),
		'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
	]);
}

function pb_premium_render_profile_video_overlay($userId)
{
	$src = pb_premium_profile_video($userId);
	if(empty($src)) {
		return '';
	}

	return pb_premium_tpl('profile_video_intro_overlay', [
		'src' => htmlspecialchars($src, ENT_QUOTES, 'UTF-8'),
	]);
}

function pb_premium_drawer_status_html($userId)
{
	try {
		$tariff = premium()->get_active_tariff($userId);
	} catch (Throwable $e) {
		$tariff = null;
	}

	if(!empty($tariff)) {
		return pb_premium_tpl('drawer_premium_status', [
			'state_class' => 'active',
			'title' => 'PREMIUM активен',
			'hint' => htmlspecialchars($tariff->name, ENT_QUOTES, 'UTF-8'),
		]);
	}

	return pb_premium_tpl('drawer_premium_status', [
		'state_class' => 'inactive',
		'title' => 'PREMIUM не активен',
		'hint' => 'Оформить подписку',
	]);
}

function pb_premium_site_background($userId)
{
	try {
		return premium()->get_active_site_background($userId);
	} catch (Throwable $e) {
		return '';
	}
}

function pb_premium_body_style()
{
	if(!function_exists('is_auth') || !is_auth() || empty($_SESSION['id'])) {
		return '';
	}

	$background = pb_premium_site_background((int) $_SESSION['id']);
	if($background === '') {
		return '';
	}

	return ' style="background-image:url(' . htmlspecialchars($background, ENT_QUOTES, 'UTF-8') . ');background-size:cover;background-attachment:fixed;background-position:center;"';
}

function pb_premium_chat_color($userId)
{
	try {
		return premium()->get_chat_color($userId);
	} catch (Throwable $e) {
		return '';
	}
}

function pb_premium_render_subscription_row($row, $site_host)
{
	$isActive = (int) $row->active === 1 && strtotime($row->expires_at) > time();

	$loginCell = !empty($row->login)
		? '<a href="' . $site_host . 'profile?id=' . (int) $row->user_id . '" target="_blank">' . htmlspecialchars($row->login, ENT_QUOTES, 'UTF-8') . '</a>'
		: '<span class="text-muted">удалён</span>';

	$statusLabel = $isActive
		? '<span class="label label-success">Активна</span>'
		: '<span class="label label-default">Истекла</span>';

	$revokeCell = $isActive
		? '<button type="button" class="btn btn-xs btn-danger" onclick="premiumRevokeUser(' . (int) $row->user_id . ');">Отозвать</button>'
		: '<span class="text-muted">—</span>';

	return pb_premium_tpl('subscription_row', [
		'user_id' => (int) $row->user_id,
		'login_cell' => $loginCell,
		'tariff_name' => htmlspecialchars($row->tariff_name ?? '—', ENT_QUOTES, 'UTF-8'),
		'expires_at' => date('d.m.Y H:i', strtotime($row->expires_at)),
		'status_label' => $statusLabel,
		'revoke_cell' => $revokeCell,
	]);
}

function pb_premium_render_panel_html($premium, $pdo, $userId, $currency)
{
	$tariffs = $premium->get_tariffs(true);
	$subscription = $premium->get_subscription($userId);
	$activeTariff = $premium->get_active_tariff($userId);
	$isPremium = !empty($activeTariff);
	$userSettings = $premium->get_user_settings($userId);

	$tariffCards = '';
	foreach($tariffs as $tariff) {
		$hasDiscount = (int) $tariff->discount_percent > 0;
		$isFeatured = !empty($tariff->is_featured);

		$featuresHtml = '';
		foreach($tariff->features_decoded as $key => $enabled) {
			if(empty($enabled) || empty(Premium::FEATURES[$key])) {
				continue;
			}
			$featureIcon = Premium::FEATURE_ICONS[$key] ?? 'bx-check';
			$featuresHtml .= pb_premium_tpl('tariff_feature', [
				'icon' => htmlspecialchars($featureIcon, ENT_QUOTES, 'UTF-8'),
				'label' => htmlspecialchars(Premium::FEATURES[$key], ENT_QUOTES, 'UTF-8'),
			]);
		}
		if(!empty($tariff->custom_perk_ids)) {
			foreach($tariff->custom_perk_ids as $perkId) {
				$perk = $premium->get_custom_perk($perkId);
				if(empty($perk) || (int) $perk->active !== 1) {
					continue;
				}
				$perkHint = !empty($perk->description)
					? pb_premium_tpl('tariff_perk_hint', ['description' => htmlspecialchars($perk->description, ENT_QUOTES, 'UTF-8')])
					: '';
				$featuresHtml .= pb_premium_tpl('tariff_perk_feature', [
					'name' => htmlspecialchars($perk->name, ENT_QUOTES, 'UTF-8'),
					'hint' => $perkHint,
				]);
			}
		}

		$priceBlock = $hasDiscount
			? pb_premium_tpl('tariff_price_discounted', [
				'old_price' => number_format((float) $tariff->price, 2),
				'price' => number_format((float) $tariff->final_price, 2),
				'currency' => htmlspecialchars($currency, ENT_QUOTES, 'UTF-8'),
			])
			: pb_premium_tpl('tariff_price_plain', [
				'price' => number_format((float) $tariff->final_price, 2),
				'currency' => htmlspecialchars($currency, ENT_QUOTES, 'UTF-8'),
			]);

		$tariffCards .= pb_premium_tpl('tariff_card', [
			'featured_class' => $isFeatured ? ' premium-tariff-card--featured' : '',
			'featured_ribbon' => $isFeatured ? pb_premium_tpl('tariff_ribbon') : '',
			'name' => htmlspecialchars($tariff->name, ENT_QUOTES, 'UTF-8'),
			'duration' => (int) $tariff->duration_days,
			'price_block' => $priceBlock,
			'description_block' => !empty($tariff->description) ? pb_premium_tpl('tariff_description', ['description' => nl2br(htmlspecialchars($tariff->description, ENT_QUOTES, 'UTF-8'))]) : '',
			'features_html' => $featuresHtml,
			'id' => (int) $tariff->id,
		]);
	}
	if(empty($tariffs)) {
		$tariffCards = pb_premium_tpl('empty_message', ['message' => 'Тарифы PREMIUM пока не настроены администрацией.']);
	}

	$settingsHtml = '';
	$serviceModalHtml = '';
	if($isPremium) {
		$settingsHtml .= pb_premium_tpl('current_tariff', [
			'name' => htmlspecialchars($activeTariff->name, ENT_QUOTES, 'UTF-8'),
			'expiry' => expand_date($subscription->expires_at, 1),
		]);

		if($premium->has_feature($activeTariff, 'server_service') && !empty($activeTariff->service_tarif_id)) {
			$serviceInfo = $premium->get_tariff_service_info($activeTariff);
			if(!empty($serviceInfo)) {
				$serviceStatus = $premium->get_user_service_status($userId, $serviceInfo);
				$binds = explode(';', (string) $serviceInfo->server_binds);
				$availableTypes = [];
				if(!empty($binds[0])) { $availableTypes[] = ['1', 'Ник + пароль', 'bx-id-card']; }
				if(!empty($binds[1])) { $availableTypes[] = ['2', 'STEAM ID', 'bxl-steam']; }
				if(!empty($binds[2])) { $availableTypes[] = ['3', 'STEAM ID + пароль', 'bxl-steam']; }

				$userSteamId = '';
				$steamRow = $pdo->prepare("SELECT `steam_id` FROM `users` WHERE `id`=:id LIMIT 1");
				$steamRow->execute([':id' => $userId]);
				$steamRow = $steamRow->fetch(PDO::FETCH_OBJ);
				if(!empty($steamRow) && !empty($steamRow->steam_id) && (string) $steamRow->steam_id !== '0') {
					$userSteamId = $steamRow->steam_id;
				}

				$serviceNameEsc = htmlspecialchars($serviceInfo->service_name, ENT_QUOTES, 'UTF-8');
				$serverNameEsc = htmlspecialchars($serviceInfo->server_name, ENT_QUOTES, 'UTF-8');

				$statusBlock = !empty($serviceStatus)
					? pb_premium_tpl('service_status_active', [
						'ending' => $serviceStatus->ending_date === '0000-00-00 00:00:00' ? 'Бессрочно' : expand_date($serviceStatus->ending_date, 1),
					])
					: pb_premium_tpl('service_status_free');

				$settingsHtml .= pb_premium_tpl('service_card', [
					'service_name' => $serviceNameEsc,
					'server_name' => $serverNameEsc,
					'status_block' => $statusBlock,
				]);

				if(empty($serviceStatus)) {
					$typeSwitchBlock = '';
					if(count($availableTypes) > 1) {
						$typeOptionsHtml = '';
						foreach($availableTypes as $i => $typeDef) {
							[$typeValue, $typeLabel, $typeIcon] = $typeDef;
							$typeOptionsHtml .= pb_premium_tpl('service_type_option', [
								'active_class' => $i === 0 ? ' is-active' : '',
								'value' => $typeValue,
								'icon' => $typeIcon,
								'label' => htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8'),
							]);
						}
						$typeSwitchBlock = pb_premium_tpl('service_type_group', [
							'type_options_html' => $typeOptionsHtml,
							'first_type_value' => htmlspecialchars($availableTypes[0][0], ENT_QUOTES, 'UTF-8'),
						]);
					} elseif(count($availableTypes) === 1) {
						$typeSwitchBlock = pb_premium_tpl('service_type_single', [
							'first_type_value' => htmlspecialchars($availableTypes[0][0], ENT_QUOTES, 'UTF-8'),
						]);
					}

					$serviceModalHtml .= pb_premium_tpl('service_modal', [
						'service_name' => $serviceNameEsc,
						'server_name' => $serverNameEsc,
						'type_switch_block' => $typeSwitchBlock,
						'user_steam_id' => htmlspecialchars($userSteamId, ENT_QUOTES, 'UTF-8'),
					]);
				}
			}
		}

		if($premium->has_feature($activeTariff, 'profile_avatar')) {
			$settingsHtml .= pb_premium_tpl('avatar_default_script', [
				'avatar_url_json' => json_encode(get_user_avatar_src($userId, null, true), JSON_UNESCAPED_SLASHES),
			]);
		}

		$cardsHtml = '';

		if($premium->has_feature($activeTariff, 'hide_profile')) {
			$cardsHtml .= pb_premium_tpl('toggle_card', [
				'icon' => 'bx-low-vision',
				'title' => 'Скрыть профиль',
				'description' => 'Профиль будет виден только вам и администрации',
				'input_id' => 'premium_user_hide_profile',
				'field' => 'hide_profile',
				'checked' => !empty($userSettings->hide_profile) ? ' checked' : '',
			]);
		}

		if($premium->has_feature($activeTariff, 'hide_online_status')) {
			$cardsHtml .= pb_premium_tpl('toggle_card', [
				'icon' => 'bx-ghost',
				'title' => 'Скрыть онлайн статус',
				'description' => 'Другие не увидят, когда вы онлайн',
				'input_id' => 'premium_user_hide_online',
				'field' => 'hide_online_status',
				'checked' => !empty($userSettings->hide_online_status) ? ' checked' : '',
			]);
		}

		if($premium->has_feature($activeTariff, 'incognito')) {
			$cardsHtml .= pb_premium_tpl('toggle_card', [
				'icon' => 'bx-hide',
				'title' => 'Инкогнито',
				'description' => 'Посещения вашего профиля не будут отображаться в списке посетителей',
				'input_id' => 'premium_user_incognito',
				'field' => 'incognito',
				'checked' => !empty($userSettings->incognito) ? ' checked' : '',
			]);
		}

		if($premium->has_feature($activeTariff, 'chat_message_color')) {
			$chatColor = !empty($userSettings->chat_color) ? $userSettings->chat_color : '#4fea9f';
			$cardsHtml .= pb_premium_tpl('chat_color_card', [
				'color' => htmlspecialchars($chatColor, ENT_QUOTES, 'UTF-8'),
			]);
		}

		$cardsHtml .= pb_premium_render_settings_card($premium, $activeTariff, 'profile_frame', 'bx-shape-square', 'Рамка профиля', 'Уникальная рамка вокруг вашего аватара, видна всем на сайте', pb_premium_render_resource_picker($premium, $activeTariff, $userSettings, 'profile_frame', 'frame', 'frame_resource'));
		$cardsHtml .= pb_premium_render_settings_card($premium, $activeTariff, 'profile_bg', 'bx-image', 'Фон профиля', 'Эксклюзивный фон на вашей личной странице профиля', pb_premium_render_resource_picker($premium, $activeTariff, $userSettings, 'profile_bg', 'background', 'background_resource'));
		$cardsHtml .= pb_premium_render_settings_card($premium, $activeTariff, 'profile_avatar', 'bx-user-circle', 'Аватар', 'Эксклюзивный аватар из набора, доступного по вашему тарифу', pb_premium_render_resource_picker($premium, $activeTariff, $userSettings, 'profile_avatar', 'avatar', 'avatar_resource'));
		$cardsHtml .= pb_premium_render_settings_card($premium, $activeTariff, 'site_background', 'bx-image-alt', 'Фон сайта', 'Применяется у вас по всему сайту, а не только в профиле', pb_premium_render_resource_picker($premium, $activeTariff, $userSettings, 'site_background', 'site_background', 'site_background'));
		$cardsHtml .= pb_premium_render_settings_card($premium, $activeTariff, 'profile_music', 'bx-music', 'Музыка профиля', 'Трек, который будет играть, когда кто-то заходит в ваш профиль', pb_premium_render_sound_picker($premium, $activeTariff, $userSettings, 'profile_music', 'sound', 'sound_resource'));
		$cardsHtml .= pb_premium_render_settings_card($premium, $activeTariff, 'profile_video_intro', 'bx-movie-play', 'Видео-заставка профиля', 'Полноэкранная заставка с видео, которую увидит любой, кто зайдёт в ваш профиль', pb_premium_render_video_picker($premium, $activeTariff, $userSettings, 'profile_video_intro', 'video', 'video_resource'));

		if($premium->has_feature($activeTariff, 'sticker_packs') && !empty($activeTariff->stickerpack_ids) && class_exists('PbgSmiles')) {
			$stickersBody = '';
			foreach($activeTariff->stickerpack_ids as $packId) {
				$pack = PbgSmiles::pack($packId);
				if(empty($pack) || (int) $pack->enabled !== 1) {
					continue;
				}
				$items = array_slice(PbgSmiles::packItems($pack), 0, 6);
				if(empty($items)) {
					continue;
				}

				$stickerItemsHtml = '';
				foreach($items as $item) {
					$src = htmlspecialchars('/' . PbgSmiles::storageUrlPath() . '/' . (!empty($pack->folder) ? $pack->folder : translit($pack->name)) . '/' . $item, ENT_QUOTES, 'UTF-8');
					$stickerItemsHtml .= PbgSmiles::mediaTag($src, 'premium-tariff-sticker-pack__item');
				}

				$stickersBody .= pb_premium_tpl('sticker_pack', [
					'items_html' => $stickerItemsHtml,
					'name' => htmlspecialchars($pack->name, ENT_QUOTES, 'UTF-8'),
				]);
			}

			if($stickersBody !== '') {
				$cardsHtml .= pb_premium_render_settings_card($premium, $activeTariff, 'sticker_packs', 'bx-smile', 'Стикер-паки', 'Эксклюзивные стикеры, доступные вам по подписке', '<div class="premium-tariff-stickers">' . $stickersBody . '</div>');
			}
		}

		$settingsHtml .= pb_premium_tpl('panel_settings', [
			'avatar_badge' => htmlspecialchars($userSettings->avatar_badge ?? '', ENT_QUOTES, 'UTF-8'),
			'cards_html' => $cardsHtml,
		]);
	}

	return [
		'is_premium' => $isPremium,
		'tariff_cards' => $tariffCards,
		'settings_html' => $settingsHtml,
		'service_modal_html' => $serviceModalHtml,
	];
}

function pb_premium_render_panel_wrap_html($panel)
{
	return $panel['is_premium']
		? pb_premium_tpl('panel_wrap_active', ['settings_html' => $panel['settings_html']])
		: pb_premium_tpl('panel_wrap_inactive', ['tariff_cards' => $panel['tariff_cards']]);
}

function pb_premium_render_settings_card($premium, $tariff, $feature, $icon, $title, $description, $body)
{
	if(!$premium->has_feature($tariff, $feature) || $body === '') {
		return '';
	}

	return pb_premium_tpl('settings_card', [
		'icon' => htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'),
		'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
		'description' => htmlspecialchars($description, ENT_QUOTES, 'UTF-8'),
		'body' => $body,
	]);
}

function pb_premium_render_resource_picker($premium, $tariff, $userSettings, $feature, $resourceType, $currentField)
{
	if(!$premium->has_feature($tariff, $feature)) {
		return '';
	}

	$items = $tariff->{'resources_' . $resourceType} ?? [];
	if(empty($items)) {
		return '';
	}

	$current = $userSettings->{$currentField} ?? '';
	if($current === '' && !empty($items[0])) {
		$current = $items[0]->resource_path;
	}

	$isNone = ($current === Premium::NONE_RESOURCE);
	$fieldEsc = htmlspecialchars($currentField, ENT_QUOTES, 'UTF-8');

	$itemsHtml = '';
	foreach($items as $item) {
		$isActive = (!$isNone && $item->resource_path === $current);
		$itemExt = strtolower(pathinfo((string) $item->resource_path, PATHINFO_EXTENSION));
		$itemBlock = ($itemExt === 'mp4' || $itemExt === 'webm') ? 'resource_picker_item_video' : 'resource_picker_item';
		$itemsHtml .= pb_premium_tpl($itemBlock, [
			'active_class' => $isActive ? ' is-active' : '',
			'field' => $fieldEsc,
			'value' => htmlspecialchars($item->resource_path, ENT_QUOTES, 'UTF-8'),
			'checked' => $isActive ? ' checked' : '',
		]);
	}

	return pb_premium_tpl('resource_picker', [
		'field' => $fieldEsc,
		'none_active_class' => $isNone ? ' is-active' : '',
		'none_value' => htmlspecialchars(Premium::NONE_RESOURCE, ENT_QUOTES, 'UTF-8'),
		'none_checked' => $isNone ? ' checked' : '',
		'items_html' => $itemsHtml,
	]);
}

function pb_premium_render_sound_picker($premium, $tariff, $userSettings, $feature, $resourceType, $currentField)
{
	if(!$premium->has_feature($tariff, $feature)) {
		return '';
	}

	$items = $tariff->{'resources_' . $resourceType} ?? [];
	if(empty($items)) {
		return '';
	}

	$current = $userSettings->{$currentField} ?? '';
	if($current === '' && !empty($items[0])) {
		$current = $items[0]->resource_path;
	}

	$isNone = ($current === Premium::NONE_RESOURCE);
	$fieldEsc = htmlspecialchars($currentField, ENT_QUOTES, 'UTF-8');

	$itemsHtml = '';
	foreach($items as $item) {
		$isActive = (!$isNone && $item->resource_path === $current);
		$itemsHtml .= pb_premium_tpl('sound_picker_item', [
			'active_class' => $isActive ? ' is-active' : '',
			'field' => $fieldEsc,
			'value' => htmlspecialchars($item->resource_path, ENT_QUOTES, 'UTF-8'),
			'checked' => $isActive ? ' checked' : '',
			'name' => htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'),
			'src' => htmlspecialchars('/templates/solution2/img/premium/' . $item->resource_path, ENT_QUOTES, 'UTF-8'),
		]);
	}

	return pb_premium_tpl('sound_picker', [
		'field' => $fieldEsc,
		'none_active_class' => $isNone ? ' is-active' : '',
		'none_value' => htmlspecialchars(Premium::NONE_RESOURCE, ENT_QUOTES, 'UTF-8'),
		'none_checked' => $isNone ? ' checked' : '',
		'items_html' => $itemsHtml,
	]);
}

function pb_premium_render_video_picker($premium, $tariff, $userSettings, $feature, $resourceType, $currentField)
{
	if(!$premium->has_feature($tariff, $feature)) {
		return '';
	}

	$items = $tariff->{'resources_' . $resourceType} ?? [];
	if(empty($items)) {
		return '';
	}

	$current = $userSettings->{$currentField} ?? '';
	if($current === '' && !empty($items[0])) {
		$current = $items[0]->resource_path;
	}

	$isNone = ($current === Premium::NONE_RESOURCE);
	$fieldEsc = htmlspecialchars($currentField, ENT_QUOTES, 'UTF-8');

	$itemsHtml = '';
	foreach($items as $item) {
		$isActive = (!$isNone && $item->resource_path === $current);
		$itemsHtml .= pb_premium_tpl('video_picker_item', [
			'active_class' => $isActive ? ' is-active' : '',
			'field' => $fieldEsc,
			'value' => htmlspecialchars($item->resource_path, ENT_QUOTES, 'UTF-8'),
			'checked' => $isActive ? ' checked' : '',
			'name' => htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'),
		]);
	}

	return pb_premium_tpl('video_picker', [
		'field' => $fieldEsc,
		'none_active_class' => $isNone ? ' is-active' : '',
		'none_value' => htmlspecialchars(Premium::NONE_RESOURCE, ENT_QUOTES, 'UTF-8'),
		'none_checked' => $isNone ? ' checked' : '',
		'items_html' => $itemsHtml,
	]);
}

function pb_premium_render_tariff_drawer($tariff, $isNew = false)
{
	$id = $isNew ? 0 : (int) $tariff->id;
	$prefix = $isNew ? 'premium-drawer-new' : 'premium-drawer-' . $id;
	$features = $isNew ? [] : $tariff->features_decoded;
	$stickerIds = $isNew ? [] : $tariff->stickerpack_ids;

	$esc = function($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

	$basicSection = pb_premium_tpl('drawer_basic_section', [
		'name' => $esc($isNew ? '' : $tariff->name),
		'sort' => $esc($isNew ? 0 : $tariff->sort),
		'description' => $esc($isNew ? '' : $tariff->description),
		'price' => $esc($isNew ? 0 : $tariff->price),
		'duration_days' => $esc($isNew ? 30 : $tariff->duration_days),
		'discount_percent' => $esc($isNew ? 0 : $tariff->discount_percent),
		'active_checked' => ($isNew || (int) $tariff->active === 1) ? ' checked' : '',
		'featured_checked' => (!$isNew && (int) $tariff->is_featured === 1) ? ' checked' : '',
	]);

	$featureItemsHtml = '';
	foreach (Premium::FEATURES as $fKey => $fLabel) {
		$featureItemsHtml .= pb_premium_tpl('drawer_feature_item', [
			'key' => $esc($fKey),
			'checked' => !empty($features[$fKey]) ? ' checked' : '',
			'label' => $esc($fLabel),
		]);
	}
	$featuresSection = pb_premium_tpl('drawer_features_section', ['items_html' => $featureItemsHtml]);

	$badgeItemsHtml = '';
	try {
		$badgeLibraryItems = (new Premium(pdo()))->get_library_resources('badge');
		foreach ($badgeLibraryItems as $libraryItem) {
			$badgeItemsHtml .= pb_premium_tpl('drawer_badge_item', [
				'active_class' => (!$isNew && $tariff->badge_icon === $libraryItem->resource_path) ? ' active' : '',
				'value' => $esc($libraryItem->resource_path),
			]);
		}
		if (empty($badgeLibraryItems)) {
			$badgeItemsHtml .= pb_premium_tpl('drawer_library_empty', ['message' => 'В библиотеке ресурсов ещё нет значков. Загрузите их в блоке «Библиотека ресурсов».']);
		}
	} catch (Throwable $e) {}
	$badgeSection = pb_premium_tpl('drawer_badge_section', [
		'items_html' => $badgeItemsHtml,
		'badge_icon' => $esc($isNew ? '' : $tariff->badge_icon),
	]);

	$premiumForLibrary = new Premium(pdo());
	$resourceSections = '';
	foreach ([
		['frame', 'Рамки профиля (выбор из библиотеки ресурсов)'],
		['background', 'Фоны профиля (выбор из библиотеки ресурсов)'],
		['avatar', 'Аватары (выбор из библиотеки ресурсов)'],
		['site_background', 'Фоны сайта (выбор из библиотеки ресурсов)'],
		['sound', 'Музыка профиля (выбор из библиотеки ресурсов)'],
		['video', 'Видео-заставка профиля (выбор из библиотеки ресурсов, до 300 МБ)'],
	] as $resourceDef) {
		[$type, $label] = $resourceDef;
		$selectedIds = [];
		if(!$isNew) {
			foreach (($tariff->{'resources_' . $type} ?? []) as $item) {
				$selectedIds[] = (int) $item->id;
			}
		}

		$resourceItemBlock = 'drawer_resource_item_image';
		if($type === 'sound') {
			$resourceItemBlock = 'drawer_resource_item_sound';
		} elseif($type === 'video') {
			$resourceItemBlock = 'drawer_resource_item_video';
		}

		$resourceItemsHtml = '';
		try {
			$libraryItems = $premiumForLibrary->get_library_resources($type);
			foreach ($libraryItems as $libraryItem) {
				$checked = in_array((int) $libraryItem->id, $selectedIds, true) ? ' checked' : '';
				$resourceItemsHtml .= pb_premium_tpl($resourceItemBlock, [
					'active_class' => $checked !== '' ? ' is-active' : '',
					'id' => (int) $libraryItem->id,
					'checked' => $checked,
					'value' => $esc($libraryItem->resource_path),
					'name' => $esc($libraryItem->name),
				]);
			}
			if (empty($libraryItems)) {
				$resourceItemsHtml .= pb_premium_tpl('drawer_library_empty', ['message' => 'В библиотеке ресурсов ещё нет файлов этого типа. Загрузите их в блоке «Библиотека ресурсов».']);
			}
		} catch (Throwable $e) {}

		$resourceSections .= pb_premium_tpl('drawer_resource_section', [
			'label' => $esc($label),
			'type' => $esc($type),
			'items_html' => $resourceItemsHtml,
		]);
	}

	$selectedServiceId = 0;
	if(!$isNew && !empty($tariff->service_tarif_id)) {
		try {
			$sth = pdo()->prepare("SELECT `service` FROM `services__tarifs` WHERE `id`=:id LIMIT 1");
			$sth->execute([':id' => (int) $tariff->service_tarif_id]);
			$selectedServiceId = (int) $sth->fetchColumn();
		} catch (Throwable $e) {}
	}

	$serviceOptionsHtml = '';
	try {
		$serviceRows = pdo()->query(
			"SELECT s.`id` AS service_id, s.`name` AS service_name, srv.`name` AS server_name
			 FROM `services` s
			 JOIN `servers` srv ON srv.`id` = s.`server`
			 ORDER BY srv.`trim` ASC, s.`name` ASC"
		)->fetchAll(PDO::FETCH_OBJ);
		foreach ($serviceRows as $serviceRow) {
			$serviceOptionsHtml .= pb_premium_tpl('drawer_service_option', [
				'id' => (int) $serviceRow->service_id,
				'selected' => ($selectedServiceId === (int) $serviceRow->service_id) ? ' selected' : '',
				'server_name' => $esc($serviceRow->server_name),
				'service_name' => $esc($serviceRow->service_name),
			]);
		}
	} catch (Throwable $e) {}
	$serviceSection = pb_premium_tpl('drawer_service_section', ['options_html' => $serviceOptionsHtml]);

	$stickerItemsHtml = '';
	if (class_exists('PbgSmiles')) {
		foreach (PbgSmiles::packs(false) as $pack) {
			$stickerItemsHtml .= pb_premium_tpl('drawer_sticker_item', [
				'id' => (int) $pack->id,
				'checked' => in_array((int) $pack->id, $stickerIds, true) ? ' checked' : '',
				'name' => $esc($pack->name),
			]);
		}
	} else {
		$stickerItemsHtml .= pb_premium_tpl('drawer_library_empty', ['message' => 'Модуль стикеров недоступен.']);
	}
	$stickersSection = pb_premium_tpl('drawer_stickers_section', ['items_html' => $stickerItemsHtml]);

	$customPerkIds = $isNew ? [] : $tariff->custom_perk_ids;
	$perkItemsHtml = '';
	try {
		$perkRows = pdo()->query("SELECT * FROM `premium__custom_perks` WHERE `active`='1' ORDER BY `sort` ASC, `id` ASC")->fetchAll(PDO::FETCH_OBJ);
		foreach ($perkRows as $perk) {
			$perkItemsHtml .= pb_premium_tpl('drawer_perk_item', [
				'id' => (int) $perk->id,
				'checked' => in_array((int) $perk->id, $customPerkIds, true) ? ' checked' : '',
				'name' => $esc($perk->name),
			]);
		}
		if (empty($perkRows)) {
			$perkItemsHtml .= pb_premium_tpl('drawer_library_empty', ['message' => 'Дополнительные привилегии ещё не созданы.']);
		}
	} catch (Throwable $e) {}
	$perksSection = pb_premium_tpl('drawer_perks_section', ['items_html' => $perkItemsHtml]);

	$footer = pb_premium_tpl('drawer_footer', [
		'remove_button' => !$isNew ? pb_premium_tpl('drawer_remove_button', ['id' => $id]) : '',
		'id' => $id,
	]);

	return pb_premium_tpl('drawer_wrap', [
		'prefix' => $prefix,
		'id' => $id,
		'basic_section' => $basicSection,
		'features_section' => $featuresSection,
		'badge_section' => $badgeSection,
		'resource_sections' => $resourceSections,
		'service_section' => $serviceSection,
		'stickers_section' => $stickersSection,
		'perks_section' => $perksSection,
		'footer' => $footer,
	]);
}

