<div class="page">

    {if('{show_security_alert}' == '1')}
    <div class="pb-sec-alert" role="alert">
        <span class="pb-sec-alert__icon"><span class="glyphicon glyphicon-exclamation-sign"></span></span>
        <div class="pb-sec-alert__text">
            <b>Важно: защитите Админцентр</b>
            <span>Включите вход по <b>ключу доступа (2FA)</b> в настройках безопасности и отключите вход по логину и паролю — это значительно снижает риск взлома панели.</span>
        </div>
        <a href="{site_host}admin/security" class="pb-sec-alert__btn">Перейти в Безопасность <span class="glyphicon glyphicon-arrow-right"></span></a>
    </div>
    {/if}

    <div class="dc-update-alert" id="dc-update-alert" role="alert" style="display:none;">
        <span class="dc-update-alert__icon"><span class="glyphicon glyphicon-cloud-download"></span></span>
        <div class="dc-update-alert__text">
            <b>Вышло обновление PBGame CMS <span id="dc-update-alert-version"></span></b>
            <span>Загляните в «Центр обновлений» — там можно посмотреть описание изменений и установить обновление.</span>
        </div>
        <button type="button" class="dc-update-alert__btn" onclick="document.querySelector('.dc-hub-tile--updates').click();">
            Открыть Центр обновлений <span class="glyphicon glyphicon-arrow-right"></span>
        </button>
    </div>

    <div class="dc-hub-menu">
        <button type="button" class="dc-hub-tile dc-hub-tile--system" data-drawer="dc-drawer-system">
            <span class="dc-hub-tile__icon"><span class="glyphicon glyphicon-cog"></span></span>
            <div class="dc-hub-tile__body">
                <div class="dc-hub-tile__title">Управление сайтом</div>
                <div class="dc-hub-tile__hint">Выключение сайта и режим разработчика</div>
            </div>
            <span class="dc-hub-tile__badge dc-hub-tile__badge--{off_status_class}">{off_status_text}</span>
        </button>

        <button type="button" class="dc-hub-tile dc-hub-tile--backup" data-drawer="dc-drawer-backup">
            <span class="dc-hub-tile__icon"><span class="glyphicon glyphicon-save-file"></span></span>
            <div class="dc-hub-tile__body">
                <div class="dc-hub-tile__title">Бекап движка</div>
                <div class="dc-hub-tile__hint">Резервные копии перед настройкой и обновлениями</div>
            </div>
            <span class="dc-hub-tile__badge">v{current_version}</span>
        </button>

        <button type="button" class="dc-hub-tile dc-hub-tile--updates" data-drawer="dc-drawer-updates">
            <span class="dc-hub-tile__icon"><span class="glyphicon glyphicon-cloud-download"></span></span>
            <div class="dc-hub-tile__body">
                <div class="dc-hub-tile__title">Центр обновлений</div>
                <div class="dc-hub-tile__hint">Версия движка, сервер обновления и лицензия</div>
            </div>
            <span class="dc-hub-tile__badge dc-hub-tile__badge--{pb_license_badge_class}">{pb_license_status}</span>
        </button>
    </div>

    <div class="dc-qa">
        <div class="dc-qa__head">
            <div class="dc-qa__title-wrap">
                <span class="dc-qa__title-icon"><span class="glyphicon glyphicon-flash"></span></span>
                <span class="dc-qa__title">Быстрый доступ</span>
            </div>
            <button type="button" class="dc-qa__settings" data-toggle="modal" data-target="#dcQuickAccessModal" title="Настроить быстрый доступ">
                <span class="glyphicon glyphicon-cog"></span> Настроить
            </button>
        </div>
        <div class="dc-qa-grid" id="dcQuickAccessGrid">
            {dc_quick_access_tiles}
        </div>
        <div class="dc-qa-empty" id="dcQuickAccessEmpty" style="display:none;">
            <span class="glyphicon glyphicon-flash"></span>
            <b>Плиток пока нет</b>
            <span>Нажмите «Настроить» и добавьте до 5 пунктов из готового списка меню.</span>
        </div>
    </div>

    <div class="dc-card dc-logins" style="margin-top:18px;">
        <div class="dc-card__head">
            <div class="dc-card__head-main">
                <span class="dc-card__icon dc-card__icon--slate"><span class="glyphicon glyphicon-log-in"></span></span>
                <div class="dc-card__titles">
                    <div class="dc-card__title">Последний вход в админку</div>
                    <div class="dc-card__subtitle">IP, дата, геопозиция и сессия каждого входа</div>
                </div>
            </div>
            <button type="button" class="dc-btn dc-btn--danger-outline" id="dcLoginsClear">
                <span class="glyphicon glyphicon-trash"></span> Очистить
            </button>
        </div>
        <div class="dc-card__body dc-card__body--tight">
            <div class="dc-logins-table-wrap">
                <table class="dc-logins-table">
                    <thead>
                        <tr>
                            <th>Администратор</th>
                            <th>IP</th>
                            <th>Геопозиция</th>
                            <th>Дата</th>
                            <th>Время</th>
                            <th>Сессия</th>
                        </tr>
                    </thead>
                    <tbody id="dcLoginsBody"></tbody>
                </table>
            </div>
            <div class="dc-logins-empty" id="dcLoginsEmpty" style="display:none;">
                <span class="glyphicon glyphicon-inbox"></span>
                <b>Записей пока нет</b>
                <span>Журнал заполняется при каждом входе в Админцентр.</span>
            </div>
            <div class="dc-logins-foot">
                <button type="button" class="dc-btn" id="dcLoginsMore" style="display:none;">
                    <span class="glyphicon glyphicon-chevron-down"></span> Показать ещё
                </button>
                <span class="dc-logins-count" id="dcLoginsCount"></span>
            </div>
            <div id="dcLoginsResult" class="dc-result"></div>
        </div>
    </div>

    <div id="dcQuickAccessModal" class="modal fade admin-quick-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Быстрый доступ на странице</h4>
                </div>
                <div class="modal-body">
                    <div class="admin-quick-modal__hint">Выберите до 5 пунктов из готового списка меню — они появятся плитками на этой странице. Настройки сохраняются в базе данных и применяются для всех администраторов.</div>
                    <div id="dcQuickAccessRows" class="admin-quick-modal__rows"></div>
                    <button type="button" class="btn btn-default" id="dcQuickAddRow">
                        <span class="glyphicon glyphicon-plus"></span> Добавить пункт
                    </button>
                    <div id="dcQuickAccessResult" class="admin-quick-modal__result"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="dcQuickSave">
                        <span class="glyphicon glyphicon-ok"></span> Сохранить
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="dcQuickAccessData">{dc_quick_access_data}</script>
    <script type="application/json" id="dcQuickAccessCatalog">{dc_quick_access_catalog}</script>

    <div id="dc-drawer-system" class="dc-drawer-content" style="display:none;">
        <div class="dc-drawer-section">
            <div class="dc-drawer-section__head">
                <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-off"></span></span>
                <span class="dc-drawer-section__title">Выключение сайта</span>
            </div>
            <div class="dc-hint" style="margin-top:0;margin-bottom:12px;">При выключении сайт будет доступен только авторизованному администратору.</div>
            <div class="dc-toggle dc-toggle--onoff" data-toggle="buttons" style="margin-bottom:10px;">
                <label class="{off_act2}" onclick="change_value('config','off','2','1');">
                    <input type="radio" style="display:none;">
                    Включить сайт
                </label>
                <label class="{off_act}" onclick="change_value('config','off','1','1');">
                    <input type="radio" style="display:none;">
                    Выключить сайт
                </label>
            </div>
            <div class="dc-field">
                <input value="{off_message}" type="text" id="off_message" maxlength="250" autocomplete="off" placeholder="Сообщение пользователям">
                <button class="dc-field__btn" type="button" onclick="edit_off_message();">Сохранить</button>
            </div>
            <div id="edit_off_message_result" class="dc-result"></div>
        </div>

        <div class="dc-drawer-section">
            <div class="dc-drawer-section__head">
                <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-console"></span></span>
                <span class="dc-drawer-section__title">Режим разработчика</span>
            </div>
            <div class="dc-state dc-state--{developer_mode_status_class}" style="margin-bottom:12px;">
                <span class="dc-state__icon glyphicon glyphicon-{developer_mode_status_icon}"></span>
                <div>
                    <b>Режим разработчика: {developer_mode_status_text}</b>
                    <small>{developer_mode_status_hint}</small>
                </div>
            </div>

            <div class="dc-tile" style="margin-bottom:12px;">
                <div class="dc-toggle dc-toggle--devmode" data-toggle="buttons" style="margin-bottom:10px;">
                    <label class="{developer_mode}" onclick="developer_mode_on('1');">
                        <input type="radio" style="display:none;">
                        Включить
                    </label>
                    <label class="{developer_mode2}" onclick="developer_mode_on('2');">
                        <input type="radio" style="display:none;">
                        Выключить
                    </label>
                </div>
                <input type="password" class="form-control" id="dev_key" maxlength="32" value="{dev_key}" placeholder="Введите ключ разработчика" style="height:38px;border-radius:9px;border-color:#dde3ec;box-shadow:none;">
            </div>

            <div class="dc-keybox">
                <span>Ключ разработчика</span>
                <code><?php $str = "{host}"; echo md5($str);?></code>
            </div>

            <div class="dc-note">
                Для операций, связанных с PHP-ошибками и установкой модулей, требуется отключить
                <a href="#" data-target="#safe_mode" data-toggle="modal" title="Открыть" onclick="return false;">Безопасный режим</a>.
            </div>
            <input type="hidden" class="form-control" id="host" value="{host}">
        </div>
    </div>

    <div id="dc-drawer-backup" class="dc-drawer-content" style="display:none;">
        <div class="dc-drawer-section" id="engine-backups">
            <div class="dc-drawer-section__head">
                <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-save-file"></span></span>
                <span class="dc-drawer-section__title">Создание резервной копии</span>
            </div>
            <div class="dc-backup-intro">
                <div>
                    <b>Текущая версия движка: {current_version}</b>
                    <small>Можно создать бекап только базы, только файлов движка или полный снимок проекта.</small>
                </div>
                <div class="dc-backup-intro-actions">
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#pb-backup-help-modal" style="border-radius:8px;">Как это работает</button>
                </div>
            </div>

            <div class="dc-grid2" style="align-items:stretch;">
                <div class="dc-span2">
                    <div class="dc-backup-modes">
                        <label class="dc-backup-mode">
                            <input type="radio" name="pb_backup_mode" value="full" checked>
                            <span><strong>Полный бекап</strong><br><small>Файлы + база данных</small></span>
                        </label>
                        <label class="dc-backup-mode">
                            <input type="radio" name="pb_backup_mode" value="files">
                            <span><strong>Только движок</strong><br><small>Файлы проекта без базы данных</small></span>
                        </label>
                        <label class="dc-backup-mode">
                            <input type="radio" name="pb_backup_mode" value="db">
                            <span><strong>Только БД</strong><br><small>Только база данных без файлов</small></span>
                        </label>
                    </div>
                </div>
                <div class="dc-span2 dc-backup-actions" style="align-items:stretch;">
                    <button type="button" class="btn btn-success" onclick="pbBackupCreate();" style="border-radius:9px;font-weight:700;">Создать бекап</button>
                    <div id="pb_backup_action_state" class="dc-hint" style="margin-top:0;"></div>
                </div>
            </div>
        </div>

        <div class="dc-drawer-section">
            <div class="dc-drawer-section__head">
                <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-list-alt"></span></span>
                <span class="dc-drawer-section__title">Созданные бекапы</span>
            </div>
            <div id="pb_backup_result"></div>
        </div>
    </div>

    <div id="dc-drawer-updates" class="dc-drawer-content" style="display:none;">
        <div class="dc-drawer-section">
            <div class="dc-drawer-section__head">
                <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-hdd"></span></span>
                <span class="dc-drawer-section__title">Версия и сервер обновления</span>
            </div>
            <div id="update_server_status"></div>
            <?php if(!extension_loaded('zip')): ?>
            <div class="dc-alert">
                <span class="glyphicon glyphicon-alert"></span>
                <div>
                    <b>ZIP расширение отключено</b>
                    <small>Настройте ZIP в PHP, чтобы автоустановка обновлений работала корректно.</small>
                </div>
            </div>
            <?php endif; ?>

            <div class="dc-overview">
                <div class="dc-overview-item dc-overview-item--accent">
                    <div class="dc-overview-icon"><span class="glyphicon glyphicon-hdd"></span></div>
                    <div>
                        <span class="dc-eyebrow">Версия движка</span>
                        <div id="version" class="dc-version-line"><img src="{site_host}templates/admin/img/loader.gif"></div>
                    </div>
                </div>
                <div class="dc-overview-item">
                    <div class="dc-overview-icon"><span class="glyphicon glyphicon-cloud-download"></span></div>
                    <div>
                        <span class="dc-eyebrow">Сервер обновления</span>
                        <div class="dc-update-line">
                            <select id="update_servers" class="form-control" style="height:38px;border-radius:9px;border-color:#dde3ec;box-shadow:none;">{update_servers}</select>
                            <button class="btn btn-default" type="button" onclick="edit_update_server();" style="height:38px;border-radius:9px;">
                                <span class="glyphicon glyphicon-ok"></span>
                                Изменить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dc-drawer-section">
            <div class="dc-drawer-section__head">
                <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-certificate"></span></span>
                <span class="dc-drawer-section__title">Лицензия PBGame CMS</span>
            </div>
            <div class="dc-license dc-license--{pb_license_badge_class}" style="margin-top:0;">
                <div class="dc-license__head">
                    <div>
                        <span class="dc-eyebrow">Статус доступа и сервисов</span>
                        <b>Официальная поддержка, обновления и модули для привязанного домена.</b>
                    </div>
                    <span class="label label-{pb_license_badge_class}">{pb_license_status}</span>
                </div>
                <div class="dc-license-grid">
                    <div><span>Домен</span><b>{pb_license_domain}</b></div>
                    <div><span>Build ID</span><b>{pb_license_build_id}</b></div>
                    <div><span>Поддержка</span><b>{pb_license_support}</b></div>
                    <div><span>Обновления</span><b>{pb_license_updates}</b></div>
                    <div><span>Модули</span><b>{pb_license_modules}</b></div>
                </div>
                <div class="dc-license-message">{pb_license_message}</div>
                <div class="dc-license-actions">
                    <a href="{pb_license_url}" target="_blank" rel="noopener" class="btn btn-sm btn-default">
                        <span class="glyphicon glyphicon-new-window"></span>
                        Открыть лицензию
                    </a>
                    <a href="{pb_license_check_url}" target="_blank" rel="noopener" class="btn btn-sm btn-info">
                        <span class="glyphicon glyphicon-search"></span>
                        Проверить домен
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="dc-drawer-overlay" id="dc-drawer-overlay"></div>
    <div class="dc-drawer" id="dc-drawer">
        <div class="dc-drawer__head">
            <div class="dc-drawer__title-wrap">
                <span class="dc-drawer__icon" id="dc-drawer-icon"><span class="glyphicon glyphicon-cog"></span></span>
                <div>
                    <h4 class="dc-drawer__title" id="dc-drawer-title">Управление сайтом</h4>
                    <div class="dc-drawer__subtitle" id="dc-drawer-subtitle"></div>
                </div>
            </div>
            <button type="button" class="dc-drawer__close" id="dc-drawer-close" aria-label="Закрыть"><span class="glyphicon glyphicon-remove"></span></button>
        </div>
        <div class="dc-drawer__body" id="dc-drawer-body"></div>
    </div>

