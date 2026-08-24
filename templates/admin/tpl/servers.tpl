<div class="page">

    <div class="dc-wrap dc-wrap--single">
    <div class="dc-col">

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon"><span class="glyphicon glyphicon-hdd"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Серверы и мониторинг</div>
                        <div class="dc-card__subtitle">Режимы игры, добавление серверов и настройки мониторинга</div>
                    </div>
                </div>
                <span class="dc-card__badge">4 раздела</span>
            </div>
            <div class="dc-card__body">
                <div class="dc-p2p-menu dc-p2p-menu--srv">
                    <button type="button" class="dc-p2p-tile dc-p2p-tile--create" data-drawer="srv-drawer-modes">
                        <span class="dc-p2p-tile__icon"><span class="glyphicon glyphicon-th-large"></span></span>
                        <div class="dc-p2p-tile__title">Режимы игры</div>
                        <div class="dc-p2p-tile__hint">Название, описание, фон и персонаж каждого режима</div>
                        <span class="dc-p2p-tile__badge dc-p2p-tile__badge--success" id="srv_modes_badge">0</span>
                    </button>
                    <button type="button" class="dc-p2p-tile dc-p2p-tile--settings" data-drawer="srv-drawer-monitoring">
                        <span class="dc-p2p-tile__icon"><span class="glyphicon glyphicon-signal"></span></span>
                        <div class="dc-p2p-tile__title">Настройки мониторинга</div>
                        <div class="dc-p2p-tile__hint">Интервал обновления и внешний сервер мониторинга</div>
                    </button>
                    <button type="button" class="dc-p2p-tile dc-p2p-tile--methods" data-drawer="srv-drawer-add">
                        <span class="dc-p2p-tile__icon"><span class="glyphicon glyphicon-plus"></span></span>
                        <div class="dc-p2p-tile__title">Добавить сервер</div>
                        <div class="dc-p2p-tile__hint">Основные, дополнительные настройки и статистика</div>
                    </button>
                    <button type="button" class="dc-p2p-tile dc-p2p-tile--requests" data-drawer="srv-drawer-list">
                        <span class="dc-p2p-tile__icon"><span class="glyphicon glyphicon-list"></span></span>
                        <div class="dc-p2p-tile__title">Список серверов</div>
                        <div class="dc-p2p-tile__hint">Редактирование и удаление добавленных серверов</div>
                    </button>
                    <button type="button" class="dc-p2p-tile dc-p2p-tile--rcon" data-drawer="srv-drawer-rcon">
                        <span class="dc-p2p-tile__icon"><span class="glyphicon glyphicon-console"></span></span>
                        <div class="dc-p2p-tile__title">RCON</div>
                        <div class="dc-p2p-tile__hint">Подключение, команды и консоль сервера</div>
                    </button>
                </div>
            </div>
        </div>

    </div>
    </div>

</div>

<div id="srv-drawer-modes" class="dc-drawer-content" style="display:none;">
    <div class="dc-drawer-section">
        <div class="dc-drawer-section__head">
            <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-th-large"></span></span>
            <span class="dc-drawer-section__title">Режимы игры</span>
        </div>
        <div class="dc-hint" style="margin-top:0;margin-bottom:14px;">Режимы выводятся на главной странице сайта — те, у которых наименьший порядок сортировки. Изображения сохраняются в <code>templates/solution2/img/server_pbg</code>.</div>

        <div class="dc-event-row" style="margin-bottom:14px;">
            <div class="dc-event-row__main">
                <span class="dc-event-row__icon"><span class="glyphicon glyphicon-th"></span></span>
                <div>
                    <div class="dc-event-row__label">Сколько режимов показывать на главной</div>
                    <div class="dc-event-row__hint">3 — обычная сетка. 1 — один режим растянут на всю ширину, выглядит масштабнее.</div>
                </div>
            </div>
            <div class="dc-toggle dc-toggle--onoff dc-toggle--compact" data-toggle="buttons" id="pb_smode_slots_toggle">
                <label data-slots="3" onclick="pbSmodeSetSlots(3);">3 режима</label>
                <label data-slots="1" onclick="pbSmodeSetSlots(1);">1 режим</label>
            </div>
        </div>

        <div class="dc-version-actions" style="margin-bottom:14px;">
            <button type="button" class="btn btn-primary" onclick="pbSmodeOpenForm(0);" style="border-radius:8px;font-weight:700;">
                <span class="glyphicon glyphicon-plus"></span> Создать режим
            </button>
        </div>

        <div id="pb_smodes_list"></div>
    </div>

    <div class="dc-drawer-section" id="pb_smode_form_section" style="display:none;">
        <div class="dc-drawer-section__head">
            <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-edit"></span></span>
            <span class="dc-drawer-section__title" id="pb_smode_form_title">Новый режим</span>
        </div>

        <input type="hidden" id="pb_smode_id" value="0">

        <div class="dc-form-grid">
            <div class="dc-form-col-6 dc-form-field">
                <label>Название режима</label>
                <div class="dc-field"><input type="text" id="pb_smode_name" maxlength="64" placeholder="PUBLIC"></div>
            </div>
            <div class="dc-form-col-3 dc-form-field">
                <label>Порядок</label>
                <div class="dc-field"><input type="number" id="pb_smode_sort" value="0" min="0" max="999"></div>
            </div>
            <div class="dc-form-col-3 dc-form-field">
                <label>Отображение</label>
                <div class="dc-field">
                    <select id="pb_smode_active">
                        <option value="1">Показывать</option>
                        <option value="2">Скрывать</option>
                    </select>
                </div>
            </div>
            <div class="dc-form-col-12 dc-form-field">
                <label>Описание режима</label>
                <div class="dc-field"><input type="text" id="pb_smode_description" maxlength="512" placeholder="Краткое описание режима для главной страницы"></div>
            </div>
        </div>

        <div class="dc-grid2" style="margin-top:12px;">
            <div class="dc-tile">
                <div class="dc-tile__head">
                    <span class="dc-tile__icon"><span class="glyphicon glyphicon-picture"></span></span>
                    <div>
                        <div class="dc-tile__label">Фон режима</div>
                        <div class="dc-tile__hint">Широкое изображение для шапки режима</div>
                    </div>
                </div>
                <div class="pb-smode-preview"><img id="pb_smode_bg_preview" src="/templates/solution2/img/server_pbg/none.svg" alt=""></div>
                <input type="file" id="pb_smode_bg" accept="image/*" class="pb-smode-file">
            </div>

            <div class="dc-tile">
                <div class="dc-tile__head">
                    <span class="dc-tile__icon"><span class="glyphicon glyphicon-user"></span></span>
                    <div>
                        <div class="dc-tile__label">Персонаж режима</div>
                        <div class="dc-tile__hint">Вертикальное изображение для карточки на главной</div>
                    </div>
                </div>
                <div class="pb-smode-preview"><img id="pb_smode_char_preview" src="/templates/solution2/img/server_pbg/none.svg" alt=""></div>
                <input type="file" id="pb_smode_char" accept="image/*" class="pb-smode-file">
            </div>
        </div>

        <div class="dc-version-actions" style="margin-top:14px;">
            <button type="button" class="btn btn-primary" onclick="pbSmodeSave();" style="border-radius:8px;font-weight:700;">
                <span class="glyphicon glyphicon-ok"></span> Сохранить режим
            </button>
            <button type="button" class="btn btn-default" onclick="pbSmodeCloseForm();" style="border-radius:8px;font-weight:700;">
                Отмена
            </button>
        </div>
    </div>
