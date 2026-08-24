<?php
if(!is_auth()){
	show_error_page('not_auth');
}

$AA = new AuthApi;

if(function_exists('pb_sa_ensure_schema')) {
	pb_sa_ensure_schema($pdo);
}
if(function_exists('pb_ensure_geo_columns')) {
	pb_ensure_geo_columns($pdo);
}
if(!isset($user->geo_hidden)) {
	$user->geo_hidden = 0;
}
if(!isset($user->google_api)) {
	$user->google_api = '0';
}
if(!isset($user->telegram_api)) {
	$user->telegram_api = '0';
}
if(!isset($user->telegram_username)) {
	$user->telegram_username = '';
}
if(!isset($user->discord_api)) {
	$user->discord_api = '0';
}
if(!isset($user->discord_username)) {
	$user->discord_username = '';
}


if(!function_exists('pb_sa_social_account_notice')) {
	function pb_sa_social_account_notice($pdo, $userId, $message) {
		$userId = (int) $userId;
		if($userId <= 0 || trim((string)$message) === '') {
			return;
		}

		if(function_exists('send_noty')) {
			try { send_noty($pdo, $message, $userId, 2); return; } catch(Throwable $e) {}
		}

		try {
			$NTH = $pdo->prepare("INSERT INTO `notifications` (`message`, `date`, `user_id`, `type`) VALUES (:message, :date, :user_id, :type)");
			$NTH->execute(array(':message' => $message, ':date' => date('Y-m-d H:i:s'), ':user_id' => $userId, ':type' => 2));
		} catch(Throwable $e) {}
	}
}

if(!function_exists('pb_sa_send_google_attach_notice')) {
	function pb_sa_send_google_attach_notice($pdo, $user) {
		$userId = !empty($user->id) ? (int) $user->id : (!empty($_SESSION['id']) ? (int) $_SESSION['id'] : 0);
		pb_sa_social_account_notice($pdo, $userId, 'Google-аккаунт успешно привязан к вашему профилю.');

		if(!function_exists('sendmail') || empty($user->email)) {
			return;
		}

		$email = trim((string) $user->email);
		if($email === '' || strpos($email, '@') === false || strpos($email, '@google.local') !== false || strpos($email, '@telegram.local') !== false || strpos($email, 'vk_id_') === 0) {
			return;
		}

		$siteName = 'сайте';
		if(function_exists('configs')) {
			$cfg = configs();
			if(!empty($cfg->name)) {
				$siteName = $cfg->name;
			}
		}

		$login = !empty($user->login) ? $user->login : 'пользователь';
		$subject = 'Google привязан к аккаунту на ' . $siteName;
		$message = "Здравствуйте, " . $login . ".\n"
			. "К вашему аккаунту на " . $siteName . " был привязан Google-аккаунт.\n"
			. "Если это были не вы, срочно смените пароль и обратитесь к администрации.\n"
			. "С уважением,\nАдминистрация " . $siteName;

		try {
			sendmail($email, $subject, $message, $pdo);
		} catch(Throwable $e) {
			if(function_exists('write_log')) {
				write_log('[PB Social Auth] Google attach mail: ' . $e->getMessage());
			}
		}
	}
}

