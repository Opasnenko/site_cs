<?php
class Template {
	const ADMIN_TEMPLATE_DIR = 'templates/admin/tpl/';
	const CLIENT_TEMPLATE_DIR = 'templates/{template}/tpl/';
	const DOWN_TO_ROOT = '../../../';

	public  $dir = '.'; 
	public  $sec_dir = ''; 
	public  $template = null; 
	public  $copy_template = null; 
	public  $data = array();
	public  $result = array('info' => '', 'content' => '');
	public  $modules_tpls = array();
	public  $files = '';
	public  $caching = 0;
	private $includeLimit = 0;
	protected $using_tpl = '';
	protected $using_tpl_path = '';
	protected $templater_preg = array(
		'/{\*.*?\*}/is' => '', // comment: {* some comment text *}

		'/{ ?if ?\( ?([^;<].[^;<]{1,250}?) ?\) ?}/' => '<?php if(${1}): ?>',
		'/{ ?else ?}/' => '<?php else: ?>',
		'/{ ?elseif ?\( ?([^;<].[^;<]{1,250}?) ?\) ?}/' => '<?php elseif(${1}): ?>',

		'/{ ?for ?\( ?([^;<].[^;<]{1,50}?) ?; ?([^;].[^;]{1,50}?) ?; ?([^;<].[^;<]{1,50}?) ?\) ?}/' => '<?php for(${1}; ${2}; ${3}): ?>', // cycle: {for($i=0;$i<$num;$i++)} {{$i}} {/for}
		'/{ ?\/(for|if) ?}/' => '<?php end${1}; ?>',

		'/{{ ?([a-zA-Z0-9>,\(\)_\-\]\[\'"$]{1,50}) ?}}/' => '<?php echo ${1}; ?>', // echo var: {{$var}}

		'/{ ?func ([a-zA-Z0-9_]{1,30}):([a-zA-Z0-9_]{1,50})\( ?([^;<].[^;<]{0,150}?)? ?\) ?}/' => '<?php if (class_exists("${1}")) { $CE = new ${1}($pdo, $tpl); if(method_exists($CE, "${2}")) { $tpl->show($CE->${2}(${3})); } unset($CE); } ?>', // do function 

		'/{ ?sql select\( ?([a-zA-Z0-9_\-$]{1,50}) ?, ?([a-zA-Z0-9_\-$]{1,50}) ?, ?\'([a-zA-Z0-9 _\-]{1,150})\' ?, ?\'([a-zA-Z0-9_\-]{1,50})\' ?, ?\'(.{1,100})\' ?, ?([0-9]{0,5}) ?\) ?}/' => '<?php ${1} = db_get_info(${2}, "${3}", "${4}", \'${5}\', ${6}); ?>', // sql select 
	);
	protected $config_preg = array(
		'/{ ?configuration ?}.*?{ ?\/configuration ?}/is', // configuration: {configuration} ... {/configuration}
		'/{ ?var:([a-zA-Z0-9_\'\]\[]{1,30}) ?}(.*?){ ?\/var ?}/is', // variable: {var:name} value {/var}
	);

	function __construct() {
		global $conf;
		if(isset($conf->caching) && $conf->caching == 1) {
			$this->caching = 1;
		}
	}

	public function set($name , $var) { 
		if (is_array($var) && count($var)) { 
			foreach ($var as $key => $key_var) { 
				$this->set($key , $key_var); 
			}
		} else {
			$this->data[$name] = $var;
		}

		return $this;
	}

	public function setCoreDir() {
		$this->dir = $this->getCoreDir();
	}

	public function setCoreAdminDir() {
		$this->dir = $this->getCoreAdminDir();
	}

	public function setExtraModuleDir($moduleName) {
		$this->dir = $this->getExtraModuleDir($moduleName);
	}

	public function setExtraModuleAdminDir($moduleName) {
		$this->dir = $this->getExtraModuleAdminDir($moduleName);
	}

	public function getCoreDir() {
		return __DIR__ . '/../../' . $this->getRelativeCoreDir();
	}

	public function getCoreAdminDir() {
		return __DIR__ . '/../../' . $this->getRelativeCoreAdminDir();
	}

	public function getExtraModuleDir($moduleName) {
		return __DIR__ . '/../../' . $this->getRelativeExtraModuleDir($moduleName);
	}

