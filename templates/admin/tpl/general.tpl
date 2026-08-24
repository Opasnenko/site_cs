<div class="page">

    <div class="dc-wrap">
    <div class="dc-col">

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon"><span class="glyphicon glyphicon-cog"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Основные настройки</div>
                        <div class="dc-card__subtitle">Название сайта и доступ администратора</div>
                    </div>
                </div>
                <span class="dc-card__badge">System</span>
            </div>
            <div class="dc-card__body">
                <div class="dc-grid2">
                    <div class="dc-tile dc-span2">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-font"></span></span>
                            <div>
                                <div class="dc-tile__label">Название сайта</div>
                                <div class="dc-tile__hint">Отображается в админцентре, письмах и публичных элементах шаблона.</div>
                            </div>
                        </div>
                        <div class="dc-field">
                            <input type="text" id="site_name" maxlength="255" autocomplete="off" value="{site_name}">
                            <button class="dc-field__btn dc-field__btn--primary" type="button" onclick="edit_site_name();">Изменить</button>
                        </div>
                        <div id="edit_site_name_result" class="dc-result"></div>
                    </div>

                    <div class="dc-tile dc-span2">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-user"></span></span>
                            <div>
                                <div class="dc-tile__label">Главный администратор</div>
                                <div class="dc-tile__hint">ID профиля для уведомлений о покупке прав.</div>
                            </div>
                        </div>
                        <div class="dc-field">
                            <input value="{admins_ids}" type="text" id="admins_ids" maxlength="80" autocomplete="off" placeholder="ID пользователя">
                            <button class="dc-field__btn" type="button" onclick="edit_admins_ids();">Сохранить</button>
                        </div>
                        <div class="dc-hint">Если несколько, укажите через запятую без пробелов.</div>
                        <div id="edit_admins_ids_result" class="dc-result"></div>
                    </div>

                    <div class="dc-tile dc-span2">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-time"></span></span>
                            <div>
                                <div class="dc-tile__label">Часовой пояс сайта</div>
                                <div class="dc-tile__hint">Для системного времени, писем и служебных сценариев.</div>
                            </div>
                        </div>
                        <div class="dc-field">
                            <select id="time_zone">
                                {time_zones}
                            </select>
                            <button class="dc-field__btn" type="button" onclick="edit_site_time_zone();">Сохранить</button>
                        </div>
                        <div id="edit_time_zone_result" class="dc-result"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dc-col">

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--green"><span class="glyphicon glyphicon-piggy-bank"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Валюта и оплата</div>
                        <div class="dc-card__subtitle">Настройки валюты и общей скидки на услуги</div>
                    </div>
                </div>
                <span class="dc-card__badge">Billing</span>
            </div>
            <div class="dc-card__body">
                <div class="dc-grid2">
                    <div class="dc-tile dc-span2">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-piggy-bank"></span></span>
                            <div>
                                <div class="dc-tile__label">Валюта сайта</div>
                                <div class="dc-tile__hint">Применяется и для сайта, и для платёжных систем.</div>
                            </div>
                        </div>
                        <div class="dc-field">
                            <input id="site_currency" type="text" autocomplete="off" value="{code}" placeholder="Например: UAH">
                            <button class="dc-field__btn" type="button" onclick="edit_currency_site();">Изменить</button>
                        </div>
                        <input id="cy_code" type="hidden" value="{code}">
                        <div id="edit_currency_site" class="dc-result"></div>
                    </div>

                    <div class="dc-tile dc-span2">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-scale"></span></span>
                            <div>
                                <div class="dc-tile__label">Скидка на все услуги в %</div>
                                <div class="dc-tile__hint">Общая скидка на все платные услуги сайта.</div>
                            </div>
                        </div>
                        <div class="dc-field">
                            <input type="number" id="discount" maxlength="2" autocomplete="off" placeholder="от 1 до 99" value="{discount}">
                            <button class="dc-field__btn" type="button" onclick="edit_discount();">Изменить</button>
                        </div>
                        <div id="edit_discount_result" class="dc-result"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--slate"><span class="glyphicon glyphicon-trash"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Очистка движка</div>
                        <div class="dc-card__subtitle">Быстрые действия для удаления старых данных и сброса кеша</div>
                    </div>
                </div>
                <span class="dc-card__badge">Maintenance</span>
            </div>
            <div class="dc-card__body">
                <div class="dc-btnbar" id="engine-cleanup">
                    <button class="dc-btn" type="button" onclick="dell_all_chat_messages();"><span class="glyphicon glyphicon-comment"></span> Чат <span class="dc-btn__count" id="chat_number">{chat_number}</span></button>
                    <button class="dc-btn" type="button" onclick="dell_all_bid_tickets();"><span class="glyphicon glyphicon-list-alt"></span> Все тикеты</button>
                    <button class="dc-btn" type="button" onclick="dell_all_bid_bans();"><span class="glyphicon glyphicon-ban-circle"></span> Заявки на разбан</button>
                    <button class="dc-btn" type="button" onclick="dell_all_bid_complaints();"><span class="glyphicon glyphicon-flag"></span> Жалобы</button>
                    <button class="dc-btn" type="button" onclick="fast_admin_action('dell_old_bans');"><span class="glyphicon glyphicon-calendar"></span> Старые разбаны</button>
                    <button class="dc-btn" type="button" onclick="fast_admin_action('dell_old_tickets');"><span class="glyphicon glyphicon-inbox"></span> Старые тикеты</button>
                    <button class="dc-btn dc-btn--primary" type="button" onclick="fast_admin_action('dell_cache');"><span class="glyphicon glyphicon-refresh"></span> Сбросить кеш</button>
                </div>
            </div>
        </div>

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--amber"><span class="glyphicon glyphicon-filter"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Видимость и фильтры</div>
                        <div class="dc-card__subtitle">Модерация публичных данных</div>
                    </div>
                </div>
                <span class="dc-card__badge">Content</span>
            </div>
            <div class="dc-card__body">
                <div class="dc-tools">
                    <div class="dc-tool">
                        <div class="dc-tool__text">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-volume-up"></span></span>
                            <div>
                                <div class="dc-tile__label">Звуки уведомлений</div>
                                <div class="dc-tile__hint">Загрузка и управление звуками всплывающих уведомлений (тостеров) для пользователей.</div>
                            </div>
                        </div>
                        <button class="dc-btn" type="button" data-target="#toast-sounds-modal" data-toggle="modal" onclick="pbAdmTsOpen();">Управлять звуками</button>
                    </div>

                    <div class="dc-tool">
                        <div class="dc-tool__text">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-ban-circle"></span></span>
                            <div>
                                <div class="dc-tile__label">Запрещённые слова</div>
                                <div class="dc-tile__hint">Список слов, которые будут фильтроваться в контенте.</div>
                            </div>
                        </div>
                        <button class="dc-btn" type="button" data-target="#forbidden-words" data-toggle="modal" onclick="loadForbiddenWords();">Добавить | редактировать</button>
                    </div>

                    <div class="dc-tool">
                        <div class="dc-tool__text">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-lock"></span></span>
                            <div>
                                <div class="dc-tile__label">Скрывать STEAM ID / IP игроков</div>
                                <div class="dc-tile__hint">Пользователи с флагами i, k, s, j имеют иммунитет к этой опции.</div>
                            </div>
                        </div>
                        <div class="dc-tool__control">
                            <select id="hidePlayersIdType">
                                <option value="0" {if('{hidePlayersId}' == '0')} selected {/if}>Не скрывать</option>
                                <option value="1" {if('{hidePlayersId}' == '1')} selected {/if}>Скрывать у всех</option>
                                <option value="2" {if('{hidePlayersId}' == '2')} selected {/if}>Только у админов</option>
                                <option value="3" {if('{hidePlayersId}' == '3')} selected {/if}>Только у игроков</option>
                            </select>
                            <button class="dc-btn" type="button" onclick="editHidingPlayersId();">Изменить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--slate"><span class="glyphicon glyphicon-ban-circle"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Запрещённые идентификаторы</div>
                        <div class="dc-card__subtitle">Ограничение покупки привилегий по нику</div>
                    </div>
                </div>
                <span class="dc-card__badge">Moderation</span>
            </div>
            <div class="dc-card__body">
                <div class="dc-tools">
                    <div class="dc-tool">
                        <div class="dc-tool__text">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-list-alt"></span></span>
                            <div>
                                <div class="dc-tile__label">Список идентификаторов</div>
                                <div class="dc-tile__hint">Шаблоны ников, на которые запрещена покупка привилегий.</div>
                            </div>
                        </div>
                        <button class="dc-btn" type="button" data-target="#bad_nicks" data-toggle="modal" onclick="load_bad_nicks();">Добавить | редактировать</button>
                    </div>
                    <div class="dc-tool">
                        <div class="dc-tool__text">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-flash"></span></span>
                            <div>
                                <div class="dc-tile__label">Статус фильтра</div>
                                <div class="dc-tile__hint">Включить проверку ников при покупке.</div>
                            </div>
                        </div>
                        <div class="dc-toggle dc-toggle--onoff dc-toggle--compact" data-toggle="buttons">
                            <label class="{bn_act}" onclick="change_value('config__secondary','bad_nicks_act','1','1');">Вкл</label>
                            <label class="{bn_act2}" onclick="change_value('config__secondary','bad_nicks_act','2','1');">Выкл</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