</div>

<div id="pbgame-update-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="pbgame-update-title">Установка обновления PBGame CMS</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" id="pbgame-update-message">Подготовка к запуску обновления...</div>
                <div class="progress" style="height:20px; margin-bottom:15px;">
                    <div id="pbgame-update-progress-bar" class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width:0%; min-width:2em;">0%</div>
                </div>
                <div class="row">
                    <div class="col-md-5">
                        <ul class="list-group" id="pbgame-update-stages">
                            <li class="list-group-item" data-step="prepare">1. Подготовка</li>
                            <li class="list-group-item" data-step="download">2. Скачивание пакета</li>
                            <li class="list-group-item" data-step="verify">3. Проверка пакета</li>
                            <li class="list-group-item" data-step="extract">4. Распаковка архива</li>
                            <li class="list-group-item" data-step="backup_files">5. Резервная копия файлов</li>
                            <li class="list-group-item" data-step="backup_db">6. Резервная копия базы</li>
                            <li class="list-group-item" data-step="apply_files">7. Установка файлов</li>
                            <li class="list-group-item" data-step="apply_sql">8. Обновление базы</li>
                            <li class="list-group-item" data-step="post_update">9. Post-update</li>
                            <li class="list-group-item" data-step="finalize">10. Очистка кеша</li>
                            <li class="list-group-item" data-step="finish">11. Завершение</li>
                        </ul>
                    </div>
                    <div class="col-md-7">
                        <div class="well" style="min-height:240px; max-height:320px; overflow:auto; background:#10151b; color:#d9f4d7; border:1px solid #1d2935;" id="pbgame-update-log">Ожидание запуска обновления...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" id="pbgame-update-close" data-dismiss="modal" disabled>Закрыть</button>
                <button type="button" class="btn btn-primary" id="pbgame-update-reload" style="display:none;" onclick="reset_page();">Обновить страницу</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(window){
    // Основная логика обновления/отката вынесена в ajax/performers/acp.min.js.
    // Этот блок оставлен только как безопасный мост, чтобы шаблон не переопределял
    // стабильный managed updater и не возвращал старый сценарий без recovery-проверки.
    if(typeof window.pbgameLaunchUpdateProcess === 'function'){
        window.get_update = window.install_update;
        window.rollbackUpdate = window.rollback_update;
        window.installUpdate = window.install_update;
        return;
    }

    window.install_update = window.install_update || function(){
        alert('Модуль установки обновлений ещё не загружен. Обновите страницу и попробуйте снова.');
        return false;
    };
    window.rollback_update = window.rollback_update || function(){
        alert('Модуль отката обновлений ещё не загружен. Обновите страницу и попробуйте снова.');
        return false;
    };
    window.get_update = window.install_update;
    window.rollbackUpdate = window.rollback_update;
    window.installUpdate = window.install_update;
})(window);
</script>


