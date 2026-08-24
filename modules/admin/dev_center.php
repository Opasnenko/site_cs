<?php
if(!is_admin()){
	show_error_page('not_adm');
}
if(function_exists('pb_ensure_admin_utility_pages')) {
	pb_ensure_admin_utility_pages($pdo);
}

$tpl->load_template('elements/title.tpl');
$tpl->set('{title}', $page->title);
$tpl->set('{name}', $conf->name);
$tpl->compile('title');
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set('{title}', $tpl->result['title']);
$tpl->set('{image}', $page->image);
$tpl->set('{other}', '');
$tpl->set('{token}', $token);
$tpl->set('{cache}', $conf->cache);
$tpl->set('{template}', $conf->template);
$tpl->set('{site_host}', $site_host);
$tpl->compile('content');
$tpl->clear();

$tpl->load_template('top.tpl');
$tpl->set('{site_host}', $site_host);
$tpl->set('{site_name}', $conf->name);
$tpl->compile('content');
$tpl->clear();

$tpl->load_template('menu.tpl');
$tpl->set('{site_host}', $site_host);
$tpl->compile('content');
$tpl->clear();

$nav = array(
    $PI->to_nav('admin', 0, 0),
    $PI->to_nav('admin_dev_center', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

$tpl->load_template('page_top.tpl');
$tpl->set('{nav}', $nav);
$tpl->compile('content');
$tpl->clear();

/* Флаг для плашки-рекомендации 2FA: показываем, пока не добавлен ни один ключ доступа. */
require_once __DIR__ . '/../../inc/pb_passkey.php';
$pbShowSecurityAlert = !(function_exists('pb_passkey_has_keys') && pb_passkey_has_keys($pdo));

$STH = $pdo->query("SELECT COUNT(*) as count FROM chat");
$STH->setFetchMode(PDO::FETCH_ASSOC);
$chat = $STH->fetch();


$time_zones = "";
for ($i=0; $i <= 12; $i++) {
    $zone = 'Etc/GMT-'.$i;
    $zone_name = substr(str_replace("-", "+", $zone), 4);
    if($conf->time_zone == $zone) {
        $time_zones .= "<option value='".$zone."' selected>".$zone_name."</option>";
    } else {
        $time_zones .= "<option value='".$zone."'>".$zone_name."</option>";
    }
}
for ($i=1; $i <= 12; $i++) {
    $zone = 'Etc/GMT+'.$i;
    $zone_name = substr(str_replace("+", "-", $zone), 4);
    if($conf->time_zone == $zone) {
        $time_zones .= "<option value='".$zone."' selected>".$zone_name."</option>";
    } else {
        $time_zones .= "<option value='".$zone."'>".$zone_name."</option>";
    }
}


$STH = $pdo->query("SELECT * FROM config__secondary LIMIT 1");
$STH->setFetchMode(PDO::FETCH_OBJ);
$conf2 = $STH->fetch();

$act = get_active($dev_mode, 2);
$tpl->set('{developer_mode}', $act[0]);
$tpl->set('{developer_mode2}', $act[1]);
$tpl->set('{dev_key}', $act[0] == 'active' ? '********************************' : '');
$devModeEnabled = ((int)$dev_mode === 1);
$tpl->set('{developer_mode_status_class}', $devModeEnabled ? 'on' : 'off');
$tpl->set('{developer_mode_status_text}', $devModeEnabled ? 'Включен' : 'Выключен');
$tpl->set('{developer_mode_status_hint}', $devModeEnabled ? 'Ошибки и tpl-подсказки сейчас активны.' : 'Публичная часть работает в обычном режиме.');
$tpl->set('{developer_mode_status_icon}', $devModeEnabled ? 'warning-sign' : 'ok-circle');

try {
	$pdo->exec("UPDATE `config__updates` SET `name`='PBG DE' WHERE `id`=1 AND (`name`='Основной сервер' OR `name`='основной сервер' OR `name`='PBGame UA' OR `name`='PBG FI')");
} catch (Exception $e) {
	if(function_exists('write_log')) {
		write_log('PBGame update server label migration error: ' . $e->getMessage());
	}
}


$act = get_active($conf->off, 2);
$tpl->set('{off_act}', $act[0]);
$tpl->set('{off_act2}', $act[1]);

$siteIsOff = ((int)$conf->off === 1);
$tpl->set('{off_status_class}', $siteIsOff ? 'danger' : 'success');
$tpl->set('{off_status_text}', $siteIsOff ? 'Выключен' : 'Включен');

$tpl->set('{token}', $token);
$tpl->set('{site_host}', $site_host);
$tpl->set('{host}', $host);
$tpl->set('{time_zone}', $conf->time_zone);
$tpl->set('{time_zones}', $time_zones);
$tpl->set('{admins_ids}', $conf2->admins_ids);
$tpl->set('{off_message}', $conf2->off_message);
$tpl->set('{chat_number}', $chat['count']);
$tpl->set('{update_servers}', get_update_servers($pdo));
$tpl->set('{current_version}', $conf2->version);
$tpl->set('{site_name}', htmlspecialchars($conf->name, ENT_QUOTES, 'UTF-8'));
$tpl->set('{name}', htmlspecialchars($conf->name, ENT_QUOTES, 'UTF-8'));

if(!function_exists('pb_dev_center_ini_bytes')) {
	function pb_dev_center_ini_bytes($value) {
		$value = trim((string)$value);
		if($value === '' || $value === '-1') {
			return -1;
		}
		$unit = strtolower(substr($value, -1));
		$number = (float)$value;
		if($unit === 'g') {
			return (int)($number * 1024 * 1024 * 1024);
		}
		if($unit === 'm') {
			return (int)($number * 1024 * 1024);
		}
		if($unit === 'k') {
			return (int)($number * 1024);
		}
		return (int)$number;
	}
}

if(!function_exists('pb_dev_center_server_row')) {
	function pb_dev_center_server_row($label, $value, $state = 'success', $note = '', $href = '') {
		$value = ($value === '' || $value === null) ? 'Не определено' : (string)$value;
		$tag = $href !== '' ? 'a' : 'div';
		$html = '<' . $tag . ($href !== '' ? ' href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" onclick="pbOpenPhpInfo(); return false;"' : '') . ' class="dc-row dc-row--' . htmlspecialchars($state, ENT_QUOTES, 'UTF-8') . ($href !== '' ? ' dc-row--link' : '') . '">';
		$html .= '<span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
		$html .= '<b>' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</b>';
		if($note !== '') {
			$html .= '<small>' . htmlspecialchars($note, ENT_QUOTES, 'UTF-8') . '</small>';
		}
		if($href !== '') {
			$html .= '<em>Открыть phpinfo</em>';
		}
		$html .= '</' . $tag . '>';
		return $html;
	}
}

$serverRows = [];
$serverDanger = 0;
$serverWarning = 0;
$addServerRow = function($label, $value, $state = 'success', $note = '', $href = '') use (&$serverRows, &$serverDanger, &$serverWarning) {
	if($state === 'danger') {
		$serverDanger++;
	}
	if($state === 'warning') {
		$serverWarning++;
	}
	$serverRows[] = pb_dev_center_server_row($label, $value, $state, $note, $href);
};

$phpState = version_compare(PHP_VERSION, '8.1.0', '>=') ? 'success' : 'danger';
$addServerRow('Версия PHP', PHP_VERSION, $phpState, $phpState === 'success' ? 'Рекомендуемая версия установлена.' : 'Требуется PHP 8.1 или выше.', '#pb-phpinfo-modal');

$mysqlVersion = '';
try {
	$mysqlVersion = (string)$pdo->query("SELECT VERSION()")->fetchColumn();
} catch(Throwable $e) {}
$mysqlState = 'warning';
if($mysqlVersion !== '') {
	$mysqlComparable = preg_replace('/[^0-9.].*$/', '', $mysqlVersion);
	$mysqlState = version_compare($mysqlComparable, '5.7.0', '>=') ? 'success' : 'danger';
}
$addServerRow('Версия MySQL', $mysqlVersion, $mysqlState, $mysqlState === 'danger' ? 'Требуется MySQL 5.7 или выше.' : '');

$addServerRow('ПО сервера', isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : php_sapi_name(), 'success');

$memoryLimit = ini_get('memory_limit');
$memoryBytes = pb_dev_center_ini_bytes($memoryLimit);
$addServerRow('PHP memory_limit', $memoryLimit, ($memoryBytes === -1 || $memoryBytes >= 256 * 1024 * 1024) ? 'success' : 'danger', ($memoryBytes !== -1 && $memoryBytes < 256 * 1024 * 1024) ? 'Рекомендуется минимум 256M.' : '');

$postMax = ini_get('post_max_size');
$postBytes = pb_dev_center_ini_bytes($postMax);
$addServerRow('PHP post_max_size', $postMax, ($postBytes === -1 || $postBytes >= 64 * 1024 * 1024) ? 'success' : 'warning', ($postBytes !== -1 && $postBytes < 64 * 1024 * 1024) ? 'Для крупных архивов лучше 64M или выше.' : '');

$uploadMax = ini_get('upload_max_filesize');
$uploadBytes = pb_dev_center_ini_bytes($uploadMax);
$addServerRow('PHP upload_max_filesize', $uploadMax, ($uploadBytes === -1 || $uploadBytes >= 64 * 1024 * 1024) ? 'success' : 'warning', ($uploadBytes !== -1 && $uploadBytes < 64 * 1024 * 1024) ? 'Для крупных архивов лучше 64M или выше.' : '');

$maxInputVars = (int)ini_get('max_input_vars');
$addServerRow('PHP max_input_vars', (string)$maxInputVars, $maxInputVars >= 1000 ? 'success' : 'warning', $maxInputVars < 1000 ? 'Рекомендуется минимум 1000.' : '');

$maxExecution = (int)ini_get('max_execution_time');
$addServerRow('PHP max_execution_time', (string)$maxExecution, ($maxExecution === 0 || $maxExecution >= 120) ? 'success' : 'warning', ($maxExecution > 0 && $maxExecution < 120) ? 'Для обновлений и бекапов лучше 120 секунд или выше.' : '');

$curlVersion = function_exists('curl_version') ? curl_version() : [];
$addServerRow('Версия cURL', !empty($curlVersion['version']) ? $curlVersion['version'] : 'Нет', extension_loaded('curl') ? 'success' : 'danger', extension_loaded('curl') ? '' : 'cURL требуется для обновлений и внешних запросов.');
$addServerRow('Версия cURL SSL', !empty($curlVersion['ssl_version']) ? $curlVersion['ssl_version'] : 'Нет', !empty($curlVersion['ssl_version']) ? 'success' : 'danger', empty($curlVersion['ssl_version']) ? 'SSL для cURL обязателен.' : '');
$addServerRow('Версия OpenSSL', defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'Нет', extension_loaded('openssl') ? 'success' : 'danger', extension_loaded('openssl') ? '' : 'OpenSSL требуется для HTTPS.');
$addServerRow('Включен Snuffleupagus', extension_loaded('snuffleupagus') ? 'Да' : 'Нет', extension_loaded('snuffleupagus') ? 'warning' : 'success', extension_loaded('snuffleupagus') ? 'Может блокировать часть PHP-операций.' : '');
$addServerRow('Поддержка Imagick', extension_loaded('imagick') ? 'Да' : 'Нет', extension_loaded('imagick') ? 'success' : 'warning', extension_loaded('imagick') ? '' : 'Необязательно, но полезно для обработки изображений.');
$addServerRow('Поддержка EXIF', extension_loaded('exif') ? 'Да' : 'Нет', extension_loaded('exif') ? 'success' : 'warning');
$addServerRow('Поддержка GZip', extension_loaded('zlib') ? 'Да' : 'Нет', extension_loaded('zlib') ? 'success' : 'danger', extension_loaded('zlib') ? '' : 'GZip/zlib нужен для архивов и сжатия.');
$addServerRow('Поддержка intl', extension_loaded('intl') ? 'Да' : 'Нет', extension_loaded('intl') ? 'success' : 'warning');
$addServerRow('Поддержка ZipArchive', class_exists('ZipArchive') ? 'Да' : 'Нет', class_exists('ZipArchive') ? 'success' : 'danger', class_exists('ZipArchive') ? '' : 'ZipArchive обязателен для автообновлений и бекапов.');
$addServerRow('Поддержка PDO MySQL', extension_loaded('pdo_mysql') ? 'Да' : 'Нет', extension_loaded('pdo_mysql') ? 'success' : 'danger', extension_loaded('pdo_mysql') ? '' : 'PDO MySQL нужен для работы с базой данных.');

$serverSummaryClass = 'success';
$serverSummaryText = 'Сервер подходит для PBGame CMS.';
if($serverDanger > 0) {
	$serverSummaryClass = 'danger';
	$serverSummaryText = 'Найдены критические проблемы совместимости.';
} elseif($serverWarning > 0) {
	$serverSummaryClass = 'warning';
	$serverSummaryText = 'Сервер работает, но есть рекомендации по настройке.';
}
$tpl->set('{server_info_class}', $serverSummaryClass);
$tpl->set('{server_info_message}', htmlspecialchars($serverSummaryText, ENT_QUOTES, 'UTF-8'));
$tpl->set('{server_info_rows}', implode('', $serverRows));

try {
	$pb_license_api = new System;
	$pb_license = $pb_license_api->license_status();
} catch(Throwable $e) {
	$pb_license = [
		'license_status' => 'inactive',
		'domain' => $_SERVER['SERVER_NAME'],
		'build_id' => defined('PBGAME_BUILD_ID') ? PBGAME_BUILD_ID : 'PB-COMMUNITY',
		'install_id' => defined('PBGAME_INSTALL_ID') ? PBGAME_INSTALL_ID : '',
		'license_url' => 'https://pbgame.top/license/',
		'check_url' => 'https://pbgame.top/license-check/?domain=' . urlencode($_SERVER['SERVER_NAME']),
		'message' => 'Не удалось проверить лицензию. Сайт продолжает работать, но официальные обновления могут быть недоступны.',
		'support' => false,
		'updates' => false,
		'modules' => false
	];
}

$pb_license_status = isset($pb_license['license_status']) ? $pb_license['license_status'] : 'inactive';
$pb_license_titles = [
	'active' => 'Активна',
	'pending' => 'Ожидает подтверждения',
	'rejected' => 'Отклонена',
	'blocked' => 'Заблокирована',
	'inactive' => 'Не активирована'
];
$pb_license_classes = [
	'active' => 'success',
	'pending' => 'warning',
	'rejected' => 'danger',
	'blocked' => 'danger',
	'inactive' => 'default'
];
$tpl->set('{pb_license_status}', htmlspecialchars(isset($pb_license_titles[$pb_license_status]) ? $pb_license_titles[$pb_license_status] : $pb_license_status, ENT_QUOTES, 'UTF-8'));
$tpl->set('{pb_license_badge_class}', htmlspecialchars(isset($pb_license_classes[$pb_license_status]) ? $pb_license_classes[$pb_license_status] : 'default', ENT_QUOTES, 'UTF-8'));
$tpl->set('{pb_license_domain}', htmlspecialchars(isset($pb_license['domain']) && !empty($pb_license['domain']) ? $pb_license['domain'] : $_SERVER['SERVER_NAME'], ENT_QUOTES, 'UTF-8'));
$tpl->set('{pb_license_build_id}', htmlspecialchars(isset($pb_license['build_id']) && !empty($pb_license['build_id']) ? $pb_license['build_id'] : (defined('PBGAME_BUILD_ID') ? PBGAME_BUILD_ID : 'PB-COMMUNITY'), ENT_QUOTES, 'UTF-8'));
$tpl->set('{pb_license_install_id}', htmlspecialchars(isset($pb_license['install_id']) ? $pb_license['install_id'] : '', ENT_QUOTES, 'UTF-8'));
$tpl->set('{pb_license_message}', htmlspecialchars(isset($pb_license['message']) ? $pb_license['message'] : '', ENT_QUOTES, 'UTF-8'));
$tpl->set('{pb_license_support}', !empty($pb_license['support']) ? '<span class="text-success">доступна</span>' : '<span class="text-danger">недоступна</span>');
$tpl->set('{pb_license_updates}', !empty($pb_license['updates']) ? '<span class="text-success">доступны</span>' : '<span class="text-danger">недоступны</span>');
$tpl->set('{pb_license_modules}', !empty($pb_license['modules']) ? '<span class="text-success">доступны</span>' : '<span class="text-danger">недоступны</span>');
$tpl->set('{pb_license_url}', htmlspecialchars(isset($pb_license['license_url']) ? $pb_license['license_url'] : 'https://pbgame.top/license/', ENT_QUOTES, 'UTF-8'));
$tpl->set('{pb_license_check_url}', htmlspecialchars(isset($pb_license['check_url']) ? $pb_license['check_url'] : ('https://pbgame.top/license-check/?domain=' . urlencode($_SERVER['SERVER_NAME'])), ENT_QUOTES, 'UTF-8'));


$pbDevCenterQuickItems = pb_admin_quick_access_get(isset($pdo) ? $pdo : null, 'dev_center', 5);
$pbDevCenterQuickCatalog = pb_admin_quick_access_catalog();
$pbDevCenterQuickTilesHtml = '';
foreach($pbDevCenterQuickItems as $pbDcqItem) {
	$pbDcqLabel = htmlspecialchars($pbDcqItem['label'], ENT_QUOTES, 'UTF-8');
	$pbDcqIcon = htmlspecialchars($pbDcqItem['icon'], ENT_QUOTES, 'UTF-8');
	if($pbDcqItem['type'] === 'action') {
		$pbDcqValue = htmlspecialchars($pbDcqItem['value'], ENT_QUOTES, 'UTF-8');
		$pbDevCenterQuickTilesHtml .= '<button type="button" class="dc-qa-tile" data-pb-admin-action="' . $pbDcqValue . '" title="' . $pbDcqLabel . '">'
			. '<span class="dc-qa-tile__icon glyphicon glyphicon-' . $pbDcqIcon . '"></span>'
			. '<span class="dc-qa-tile__title">' . $pbDcqLabel . '</span>'
			. '</button>';
	} else {
		$pbDcqHref = htmlspecialchars(pb_admin_quick_access_href($pbDcqItem['value'], $site_host), ENT_QUOTES, 'UTF-8');
		$pbDevCenterQuickTilesHtml .= '<a class="dc-qa-tile" href="' . $pbDcqHref . '" title="' . $pbDcqLabel . '">'
			. '<span class="dc-qa-tile__icon glyphicon glyphicon-' . $pbDcqIcon . '"></span>'
			. '<span class="dc-qa-tile__title">' . $pbDcqLabel . '</span>'
			. '</a>';
	}
}

$tpl->load_template('dev_center.tpl');
$tpl->set('{show_security_alert}', $pbShowSecurityAlert ? '1' : '0');
$tpl->set('{site_host}', $site_host);
$tpl->set('{dc_quick_access_tiles}', $pbDevCenterQuickTilesHtml);
$tpl->set('{dc_quick_access_data}', json_encode($pbDevCenterQuickItems, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
$tpl->set('{dc_quick_access_catalog}', json_encode($pbDevCenterQuickCatalog, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
$tpl->compile('content');
$tpl->clear();

$tpl->load_template('bottom.tpl');
$tpl->set('{site_host}', $site_host);
$tpl->compile('content');
$tpl->clear();
?>