</div>

<div id="bad_nicks" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Запрещенные идентификаторы</h4>
			</div>
			<div class="modal-body">
				<div class="bs-callout bs-callout-info mb-10 fs-14">
					Данная функция запрещает покупку привилегий на указанные Вами идентификаторы.<br>
					<br>
					Вы можете указать шаблон для запрета идентификаторов при помощи символа: <b>{%}</b>. Пример:<br>
					<b>{%}bad nick</b> - запретит все идентификаторы, которые заканчиваются на <b>bad nick</b> <br>
					<b>bad nick{%}</b> - запретит все идентификаторы, которые начинаются на <b>bad nick</b> <br>
					<b>{%}bad nick{%}</b> - запретит все идентификаторы, в которых встречается строка <b>bad nick</b> <br>
				</div>

				<form id="bad_nicks_list"></form>
				<button class="btn btn-default mt-5 f-l" onclick="save_bad_nicks();">Сохранить</button>
				<button class="btn btn-default mt-5 f-r" onclick="add_nick_input();">Добавить</button>
				<div class="clearfix"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="forbidden-words" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">x</button>
                <h4 class="modal-title">Запрещённые слова</h4>
            </div>
            <div class="modal-body">
                <form id="forbidden-words-list"></form>
                <button class="btn btn-default mt-5 f-l" onclick="saveForbiddenWords();">Сохранить</button>
                <button class="btn btn-default mt-5 ml-5 f-l" onclick="addForbiddenWordInput();">Добавить</button>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>

