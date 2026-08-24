<?php
if($page->privacy == 1 && !is_auth()) {
	show_error_page('not_auth');
}

$urlParts = explode('/', $page->originalUrl);

if(count($urlParts) > 1) {
	$row = Users::getIdByRoute($pdo, $urlParts[1]);
	$id = empty($row->id) ? null : $row->id;
} else {
	if(array_key_exists('id', $_GET) || is_auth()) {
		if(array_key_exists('id', $_GET)) {
			$id = clean($_GET['id'], "int");
		} else {
			$id = $_SESSION['id'];
		}

		$row = Users::getRouteById($pdo, $id);

		if(!empty($row->route)) {
			http_response_code(301);
			header('Location: ../' .  PagesInfo::PROFILE_PAGE_URL . '/' . $row->route);
			exit();
		} elseif(!array_key_exists('id', $_GET)) {
			header('Location: ../' .  PagesInfo::PROFILE_PAGE_URL . '?id=' . $id);
			exit();
		}
	} else {
		show_error_page('not_settings');
	}
}

global $profile;

if(!$profile = Users::getUserData($pdo, $id)) {
	show_error_page();
}

$viewerId = is_auth() ? (int) $_SESSION['id'] : 0;
if(function_exists('pb_premium_is_profile_hidden_from') && pb_premium_is_profile_hidden_from($profile->id, $viewerId)) {
	show_error_page();
}

if(is_auth() && function_exists('pb_track_profile_visit')) {
	pb_track_profile_visit($profile->id, $_SESSION['id']);
}

// Безопасные значения для профиля: на обновлённых/частично мигрированных БД
// некоторые новые или модульные поля могут отсутствовать. Профиль не должен
// падать с 500 из-за пустого необязательного поля.
$profileDefaults = [
	'last_topic' => 0,
	'last_activity' => '0000-00-00 00:00:00',
	'avatar' => '',
	'rights' => 1,
	'name' => '',
	'answers' => 0,
	'thanks' => 0,
	'telegram' => '',
	'Telegram' => '',
	'nick' => '',
	'skype' => '',
	'discord' => '',
	'reit' => 0,
	'vk' => 0,
	'vk_api' => 0,
	'fb' => 0,
	'fb_api' => 0,
	'steam_api' => 0,
		'google_api' => '0',
	'telegram_api' => '0',
	'telegram_username' => '',
	'discord_api' => '0',
	'discord_username' => '',
	'steam_id' => '',
	'birth' => '0000-00-00',
	'dell' => 0,
	'shilings' => 0,
	'playground' => 0,
	'proc' => 0,
	'ip' => '',
	'profile_status' => '',
	'geo_country_iso' => '',
	'geo_country_en' => '',
	'geo_city_en' => '',
	'geo_hidden' => 0
];
foreach($profileDefaults as $profileDefaultKey => $profileDefaultValue) {
	if(!property_exists($profile, $profileDefaultKey) || $profile->{$profileDefaultKey} === null) {
		$profile->{$profileDefaultKey} = $profileDefaultValue;
	}
}

