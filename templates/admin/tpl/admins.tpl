<div class="page">

    <div class="dc-card" style="margin-bottom:14px;">
        <div class="dc-card__head">
            <div class="dc-card__head-main">
                <span class="dc-card__icon dc-card__icon--blue"><span class="glyphicon glyphicon-hdd"></span></span>
                <div class="dc-card__titles">
                    <div class="dc-card__title">Администраторы серверов</div>
                    <div class="dc-card__subtitle">Выдача привилегий, синхронизация и дисциплина</div>
                </div>
            </div>
            <span class="dc-card__badge">Admins</span>
        </div>
        <div class="dc-card__body dc-card__body--tight">
            <div class="dc-field">
                <select id="store_server" onchange="server_change();">{servers}</select>
            </div>
        </div>
    </div>

    <div class="dc-wrap">
        <div class="dc-col">

            <div class="dc-card">
                <div class="dc-card__head">
                    <div class="dc-card__head-main">
                        <span class="dc-card__icon dc-card__icon--green"><span class="glyphicon glyphicon-plus"></span></span>
                        <div class="dc-card__titles">
                            <div class="dc-card__title">Выдать права</div>
                            <div class="dc-card__subtitle">Назначение привилегии игроку</div>
                        </div>
                    </div>
                </div>
                <div class="dc-card__body">
                    <div class="dc-grid2">
                        <div class="dc-tile dc-span2">
                            <div class="dc-tile__head">
                                <span class="dc-tile__icon"><span class="glyphicon glyphicon-shopping-cart"></span></span>
                                <div>
                                    <div class="dc-tile__label">Услуга</div>
                                    <div class="dc-tile__hint">Набор прав, который получит игрок.</div>
                                </div>
                            </div>
                            <div class="dc-field">
                                <select id="store_services" onchange="local_change_service();"></select>
                            </div>
                        </div>

                        <div class="dc-tile dc-span2">
                            <div class="dc-tile__head">
                                <span class="dc-tile__icon"><span class="glyphicon glyphicon-time"></span></span>
                                <div>
                                    <div class="dc-tile__label">Тариф</div>
                                    <div class="dc-tile__hint">Срок действия привилегии.</div>
                                </div>
                            </div>
                            <div class="dc-field">
                                <select id="store_tarifs"></select>
                            </div>
                        </div>

                        <div class="dc-tile dc-span2">
                            <div class="dc-tile__head">
                                <span class="dc-tile__icon"><span class="glyphicon glyphicon-link"></span></span>
                                <div>
                                    <div class="dc-tile__label">Тип привязки</div>
                                    <div class="dc-tile__hint">Как игрок будет авторизован на сервере.</div>
                                </div>
                            </div>
                            <div class="dc-field">
                                <select id="store_type" onchange="local_change_store_type();">
                                    {if($binds[0])}<option value="1">Ник + пароль</option>{/if}
                                    {if($binds[1])}<option value="2">STEAM ID</option>{/if}
                                </select>
                            </div>
                            <div class="dc-field disp-n" id="wrap_player_nick" style="margin-top:8px;">
                                <input type="text" maxlength="32" id="player_nick" placeholder="Введите ник">
                            </div>
                            <div class="dc-field disp-n" id="wrap_player_steam_id" style="margin-top:8px;">
                                <input type="text" maxlength="32" id="player_steam_id" placeholder="Введите STEAM ID">
                            </div>
                            <div class="dc-field disp-n" id="wrap_player_pass" style="margin-top:8px;">
                                <input type="text" maxlength="32" id="player_pass" placeholder="Придумайте пароль">
                            </div>
                        </div>

                        <div class="dc-tile dc-span2">
                            <div class="dc-tile__head">
                                <span class="dc-tile__icon"><span class="glyphicon glyphicon-user"></span></span>
                                <div>
                                    <div class="dc-tile__label">ID профиля на сайте</div>
                                    <div class="dc-tile__hint">Нужен, чтобы связать привилегию с аккаунтом.</div>
                                </div>
                            </div>
                            <div class="dc-field">
                                <input type="number" maxlength="5" id="player_user_id" placeholder="Введите ID">
                            </div>
                        </div>
                    </div>

                    <div class="dc-version-actions" style="margin-top:14px;">
                        <button id="store_buy_btn" type="button" class="btn btn-primary" onclick="add_admin();" style="border-radius:8px;font-weight:700;">
                            <span class="glyphicon glyphicon-ok"></span> Выдать права
                        </button>
                        <button id="store_answer_btn" type="button" class="btn btn-default disp-n" onclick="" style="border-radius:8px;font-weight:700;">Нет</button>
                    </div>
                    <div id="add_result" class="dc-result"></div>
                </div>
            </div>

        </div>

        <div class="dc-col">

            <div class="dc-card">
                <div class="dc-card__head">
                    <div class="dc-card__head-main">
                        <span class="dc-card__icon dc-card__icon--amber"><span class="glyphicon glyphicon-warning-sign"></span></span>
                        <div class="dc-card__titles">
                            <div class="dc-card__title">Дисциплина</div>
                            <div class="dc-card__subtitle">Лимит выговоров до снятия привилегии</div>
                        </div>
                    </div>
                    <span class="dc-card__badge dc-card__badge--warning" id="warns_limit_badge">{warns_limit}</span>
                </div>
                <div class="dc-card__body">
                    <div class="dc-state dc-state--warning" style="margin-bottom:14px;">
                        <span class="dc-state__icon glyphicon glyphicon-info-sign"></span>
                        <div>
                            <b>Как это работает</b>
                            <small>При каждом выговоре администратор получает уведомление на сайте. Когда число активных выговоров достигает лимита, привилегия снимается автоматически.</small>
                        </div>
                    </div>

                    <div class="dc-tile">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-alert"></span></span>
                            <div>
                                <div class="dc-tile__label">Лимит выговоров</div>
                                <div class="dc-tile__hint">Сколько выговоров допускается до снятия прав.</div>
                            </div>
                        </div>
                        <div class="dc-field">
                            <input type="number" id="warns_limit" min="1" max="50" value="{warns_limit}">
                            <button type="button" class="dc-field__btn dc-field__btn--primary" onclick="pbSaveWarnsLimit();">
                                <span class="glyphicon glyphicon-ok"></span> Сохранить
                            </button>
                        </div>
                    </div>
                    <div id="warns_limit_result" class="dc-result"></div>

                    <div class="dc-divider" style="margin:14px 0;"></div>

                    <div class="dc-tile">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-shopping-cart"></span></span>
                            <div>
                                <div class="dc-tile__label">Платное снятие выговора</div>
                                <div class="dc-tile__hint">Администратор сможет снять выговор сам, списав средства с баланса.</div>
                            </div>
                        </div>

                        <div class="dc-toggle dc-toggle--onoff" data-toggle="buttons" id="warns_buyout_toggle">
                            <label class="{if('{warns_buyout}' == '1')}active{/if}" onclick="pbSetBuyoutState(1);">Включить</label>
                            <label class="{if('{warns_buyout}' != '1')}active{/if}" onclick="pbSetBuyoutState(0);">Выключить</label>
                        </div>

                        <div class="dc-field" style="margin-top:8px;">
                            <input type="number" id="warns_price" min="0" step="0.01" value="{warns_price}" placeholder="Цена снятия">
                            <button type="button" class="dc-field__btn dc-field__btn--primary" onclick="pbSaveBuyout();">
                                <span class="glyphicon glyphicon-ok"></span> Сохранить
                            </button>
                        </div>
                        <div class="dc-hint" style="margin-top:6px;">
                            Снимается самый старый активный выговор. Цена в {currency}.
                        </div>
                    </div>
                    <div id="warns_buyout_result" class="dc-result"></div>

                    <div class="dc-divider" style="margin:14px 0;"></div>

                    <div class="dc-tile">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-lock"></span></span>
                            <div>
                                <div class="dc-tile__label">Цена разблокировки услуги</div>
                                <div class="dc-tile__hint">Подставляется по умолчанию при блокировке администратора.</div>
                            </div>
                        </div>
                        <div class="dc-field">
                            <input type="number" id="unblock_price" min="0" step="1" value="{unblock_price}" placeholder="Цена разблокировки">
                            <button type="button" class="dc-field__btn dc-field__btn--primary" onclick="pbSaveUnblockPrice();">
                                <span class="glyphicon glyphicon-ok"></span> Сохранить
                            </button>
                        </div>
                        <div class="dc-hint" style="margin-top:6px;">
                            Цена в {currency}. Игрок платит её, чтобы снять блокировку услуги.
                        </div>
                    </div>
                    <div id="unblock_price_result" class="dc-result"></div>
                </div>
            </div>

            <div class="dc-card" style="margin-top:14px;">
                <div class="dc-card__head">
                    <div class="dc-card__head-main">
                        <span class="dc-card__icon dc-card__icon--slate"><span class="glyphicon glyphicon-refresh"></span></span>
                        <div class="dc-card__titles">
                            <div class="dc-card__title">Синхронизация</div>
                            <div class="dc-card__subtitle">Импорт и экспорт базы администраторов</div>
                        </div>
                    </div>
                </div>
                <div class="dc-card__body">
                    <div id="ftp" class="disp-n">
                        <div class="dc-state dc-state--info" style="margin-bottom:12px;">
                            <span class="dc-state__icon glyphicon glyphicon-info-sign"></span>
                            <div>
                                <b>Импорт из users.ini</b>
                                <small>Предварительно удалите из файла комментарии и мусор. <a target="_blank" href="{site_host}templates/admin/img/users.png">Посмотреть пример</a> — используются двойные кавычки, в конце строки ставится <b>:end:</b></small>
                            </div>
                        </div>
                        <button type="button" class="dc-btn" onclick="import_admins();">
                            <span class="glyphicon glyphicon-import"></span> Импортировать из users.ini
                        </button>
                        <div class="dc-divider" style="margin:12px 0;"></div>
                        <div class="dc-hint" style="margin-bottom:10px;">Если файл users.ini был утерян — выгрузите админов из базы сайта обратно в файл.</div>
                        <button type="button" class="dc-btn" onclick="export_admins();">
                            <span class="glyphicon glyphicon-export"></span> Экспортировать в users.ini
                        </button>
                    </div>

                    <div id="db" class="disp-n">
                        <div class="dc-state dc-state--warning" style="margin-bottom:12px;">
                            <span class="dc-state__icon glyphicon glyphicon-alert"></span>
                            <div>
                                <b>Импорт из базы банса</b>
                                <small>Выключенные администраторы не импортируются.</small>
                            </div>
                        </div>
                        <button type="button" class="dc-btn" onclick="import_admins();">
                            <span class="glyphicon glyphicon-import"></span> Импортировать из БД банса
                        </button>
                        <div class="dc-divider" style="margin:12px 0;"></div>
                        <div class="dc-hint" style="margin-bottom:10px;">Если база админов в бансе утеряна — выгрузите её из базы сайта.</div>
                        <button type="button" class="dc-btn" onclick="export_admins();">
                            <span class="glyphicon glyphicon-export"></span> Экспортировать в БД банса
                        </button>
                    </div>

                    <div id="none" class="disp-n">
                        <div class="dc-state dc-state--muted">
                            <span class="dc-state__icon glyphicon glyphicon-ban-circle"></span>
                            <div>
                                <b>Не поддерживается</b>
                                <small>Данный способ интеграции сервера не поддерживает импорт и экспорт.</small>
                            </div>
                        </div>
                    </div>

                    <div id="timing_result" class="dc-result"></div>
                </div>
            </div>

        </div>
    </div>

    <div class="dc-card" style="margin-top:14px;">
        <div class="dc-card__head">
            <div class="dc-card__head-main">
                <span class="dc-card__icon dc-card__icon--green"><span class="glyphicon glyphicon-list-alt"></span></span>
                <div class="dc-card__titles">
                    <div class="dc-card__title">Список администраторов</div>
                    <div class="dc-card__subtitle">Права, сроки и выговоры</div>
                </div>
            </div>
        </div>
        <div class="dc-card__body dc-card__body--tight">
            <div id="admins">
                <div class="admin-loading-card"><img src="{site_host}templates/admin/img/loader.gif"></div>
            </div>
            <script>load_servers_admins();</script>
        </div>
    </div>