<script>
(function(){
    function pbBackupToken(){
        return $('#token').val();
    }
    function pbBackupSetBusy(message){
        $('#pb_backup_action_state').text(message || '');
        $('#engine-backups').find('button').prop('disabled', !!message);
    }
    function pbBackupRenderResponse(result){
        if(result && typeof result.html !== 'undefined'){
            $('#pb_backup_result').html(result.html);
        }
        if(result && result.message){
            $('#pb_backup_action_state').text(result.message);
        } else {
            $('#pb_backup_action_state').text('');
        }
    }
    window.pbBackupLoadList = function(){
        $.ajax({
            type: 'POST',
            url: '../ajax/backup_tools.php',
            dataType: 'json',
            data: 'phpaction=1&token=' + encodeURIComponent(pbBackupToken()) + '&backup_list=1',
            success: function(result){
                pbBackupRenderResponse(result || {});
            },
            error: function(){
                $('#pb_backup_result').html('<div class="alert alert-danger mb-0">Не удалось загрузить список резервных копий.</div>');
            }
        });
    };
    window.pbBackupCreate = function(){
        var mode = $('input[name="pb_backup_mode"]:checked').val() || 'full';
        pbBackupSetBusy('Создаём резервную копию...');
        NProgress.start();
        $.ajax({
            type: 'POST',
            url: '../ajax/backup_tools.php',
            dataType: 'json',
            timeout: 0,
            data: 'phpaction=1&token=' + encodeURIComponent(pbBackupToken()) + '&backup_create=1&backup_mode=' + encodeURIComponent(mode),
            success: function(result){
                NProgress.done();
                pbBackupSetBusy('');
                pbBackupRenderResponse(result || {});
                if(result && String(result.status) === '1'){
                    setTimeout(show_ok, 300);
                } else {
                    setTimeout(show_error, 300);
                }
            },
            error: function(){
                NProgress.done();
                pbBackupSetBusy('');
                $('#pb_backup_action_state').text('Не удалось создать резервную копию.');
                setTimeout(show_error, 300);
            }
        });
    };
    window.pbBackupRestore = function(file){
        if(!confirm('Восстановить резервную копию "' + file + '"? Перед восстановлением будет создан аварийный полный бекап текущего состояния.')){
            return;
        }
        pbBackupSetBusy('Восстанавливаем резервную копию...');
        NProgress.start();
        $.ajax({
            type: 'POST',
            url: '../ajax/backup_tools.php',
            dataType: 'json',
            timeout: 0,
            data: 'phpaction=1&token=' + encodeURIComponent(pbBackupToken()) + '&backup_restore=1&backup_file=' + encodeURIComponent(file),
            success: function(result){
                NProgress.done();
                pbBackupSetBusy('');
                pbBackupRenderResponse(result || {});
                if(result && String(result.status) === '1'){
                    setTimeout(show_ok, 300);
                } else {
                    setTimeout(show_error, 300);
                }
            },
            error: function(){
                NProgress.done();
                pbBackupSetBusy('');
                $('#pb_backup_action_state').text('Не удалось восстановить резервную копию.');
                setTimeout(show_error, 300);
            }
        });
    };
    window.pbBackupDelete = function(file){
        if(!confirm('Удалить резервную копию "' + file + '"?')){
            return;
        }
        pbBackupSetBusy('Удаляем резервную копию...');
        $.ajax({
            type: 'POST',
            url: '../ajax/backup_tools.php',
            dataType: 'json',
            data: 'phpaction=1&token=' + encodeURIComponent(pbBackupToken()) + '&backup_delete=1&backup_file=' + encodeURIComponent(file),
            success: function(result){
                pbBackupSetBusy('');
                pbBackupRenderResponse(result || {});
                if(result && String(result.status) === '1'){
                    setTimeout(show_ok, 300);
                } else {
                    setTimeout(show_error, 300);
                }
            },
            error: function(){
                pbBackupSetBusy('');
                $('#pb_backup_action_state').text('Не удалось удалить резервную копию.');
                setTimeout(show_error, 300);
            }
        });
    };
    $(document).ready(function(){
        pbBackupLoadList();
    });
})();
</script>

