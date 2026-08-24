<?php
$pbAdminEngineLang = 'ru';
if (function_exists('pb_current_language')) {
    $pbAdminEngineLang = pb_current_language();
} elseif (function_exists('sys')) {
    try {
        $pbAdminSecondary = sys()->secondary();
        if (is_object($pbAdminSecondary) && !empty($pbAdminSecondary->site_lang)) {
            $pbAdminEngineLang = (string)$pbAdminSecondary->site_lang;
        }
    } catch (Throwable $e) {}
}
if (function_exists('pb_normalize_lang')) {
    $pbAdminEngineLang = pb_normalize_lang($pbAdminEngineLang);
} else {
    $pbAdminEngineLang = strtolower(trim((string)$pbAdminEngineLang));
    if ($pbAdminEngineLang === 'uk') $pbAdminEngineLang = 'ua';
    if ($pbAdminEngineLang === 'kk') $pbAdminEngineLang = 'kz';
    if (!in_array($pbAdminEngineLang, ['ru','ua','en','kz','pl'], true)) $pbAdminEngineLang = 'ru';
}
$pbAdminGoogleLangMap = ['ru' => 'ru', 'ua' => 'uk', 'en' => 'en', 'kz' => 'kk', 'pl' => 'pl'];
$pbAdminGoogleLang = $pbAdminGoogleLangMap[$pbAdminEngineLang] ?? 'ru';

$pbAdminTheme = function_exists('pb_admin_theme_get') ? pb_admin_theme_get(function_exists('pdo') ? pdo() : null) : 'light';
?>
<head>
    <meta charset="UTF-8">
    <title>{title}</title>

    <meta name="robots" content="none">
    <meta name="author" content="PBGame CMS">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script>
        window.PB_ADMIN_ENGINE_LANGUAGE = {
            engine: "<?=htmlspecialchars($pbAdminEngineLang, ENT_QUOTES, 'UTF-8');?>",
            google: "<?=htmlspecialchars($pbAdminGoogleLang, ENT_QUOTES, 'UTF-8');?>",
            source: "ru"
        };
        document.documentElement.setAttribute("lang", window.PB_ADMIN_ENGINE_LANGUAGE.google || "ru");
    </script>

    <script>
        try {
            localStorage.setItem('admin_theme', 'css');
            document.documentElement.setAttribute('data-theme', 'css');
        } catch(e) {
            document.documentElement.setAttribute('data-theme', 'css');
        }
    </script>

    <link rel="stylesheet" href="{site_host}templates/admin/css/main.css?v={cache}">
    <link rel="stylesheet" href="{site_host}ajax/addons/toasts/pb-toast.css?v={cache}">
    <link rel="stylesheet" href="{site_host}templates/admin/css/primary.css?v={cache}&pbfix=20260429">
    <link rel="stylesheet" href="{site_host}templates/admin/css/pbg_admin.css?v={cache}&pbdark=20260816">
    <link rel="stylesheet" href="{site_host}templates/admin/css/pbg_profile_drawer.css?v={cache}">

    <style id="pb-theme-accent-vars">:root, body.has-dc-sidebar { {global_accent_vars} }</style>

    <link rel="shortcut icon" href="{site_host}templates/admin/img/logonew.png?v={cache}">
    <link rel="image_src" href="{image}">

    <script src="{site_host}templates/admin/js/jquery.js?v={cache}"></script>
    <script src="{site_host}templates/admin/js/nprogress.js?v={cache}"></script>
    <script src="{site_host}templates/admin/js/secondary.js?v={cache}"></script>
    <script src="{site_host}templates/admin/js/bootstrap.js?v={cache}"></script>
    <script src="{site_host}ajax/performers/functions.min.js?v={cache}"></script>
    <script src="{site_host}ajax/performers/acp.min.js?v={cache}"></script>
    <script src="{site_host}ajax/addons/toasts/pb-toast.js?v={cache}"></script>

    {other}

    <style>
        /* PBGame CMS admin critical layout/button fix 2026-04-29 */
        html[data-theme="css"] body:not([data-admin-theme="dark"]),
        body.theme-css:not([data-admin-theme="dark"]) {
            background: #f5f6f8 !important;
            color: #1f2933;
        }
        body.theme-css[data-admin-theme="dark"] {
            background: #0a1310 !important;
            color: #eaf6ef;
        }