	public function getExtraModuleAdminDir($moduleName) {
		return __DIR__ . '/../../' . $this->getRelativeExtraModuleAdminDir($moduleName);
	}

	private function getActiveTemplateName($default = 'solution2') {
		global $conf;

		if(function_exists('configs')) {
			$cfg = configs();
			if(is_object($cfg) && !empty($cfg->template)) {
				return $cfg->template;
			}
			if(is_array($cfg) && !empty($cfg['template'])) {
				return $cfg['template'];
			}
		}

		if(isset($conf)) {
			if(is_object($conf) && !empty($conf->template)) {
				return $conf->template;
			}
			if(is_array($conf) && !empty($conf['template'])) {
				return $conf['template'];
			}
		}

		return $default;
	}

	public function getRelativeCoreDir() {
		return str_replace('{template}', $this->getActiveTemplateName(), self::CLIENT_TEMPLATE_DIR);
	}

	public function getRelativeCoreAdminDir() {
		return self::ADMIN_TEMPLATE_DIR;
	}

	public function getRelativeExtraModuleDir($moduleName) {
		return 'modules_extra/' . $moduleName . '/' . str_replace('{template}', $this->getActiveTemplateName(), self::CLIENT_TEMPLATE_DIR);
	}

	public function getRelativeExtraModuleAdminDir($moduleName) {
		return 'modules_extra/' . $moduleName . '/' . self::ADMIN_TEMPLATE_DIR;
	}

	public function replace_preg($data) {
		foreach ($this->templater_preg as $preg=>$replace){
			$data = preg_replace($preg, $replace, $data);
		}
		return $data;
	}

	private function resolveTemplatePath($tpl_name, $dir = null, $sec_dir = null) {
		$dir = ($dir === null) ? $this->dir : $dir;
		$sec_dir = ($sec_dir === null) ? $this->sec_dir : $sec_dir;
		$path = $sec_dir . $dir . $tpl_name;
		$candidates = array($path);

		$template = $this->getActiveTemplateName(null);
		if(!empty($template)) {
			$fallbacks = array('solution2', 'pbgame_ui', 'standart', 'default');
			foreach($fallbacks as $fallback) {
				if($fallback == $template) {
					continue;
				}

				$candidate = preg_replace('#(?<=templates/)' . preg_quote($template, '#') . '(?=/)#', $fallback, $path, 1);
				if(!empty($candidate) && $candidate != $path) {
					$candidates[] = $candidate;
				}
			}
		}

		$candidates = array_values(array_unique($candidates));
		foreach($candidates as $candidate) {
			if(!empty($candidate) && file_exists($candidate)) {
				return $candidate;
			}
		}

		return $path;
	}

	private function devModeEnabled() {
		global $dev_mode, $safe_mode;

		$configDevMode = isset($dev_mode) ? (int) $dev_mode : 2;
		$sessionDevMode = isset($_SESSION['dev_mode']) ? (int) $_SESSION['dev_mode'] : $configDevMode;
		$safeModeOff = !isset($safe_mode) || (int) $safe_mode != 1;

		return $safeModeOff && ($configDevMode == 1 || $sessionDevMode == 1);
	}

	private function getDevTemplatePathLabel($path, $tplName) {
		$path = str_replace('\\', '/', (string) $path);
		$root = '';

		if(!empty($_SERVER['DOCUMENT_ROOT'])) {
			$root = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\'));
		}

		if($root !== '' && strpos($path, $root . '/') === 0) {
			$path = substr($path, strlen($root) + 1);
		} else {
			$base = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
			if($base !== '' && strpos($path, $base . '/') === 0) {
				$path = substr($path, strlen($base) + 1);
			}
		}

		if($path === '' || $path === false) {
			$path = $tplName;
		}

		return ltrim($path, '/');
	}

	private function isPublicTemplatePath($path) {
		$path = str_replace('\\', '/', (string) $path);

		if(strpos($path, 'templates/admin/') !== false) {
			return false;
		}

		return (strpos($path, 'templates/') !== false);
	}

	private function canShowVisibleDevTemplatePath($tplName, $path) {
		$tplName = ltrim((string) $tplName, '/');
		$hiddenTemplates = array('config.tpl', 'head.tpl', 'main.tpl', 'off_site.tpl', 'bottom.tpl', 'elements/title.tpl');

		if(in_array($tplName, $hiddenTemplates, true)) {
			return false;
		}

		return $this->isPublicTemplatePath($path);
	}