</div>

<div id="srv-drawer-monitoring" class="dc-drawer-content" style="display:none;">
    <div class="dc-drawer-section">
        <div class="dc-drawer-section__head">
            <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-signal"></span></span>
            <span class="dc-drawer-section__title">Настройки мониторинга</span>
        </div>

        <div class="dc-event-row">
            <div class="dc-event-row__main">
                <span class="dc-event-row__icon"><span class="glyphicon glyphicon-cloud"></span></span>
                <div>
                    <div class="dc-event-row__label">Внешний сервер мониторинга</div>
                    <div class="dc-event-row__hint">Используйте, если на вашем хостинге не работает стандартный мониторинг. Снижает нагрузку и увеличивает быстродействие сайта.</div>
                </div>
            </div>
            <div class="dc-toggle dc-toggle--onoff dc-toggle--compact" data-toggle="buttons">
                <label class="{api_active}" onclick="edit_mon_api('1');">Вкл</label>
                <label class="{api_active2}" onclick="edit_mon_api('2');">Выкл</label>
            </div>
        </div>

        <input type="hidden" id="mon_key" value="https://pbgame.top/pbmonitor/monitor.php">

        <div class="dc-form-grid" style="margin-top:12px;">
            <div class="dc-form-col-12 dc-form-field">
                <label>Интервал обновления мониторинга (в секундах)</label>
                <div class="dc-field">
                    <input type="number" id="mon_gap" maxlength="5" autocomplete="off" value="{mon_gap}" {if('{api_active}' == 'active')}disabled{/if}>
                    <button type="button" class="dc-field__btn" id="btn_mon_gap" onclick="edit_mon_gap();" {if('{api_active}' == 'active')}disabled{/if}>Изменить</button>
                </div>
            </div>
        </div>

        <div class="dc-hint" style="margin-top:10px;">Рекомендуемые значения интервала: 60, 120 или 180 секунд. При включённом внешнем мониторинге интервал задаётся на стороне сервиса.</div>
    </div>
</div>

<div id="srv-drawer-add" class="dc-drawer-content" style="display:none;">
    <div class="dc-drawer-section">
        <div class="dc-drawer-section__head">
            <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-plus"></span></span>
            <span class="dc-drawer-section__title">Добавить сервер</span>
        </div>

        <div class="servers_options">
        <div class="row">
            <div class="col-md-4 mb-10">
                <b>Основные настройки</b>
                <div class="form-group">
                    <small>Название</small>
                    <input type="text" placeholder="Название сервера" class="form-control" id="name" maxlength="255" autocomplete="off">
                </div>
                <div class="form-group">
                    <small>IP</small>
                    <input type="text" placeholder="IP адрес сервера" class="form-control" id="ip" maxlength="30" autocomplete="off">
                </div>
                <div class="form-group">
                    <small>Port</small>
                    <input type="text" placeholder="Port сервера" class="form-control" id="port" maxlength="5" autocomplete="off">
                </div>
                <div class="form-group">
                    <small>Отображаемый адрес</small>
                    <input type="text" placeholder="IP:Port" class="form-control" id="address" maxlength="255" autocomplete="off">
                </div>
                <div class="form-group">
                    <small>Скидка на услуги в %</small>
                    <input value="0" placeholder="От 0 до 99" type="number" class="form-control" id="discount" maxlength="2" autocomplete="off">
                </div>
                <div class="form-group">
                    <small>Отображение в мониторинге</small>
                    <select class="form-control" id="show">
                        <option value="1" selected>Показывать</option>
                        <option value="2">Скрывать</option>
                    </select>
                </div>
                <div class="form-group">
                    <small>Режим сервера</small>
                    <select class="form-control" id="mode_id">
                        {modes_options}
                    </select>
                </div>
                <div class="form-group disp-n" id="import">
                    <small>Импортировать админов, услуги и тарифы</small>
                    <select class="form-control" id="import_settings">
                        {servers}
                    </select>
                </div>
                <div class="form-group">
                    <small>Способы привязки услуг</small>
                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default btn-sm active" id="bind_nick_pass_btn" for="bind_nick_pass">
                            <input type="checkbox" id="bind_nick_pass" autocomplete="off"> Ник + пароль
                        </label>
                        <label class="btn btn-default btn-sm active" id="bind_steam_btn" for="bind_steam">
                            <input type="checkbox" id="bind_steam" autocomplete="off"> STEAM ID
                        </label>
                    </div>

                    <script>
                        $("#bind_nick_pass").prop("checked", true);
                        $("#bind_steam").prop("checked", true);
                    </script>
                </div>
            </div>
            <div class="col-md-4 mb-10">
                <b>Дополнительные настройки</b>
                <div class="form-group">
                    <small>Интеграция</small>
                    <script>
                    function local_change_type(id) {
                        var type = $('#type'+id+' option:selected').val();
                        select_serv_type(type, id);
                    }
                    </script>
                    <select class="form-control" id="type" onchange="local_change_type('');">
                        <option id="opt0_" value="0">Нет настроек</option>
                        <option id="opt1_" value="1">Файл (Users.ini)</option>
                        <option id="opt2_" value="2">AmxBans/CsBans</option>
                        <option id="opt3_" value="3">AmxBans/CsBans + файл</option>
                        <option id="opt4_" class="disp-n" value="4">SourceBans/MaterialAdmin</option>
                        <option id="opt5_" value="5">AmxBans/CsBans + PBGame CMS API</option>
                    </select>
                </div>
                <div id="none_">
                    <div class="bs-callout bs-callout-info bs-callout-sm mt-5">
                        <p>Чтение/запись привилегий и банов не осуществляется</p>
                    </div>
                </div>
                <div id="tip1_" class="disp-n">
                    <div class="bs-callout bs-callout-info bs-callout-sm mt-5">
                        <h4>Поддержка: привилегии</h4>
                        <p>Для чтения/записи привилегий используется файл</p>
                    </div>
                </div>
                <div id="tip2_" class="disp-n">
                    <div class="bs-callout bs-callout-info bs-callout-sm mt-5">
                        <h4>Поддержка: привилегии и баны</h4>
                        <p>Для чтения/записи банов и привилегий используется база данных от AmxBans/CsBans
                        <br />
                        <br />
                        Для это типа интеграции необходима база данных от <a href="https://pbgame.top">CsBans</a></p>
                    </div>
                </div>
                <div id="tip3_" class="disp-n">
                    <div class="bs-callout bs-callout-info bs-callout-sm mt-5">
                        <h4>Поддержка: привилегии и баны</h4>
                        <p>Для чтения/записи привилегий используется файл, для чтения/записи банов используется база данных от AmxBans/CsBans
                        <br />
                        <br />
                        Для это типа интеграции необходима база данных от <a href="https://pbgame.top">CsBans</a></p>
                    </div>
                </div>
                <div id="tip4_" class="disp-n">
                    <div class="bs-callout bs-callout-info bs-callout-sm mt-5">
                        <h4>Поддержка: привилегии и баны</h4>
                        <p>Для чтения/записи банов и привилегий используется база данных от SourceBans/<a href="https://github.com/SB-MaterialAdmin" target="_blank">MaterialAdmin</a></p>
                    </div>
                </div>
                <div id="tip5_" class="disp-n">
                    <div class="bs-callout bs-callout-info bs-callout-sm mt-5">
                        <h4>Поддержка: привилегии и баны</h4>
                        <p>Для чтения/записи привилегий используется база данных текущего сайта, для чтения/записи банов используется база данных от AmxBans/CsBans. Данный тип интеграции требует установку плагина PBGame CMS API (amx) на игровой сервер
