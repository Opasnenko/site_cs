<script>document.body.classList.add("has-dc-sidebar");</script>
<?php if(function_exists("pb_ensure_admin_utility_pages")) { pb_ensure_admin_utility_pages(pdo()); } ?>
<?php
$xfIsMasterSession = function_exists('pb_admin_master_is_active_session') && pb_admin_master_is_active_session();
$xf_admin_login = function_exists('pb_admin_current_login') ? pb_admin_current_login() : 'admin';

$xfAdminSessionUntil = 0;
if (function_exists('pb_admin_current_session_until')) {
    try {
        $xfAdminSessionUntil = pb_admin_current_session_until(function_exists('pdo') ? pdo() : (isset($pdo) ? $pdo : null));
    } catch (Throwable $e) {
        $xfAdminSessionUntil = 0;
    } catch (Exception $e) {
        $xfAdminSessionUntil = 0;
    }
}
$xfAdminSessionLeft = $xfAdminSessionUntil > time() ? ($xfAdminSessionUntil - time()) : 0;
?>

<aside class="hidden-xs dc-sidebar xf-admin-sidebar admin-sidebar-shell">
    <div class="xf-admin-brand">
        <a class="xf-admin-brand__home" href="{site_host}admin" title="Админцентр">
            <img src="{site_host}templates/admin/img/logonew.png?v={cache}" alt="PBGcms" class="xf-admin-brand__logo">
        </a>
        <a class="xf-admin-brand__title" href="{site_host}admin">PBGcms</a>
        <?php if($xfIsMasterSession): ?>
        <span class="xf-admin-brand__admin" style="color:#fca5a5;font-weight:800;">Временный доступ</span>
        <?php else: ?>
        <span class="xf-admin-brand__admin">Admin center</span>
        <?php endif; ?>
    </div>

    <div class="xf-admin-scroll">
        <nav class="xf-admin-nav" aria-label="Админ меню">
            <section class="xf-admin-section xf-admin-section--dev" data-xf-section>
                <button class="xf-admin-section__head" type="button">
                    <span class="glyphicon glyphicon-wrench"></span>
                    <span>Инструменты</span>
                    <i class="glyphicon glyphicon-chevron-down"></i>
                </button>
                <div class="xf-admin-section__body">
                    <?php if(function_exists('isPageActive') && isPageActive('admin_dev_center') && pb_admin_master_page_allowed('admin_dev_center')): ?>
                    <a href="{site_host}admin/dev_center"><span class="glyphicon glyphicon-wrench"></span>Центр разработчика</a>
                    <?php endif; ?>
                    <?php if(function_exists('isPageActive') && isPageActive('admin_docs') && pb_admin_master_page_allowed('admin_docs')): ?>
                    <a href="{site_host}admin/docs"><span class="glyphicon glyphicon-book"></span>Документация</a>
                    <?php endif; ?>
                    <?php if(function_exists('isPageActive') && isPageActive('admin_dev_ssh') && pb_admin_master_page_allowed('admin_dev_ssh')): ?>
                    <a href="{site_host}admin/dev_ssh"><span class="glyphicon glyphicon-blackboard"></span>SSH | Терминал</a>
                    <?php endif; ?>
                    <?php if(function_exists('isPageActive') && isPageActive('admin_dev_migration') && pb_admin_master_page_allowed('admin_dev_migration')): ?>
                    <a href="{site_host}admin/dev_migration"><span class="glyphicon glyphicon-transfer"></span>Миграция данных</a>
                    <?php endif; ?>
                    <?php if(function_exists('isPageActive') && isPageActive('admin_dev_terminal_data') && pb_admin_master_page_allowed('admin_dev_terminal_data')): ?>
                    <a href="{site_host}admin/dev_terminal_data"><span class="glyphicon glyphicon-tasks"></span>Терминал | Данные</a>
                    <?php endif; ?>
                    <?php if(function_exists('isPageActive') && isPageActive('admin_geo') && pb_admin_master_page_allowed('admin_geo')): ?>
                    <a href="{site_host}admin/geo"><span class="glyphicon glyphicon-globe"></span>Геопозиция</a>
                    <?php endif; ?>
                    <?php if(function_exists('isPageActive') && isPageActive('admin_seo') && pb_admin_master_page_allowed('admin_seo')): ?>
                    <a href="{site_host}admin/seo"><span class="glyphicon glyphicon-search"></span>Индексация SEO</a>
                    <?php endif; ?>
                    <?php if(!pb_admin_master_is_active_session()): ?>
                    <a href="{site_host}admin/remote_access"><span class="glyphicon glyphicon-transfer"></span>Удалённый доступ</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_library')): ?>
                    <?php if(function_exists('sys') && sys()->license_allows_updates()): ?>
                    <a href="{site_host}admin/library"><span class="glyphicon glyphicon-picture"></span>Библиотека ресурсов</a>
                    <?php else: ?>
                    <a href="{site_host}admin/dev_center" title="Доступно после активации лицензии движка" class="xf-admin-nav__locked"><span class="glyphicon glyphicon-lock"></span>Библиотека ресурсов</a>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="xf-admin-section" data-xf-section>
                <button class="xf-admin-section__head" type="button">
                    <span class="glyphicon glyphicon-equalizer"></span>
                    <span>Настройки</span>
                    <i class="glyphicon glyphicon-chevron-down"></i>
                </button>
                <div class="xf-admin-section__body">
                    <?php if(pb_admin_master_page_allowed('admin_general')): ?>
                    <a href="{site_host}admin/general"><span class="glyphicon glyphicon-cog"></span>Основные</a>
                    <?php endif; ?>
                    <?php if(function_exists('isPageActive') && isPageActive('admin_telegram') && pb_admin_master_page_allowed('admin_telegram')): ?>
                    <a href="{site_host}admin/telegram"><span class="glyphicon glyphicon-send"></span>Telegram</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_widgets')): ?>
                    <a href="{site_host}admin/widgets"><span class="glyphicon glyphicon-th"></span>Виджеты</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_security')): ?>
                    <a href="{site_host}admin/security"><span class="glyphicon glyphicon-lock"></span>Безопасность</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_registration')): ?>
                    <a href="{site_host}admin/registration"><span class="glyphicon glyphicon-pencil"></span>Регистрация</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_email_settings')): ?>
                    <a href="{site_host}admin/email_settings"><span class="glyphicon glyphicon-envelope"></span>Настройка почты</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_logs')): ?>
                    <a href="{site_host}admin/logs"><span class="glyphicon glyphicon-align-justify"></span>Логи | Блокировки</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="xf-admin-section" data-xf-section>
                <button class="xf-admin-section__head" type="button">
                    <span class="glyphicon glyphicon-hdd"></span>
                    <span>Сервера</span>
                    <i class="glyphicon glyphicon-chevron-down"></i>
                </button>
                <div class="xf-admin-section__body">
                    <?php if(pb_admin_master_page_allowed('admin_admins')): ?>
                    <a href="{site_host}admin/admins"><span class="glyphicon glyphicon-queen"></span>Администраторы</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_servers')): ?>
                    <a href="{site_host}admin/servers"><span class="glyphicon glyphicon-hdd"></span>Настройка серверов</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_store')): ?>
                    <a href="{site_host}admin/store"><span class="glyphicon glyphicon-barcode"></span>Настройка услуг</a>
                    <?php endif; ?>
                    <?php if(!pb_admin_master_is_active_session()): ?>
                    <a href="https://pbgame.top/amxx" target="_blank" rel="noopener"><span class="glyphicon glyphicon-cloud-download"></span>AMXX Плагины</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_cs_install')): ?>
                    <a href="{site_host}admin/cs_install"><span class="glyphicon glyphicon-cog"></span>Установщик</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="xf-admin-section" data-xf-section>
                <button class="xf-admin-section__head" type="button">
                    <span class="glyphicon glyphicon-credit-card"></span>
                    <span>Коммерция</span>
                    <i class="glyphicon glyphicon-chevron-down"></i>
                </button>
                <div class="xf-admin-section__body">
                    <?php if(pb_admin_master_page_allowed('admin_payments')): ?>
                    <a href="{site_host}admin/payments"><span class="glyphicon glyphicon-credit-card"></span>Платёжные системы</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_bank')): ?>
                    <a href="{site_host}admin/bank"><span class="glyphicon glyphicon-piggy-bank"></span>Монетизация</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_playground')): ?>
                    <a href="{site_host}admin/playground"><span class="glyphicon glyphicon-shopping-cart"></span>Торговая площадка</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_promocodes')): ?>
                    <a href="{site_host}admin/promocodes"><span class="glyphicon glyphicon-tag"></span>Промокоды</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_premium')): ?>
                    <a href="{site_host}admin/premium"><span class="glyphicon glyphicon-star"></span>PREMIUM подписка</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="xf-admin-section" data-xf-section>
                <button class="xf-admin-section__head" type="button">
                    <span class="glyphicon glyphicon-user"></span>
                    <span>Пользователи</span>
                    <i class="glyphicon glyphicon-chevron-down"></i>
                </button>
                <div class="xf-admin-section__body">
                    <?php if(pb_admin_master_page_allowed('admin_users_groups')): ?>
                    <a href="{site_host}admin/users_groups"><span class="glyphicon glyphicon-fire"></span>Группы пользователей</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_users')): ?>
                    <a href="{site_host}admin/users"><span class="glyphicon glyphicon-user"></span>Настройка пользователей</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_verifications')): ?>
                    <a href="{site_host}admin/verifications"><span class="glyphicon glyphicon-check"></span>Верификация пользователей</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="xf-admin-section" data-xf-section>
                <button class="xf-admin-section__head" type="button">
                    <span class="glyphicon glyphicon-list-alt"></span>
                    <span>Контент</span>
                    <i class="glyphicon glyphicon-chevron-down"></i>
                </button>
                <div class="xf-admin-section__body">
                    <?php if(pb_admin_master_page_allowed('admin_news')): ?>
                    <a href="{site_host}admin/news"><span class="glyphicon glyphicon-folder-close"></span>Настройка новостей</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_forum_settings')): ?>
                    <a href="{site_host}admin/forum_settings"><span class="glyphicon glyphicon-text-background"></span>Настройка форума</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_page_editor')): ?>
                    <a href="{site_host}admin/page_editor"><span class="glyphicon glyphicon-file"></span>Редактор страниц</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="xf-admin-section" data-xf-section>
                <button class="xf-admin-section__head" type="button">
                    <span class="glyphicon glyphicon-leaf"></span>
                    <span>Шаблонизатор</span>
                    <i class="glyphicon glyphicon-chevron-down"></i>
                </button>
                <div class="xf-admin-section__body">
                    <?php if(pb_admin_master_page_allowed('admin_template')): ?>
                    <a href="{site_host}admin/template"><span class="glyphicon glyphicon-leaf"></span>Редактор шаблонов</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_menu_editor')): ?>
                    <a href="{site_host}admin/menu_editor"><span class="glyphicon glyphicon-list"></span>Редактор меню</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_template_settings')): ?>
                    <a href="{site_host}admin/template_settings"><span class="glyphicon glyphicon-cog"></span>Настройки шаблона</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_platform_notifications')): ?>
                    <a href="{site_host}admin/platform_notifications"><span class="glyphicon glyphicon-bell"></span>Уведомления</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="xf-admin-section" data-xf-section>
                <button class="xf-admin-section__head" type="button">
                    <span class="glyphicon glyphicon-cloud-download"></span>
                    <span>Дополнения</span>
                    <i class="glyphicon glyphicon-chevron-down"></i>
                </button>
                <div class="xf-admin-section__body">
                    <?php if(pb_admin_master_page_allowed('admin_modules')): ?>
                    <a href="{site_host}admin/modules"><span class="glyphicon glyphicon-inbox"></span>Модули</a>
                    <?php endif; ?>

                    <div class="xf-admin-nav__label">Настройки модулей</div>
                    <?php if(pb_admin_master_page_allowed('admin_cases')): ?>
                    <a href="{site_host}admin/cases"><span class="glyphicon glyphicon-gift"></span>Кейсы</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_sortition')): ?>
                    <a href="{site_host}admin/sortition"><span class="glyphicon glyphicon-random"></span>Розыгрыш</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_activity_rewards')): ?>
                    <a href="{site_host}admin/activity_rewards"><span class="glyphicon glyphicon-star"></span>Activity Rewards</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_rcon_shop')): ?>
                    <a href="{site_host}admin/rcon_shop"><span class="glyphicon glyphicon-console"></span>RCON-магазин</a>
                    <?php endif; ?>
                    <?php if(pb_admin_master_page_allowed('admin_digital_store')): ?>
                    <a href="{site_host}admin/digital_store"><span class="glyphicon glyphicon-th-large"></span>Цифровой магазин</a>
                    <?php endif; ?>
                </div>
            </section>
        </nav>
    </div>

    <div class="xf-admin-footer">
        <a href="../" title="Вернуться на сайт">
            <span class="glyphicon glyphicon-new-window"></span>
            <span>Сайт</span>
        </a>
        <a href="javascript:void(0);" onclick="pbAdminLogout();return false;" class="xf-admin-footer__danger" title="Выйти из Админцентра">
            <span class="glyphicon glyphicon-log-out"></span>
            <span>Выход</span>
        </a>
        <script>
        /* Выход ТОЛЬКО из Админцентра — сессия сайта не трогается. */
        function pbAdminLogout(){
            var token = (document.getElementById('token') || {}).value || '';
            var done = function(){ window.location.href = '{site_host}admin'; };
            try {
                $.ajax({
                    type: 'POST',
                    url: '{site_host}ajax/actions_panel.php',
                    data: 'phpaction=1&token=' + encodeURIComponent(token) + '&admin_exit=1',
                    complete: done
                });
            } catch(e){ done(); }
        }
        </script>
    </div>
