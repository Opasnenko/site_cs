<?php
if(!is_admin()){
	show_error_page('not_adm');
}

$tpl->load_template('elements/title.tpl');
$tpl->set("{title}", $page->title);
$tpl->set("{name}", $conf->name);
$tpl->compile( 'title' );
$tpl->clear();

$tpl->load_template('head.tpl');
$tpl->set("{title}", $tpl->result['title']);
$tpl->set("{image}", $page->image);
$tpl->set("{other}", '<script src="{site_host}modules/editors/ckeditor/ckeditor.js"></script>');
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{template}", $conf->template);
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('top.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{site_name}", $conf->name);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('menu.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();

$nav = array(
	$PI->to_nav('admin', 0, 0),
	$PI->to_nav('admin_page_editor', 1, 0)
);
$nav = $tpl->get_nav($nav, 'elements/nav_li.tpl', 1);

$tpl->load_template('page_top.tpl');
$tpl->set("{nav}", $nav);
$tpl->compile( 'content' );
$tpl->clear();

$classes = '';
$STH = $pdo->query("SELECT `id`, `name` from `pages__classes`");
$STH->execute();
$row = $STH->fetchAll();
$count = count($row);

$classes = ''; 
for($i = 0; $i < $count; $i++){
	if($row[$i]['name'] == '') {
		$row[$i]['name'] = $messages['Initial'];
	}
	$classes .= '<option value="'.$row[$i]['id'].'">'.$row[$i]['name'].'</option>';
}

$STH = $pdo->query("SELECT `users_lim`, `bans_lim`, `bans_lim2`, `muts_lim`, `news_lim`, `stats_lim`, `complaints_lim` FROM `config__secondary` LIMIT 1");
$STH->setFetchMode(PDO::FETCH_OBJ);
$paginator = $STH->fetch();
if(!$paginator) {
	$paginator = (object)[
		'users_lim' => 12,
		'bans_lim' => 30,
		'bans_lim2' => 30,
		'muts_lim' => 30,
		'news_lim' => 10,
		'stats_lim' => 30,
		'complaints_lim' => 30
	];
}

$tpl->load_template('page_editor.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->set("{token}", $token);
$tpl->set("{cache}", $conf->cache);
$tpl->set("{default_image}", function_exists('pb_page_default_image') ? pb_page_default_image($pdo) : 'files/miniatures/pbgame_ui.jpg');
$tpl->set("{classes}", $classes);
$tpl->set("{users_lim}", (int)$paginator->users_lim);
$tpl->set("{bans_lim}", (int)$paginator->bans_lim);
$tpl->set("{bans_lim2}", (int)$paginator->bans_lim2);
$tpl->set("{muts_lim}", (int)$paginator->muts_lim);
$tpl->set("{news_lim}", (int)$paginator->news_lim);
$tpl->set("{stats_lim}", (int)$paginator->stats_lim);
$tpl->set("{complaints_lim}", (int)$paginator->complaints_lim);
$tpl->compile( 'content' );
$tpl->clear();

$tpl->load_template('bottom.tpl');
$tpl->set("{site_host}", $site_host);
$tpl->compile( 'content' );
$tpl->clear();
?>