<div id="pb-backup-help-modal" class="modal fade pb-admin-help-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Резервные копии — как это работает</h4>
            </div>
            <div class="modal-body">
                <div class="pb-help-grid">
                    <div class="pb-help-card"><b>Полный бекап</b>Сохраняет файлы проекта и базу данных в одном архиве.</div>
                    <div class="pb-help-card"><b>Только движок</b>Сохраняет файлы проекта без базы данных. Подходит перед правками шаблонов, модулей и конфигов.</div>
                    <div class="pb-help-card"><b>Только БД</b>Сохраняет только базу данных без файлов. Подходит перед изменением контента, настроек и таблиц.</div>
                </div>
                <ul class="pb-help-list">
                    <li>При создании бекапа в имя файла автоматически записываются версия движка и время создания.</li>
                    <li>Перед восстановлением система делает аварийный полный бекап текущего состояния.</li>
                    <li>После восстановления очищается кеш, чтобы движок сразу подхватил файлы и настройки из резервной копии.</li>
                    <li>Используйте восстановление только тогда, когда точно понимаете, какой снимок хотите вернуть.</li>
                </ul>
                <div class="pb-help-note"><b>Рекомендация</b>Перед релизом делайте полный бекап. Для шаблонов достаточно режима <b>Только движок</b>, для данных — <b>Только БД</b>.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Понятно</button>
            </div>
        </div>
    </div>