<br />
<br />
Актуальная версия плагина: <a href="https://pbgame.top">Скачать</a></p>
                    </div>
                </div>
                <div id="tip6_" class="disp-n">
                    <div class="bs-callout bs-callout-info bs-callout-sm mt-5">
                        <h4>Поддержка: привилегии и баны</h4>
                        <p>Для чтения/записи привилегий используется база данных текущего сайта, для чтения/записи банов используется база данных от SourceBans/<a href="https://github.com/SB-MaterialAdmin" target="_blank">MaterialAdmin</a>. Данный тип интеграции требует установку плагина PBGame CMS API (sm) на игровой сервер</p>
                    </div>
                </div>
                <div id="auth_prefix" class="disp-n">
                    <div class="form-group">
                        <small>Префикс для авторизации админа на сервере</small>
                        <input type="text" class="form-control" id="pass_prifix" maxlength="10" autocomplete="off" placeholder="_pw">
                    </div>
                </div>
                <div id="ftp" class="disp-n">
                    <div class="form-group">
                        <small>FTP хост</small>
                        <input type="text" class="form-control" id="ftp_host" maxlength="64" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <small>FTP порт</small>
                        <input type="text" class="form-control" id="ftp_port" maxlength="5" autocomplete="off" placeholder="По умлочанию: 21">
                    </div>
                    <div class="form-group">
                        <small>FTP логин</small>
                        <input type="text" class="form-control" id="ftp_login" maxlength="32" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <small>FTP пароль</small>
                        <input type="password" class="form-control" id="ftp_pass" maxlength="32" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <small>Путь до файла (Пример: cstrike/addons/amxmodx/configs/users.ini)</small>
                        <input type="text" class="form-control" id="ftp_string" maxlength="255" autocomplete="off">
                    </div>
                </div>
                <div id="db" class="disp-n">
                    <div class="form-group">
                        <small>db хост</small>
                        <input type="text" class="form-control" id="db_host" maxlength="64" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <small>db логин</small>
                        <input type="text" class="form-control" id="db_user" maxlength="32" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <small>db пароль</small>
                        <input type="password" class="form-control" id="db_pass" maxlength="32" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <small>db база</small>
                        <input type="text" class="form-control" id="db_db" maxlength="32" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <small>db префикс</small>
                        <input type="text" class="form-control" id="db_prefix" maxlength="32" autocomplete="off" placeholder="amx / cs / gm / sb">
                    </div>
                    <div class="form-group">
                        <small>Кодировка</small>
                        <select class="form-control" id="db_code">
                            <option value="0">Определять автоматически</option>
                            <option value="1">utf-8</option>
                            <option value="2">latin1</option>
                            <option value="3">utf8mb4</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-10">
                <b>Настройки статистики</b>
                <div class="form-group">
                    <small>Интеграция</small>
                    <script>
                    function local_change_type_st(id) {
                        var st_type = $('#st_type'+id+' option:selected').val();
                        select_stat_type(st_type, id);
                    }
                    </script>
                    <select class="form-control" id="st_type" onchange="local_change_type_st('');">
                        <option id="st_opt0_" value="0">Нет настроек</option>
                        <option id="st_opt1_" value="1">CsStats MySQL</option>
                        <option id="st_opt2_" value="2">Army Ranks Ultimate</option>
                        <option id="st_opt3_" value="3">CSstatsX SQL</option>
                        <option id="st_opt4_" value="4">HLstatsX:CE</option>
                        <option id="st_opt5_" class="disp-n" value="5">RankMe</option>
                        <option id="st_opt6_" class="disp-n" value="6">Level Rank</option>
                        <option id="st_opt7_" value="7">Rank System Ultimate (BETA)</option>
                    </select>
                </div>
                <div id="st_none_">
                    <div class="bs-callout bs-callout-info bs-callout-sm mt-5">
                        <p>Статистика данного сервера не будет отображаться на сайте</p>
                    </div>
                </div>
                <div id="st_tip1_" class="disp-n">
                    <div class="form-group">
                        <small>db хост</small>
                        <input type="text" class="form-control" id="st_db_host" maxlength="64" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <small>db логин</small>
                        <input type="text" class="form-control" id="st_db_user" maxlength="32" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <small>db пароль</small>
                        <input type="password" class="form-control" id="st_db_pass" maxlength="32" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <small>db база</small>
                        <input type="text" class="form-control" id="st_db_db" maxlength="32" autocomplete="off">
                    </div>
                    <div class="form-group" id="st_db_table_input">
                        <small>db таблица</small>
                        <input type="text" class="form-control" id="st_db_table" maxlength="32" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <small>Кодировка</small>
                        <select class="form-control" id="st_db_code">
                            <option value="0">Определять автоматически</option>
                            <option value="1">utf-8</option>
                            <option value="2">latin1</option>
                            <option value="3">utf8mb4</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <small>Сортировка</small>
                        <select class="form-control" id="st_sort_type">
                            <option value="0">Убийства - смерти - teamkills</option>
                            <option value="1">Убийства</option>
                            <option value="2">Убийства + headshods</option>
                            <option value="3">Skill</option>
                            <option value="4">Время онлайн</option>
                            <option value="5">Место</option>
                            <option value="6">Продвинутая</option>
                            <option value="7">Ранг</option>
                            <option value="8">Очки</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div id="add_serv_result"></div>
                <button onclick="server('add');" type="button" class="btn btn-primary" style="border-radius:8px;font-weight:700;">
                    <span class="glyphicon glyphicon-ok"></span> Создать сервер
                </button>
            </div>
        </div>
        </div>
    </div>
</div>