if(!empty($profile->last_topic)) {
	$STH = $pdo->prepare("SELECT `name` FROM `forums__topics` WHERE `id`=:id LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute(array(':id' => $profile->last_topic));
	$row = $STH->fetch();
	if(isset($row->name)) {
		$profile->topic_name = $row->name;
	} else {
		$profile->topic_name = '';
		$profile->last_topic = 0;
	}
} else {
	$profile->last_topic = 0;
	$profile->topic_name = '';
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $PI->compile_str($page->title, $profile->login));
$tpl->set("{name}", $conf->name);
$tpl->compile('title');
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{image}", $PI->compile_img_str($profile->avatar));
$tpl->set("{robots}", $page->robots);
$tpl->set("{type}", $page->kind);
$tpl->set("{description}", $PI->compile_str($page->description, $profile->login));
$tpl->set("{keywords}", $PI->compile_str($page->keywords, $profile->login));
$tpl->set("{url}", $page->full_url);
$pbProfileAssets = '<script src="{site_host}modules/editors/tinymce/tinymce.min.js"></script>';

if(is_file($_SERVER['DOCUMENT_ROOT'] . '/modules_extra/rcon_shop/base/inc/config.php')) {
	$pbProfileAssets .= '<link rel="stylesheet" href="' . $site_host . 'modules_extra/rcon_shop/templates/' . $conf->template . '/css/primary.css?v=' . $conf->cache . '">';
}

$tpl->set("{other}", $pbProfileAssets);
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile('content');
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = array($PI->to_nav('main', 0, 0),
			 $PI->to_nav('users', 0, 0),
			 $PI->to_nav('profile', 1, 0, $profile->login));
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

if(is_auth()) {
	include_once "inc/authorized.php";
} else {
	include_once "inc/not_authorized.php";
}

if(is_auth()) {
	$STH = $pdo->query("SELECT id FROM users__friends WHERE ((id_sender = '$id' AND id_taker = '$_SESSION[id]') OR (id_sender = '$_SESSION[id]' AND id_taker = '$id')) AND accept = '1' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(empty($row->id)) {
		if($_SESSION['id'] != $profile->id) {
			$checker = '1';
		} else {
			$checker = '2';
		}
	} else {
		$checker = '2';
	}
} else {
	$checker = '1';
}

$premiumHideOnline = false;
if(function_exists('premium')) {
	try {
		$premiumHideOnline = ($profile->id != $viewerId) && premium()->is_premium($profile->id)
			&& !empty(premium()->get_user_settings($profile->id)->hide_online_status);
	} catch(Throwable $e) {
		$premiumHideOnline = false;
	}
}

if($premiumHideOnline) {
	$last_activity = '<span id="soffline">'.'</span>'.$messages['Was_online'].$messages['Bc'];
} else {
	$STH = $pdo->query("SELECT id FROM users__online WHERE user_id='$id' LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$row = $STH->fetch();
	if(isset($row->id)) {
	    $last_activity = '<span id="sonline">'.'</span>'.$messages['Online'];
	} else {
	    if($profile->last_activity == '0000-00-00 00:00:00') {
	        $last_activity = $messages['Was_online'].$messages['Bc'];
	    } else {
	        $last_activity = expand_date($profile->last_activity, 7);
	        $last_activity = '<span id="soffline">'.'</span>'.$messages['Was_online'].$last_activity;
	    }
	}
}

$tpl->result['friends'] = '';
$profileVery = new Verification($pdo);


$publications = 0;
$STH = $pdo->prepare("SELECT COUNT(*) FROM `forums__topics` WHERE `author` = :author");
$STH->execute([':author' => $profile->id]);
$publications = (int) $STH->fetchColumn();


$STH = $pdo->query("SELECT users__friends.id_taker, users.id, users.login, users.avatar, users.rights FROM users__friends LEFT JOIN users on users__friends.id_taker = users.id WHERE (users__friends.id_sender='$profile->id') AND users__friends.accept='1' UNION SELECT users__friends.id_sender, users.id, users.login, users.avatar, users.rights FROM users__friends LEFT JOIN users ON users__friends.id_sender = users.id WHERE (users__friends.id_taker='$profile->id') AND users__friends.accept='1'");
$STH->setFetchMode(PDO::FETCH_OBJ);
while($row = $STH->fetch()) {
	$friend_group = $users_groups[$row->rights] ?? ['color' => '#999', 'name' => 'Пользователь'];
	$tpl->load_template('elements/mini_friend.tpl');
	$tpl->set("{id}", $row->id);
	$tpl->set("{avatar}", ltrim(convert_avatar($row->id), "/"));
	$tpl->set("{avatar_frame}", ltrim(get_user_frame($row->id), "/"));
	$tpl->set("{login}", $row->login);
	$tpl->set("{gp_color}", $friend_group['color']);
	$tpl->set("{gp_name}", $friend_group['name']);
	$tpl->set("{verification}", $profileVery->render_badge($row->id, $conf->template));
	$tpl->compile('friends');
	$tpl->clear();
}
if($tpl->result['friends'] == '') {
	$tpl->result['friends'] = '<span class="empty-element">'.$messages['There_are_no_friends'].'</span>';
}

$tpl->result['visitors'] = '';
if(function_exists('pb_get_profile_visitors')) {
	$visitorsRows = pb_get_profile_visitors($profile->id, 4);
	foreach($visitorsRows as $visitorRow) {
		$visitorGroup = $users_groups[$visitorRow->rights] ?? ['color' => '#999', 'name' => 'Пользователь'];
		$tpl->load_template('elements/mini_visitor.tpl');
		$tpl->set("{id}", (int) $visitorRow->visitor_id);
		$tpl->set("{avatar}", ltrim(convert_avatar($visitorRow->visitor_id), "/"));
		$tpl->set("{avatar_frame}", ltrim(get_user_frame($visitorRow->visitor_id), "/"));
		$tpl->set("{login}", htmlspecialchars((string) $visitorRow->login, ENT_QUOTES, 'UTF-8'));
		$tpl->set("{gp_color}", $visitorGroup['color']);
		$tpl->compile('visitors');
		$tpl->clear();
	}
}

if(empty($profile->fb)) {
	$profile->fb = 0;
}
if(empty($profile->fb_api)) {
	$profile->fb_api = 0;
}

$isFriend = 'false';
$issetFriendRequestFromMe = 'false';
$issetFriendRequestFromHim = 'false';

if(is_auth()) {
	$STH = $pdo->prepare("SELECT id, id_sender, id_taker, accept FROM users__friends WHERE (id_sender=:friend_id AND id_taker=:my_id) OR (id_sender=:my_id AND id_taker=:friend_id) LIMIT 1");
	$STH->setFetchMode(PDO::FETCH_OBJ);
	$STH->execute([':my_id' => $_SESSION['id'], ':friend_id' => $profile->id]);
	$row = $STH->fetch();
}

if(isset($row->id) && ($row->accept == 1)) {
	$isFriend = 'true';
}
if(isset($row->id) && ($row->accept == 0)) {
	if($row->id_sender == $_SESSION['id']) {
		$issetFriendRequestFromMe = 'true';
	}
	if($row->id_taker == $_SESSION['id']) {
		$issetFriendRequestFromHim = 'true';
	}
}

$editor_settings = get_editor_settings($pdo);
$tpl->load_template('/home/profile.tpl');
$tpl->set("{file_manager}", $editor_settings['file_manager']);
$tpl->set("{file_manager_theme}", $editor_settings['file_manager_theme']);
$tpl->set("{site_host}", $site_host);
$tpl->set("{last_activity}", $last_activity);
$tpl->set("{template}", $conf->template);
$tpl->set("{profile_id}", $profile->id);
$tpl->set("{login}", $profile->login);
$tpl->set("{avatar}", ltrim((string) $profile->avatar, "/"));
$tpl->set("{avatar_frame}", ltrim(get_user_frame($profile->id), "/"));
$profile_group = $users_groups[$profile->rights] ?? ['color' => '#999', 'name' => 'Пользователь'];
$tpl->set("{group}", $profile_group['name']);
$tpl->set("{group_color}", $profile_group['color']);
$tpl->set("{regdate}", expand_date($profile->regdate, 1));
$daysWithUs = 0;
if(!empty($profile->regdate) && $profile->regdate !== '0000-00-00 00:00:00') {
	$regTimestamp = strtotime($profile->regdate);
	if($regTimestamp !== false) {
		$daysWithUs = max(0, (int) floor((time() - $regTimestamp) / 86400));
	}
}
$tpl->set("{days_with_us}", $daysWithUs);
$tpl->set("{name}", $profile->name);
$tpl->set("{publications}", $publications);
$tpl->set("{answers}", $profile->answers);
$tpl->set("{thanks}", $profile->thanks);
$tpl->set("{warns_count}", function_exists('pb_admin_warns_count_user') ? pb_admin_warns_count_user($pdo, (int)$profile->id) : 0);
$tpl->set("{warns_limit}", function_exists('pb_admin_warns_limit') ? pb_admin_warns_limit($pdo) : 3);
$tpl->set("{telegram}", $profile->telegram);
$tpl->set("{nick}", $profile->nick);
$tpl->set("{status}", $profile->profile_status);
$tpl->set("{skype}", $profile->skype);
$tpl->set("{discord}", $profile->discord);
$tpl->set("{reit}", $profile->reit);
$tpl->set("{topic_id}", $profile->last_topic);
$tpl->set("{topic_name}", $profile->topic_name);
$tpl->set("{checker}", $checker);
$tpl->set("{vk}", $profile->vk);
$tpl->set("{vk_api}", $profile->vk_api);
$tpl->set("{fb}", $profile->fb);
$tpl->set("{fb_api}", $profile->fb_api);
$tpl->set("{steam_api}", $profile->steam_api);
$tpl->set("{steam_id}", $profile->steam_id);
$steamLevelBadge = '';
if(function_exists('pb_steam_profile_level_badge') && !empty($profile->steam_api) && (string)$profile->steam_api !== '0') {
	$steamLevelBadge = pb_steam_profile_level_badge((int)$profile->id, 172);
}
$tpl->set("{steam_level_badge}", $steamLevelBadge);
$profile_google_api = isset($profile->google_api) ? (string) $profile->google_api : '0';
$tpl->set("{google_api}", htmlspecialchars($profile_google_api, ENT_QUOTES, 'UTF-8'));
$tpl->set("{google_bound}", ($profile_google_api !== '' && $profile_google_api !== '0') ? '1' : '0');

$profile_telegram_api = isset($profile->telegram_api) ? (string) $profile->telegram_api : '0';
$profile_telegram_bound = ($profile_telegram_api !== '' && $profile_telegram_api !== '0');
$tpl->set("{telegram_bound}", $profile_telegram_bound ? '1' : '0');
$tpl->set("{telegram_username}", htmlspecialchars(ltrim((string) $profile->telegram_username, '@'), ENT_QUOTES, 'UTF-8'));

$profile_discord_api = isset($profile->discord_api) ? (string) $profile->discord_api : '0';
$profile_discord_bound = ($profile_discord_api !== '' && $profile_discord_api !== '0');
$tpl->set("{discord_bound}", $profile_discord_bound ? '1' : '0');
$tpl->set("{discord_username}", htmlspecialchars((string) $profile->discord_username, ENT_QUOTES, 'UTF-8'));
$tpl->set("{birth}", expand_date($profile->birth, 2));
$tpl->set("{dell}", $profile->dell);
$tpl->set("{friends}", $tpl->result['friends']);
$tpl->set("{visitors}", $tpl->result['visitors']);
$tpl->set("{visitors_count}", count($visitorsRows ?? []));
$tpl->set("{isFriend}", $isFriend);
$tpl->set("{issetFriendRequestFromMe}", $issetFriendRequestFromMe);
$tpl->set("{issetFriendRequestFromHim}", $issetFriendRequestFromHim);
$tpl->set("{shilings}", $profile->shilings);
$tpl->set("{points}", $profile->playground);
$tpl->set("{proc}", $profile->proc);

// Активный фон профиля из площадки. Папки/таблицы playground являются
// дополнительным функционалом, поэтому профиль должен открываться даже если
// модуль или таблицы ещё не установлены.
$_profileBgHtml = '';
if(function_exists('pb_premium_profile_background')) {
	$_premiumBg = pb_premium_profile_background($profile->id);
	if(!empty($_premiumBg)) {
		$_premiumBgExt = strtolower(pathinfo($_premiumBg, PATHINFO_EXTENSION));
		if($_premiumBgExt === 'mp4' || $_premiumBgExt === 'webm') {
			$_profileBgHtml = '<div class="profile-bg-wrap"><video class="profile-bg-video" autoplay muted loop playsinline preload="none" poster="../templates/solution2/img/back_profile.png"><source src="' . htmlspecialchars($_premiumBg, ENT_QUOTES, 'UTF-8') . '" type="video/' . $_premiumBgExt . '"></video><div class="profile-bg-overlay"></div></div>';
		} else {
			$_profileBgHtml = '<div class="profile-bg-wrap" style="background-image:url(' . htmlspecialchars($_premiumBg, ENT_QUOTES, 'UTF-8') . ');"></div><div class="profile-bg-overlay"></div>';
		}
	}
}
if(empty($_profileBgHtml)) {
try {
	$_bgCatStmt = pdo()->query("SELECT `id` FROM `playground__category` WHERE `name` LIKE '%фон%' OR `code_name` LIKE '%bg%' OR `code_name` LIKE '%background%' LIMIT 1");
	$_bgCat = $_bgCatStmt ? $_bgCatStmt->fetch(PDO::FETCH_OBJ) : null;
	if(!empty($_bgCat->id)) {
		$_bgPurchaseStmt = pdo()->query("SELECT p.resource FROM `playground__purchases` pu JOIN `playground__product` p ON p.id = pu.pid WHERE pu.uid='" . (int)$profile->id . "' AND (pu.category='" . (int)$_bgCat->id . "' OR p.id_category='" . (int)$_bgCat->id . "') AND pu.enable='1' ORDER BY pu.id DESC LIMIT 1");
		$_bgPurchase = $_bgPurchaseStmt ? $_bgPurchaseStmt->fetch(PDO::FETCH_OBJ) : null;
		if(!empty($_bgPurchase->resource)) {
			// В БД resource хранится с папкой категории: background/file.png.
			// basename() ломал путь и профиль искал /files/playground/file.png вместо /files/playground/background/file.png.
			$_bgResource = ltrim(str_replace('..', '', (string) $_bgPurchase->resource), '/');
			$_bgFile = '/files/playground/' . $_bgResource;
			$_bgExt = strtolower(pathinfo($_bgResource, PATHINFO_EXTENSION));
			if($_bgExt === 'mp4' || $_bgExt === 'webm') {
				$_profileBgHtml = '<div class="profile-bg-wrap"><video class="profile-bg-video" autoplay muted loop playsinline preload="none" poster="../templates/solution2/img/back_profile.png"><source src="' . htmlspecialchars($_bgFile, ENT_QUOTES, 'UTF-8') . '" type="video/' . $_bgExt . '"></video><div class="profile-bg-overlay"></div></div>';
			} else {
				$_profileBgHtml = '<div class="profile-bg-wrap" style="background-image:url(' . htmlspecialchars($_bgFile, ENT_QUOTES, 'UTF-8') . ');"></div><div class="profile-bg-overlay"></div>';
			}
		}
	}
} catch(Throwable $e) {
	$_profileBgHtml = '';
}
}
if(empty($_profileBgHtml) && function_exists('pb_steam_profile_background_html') && !empty($profile->steam_api) && (string)$profile->steam_api !== '0') {
	$_profileBgHtml = pb_steam_profile_background_html((int)$profile->id, '');
}
if(empty($_profileBgHtml)) {
	$_profileBgHtml = '<div class="profile-bg-wrap" style="background-image:url(../templates/solution2/img/back_profile.png);"></div><div class="profile-bg-overlay"></div>';
}
$tpl->set("{profile_bg_html}", $_profileBgHtml);
$profileModuleWidgets = '';
$profileCoverWidget = '';
if(function_exists('renderModuleWidgets')) {
	try {
		$profileModuleWidgets = renderModuleWidgets('profile', ['profile' => $profile, 'profileUserId' => (int) $profile->id, 'isProfileOwner' => (is_auth() && (int) $_SESSION['id'] === (int) $profile->id)]);
		$profileCoverWidget = renderModuleWidgets('profile_cover', ['profile' => $profile, 'profileUserId' => (int) $profile->id, 'isProfileOwner' => (is_auth() && (int) $_SESSION['id'] === (int) $profile->id)]);
	} catch(Throwable $e) {
		$profileModuleWidgets = '';
		$profileCoverWidget = '';
	}
}
$tpl->set("{ip}", $profile->ip);
$isProfileOwner = is_auth() && (int) $_SESSION['id'] === (int) $profile->id;
$geoVisible = !empty($profile->geo_country_iso) && (!$profile->geo_hidden || $isProfileOwner);
$tpl->set("{geo_visible}", $geoVisible ? '1' : '0');
$tpl->set("{geo_country}", htmlspecialchars((string) $profile->geo_country_en, ENT_QUOTES, 'UTF-8'));
$tpl->set("{geo_city}", htmlspecialchars((string) $profile->geo_city_en, ENT_QUOTES, 'UTF-8'));
$tpl->set("{geo_flag}", function_exists('pb_geo_flag_url') ? pb_geo_flag_url($profile->geo_country_iso) : '');
$tpl->set("{profile_module_widgets}", $profileModuleWidgets);
$tpl->set("{profile_cover_widget}", $profileCoverWidget);
$very = new Verification($pdo);
$tpl->set("{verification}", $very->render_badge($profile->id, $conf->template));

$tpl->compile('content');
$tpl->clear();
?>