</div>

<script>
function pbOpenUpdateDescription() {
    var token = $('#token').val();
    var box = $('#pb-update-description-content');

    box.html('<div class="pb-update-empty">Загрузка описания обновления...</div>');
    window.pbOpenDrawer('dc-drawer-update-description', {
        icon: 'glyphicon-refresh',
        title: 'Описание обновления',
        subtitle: 'Что изменится после установки'
    });

    $.ajax({
        type: 'POST',
        url: '../ajax/actions_panel.php',
        data: 'phpaction=1&token=' + encodeURIComponent(token) + '&get_update_description=1',
        success: function(html) {
            box.html(html);
        },
        error: function() {
            box.html('<div class="pb-update-empty">Не удалось получить описание обновления. Проверьте сервер обновлений и повторите попытку.</div>');
        }
    });
}

function pbCloseUpdateDescription(instant) {
    window.pbCloseDrawer(instant);
}

function pbOpenPhpInfo() {
    var token = $('#token').val();
    var box = $('#pb-phpinfo-content');

    box.html('<div class="pb-update-empty">Загрузка PHP Info...</div>');
    $('#pb-phpinfo-modal').modal('show');

    $.ajax({
        type: 'POST',
        url: '../ajax/actions_panel.php',
        data: 'phpaction=1&token=' + encodeURIComponent(token) + '&get_phpinfo=1',
        success: function(html) {
            box.html(html);
        },
        error: function() {
            box.html('<div class="pb-update-empty">Не удалось загрузить PHP Info. Обновите страницу и повторите попытку.</div>');
        }
    });
}
</script>

<div id="dc-drawer-update-description" class="dc-drawer-content" style="display:none;">
    <div id="pb-update-description-content">
        <div class="pb-update-empty">Загрузка описания обновления...</div>
    </div>
