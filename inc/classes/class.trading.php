<?PHP
	class Trading {
		private static $schemaEnsured = false;

		private static function tableExists($table) {
			try {
				$sth = pdo()->query("SHOW TABLES LIKE " . pdo()->quote($table));
				return $sth && $sth->rowCount() > 0;
			} catch (Throwable $e) {
				return false;
			}
		}

		private static function columnExists($table, $column) {
			try {
				$sth = pdo()->query("SHOW COLUMNS FROM `" . str_replace('`', '', $table) . "` LIKE " . pdo()->quote($column));
				return $sth && $sth->rowCount() > 0;
			} catch (Throwable $e) {
				return false;
			}
		}

		public static function ensureSchema() {
			if(self::$schemaEnsured) {
				return true;
			}
			self::$schemaEnsured = true;

			$pdo = pdo();
			try {
				$pdo->exec("CREATE TABLE IF NOT EXISTS `playground` (
					`id` int NOT NULL AUTO_INCREMENT,
					`currency` varchar(64) NOT NULL DEFAULT 'поинт',
					`secret` varchar(256) NOT NULL DEFAULT 'none',
					`limit_product` int(9) NOT NULL DEFAULT '9',
					`course` float NOT NULL DEFAULT '0.1',
					`bonuses` varchar(9) NOT NULL DEFAULT '0',
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8");

				if(!self::columnExists('playground', 'secret')) {
					$pdo->exec("ALTER TABLE `playground` ADD `secret` varchar(256) NOT NULL DEFAULT 'none'");
				}
				if(!self::columnExists('playground', 'limit_product')) {
					$pdo->exec("ALTER TABLE `playground` ADD `limit_product` int(9) NOT NULL DEFAULT '9'");
				}
				if(!self::columnExists('playground', 'course')) {
					$pdo->exec("ALTER TABLE `playground` ADD `course` float NOT NULL DEFAULT '0.1'");
				}
				if(!self::columnExists('playground', 'bonuses')) {
					$pdo->exec("ALTER TABLE `playground` ADD `bonuses` varchar(9) NOT NULL DEFAULT '0'");
				}
				if(!self::columnExists('playground', 'default_price')) {
					$pdo->exec("ALTER TABLE `playground` ADD `default_price` float NOT NULL DEFAULT '0'");
				}
				if(!self::columnExists('playground', 'default_availability')) {
					$pdo->exec("ALTER TABLE `playground` ADD `default_availability` int NOT NULL DEFAULT '100'");
				}
				$pdo->exec("INSERT IGNORE INTO `playground` (`id`, `currency`, `secret`, `limit_product`, `course`, `bonuses`) VALUES (1, 'поинт', 'none', 9, 0.1, '0')");
				$pdo->exec("UPDATE `playground` SET `limit_product`='9' WHERE `id`='1' AND (`limit_product` IS NULL OR `limit_product` < 1)");

				$pdo->exec("CREATE TABLE IF NOT EXISTS `playground__category` (
					`id` int NOT NULL AUTO_INCREMENT,
					`name` varchar(64) NOT NULL,
					`code_name` varchar(64) NOT NULL,
					`main` int NOT NULL DEFAULT '0',
					`enabled` int NOT NULL DEFAULT '1',
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8");
				if(!self::columnExists('playground__category', 'enabled')) {
					$pdo->exec("ALTER TABLE `playground__category` ADD `enabled` int NOT NULL DEFAULT '1'");
				}
				if((int)$pdo->query("SELECT COUNT(*) FROM `playground__category`")->fetchColumn() === 0) {
					$pdo->exec("INSERT INTO `playground__category` (`id`, `name`, `code_name`, `main`, `enabled`) VALUES
						(1, 'Фон профиля', 'background', 1, 1),
						(2, 'Аватар', 'avatar', 1, 1),
						(3, 'Рамка профиля', 'frame', 1, 1)");
				}

				$pdo->exec("CREATE TABLE IF NOT EXISTS `playground__product` (
					`id` int NOT NULL AUTO_INCREMENT,
					`name` varchar(128) NOT NULL,
					`price` float NOT NULL DEFAULT '0',
					`resource` text NOT NULL,
					`executor` text NOT NULL,
					`id_category` int NOT NULL DEFAULT '0',
					`availability` int NOT NULL DEFAULT '100',
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8");
				if(!self::columnExists('playground__product', 'library_source')) {
					$pdo->exec("ALTER TABLE `playground__product` ADD `library_source` varchar(191) DEFAULT NULL AFTER `availability`");
					$pdo->exec("ALTER TABLE `playground__product` ADD KEY `library_source` (`library_source`)");
				}

				$pdo->exec("CREATE TABLE IF NOT EXISTS `playground__purchases` (
					`id` int NOT NULL AUTO_INCREMENT,
					`pid` int NOT NULL,
					`category` int NOT NULL,
					`uid` int NOT NULL,
					`price` float NOT NULL DEFAULT '0',
					`date` varchar(64) NOT NULL DEFAULT '0000-00-00 00:00:00',
					`enable` int NOT NULL DEFAULT '0',
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8");

				$pdo->exec("CREATE TABLE IF NOT EXISTS `playground__sale` (
					`id` int NOT NULL AUTO_INCREMENT,
					`id_product` int NOT NULL,
					`id_category` int NOT NULL,
					`id_seller` int NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8");

				$pdo->exec("CREATE TABLE IF NOT EXISTS `playground__commands` (
					`id` int NOT NULL AUTO_INCREMENT,
					`pid` int NOT NULL,
					`sid` int NOT NULL,
					`command` varchar(256) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8");

				if(self::tableExists('users') && !self::columnExists('users', 'playground')) {
					$pdo->exec("ALTER TABLE `users` ADD `playground` int DEFAULT '0'");
				}
			} catch (Throwable $e) {
				// Не ломаем страницу маркета, если у пользователя БД не дала ALTER/CREATE.
				return false;
			}

			return true;
		}

		private static function defaultConf() {
			return (object)[
				'id' => 1,
				'currency' => 'поинт',
				'secret' => 'none',
				'limit_product' => 9,
				'course' => 0.1,
				'bonuses' => '0'
			];
		}

		private static function productTpl() {
			$_tplName = configs()->template;
			if(empty($_tplName)) {
				try {
					$row = pdo()->query("SELECT `template` FROM `settings` LIMIT 1")->fetch(PDO::FETCH_OBJ);
					$_tplName = !empty($row->template) ? $row->template : 'solution2';
				} catch (Throwable $e) {
					$_tplName = 'solution2';
				}
			}
			$_productPatch = $_SERVER['DOCUMENT_ROOT'] . "/templates/" . $_tplName . "/tpl/elements/playground/ui/product.tpl";
			if(file_exists($_productPatch)) {
				return file_get_contents($_productPatch);
			}

			return '<div class="pb-market-item cs16-playground-product-card{purchased_class}" data-id="{id}">
				<div class="pb-market-preview cs16-market-item-preview">{preview}</div>
				<div class="pb-market-name cs16-market-item-name">{name}</div>
				<div class="pb-market-price cs16-market-item-stats"><span class="pb-market-stat-value">{price}</span> {currency}</div>
				<button class="btn btn-primary pb-market-buy" data-id="{id}"{purchased_disabled}{purchased_title}>Купить</button>
			</div>';
		}

		public static function getProducts($page = 1, $category = null) {
			self::ensureSchema();
			$category = clean($category);
			$ic = null;
			if(!empty($category)) {
				$ic = self::IsValidCategory($category) ? self::GetCategoryId($category) : -1;
			}

			$conf = self::conf();
			$limit = max(1, (int)$conf->limit_product);
			$page = max(1, (int)$page);
			$start = ($page * $limit) - $limit;

			try {
				$sql = "SELECT p.* FROM `playground__product` p LEFT JOIN `playground__category` c ON c.id=p.id_category WHERE (c.enabled IS NULL OR c.enabled=1)";
				if(!empty($category)) {
					$sql .= " AND p.`id_category`='" . (int)$ic . "'";
				}
				$sql .= " ORDER BY p.`id` DESC LIMIT " . (int)$start . ", " . (int)$limit;
				$sth = pdo()->query($sql);
			} catch (Throwable $e) {
				return '<center>Торговая площадка временно недоступна. Проверьте таблицы playground.</center>';
			}

			if(!$sth || !$sth->rowCount()) {
				return '<center>Товаров нет.</center>';
			}

			$uid = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
			try {
				$_currency = sys()->currency()->lang;
			} catch (Throwable $e) {
				$_currency = '₽';
			}
			$_productTpl = self::productTpl();

			$buf = '';
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				try {
					$purchased = $uid > 0 && pdo()->query("SELECT `id` FROM `playground__purchases` WHERE `uid`='$uid' AND `pid`='" . (int)$row->id . "' LIMIT 1")->rowCount() > 0;
				} catch (Throwable $e) {
					$purchased = false;
				}
				$_ext = strtolower(pathinfo($row->resource, PATHINFO_EXTENSION));
				$_isVideo = in_array($_ext, ['mp4', 'webm']);
				$_isGif = $_ext === 'gif';
				$_imgClass = $_isGif ? 'pb-lazy-img pb-lazy-gif' : 'pb-lazy-img';
				$_preview = $_isVideo
					? '<video class="pb-market-preview-video pb-lazy-video" muted loop playsinline preload="none" data-src="/files/playground/' . htmlspecialchars($row->resource) . '"><source data-src="/files/playground/' . htmlspecialchars($row->resource) . '" type="video/' . $_ext . '"></video>'
					: '<img class="' . $_imgClass . '" src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\'%3E%3C/svg%3E" data-src="/files/playground/' . htmlspecialchars($row->resource) . '" alt="' . htmlspecialchars($row->name) . '" loading="lazy" decoding="async">';

				$card = $_productTpl;
				$card = str_replace("{id}", $row->id, $card);
				$card = str_replace("{name}", $row->name, $card);
				$card = str_replace("{price}", $row->price, $card);
				$card = str_replace("{currency}", $_currency, $card);
				$card = str_replace("{resource}", $row->resource, $card);
				$card = str_replace("{preview}", $_preview, $card);
				$card = str_replace("{availability}", $row->availability, $card);
				$card = str_replace("{id_category}", $row->id_category, $card);
				$card = str_replace("{category_name}", self::getCategoryName($row->id_category), $card);
				$card = str_replace("{purchased_class}", $purchased ? " dc-purchased" : "", $card);
				$card = str_replace("{purchased_disabled}", $purchased ? " disabled" : "", $card);
				$card = str_replace("{purchased_title}", $purchased ? " title=\"Уже куплено\"" : "", $card);
				$buf .= $card;
			}

			return $buf;
		}
		
		public static function rowProducts($category = null) {
			self::ensureSchema();
			$category = clean($category);
			try {
				if(empty($category)) {
					return pdo()->query("SELECT p.id FROM `playground__product` p LEFT JOIN `playground__category` c ON c.id=p.id_category WHERE (c.enabled IS NULL OR c.enabled=1)")->rowCount();
				}
				return pdo()->query("SELECT p.id FROM `playground__product` p LEFT JOIN `playground__category` c ON c.id=p.id_category WHERE (c.enabled IS NULL OR c.enabled=1) AND p.`id_category`='" . (int)self::GetCategoryId($category) . "'")->rowCount();
			} catch (Throwable $e) {
				return 0;
			}
		}
		
		public static function IsValidCategory($name) {
			self::ensureSchema();
			$name = clean($name);
			try {
				return pdo()->query("SELECT * FROM `playground__category` WHERE `code_name`=" . pdo()->quote($name) . " LIMIT 1")->rowCount();
			} catch (Throwable $e) {
				return 0;
			}
		}
		
		public static function GetCategoryId($name) {
			self::ensureSchema();
			$name = clean($name);
			try {
				$sth = pdo()->query("SELECT * FROM `playground__category` WHERE `code_name`=" . pdo()->quote($name) . " LIMIT 1");
				if($sth->rowCount()) {
					return $sth->fetch(PDO::FETCH_OBJ)->id;
				}
			} catch (Throwable $e) {
				return null;
			}
			return null;
		}
		
		public static function conf() {
			self::ensureSchema();
			try {
				$row = pdo()->query("SELECT * FROM `playground` LIMIT 1")->fetch(PDO::FETCH_OBJ);
				if($row) {
					if(empty($row->limit_product) || (int)$row->limit_product < 1) {
						$row->limit_product = 9;
					}
					return $row;
				}
			} catch (Throwable $e) {
				// fallback ниже
			}
			return self::defaultConf();
		}

		public static function getCategoryName($categoryId) {
			self::ensureSchema();
			$categoryId = (int)$categoryId;
			if($categoryId < 1) {
				return 'Без категории';
			}

			try {
				$sth = pdo()->prepare("SELECT `name` FROM `playground__category` WHERE `id`=:id LIMIT 1");
				$sth->execute([':id' => $categoryId]);
				$row = $sth->fetch(PDO::FETCH_OBJ);
				if(!empty($row->name)) {
					return $row->name;
				}
			} catch (Throwable $e) {
				// noop
			}

			return 'Без категории';
		}
		
		private static function categoryKind($categoryId) {
			$categoryId = (int)$categoryId;
			$codeName = '';
			$name = '';
			if($categoryId > 0) {
				try {
					$sth = pdo()->prepare("SELECT `code_name`, `name` FROM `playground__category` WHERE `id`=:id LIMIT 1");
					$sth->execute([':id' => $categoryId]);
					$row = $sth->fetch(PDO::FETCH_OBJ);
					if($row) {
						$codeName = mb_strtolower((string)$row->code_name);
						$name = mb_strtolower((string)$row->name);
					}
				} catch (Throwable $e) {
					// noop
				}
			}

			$probe = $codeName . ' ' . $name;
			if(strpos($probe, 'frame') !== false || strpos($probe, 'ram') !== false || strpos($probe, 'рам') !== false) {
				return 'frame';
			}
			if(strpos($probe, 'avatar') !== false || strpos($probe, 'avatarki') !== false || strpos($probe, 'авата') !== false) {
				return 'avatar';
			}
			if(strpos($probe, 'background') !== false || strpos($probe, 'bg') !== false || strpos($probe, 'фон') !== false) {
				return 'background';
			}
			return 'other';
		}

		public static function renderPreview($pid, $uid = 0) {
			self::ensureSchema();
			$product = self::GetProduct($pid);
			if(empty($product->id)) {
				return '<div class="pb-preview-empty">Товар не найден</div>';
			}

			$uid = (int)$uid;
			$kind = self::categoryKind($product->id_category ?? 0);
			$resource = ltrim(str_replace('..', '', (string)$product->resource), '/');
			$resourceFile = '/files/playground/' . $resource;
			$ext = strtolower(pathinfo($resource, PATHINFO_EXTENSION));
			$isVideo = in_array($ext, ['mp4', 'webm']);
			$safeFile = htmlspecialchars($resourceFile, ENT_QUOTES, 'UTF-8');
			$safeName = htmlspecialchars((string)$product->name, ENT_QUOTES, 'UTF-8');

			if($kind === 'background') {
				$media = $isVideo
					? '<video class="pb-preview-bg-video" autoplay muted loop playsinline preload="none"><source src="' . $safeFile . '" type="video/' . $ext . '"></video>'
					: '<div class="pb-preview-bg-img" style="background-image:url(' . $safeFile . ');"></div>';

				$avatarSrc = htmlspecialchars(function_exists('get_user_avatar_src') ? get_user_avatar_src($uid) : '', ENT_QUOTES, 'UTF-8');
				$frame = $uid > 0 && function_exists('get_user_frame') ? get_user_frame($uid) : '';
				$frameHtml = !empty($frame) ? '<img class="pb-preview-card-frame" src="' . htmlspecialchars($frame, ENT_QUOTES, 'UTF-8') . '" alt="">' : '';
				$currentUser = $uid > 0 && function_exists('usr') ? usr($uid) : null;
				$login = $currentUser && !empty($currentUser->login) ? $currentUser->login : 'Игрок';

				return '<div class="pb-preview-frame pb-preview-frame--bg">'
					. $media
					. '<div class="pb-preview-bg-overlay"></div>'
					. '<div class="pb-preview-card">'
					. '<span class="pb-preview-card-avatar-wrap"><img class="pb-preview-card-avatar" src="' . $avatarSrc . '" alt="">' . $frameHtml . '</span>'
					. '<span class="pb-preview-card-login">' . htmlspecialchars($login, ENT_QUOTES, 'UTF-8') . '</span>'
					. '</div>'
					. '</div>';
			}

			if($kind === 'frame') {
				$avatarSrc = htmlspecialchars(function_exists('get_user_avatar_src') ? get_user_avatar_src($uid) : '', ENT_QUOTES, 'UTF-8');
				return '<div class="pb-preview-frame pb-preview-frame--avatar">'
					. '<span class="pb-preview-avatar-wrap pb-preview-avatar-wrap--big"><img class="pb-preview-avatar" src="' . $avatarSrc . '" alt=""><img class="pb-preview-frame-img" src="' . $safeFile . '" alt=""></span>'
					. '</div>';
			}

			if($kind === 'avatar') {
				return '<div class="pb-preview-frame pb-preview-frame--avatar">'
					. '<span class="pb-preview-avatar-wrap pb-preview-avatar-wrap--big"><img class="pb-preview-avatar" src="' . $safeFile . '" alt="' . $safeName . '"></span>'
					. '</div>';
			}

			$media = $isVideo
				? '<video class="pb-preview-plain-video" autoplay muted loop playsinline preload="none"><source src="' . $safeFile . '" type="video/' . $ext . '"></video>'
				: '<img class="pb-preview-plain-img" src="' . $safeFile . '" alt="' . $safeName . '">';
			return '<div class="pb-preview-frame pb-preview-frame--plain">' . $media . '</div>';
		}

		public static function getCategoryMenu($active = 0) {
			self::ensureSchema();
			try {
				$sth = pdo()->query("SELECT * FROM `playground__category` WHERE `enabled`=1 OR `enabled` IS NULL ORDER BY `id` ASC");
			} catch (Throwable $e) {
				return "";
			}
			
			$buf = "";
			if(!$sth->rowCount()) {
				return $buf;
			}
			
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				$buf .= "<li><a href=\"/market?sort=" . htmlspecialchars($row->code_name) . "\" data-sort=\"" . htmlspecialchars($row->code_name) . "\" " . ($active == $row->id ? "class='active'" : "") . ">" . htmlspecialchars($row->name) . "</a></li>";
			}
			
			return $buf;
		}
		
		public static function IsValidProduct($pid) {
			self::ensureSchema();
			try {
				return pdo()->query("SELECT * FROM `playground__product` WHERE `id`='" . (int)$pid . "' LIMIT 1")->rowCount();
			} catch (Throwable $e) {
				return 0;
			}
		}
		
		public static function GetProduct($pid) {
			self::ensureSchema();
			try {
				return pdo()->query("SELECT * FROM `playground__product` WHERE `id`='" . (int)$pid . "' LIMIT 1")->fetch(PDO::FETCH_OBJ);
			} catch (Throwable $e) {
				return (object)['id' => 0];
			}
		}
		
		public static function SetProduct($pid, $key, $vault) {
			self::ensureSchema();
			return pdo()->prepare("UPDATE `playground__product` SET `$key`=:vault WHERE `id`=:pid")->execute([
				':pid' => $pid,
				':vault' => $vault
			]);
		}
		
		public static function GetBalance($uid) {
			try {
				$row = pdo()->query("SELECT * FROM `users` WHERE `id`='" . (int)$uid . "' LIMIT 1")->fetch(PDO::FETCH_OBJ);
				return !empty($row->playground) ? $row->playground : 0;
			} catch (Throwable $e) {
				return 0;
			}
		}
		
		public static function SetBalance($uid, $count) {
			return pdo()->prepare("UPDATE `users` SET `playground`=:count WHERE `id`=:uid LIMIT 1")->execute([
				':uid' => $uid,
				':count' => $count
			]);
		}
		
		public static function addPurchases($uid, $pid, $price, $enable = 0) {
			self::ensureSchema();
			$product = self::GetProduct($pid);
			$category = isset($product->id_category) ? (int)$product->id_category : 0;
			$enable = (int)$enable ? 1 : 0;
			return pdo()->prepare("INSERT INTO `playground__purchases`(`pid`, `category`, `uid`, `price`, `date`, `enable`) VALUES (:pid, :category, :uid, :price, :date, :enable)")->execute([
				':pid' => $pid,
				':uid' => $uid,
				':category' => $category,
				':price' => $price,
				':date' => date("Y-m-d H:i:s"),
				':enable' => $enable
			]);
		}
		
		public static function GetInventory($uid) {
			self::ensureSchema();
			try {
				$sth = pdo()->query("SELECT * FROM `playground__purchases` WHERE `uid`='" . (int)$uid . "' ORDER BY `id` DESC");
			} catch (Throwable $e) {
				return "<div style='grid-column:1/-1;padding:32px;border-radius:18px;background:rgba(52,54,73,.78);text-align:center;color:#fff;'>Инвентарь временно недоступен</div>";
			}

			if(!$sth->rowCount()) {
				return "<div style='grid-column:1/-1;padding:32px;border-radius:18px;background:rgba(52,54,73,.78);text-align:center;color:#fff;'>Инвентарь пуст</div>";
			}

			$buf = '';
			$_tplName = configs()->template;
			if(empty($_tplName)) {
				try {
					$_tplName = pdo()->query("SELECT `template` FROM `settings` LIMIT 1")->fetch(PDO::FETCH_OBJ)->template ?? 'solution2';
				} catch (Throwable $e) {
					$_tplName = 'solution2';
				}
			}
			$patch = $_SERVER['DOCUMENT_ROOT'] . "/templates/" . $_tplName . "/tpl/elements/playground/card/items.tpl";
			$tpl = file_exists($patch) ? file_get_contents($patch) : '<div>{preview}<b>{name}</b></div>';

			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				$Product = Trading::GetProduct($row->pid);
				if(empty($Product->id)) {
					continue;
				}

				$_ext = strtolower(pathinfo($Product->resource, PATHINFO_EXTENSION));
				$_isVideo = in_array($_ext, ['mp4', 'webm']);
				$_isGif = $_ext === 'gif';
				$_imgClass = $_isGif ? 'pb-lazy-img pb-lazy-gif' : 'pb-lazy-img';
				$_preview = $_isVideo
					? '<video class="pb-inventory-preview-video pb-lazy-video" muted loop playsinline preload="none" data-src="/files/playground/' . htmlspecialchars($Product->resource) . '"><source data-src="/files/playground/' . htmlspecialchars($Product->resource) . '" type="video/' . $_ext . '"></video>'
					: '<img class="' . $_imgClass . '" src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\'%3E%3C/svg%3E" data-src="/files/playground/' . htmlspecialchars($Product->resource) . '" alt="' . htmlspecialchars($Product->name) . '" loading="lazy" decoding="async">';

				$card = $tpl;
				$card = str_replace("{id}", $row->id, $card);
				$card = str_replace("{name}", $Product->name, $card);
				$card = str_replace("{category}", $row->category, $card);
				$card = str_replace("{enable}", $row->enable ? "active" : "", $card);
				$card = str_replace("{state}", $row->enable ? "Активен" : "Не активен", $card);
				$card = str_replace("{resource}", $Product->resource, $card);
				$card = str_replace("{preview}", $_preview, $card);
				$card = str_replace("{category_name}", self::getCategoryName($row->category), $card);
				$buf .= $card;
			}

			return $buf;
		}
		
		public static function IsUserPurchases($uid, $pid) {
			self::ensureSchema();
			try {
				return pdo()->query("SELECT * FROM `playground__purchases` WHERE `uid`='" . (int)$uid . "' and `id`='" . (int)$pid . "' LIMIT 1")->rowCount();
			} catch (Throwable $e) {
				return 0;
			}
		}
		
		public static function GetPurchases($pid) {
			self::ensureSchema();
			try {
				return pdo()->query("SELECT * FROM `playground__purchases` WHERE `id`='" . (int)$pid . "' LIMIT 1")->fetch(PDO::FETCH_OBJ);
			} catch (Throwable $e) {
				return (object)['id' => 0];
			}
		}
		
		public static function SetPurchases($pid, $key, $value) {
			self::ensureSchema();
			return pdo()->prepare("UPDATE `playground__purchases` SET `$key`=:value WHERE `id`=:pid LIMIT 1")->execute([
				':value' => $value,
				':pid' => $pid
			]);
		}
		
		public static function getInventoryCategories($uid) {
			self::ensureSchema();
			try {
				$sth = pdo()->query("SELECT DISTINCT pu.category FROM `playground__purchases` pu WHERE pu.uid='" . (int)$uid . "'");
			} catch (Throwable $e) {
				return '';
			}
			if(!$sth->rowCount()) return '';

			$buf = '<li><a href="#" class="active" data-cat="all">Все</a></li>';
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				$name = self::getCategoryName($row->category);
				$buf .= '<li><a href="#" data-cat="' . (int)$row->category . '">' . htmlspecialchars($name) . '</a></li>';
			}
			return $buf;
		}

		public static function OffUserPurchases($uid, $category) {
			self::ensureSchema();
			return pdo()->query("UPDATE `playground__purchases` SET `enable`='0' WHERE `uid`='" . (int)$uid . "' and `category`='" . (int)$category . "'");
		}
		
		public static function RemoteNoty($pid, $postfields = []) {
			$Product = self::GetProduct($pid);

			if (empty($Product->executor) || $Product->executor === 'none' || !preg_match('~^https?://~i', $Product->executor)) {
				return null;
			}

			foreach($postfields as $key => $value) {
				$Product->executor = str_replace('{' . $key . '}', $value, $Product->executor);
			}

			$ch = curl_init($Product->executor);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			$result = curl_exec($ch);
			curl_close($ch);

			return $result;

		}
		public static function addLogs($message) {
			$line = '[' . date('Y-m-d H:i:s') . '] ' . (string)$message . "\r\n";
			$dir = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2)), '/\\') . '/logs';
			if(!is_dir($dir)) {
				@mkdir($dir, 0755, true);
			}
			@file_put_contents($dir . '/trading.txt', $line, FILE_APPEND | LOCK_EX);
			return true;
		}

		public static function getServersRcon() {
			try {
				$sth = pdo()->query("SELECT `id`, `name`, `ip`, `port`, `rcon`, `rcon_password`, `show` FROM `servers` WHERE `rcon`='1' AND `rcon_password`!='' AND `rcon_password`!='0' ORDER BY `id` ASC");
			} catch (Throwable $e) {
				return '<option value="0">Серверы с RCON не найдены</option>';
			}

			if(!$sth || !$sth->rowCount()) {
				return '<option value="0">Серверы с RCON не найдены</option>';
			}

			$html = '<option disabled selected>- выбрать сервер -</option>';
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				$name = !empty($row->name) ? $row->name : ($row->ip . ':' . $row->port);
				$html .= '<option value="' . (int)$row->id . '">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</option>';
			}
			return $html;
		}

		public static function addCommand($pid, $sid, $command) {
			self::ensureSchema();
			$pid = (int)$pid;
			$sid = (int)$sid;
			$command = trim((string)$command);
			if($pid < 1 || $sid < 1 || $command === '') {
				return false;
			}
			return pdo()->prepare("INSERT INTO `playground__commands` (`pid`, `sid`, `command`) VALUES (:pid, :sid, :command)")->execute([
				':pid' => $pid,
				':sid' => $sid,
				':command' => $command
			]);
		}

		public static function removeCommand($id) {
			self::ensureSchema();
			return pdo()->prepare("DELETE FROM `playground__commands` WHERE `id`=:id LIMIT 1")->execute([':id' => (int)$id]);
		}

		public static function getCommands($pid) {
			self::ensureSchema();
			$pid = (int)$pid;
			try {
				$sth = pdo()->query("SELECT pc.*, s.`name` AS server_name, s.`ip`, s.`port` FROM `playground__commands` pc LEFT JOIN `servers` s ON s.`id`=pc.`sid` WHERE pc.`pid`='" . $pid . "' ORDER BY pc.`id` DESC");
			} catch (Throwable $e) {
				return '<tr><td colspan="4"><center>Команды временно недоступны</center></td></tr>';
			}

			if(!$sth || !$sth->rowCount()) {
				return '<tr><td colspan="4"><center>RCON-команд нет</center></td></tr>';
			}

			$html = '';
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				$server = !empty($row->server_name) ? $row->server_name : ($row->ip . ':' . $row->port);
				$html .= '<tr>';
				$html .= '<td>' . (int)$row->id . '</td>';
				$html .= '<td>' . htmlspecialchars($server, ENT_QUOTES, 'UTF-8') . '</td>';
				$html .= '<td><code>' . htmlspecialchars($row->command, ENT_QUOTES, 'UTF-8') . '</code></td>';
				$html .= '<td><button class="btn btn-xs btn-danger" onclick="removeCommand(' . (int)$row->id . ', ' . $pid . ')">Удалить</button></td>';
				$html .= '</tr>';
			}
			return $html;
		}

		private static function replaceCommandTags($command, $data = []) {
			$replacements = [];
			foreach((array)$data as $key => $value) {
				$replacements['{' . $key . '}'] = (string)$value;
			}

			try {
				$user = user();
				$replacements['{login}'] = isset($user->login) ? (string)$user->login : '';
				$replacements['{nick}'] = isset($user->nick) ? (string)$user->nick : '';
				$replacements['{email}'] = isset($user->email) ? (string)$user->email : '';
				if(!isset($replacements['{steamid}']) && !empty($user->steam_id)) {
					$replacements['{steamid}'] = (string)$user->steam_id;
				}
			} catch (Throwable $e) {
				// user() может быть недоступен в отдельных ajax-контекстах.
			}

			return strtr((string)$command, $replacements);
		}

		public static function SendRcon($pid, $data = []) {
			self::ensureSchema();
			$pid = (int)$pid;
			try {
				$sth = pdo()->query("SELECT pc.*, s.* FROM `playground__commands` pc LEFT JOIN `servers` s ON s.`id`=pc.`sid` WHERE pc.`pid`='" . $pid . "' ORDER BY pc.`id` ASC");
			} catch (Throwable $e) {
				self::addLogs('SendRcon db error pid=' . $pid . ': ' . $e->getMessage());
				return false;
			}

			if(!$sth || !$sth->rowCount()) {
				return true;
			}

			$ok = true;
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				$command = self::replaceCommandTags($row->command, $data);
				self::addLogs('SendRcon command: server=' . (int)$row->sid . ' pid=' . $pid . ' cmd=' . $command);

				try {
					if(empty($row->id) || (string)$row->rcon !== '1' || empty($row->rcon_password) || $row->rcon_password === '0') {
						self::addLogs('SendRcon skipped: RCON disabled or empty password for server=' . (int)$row->sid);
						$ok = false;
						continue;
					}

					if(class_exists('OurSourceQuery')) {
						$query = (new OurSourceQuery())->setServer($row);
						if(!$query->isServerCanWorkWithRcon()) {
							self::addLogs('SendRcon skipped: server cannot work with RCON server=' . (int)$row->sid);
							$ok = false;
							continue;
						}
						$query->checkConnect();
						$query->auth();
						$answer = $query->send($command);
						$query->Disconnect();
						self::addLogs('SendRcon answer: ' . $answer);
					} elseif(function_exists('SourceQuery')) {
						$SourceQuery = SourceQuery();
						$SourceQuery->Connect($row->ip, (int) $row->port, 3, SourceQuery::GOLDSOURCE);
						$SourceQuery->SetRconPassword($row->rcon_password);
						$answer = $SourceQuery->Rcon($command);
						$SourceQuery->Disconnect();
						self::addLogs('SendRcon answer: ' . $answer);
					} else {
						self::addLogs('SendRcon error: RCON class not found');
						$ok = false;
					}
				} catch (Throwable $e) {
					self::addLogs('SendRcon error server=' . (int)$row->sid . ' pid=' . $pid . ': ' . $e->getMessage());
					$ok = false;
				}
			}

			return $ok;
		}

	}