<?php if($pbAdminTheme === 'dark') { ?>
        html {
            background-color: #0a1310 !important;
        }
<?php } ?>
        @media (min-width: 768px) {
            body.has-dc-sidebar {
                padding-left: 260px !important;
                box-sizing: border-box !important;
                min-height: 100vh !important;
            }
            body.has-dc-sidebar .dc-sidebar {
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                bottom: 0 !important;
                width: 260px !important;
                min-width: 260px !important;
                max-width: 260px !important;
                height: 100vh !important;
                z-index: 1000 !important;
                margin: 0 !important;
            }
            body.has-dc-sidebar > .page,
            body.has-dc-sidebar > .breadcrumbs,
            body.has-dc-sidebar > .block,
            body.has-dc-sidebar > .content,
            body.has-dc-sidebar > .container,
            body.has-dc-sidebar > .container-fluid {
                margin-left: 0 !important;
            }
        }
        body.theme-css label.btn,
        body.theme-css .btn,
        body.theme-css .btn2,
        body.theme-css .input-group-btn > .btn,
        body.theme-css .input-group-btn > label.btn,
        body.theme-css .btn-group > .btn,
        body.theme-css .btn-group > label.btn {
            white-space: nowrap !important;
            word-break: keep-all !important;
            overflow-wrap: normal !important;
            text-align: center !important;
        }
        body.theme-css .input-group-btn[data-toggle="buttons"],
        body.theme-css .btn-group[data-toggle="buttons"] {
            white-space: nowrap !important;
            width: auto !important;
            max-width: 100% !important;
        }
        body.theme-css .input-group-btn[data-toggle="buttons"] > label.btn,
        body.theme-css .btn-group[data-toggle="buttons"] > label.btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: auto !important;
            min-width: 96px !important;
            max-width: none !important;
            height: 34px !important;
            padding: 6px 12px !important;
            line-height: 1.2 !important;
            margin-bottom: 0 !important;
        }
        body.theme-css .input-group-btn[data-toggle="buttons"] > label.btn input[type="radio"],
        body.theme-css .btn-group[data-toggle="buttons"] > label.btn input[type="radio"] {
            position: absolute !important;
            opacity: 0 !important;
            pointer-events: none !important;
            width: 1px !important;
            height: 1px !important;
            margin: 0 !important;
        }
        body.theme-css .bs-callout,
        body.theme-css .bs-callout p,
        body.theme-css .bs-callout li,
        body.theme-css .panel-body p,
        body.theme-css .panel-body li {
            word-break: normal !important;
            overflow-wrap: anywhere !important;
        }

        /* PBGame CMS final admin content left alignment */
        @media (min-width:768px){
            body.has-dc-sidebar{padding-left:260px!important;margin-left:0!important;overflow-x:hidden!important;}
            body.has-dc-sidebar .wapper,
            body.has-dc-sidebar .wapper>section{width:100%!important;max-width:none!important;min-width:0!important;margin:0!important;padding:0!important;overflow:visible!important;}
            body.has-dc-sidebar .wapper>section>header{float:none!important;width:100%!important;max-width:none!important;margin:0!important;left:0!important;right:auto!important;}
            body.has-dc-sidebar .wapper>section>main{float:none!important;clear:none!important;width:100%!important;max-width:none!important;min-width:0!important;margin:0!important;padding:0 14px 18px!important;left:0!important;right:auto!important;position:relative!important;overflow:visible!important;box-sizing:border-box!important;}
            body.has-dc-sidebar .wapper>section>main .page{width:100%!important;max-width:none!important;margin:0!important;padding:15px 0!important;box-sizing:border-box!important;}
        }
        @media (max-width:767px){
            body.has-dc-sidebar{padding-left:0!important;}
            body.has-dc-sidebar .wapper>section>main{float:none!important;width:100%!important;padding:0 10px 14px!important;}
        }

    </style>