</div>

<div id="pb-phpinfo-modal" class="modal fade pb-admin-help-modal pb-phpinfo-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">PHP Info</h4>
            </div>
            <div class="modal-body">
                <div class="pb-phpinfo-frame" id="pb-phpinfo-content">
                    <div class="pb-update-empty">Загрузка PHP Info...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    var DRAWER_META = {
        'dc-drawer-system': { icon: 'glyphicon-cog', title: 'Управление сайтом', subtitle: 'Выключение сайта и режим разработчика' },
        'dc-drawer-backup': { icon: 'glyphicon-save-file', title: 'Бекап движка', subtitle: 'Резервные копии перед настройкой и обновлениями' },
        'dc-drawer-updates': { icon: 'glyphicon-cloud-download', title: 'Центр обновлений', subtitle: 'Версия движка, сервер обновления и лицензия' },
        'dc-drawer-update-description': { icon: 'glyphicon-refresh', title: 'Описание обновления', subtitle: 'Что изменится после установки' }
    };

    var overlay = document.getElementById('dc-drawer-overlay');
    var drawer = document.getElementById('dc-drawer');
    var drawerBody = document.getElementById('dc-drawer-body');
    var drawerIcon = document.getElementById('dc-drawer-icon');
    var drawerTitle = document.getElementById('dc-drawer-title');
    var drawerSubtitle = document.getElementById('dc-drawer-subtitle');
    var currentContentId = null;
    var currentHome = null;

    function openDrawer(contentId, metaOverride){
        if (!drawer || !overlay) { return; }
        var content = document.getElementById(contentId);
        if (!content) { return; }

        closeDrawer(true);

        currentContentId = contentId;
        currentHome = document.createComment('dc-drawer-home:' + contentId);
        content.parentNode.insertBefore(currentHome, content);
        drawerBody.appendChild(content);
        content.style.display = 'block';

        var meta = metaOverride || DRAWER_META[contentId] || {};
        drawerIcon.innerHTML = '<span class="glyphicon ' + (meta.icon || 'glyphicon-cog') + '"></span>';
        drawerTitle.textContent = meta.title || '';
        drawerSubtitle.textContent = meta.subtitle || '';

        document.body.style.overflow = 'hidden';
        overlay.classList.add('active');
        drawer.classList.add('active');
    }

    function closeDrawer(instant){
        if (!drawer || !overlay) { return; }
        overlay.classList.remove('active');
        drawer.classList.remove('active');
        document.body.style.overflow = '';

        var finish = function(){
            if (currentContentId && currentHome && currentHome.parentNode) {
                var content = document.getElementById(currentContentId);
                if (content) {
                    content.style.display = 'none';
                    currentHome.parentNode.insertBefore(content, currentHome);
                }
                currentHome.parentNode.removeChild(currentHome);
            }
            currentContentId = null;
            currentHome = null;
        };

        if (instant) { finish(); }
        else { setTimeout(finish, 260); }
    }

    window.pbOpenDrawer = openDrawer;
    window.pbCloseDrawer = closeDrawer;

    document.querySelectorAll('.dc-hub-tile').forEach(function(tile){
        tile.addEventListener('click', function(){
            openDrawer(tile.getAttribute('data-drawer'));
        });
    });

    var closeBtn = document.getElementById('dc-drawer-close');
    if (closeBtn) { closeBtn.addEventListener('click', function(){ closeDrawer(false); }); }
    if (overlay) { overlay.addEventListener('click', function(){ closeDrawer(false); }); }
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && drawer && drawer.classList.contains('active')) { closeDrawer(false); }
    });
})();
</script>

<script>
(function(){
    var CACHE_KEY = 'pb_dev_center_update_check';
    var CACHE_TTL = 5 * 60 * 1000;

    function applyUpdateState(result){
        var tile = document.querySelector('.dc-hub-tile--updates');
        var alertBox = document.getElementById('dc-update-alert');
        var alertVersion = document.getElementById('dc-update-alert-version');

        if (result && result.has_update) {
            if (alertVersion) { alertVersion.textContent = result.new_version ? ('v' + result.new_version) : ''; }
            if (alertBox) { alertBox.style.display = 'flex'; }
            if (tile) { tile.classList.add('dc-hub-tile--glow'); }
        } else {
            if (alertBox) { alertBox.style.display = 'none'; }
            if (tile) { tile.classList.remove('dc-hub-tile--glow'); }
        }
    }

    function readCache(){
        try {
            var raw = sessionStorage.getItem(CACHE_KEY);
            if (!raw) { return null; }
            var parsed = JSON.parse(raw);
            if (!parsed || (Date.now() - parsed.ts) > CACHE_TTL) { return null; }
            return parsed.result;
        } catch(e) { return null; }
    }

    function writeCache(result){
        try {
            sessionStorage.setItem(CACHE_KEY, JSON.stringify({ ts: Date.now(), result: result }));
        } catch(e) {}
    }

    function pbDevCenterCheckUpdate(){
        var token = $('#token').val();
        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: 'phpaction=1&token=' + encodeURIComponent(token) + '&get_main_info=1',
            dataType: 'json',
            timeout: 12000,
            success: function(result){
                if (!result) { return; }
                if (typeof result.version !== 'undefined') {
                    $('#version').html(result.version);
                }
                if (result.message) {
                    $('#message').html(result.message);
                    $('#message').fadeIn();
                }
                applyUpdateState(result);
                writeCache(result);
            }
        });
    }

    window.get_main_info = pbDevCenterCheckUpdate;

    var cached = readCache();
    if (cached) {
        applyUpdateState(cached);
    }
    pbDevCenterCheckUpdate();
})();
</script>

