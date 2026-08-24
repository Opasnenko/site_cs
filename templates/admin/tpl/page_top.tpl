<?php
	$pbQuickItems = pb_admin_quick_access_get(isset($pdo) ? $pdo : null);
	$pbQuickCatalog = pb_admin_quick_access_catalog();
	$pbQuickSiteHost = isset($site_host) ? $site_host : (isset($full_site_host) ? $full_site_host : '/');
	$pbQuickPageTitle = isset($page) && !empty($page->title) ? $page->title : 'Админцентр';

	$pbProfileAdminLogin = function_exists('pb_admin_current_login') ? pb_admin_current_login() : 'Администратор';

	$pbProfileAriaVisible = function_exists('pb_aria_widget_is_visible') ? pb_aria_widget_is_visible(pdo()) : true;
	$pbProfileAccent = function_exists('pb_theme_get_accent') ? pb_theme_get_accent(pdo()) : ['from' => '#4fea9f', 'to' => '#38644f'];

	$pbProfileUid = !empty($_SESSION['admin_uid']) ? (int) $_SESSION['admin_uid'] : 0;
	$pbProfileAvatarSrc = rtrim($pbQuickSiteHost, '/') . '/' . ltrim(
		function_exists('pb_admin_avatar_get')
			? pb_admin_avatar_get(pdo(), $pbProfileUid)
			: 'templates/admin/img/logonew.png',
		'/'
	);