	private function wrapTemplateForDevMode($template, $tplName, $path) {
		if($tplName == 'elements/title.tpl' || !$this->devModeEnabled()) {
			return $template;
		}

		return "\n<!-- Start " . $tplName . " -->\n" . $template . "\n<!-- End " . $tplName . " -->\n";
	}

	public function load_template($tpl_name) {
		if(isset($this->modules_tpls)) {
			$tpl_name = $this->replace_tpl($tpl_name);
		}
		$resolved_path = $this->resolveTemplatePath($tpl_name);
		if ($tpl_name == '' || !file_exists($resolved_path)) { 
			die ("[Class Template]: Unable to load template: ". $this->sec_dir.$this->dir.$tpl_name); 
			return false;
		} 
		$this->using_tpl = $tpl_name;
		$this->using_tpl_path = $resolved_path;

		if($this->caching && !$this->devModeEnabled()) {
			$cache_file = $this->cache_file_name($this->sec_dir.$this->dir.'../cache/'.$this->using_tpl);
			$orig_time = filemtime($this->using_tpl_path);
			if(file_exists($cache_file)) {
				$cache_time = filemtime($cache_file);
				if($cache_time > $orig_time) {
					return $this; 
				}
			}
		}

		$this->template = file_get_contents($this->using_tpl_path); 
		
		$this->template = $this->wrapTemplateForDevMode($this->template, $tpl_name, $this->using_tpl_path);
		
		if ( stristr( $this->template, "{include file=" ) ) {
			$this->includeLimit = 0;
			$this->template = preg_replace_callback( "#\\{include file=['\"](.+?)['\"]\\}#is", [$this, "sub_load_template"], $this->template);
		}

		$this->copy_template = $this->template;
		return $this; 
	}

	public function _clear() { 
		$this->data = array(); 
		$this->copy_template = $this->template;
	} 

	public function clear() { 
		$this->data = array();
		$this->copy_template = null; 
		$this->template = null; 
		$this->using_tpl = '';

		return $this;
	}
	
	public function global_clear() { 
		$this->data = array(); 
		$this->result = array(); 
		$this->copy_template = null; 
		$this->template = null; 
		$this->sec_dir = ''; 
		$this->modules_tpls = array();
		$this->files = '';
		$this->using_tpl = '';
	}

	private function cache_file_name($name) { 
		$path = explode('cache/', $name);
		$path[1] = str_replace('../../../modules_extra/', '', $path[1]);
		$path[1] = str_replace('/', '__', $path[1]);
		return $path[0].'cache/'.$path[1];
	}

	public function compile($tpl) {
		$cache_time = 0;
		$orig_time = 1;
		if($this->caching && !$this->devModeEnabled()) {
			$cache_file = $this->cache_file_name($this->sec_dir.$this->dir.'../cache/'.$this->using_tpl);
			$orig_time = filemtime(isset($this->using_tpl_path) ? $this->using_tpl_path : $this->sec_dir.$this->dir.$this->using_tpl);
			if(file_exists($cache_file)) {
				$cache_time = filemtime($cache_file);
			}
		}

		if($cache_time > $orig_time) {
			$result = file_get_contents($cache_file); 
		} else {
			$result = $this->replace_preg($this->copy_template);

			if($this->caching && !$this->devModeEnabled() && @$cache_file = fopen($cache_file, "w+")) {
				fwrite($cache_file, $result);
				fclose($cache_file); 		
			}
		}

		if($this->using_tpl == 'head.tpl' && $this->dir === self::ADMIN_TEMPLATE_DIR) {
			$find = array('{files}', '{body_style}', '{global_accent_vars}');
			$replace = array(
				$this->files,
				function_exists('pb_premium_body_style') ? pb_premium_body_style() : '',
				function_exists('pb_theme_admin_css_vars') ? pb_theme_admin_css_vars(function_exists('pdo') ? pdo() : null) : '--GLOBAL: radial-gradient(100% 100% at 50% 0, #4fea9f 0, #38644f 100%);'
			);
		} else if($this->using_tpl == 'head.tpl') {
			$find = array('{files}', '{body_style}');
			$replace = array($this->files, function_exists('pb_premium_body_style') ? pb_premium_body_style() : '');
		} else {
			$find[] = 0;	
			$replace[] = 0;	
		}

		foreach ($this->data as $key_find => $key_replace) { 
			$find[] = $key_find; 
			$replace[] = $key_replace; 
		}
		$result = str_replace($find, $replace, $result); 

		if (isset($this->result[$tpl])) {
			$this->result[$tpl] .= $result; 
		} else {
			$this->result[$tpl] = $result; 
		}
		$this->_clear();

		return $this;
	}