<script>
(function(){
    if (window.PBDevCenterQuickAccessReady) return;
    window.PBDevCenterQuickAccessReady = true;

    var MAX_ITEMS = 5;
    var dangerousActions = {
        dell_all_chat_messages: 'Очистить все сообщения чата?',
        dell_all_bid_tickets: 'Удалить все тикеты и ответы?',
        dell_all_bid_bans: 'Удалить все заявки на разбан и комментарии?',
        dell_all_bid_complains: 'Удалить все жалобы и комментарии?'
    };

    function getCatalog() {
        var node = document.getElementById('dcQuickAccessCatalog');
        if (!node) return {};
        try {
            var data = JSON.parse(node.textContent || '{}');
            return data && typeof data === 'object' ? data : {};
        } catch(e) { return {}; }
    }

    function getInitialItems() {
        var node = document.getElementById('dcQuickAccessData');
        if (!node) return [];
        try {
            var data = JSON.parse(node.textContent || '[]');
            return Array.isArray(data) ? data : [];
        } catch(e) { return []; }
    }

    var presets = getCatalog();
    var quickSiteHost = '{site_host}'.replace(/\/$/, '');

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
        var box = document.getElementById('dcQuickAccessRows');
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
            if (urlEl) { urlEl.value = ''; urlEl.disabled = true; }
            return {label: label, type: preset.type, value: preset.value, icon: preset.icon};
        }

        if (urlEl) urlEl.disabled = false;
        return {label: label, type: 'link', value: urlEl ? urlEl.value.trim() : '', icon: 'link'};
    }

    function collectRows() {
        var rows = document.querySelectorAll('#dcQuickAccessRows .admin-quick-row');
        var items = [];
        rows.forEach(function(row) {
            var item = normalizeRow(row);
            if (item.value) items.push(item);
        });
        return items.slice(0, MAX_ITEMS);
    }

    function setResult(message, isError) {
        var out = document.getElementById('dcQuickAccessResult');
        if (out) out.innerHTML = '<p class="' + (isError ? 'text-danger' : 'text-success') + '">' + escapeHtml(message) + '</p>';
    }

    function quickHref(value) {
        value = String(value || '').trim();
        if (/^https?:\/\//i.test(value) || value.indexOf('/') === 0 || value.indexOf('./') === 0 || value.indexOf('../') === 0) {
            return value;
        }
        return quickSiteHost + '/' + value.replace(/^\/+/, '');
    }

    function tileHtml(item) {
        var label = escapeHtml(item.label);
        var icon = escapeHtml(item.icon || 'link');
        if (item.type === 'action') {
            return '<button type="button" class="dc-qa-tile" data-pb-admin-action="' + escapeHtml(item.value) + '" title="' + label + '">' +
                '<span class="dc-qa-tile__icon glyphicon glyphicon-' + icon + '"></span>' +
                '<span class="dc-qa-tile__title">' + label + '</span>' +
            '</button>';
        }
        return '<a class="dc-qa-tile" href="' + escapeHtml(quickHref(item.value)) + '" title="' + label + '">' +
            '<span class="dc-qa-tile__icon glyphicon glyphicon-' + icon + '"></span>' +
            '<span class="dc-qa-tile__title">' + label + '</span>' +
        '</a>';
    }

    function renderGrid(items) {
        var grid = document.getElementById('dcQuickAccessGrid');
        var empty = document.getElementById('dcQuickAccessEmpty');
        if (!grid) return;
        if (items && items.length) {
            grid.innerHTML = items.map(tileHtml).join('');
            grid.style.display = '';
            if (empty) empty.style.display = 'none';
        } else {
            grid.innerHTML = '';
            grid.style.display = 'none';
            if (empty) empty.style.display = '';
        }
    }

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

        if (event.target.closest('#dcQuickAddRow')) {
            var box = document.getElementById('dcQuickAccessRows');
            if (box && box.querySelectorAll('.admin-quick-row').length < MAX_ITEMS) {
                box.insertAdjacentHTML('beforeend', rowHtml({label: 'Быстрый доступ', type: 'link', value: '', icon: 'link'}));
            }
            return;
        }

        if (event.target.closest('[data-action="remove"]')) {
            var row = event.target.closest('.admin-quick-row');
            if (row) row.parentNode.removeChild(row);
            return;
        }

        if (event.target.closest('#dcQuickSave')) {
            var token = document.getElementById('token') ? document.getElementById('token').value : '';
            var items = collectRows();
            if (!items.length) {
                setResult('Добавьте хотя бы один пункт.', true);
                return;
            }
            $.ajax({
                type: 'POST',
                url: '../ajax/admin_dev_center_quick_access.php',
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
                        renderGrid(result.items || items);
                        if (typeof show_ok === 'function') setTimeout(show_ok, 200);
                        setTimeout(function(){ $('#dcQuickAccessModal').modal('hide'); }, 500);
                    } else {
                        setResult(result && result.message ? result.message : 'Не удалось сохранить.', true);
                        if (typeof show_error === 'function') setTimeout(show_error, 200);
                    }
                },
                error: function() {
                    setResult('Ошибка соединения при сохранении.', true);
                    if (typeof show_error === 'function') setTimeout(show_error, 200);
                }
            });
        }
    });

    renderRows(getInitialItems());
})();
</script>