</head>
<body class="theme-css" data-admin-theme="<?=htmlspecialchars($pbAdminTheme, ENT_QUOTES, 'UTF-8');?>">
<div id="pb-preloader">
    <div class="pb-preloader__orbit">
        <div class="pb-preloader__ring pb-preloader__ring--1"></div>
        <div class="pb-preloader__ring pb-preloader__ring--2"></div>
        <div class="pb-preloader__ring pb-preloader__ring--3"></div>
        <div class="pb-preloader__core"></div>
    </div>
    <div class="pb-preloader__label">Загрузка</div>
    <div class="pb-preloader__bar-wrap"><div class="pb-preloader__bar"></div></div>
</div>
<script>
(function(){
    function pbHidePreloader(){
        var el = document.getElementById('pb-preloader');
        if(!el) return;
        el.classList.add('pb-preloader--hidden');
        setTimeout(function(){ if(el.parentNode) el.parentNode.removeChild(el); }, 500);
    }
    if(document.readyState === 'complete'){
        pbHidePreloader();
    } else {
        window.addEventListener('load', pbHidePreloader);
        setTimeout(pbHidePreloader, 8000);
    }
})();
</script>
<div class="pbg-progress-wrap"><div class="pbg-progress-bar" id="pbgProgressBar"></div></div>
<button type="button" class="pbg-backtop" id="pbgBackTop" aria-label="Наверх">↑</button>
<input id="token" type="hidden" value="{token}">
<script>
(function(){
    try {
        localStorage.setItem('admin_theme', 'css');
        document.documentElement.setAttribute('data-theme', 'css');
        document.body.classList.remove('theme-css_dark');
        document.body.classList.add('theme-css');
    } catch(e) {}
    const b = document.getElementById('pbgBackTop');
    const p = document.getElementById('pbgProgressBar');
    function onScroll(){
        const top = window.pageYOffset || document.documentElement.scrollTop || 0;
        const max = Math.max(document.documentElement.scrollHeight - window.innerHeight, 1);
        p.style.width = Math.min((top / max) * 100, 100) + '%';
        if (top > 250) b.classList.add('visible'); else b.classList.remove('visible');
    }
    if (b) b.addEventListener('click', function(){ window.scrollTo({top:0, behavior:'smooth'}); });
    window.addEventListener('scroll', onScroll, {passive:true});
    window.addEventListener('load', onScroll);
    window.addEventListener('resize', onScroll);
})();

(function(){
    function markAdminCurrent(){
        try {
            var current = window.location.pathname.replace(/\/$/, '');
            document.querySelectorAll('.navigation li a').forEach(function(link){
                try {
                    var href = link.getAttribute('href') || '';
                    if(!href) return;
                    var url = new URL(href, window.location.origin);
                    var normalized = url.pathname.replace(/\/$/, '');
                    if(normalized === current){
                        link.classList.add('is-current');
                    }
                } catch(e) {}
            });
        } catch(e) {}
    }

    function bindImmediateButtonStates(){
        try {
            document.querySelectorAll('.btn-group[data-toggle="buttons"], .input-group-btn[data-toggle="buttons"]').forEach(function(group){
                if(group.querySelector('input[type="checkbox"]')) return;
                group.addEventListener('click', function(event){
                    var btn = event.target.closest('.btn');
                    if(!btn || !group.contains(btn)) return;
                    group.querySelectorAll('.btn').forEach(function(item){ item.classList.remove('active'); });
                    btn.classList.add('active');
                });
            });
            document.querySelectorAll('.dc-toggle[data-toggle="buttons"]').forEach(function(group){
                group.addEventListener('click', function(event){
                    var item = event.target.closest('label');
                    if(!item || !group.contains(item)) return;
                    group.querySelectorAll('label').forEach(function(el){ el.classList.remove('active'); });
                    item.classList.add('active');
                });
            });
        } catch(e) {}
    }

    function restoreSidebarScroll(){
        try {
            var sidebar = document.querySelector('.admin-sidebar-shell');
            if(!sidebar) return;
            var key = 'pb_admin_sidebar_scroll';
            var saved = 0;
            try {
                saved = parseInt(sessionStorage.getItem(key) || localStorage.getItem(key) || '0', 10) || 0;
            } catch(e) { saved = 0; }

            var applyScroll = function(){
                try { sidebar.scrollTop = saved; } catch(e) {}
            };
            applyScroll();
            window.requestAnimationFrame(applyScroll);
            setTimeout(applyScroll, 60);

            var storeScroll = function(){
                try {
                    sessionStorage.setItem(key, String(sidebar.scrollTop));
                    localStorage.setItem(key, String(sidebar.scrollTop));
                } catch(e) {}
            };

            sidebar.addEventListener('scroll', storeScroll, {passive:true});
            document.querySelectorAll('.navigation li a').forEach(function(link){
                link.addEventListener('click', storeScroll);
            });
            window.addEventListener('beforeunload', storeScroll);
        } catch(e) {}
    }

    function initAdminUiPolish(){
        markAdminCurrent();
        bindImmediateButtonStates();
        restoreSidebarScroll();
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', initAdminUiPolish);
    } else {
        initAdminUiPolish();
    }
})();