<div id="toast-sounds-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">x</button>
                <h4 class="modal-title">Звуки уведомлений (тостеры)</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="margin-bottom:15px;">
                    Загружайте .mp3 (до 3 МБ) для каждого типа уведомления. Пользователи выбирают
                    из этих звуков в настройках профиля. Удаление файла убирает его у всех пользователей.
                </p>
                <div id="toast-sounds-body">
                    <div class="text-center" style="padding:30px;"><span class="glyphicon glyphicon-refresh"></span> Загрузка...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pb-adm-ts-group { margin-bottom: 18px; border: 1px solid rgba(0,0,0,.08); border-radius: 8px; overflow: hidden; }
.pb-adm-ts-group__head { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 14px; background:#f5f6f8; font-weight:600; }
.pb-adm-ts-group__head .label { padding:4px 8px; }
.pb-adm-ts-list { padding: 8px 14px; }
.pb-adm-ts-row { display:flex; align-items:center; gap:8px; padding:8px 0; border-bottom:1px solid rgba(0,0,0,.05); }
.pb-adm-ts-row:last-child { border-bottom:0; }
.pb-adm-ts-row__name { flex:1 1 auto; min-width:0; }
.pb-adm-ts-row__name input { width:100%; }
.pb-adm-ts-row__actions { flex:0 0 auto; display:flex; gap:6px; }
.pb-adm-ts-empty { color:#999; font-size:12px; padding:8px 0; }
.pb-adm-ts-upload { display:flex; gap:8px; align-items:center; padding:10px 14px; background:#fafbfc; border-top:1px solid rgba(0,0,0,.06); flex-wrap:wrap; }
.pb-adm-ts-upload input[type=text] { width:180px; }
</style>

<script>
(function () {
    function getToken() {
        var el = document.getElementById('token');
        return el ? el.value : '';
    }
    var ENDPOINT = '/ajax/addons/toasts/actions.php';
    var SND_BASE = '/ajax/addons/toasts/sounds/';
    var loaded = false;

    function msg(text, ok) {
        if (typeof show_message === 'function') { show_message(text); return; }
        var m = document.getElementById('message');
        if (m) { m.className = ''; m.innerHTML = text; }
    }

    function req(data, isForm, cb) {
        var opts = {
            type: 'POST',
            url: ENDPOINT,
            dataType: 'json',
            success: function (r) { cb(r); },
            error: function () { cb({ status: 0, message: 'Ошибка запроса.' }); }
        };
        if (isForm) { opts.data = data; opts.processData = false; opts.contentType = false; }
        else { opts.data = data; }
        $.ajax(opts);
    }

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
    }); }

    function render(manifest) {
        var html = '';
        for (var g = 0; g < manifest.length; g++) {
            var grp = manifest[g];
            html += '<div class="pb-adm-ts-group" data-type="' + esc(grp.type) + '">';
            html += '<div class="pb-adm-ts-group__head"><span>' + esc(grp.label) + '</span>' +
                    '<span class="label label-default">' + grp.sounds.length + ' зв.</span></div>';
            html += '<div class="pb-adm-ts-list">';
            if (!grp.sounds.length) {
                html += '<div class="pb-adm-ts-empty">Пока нет звуков — загрузите первый.</div>';
            } else {
                for (var s = 0; s < grp.sounds.length; s++) {
                    var snd = grp.sounds[s];
                    var url = SND_BASE + grp.type + '/' + snd.file;
                    html += '<div class="pb-adm-ts-row" data-file="' + esc(snd.file) + '">' +
                        '<button class="btn btn-xs btn-default" onclick="pbAdmTsPreview(\'' + esc(url) + '\')" title="Прослушать"><span class="glyphicon glyphicon-play"></span></button>' +
                        '<div class="pb-adm-ts-row__name"><input type="text" class="form-control input-sm" value="' + esc(snd.name) + '"></div>' +
                        '<div class="pb-adm-ts-row__actions">' +
                            '<button class="btn btn-xs btn-default" onclick="pbAdmTsRename(this)">Сохранить имя</button>' +
                            '<button class="btn btn-xs btn-danger" onclick="pbAdmTsDelete(this)">Удалить</button>' +
                        '</div></div>';
                }
            }
            html += '</div>';
            html += '<div class="pb-adm-ts-upload">' +
                '<input type="file" accept="audio/mpeg,.mp3" class="pb-adm-ts-file">' +
                '<input type="text" class="form-control input-sm pb-adm-ts-newname" placeholder="Название (необязательно)" maxlength="40">' +
                '<button class="btn btn-sm btn-primary" onclick="pbAdmTsUpload(this)">Загрузить .mp3</button>' +
                '</div>';
            html += '</div>';
        }
        $('#toast-sounds-body').html(html);
    }

    window.pbAdmTsOpen = function () {
        if (loaded) { return; }
        req({ action: 'list' }, false, function (r) {
            if (r.status == 1) { loaded = true; render(r.manifest); }
            else { $('#toast-sounds-body').html('<div class="text-danger">' + esc(r.message || 'Ошибка') + '</div>'); }
        });
    };

    window.pbAdmTsPreview = function (url) {
        try { var a = new Audio(url); a.volume = 0.8; a.play().catch(function () {}); } catch (e) {}
    };

    window.pbAdmTsUpload = function (btn) {
        var grp = btn.closest('.pb-adm-ts-group');
        var type = grp.getAttribute('data-type');
        var fileInput = grp.querySelector('.pb-adm-ts-file');
        var nameInput = grp.querySelector('.pb-adm-ts-newname');
        if (!fileInput.files || !fileInput.files.length) { msg('Выберите .mp3 файл.'); return; }

        var fd = new FormData();
        fd.append('action', 'upload');
        fd.append('token', getToken());
        fd.append('type', type);
        fd.append('name', nameInput.value || '');
        fd.append('sound', fileInput.files[0]);

        btn.disabled = true;
        req(fd, true, function (r) {
            btn.disabled = false;
            msg(r.message || '');
            if (r.status == 1 && r.manifest) { render(r.manifest); }
        });
    };

    window.pbAdmTsDelete = function (btn) {
        if (!confirm('Удалить этот звук? Он пропадёт у всех пользователей.')) { return; }
        var row = btn.closest('.pb-adm-ts-row');
        var grp = btn.closest('.pb-adm-ts-group');
        req({ action: 'delete', token: getToken(), type: grp.getAttribute('data-type'), file: row.getAttribute('data-file') }, false, function (r) {
            msg(r.message || '');
            if (r.status == 1 && r.manifest) { render(r.manifest); }
        });
    };

    window.pbAdmTsRename = function (btn) {
        var row = btn.closest('.pb-adm-ts-row');
        var grp = btn.closest('.pb-adm-ts-group');
        var name = row.querySelector('.pb-adm-ts-row__name input').value;
        req({ action: 'rename', token: getToken(), type: grp.getAttribute('data-type'), file: row.getAttribute('data-file'), name: name }, false, function (r) {
            msg(r.message || '');
            if (r.status == 1 && r.manifest) { render(r.manifest); }
        });
    };
})();
</script>

<link href="{site_host}files/toasts/toasty.min.css?v={cache}" rel="stylesheet">
<script src="{site_host}files/toasts/toasty.min.js?v={cache}" type="text/javascript"></script>