</div>

<div id="pb-warn-modal" class="modal fade admin-quick-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Выговоры администратора</h4>
            </div>
            <div class="modal-body">
                <div class="dc-state dc-state--warning" style="margin-bottom:14px;">
                    <span class="dc-state__icon glyphicon glyphicon-warning-sign"></span>
                    <div>
                        <b id="pb_warn_admin_name">—</b>
                        <small id="pb_warn_counter">Активных выговоров: 0</small>
                    </div>
                </div>

                <div class="dc-tile" style="margin-bottom:14px;">
                    <div class="dc-tile__head">
                        <span class="dc-tile__icon"><span class="glyphicon glyphicon-pencil"></span></span>
                        <div>
                            <div class="dc-tile__label">Выдать выговор</div>
                            <div class="dc-tile__hint">Причина обязательна — она придёт администратору в уведомлении.</div>
                        </div>
                    </div>
                    <div class="dc-field">
                        <input type="text" id="pb_warn_reason" maxlength="500" placeholder="Причина выговора">
                        <button type="button" class="dc-field__btn dc-field__btn--primary" id="pb_warn_issue_btn" onclick="pbIssueWarn();">
                            <span class="glyphicon glyphicon-plus"></span> Выдать
                        </button>
                    </div>
                    <div class="dc-hint" id="pb_warn_limit_hint" style="display:none;margin-top:6px;color:#f0685c;">
                        Лимит выговоров исчерпан — сначала снимите хотя бы один.
                    </div>
                </div>

                <div class="pb-warn-toolbar">
                    <button type="button" class="dc-btn dc-btn--danger-outline" id="pb_warn_clear_btn" onclick="pbClearWarns();">
                        <span class="glyphicon glyphicon-ok-circle"></span> Снять активные
                    </button>
                    <button type="button" class="dc-btn dc-btn--danger-outline pb-warn-purge" id="pb_warn_purge_btn" onclick="pbPurgeWarns();">
                        <span class="glyphicon glyphicon-trash"></span> Удалить историю
                    </button>
                </div>

                <div id="pb_warn_list"></div>
                <div id="pb_warn_result" class="dc-result"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<script>
    function local_change_service() {
        var service = $('#store_services option:selected').val();
        get_tarifs_adm(service);
    }
    function local_change_store_type() {
        var type = $('#store_type option:selected').val();
        change_store_bind_type(type);
    }
    function local_change_admin_type(id) {
        var type = $('#store_type_'+id+' option:selected').val();
        change_admin_bind_type(type, id);
    }
    function server_change() {
        var server = $('#store_server').val();
        location.href = '../admin/admins?server='+server;
    }

    function pbWarnToken() {
        var el = document.getElementById('token');
        return el ? el.value : '';
    }

    function pbWarnSay(msg, ok) {
        var box = document.getElementById('pb_warn_result');
        if (box) {
            box.innerHTML = '<p class="' + (ok ? 'text-success' : 'text-danger') + ' m-0">' + msg + '</p>';
        }
        if (window.PBToast) { PBToast.show(ok ? 'success' : 'error', msg); }
    }

    function pbEscape(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function(ch){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch];
        });
    }

    var pbWarnAdminId = 0;

    function pbOpenWarns(adminId, adminName) {
        pbWarnAdminId = parseInt(adminId, 10) || 0;
        var nameBox = document.getElementById('pb_warn_admin_name');
        if (nameBox) { nameBox.textContent = adminName || ('Администратор #' + pbWarnAdminId); }
        var reason = document.getElementById('pb_warn_reason');
        if (reason) { reason.value = ''; }
        var res = document.getElementById('pb_warn_result');
        if (res) { res.innerHTML = ''; }
        pbLoadWarns();
        $('#pb-warn-modal').modal('show');
    }

    function pbLoadWarns() {
        if (!pbWarnAdminId) { return; }
        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: { phpaction: 1, token: pbWarnToken(), pb_admin_warns_list: 1, admin_id: pbWarnAdminId },
            dataType: 'json',
            success: function(res) {
                var list = document.getElementById('pb_warn_list');
                var counter = document.getElementById('pb_warn_counter');
                if (!res || String(res.status) !== '1') {
                    if (list) { list.innerHTML = '<div class="dc-hint">Не удалось загрузить выговоры.</div>'; }
                    return;
                }
                if (counter) { counter.textContent = 'Активных выговоров: ' + res.active + ' из ' + res.limit; }

                var atLimit = res.active >= res.limit;
                var issueBtn = document.getElementById('pb_warn_issue_btn');
                var reasonInput = document.getElementById('pb_warn_reason');
                var limitHint = document.getElementById('pb_warn_limit_hint');
                var clearBtn = document.getElementById('pb_warn_clear_btn');

                if (issueBtn) { issueBtn.disabled = atLimit; }
                if (reasonInput) { reasonInput.disabled = atLimit; }
                if (limitHint) { limitHint.style.display = atLimit ? '' : 'none'; }
                if (clearBtn) { clearBtn.style.display = res.active > 0 ? '' : 'none'; }

                var purgeBtn = document.getElementById('pb_warn_purge_btn');
                var hasHistory = res.rows && res.rows.length > 0;
                if (purgeBtn) { purgeBtn.style.display = hasHistory ? '' : 'none'; }
                var toolbar = document.querySelector('.pb-warn-toolbar');
                if (toolbar) { toolbar.style.display = hasHistory ? '' : 'none'; }

                if (!list) { return; }
                if (!res.rows || !res.rows.length) {
                    list.innerHTML = '<div class="dc-hint">Выговоров пока нет.</div>';
                    return;
                }
                var html = '';
                res.rows.forEach(function(w){
                    html += '<div class="pb-warn-row' + (w.active ? '' : ' is-removed') + '">' +
                        '<div class="pb-warn-row__head">' +
                            '<span class="pb-warn-row__badge' + (w.active ? '' : ' is-off') + '">' + (w.active ? 'Активен' : 'Снят') + '</span>' +
                            '<span class="pb-warn-row__date">' + pbEscape(w.date) + '</span>' +
                        '</div>' +
                        '<div class="pb-warn-row__reason">' + pbEscape(w.reason) + '</div>' +
                        '<div class="pb-warn-row__meta">Выдал: <b>' + pbEscape(w.issued_by || '—') + '</b></div>' +
                        (w.active
                            ? '<button type="button" class="dc-btn dc-btn--danger-outline" onclick="pbRemoveWarn(' + w.id + ');"><span class="glyphicon glyphicon-remove"></span> Снять выговор</button>'
                            : '<div class="pb-warn-row__meta">Снял: <b>' + pbEscape(w.removed_by || '—') + '</b>' + (w.removed_at ? ' · ' + pbEscape(w.removed_at) : '') + (w.removed_reason ? '<br>Причина снятия: ' + pbEscape(w.removed_reason) : '') + '</div>') +
                        '</div>';
                });
                list.innerHTML = html;
            }
        });
    }

    function pbIssueWarn() {
        var reason = document.getElementById('pb_warn_reason');
        var value = reason ? reason.value.trim() : '';
        if (value === '') { pbWarnSay('Укажите причину выговора', false); return; }

        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: { phpaction: 1, token: pbWarnToken(), pb_admin_warn_add: 1, admin_id: pbWarnAdminId, reason: value },
            dataType: 'json',
            success: function(res) {
                var ok = res && String(res.status) === '1';
                pbWarnSay((res && res.data) || (ok ? 'Готово' : 'Ошибка'), ok);
                if (ok) {
                    if (reason) { reason.value = ''; }
                    pbLoadWarns();
                    if (res.revoked) { setTimeout(function(){ load_servers_admins(); }, 600); }
                }
            },
            error: function(){ pbWarnSay('Ошибка соединения', false); }
        });
    }

    function pbClearWarns() {
        if (!confirm('Снять ВСЕ активные выговоры этого администратора?')) { return; }
        var reason = prompt('Причина очистки (не обязательно):', '');
        if (reason === null) { return; }

        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: { phpaction: 1, token: pbWarnToken(), pb_admin_warns_clear: 1, admin_id: pbWarnAdminId, reason: reason },
            dataType: 'json',
            success: function(res) {
                var ok = res && String(res.status) === '1';
                pbWarnSay((res && res.data) || (ok ? 'Готово' : 'Ошибка'), ok);
                if (ok) {
                    pbLoadWarns();
                    setTimeout(function(){ load_servers_admins(); }, 600);
                }
            },
            error: function(){ pbWarnSay('Ошибка соединения', false); }
        });
    }

    function pbPurgeWarns() {
        if (!confirm('Удалить ВСЮ историю выговоров без возможности восстановления?\n\nЗаписи стираются из базы навсегда, включая уже снятые.')) { return; }

        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: { phpaction: 1, token: pbWarnToken(), pb_admin_warns_purge: 1, admin_id: pbWarnAdminId },
            dataType: 'json',
            success: function(res) {
                var ok = res && String(res.status) === '1';
                pbWarnSay((res && res.data) || (ok ? 'Готово' : 'Ошибка'), ok);
                if (ok) {
                    pbLoadWarns();
                    setTimeout(function(){ load_servers_admins(); }, 600);
                }
            },
            error: function(){ pbWarnSay('Ошибка соединения', false); }
        });
    }

    function pbRemoveWarn(warnId) {
        var reason = prompt('Причина снятия выговора (не обязательно):', '');
        if (reason === null) { return; }

        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: { phpaction: 1, token: pbWarnToken(), pb_admin_warn_remove: 1, warn_id: warnId, reason: reason },
            dataType: 'json',
            success: function(res) {
                var ok = res && String(res.status) === '1';
                pbWarnSay((res && res.data) || (ok ? 'Готово' : 'Ошибка'), ok);
                if (ok) { pbLoadWarns(); }
            },
            error: function(){ pbWarnSay('Ошибка соединения', false); }
        });
    }

    function pbSaveWarnsLimit() {
        var input = document.getElementById('warns_limit');
        var value = input ? parseInt(input.value, 10) : 0;
        var box = document.getElementById('warns_limit_result');

        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: { phpaction: 1, token: pbWarnToken(), pb_admin_warns_limit_set: 1, limit: value },
            dataType: 'json',
            success: function(res) {
                var ok = res && String(res.status) === '1';
                if (box) {
                    box.innerHTML = '<p class="' + (ok ? 'text-success' : 'text-danger') + ' m-0">' + ((res && res.data) || (ok ? 'Готово' : 'Ошибка')) + '</p>';
                }
                if (window.PBToast) { PBToast.show(ok ? 'success' : 'error', (res && res.data) || (ok ? 'Готово' : 'Ошибка')); }
                if (ok) {
                    var badge = document.getElementById('warns_limit_badge');
                    if (badge) { badge.textContent = res.limit; }
                }
            },
            error: function(){
                if (box) { box.innerHTML = '<p class="text-danger m-0">Ошибка соединения</p>'; }
            }
        });
    }

    var pbBuyoutEnabled = {warns_buyout};

    function pbSetBuyoutState(state) {
        pbBuyoutEnabled = state ? 1 : 0;
        var wrap = document.getElementById('warns_buyout_toggle');
        if (!wrap) { return; }
        var labels = wrap.querySelectorAll('label');
        if (labels.length >= 2) {
            labels[0].classList.toggle('active', pbBuyoutEnabled === 1);
            labels[1].classList.toggle('active', pbBuyoutEnabled !== 1);
        }
    }

    function pbSaveUnblockPrice() {
        var input = document.getElementById('unblock_price');
        var price = input ? parseFloat(String(input.value).replace(',', '.')) : 0;
        if (isNaN(price) || price < 0) { price = 0; }

        var box = document.getElementById('unblock_price_result');

        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: { phpaction: 1, token: pbWarnToken(), pb_admin_unblock_price_set: 1, price: price },
            dataType: 'json',
            success: function(res) {
                var ok = res && String(res.status) === '1';
                if (box) {
                    box.innerHTML = '<p class="' + (ok ? 'text-success' : 'text-danger') + ' m-0">' + ((res && res.data) || (ok ? 'Готово' : 'Ошибка')) + '</p>';
                }
                if (window.PBToast) { PBToast.show(ok ? 'success' : 'error', (res && res.data) || (ok ? 'Готово' : 'Ошибка')); }
            },
            error: function() {
                if (box) { box.innerHTML = '<p class="text-danger m-0">Ошибка соединения</p>'; }
            }
        });
    }

    function pbSaveBuyout() {
        var priceInput = document.getElementById('warns_price');
        var price = priceInput ? parseFloat(priceInput.value.replace(',', '.')) : 0;
        if (isNaN(price)) { price = 0; }

        var box = document.getElementById('warns_buyout_result');

        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: {
                phpaction: 1,
                token: pbWarnToken(),
                pb_admin_warns_buyout_set: 1,
                enabled: pbBuyoutEnabled,
                price: price
            },
            dataType: 'json',
            success: function(res) {
                var ok = res && String(res.status) === '1';
                if (box) {
                    box.innerHTML = '<p class="' + (ok ? 'text-success' : 'text-danger') + ' m-0">' + ((res && res.data) || (ok ? 'Готово' : 'Ошибка')) + '</p>';
                }
                if (window.PBToast) { PBToast.show(ok ? 'success' : 'error', (res && res.data) || (ok ? 'Готово' : 'Ошибка')); }
            },
            error: function() {
                if (box) { box.innerHTML = '<p class="text-danger m-0">Ошибка соединения</p>'; }
            }
        });
    }

    get_services_adm({server});
    timing_serv_type({server_type});
    local_change_store_type();
</script>
<script src="{site_host}templates/admin/js/timepicker/timepicker.js"></script>
<script src="{site_host}templates/admin/js/timepicker/jquery-ui-timepicker-addon.js"></script>
<script src="{site_host}templates/admin/js/timepicker/jquery-ui-timepicker-addon-i18n.min.js"></script>
<script src="{site_host}templates/admin/js/timepicker/jquery-ui-sliderAccess.js"></script>