</aside>

<script>
(function(){
    var sidebar = document.querySelector('.xf-admin-sidebar');
    var scrollBox = document.querySelector('.xf-admin-scroll');
    var sections = document.querySelectorAll('[data-xf-section]');
    var links = document.querySelectorAll('.xf-admin-section__body a, .xf-admin-footer a');

    function normalizePath(value) {
        try {
            return new URL(value, window.location.origin).pathname.replace(/\/$/, '');
        } catch(e) {
            return (value || '').replace(/^https?:\/\/[^\/]+/, '').replace(/\/$/, '');
        }
    }

    function setSectionState(section, open) {
        var body = section.querySelector('.xf-admin-section__body');
        var icon = section.querySelector('.xf-admin-section__head i');
        var head = section.querySelector('.xf-admin-section__head');

        if(body) {
            if(body._xfEndHandler) {
                body.removeEventListener('transitionend', body._xfEndHandler);
                body._xfEndHandler = null;
            }

            if(open) {
                body.style.maxHeight = body.scrollHeight + 'px';
                body._xfEndHandler = function(e) {
                    if(e.propertyName !== 'max-height') return;
                    if(section.classList.contains('is-open')) {
                        body.style.maxHeight = 'none';
                    }
                    body.removeEventListener('transitionend', body._xfEndHandler);
                    body._xfEndHandler = null;
                };
                body.addEventListener('transitionend', body._xfEndHandler);
            } else {
                body.style.maxHeight = body.scrollHeight + 'px';
                void body.offsetHeight;
                window.requestAnimationFrame(function(){
                    body.style.maxHeight = '0px';
                });
            }
        }

        section.classList.toggle('is-open', open);
        if(head) {
            head.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        if(icon) {
            icon.className = 'glyphicon glyphicon-chevron-down';
        }
    }

function markActiveItem() {
    var current = window.location.pathname.replace(/\/$/, '');
    var activeSection = null;

    links.forEach(function(link){
        var href = link.getAttribute('href') || '';
        var path = normalizePath(href);
        if(path && path !== '/' && current.indexOf(path) === 0) {
            link.classList.add('is-current');
            activeSection = link.closest('[data-xf-section]') || activeSection;
        }
    });

        if(activeSection) {
            setSectionState(activeSection, true);
            var activeLink = activeSection.querySelector('a.is-current');
            if(activeLink && sidebar) {
                setTimeout(function(){
                    activeLink.scrollIntoView({block: 'nearest'});
                }, 160);
            }
        }
    }

    markActiveItem();

    sections.forEach(function(section){
        var head = section.querySelector('.xf-admin-section__head');
        if(!head) return;
        head.setAttribute('aria-expanded', section.classList.contains('is-open') ? 'true' : 'false');
        head.addEventListener('click', function(){
            var willOpen = !section.classList.contains('is-open');
            if(willOpen) {
                sections.forEach(function(other){
                    if(other !== section && other.classList.contains('is-open')) {
                        setSectionState(other, false);
                    }
                });
            }
            setSectionState(section, willOpen);
        });
    });

    var resizeTimer = null;
    window.addEventListener('resize', function(){
        if(resizeTimer) { clearTimeout(resizeTimer); }
        resizeTimer = setTimeout(function(){
            sections.forEach(function(section){
                var body = section.querySelector('.xf-admin-section__body');
                if(body && section.classList.contains('is-open')) {
                    body.style.maxHeight = 'none';
                }
            });
        }, 120);
    });

    if(scrollBox) {
        ['wheel', 'mousewheel', 'DOMMouseScroll'].forEach(function(eventName){
            scrollBox.addEventListener(eventName, function(event){
                event.stopPropagation();
            }, {passive: true});
        });
    }
})();
</script>
<!-- PBGame CMS ARIA floating assistant -->
<?php $pbAriaWidgetVisible = function_exists('pb_aria_widget_is_visible') ? pb_aria_widget_is_visible(pdo()) : true; ?>
<link rel="stylesheet" href="{site_host}templates/admin/css/aria.css?v=6.2.8">
<div id="pb-aria-widget" class="aria-widget" data-aria-site-host="{site_host}" aria-live="polite"<?php if(!$pbAriaWidgetVisible) { ?> style="display:none;"<?php } ?>>
    <div class="aria-greeting" data-aria-greeting hidden>
        <button type="button" class="aria-greeting-close" data-aria-greeting-close aria-label="Скрыть приветствие">×</button>
        <button type="button" class="aria-greeting-main" data-aria-widget-toggle>
            <span class="aria-greeting-avatar-wrap">
                <img src="{site_host}templates/admin/img/aria-avatar.png" alt="ARIA" class="aria-greeting-avatar">
                <span class="aria-greeting-status"></span>
            </span>
            <span class="aria-greeting-copy">
                <strong>Привет! Я ARIA 👋</strong>
                <span>Чем помочь с PBGame CMS?</span>
            </span>
        </button>
    </div>

    <button type="button" class="aria-fab" data-aria-widget-toggle title="Открыть ARIA" aria-label="Открыть ARIA">
        <img src="{site_host}templates/admin/img/aria-avatar.png" alt="ARIA" class="aria-fab-avatar">
        <span class="aria-fab-status" data-aria-fab-status></span>
    </button>

    <section class="aria-widget-panel" data-aria-widget-panel hidden>
        <header class="aria-widget-head">
            <div class="aria-widget-brand">
                <img src="{site_host}templates/admin/img/aria-avatar.png" alt="ARIA" class="aria-widget-avatar">
                <div class="aria-widget-brand-copy">
                    <strong>ARIA</strong>
                    <span><i></i> Онлайн · <b data-aria-tier>ARIA Mini</b></span>
                </div>
            </div>
            <div class="aria-widget-actions">
                <button type="button" class="aria-mini-icon" data-aria-new title="Новый диалог"><span class="glyphicon glyphicon-plus"></span></button>
                <button type="button" class="aria-mini-icon" data-aria-expand title="Открыть полный чат"><span class="glyphicon glyphicon-resize-full"></span></button>
                <button type="button" class="aria-mini-icon" data-aria-widget-close title="Закрыть"><span class="glyphicon glyphicon-remove"></span></button>
            </div>
        </header>

        <div class="aria-widget-plans" aria-label="Версия ARIA">
            <button type="button" class="aria-widget-plan is-active" data-aria-plan-tab="mini">Mini</button>
            <button type="button" class="aria-widget-plan" data-aria-plan-tab="hard" data-aria-license-open>Hard</button>
            <button type="button" class="aria-widget-plan aria-widget-plan--premium" data-aria-plan-tab="premium" data-aria-license-open>Premium <span aria-hidden="true">♛</span></button>
        </div>
        <div class="aria-service-banner aria-service-banner--widget" data-aria-service-banner hidden></div>

        <div class="aria-widget-messages aria-messages" data-aria-messages>
            <div class="aria-empty aria-empty--widget" data-aria-empty>
                <div class="aria-welcome-message">
                    <img src="{site_host}templates/admin/img/aria-avatar.png" alt="ARIA">
                    <div>
                        <strong>Привет! 👋</strong>
                        <span>Я рядом. Могу проверить CMS, найти ошибки или помочь с настройкой.</span>
                    </div>
                </div>
                <div class="aria-empty-label">Быстрые действия</div>
                <div class="aria-suggestions aria-suggestions--widget">
                    <button type="button" data-aria-suggest="Проверь состояние PBGame CMS и скажи, есть ли проблемы."><span class="glyphicon glyphicon-ok-circle"></span> Проверить CMS</button>
                    <button type="button" data-aria-suggest="Проверь последние ошибки PBGame CMS и объясни, что важно исправить."><span class="glyphicon glyphicon-warning-sign"></span> Найти ошибки</button>
                </div>
            </div>
        </div>

        <div class="aria-widget-compose">
            <div class="aria-error" data-aria-error hidden></div>
            <div class="aria-composer aria-composer--widget">
                <textarea rows="1" maxlength="16000" placeholder="Напишите сообщение…" data-aria-input></textarea>
                <button type="button" class="aria-send" data-aria-send title="Отправить"><span class="glyphicon glyphicon-send"></span></button>
            </div>
            <div class="aria-widget-hint">Enter — отправить · Shift+Enter — новая строка</div>
        </div>

        <div class="aria-license-popover" data-aria-license-modal hidden>
            <div class="aria-license-popover-head"><strong>Лицензия ARIA</strong><button type="button" data-aria-license-close>×</button></div>
            <p>После успешной привязки домена начинается бесплатный 72-часовой Trial ARIA Mini. После Trial активируйте лицензию Mini, Hard или Premium.</p>
            <div class="aria-current-plan"><span>Текущий план</span><strong data-aria-license-tier>ARIA Mini</strong></div>
            <input type="text" class="form-control" maxlength="190" placeholder="Ключ ARIA" data-aria-license-key autocomplete="off">
            <button type="button" class="btn btn-primary" data-aria-license-activate>Активировать</button>
            <div class="aria-license-result" data-aria-license-result hidden></div>
        </div>
    </section>
</div>
<script defer src="{site_host}templates/admin/js/aria.js?v=6.2.8"></script>
<!-- /PBGame CMS ARIA -->