?>
<main>
	<div class="admin-quick-header">
		<div class="admin-quick-header__title">
			<span class="admin-quick-header__eyebrow">Админцентр</span>
			<strong><?=htmlspecialchars($pbQuickPageTitle, ENT_QUOTES, 'UTF-8');?></strong>
		</div>
		<div class="admin-global-search" id="pbAdminGlobalSearch">
			<span class="glyphicon glyphicon-search"></span>
			<input type="search" id="pbAdminGlobalSearchInput" autocomplete="off" placeholder="Поиск по админке">
			<div class="admin-global-search__results" id="pbAdminGlobalSearchResults"></div>
		</div>
		<div class="admin-quick-header__actions" id="pbAdminQuickHeaderItems">
			<?php foreach($pbQuickItems as $pbQuickItem): ?>
				<?php if($pbQuickItem['type'] === 'action'): ?>
					<button type="button" class="admin-quick-chip" data-pb-admin-action="<?=htmlspecialchars($pbQuickItem['value'], ENT_QUOTES, 'UTF-8');?>">
						<span class="glyphicon glyphicon-<?=htmlspecialchars($pbQuickItem['icon'], ENT_QUOTES, 'UTF-8');?>"></span>
						<span><?=htmlspecialchars($pbQuickItem['label'], ENT_QUOTES, 'UTF-8');?></span>
					</button>
				<?php else: ?>
					<a class="admin-quick-chip" href="<?=htmlspecialchars(pb_admin_quick_access_href($pbQuickItem['value'], $pbQuickSiteHost), ENT_QUOTES, 'UTF-8');?>">
						<span class="glyphicon glyphicon-<?=htmlspecialchars($pbQuickItem['icon'], ENT_QUOTES, 'UTF-8');?>"></span>
						<span><?=htmlspecialchars($pbQuickItem['label'], ENT_QUOTES, 'UTF-8');?></span>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<button type="button" class="admin-quick-settings admin-quick-settings--avatar" id="admin-profile-avatar-btn" title="Профиль администратора">
			<img src="<?=htmlspecialchars($pbProfileAvatarSrc, ENT_QUOTES, 'UTF-8');?>" alt="" class="admin-quick-avatar-img" id="admin-quick-avatar-img">
		</button>
		<button type="button" class="admin-quick-settings" data-toggle="modal" data-target="#pbAdminQuickAccessModal" title="Настроить быстрый доступ">
			<span class="glyphicon glyphicon-cog"></span>
		</button>
	</div>

	<div id="pbAdminQuickAccessModal" class="modal fade admin-quick-modal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">×</span></button>
					<h4 class="modal-title">Быстрый доступ в шапке</h4>
				</div>
				<div class="modal-body">
					<div class="admin-quick-modal__hint">Настройки сохраняются в базе данных и применяются для всех администраторов. Действия ограничены безопасным списком.</div>
					<div id="pbAdminQuickAccessRows" class="admin-quick-modal__rows"></div>
					<button type="button" class="btn btn-default" id="pbAdminQuickAddRow">
						<span class="glyphicon glyphicon-plus"></span> Добавить пункт
					</button>
					<div id="pbAdminQuickAccessResult" class="admin-quick-modal__result"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" id="pbAdminQuickSave">
						<span class="glyphicon glyphicon-ok"></span> Сохранить
					</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
				</div>
			</div>
		</div>
	</div>

	<script type="application/json" id="pbAdminQuickAccessData"><?=json_encode($pbQuickItems, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);?></script>
	<script type="application/json" id="pbAdminQuickAccessCatalog"><?=json_encode($pbQuickCatalog, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);?></script>
	<script>
	(function(){
		if (window.PBAdminQuickAccessReady) return;
		window.PBAdminQuickAccessReady = true;

		var presets = getCatalog();
		var quickSiteHost = '<?=htmlspecialchars(rtrim($pbQuickSiteHost, '/'), ENT_QUOTES, 'UTF-8');?>';
		var dangerousActions = {
			dell_all_chat_messages: 'Очистить все сообщения чата?',
			dell_all_bid_tickets: 'Удалить все тикеты и ответы?',
			dell_all_bid_bans: 'Удалить все заявки на разбан и комментарии?',
			dell_all_bid_complains: 'Удалить все жалобы и комментарии?'
		};

		function getCatalog() {
			var node = document.getElementById('pbAdminQuickAccessCatalog');
			if (!node) return {};
			try {
				var data = JSON.parse(node.textContent || '{}');
				return data && typeof data === 'object' ? data : {};
			} catch(e) {
				return {};
			}
		}

		function getInitialItems() {
			var node = document.getElementById('pbAdminQuickAccessData');
			if (!node) return [];
			try {
				var data = JSON.parse(node.textContent || '[]');
				return Array.isArray(data) ? data : [];
			} catch(e) {
				return [];
			}
		}

		function presetKey(item) {
			item = item || {};
			for (var key in presets) {
				if (!Object.prototype.hasOwnProperty.call(presets, key) || key === 'custom') continue;
				if (presets[key].type === item.type && presets[key].value === item.value) return key;
			}
			return 'custom';
		}

		function escapeHtml(value) {
			return String(value || '').replace(/[&<>"']/g, function(ch) {
				return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch];
			});
		}

		function rowHtml(item) {
			item = item || {};
			var selected = presetKey(item);
			var urlDisabled = selected === 'custom' ? '' : ' disabled';
			var customValue = selected === 'custom' ? (item.value || '') : '';
			var label = item.label || (presets[selected] ? presets[selected].title : 'Быстрый доступ');

			var options = '';
			Object.keys(presets).forEach(function(key) {
				var itemTitle = presets[key].title || key;
				var itemGroup = presets[key].group ? presets[key].group + ' — ' : '';
				options += '<option value="' + key + '"' + (key === selected ? ' selected' : '') + '>' + escapeHtml(itemGroup + itemTitle) + '</option>';
			});

			return '<div class="admin-quick-row">' +
				'<input type="text" class="form-control" data-field="label" maxlength="40" value="' + escapeHtml(label) + '" placeholder="Название">' +
				'<select class="form-control" data-field="preset">' + options + '</select>' +
				'<input type="text" class="form-control" data-field="url" maxlength="255" value="' + escapeHtml(customValue) + '" placeholder="admin/users или https://..."' + urlDisabled + '>' +
				'<button type="button" class="btn btn-danger" data-action="remove" title="Удалить"><span class="glyphicon glyphicon-trash"></span></button>' +
			'</div>';
		}

		function renderRows(items) {
			var box = document.getElementById('pbAdminQuickAccessRows');
			if (!box) return;
			box.innerHTML = (items && items.length ? items : []).map(rowHtml).join('');
			if (!box.innerHTML) {
				box.innerHTML = rowHtml({label: 'Сброс кэша', type: 'action', value: 'dell_cache', icon: 'refresh'});
			}
		}

		function normalizeRow(row) {
			var presetEl = row.querySelector('[data-field="preset"]');
			var labelEl = row.querySelector('[data-field="label"]');
			var urlEl = row.querySelector('[data-field="url"]');
			var key = presetEl ? presetEl.value : 'custom';
			var preset = presets[key] || presets.custom;
			var label = labelEl ? labelEl.value.trim() : '';

			if (key !== 'custom' && labelEl && label !== preset.title) {
				labelEl.value = preset.title;
				label = preset.title;
			}
			if (!label) label = preset.title;
			if (key !== 'custom') {
				if (urlEl) {
					urlEl.value = '';
					urlEl.disabled = true;
				}
				return {label: label, type: preset.type, value: preset.value, icon: preset.icon};
			}

			if (urlEl) urlEl.disabled = false;
			return {label: label, type: 'link', value: urlEl ? urlEl.value.trim() : '', icon: 'link'};
		}

		function collectRows() {
			var rows = document.querySelectorAll('#pbAdminQuickAccessRows .admin-quick-row');
			var items = [];
			rows.forEach(function(row) {
				var item = normalizeRow(row);
				if (item.value) items.push(item);
			});
			return items.slice(0, 16);
		}

		function setResult(message, isError) {
			var out = document.getElementById('pbAdminQuickAccessResult');
			if (out) out.innerHTML = '<p class="' + (isError ? 'text-danger' : 'text-success') + '">' + escapeHtml(message) + '</p>';
		}

		function normalizeSearchText(value) {
			return String(value || '').toLowerCase().replace(/ё/g, 'е').trim();
		}

		function quickHref(value) {
			value = String(value || '').trim();
			if (/^https?:\/\//i.test(value) || value.indexOf('/') === 0 || value.indexOf('./') === 0 || value.indexOf('../') === 0) {
				return value;
			}
			return quickSiteHost + '/' + value.replace(/^\/+/, '');
		}

		function searchItems() {
			var items = [];
			Object.keys(presets).forEach(function(key) {
				var item = presets[key] || {};
				if (key === 'custom' || !item.value) return;
				items.push({
					key: key,
					title: item.title || key,
					group: item.group || '',
					type: item.type || 'link',
					value: item.value || '',
					icon: item.icon || 'link',
					text: normalizeSearchText([item.title, item.group, item.value].join(' '))
				});
			});
			return items;
		}

		var adminSearchIndex = searchItems();
		var adminSearchActive = 0;

		function renderAdminSearch(query) {
			var results = document.getElementById('pbAdminGlobalSearchResults');
			if (!results) return [];
			query = normalizeSearchText(query);
			if (!query) {
				results.innerHTML = '';
				results.classList.remove('is-open');
				return [];
			}

			var words = query.split(/\s+/).filter(Boolean);
			var found = adminSearchIndex.filter(function(item) {
				return words.every(function(word) {
					return item.text.indexOf(word) !== -1;
				});
			}).slice(0, 8);

			adminSearchActive = 0;
			results.innerHTML = found.length ? found.map(function(item, index) {
				return '<button type="button" class="admin-global-search__item' + (index === 0 ? ' is-active' : '') + '" data-search-index="' + index + '">' +
					'<span class="glyphicon glyphicon-' + escapeHtml(item.icon) + '"></span>' +
					'<span><b>' + escapeHtml(item.title) + '</b><small>' + escapeHtml(item.group || (item.type === 'action' ? 'Действие' : 'Раздел')) + '</small></span>' +
				'</button>';
			}).join('') : '<div class="admin-global-search__empty">Ничего не найдено</div>';
			results.classList.add('is-open');
			results._items = found;
			return found;
		}

		function setAdminSearchActive(next) {
			var results = document.getElementById('pbAdminGlobalSearchResults');
			var items = results && results._items ? results._items : [];
			if (!results || !items.length) return;
			adminSearchActive = Math.max(0, Math.min(next, items.length - 1));
			var buttons = results.querySelectorAll('[data-search-index]');
			buttons.forEach(function(button, index) {
				button.classList.toggle('is-active', index === adminSearchActive);
			});
		}

		function runAdminSearchItem(item) {
			if (!item) return;
			if (item.type === 'action') {
				if (dangerousActions[item.value] && !confirm(dangerousActions[item.value])) return;
				if (typeof fast_admin_action === 'function') fast_admin_action(item.value);
			} else {
				window.location.href = quickHref(item.value);
			}
		}

		function closeAdminSearch() {
			var results = document.getElementById('pbAdminGlobalSearchResults');
			if (results) {
				results.innerHTML = '';
				results.classList.remove('is-open');
				results._items = [];
			}
		}

		document.addEventListener('input', function(event) {
			if (event.target && event.target.id === 'pbAdminGlobalSearchInput') {
				renderAdminSearch(event.target.value);
			}
		});

		document.addEventListener('keydown', function(event) {
			var input = document.getElementById('pbAdminGlobalSearchInput');
			var results = document.getElementById('pbAdminGlobalSearchResults');
			if ((event.ctrlKey || event.metaKey) && String(event.key).toLowerCase() === 'k') {
				if (input) {
					event.preventDefault();
					input.focus();
					input.select();
				}
				return;
			}
			if (!input || document.activeElement !== input) return;
			var items = results && results._items ? results._items : [];
			if (event.key === 'ArrowDown') {
				event.preventDefault();
				setAdminSearchActive(adminSearchActive + 1);
			} else if (event.key === 'ArrowUp') {
				event.preventDefault();
				setAdminSearchActive(adminSearchActive - 1);
			} else if (event.key === 'Enter') {
				if (items.length) {
					event.preventDefault();
					runAdminSearchItem(items[adminSearchActive] || items[0]);
				}
			} else if (event.key === 'Escape') {
				closeAdminSearch();
				input.blur();
			}
		});

		document.addEventListener('change', function(event) {
			var preset = event.target && event.target.getAttribute('data-field') === 'preset' ? event.target : null;
			if (!preset) return;
			var row = preset.closest('.admin-quick-row');
			if (!row) return;
			var label = row.querySelector('[data-field="label"]');
			var key = preset.value;
			if (label && presets[key]) {
				label.value = presets[key].title;
			}
			normalizeRow(row);
		});

		document.addEventListener('click', function(event) {
			var searchButton = event.target.closest('[data-search-index]');
			if (searchButton) {
				var results = document.getElementById('pbAdminGlobalSearchResults');
				var items = results && results._items ? results._items : [];
				runAdminSearchItem(items[parseInt(searchButton.getAttribute('data-search-index'), 10)]);
				return;
			}

			if (!event.target.closest('#pbAdminGlobalSearch')) {
				closeAdminSearch();
			}

			var actionButton = event.target.closest('[data-pb-admin-action]');
			if (actionButton) {
				event.preventDefault();
				var action = actionButton.getAttribute('data-pb-admin-action');
				if (dangerousActions[action] && !confirm(dangerousActions[action])) {
					return;
				}
				if (typeof fast_admin_action === 'function') {
					fast_admin_action(action);
				}
				return;
			}

			if (event.target.closest('#pbAdminQuickAddRow')) {
				var box = document.getElementById('pbAdminQuickAccessRows');
				if (box && box.querySelectorAll('.admin-quick-row').length < 16) {
					box.insertAdjacentHTML('beforeend', rowHtml({label: 'Быстрый доступ', type: 'link', value: '', icon: 'link'}));
				}
				return;
			}

			if (event.target.closest('[data-action="remove"]')) {
				var row = event.target.closest('.admin-quick-row');
				if (row) row.parentNode.removeChild(row);
				return;
			}

			if (event.target.closest('#pbAdminQuickSave')) {
				var token = document.getElementById('token') ? document.getElementById('token').value : '';
				var items = collectRows();
				if (!items.length) {
					setResult('Добавьте хотя бы один пункт.', true);
					return;
				}
				$.ajax({
					type: 'POST',
					url: '<?=htmlspecialchars(rtrim($pbQuickSiteHost, '/') . '/ajax/admin_quick_access.php', ENT_QUOTES, 'UTF-8');?>',
					dataType: 'json',
					data: {
						phpaction: 1,
						token: token,
						action: 'save',
						items: JSON.stringify(items)
					},
					success: function(result) {
						if (result && String(result.status) === '1') {
							setResult(result.message || 'Сохранено.', false);
							setTimeout(function(){ window.location.reload(); }, 500);
						} else {
							setResult(result && result.message ? result.message : 'Не удалось сохранить.', true);
						}
					},
					error: function() {
						setResult('Ошибка соединения при сохранении.', true);
					}
				});
			}
		});

		renderRows(getInitialItems());
	})();
	</script>

	<div class="breadcrumbs">
		<ul>
			<span class="glyphicon glyphicon-chevron-right"></span>&nbsp {nav}
		</ul>
	</div>

	<div class="profile-drawer-overlay" id="admin-profile-drawer-overlay"></div>
	<div class="profile-drawer admin-profile-drawer" id="admin-profile-drawer">
		<div class="profile-drawer-header">
			<span class="profile-drawer-title">Панель администратора</span>
			<button class="profile-drawer-close" id="admin-profile-drawer-close">&times;</button>
		</div>
		<div class="profile-drawer-body">

			<div class="profile-drawer-user">
				<button type="button" class="admin-profile-avatar admin-profile-avatar--editable" id="admin-profile-avatar-edit" title="Сменить аватар">
					<img src="<?=htmlspecialchars($pbProfileAvatarSrc, ENT_QUOTES, 'UTF-8');?>" alt="" id="admin-profile-avatar-img">
					<span class="admin-profile-avatar__overlay"><span class="glyphicon glyphicon-camera"></span></span>
				</button>
				<div class="profile-drawer-user-info">
					<span class="profile-drawer-login" id="admin-profile-login"><?=htmlspecialchars($pbProfileAdminLogin, ENT_QUOTES, 'UTF-8');?></span>
					<span class="profile-drawer-group" id="admin-profile-greeting">Админцентр PBGcms</span>
				</div>
				<input type="file" id="admin-profile-avatar-file" class="admin-profile-avatar-file" accept="image/png,image/jpeg,image/gif,image/webp">
			</div>
			<div id="admin-avatar-result" class="dc-result"></div>

			<div class="admin-profile-section">
				<div class="admin-profile-section__head">
					<span class="admin-profile-section__icon"><span class="glyphicon glyphicon-adjust"></span></span>
					<div>
						<div class="admin-profile-section__title">Тема оформления</div>
						<div class="admin-profile-section__hint">Применяется сразу для всех администраторов</div>
					</div>
				</div>
				<div class="admin-theme-seg" id="admin_theme_switch" data-value="<?=htmlspecialchars($pbAdminTheme, ENT_QUOTES, 'UTF-8');?>">
					<span class="admin-theme-seg__glider"></span>
					<button type="button" class="admin-theme-seg__opt" data-theme="light">
						<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><circle cx="12" cy="12" r="4.4" fill="currentColor"/><g stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><line x1="12" y1="2.6" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="21.4"/><line x1="2.6" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="21.4" y2="12"/><line x1="5.6" y1="5.6" x2="7.3" y2="7.3"/><line x1="16.7" y1="16.7" x2="18.4" y2="18.4"/><line x1="5.6" y1="18.4" x2="7.3" y2="16.7"/><line x1="16.7" y1="7.3" x2="18.4" y2="5.6"/></g></svg>
						<span>Светлая</span>
					</button>
					<button type="button" class="admin-theme-seg__opt" data-theme="dark">
						<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path d="M20.5 14.6A8.6 8.6 0 0 1 9.4 3.5a8.7 8.7 0 1 0 11.1 11.1Z" fill="currentColor"/></svg>
						<span>Тёмная</span>
					</button>
				</div>
				<div id="admin-theme-result" class="dc-result"></div>
			</div>

			<div class="admin-profile-section">
				<div class="admin-profile-section__head">
					<span class="admin-profile-section__icon"><span class="glyphicon glyphicon-comment"></span></span>
					<div>
						<div class="admin-profile-section__title">ARIA AI</div>
						<div class="admin-profile-section__hint">Плавающий помощник в правом нижнем углу</div>
					</div>
				</div>
				<label class="profile-drawer-perf-row" for="admin_aria_visible_toggle">
					<span class="profile-drawer-perf-icon"><span class="glyphicon glyphicon-comment"></span></span>
					<span class="profile-drawer-perf-info">
						<span class="profile-drawer-perf-title">Показывать ARIA</span>
						<span class="profile-drawer-perf-sub" id="admin-aria-status-sub">Виджет виден на всех страницах</span>
					</span>
					<span class="profile-drawer-perf-switch">
						<input type="checkbox" id="admin_aria_visible_toggle"<?php if($pbProfileAriaVisible) { ?> checked<?php } ?>>
						<span class="profile-drawer-perf-switch__track"><span class="profile-drawer-perf-switch__thumb"></span></span>
					</span>
				</label>
			</div>

			<div class="admin-profile-section">
				<div class="admin-profile-section__head">
					<span class="admin-profile-section__icon"><span class="glyphicon glyphicon-tint"></span></span>
					<div>
						<div class="admin-profile-section__title">Акцент сайта</div>
						<div class="admin-profile-section__hint">Цвет меняется в реальном времени для всех посетителей</div>
					</div>
				</div>

				<div class="admin-palette-preview" id="admin-palette-preview" style="background: radial-gradient(100% 100% at 50% 0, <?=htmlspecialchars($pbProfileAccent['from'], ENT_QUOTES, 'UTF-8');?> 0, <?=htmlspecialchars($pbProfileAccent['to'], ENT_QUOTES, 'UTF-8');?> 100%);"></div>

				<div class="admin-palette-grid" id="admin-palette-grid">
					<button type="button" class="admin-palette-swatch" data-from="#4fea9f" data-to="#38644f" title="Изумрудный (по умолчанию)"><span class="admin-palette-swatch__chip" style="background-image: radial-gradient(100% 100% at 50% 0%, #4fea9f 0%, #38644f 100%);"></span><span class="admin-palette-swatch__name">Изумруд</span></button>
					<button type="button" class="admin-palette-swatch" data-from="#3ddc97" data-to="#1c5e46" title="Мятный"><span class="admin-palette-swatch__chip" style="background-image: radial-gradient(100% 100% at 50% 0%, #3ddc97 0%, #1c5e46 100%);"></span><span class="admin-palette-swatch__name">Мята</span></button>
					<button type="button" class="admin-palette-swatch" data-from="#2be3b3" data-to="#0a4a4a" title="Бирюзовый"><span class="admin-palette-swatch__chip" style="background-image: radial-gradient(100% 100% at 50% 0%, #2be3b3 0%, #0a4a4a 100%);"></span><span class="admin-palette-swatch__name">Бирюза</span></button>
					<button type="button" class="admin-palette-swatch" data-from="#5aa9e6" data-to="#12395c" title="Синий океан"><span class="admin-palette-swatch__chip" style="background-image: radial-gradient(100% 100% at 50% 0%, #5aa9e6 0%, #12395c 100%);"></span><span class="admin-palette-swatch__name">Океан</span></button>
					<button type="button" class="admin-palette-swatch" data-from="#a78bfa" data-to="#3b2a6b" title="Фиолетовый"><span class="admin-palette-swatch__chip" style="background-image: radial-gradient(100% 100% at 50% 0%, #a78bfa 0%, #3b2a6b 100%);"></span><span class="admin-palette-swatch__name">Аметист</span></button>
					<button type="button" class="admin-palette-swatch" data-from="#f472b6" data-to="#6d2444" title="Розовый"><span class="admin-palette-swatch__chip" style="background-image: radial-gradient(100% 100% at 50% 0%, #f472b6 0%, #6d2444 100%);"></span><span class="admin-palette-swatch__name">Фламинго</span></button>
					<button type="button" class="admin-palette-swatch" data-from="#f2b54c" data-to="#6b4310" title="Янтарный"><span class="admin-palette-swatch__chip" style="background-image: radial-gradient(100% 100% at 50% 0%, #f2b54c 0%, #6b4310 100%);"></span><span class="admin-palette-swatch__name">Янтарь</span></button>
					<button type="button" class="admin-palette-swatch" data-from="#fb7185" data-to="#6b1d28" title="Красный"><span class="admin-palette-swatch__chip" style="background-image: radial-gradient(100% 100% at 50% 0%, #fb7185 0%, #6b1d28 100%);"></span><span class="admin-palette-swatch__name">Коралл</span></button>
					<button type="button" class="admin-palette-swatch" data-from="#22d3ee" data-to="#0b4a58" title="Голубой"><span class="admin-palette-swatch__chip" style="background-image: radial-gradient(100% 100% at 50% 0%, #22d3ee 0%, #0b4a58 100%);"></span><span class="admin-palette-swatch__name">Циан</span></button>
				</div>

				<div class="admin-palette-custom">
					<label>
						<span>Начальный тон</span>
						<input type="color" id="admin_palette_from" value="<?=htmlspecialchars($pbProfileAccent['from'], ENT_QUOTES, 'UTF-8');?>">
					</label>
					<label>
						<span>Конечный тон</span>
						<input type="color" id="admin_palette_to" value="<?=htmlspecialchars($pbProfileAccent['to'], ENT_QUOTES, 'UTF-8');?>">
					</label>
				</div>

				<div class="dc-version-actions" style="margin-top:12px;">
					<button type="button" class="btn btn-primary" onclick="pbAdminPaletteSave();" style="border-radius:8px;font-weight:700;">
						<span class="glyphicon glyphicon-ok"></span> Сохранить для всех
					</button>
					<button type="button" class="btn btn-default" onclick="pbAdminPaletteReset();" style="border-radius:8px;font-weight:700;">
						<span class="glyphicon glyphicon-refresh"></span> Сбросить
					</button>
				</div>
				<div id="admin-palette-result" class="dc-result"></div>
			</div>

			<div class="admin-profile-section">
				<div class="admin-profile-section__head">
					<span class="admin-profile-section__icon"><span class="glyphicon glyphicon-bell"></span></span>
					<div>
						<div class="admin-profile-section__title">Звук уведомлений</div>
						<div class="admin-profile-section__hint">Звуковое сопровождение всплывающих тостов</div>
					</div>
				</div>
				<div class="profile-drawer-perf-row-wrap">
					<label class="profile-drawer-perf-row" for="admin_toast_sound_enabled_toggle">
						<span class="profile-drawer-perf-icon"><span class="glyphicon glyphicon-volume-up"></span></span>
						<span class="profile-drawer-perf-info">
							<span class="profile-drawer-perf-title">Звуковые оповещения</span>
							<span class="profile-drawer-perf-sub">Проигрывать звук при показе тоста</span>
						</span>
						<span class="profile-drawer-perf-switch">
							<input type="checkbox" id="admin_toast_sound_enabled_toggle">
							<span class="profile-drawer-perf-switch__track"><span class="profile-drawer-perf-switch__thumb"></span></span>
						</span>
					</label>
				</div>

				<div id="admin_toast_sound_pick_wrap">
					<div class="toast-sound-grid">
						<div class="toast-sound-row">
							<span class="toast-sound-row-label"><i class="glyphicon glyphicon-ok-circle" style="color:#22c55e;"></i>Успешно</span>
							<select class="toast-sound-select" id="admin_toast_sound_select_success"><option value="">Без звука</option></select>
						</div>
						<div class="toast-sound-row">
							<span class="toast-sound-row-label"><i class="glyphicon glyphicon-remove-circle" style="color:#ef4444;"></i>Ошибка</span>
							<select class="toast-sound-select" id="admin_toast_sound_select_error"><option value="">Без звука</option></select>
						</div>
						<div class="toast-sound-row">
							<span class="toast-sound-row-label"><i class="glyphicon glyphicon-warning-sign" style="color:#f59e0b;"></i>Внимание</span>
							<select class="toast-sound-select" id="admin_toast_sound_select_warning"><option value="">Без звука</option></select>
						</div>
						<div class="toast-sound-row">
							<span class="toast-sound-row-label"><i class="glyphicon glyphicon-info-sign" style="color:#3b82f6;"></i>Информация</span>
							<select class="toast-sound-select" id="admin_toast_sound_select_info"><option value="">Без звука</option></select>
						</div>
					</div>
				</div>
			</div>

			<div class="admin-profile-section">
				<div class="admin-profile-section__head">
					<span class="admin-profile-section__icon"><span class="glyphicon glyphicon-move"></span></span>
					<div>
						<div class="admin-profile-section__title">Расположение уведомлений</div>
						<div class="admin-profile-section__hint">Где показывать всплывающие окна</div>
					</div>
				</div>
				<div class="toast-pos-grid" id="admin_toast_pos_grid">
					<div class="toast-pos-card" data-toast-pos="top-left">
						<div class="toast-pos-preview"><span class="toast-pos-dot toast-pos-dot--top-left"></span></div>
						<span class="toast-pos-card-label">Слева вверху</span>
					</div>
					<div class="toast-pos-card" data-toast-pos="top-center">
						<div class="toast-pos-preview"><span class="toast-pos-dot toast-pos-dot--top-center"></span></div>
						<span class="toast-pos-card-label">По центру вверху</span>
					</div>
					<div class="toast-pos-card" data-toast-pos="top-right">
						<div class="toast-pos-preview"><span class="toast-pos-dot toast-pos-dot--top-right"></span></div>
						<span class="toast-pos-card-label">Справа вверху</span>
					</div>
					<div class="toast-pos-card" data-toast-pos="bottom-left">
						<div class="toast-pos-preview"><span class="toast-pos-dot toast-pos-dot--bottom-left"></span></div>
						<span class="toast-pos-card-label">Слева внизу</span>
					</div>
					<div class="toast-pos-card" data-toast-pos="bottom-center">
						<div class="toast-pos-preview"><span class="toast-pos-dot toast-pos-dot--bottom-center"></span></div>
						<span class="toast-pos-card-label">По центру внизу</span>
					</div>
					<div class="toast-pos-card" data-toast-pos="bottom-right">
						<div class="toast-pos-preview"><span class="toast-pos-dot toast-pos-dot--bottom-right"></span></div>
						<span class="toast-pos-card-label">Справа внизу</span>
					</div>
				</div>
				<button type="button" class="toast-pos-test-btn" onclick="pbAdminToastPositionTest();">
					<span class="glyphicon glyphicon-bell"></span> Показать тестовое уведомление
				</button>
			</div>

		</div>
	</div>

	<script>
	(function(){
		if (window.PBAdminProfileDrawerReady) { return; }
		window.PBAdminProfileDrawerReady = true;

		var drawer = document.getElementById('admin-profile-drawer');
		var overlay = document.getElementById('admin-profile-drawer-overlay');
		var avatarBtn = document.getElementById('admin-profile-avatar-btn');
		var closeBtn = document.getElementById('admin-profile-drawer-close');

		if (!drawer || !overlay || !avatarBtn) { return; }

		function openDrawer() {
			drawer.classList.add('is-open');
			overlay.classList.add('is-open');
			document.body.classList.add('profile-drawer-active');
			pbAdminPaletteSyncActiveSwatch();
			pbAdminToastSoundSyncUI();
			pbAdminToastPositionSyncUI();
		}
		function closeDrawer() {
			drawer.classList.remove('is-open');
			overlay.classList.remove('is-open');
			document.body.classList.remove('profile-drawer-active');
		}
		avatarBtn.addEventListener('click', function(e) {
			e.stopPropagation();
			drawer.classList.contains('is-open') ? closeDrawer() : openDrawer();
		});
		if (closeBtn) { closeBtn.addEventListener('click', closeDrawer); }
		if (overlay) { overlay.addEventListener('click', closeDrawer); }
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' && drawer.classList.contains('is-open')) { closeDrawer(); }
		});

		/* ---- Тема оформления ---- */
		var themeSwitch = document.getElementById('admin_theme_switch');
		if (themeSwitch) {
			themeSwitch.addEventListener('click', function(e) {
				var opt = e.target.closest ? e.target.closest('.admin-theme-seg__opt') : null;
				if (!opt) { return; }
				var theme = opt.getAttribute('data-theme');
				if (!theme || theme === themeSwitch.getAttribute('data-value')) { return; }

				themeSwitch.setAttribute('data-value', theme);
				document.documentElement.style.backgroundColor = theme === 'dark' ? '#0a1310' : '#f5f5f5';
				document.body.setAttribute('data-admin-theme', theme);

				adminAjax({ pb_admin_theme_set: 1, theme: theme }, function(result) {
					var out = document.getElementById('admin-theme-result');
					var ok = result && result.status == '1';
					if (out) {
						out.innerHTML = '<p class="' + (ok ? 'text-success' : 'text-danger') + ' m-0">' + ((result && result.data) || (ok ? 'Готово' : 'Ошибка сохранения')) + '</p>';
					}
					if (typeof show_ok === 'function' && ok) { setTimeout(show_ok, 150); }
					if (typeof show_error === 'function' && !ok) { setTimeout(show_error, 150); }
				});
			});
		}

		function adminAjax(data, done) {
			data.phpaction = 1;
			data.token = document.getElementById('token') ? document.getElementById('token').value : '';
			$.ajax({
				type: 'POST',
				url: '<?=htmlspecialchars(rtrim($pbQuickSiteHost, '/') . '/ajax/actions_panel.php', ENT_QUOTES, 'UTF-8');?>',
				data: data,
				dataType: 'json',
				success: done,
				error: function(){ done(null); }
			});
		}

		/* ---- Приветствие ---- */
		var greetEl = document.getElementById('admin-profile-greeting');
		if (greetEl) {
			var updateGreeting = function() {
				var d = new Date();
				var h = d.getHours();
				var g = h >= 5 && h < 12 ? 'Доброе утро' :
						h >= 12 && h < 17 ? 'Добрый день' :
						h >= 17 && h < 23 ? 'Добрый вечер' : 'Доброй ночи';
				var mm = String(d.getMinutes()).padStart(2, '0');
				greetEl.textContent = g + ' · ' + String(h).padStart(2, '0') + ':' + mm;
			};
			updateGreeting();
			setInterval(updateGreeting, 30000);
		}

		/* ---- Смена аватара ---- */
		var avatarEditBtn = document.getElementById('admin-profile-avatar-edit');
		var avatarFile = document.getElementById('admin-profile-avatar-file');
		if (avatarEditBtn && avatarFile) {
			avatarEditBtn.addEventListener('click', function() { avatarFile.click(); });
			avatarFile.addEventListener('change', function() {
				if (!avatarFile.files || !avatarFile.files.length) { return; }
				var out = document.getElementById('admin-avatar-result');
				var fd = new FormData();
				fd.append('phpaction', '1');
				fd.append('pb_admin_avatar_set', '1');
				fd.append('admin_avatar', avatarFile.files[0]);
				fd.append('token', document.getElementById('token') ? document.getElementById('token').value : '');
				if (out) { out.innerHTML = '<p class="m-0">Загрузка…</p>'; }
				$.ajax({
					type: 'POST',
					url: '<?=htmlspecialchars(rtrim($pbQuickSiteHost, '/') . '/ajax/actions_panel.php', ENT_QUOTES, 'UTF-8');?>',
					data: fd,
					processData: false,
					contentType: false,
					dataType: 'json',
					success: function(result) {
						var ok = result && String(result.status) === '1';
						if (out) {
							out.innerHTML = '<p class="' + (ok ? 'text-success' : 'text-danger') + ' m-0">' +
								((result && result.data) || (ok ? 'Готово' : 'Не удалось обновить аватар')) + '</p>';
						}
						if (ok && result.src) {
							var bust = result.src + '?t=' + Date.now();
							['admin-profile-avatar-img', 'admin-quick-avatar-img'].forEach(function(id) {
								var img = document.getElementById(id);
								if (img) { img.src = bust; }
							});
							if (typeof show_ok === 'function') { setTimeout(show_ok, 150); }
						} else if (typeof show_error === 'function') {
							setTimeout(show_error, 150);
						}
					},
					error: function() {
						if (out) { out.innerHTML = '<p class="text-danger m-0">Ошибка соединения</p>'; }
						if (typeof show_error === 'function') { setTimeout(show_error, 150); }
					}
				});
				avatarFile.value = '';
			});
		}

		/* ---- ARIA toggle ---- */
		var ariaToggle = document.getElementById('admin_aria_visible_toggle');
		if (ariaToggle) {
			ariaToggle.addEventListener('change', function() {
				var visible = ariaToggle.checked;
				var widget = document.getElementById('pb-aria-widget');
				if (widget) { widget.style.display = visible ? '' : 'none'; }
				var sub = document.getElementById('admin-aria-status-sub');
				if (sub) { sub.textContent = visible ? 'Виджет виден на всех страницах' : 'Виджет скрыт для всех администраторов'; }

				adminAjax({ pb_aria_set_visible: 1, visible: visible ? 1 : 0 }, function(result) {
					if (!result || result.status != '1') {
						if (typeof show_error === 'function') { setTimeout(show_error, 150); }
					}
				});
			});
		}

		/* ---- Палитра ---- */
		var paletteFrom = document.getElementById('admin_palette_from');
		var paletteTo = document.getElementById('admin_palette_to');
		var palettePreview = document.getElementById('admin-palette-preview');
		var paletteGrid = document.getElementById('admin-palette-grid');

		function currentGradient(from, to) {
			return 'radial-gradient(100% 100% at 50% 0, ' + from + ' 0, ' + to + ' 100%)';
		}

		function hexToRgb(hex) {
			hex = String(hex || '').replace('#', '');
			if (hex.length !== 6) { return '47,158,107'; }
			var r = parseInt(hex.substr(0, 2), 16);
			var g = parseInt(hex.substr(2, 2), 16);
			var b = parseInt(hex.substr(4, 2), 16);
			return r + ',' + g + ',' + b;
		}

		window.pbAdminPaletteApplyLive = function(from, to) {
			var root = document.body.style;
			var toRgb = hexToRgb(to);

			root.setProperty('--GLOBAL', currentGradient(from, to));
			root.setProperty('--pbc-accent', to);
			root.setProperty('--pbc-accent-strong', from);
			root.setProperty('--pbc-accent-soft', 'rgba(' + toRgb + ',.14)');
			root.setProperty('--pbc-accent-soft-2', 'rgba(' + toRgb + ',.24)');
			root.setProperty('--uix-accent', to);
			root.setProperty('--pb-toast-accent', from);
			root.setProperty('--pb-toast-accent-2', to);

			if (palettePreview) { palettePreview.style.background = currentGradient(from, to); }
		};

		window.pbAdminPaletteSyncActiveSwatch = function() {
			if (!paletteGrid || !paletteFrom || !paletteTo) { return; }
			var from = paletteFrom.value.toLowerCase();
			var to = paletteTo.value.toLowerCase();
			paletteGrid.querySelectorAll('.admin-palette-swatch').forEach(function(sw) {
				var match = sw.getAttribute('data-from').toLowerCase() === from && sw.getAttribute('data-to').toLowerCase() === to;
				sw.classList.toggle('is-active', match);
			});
		};

		if (paletteGrid) {
			paletteGrid.addEventListener('click', function(e) {
				var sw = e.target.closest ? e.target.closest('.admin-palette-swatch') : null;
				if (!sw) { return; }
				var from = sw.getAttribute('data-from');
				var to = sw.getAttribute('data-to');
				if (paletteFrom) { paletteFrom.value = from; }
				if (paletteTo) { paletteTo.value = to; }
				pbAdminPaletteApplyLive(from, to);
				pbAdminPaletteSyncActiveSwatch();
			});
		}

		if (paletteFrom) {
			paletteFrom.addEventListener('input', function() {
				pbAdminPaletteApplyLive(paletteFrom.value, paletteTo.value);
				pbAdminPaletteSyncActiveSwatch();
			});
		}
		if (paletteTo) {
			paletteTo.addEventListener('input', function() {
				pbAdminPaletteApplyLive(paletteFrom.value, paletteTo.value);
				pbAdminPaletteSyncActiveSwatch();
			});
		}

		window.pbAdminPaletteSave = function() {
			if (!paletteFrom || !paletteTo) { return; }
			var result = document.getElementById('admin-palette-result');
			adminAjax({ pb_theme_set_accent: 1, from_color: paletteFrom.value, to_color: paletteTo.value }, function(res) {
				var ok = res && res.status == '1';
				if (result) {
					result.innerHTML = '<p class="' + (ok ? 'text-success' : 'text-danger') + ' m-0">' + ((res && res.data) || (ok ? 'Готово' : 'Ошибка сохранения')) + '</p>';
				}
				if (typeof show_ok === 'function' && ok) { setTimeout(show_ok, 150); }
				if (typeof show_error === 'function' && !ok) { setTimeout(show_error, 150); }
			});
		};

		window.pbAdminPaletteReset = function() {
			if (!paletteFrom || !paletteTo) { return; }
			paletteFrom.value = '#4fea9f';
			paletteTo.value = '#38644f';
			pbAdminPaletteApplyLive('#4fea9f', '#38644f');
			pbAdminPaletteSyncActiveSwatch();
		};

		/* ---- Звук тостов ---- */
		window.pbAdminToastSoundSyncUI = function() {
			if (typeof pbToastSoundGetPrefs !== 'function') { return; }
			var prefs = pbToastSoundGetPrefs();

			var enabledToggle = document.getElementById('admin_toast_sound_enabled_toggle');
			if (enabledToggle) { enabledToggle.checked = !!prefs.enabled; }

			var wrap = document.getElementById('admin_toast_sound_pick_wrap');
			if (wrap) { wrap.style.opacity = prefs.enabled ? '1' : '.45'; }

			['success', 'error', 'warning', 'info'].forEach(function(type) {
				var sel = document.getElementById('admin_toast_sound_select_' + type);
				if (!sel) { return; }
				sel.innerHTML = '<option value="">Без звука</option>';
				var list = (typeof pbToastSoundsForType === 'function') ? pbToastSoundsForType(type) : [];
				list.forEach(function(snd) {
					var opt = document.createElement('option');
					opt.value = snd.file;
					opt.textContent = snd.name || snd.file;
					sel.appendChild(opt);
				});
				sel.value = prefs.sounds[type] || '';
			});
		};

		var ariaSoundToggle = document.getElementById('admin_toast_sound_enabled_toggle');
		if (ariaSoundToggle) {
			ariaSoundToggle.addEventListener('change', function() {
				if (typeof pbToastSoundSetEnabled === 'function') { pbToastSoundSetEnabled(ariaSoundToggle.checked); }
				pbAdminToastSoundSyncUI();
			});
		}

		['success', 'error', 'warning', 'info'].forEach(function(type) {
			var sel = document.getElementById('admin_toast_sound_select_' + type);
			if (!sel) { return; }
			sel.addEventListener('change', function() {
				if (typeof pbToastSoundSet === 'function') { pbToastSoundSet(type, sel.value); }
				if (typeof pbToastSoundPreview === 'function') { pbToastSoundPreview(type, sel.value); }
			});
		});

		if (typeof pbToastSoundOnManifest === 'function') {
			pbToastSoundOnManifest(function(){ pbAdminToastSoundSyncUI(); });
		}

		/* ---- Позиция тостов ---- */
		window.pbAdminToastPositionSyncUI = function() {
			if (typeof pbToastSoundGetPrefs !== 'function') { return; }
			var prefs = pbToastSoundGetPrefs();
			var grid = document.getElementById('admin_toast_pos_grid');
			if (!grid) { return; }
			grid.querySelectorAll('.toast-pos-card').forEach(function(card) {
				card.classList.toggle('toast-pos-card--active', card.getAttribute('data-toast-pos') === prefs.position);
			});
		};

		var posGrid = document.getElementById('admin_toast_pos_grid');
		if (posGrid) {
			posGrid.addEventListener('click', function(e) {
				var card = e.target.closest ? e.target.closest('.toast-pos-card') : null;
				if (!card) { return; }
				if (typeof pbToastPositionSet === 'function') { pbToastPositionSet(card.getAttribute('data-toast-pos')); }
				pbAdminToastPositionSyncUI();
			});
		}

		window.pbAdminToastPositionTest = function() {
			if (typeof show_ok === 'function') { show_ok(); }
			setTimeout(function() {
				if (typeof window.push === 'function') { window.push('Так будут выглядеть уведомления', 'info'); }
			}, 250);
		};
	})();
	</script>