	public function show($tpl_data_) {
		foreach($GLOBALS as $key=>$val){
			global $$key;
		}
		eval(' ?>'.$tpl_data_.'<?php ');
	}

	public function getShow($tpl_data_) {
		ob_start();

		foreach($GLOBALS as $key=>$val){
			global $$key;
		}
		eval(' ?>'.$tpl_data_.'<?php ');

		return ob_get_clean();
	}

	public function toNav($name, $point = 0, $cache = 0) {
		global $PI;
		if (!isset($PI) || !is_object($PI) || !method_exists($PI, 'to_nav')) {
			return ['', ''];
		}
		return $PI->to_nav($name, $point, $cache);
	}

	public function get_nav($array, $tpl_name, $point = 0) {
		$count = count($array);
		for ($i=0; $i < $count; $i++) { 
			$this->load_template($tpl_name);
			$this->set("{href}", $array[$i][0]);
			$this->set("{name}", $array[$i][1]);
			$this->compile( 'nav' );
			$this->clear();
		}

		ob_start();
		eval('?>'.$this->result['nav'].'<?php '); 
		$this->result['nav'] = ob_get_clean();

		return $this->result['nav'];
	}

	/*
	$page - номер текущей страницы
	$number - общее количество элементов
	$limit - количество элементов на страницу
	$stages = 3
	$page_name - начало url'a страницы
	*/
	public function get_paginator($page,$number,$limit,$stages,$page_name) {
		if ($page == 0){
			$page = 1;
		}
		if(empty($number)) {
			$number = 0;
		}
		$prev = $page - 1;
		$next = $page + 1;
		$lastpage = ceil($number/$limit);
		$lastpage2 = $lastpage - 1;

		$paginate = '';
		if($lastpage > 1){
			$paginate .= "<ul class='pagination'>";
			if ($page > 1){
				$paginate.= "<li><a href='".$page_name."page=".$prev."'><span aria-hidden='true'>&laquo;</span><span class='sr-only'>Назад</span></a></li>";
			} else {
				$paginate.= "<li class='disabled'><a><span aria-hidden='true'>&laquo;</span><span class='sr-only'>Назад</span></a></li>";
			}

			if ($lastpage < 7 + ($stages * 2)){ 
				for ($counter = 1; $counter <= $lastpage; $counter++){
					if ($counter == $page){
						$paginate.= "<li class='active'><a>$counter</a></li>";
					} else {
						$paginate.= "<li><a href='".$page_name."page=".$counter."'>$counter</a></li>";
					}
				}
			} elseif($lastpage > 5 + ($stages * 2)){
				if($page < 1 + ($stages * 2)){
					for ($counter = 1; $counter < 4 + ($stages * 2); $counter++){
						if ($counter == $page){
							$paginate.= "<li class='active'><a>$counter</a></li>";
						} else {
							$paginate.= "<li><a href='".$page_name."page=".$counter."'>$counter</a></li>";
						}
					}
					$paginate.= "<li><a>...</a></li>";
					$paginate.= "<li><a href='".$page_name."page=$lastpage2'>$lastpage2</a></li>";
					$paginate.= "<li><a href='".$page_name."page=$lastpage'>$lastpage</a></li>";
				} elseif($lastpage - ($stages * 2) > $page && $page > ($stages * 2)){
					$paginate.= "<li><a href='".$page_name."page=1'>1</a></li>";
					$paginate.= "<li><a href='".$page_name."page=2'>2</a></li>";
					$paginate.= "<li><a>...</a></li>";
					for ($counter = $page - $stages; $counter <= $page + $stages; $counter++){
						if ($counter == $page){
							$paginate.= "<li class='active'><a>$counter</a></li>";;
						} else {
							$paginate.= "<li><a href='".$page_name."page=".$counter."'>$counter</a></li>";
						}
					}
					$paginate.= "<li><a>...</a></li>";
					$paginate.= "<li><a href='".$page_name."page=".$lastpage2."'>$lastpage2</a></li>";
					$paginate.= "<li><a href='".$page_name."page=".$lastpage."'>$lastpage</a></li>";
				} else {
					$paginate.= "<li><a href='".$page_name."page=1'>1</a></li>";
					$paginate.= "<li><a href='".$page_name."page=2'>2</a></li>";
					$paginate.= "<li><a>...</a></li>";
					for ($counter = $lastpage - (2 + ($stages * 2)); $counter <= $lastpage; $counter++){
						if ($counter == $page){
							$paginate.= "<li class='active'><a>$counter</a></li>";
						} else {
							$paginate.= "<li><a href='".$page_name."page=".$counter."'>$counter</a></li>";
						}
					}
				}
			}

			if ($page < $counter - 1){ 
				$paginate.= "<li><a href='".$page_name."page=".$next."'><span aria-hidden='true'>&raquo;</span><span class='sr-only'>Вперед</span></a></li>";
			} else {
				$paginate.= "<li class='disabled'><a><span aria-hidden='true'>&raquo;</span><span class='sr-only'>Вперед</span></a></li>";
			}
			$paginate.= "</ul>";
		}
		return $paginate;
	}