<script>
(function(){
    if (window.PBDcLoginsReady) { return; }
    window.PBDcLoginsReady = true;

    var body = document.getElementById('dcLoginsBody');
    var moreBtn = document.getElementById('dcLoginsMore');
    var clearBtn = document.getElementById('dcLoginsClear');
    var emptyBox = document.getElementById('dcLoginsEmpty');
    var countBox = document.getElementById('dcLoginsCount');
    var resultBox = document.getElementById('dcLoginsResult');
    if (!body) { return; }

    var offset = 0;
    var loaded = 0;

    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function(ch) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch];
        });
    }

    function ajax(data, done) {
        data.phpaction = 1;
        data.token = document.getElementById('token') ? document.getElementById('token').value : '';
        $.ajax({
            type: 'POST',
            url: '{site_host}ajax/actions_panel.php',
            data: data,
            dataType: 'json',
            success: done,
            error: function(){ done(null); }
        });
    }

    function render(rows) {
        rows.forEach(function(r) {
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td><b>' + esc(r.login) + '</b></td>' +
                '<td><code>' + esc(r.ip) + '</code></td>' +
                '<td>' +
                    (r.flag
                        ? '<span class="dc-geo-cell"><img class="dc-geo-cell__flag" src="..' + esc(r.flag) + '" alt="' + esc(r.iso) + '" title="' + esc(r.iso) + '">' + esc(r.place) + '</span>'
                        : '<span class="dc-geo-cell"><span class="dc-geo-cell__flag dc-geo-cell__flag--empty"></span>' + esc(r.place) + '</span>') +
                '</td>' +
                '<td>' + esc(r.date) + '</td>' +
                '<td>' + esc(r.time) + '</td>' +
                '<td>' + (r.current ? '<span class="dc-badge dc-badge--success">текущая</span>' : '<code>' + esc(r.session) + '</code>') + '</td>';
            body.appendChild(tr);
        });
    }

    function load(reset) {
        if (reset) { offset = 0; loaded = 0; body.innerHTML = ''; }
        ajax({ pb_admin_logins_list: 1, offset: offset }, function(res) {
            if (!res || String(res.status) !== '1') {
                if (resultBox) { resultBox.innerHTML = '<p class="text-danger m-0">' + ((res && res.data) || 'Не удалось загрузить журнал') + '</p>'; }
                return;
            }
            render(res.rows || []);
            loaded += (res.rows || []).length;
            offset = loaded;

            if (emptyBox) { emptyBox.style.display = res.total > 0 ? 'none' : ''; }
            if (moreBtn) { moreBtn.style.display = res.has_more ? '' : 'none'; }
            if (countBox) { countBox.textContent = res.total > 0 ? ('Показано ' + loaded + ' из ' + res.total) : ''; }
        });
    }

    if (moreBtn) { moreBtn.addEventListener('click', function(){ load(false); }); }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            if (!confirm('Очистить весь журнал входов в админку?')) { return; }
            ajax({ pb_admin_logins_clear: 1 }, function(res) {
                var ok = res && String(res.status) === '1';
                if (resultBox) {
                    resultBox.innerHTML = '<p class="' + (ok ? 'text-success' : 'text-danger') + ' m-0">' +
                        ((res && res.data) || (ok ? 'Готово' : 'Ошибка')) + '</p>';
                }
                if (ok) {
                    load(true);
                    if (typeof show_ok === 'function') { setTimeout(show_ok, 150); }
                } else if (typeof show_error === 'function') {
                    setTimeout(show_error, 150);
                }
            });
        });
    }

    load(true);
})();
</script>

<script>$('#safe_mode').modal('hide');</script>
<div id="safe_mode" class="modal fade bd-example-modal-lg pb-admin-help-modal" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-lg" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">×</span></button><h4 class="modal-title">Безопасный режим — что это значит</h4></div><div class="modal-body"><p>Безопасный режим ограничивает вставку PHP-кода в редакторе страниц, редакторе шаблонов и других компонентах админцентра. В этом режиме логика шаблонов должна выполняться через встроенный шаблонизатор PBGame CMS.</p><ul class="pb-help-list"><li>Включенный режим снижает риск случайного запуска опасного PHP-кода из админцентра.</li><li>Для установки собственных модулей и некоторых технических работ режим иногда нужно временно отключить.</li><li>После завершения работ безопасный режим рекомендуется вернуть обратно.</li></ul><div class="pb-help-note"><b>Файл настройки:</b> <code>inc/config.php</code><br><code>$safe_mode = 1;</code> — режим включен<br><code>$safe_mode = 2;</code> — режим выключен</div></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Понятно</button></div></div></div></div>