$conf_mess = '';
if($auth_api->vk_api == 1 && isset($_GET['code']) && empty($_GET['fb_attach']) && empty($_GET['google_attach'])) {

	$result = false;

	$params = [
		'client_id'     => $auth_api->vk_id,
		'client_secret' => $auth_api->vk_key,
		'code'          => $_GET['code'],
		'redirect_uri'  => $full_site_host . 'settings',
		'v'             => configs()->vk_api_version
	];

	$vk_token = json_decode(
		file_get_contents_curl(
			'https://oauth.vk.com/access_token?'
				. urldecode(http_build_query($params))
		),
		true
	);

	if(isset($vk_token['access_token'])) {
		$params = [
			'user_id' => $vk_token['user_id'],
			'access_token' => $vk_token['access_token'],
			'v' => configs()->vk_api_version
		];

		$userInfo = json_decode(
			file_get_contents_curl(
				'https://api.vk.com/method/users.get?'
					. urldecode(http_build_query($params))
			),
			true
		);

		if(isset($userInfo['response'][0]['id'])) {
			$userInfo = $userInfo['response'][0];
			$result   = true;
		}
	}

	if ($result == true && $_GET['state'] == md5($SC->set_token())) {
		$vk_id = $userInfo['id'];
		$STH = $pdo->prepare("SELECT id FROM users WHERE vk_api=:vk_api LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':vk_api' => $vk_id]);
		$row = $STH->fetch();
		if(empty($row->id) && $AA->isAttachCacheCorrect($user->password)) {
			$STH = $pdo->prepare("UPDATE `users` SET `vk_api`=:vk_api, `vk`=:vk WHERE `id`=:id LIMIT 1");
			if(
				$STH->execute(
					[
						':vk_api' => $vk_id,
						':vk'     => 'id' . $vk_id,
						':id'     => $_SESSION['id']
					]
				) == '1'
			) {
				header('Location: ../settings#vk_area');
				exit();
			}
		}
	}

	$conf_mess = '<span class="m-icon icon-remove"></span> ' . $messages['Error'];
}

$conf_mess2 = '';
if($auth_api->steam_api == 1 && isset($_GET['steam_attach'])) {

	$result = false;
	$steam_api = 0;
	$steam_id = '';

	try {
		$openid = new LightOpenID($host);

		/*
		 * Steam возвращает openid.return_to в GET, а PHP превращает точки в подчёркивания.
		 * LightOpenID иногда проверяет returnUrl строго, поэтому выставляем фактический URL возврата.
		 */
		if(!empty($_GET['openid_return_to'])) {
			$openid->returnUrl = $_GET['openid_return_to'];
		}

		if($openid->mode == 'cancel') {
			$conf_mess2 = '<span class="m-icon icon-remove"></span> ' . $messages['Error'];
		} else {
			if($openid->validate()) {
				preg_match(
					"/^https:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/",
					$openid->identity,
					$matches
				);

				if(!empty($matches[1])) {
					$steam_api = $matches[1];
					$SIDO = new SteamIDOperations;
					$steam_id = $SIDO->GetSteamID32($steam_api);
					$result = true;
				} else {
					$conf_mess2 = '<span class="m-icon icon-remove"></span> ' . $messages['Error'];
				}
			} else {
				$conf_mess2 = '<span class="m-icon icon-remove"></span> ' . $messages['Error'];
			}
		}
	} catch(Exception $e) {
		$conf_mess2 = '<span class="m-icon icon-remove"></span> ' . $messages['Error'];
	} catch(Throwable $e) {
		$conf_mess2 = '<span class="m-icon icon-remove"></span> ' . $messages['Error'];
	}

	if($result == true && isset($_GET['state']) && $_GET['state'] == md5($SC->set_token())) {
		$STH = $pdo->prepare("SELECT id FROM users WHERE steam_api=:steam_api AND id<>:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':steam_api' => $steam_api, ':id' => $_SESSION['id']]);
		$row = $STH->fetch();

		if(empty($row->id) && $AA->isAttachCacheCorrect($user->password)) {
			$STH = $pdo->prepare("UPDATE users SET steam_api=:steam_api, steam_id=:steam_id WHERE id=:id LIMIT 1");
			if(
				$STH->execute(
					[
						':steam_api' => $steam_api,
						':steam_id' => $steam_id,
						':id' => $_SESSION['id']
					]
				) == '1'
			) {
				header('Location: ../settings#steam_area');
				exit();
			}
		}
	}

	$conf_mess2 = '<span class="m-icon icon-remove"></span> ' . $messages['Error'];
}
$conf_mess3 = '';
$conf_mess_social = '';
if(isset($_GET['google_attach']) && isset($_GET['code'])) {
	$stateOk = isset($_GET['state']) && $_GET['state'] == md5($SC->set_token());
	$settingsReturnUrl = $full_site_host . 'settings?google_attach=1';
	if(!$stateOk || !$AA->isAttachCacheCorrect($user->password)) {
		$conf_mess_social = '<span class="m-icon icon-remove"></span> Ошибка проверки привязки Google. Попробуйте ещё раз.';
	} elseif(!function_exists('pb_sa_fetch_google_user_from_code')) {
		$conf_mess_social = '<span class="m-icon icon-remove"></span> Google-интеграция не подключена.';
	} else {
		$googleResult = pb_sa_fetch_google_user_from_code($_GET['code'], $settingsReturnUrl, $pdo);
		if(empty($googleResult['ok'])) {
			$conf_mess_social = '<span class="m-icon icon-remove"></span> ' . htmlspecialchars($googleResult['error'] ?? 'Ошибка Google-авторизации', ENT_QUOTES, 'UTF-8');
		} else {
			$googleId = (string)$googleResult['id'];
			$STH = $pdo->prepare("SELECT id FROM users WHERE google_api=:google_api AND id<>:id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':google_api' => $googleId, ':id' => $_SESSION['id']]);
			$row = $STH->fetch();
			if(!empty($row->id)) {
				$conf_mess_social = '<span class="m-icon icon-remove"></span> Этот Google-аккаунт уже привязан к другому пользователю.';
			} else {
				$STH = $pdo->prepare("UPDATE users SET google_api=:google_api WHERE id=:id LIMIT 1");
				if($STH->execute([':google_api' => $googleId, ':id' => $_SESSION['id']]) == '1') {
					pb_sa_send_google_attach_notice($pdo, $user);
					header('Location: ../settings#social_accounts_area');
					exit();
				}
				$conf_mess_social = '<span class="m-icon icon-remove"></span> Не удалось привязать Google.';
			}
		}
	}
}

if(isset($_GET['telegram_attach']) && isset($_GET['hash'])) {
	$cfg = function_exists('pb_sa_config') ? pb_sa_config($pdo) : null;
	$telegramEnabled = ($cfg && (int)$cfg->telegram_api === 1 && !empty($cfg->telegram_bot_token));
	$telegramData = [];
	foreach(['id','first_name','last_name','username','photo_url','auth_date','hash'] as $key) {
		if(isset($_GET[$key])) { $telegramData[$key] = (string)$_GET[$key]; }
	}
	if(!$telegramEnabled || !function_exists('pb_sa_verify_telegram')) {
		$conf_mess_social = '<span class="m-icon icon-remove"></span> Telegram-интеграция не настроена.';
	} elseif(!pb_sa_verify_telegram($telegramData, $cfg->telegram_bot_token)) {
		$conf_mess_social = '<span class="m-icon icon-remove"></span> Telegram не прошёл проверку подписи или время авторизации истекло.';
	} else {
		$telegramId = (string)$telegramData['id'];
		$username = trim((string)($telegramData['username'] ?? ''));
		$STH = $pdo->prepare("SELECT id FROM users WHERE telegram_api=:telegram_api AND id<>:id LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':telegram_api' => $telegramId, ':id' => $_SESSION['id']]);
		$row = $STH->fetch();
		if(!empty($row->id)) {
			$conf_mess_social = '<span class="m-icon icon-remove"></span> Этот Telegram уже привязан к другому пользователю.';
		} else {
			$STH = $pdo->prepare("UPDATE users SET telegram_api=:telegram_api, telegram_username=:telegram_username WHERE id=:id LIMIT 1");
			if($STH->execute([':telegram_api' => $telegramId, ':telegram_username' => $username, ':id' => $_SESSION['id']]) == '1') {
				pb_sa_social_account_notice($pdo, (int)$_SESSION['id'], 'Telegram успешно привязан к вашему профилю.');
				header('Location: ../settings#social_accounts_area');
				exit();
			}
			$conf_mess_social = '<span class="m-icon icon-remove"></span> Не удалось привязать Telegram.';
		}
	}
}

if(isset($_GET['discord_attach']) && isset($_GET['code'])) {
	$stateOk = isset($_GET['state']) && $_GET['state'] == md5($SC->set_token());
	$settingsReturnUrl = $full_site_host . 'settings?discord_attach=1';
	if(!$stateOk || !$AA->isAttachCacheCorrect($user->password)) {
		$conf_mess_social = '<span class="m-icon icon-remove"></span> Ошибка проверки привязки Discord. Попробуйте ещё раз.';
	} elseif(!function_exists('pb_sa_fetch_discord_user_from_code')) {
		$conf_mess_social = '<span class="m-icon icon-remove"></span> Discord-интеграция не подключена.';
	} else {
		$discordResult = pb_sa_fetch_discord_user_from_code($_GET['code'], $settingsReturnUrl, $pdo);
		if(empty($discordResult['ok'])) {
			$conf_mess_social = '<span class="m-icon icon-remove"></span> ' . htmlspecialchars($discordResult['error'] ?? 'Ошибка Discord-авторизации', ENT_QUOTES, 'UTF-8');
		} else {
			$discordId = (string)$discordResult['id'];
			$discordUsername = (string)$discordResult['username'];
			$STH = $pdo->prepare("SELECT id FROM users WHERE discord_api=:discord_api AND id<>:id LIMIT 1");
			$STH->setFetchMode(PDO::FETCH_OBJ);
			$STH->execute([':discord_api' => $discordId, ':id' => $_SESSION['id']]);
			$row = $STH->fetch();
			if(!empty($row->id)) {
				$conf_mess_social = '<span class="m-icon icon-remove"></span> Этот Discord-аккаунт уже привязан к другому пользователю.';
			} else {
				$STH = $pdo->prepare("UPDATE users SET discord_api=:discord_api, discord_username=:discord_username WHERE id=:id LIMIT 1");
				if($STH->execute([':discord_api' => $discordId, ':discord_username' => $discordUsername, ':id' => $_SESSION['id']]) == '1') {
					pb_sa_social_account_notice($pdo, (int)$_SESSION['id'], 'Discord-аккаунт успешно привязан к вашему профилю.');
					header('Location: ../settings#social_accounts_area');
					exit();
				}
				$conf_mess_social = '<span class="m-icon icon-remove"></span> Не удалось привязать Discord.';
			}
		}
	}
}

if($auth_api->fb_api == 1 && isset($_GET['code']) && isset($_GET['fb_attach'])) {

	$result = false;
	$params = [
		'client_id'     => $auth_api->fb_id,
		'redirect_uri'  => $full_site_host . "settings?fb_attach=1",
		'client_secret' => $auth_api->fb_key,
		'code'          => $_GET['code']
	];

	$fb_token = null;
	$fb_token = json_decode(
		file_get_contents(
			str_replace(
				"&amp;",
				"&",
				'https://graph.facebook.com/oauth/access_token?'
					. http_build_query($params)
			)
		),
		true
	);

	if(isset($fb_token['access_token'])) {
		$params = ['access_token' => $fb_token['access_token']];
		$userInfo = json_decode(
			file_get_contents(
				str_replace(
					"&amp;",
					"&",
					'https://graph.facebook.com/me?'
						. urldecode(http_build_query($params))
				)
			),
			true
		);

		if(isset($userInfo['id'])) {
			$result = true;
		}
	}

	if($result == true && $_GET['state'] == md5($SC->set_token())) {
		$fb_api = $userInfo['id'];
		$STH = $pdo->prepare("SELECT id FROM users WHERE fb_api=:fb_api LIMIT 1");
		$STH->setFetchMode(PDO::FETCH_OBJ);
		$STH->execute([':fb_api' => $fb_api]);
		$row = $STH->fetch();
		if(empty($row->id) && $AA->isAttachCacheCorrect($user->password)) {
			$STH = $pdo->prepare("UPDATE users SET fb_api=:fb_api WHERE id=:id LIMIT 1");
			if(
				$STH->execute(
					[':fb_api' => $fb_api, ':id' => $_SESSION['id']]
				) == '1'
			) {
				header('Location: ../settings#fb_area');
				exit();
			}
		}
	}

	$conf_mess3 = '<span class="m-icon icon-remove"></span> '.$messages['Error'];
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $page->title);
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{site_name}", $conf->name);
$tpl->set("{image}", $page->image);
$tpl->set("{robots}", $page->robots);
$tpl->set("{type}", $page->kind);
$tpl->set("{description}", $page->description);
$tpl->set("{keywords}", $page->keywords);
$tpl->set("{url}", $page->url);
$tpl->set("{other}", '<script src="{site_host}modules/editors/tinymce/tinymce.min.js"></script>');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$menu = $tpl->get_menu($pdo);

$nav = array(
	$PI->to_nav('main', 0, 0),
	$PI->to_nav('users', 0, 0),
	$PI->to_nav('profile', 0, $_SESSION['id'], $user->login),
	$PI->to_nav('settings', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl');

include_once "inc/authorized.php";

$STH = $pdo->query("SELECT `referral_program`, `referral_percent` FROM `config__prices` LIMIT 1"); $STH->setFetchMode(PDO::FETCH_OBJ);
$ref = $STH->fetch();

if(empty($user->signature)) {
	$user->signature = '';
}
if(empty($user->fb)) {
	$user->fb = 0;
}
if(empty($user->fb_api)) {
	$user->fb_api = 0;
}

if(substr($user->password, 0, 5) == "none_") {
	$user->password = "none";
}

$folders = scandir("templates/");

if(!isset($_COOKIE['template'])) {
	$templates_prsonal = "<option value='0' selected>Не задан</option>";
} else {
	$templates_prsonal = "<option value='0'>Не задан</option>";
}

for ($i=2; $i < count($folders); $i++) {
	if($folders[$i] != 'admin' and file_exists("templates/".$folders[$i]."/tpl/head.tpl")) {
		if(isset($_COOKIE['template']) && $_COOKIE['template'] == $folders[$i]) {
			$templates_prsonal .= "<option value='".$folders[$i]."' selected>".$folders[$i]."</option>";
		} else {
			$templates_prsonal .= "<option value='".$folders[$i]."'>".$folders[$i]."</option>";
		}
	}
}

$groupsCardsHtml = '';
if(function_exists('pb_groups_manager_selectable_ids')) {
	global $users_groups;
	$currentGroupId = (int) $user->rights;
	$selectableIds = pb_groups_manager_selectable_ids($pdo, $user->id);
	if(!in_array($currentGroupId, $selectableIds, true)) {
		$selectableIds[] = $currentGroupId;
	}

	foreach($selectableIds as $gid) {
		if(empty($users_groups[$gid]) || (int) $gid === 0) {
			continue;
		}
		$g = $users_groups[$gid];
		$isCurrent = ((int) $gid === $currentGroupId);
		$colorParts = explode(';', (string) $g['color'], 2);
		$groupColor = htmlspecialchars(trim($colorParts[0]), ENT_QUOTES, 'UTF-8');

		if($isCurrent) {
			$tpl->load_template('elements/group_card_badge.tpl');
			$tpl->compile('{group_card_action}');
			$tpl->clear();
		} else {
			$tpl->load_template('elements/group_card_select_btn.tpl');
			$tpl->set("{id}", (int) $gid);
			$tpl->compile('{group_card_action}');
			$tpl->clear();
		}
		$actionHtml = $tpl->result['{group_card_action}'];
		$tpl->result['{group_card_action}'] = '';

		$tpl->load_template('elements/group_card.tpl');
		$tpl->set("{active_class}", $isCurrent ? ' pbg-group-card--active' : '');
		$tpl->set("{id}", (int) $gid);
		$tpl->set("{color}", $groupColor);
		$tpl->set("{name}", htmlspecialchars($g['name'], ENT_QUOTES, 'UTF-8'));
		$tpl->set("{status_text}", $isCurrent ? 'Текущая группа' : 'Доступна для выбора');
		$tpl->set("{action_html}", $actionHtml);
		$tpl->compile('{group_card}');
		$groupsCardsHtml .= $tpl->result['{group_card}'];
		$tpl->result['{group_card}'] = '';
		$tpl->clear();
	}
	if($groupsCardsHtml === '') {
		$tpl->load_template('elements/group_card_empty.tpl');
		$tpl->compile('{group_card_empty}');
		$groupsCardsHtml = $tpl->result['{group_card_empty}'];
		$tpl->result['{group_card_empty}'] = '';
		$tpl->clear();
	}
}

$editor_settings = get_editor_settings($pdo);
$tpl->load_template('/home/settings.tpl');
$tpl->set("{file_manager}", $editor_settings['file_manager']);
$tpl->set("{file_manager_theme}", $editor_settings['file_manager_theme']);
$tpl->set("{token}", $token);
$tpl->set("{site_host}", $site_host);
$tpl->set("{template}", $conf->template);
$tpl->set("{templates_prsonal}", $templates_prsonal);
$tpl->set("{referral_link}", $full_site_host.'?ref='.$_SESSION['id']);
$tpl->set("{profileLink}", $full_site_host . PagesInfo::PROFILE_PAGE_URL . '/');
$tpl->set("{referral_program}", $ref->referral_program);
$tpl->set("{referral_percent}", $ref->referral_percent);
$tpl->set("{login}", $user->login);
$tpl->set("{route}", $user->route);
$tpl->set("{avatar}", ltrim((string) $user->avatar, "/"));
$avatarSettingsForBlock = function_exists('pb_get_user_avatar_settings') ? pb_get_user_avatar_settings($pdo) : (object)['user_avatar_locked' => 0];
$tpl->set("{avatar_locked}", ((int) $avatarSettingsForBlock->user_avatar_locked === 1) ? '1' : '0');
$tpl->set("{avatar_frame}", ltrim(get_user_frame($user->id), "/"));
$tpl->set("{regdate}", expand_date($user->regdate,2));
$tpl->set("{name}", $user->name);
$tpl->set("{nick}", $user->nick);
$tpl->set("{skype}", $user->skype);
$tpl->set("{discord}", $user->discord);
$tpl->set("{vk}", $user->vk);
$tpl->set("{vk_api}", $user->vk_api);
$tpl->set("{fb}", $user->fb);
$tpl->set("{fb_api}", $user->fb_api);
$tpl->set("{signature}", $user->signature);
$tpl->set("{steam_id}", $user->steam_id);
$tpl->set("{steam_api}", $user->steam_api);
$tpl->set("{telegram}", $user->telegram);
$birth = explode("-", $user->birth);
$birth_day = "";
for ($x = 31; $x > 0; $x--){
	$birth_day = $birth_day.'<option value="'.$x.'" ';
	if($birth[2]==$x) $birth_day = $birth_day.' selected';
	$birth_day = $birth_day.'>'.$x.'</option>';
}

$birth_month = "";
for ($x = 12; $x > 0; $x--){
	$birth_month = $birth_month.'<option value="'.$x.'" ';
	if($birth[1]==$x) $birth_month = $birth_month.' selected';
	$birth_month = $birth_month.'>'.get_month($x).'</option>';
}

$birth_year = "";
for ($x = (date('Y')); $x > 1959; $x--){
	$birth_year = $birth_year.'<option value="'.$x.'" ';
	if($birth[0]==$x) $birth_year = $birth_year.' selected';
	$birth_year = $birth_year.'>'.$x.'</option>';
}

$tpl->set("{birth_day}", $birth_day);
$tpl->set("{birth_month}", $birth_month);
$tpl->set("{birth_year}", $birth_year);

$act = get_active($user->im, 2);
$tpl->set("{im_radio_1}", $act[0]);
$tpl->set("{im_radio_2}", $act[1]);

$act = get_active($user->protect, 2);
$tpl->set("{protect_radio_1}", $act[0]);
$tpl->set("{protect_radio_2}", $act[1]);

$act = get_active($user->email_notice, 2);
$tpl->set("{notice_radio_1}", $act[0]);
$tpl->set("{notice_radio_2}", $act[1]);

$act = get_active(!empty($user->geo_hidden) ? 1 : 0, 1);
$tpl->set("{geo_hidden_radio_1}", $act[0]);
$tpl->set("{geo_hidden_radio_2}", $act[1]);

$tpl->set("{conf_mess}", $conf_mess);
$tpl->set("{conf_mess2}", $conf_mess2);
$tpl->set("{conf_mess_social}", $conf_mess_social);

$settingsSocialCfg = function_exists('pb_sa_config') ? pb_sa_config($pdo) : null;
$settingsGoogleEnabled = ($settingsSocialCfg && (int)$settingsSocialCfg->google_api === 1 && !empty($settingsSocialCfg->google_client_id) && !empty($settingsSocialCfg->google_client_secret));
$settingsGoogleBound = !empty($user->google_api) && (string)$user->google_api !== '0';
$settingsGoogleAttachUrl = '#';
if($settingsGoogleEnabled && !$settingsGoogleBound && function_exists('pb_sa_google_oauth_url_for_redirect')) {
	$AA->setAttachCache($pdo);
	$settingsGoogleAttachUrl = pb_sa_google_oauth_url_for_redirect($full_site_host . 'settings?google_attach=1', md5($SC->set_token()), $pdo);
}
$settingsTelegramEnabled = ($settingsSocialCfg && (int)$settingsSocialCfg->telegram_api === 1 && !empty($settingsSocialCfg->telegram_bot_username) && !empty($settingsSocialCfg->telegram_bot_token));
$settingsTelegramBound = !empty($user->telegram_api) && (string)$user->telegram_api !== '0';
$settingsTelegramBot = $settingsSocialCfg ? ltrim((string)$settingsSocialCfg->telegram_bot_username, '@') : '';
$settingsTelegramAuthUrl = $full_site_host . 'settings?telegram_attach=1';
$settingsTelegramBoundUser = !empty($user->telegram_username) ? '@' . ltrim((string)$user->telegram_username, '@') : 'привязан';
$settingsDiscordEnabled = ($settingsSocialCfg && (int)$settingsSocialCfg->discord_api === 1 && !empty($settingsSocialCfg->discord_client_id) && !empty($settingsSocialCfg->discord_client_secret));
$settingsDiscordBound = !empty($user->discord_api) && (string)$user->discord_api !== '0';
$settingsDiscordAttachUrl = '#';
if($settingsDiscordEnabled && !$settingsDiscordBound && function_exists('pb_sa_discord_oauth_url_for_redirect')) {
	$AA->setAttachCache($pdo);
	$settingsDiscordAttachUrl = pb_sa_discord_oauth_url_for_redirect($full_site_host . 'settings?discord_attach=1', md5($SC->set_token()), $pdo);
}
$settingsDiscordBoundUser = !empty($user->discord_username) ? $user->discord_username : 'привязан';
$tpl->set("{social_google_enabled}", $settingsGoogleEnabled ? '1' : '0');
$tpl->set("{social_google_bound}", $settingsGoogleBound ? '1' : '0');
$tpl->set("{social_google_attach_url}", htmlspecialchars($settingsGoogleAttachUrl, ENT_QUOTES, 'UTF-8'));
$tpl->set("{social_google_value}", $settingsGoogleBound ? 'привязан' : 'не привязан');
$tpl->set("{social_telegram_enabled}", $settingsTelegramEnabled ? '1' : '0');
$tpl->set("{social_telegram_bound}", $settingsTelegramBound ? '1' : '0');
$tpl->set("{social_telegram_bot_username}", htmlspecialchars($settingsTelegramBot, ENT_QUOTES, 'UTF-8'));
$tpl->set("{social_telegram_auth_url}", htmlspecialchars($settingsTelegramAuthUrl, ENT_QUOTES, 'UTF-8'));
$tpl->set("{social_telegram_value}", htmlspecialchars($settingsTelegramBoundUser, ENT_QUOTES, 'UTF-8'));
$tpl->set("{social_discord_enabled}", $settingsDiscordEnabled ? '1' : '0');
$tpl->set("{social_discord_bound}", $settingsDiscordBound ? '1' : '0');
$tpl->set("{social_discord_attach_url}", htmlspecialchars($settingsDiscordAttachUrl, ENT_QUOTES, 'UTF-8'));
$tpl->set("{social_discord_value}", htmlspecialchars($settingsDiscordBoundUser, ENT_QUOTES, 'UTF-8'));
$settingsModuleWidgets = renderModuleWidgets('settings', ['user' => $user, 'settingsUserId' => (int) $user->id]);
$tgAdmin2FaConfig = ['enabled' => 2, 'bot_username' => '', 'bot_token' => '', 'code_ttl' => 300, 'session_ttl' => 1800];
$tgAdmin2FaBinding = ['chat_id' => '', 'username' => ''];
$tgAdmin2FaFeatureReady = false;
$tgAdmin2FaSessionValid = false;
if (function_exists('pb_admin_2fa_get_config')) {
	$tgAdmin2FaConfig = pb_admin_2fa_get_config($pdo);
	$tgAdmin2FaBinding = function_exists('pb_admin_2fa_user_binding') ? pb_admin_2fa_user_binding($pdo, $user->id) : $tgAdmin2FaBinding;
	$tgAdmin2FaFeatureReady = function_exists('pb_admin_2fa_is_feature_ready') ? pb_admin_2fa_is_feature_ready($pdo) : false;
	$tgAdmin2FaSessionValid = function_exists('pb_admin_2fa_session_valid') ? pb_admin_2fa_session_valid($pdo) : false;
}
$showTgAdmin2FaBlock = ((function_exists('is_creator_group') && is_creator_group((int) $user->rights)) || (function_exists('is_admin') && is_admin()));
$tgAdmin2FaScopeText = (function_exists('is_creator_group') && is_creator_group((int) $user->rights)) ? 'Защита входа в Админцентр для группы «Создатель».' : 'Защита входа в Админцентр для привилегированного аккаунта.';
$tgRegConfig = function_exists('pb_tg_reg_get_config') ? pb_tg_reg_get_config($pdo) : ['mode' => 0, 'apply_to' => 0, 'bot_username' => '', 'bot_token' => '', 'hint_text' => ''];
$tgRegBinding = function_exists('pb_tg_reg_user_binding') ? pb_tg_reg_user_binding($pdo, $user->id) : ['chat_id' => '', 'username' => '', 'required' => 0, 'source' => ''];
$tgRegFeatureReady = function_exists('pb_tg_reg_is_feature_ready') ? pb_tg_reg_is_feature_ready($pdo) : false;
$tgRegShowBlock = ((int) $tgRegConfig['mode'] > 0) || !empty($tgRegBinding['chat_id']) || isset($_GET['telegram_bind_prompt']) || isset($_GET['telegram_bind_required']);
$tgRegPrompt = isset($_GET['telegram_bind_prompt']) ? '1' : '0';
$tgRegRequiredGate = isset($_GET['telegram_bind_required']) || (!empty($tgRegBinding['required']) && empty($tgRegBinding['chat_id']));
$tgRegModeLabel = 'Telegram не участвует в регистрации.';
if ((int) $tgRegConfig['mode'] === 1) {
	$tgRegModeLabel = 'После регистрации Telegram можно привязать по желанию.';
} elseif ((int) $tgRegConfig['mode'] === 2) {
	$tgRegModeLabel = 'Для новых пользователей Telegram обязателен до завершения привязки.';
}
$tgRegApplyLabel = 'Email и Steam';
if ((int) $tgRegConfig['apply_to'] === 1) {
	$tgRegApplyLabel = 'только Email-регистрация';
} elseif ((int) $tgRegConfig['apply_to'] === 2) {
	$tgRegApplyLabel = 'только Steam-регистрация';
}
$tgRegScopeText = 'Политика: ' . $tgRegModeLabel . ' Применение: ' . $tgRegApplyLabel . '.';
$publicTelegramValue = $user->telegram;
if ($publicTelegramValue == '' && !empty($tgRegBinding['username']) && function_exists('pb_tg_reg_public_username')) {
	$publicTelegramValue = pb_tg_reg_public_username($tgRegBinding['username']);
	$tpl->set("{telegram}", $publicTelegramValue);
}
$tpl->set("{conf_mess3}", $conf_mess3);
$tpl->set("{settings_module_widgets}", $settingsModuleWidgets);
$tpl->set("{tg_admin2fa_show_block}", $showTgAdmin2FaBlock ? '1' : '0');
$tpl->set("{tg_admin2fa_scope_text}", htmlspecialchars($tgAdmin2FaScopeText));
$tpl->set("{tg_admin2fa_feature_enabled}", (int) $tgAdmin2FaConfig['enabled']);
$tpl->set("{tg_admin2fa_feature_ready}", $tgAdmin2FaFeatureReady ? '1' : '0');
$tpl->set("{tg_admin2fa_bot_username}", htmlspecialchars($tgAdmin2FaConfig['bot_username']));
$tpl->set("{tg_admin2fa_bound}", !empty($tgAdmin2FaBinding['chat_id']) ? '1' : '0');
$tpl->set("{tg_admin2fa_bound_user}", !empty($tgAdmin2FaBinding['username']) ? htmlspecialchars($tgAdmin2FaBinding['username']) : 'не привязан');
$tpl->set("{tg_admin2fa_session_valid}", $tgAdmin2FaSessionValid ? '1' : '0');
$tpl->set("{tg_reg_show_block}", $tgRegShowBlock ? '1' : '0');
$tpl->set("{tg_reg_scope_text}", htmlspecialchars($tgRegScopeText));
$tpl->set("{tg_reg_feature_enabled}", (int) $tgRegConfig['mode'] > 0 ? '1' : '0');
$tpl->set("{tg_reg_feature_ready}", $tgRegFeatureReady ? '1' : '0');
$tpl->set("{tg_reg_bot_username}", htmlspecialchars($tgRegConfig['bot_username']));
$tpl->set("{tg_reg_bound}", !empty($tgRegBinding['chat_id']) ? '1' : '0');
$tpl->set("{tg_reg_bound_user}", !empty($tgRegBinding['username']) ? htmlspecialchars($tgRegBinding['username']) : 'не привязан');
$tpl->set("{tg_reg_hint_text}", htmlspecialchars($tgRegConfig['hint_text']));
$tpl->set("{tg_reg_mode}", (int) $tgRegConfig['mode']);
$tpl->set("{tg_reg_prompt}", $tgRegPrompt);
$tpl->set("{tg_reg_required_gate}", $tgRegRequiredGate ? '1' : '0');
$very = new Verification($pdo);
$tpl->set("{verification_block}", $very->render_settings_box($user->id, $conf->template));

$tpl->set("{groups_cards}", $groupsCardsHtml);

$tpl->compile( 'content' );
$tpl->clear();
?>