</script>

<script>
/* PBGame CMS — engine language for Admin Center */
(function(){
    var cfg = window.PB_ADMIN_ENGINE_LANGUAGE || {engine:'ru', google:'ru', source:'ru'};
    var source = (cfg.source || 'ru').toLowerCase();
    var target = (cfg.google || 'ru').toLowerCase();
    var engine = (cfg.engine || 'ru').toLowerCase();
    var translateLoaded = false;
    var translateReady = false;

    function getHost() {
        return (document.domain || location.hostname || '').replace(/:\d+$/, '');
    }

    function getRootDomain() {
        var host = getHost();
        if (!host || /^[\d.]+$/.test(host) || host.indexOf('.') < 0) return '';
        return '.' + host;
    }

    function setRawCookie(name, value, days, path, domain) {
        var d = new Date();
        d.setTime(d.getTime() + (days || 365) * 86400 * 1000);
        var c = name + '=' + (value || '') + ';path=' + (path || '/') + ';expires=' + d.toUTCString() + ';SameSite=Lax';
        if (domain) c += ';domain=' + domain;
        document.cookie = c;
    }

    function deleteCookie(name, path, domain) {
        var c = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=' + (path || '/') + ';SameSite=Lax';
        if (domain) c += ';domain=' + domain;
        document.cookie = c;
    }

    function clearGoogTrans() {
        var host = getHost();
        var root = getRootDomain();
        var paths = ['/', '/admin'];
        for (var i=0;i<paths.length;i++) {
            deleteCookie('googtrans', paths[i]);
            if (host) deleteCookie('googtrans', paths[i], host);
            if (root) deleteCookie('googtrans', paths[i], root);
        }
        try { localStorage.removeItem('pb_admin_engine_lang'); } catch(e) {}
    }

    function setTranslateCookie(lang) {
        lang = (lang || 'ru').toLowerCase();
        clearGoogTrans();

        if (lang === 'ru') return '';

        var value = '/' + source + '/' + lang;
        var root = getRootDomain();

        /*
         * Для Google Translate значение должно быть сырым "/ru/en",
         * без encodeURIComponent, иначе перевод не применяется.
         */
        setRawCookie('googtrans', value, 365, '/');
        setRawCookie('googtrans', value, 365, '/admin');
        if (root) {
            setRawCookie('googtrans', value, 365, '/', root);
            setRawCookie('googtrans', value, 365, '/admin', root);
        }

        try { localStorage.setItem('pb_admin_engine_lang', engine); } catch(e) {}
        return value;
    }

    function killGoogleBanner() {
        try {
            if (document.body && document.body.style.top) document.body.style.top = '';
            if (document.documentElement && document.documentElement.style.top) document.documentElement.style.top = '';

            var selectors = [
                '.goog-te-banner-frame',
                'iframe.goog-te-banner-frame',
                '.skiptranslate.goog-te-banner-frame',
                '#goog-gt-tt',
                '.goog-te-balloon-frame'
            ];

            for (var i=0;i<selectors.length;i++) {
                var nodes = document.querySelectorAll(selectors[i]);
                for (var j=0;j<nodes.length;j++) {
                    nodes[j].style.display = 'none';
                    nodes[j].style.visibility = 'hidden';
                    nodes[j].style.height = '0';
                    nodes[j].style.opacity = '0';
                    if (nodes[j].parentNode) nodes[j].parentNode.removeChild(nodes[j]);
                }
            }
        } catch(e) {}
    }

    function startBannerGuard() {
        killGoogleBanner();
        var t = 0;
        var iv = setInterval(function(){
            killGoogleBanner();
            if (++t > 50) clearInterval(iv);
        }, 250);
        if (window.MutationObserver) {
            var mo = new MutationObserver(killGoogleBanner);
            mo.observe(document.documentElement, {childList:true, subtree:true, attributes:true, attributeFilter:['style','class']});
        }
    }

    function ensureContainer() {
        var el = document.getElementById('pb_admin_google_translate_element');
        if (el) return el;
        el = document.createElement('div');
        el.id = 'pb_admin_google_translate_element';
        el.style.cssText = 'position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;visibility:hidden;';
        document.body.appendChild(el);
        return el;
    }

    function triggerCombo(lang) {
        var tries = 0;
        var timer = setInterval(function(){
            tries++;
            var combo = document.querySelector('.goog-te-combo');
            if (combo) {
                combo.value = lang;
                combo.dispatchEvent(new Event('change'));
                clearInterval(timer);
            }
            if (tries > 35) clearInterval(timer);
        }, 120);
    }

    function loadGoogle(lang) {
        if (lang === 'ru') return;
        ensureContainer();
        setTranslateCookie(lang);

        if (translateReady) {
            triggerCombo(lang);
            return;
        }

        window.pbAdminGoogleTranslateInit = function(){
            try {
                new google.translate.TranslateElement({
                    pageLanguage: source,
                    autoDisplay: false
                }, 'pb_admin_google_translate_element');

                translateReady = true;
                triggerCombo(lang);
                startBannerGuard();
            } catch(e) {}
        };

        if (!translateLoaded) {
            translateLoaded = true;
            var s = document.createElement('script');
            s.src = (location.protocol === 'https:' ? 'https:' : 'http:') + '//translate.google.com/translate_a/element.js?cb=pbAdminGoogleTranslateInit';
            s.async = true;
            document.body.appendChild(s);
        }
    }

    window.pbAdminApplyEngineLanguage = function(nextEngine, reloadAfter) {
        var map = {ru:'ru', ua:'uk', uk:'uk', en:'en', kz:'kk', kk:'kk', pl:'pl'};
        engine = (nextEngine || engine || 'ru').toLowerCase();
        target = map[engine] || 'ru';

        if (target === 'ru') {
            clearGoogTrans();
            if (reloadAfter) location.reload();
            return;
        }

        setTranslateCookie(target);
        loadGoogle(target);

        if (reloadAfter) {
            setTimeout(function(){ location.reload(); }, 700);
        }
    };

    window.edit_site_language = window.edit_site_language || function(){
        var token = document.getElementById('token') ? document.getElementById('token').value : '';
        var langEl = document.getElementById('site_lang');
        var lang = langEl ? langEl.value : 'ru';
        var resultEl = document.getElementById('edit_site_language_result');

        if (window.NProgress) NProgress.start();

        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: 'phpaction=1&token=' + encodeURIComponent(token) + '&edit_site_lang=1&site_lang=' + encodeURIComponent(lang),
            dataType: 'json',
            success: function(result) {
                if (window.NProgress) NProgress.done();

                if (result && result.status == 1) {
                    if (resultEl) resultEl.innerHTML = '<p class="text-success">' + (result.data || 'Язык движка обновлен.') + '</p>';
                    if (typeof show_ok === 'function') setTimeout(show_ok, 300);
                    window.pbAdminApplyEngineLanguage(lang, true);
                } else {
                    if (resultEl) resultEl.innerHTML = '<p class="text-danger">' + ((result && result.data) ? result.data : 'Не удалось сохранить язык.') + '</p>';
                    if (typeof show_error === 'function') setTimeout(show_error, 300);
                }
            },
            error: function() {
                if (window.NProgress) NProgress.done();
                if (resultEl) resultEl.innerHTML = '<p class="text-danger">Ошибка сохранения языка.</p>';
                if (typeof show_error === 'function') setTimeout(show_error, 300);
            }
        });
    };

    function boot() {
        if (target === 'ru') {
            clearGoogTrans();
            return;
        }

        setTranslateCookie(target);
        loadGoogle(target);
        startBannerGuard();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>