	public function get_menu($pdo){
		$menu = '';

		try {
			foreach(['menu', 'menu__sub'] as $table) {
				$STH = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'groups_access'");
				if(!$STH || !$STH->fetch(PDO::FETCH_ASSOC)) {
					$pdo->exec("ALTER TABLE `$table` ADD COLUMN `groups_access` varchar(255) NOT NULL DEFAULT '' AFTER `for_all`");
				}
			}
		} catch(Throwable $e) {}

		$menuAccessAllowed = function($row) {
			$forAll = isset($row['for_all']) ? (int)$row['for_all'] : 1;
			$privacyAllowed = (
				$forAll === 1 ||
				($forAll === 2 && isset($_SESSION['id'])) ||
				($forAll === 3 && empty($_SESSION['id']))
			);

			if(!$privacyAllowed) {
				return false;
			}

			$groupsAccess = isset($row['groups_access']) ? trim((string)$row['groups_access']) : '';
			if($groupsAccess === '') {
				return true;
			}

			if(empty($_SESSION['id']) || !isset($_SESSION['rights'])) {
				return false;
			}

			$allowed = [];
			foreach(preg_split('/[,\s;]+/', $groupsAccess) as $groupId) {
				$groupId = (int)$groupId;
				if($groupId > 0) {
					$allowed[] = $groupId;
				}
			}

			if(empty($allowed)) {
				return true;
			}

			return in_array((int)$_SESSION['rights'], $allowed, true);
		};

		$STH = $pdo->query("SELECT * FROM `menu` ORDER BY `poz`");
		$STH->execute();
		$temp = $STH->fetchAll();
		$count_temp = count($temp);

		if ($count_temp != 0){
			for($i_temp=0; $i_temp<$count_temp; $i_temp++){
				if($temp[$i_temp]['menu__sub']!='0'){
					$id=$temp[$i_temp]['id'];
					$STH = $pdo->query("SELECT * FROM `menu__sub` WHERE `menu`='$id' ORDER BY `poz`");
					$STH->execute();
					$temp2 = $STH->fetchAll();
					$count_temp2 = count($temp2);

					if (($count_temp2 != 0) && $menuAccessAllowed($temp[$i_temp])){
						$subMenu = '';
						for($i_temp2=0; $i_temp2<$count_temp2; $i_temp2++){
							if($menuAccessAllowed($temp2[$i_temp2])) {
								if($temp2[$i_temp2]['link'] == '../profile' && isset($_SESSION['id'])) {
									$temp2[$i_temp2]['link'] = '../profile?id='.$_SESSION['id'];
								}
								$subMenu.='<li><a href="'.$temp2[$i_temp2]['link'].'">'.$temp2[$i_temp2]['name'].'</a></li>';
							}
						}

						if($subMenu != '') {
							$menu.= '<li class="collapsible"><a href="">'.$temp[$i_temp]['name'].'</a><ul>';
							$menu.= $subMenu;
							$menu.='</ul></li>';
						}
					}
				} else {
					if($menuAccessAllowed($temp[$i_temp])) {
						if($temp[$i_temp]['link'] == '../profile' && isset($_SESSION['id'])) {
							$temp[$i_temp]['link'] = '../profile?id='.$_SESSION['id'];
						}
						$menu.= '<li><a href="'.$temp[$i_temp]['link'].'">'.$temp[$i_temp]['name'].'</a></li>';
					}
				}
			} 
		}
		return $menu;
	}