<div id="srv-drawer-rcon" class="dc-drawer-content" style="display:none;">
    <div class="dc-drawer-section">
        <div class="dc-drawer-section__head">
            <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-console"></span></span>
            <span class="dc-drawer-section__title">Выбор сервера</span>
        </div>
        <div class="dc-tile dc-span2" style="margin-bottom:0;">
            <div class="dc-tile__head">
                <span class="dc-tile__icon"><span class="glyphicon glyphicon-hdd"></span></span>
                <div>
                    <div class="dc-tile__label">Игровой сервер</div>
                    <div class="dc-tile__hint">Выберите сервер, чтобы настроить RCON и отправлять команды.</div>
                </div>
            </div>
            <div class="dc-field">
                <select id="rcon_server_select" onchange="pbRconSelectServer(this.value);">
                    <option value="0">— выберите сервер —</option>
                    {rcon_servers_options}
                </select>
            </div>
        </div>
    </div>

    <div id="rcon_panel_body" style="display:none;">
        <div class="dc-drawer-section">
            <div class="dc-drawer-section__head">
                <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-signal"></span></span>
                <span class="dc-drawer-section__title">Статус подключения</span>
            </div>
            <div class="dc-state" id="rcon_status_state" style="margin-bottom:14px;">
                <span class="dc-state__icon glyphicon glyphicon-question-sign" id="rcon_status_icon"></span>
                <div>
                    <b id="rcon_status_text">Не проверено</b>
                    <small id="rcon_status_hint">Нажмите «Проверить соединение», чтобы протестировать RCON.</small>
                </div>
            </div>
            <div class="dc-version-actions">
                <button type="button" class="btn btn-default" id="rcon_test_btn" onclick="pbRconTestConnection();" style="border-radius:8px;font-weight:700;">
                    <span class="glyphicon glyphicon-refresh"></span> Проверить соединение
                </button>
            </div>
        </div>

        <div class="dc-drawer-section">
            <div class="dc-drawer-section__head">
                <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-cog"></span></span>
                <span class="dc-drawer-section__title">Настройка RCON</span>
            </div>
            <div class="dc-event-row" style="margin-bottom:14px;">
                <div class="dc-event-row__main">
                    <span class="dc-event-row__icon"><span class="glyphicon glyphicon-flash"></span></span>
                    <div>
                        <div class="dc-event-row__label">RCON включён</div>
                        <div class="dc-event-row__hint">Отправка команд и приём консольных ответов от сервера.</div>
                    </div>
                </div>
                <div class="dc-toggle dc-toggle--onoff dc-toggle--compact" data-toggle="buttons" id="rcon_enabled_toggle">
                    <label data-value="1" onclick="pbRconSetEnabled(1);">Вкл</label>
                    <label data-value="2" onclick="pbRconSetEnabled(2);">Выкл</label>
                </div>
            </div>
            <div class="dc-grid2">
                <div class="dc-tile dc-span2">
                    <div class="dc-tile__head">
                        <span class="dc-tile__icon"><span class="glyphicon glyphicon-lock"></span></span>
                        <div>
                            <div class="dc-tile__label">RCON пароль сервера</div>
                            <div class="dc-tile__hint">Задаётся в конфиге сервера (rcon_password). Кавычки " в пароле не рекомендуются.</div>
                        </div>
                    </div>
                    <div class="dc-field">
                        <input type="password" id="rcon_password_input" autocomplete="off" maxlength="256" placeholder="Rcon пароль сервера">
                        <button type="button" class="dc-field__btn" onclick="pbRconTogglePasswordVisibility();"><span class="glyphicon glyphicon-eye-open" id="rcon_password_eye"></span></button>
                    </div>
                </div>
            </div>
            <div class="dc-notice dc-notice--info" style="margin-top:14px;">
                Для работы RCON на веб-хостинге должны поддерживаться UDP-соединения. Таймаут ожидания ответа — 3 сек, при потере пакета выполняется одна автоматическая повторная попытка.
            </div>
            <div class="dc-version-actions" style="margin-top:14px;">
                <button type="button" class="btn btn-primary" onclick="pbRconSaveSettings();" style="border-radius:8px;font-weight:700;">
                    <span class="glyphicon glyphicon-ok"></span> Сохранить настройки
                </button>
            </div>
            <div id="rcon_settings_result" class="dc-result"></div>
        </div>

        <div class="dc-drawer-section">
            <div class="dc-drawer-section__head">
                <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-plus"></span></span>
                <span class="dc-drawer-section__title">Добавить команду</span>
            </div>
            <div class="dc-hint" style="margin-top:0;margin-bottom:14px;">Команда действия над игроком должна содержать переменную <code>nick</code> — при отправке в неё подставляется ник игрока. Остальные переменные админ заполняет вручную.</div>
            <div class="dc-form-grid">
                <div class="dc-form-col-3 dc-form-field">
                    <label>Категория</label>
                    <div class="dc-field">
                        <select id="rcon_command_category_new">
                            <option value="2">Действия над игроками</option>
                            <option value="3">Управление сервером</option>
                        </select>
                    </div>
                </div>
                <div class="dc-form-col-4 dc-form-field">
                    <label>Команда</label>
                    <div class="dc-field"><input type="text" id="rcon_command_value_new" placeholder="Например: amx_kick"></div>
                </div>
                <div class="dc-form-col-3 dc-form-field">
                    <label>Название</label>
                    <div class="dc-field"><input type="text" id="rcon_command_title_new" placeholder="Например: Кик"></div>
                </div>
                <div class="dc-form-col-2 dc-form-field">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary" style="width:100%;border-radius:8px;font-weight:700;" onclick="pbRconAddCommand();">
                        <span class="glyphicon glyphicon-plus"></span> Добавить
                    </button>
                </div>
            </div>
        </div>

        <div class="dc-drawer-section">
            <div class="dc-drawer-section__head">
                <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-list-alt"></span></span>
                <span class="dc-drawer-section__title">Сохранённые команды</span>
            </div>
            <div id="rcon_commands_container">Выберите сервер, чтобы увидеть команды.</div>
        </div>

        <div class="dc-drawer-section">
            <div class="dc-drawer-section__head">
                <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-console"></span></span>
                <span class="dc-drawer-section__title">Консоль сервера</span>
            </div>
            <div class="dc-hint" style="margin-top:0;margin-bottom:10px;">История команд и ответов сервера за текущую сессию администрирования. Полная история также сохраняется в лог-файл.</div>
            <div class="pb-rcon-console" id="rcon_console_output">
                <div class="pb-rcon-console__empty">Здесь появится история отправленных команд и ответов сервера.</div>
            </div>
            <div class="pb-rcon-console-input">
                <input type="text" id="rcon_console_command" placeholder="Введите команду, например: status" onkeydown="if(event.key==='Enter'){pbRconSendCommand();}">
                <button type="button" class="btn btn-primary" onclick="pbRconSendCommand();" style="border-radius:8px;font-weight:700;">
                    <span class="glyphicon glyphicon-send"></span> Отправить
                </button>
            </div>
            <div class="dc-version-actions" style="margin-top:10px;">
                <button type="button" class="btn btn-default" onclick="pbRconLoadHistory();" style="border-radius:8px;font-weight:700;">
                    <span class="glyphicon glyphicon-refresh"></span> Обновить историю
                </button>
                <button type="button" class="btn btn-default" onclick="pbRconClearHistory();" style="border-radius:8px;font-weight:700;">
                    <span class="glyphicon glyphicon-trash"></span> Очистить лог
                </button>
            </div>
        </div>
    </div>
