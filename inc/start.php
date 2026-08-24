<?php
require_once __DIR__ . '/app_meta.php';
if(!isset($protection)) {
	$protection = 1;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/pb_social_auth.php';
require_once __DIR__ . '/pb_server_modes.php';
require_once __DIR__ . '/pb_slider.php';
require_once __DIR__ . '/pb_seo.php';
require_once __DIR__ . '/pb_system_log.php';
require_once __DIR__ . '/dictionary.php';
require_once __DIR__ . '/config_additional.php';

if (function_exists('pb_admin_2fa_ensure_schema')) {
	pb_admin_2fa_ensure_schema($pdo);
}
if (function_exists('pb_admin_gate_ensure_schema')) {
	pb_admin_gate_ensure_schema($pdo);
}
if (function_exists('pb_tg_reg_ensure_schema')) {
	pb_tg_reg_ensure_schema($pdo);
}
if (function_exists('pb_sa_ensure_schema')) {
	pb_sa_ensure_schema($pdo);
}
if (function_exists('pb_mon_key_ensure_schema')) {
	pb_mon_key_ensure_schema($pdo);
}
if (function_exists('pb_premium_ensure_schema')) {
	pb_premium_ensure_schema($pdo);
}
if (function_exists('pb_groups_manager_ensure_schema')) {
	pb_groups_manager_ensure_schema($pdo);
}
if (function_exists('pb_theme_ensure_schema')) {
	pb_theme_ensure_schema($pdo);
}
if (function_exists('pb_profile_status_ensure_schema')) {
	pb_profile_status_ensure_schema($pdo);
}
if (function_exists('pb_admin_master_ensure_schema')) {
	pb_admin_master_ensure_schema($pdo);
}
if (function_exists('pb_smodes_ensure_schema')) {
	pb_smodes_ensure_schema($pdo);
}
if (function_exists('pb_slider_ensure_schema')) {
	pb_slider_ensure_schema($pdo);
}
if (function_exists('pb_slog_ensure_schema')) {
	pb_slog_ensure_schema($pdo);
	pb_slog_migrate_legacy_files($pdo);
}

if(!isset($conf->standard_registration)) {
	$conf->standard_registration = isset($config_additional['off_standart_registration']) ? 0 : 1;
}

$SC    = new SessionsCookies($conf->salt, $host);
$token = $SC->set_token();

if(function_exists('pb_dev_mode_output_allowed') && pb_dev_mode_output_allowed()) {
	error_reporting(E_ALL);
	ini_set('display_errors', '0');
	ini_set('display_startup_errors', '0');
}

if(empty($_SERVER['HTTP_USER_AGENT'])) {
	$_SERVER['HTTP_USER_AGENT'] = 'undefined';
}

if(isset($_COOKIE['id']) && isset($_COOKIE['cache'])) {
	$_SESSION['cache'] = clean($_COOKIE['cache'], null);
	$_SESSION['id']    = clean($_COOKIE['id'], "int");
} else {
	$SC->clean_user_session();
}

/*
	Независимая админ-сессия (см. inc/functions.php).
	pb_admin_session_check() сама проверяет отпечаток/срок и восстанавливает
	$_SESSION['rights']. Если сессия невалидна — очищает её. Раньше здесь была
	старая логика на get_admin_cache(), которая стирала новую админ-сессию
	(admin/admin_cache) на каждом запросе — из-за этого вход «не пускал» в панель.
*/
if(function_exists('pb_admin_session_check') && pb_admin_session_check()) {
	if(isset($conf->ip_protect) && (int)$conf->ip_protect == 1) {
		$SC->admin_ip = get_ip();
	}
	$_SESSION['dev_mode'] = $dev_mode;
}

$users_groups = get_groups($pdo);

if(is_auth()) {
	$user = Users::getUserData($pdo, $_SESSION['id']);

	if($user->protect == 1) {
		$SC->ip = get_ip();
	}

	$_SESSION['rights']   = $user->rights;
	$_SESSION['stickers'] = $user->stickers;

	$pbUserCacheNow = $SC->get_cache($user->password);
	$pbUserCacheLegacy = method_exists($SC, 'get_cache_legacy') ? $SC->get_cache_legacy($user->password) : $pbUserCacheNow;

	if(
		empty($user->id)
		|| (($_SESSION['cache'] != $pbUserCacheNow) && ($_SESSION['cache'] != $pbUserCacheLegacy))
		|| ($user->dell == 1)
		|| (is_worthy("z"))
	) {
		require_once __DIR__ . '/../modules/exit/index.php';
	}

	if(is_worthy("x")) {
		$ban = true;
		require_once __DIR__ . '/../modules/exit/index.php';
	}

	if (function_exists('pb_tg_reg_gate_required_user')) {
		pb_tg_reg_gate_required_user($pdo, $user, $full_site_host);
	}
} else {
	$_SESSION['stickers'] = 0;
}

if (isset($conf->currency) && !empty($conf->currency)) {
	$pbCurrency = json_decode($conf->currency, true);
	if (is_array($pbCurrency)) {
		$pbCurrencyLabel = '';
		if (!empty($pbCurrency['lang'])) {
			$pbCurrencyLabel = trim($pbCurrency['lang']);
		} elseif (!empty($pbCurrency['code'])) {
			$pbCurrencyLabel = trim($pbCurrency['code']);
		}

		if (!empty($pbCurrencyLabel)) {
			$messages['RUB'] = $pbCurrencyLabel;
		}
	}
}

$conf->template = get_template($conf);