	public function dell_cache($template = null) {
		$root = rtrim(str_replace('\\', '/', $_SERVER["DOCUMENT_ROOT"]), '/');
		$rootReal = realpath($root);
		$cacheDirs = array();
		$deleted = 0;

		$addCacheDir = function($dir) use (&$cacheDirs, $rootReal) {
			$dir = rtrim(str_replace('\\', '/', $dir), '/');
			if(!is_dir($dir)) {
				return;
			}
			$dirReal = realpath($dir);
			if($dirReal === false || $rootReal === false || strpos($dirReal, $rootReal) !== 0) {
				return;
			}
			$cacheDirs[$dirReal] = $dirReal;
		};

		$clearCacheDir = function($dir) use (&$clearCacheDir, &$deleted) {
			if(!is_dir($dir)) {
				return;
			}

			$items = @scandir($dir);
			if(!is_array($items)) {
				return;
			}

			foreach($items as $item) {
				if($item === '.' || $item === '..' || $item === '.htaccess' || $item === '.keep' || $item === 'index.html' || $item === 'index.php') {
					continue;
				}

				$path = $dir . '/' . $item;
				if(is_dir($path)) {
					$clearCacheDir($path);
					if(@rmdir($path)) {
						$deleted++;
					}
				} elseif(is_file($path) || is_link($path)) {
					if(@unlink($path)) {
						$deleted++;
					}
				}
			}
		};

		// Clear runtime caches used by the engine and templates.
		foreach(array(
			$root . '/cache',
			$root . '/tmp',
			$root . '/files/cache',
			$root . '/files/temp',
			$root . '/files/tmp'
		) as $dir) {
			$addCacheDir($dir);
		}

		if($template != null) {
			$addCacheDir($root . "/templates/" . $template . "/cache");
		} else {
			foreach((array) glob($root . '/templates/*/cache', GLOB_ONLYDIR) as $dir) {
				$addCacheDir($dir);
			}
		}

		foreach((array) glob($root . '/modules_extra/*/templates/*/cache', GLOB_ONLYDIR) as $dir) {
			$addCacheDir($dir);
		}

		foreach($cacheDirs as $dir) {
			$clearCacheDir($dir);
		}

		clearstatcache();

		return $deleted;
	}

	private function replace_tpl($tpl_name) {
		$this->sec_dir = '';
		for ($i=0; $i < count($this->modules_tpls); $i++) {
			for ($j=0; $j < count($this->modules_tpls[$i]); $j++) {
				if($tpl_name == $this->modules_tpls[$i][$j][0]) {
					$this->sec_dir = "modules_extra/".$this->modules_tpls[$i][0]."/";
					$tpl_name = $this->modules_tpls[$i][$j][1];
					break(2);
				}
			}
		}
		return $tpl_name;
	}

	private function sub_load_template($tpl_name) {
		$this->includeLimit++;

		if($this->includeLimit == 10) {
			return '';
		}

		if ($tpl_name[1] == '' || !file_exists($this->dir.'/'.$tpl_name[1])) {
			die ("[Class Template]: Unable to load template: ". $tpl_name[1]);
		}

		$includePath = $this->dir.'/'.$tpl_name[1];
		$template = file_get_contents($includePath);

		if ( stristr( $template, "{include file=" ) ) {
			$template = preg_replace_callback( "#\\{include file=['\"](.+?)['\"]\\}#is", [$this, "sub_load_template"], $template);
		} else {
			$template = $this->wrapTemplateForDevMode($template, $tpl_name[1], $includePath);
			if($tpl_name[1] == 'config.tpl') {
				if(preg_match($this->config_preg[0], $template)) {

					preg_match_all($this->config_preg[1], $template, $matches, PREG_SET_ORDER);

					$template = '<?php'."\n";
					for ($i = 0; $i < count($matches); $i++) {
						$template .= '$'.$matches[$i][1].' = \''.str_replace("'", "\'", $matches[$i][2]).'\';'."\n";
					}
					$template .= "\n".'?>';
				}
			}
		}

		return $template;
	}
}