</div>

<div id="srv-drawer-list" class="dc-drawer-content" style="display:none;">
    <div class="dc-drawer-section">
        <div class="dc-drawer-section__head">
            <span class="dc-drawer-section__icon"><span class="glyphicon glyphicon-list"></span></span>
            <span class="dc-drawer-section__title">Список серверов</span>
        </div>
        <div class="servers-settins">
            <div id="servers">
                <center><img src="{site_host}templates/admin/img/loader.gif"></center>
            </div>
        </div>
    </div>
</div>

<div class="dc-drawer-overlay" id="dc-drawer-overlay"></div>
<div class="dc-drawer dc-drawer--wide" id="dc-drawer">
    <div class="dc-drawer__head">
        <div class="dc-drawer__title-wrap">
            <span class="dc-drawer__icon" id="dc-drawer-icon"><span class="glyphicon glyphicon-cog"></span></span>
            <div>
                <h4 class="dc-drawer__title" id="dc-drawer-title">Настройки</h4>
                <div class="dc-drawer__subtitle" id="dc-drawer-subtitle"></div>
            </div>
        </div>
        <button type="button" class="dc-drawer__close" id="dc-drawer-close">&times;</button>
    </div>
    <div class="dc-drawer__body" id="dc-drawer-body"></div>
</div>

<style>
.dc-p2p-menu--srv{grid-template-columns:repeat(auto-fill,minmax(220px,1fr));}
.dc-p2p-tile__badge{position:absolute;top:12px;right:12px;padding:3px 9px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:10.5px;font-weight:700;}
.dc-p2p-tile__badge--success{background:#ecfdf5;color:#059669;}

.pb-smode-preview{display:flex;align-items:center;justify-content:center;height:130px;margin-bottom:10px;border:1px solid #e6ebf2;border-radius:10px;background:#f8fafc;overflow:hidden;}
.pb-smode-preview img{max-width:100%;max-height:100%;object-fit:contain;}
.pb-smode-file{width:100%;font-size:12px;}

.pb-smode-row{display:flex;align-items:center;gap:12px;padding:12px 14px;margin-bottom:10px;border:1px solid #edf1f6;border-radius:12px;background:#fbfcfe;}
.pb-smode-row:last-child{margin-bottom:0;}
.pb-smode-row__thumb{flex:0 0 auto;width:44px;height:44px;border-radius:10px;background:#eef2ff center/cover no-repeat;border:1px solid #e6ebf2;}
.pb-smode-row__main{flex:1 1 auto;min-width:0;}
.pb-smode-row__name{color:#0f172a;font-size:13.5px;font-weight:800;}
.pb-smode-row__hint{overflow:hidden;color:#64748b;font-size:12px;line-height:1.45;text-overflow:ellipsis;white-space:nowrap;}
.pb-smode-row__stats{flex:0 0 auto;display:flex;gap:6px;flex-wrap:wrap;}
.pb-smode-chip{padding:3px 9px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:10.5px;font-weight:700;}
.pb-smode-chip--on{background:#ecfdf5;color:#059669;}
.pb-smode-chip--off{background:#fef2f2;color:#dc2626;}
.pb-smode-chip--top{background:#eef2ff;color:#4338ca;}
.pb-smode-row__actions{flex:0 0 auto;display:flex;gap:6px;}
.pb-smode-row__actions .btn{border-radius:8px;font-weight:700;}
.pb-smode-empty{padding:18px;border:1px dashed #dde3ec;border-radius:12px;background:#fbfcfe;color:#64748b;font-size:12.5px;text-align:center;}

.servers-settins .block{margin-bottom:14px;border:1px solid #edf1f6;border-radius:12px;background:#fbfcfe;}
.servers-settins .block_head{padding:12px 15px;border-bottom:1px solid #edf1f6;color:#0f172a;font-size:13px;font-weight:800;}

.dc-state--success{border-color:#bbf7d0;background:#f0fdf4;}
.dc-state--success .dc-state__icon{background:#dcfce7;color:#16a34a;}
.dc-state--warning{border-color:#fde68a;background:#fffbeb;}
.dc-state--warning .dc-state__icon{background:#fef3c7;color:#b45309;}
.dc-state--danger{border-color:#fecaca;background:#fef2f2;}
.dc-state--danger .dc-state__icon{background:#fee2e2;color:#dc2626;}
.dc-state--info{border-color:#bfdbfe;background:#eff6ff;}
.dc-state--info .dc-state__icon{background:#dbeafe;color:#1e40af;}
.dc-state__icon{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;flex:0 0 auto;border-radius:9px;font-size:15px;}

.dc-field__btn{flex:0 0 auto;height:38px;padding:0 12px;margin-left:6px;border:1px solid #e6ebf2;border-radius:8px;background:#fbfcfe;color:#475569;cursor:pointer;}
.dc-field__btn:hover{background:#f1f5f9;}

.pb-rcon-console{max-height:340px;min-height:120px;overflow-y:auto;padding:12px;margin-bottom:10px;border:1px solid #edf1f6;border-radius:12px;background:#0f172a;}
.pb-rcon-console__empty{padding:18px;color:#94a3b8;font-size:12.5px;text-align:center;}
.pb-rcon-console__entry{padding:8px 0;border-bottom:1px solid rgba(255,255,255,.06);font-family:Consolas,Monaco,monospace;font-size:12.5px;}
.pb-rcon-console__entry:last-child{border-bottom:0;}
.pb-rcon-console__entry--error .pb-rcon-console__answer{color:#fca5a5;}
.pb-rcon-console__meta{display:flex;justify-content:space-between;gap:10px;margin-bottom:3px;color:#64748b;font-size:10.5px;}
.pb-rcon-console__cmd{color:#4ade80;word-break:break-word;}
.pb-rcon-console__answer{margin-top:3px;color:#cbd5e1;white-space:pre-wrap;word-break:break-word;}

.pb-rcon-console-input{display:flex;gap:8px;}
.pb-rcon-console-input input{flex:1 1 auto;height:38px;padding:0 12px;border:1px solid #e6ebf2;border-radius:8px;font-family:Consolas,Monaco,monospace;font-size:12.5px;}
.pb-rcon-console-input .btn{border-radius:8px;font-weight:700;}

@media(max-width:640px){
.dc-p2p-menu--srv{grid-template-columns:1fr;}
.pb-smode-row{flex-wrap:wrap;}
.pb-smode-row__main{flex:1 1 100%;order:2;}
.pb-smode-row__actions{order:3;}
}
</style>

<script>
(function(){
  var SRV_DRAWER_META = {
    'srv-drawer-modes': { icon: 'glyphicon-th-large', title: 'Режимы игры', subtitle: 'Название, описание, фон и персонаж каждого режима' },
    'srv-drawer-monitoring': { icon: 'glyphicon-signal', title: 'Настройки мониторинга', subtitle: 'Интервал обновления и внешний сервер мониторинга' },
    'srv-drawer-add': { icon: 'glyphicon-plus', title: 'Добавить сервер', subtitle: 'Основные, дополнительные настройки и статистика' },
    'srv-drawer-list': { icon: 'glyphicon-list', title: 'Список серверов', subtitle: 'Редактирование и удаление добавленных серверов' },
    'srv-drawer-rcon': { icon: 'glyphicon-console', title: 'RCON', subtitle: 'Подключение, команды и консоль сервера' }
  };

  var overlay = document.getElementById('dc-drawer-overlay');
  var drawer = document.getElementById('dc-drawer');
  var drawerBody = document.getElementById('dc-drawer-body');
  var drawerIcon = document.getElementById('dc-drawer-icon');
  var drawerTitle = document.getElementById('dc-drawer-title');
  var drawerSubtitle = document.getElementById('dc-drawer-subtitle');
  var currentContentId = null;
  var currentHome = null;

  function openDrawer(contentId, title){
    if (!drawer || !overlay) { return; }
    var content = document.getElementById(contentId);
    if (!content) { return; }

    closeDrawer(true);

    currentContentId = contentId;
    currentHome = document.createComment('dc-drawer-home:' + contentId);
    content.parentNode.insertBefore(currentHome, content);
    drawerBody.appendChild(content);
    content.style.display = 'block';

    var meta = SRV_DRAWER_META[contentId] || {};
    drawerIcon.innerHTML = '<span class="glyphicon ' + (meta.icon || 'glyphicon-cog') + '"></span>';
    drawerTitle.textContent = title || meta.title || '';
    drawerSubtitle.textContent = meta.subtitle || '';

    document.body.style.overflow = 'hidden';
    overlay.classList.add('active');
    drawer.classList.add('active');

    if (contentId === 'srv-drawer-modes') { pbSmodeLoadList(); }
    if (contentId === 'srv-drawer-list') { load_servers(); }
    if (contentId === 'srv-drawer-rcon') {
      var select = document.getElementById('rcon_server_select');
      if (select) { select.value = '0'; }
      var body = document.getElementById('rcon_panel_body');
      if (body) { body.style.display = 'none'; }
    }
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

  window.SrvOpenDrawer = openDrawer;
  window.SrvCloseDrawer = closeDrawer;

  document.addEventListener('click', function(e){
    var tile = e.target.closest ? e.target.closest('.dc-p2p-tile[data-drawer^="srv-drawer-"]') : null;
    if (tile) { openDrawer(tile.getAttribute('data-drawer')); }
  });

  var closeBtn = document.getElementById('dc-drawer-close');
  if (closeBtn) { closeBtn.addEventListener('click', function(){ closeDrawer(false); }); }
  if (overlay) { overlay.addEventListener('click', function(){ closeDrawer(false); }); }
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && drawer && drawer.classList.contains('active')) { closeDrawer(false); }
  });
})();

var PB_SMODES_CACHE = [];

function pbSmodeToast(message, ok){
  if(typeof window.push === 'function'){ window.push(message, ok ? 'success' : 'error'); }
}

function pbSmodeEscape(value){
  return String(value == null ? '' : value)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function pbSmodeLoadList(){
  $.ajax({
    type: 'POST',
    url: '../ajax/actions_panel.php',
    data: { phpaction: 1, token: $('#token').val(), smode_list: 1 },
    dataType: 'json',
    success: function(r){
      if(!r || r.status != '1'){ pbSmodeToast('Не удалось загрузить режимы', false); return; }
      PB_SMODES_CACHE = r.items || [];
      pbSmodeRenderList();
    },
    error: function(){ pbSmodeToast('Ошибка запроса', false); }
  });
}

function pbSmodeRenderList(){
  var box = document.getElementById('pb_smodes_list');
  var badge = document.getElementById('srv_modes_badge');
  if(badge){ badge.textContent = PB_SMODES_CACHE.length; }
  if(!box){ return; }

  if(!PB_SMODES_CACHE.length){
    box.innerHTML = '<div class="pb-smode-empty">Режимы ещё не созданы. Нажмите «Создать режим», чтобы добавить первый.</div>';
    return;
  }

  var shown = 0;
  var html = '';
  for(var i = 0; i < PB_SMODES_CACHE.length; i++){
    var m = PB_SMODES_CACHE[i];
    var thumb = m['character'] || m.background || 'templates/solution2/img/server_pbg/none.svg';
    var isTop = (m.active == 1 && shown < 3);
    if(m.active == 1){ shown++; }

    html += '<div class="pb-smode-row">'
      + '<span class="pb-smode-row__thumb" style="background-image:url(\'/' + pbSmodeEscape(thumb) + '\');"></span>'
      + '<div class="pb-smode-row__main">'
      + '<div class="pb-smode-row__name">' + pbSmodeEscape(m.name) + '</div>'
      + '<div class="pb-smode-row__hint">' + (m.description ? pbSmodeEscape(m.description) : 'Без описания') + '</div>'
      + '</div>'
      + '<div class="pb-smode-row__stats">'
      + '<span class="pb-smode-chip">Серверов: ' + m.servers + '</span>'
      + '<span class="pb-smode-chip">Онлайн: ' + m.online + '</span>'
      + '<span class="pb-smode-chip pb-smode-chip--' + (m.active == 1 ? 'on' : 'off') + '">' + (m.active == 1 ? 'Показывать' : 'Скрыт') + '</span>'
      + (isTop ? '<span class="pb-smode-chip pb-smode-chip--top">На главной</span>' : '')
      + '</div>'
      + '<div class="pb-smode-row__actions">'
      + '<button type="button" class="btn btn-default btn-sm" onclick="pbSmodeOpenForm(' + m.id + ');"><span class="glyphicon glyphicon-pencil"></span></button>'
      + '<button type="button" class="btn btn-danger btn-sm" onclick="pbSmodeDelete(' + m.id + ');"><span class="glyphicon glyphicon-trash"></span></button>'
      + '</div>'
      + '</div>';
  }
  box.innerHTML = html;
}

function pbSmodeOpenForm(id){
  var section = document.getElementById('pb_smode_form_section');
  var title = document.getElementById('pb_smode_form_title');
  var placeholder = '/templates/solution2/img/server_pbg/none.svg';

  document.getElementById('pb_smode_id').value = id || 0;
  document.getElementById('pb_smode_bg').value = '';
  document.getElementById('pb_smode_char').value = '';

  if(id){
    var mode = null;
    for(var i = 0; i < PB_SMODES_CACHE.length; i++){
      if(PB_SMODES_CACHE[i].id == id){ mode = PB_SMODES_CACHE[i]; break; }
    }
    if(!mode){ return; }
    title.textContent = 'Редактирование режима';
    document.getElementById('pb_smode_name').value = mode.name || '';
    document.getElementById('pb_smode_description').value = mode.description || '';
    document.getElementById('pb_smode_sort').value = mode.sort || 0;
    document.getElementById('pb_smode_active').value = mode.active || 1;
    document.getElementById('pb_smode_bg_preview').src = mode.background ? '/' + mode.background : placeholder;
    document.getElementById('pb_smode_char_preview').src = mode['character'] ? '/' + mode['character'] : placeholder;
  } else {
    title.textContent = 'Новый режим';
    document.getElementById('pb_smode_name').value = '';
    document.getElementById('pb_smode_description').value = '';
    document.getElementById('pb_smode_sort').value = 0;
    document.getElementById('pb_smode_active').value = 1;
    document.getElementById('pb_smode_bg_preview').src = placeholder;
    document.getElementById('pb_smode_char_preview').src = placeholder;
  }

  section.style.display = 'block';
  section.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function pbSmodeCloseForm(){
  document.getElementById('pb_smode_form_section').style.display = 'none';
}

function pbSmodeSave(){
  var fd = new FormData();
  fd.append('phpaction', 1);
  fd.append('token', $('#token').val());
  fd.append('smode_save', 1);
  fd.append('id', document.getElementById('pb_smode_id').value);
  fd.append('name', document.getElementById('pb_smode_name').value);
  fd.append('description', document.getElementById('pb_smode_description').value);
  fd.append('sort', document.getElementById('pb_smode_sort').value);
  fd.append('active', document.getElementById('pb_smode_active').value);

  var bg = document.getElementById('pb_smode_bg');
  var ch = document.getElementById('pb_smode_char');
  if(bg.files && bg.files[0]){ fd.append('background', bg.files[0]); }
  if(ch.files && ch.files[0]){ fd.append('character', ch.files[0]); }

  $.ajax({
    type: 'POST',
    url: '../ajax/actions_panel.php',
    data: fd,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function(r){
      var ok = r && r.status == '1';
      pbSmodeToast(r && r.message ? r.message : (ok ? 'Готово' : 'Ошибка'), ok);
      if(ok){ pbSmodeCloseForm(); pbSmodeLoadList(); }
    },
    error: function(){ pbSmodeToast('Ошибка запроса', false); }
  });
}

function pbSmodeDelete(id){
  if(!confirm('Удалить режим? Серверы этого режима останутся без режима.')){ return; }
  $.ajax({
    type: 'POST',
    url: '../ajax/actions_panel.php',
    data: { phpaction: 1, token: $('#token').val(), smode_delete: 1, id: id },
    dataType: 'json',
    success: function(r){
      var ok = r && r.status == '1';
      pbSmodeToast(r && r.message ? r.message : (ok ? 'Готово' : 'Ошибка'), ok);
      if(ok){ pbSmodeCloseForm(); pbSmodeLoadList(); }
    },
    error: function(){ pbSmodeToast('Ошибка запроса', false); }
  });
}

function pbSmodeRenderSlotsToggle(slots){
  var box = document.getElementById('pb_smode_slots_toggle');
  if(!box){ return; }
  box.querySelectorAll('label').forEach(function(label){
    label.classList.toggle('active', parseInt(label.getAttribute('data-slots'), 10) === slots);
  });
}

function pbSmodeLoadSlots(){
  $.ajax({
    type: 'POST',
    url: '../ajax/actions_panel.php',
    data: { phpaction: 1, token: $('#token').val(), smode_get_slots: 1 },
    dataType: 'json',
    success: function(r){
      if(r && r.status == '1'){ pbSmodeRenderSlotsToggle(parseInt(r.slots, 10)); }
    }
  });
}

function pbSmodeSetSlots(slots){
  $.ajax({
    type: 'POST',
    url: '../ajax/actions_panel.php',
    data: { phpaction: 1, token: $('#token').val(), smode_set_slots: 1, slots: slots },
    dataType: 'json',
    success: function(r){
      var ok = r && r.status == '1';
      pbSmodeToast(r && r.message ? r.message : (ok ? 'Готово' : 'Ошибка'), ok);
      if(ok){ pbSmodeRenderSlotsToggle(parseInt(r.slots, 10)); }
    },
    error: function(){ pbSmodeToast('Ошибка запроса', false); }
  });
}

$(function(){
  pbSmodeLoadList();
  pbSmodeLoadSlots();
  $('#pb_smode_bg').on('change', function(){
    if(this.files && this.files[0]){ document.getElementById('pb_smode_bg_preview').src = URL.createObjectURL(this.files[0]); }
  });
  $('#pb_smode_char').on('change', function(){
    if(this.files && this.files[0]){ document.getElementById('pb_smode_char_preview').src = URL.createObjectURL(this.files[0]); }
  });
});
</script>

<script>
(function(){
  var currentRconServerId = 0;
  var currentRconEnabled = 2;

  function rconEscape(value){
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function rconPost(data, done){
    data.phpaction = 1;
    data.token = $('#token').val();
    $.ajax({
      type: 'POST',
      url: '../ajax/actions_panel.php',
      data: data,
      dataType: 'json',
      success: done,
      error: function(){ done(null); }
    });
  }

  function rconSetStatus(cls, icon, text, hint){
    var state = document.getElementById('rcon_status_state');
    if (!state) { return; }
    state.className = 'dc-state dc-state--' + cls;
    document.getElementById('rcon_status_icon').className = 'dc-state__icon glyphicon glyphicon-' + icon;
    document.getElementById('rcon_status_text').textContent = text;
    document.getElementById('rcon_status_hint').textContent = hint || '';
  }

  function rconSyncEnabledToggle(value){
    currentRconEnabled = value;
    var wrap = document.getElementById('rcon_enabled_toggle');
    if (!wrap) { return; }
    wrap.querySelectorAll('label').forEach(function(label){
      label.classList.toggle('active', parseInt(label.getAttribute('data-value'), 10) === value);
    });
  }

  window.pbRconSetEnabled = function(value){
    rconSyncEnabledToggle(value);
  };

  window.pbRconTogglePasswordVisibility = function(){
    var input = document.getElementById('rcon_password_input');
    var eye = document.getElementById('rcon_password_eye');
    if (!input) { return; }
    var toText = input.type === 'password';
    input.type = toText ? 'text' : 'password';
    if (eye) { eye.className = 'glyphicon ' + (toText ? 'glyphicon-eye-close' : 'glyphicon-eye-open'); }
  };

  window.pbRconSelectServer = function(id){
    id = parseInt(id, 10) || 0;
    currentRconServerId = id;

    var body = document.getElementById('rcon_panel_body');
    if (!id) {
      if (body) { body.style.display = 'none'; }
      return;
    }
    if (body) { body.style.display = 'block'; }

    rconSetStatus('info', 'question-sign', 'Не проверено', 'Нажмите «Проверить соединение», чтобы протестировать RCON.');
    document.getElementById('rcon_password_input').value = '';
    document.getElementById('rcon_password_input').placeholder = 'Rcon пароль сервера';

    rconPost({ rcon_panel_bootstrap: 1, id: id }, function(result){
      if (!result || result.status != '1') {
        rconSetStatus('danger', 'remove-sign', 'Ошибка', (result && result.data) || 'Не удалось загрузить настройки сервера');
        return;
      }
      rconSyncEnabledToggle(parseInt(result.rcon, 10) === 1 ? 1 : 2);
      if (result.rcon_password_set == '1') {
        document.getElementById('rcon_password_input').placeholder = 'Пароль сохранён — оставьте пустым, чтобы не менять';
      }
    });

    pbRconLoadCommands(id);
    pbRconLoadHistory();
  };

  window.pbRconTestConnection = function(){
    if (!currentRconServerId) { return; }
    var btn = document.getElementById('rcon_test_btn');
    if (btn) { btn.disabled = true; }
    rconSetStatus('info', 'refresh', 'Проверка...', 'Отправляем запрос на сервер, подождите до нескольких секунд.');

    rconPost({ rcon_test_connection: 1, id: currentRconServerId }, function(result){
      if (btn) { btn.disabled = false; }
      if (result && result.status == '1') {
        rconSetStatus('success', 'ok-circle', 'Подключен', result.data || 'RCON доступен.');
      } else {
        rconSetStatus('danger', 'remove-sign', 'Недоступен', (result && result.data) || 'Не удалось подключиться к серверу.');
      }
    });
  };

  window.pbRconSaveSettings = function(){
    if (!currentRconServerId) { return; }
    var result = document.getElementById('rcon_settings_result');
    var password = document.getElementById('rcon_password_input').value;

    rconPost({
      rcon_save_settings: 1,
      id: currentRconServerId,
      rcon: currentRconEnabled,
      rcon_password: password
    }, function(res){
      var ok = res && res.status == '1';
      if (result) {
        result.innerHTML = '<p class="' + (ok ? 'text-success' : 'text-danger') + ' m-0">' + rconEscape((res && res.data) || (ok ? 'Готово' : 'Ошибка сохранения')) + '</p>';
      }
      if (typeof show_ok === 'function' && ok) { setTimeout(show_ok, 200); }
      if (typeof show_error === 'function' && !ok) { setTimeout(show_error, 200); }
      if (ok) {
        document.getElementById('rcon_password_input').value = '';
        document.getElementById('rcon_password_input').placeholder = 'Пароль сохранён — оставьте пустым, чтобы не менять';
      }
    });
  };

  window.pbRconLoadCommands = function(id){
    var box = document.getElementById('rcon_commands_container');
    if (!box) { return; }
    box.innerHTML = '<div class="dc-tile__hint">Загрузка...</div>';
    rconPost({ getServerCommands: 1, serverId: id }, function(html){
      box.innerHTML = (typeof html === 'string' && html.trim() !== '') ? html : '<div class="dc-tile__hint">Команд пока нет.</div>';
    });
  };

  /*
   * saveServerCommand()/dellServerCommand() из общего acp.min.js после успеха
   * сами вызывают getServerCommands(serverId) и пишут результат в контейнер.
   * Переопределяем её здесь, чтобы результат попадал в контейнер новой
   * RCON-панели, а не в контейнер старой (уже удалённой) Bootstrap-модалки.
   */
  window.getServerCommands = function(serverId){
    if (currentRconServerId && parseInt(serverId, 10) === currentRconServerId) {
      window.pbRconLoadCommands(serverId);
    }
  };

  window.pbRconAddCommand = function(){
    if (!currentRconServerId) { return; }
    var title = document.getElementById('rcon_command_title_new').value.trim();
    var command = document.getElementById('rcon_command_value_new').value.trim();
    var categoryId = document.getElementById('rcon_command_category_new').value;

    if (!command || !title) {
      if (typeof show_error === 'function') { setTimeout(show_error, 100); }
      return;
    }

    rconPost({
      saveServerCommand: 1,
      serverId: currentRconServerId,
      categoryId: categoryId,
      title: title,
      command: command,
      id: ''
    }, function(result){
      if (result && result.status == 1) {
        document.getElementById('rcon_command_title_new').value = '';
        document.getElementById('rcon_command_value_new').value = '';
        if (typeof show_ok === 'function') { setTimeout(show_ok, 150); }
        window.pbRconLoadCommands(currentRconServerId);
      } else {
        if (typeof show_error === 'function') { setTimeout(show_error, 150); }
        if (result && result.data) { alert(result.data); }
      }
    });
  };

  function rconConsoleEntryHtml(entry){
    var hasAnswer = entry.answer !== null && entry.answer !== undefined && String(entry.answer) !== '';
    return '' +
      '<div class="pb-rcon-console__entry">' +
        '<div class="pb-rcon-console__meta"><span>' + rconEscape(entry.date) + '</span><span>' + rconEscape(entry.user) + '</span></div>' +
        '<div class="pb-rcon-console__cmd">&gt; ' + rconEscape(entry.command) + '</div>' +
        (hasAnswer ? '<div class="pb-rcon-console__answer">' + rconEscape(entry.answer) + '</div>' : '') +
      '</div>';
  }

  window.pbRconLoadHistory = function(){
    if (!currentRconServerId) { return; }
    var out = document.getElementById('rcon_console_output');
    if (!out) { return; }

    rconPost({ rcon_console_history: 1, id: currentRconServerId }, function(result){
      if (!result || result.status != '1' || !result.data || !result.data.length) {
        out.innerHTML = '<div class="pb-rcon-console__empty">История пуста — здесь появятся отправленные команды и ответы сервера.</div>';
        return;
      }
      var html = '';
      result.data.forEach(function(entry){ html += rconConsoleEntryHtml(entry); });
      out.innerHTML = html;
      out.scrollTop = out.scrollHeight;
    });
  };

  window.pbRconClearHistory = function(){
    if (!currentRconServerId) { return; }
    if (!confirm('Очистить историю команд для этого сервера?')) { return; }
    rconPost({ rcon_console_clear: 1, id: currentRconServerId }, function(){
      pbRconLoadHistory();
    });
  };

  window.pbRconSendCommand = function(){
    if (!currentRconServerId) { return; }
    var input = document.getElementById('rcon_console_command');
    var command = (input.value || '').trim();
    if (!command) { return; }

    input.disabled = true;
    var out = document.getElementById('rcon_console_output');
    var emptyNode = out ? out.querySelector('.pb-rcon-console__empty') : null;
    if (emptyNode) { emptyNode.remove(); }

    rconPost({ rcon_console_send: 1, id: currentRconServerId, command: command }, function(result){
      input.disabled = false;

      if (result && result.status == '1') {
        if (out) {
          out.insertAdjacentHTML('beforeend', rconConsoleEntryHtml({
            date: 'только что',
            user: 'вы',
            command: result.command,
            answer: result.answer
          }));
          out.scrollTop = out.scrollHeight;
        }
        input.value = '';
        if (typeof show_ok === 'function') { setTimeout(show_ok, 150); }
      } else {
        if (out) {
          out.insertAdjacentHTML('beforeend', '<div class="pb-rcon-console__entry pb-rcon-console__entry--error"><div class="pb-rcon-console__cmd">&gt; ' + rconEscape(command) + '</div><div class="pb-rcon-console__answer">' + rconEscape((result && result.data) || 'Ошибка отправки команды') + '</div></div>');
          out.scrollTop = out.scrollHeight;
        }
        if (typeof show_error === 'function') { setTimeout(show_error, 150); }
      }
    });
  };
})();
</script>
