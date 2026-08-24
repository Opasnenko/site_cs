<div class="page">
    <div class="dc-wrap">
    <div class="dc-col">

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--blue"><span class="glyphicon glyphicon-picture"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Настройки аватарок</div>
                        <div class="dc-card__subtitle">Стандартный аватар и ограничения загрузки</div>
                    </div>
                </div>
            </div>
            <div class="dc-card__body">
                <div class="dc-form-grid">
                    <div class="dc-form-col-8 dc-form-field">
                        <label>Стандартный аватар</label>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <img id="pb_user_default_avatar_preview" src="../{user_default_avatar}?v={cache}" alt="Стандартный аватар" style="width:56px;height:56px;object-fit:cover;border-radius:12px;border:1px solid #edf1f6;">
                            <div style="flex:1;">
                                <input type="file" id="pb_user_default_avatar_file" class="form-control" accept="image/jpeg,image/png,image/gif">
                                <div class="dc-hint">JPG, PNG или GIF.</div>
                            </div>
                        </div>
                    </div>
                    <div class="dc-form-col-4 dc-form-field">
                        <label>Макс. размер, МБ</label>
                        <input type="number" min="1" max="20" id="pb_user_avatar_max_mb" class="form-control" value="{user_avatar_max_mb}">
                    </div>
                    <div class="dc-form-col-6 dc-form-field">
                        <label>Применение стандартного аватара</label>
                        <select id="pb_user_default_avatar_scope" class="form-control">
                            <option value="1" selected>Новым пользователям</option>
                            <option value="2">Всем пользователям</option>
                        </select>
                    </div>
                    <div class="dc-form-col-6 dc-form-field">
                        <label>Смена аватара пользователями</label>
                        <div class="dc-toggle dc-toggle--onoff dc-toggle--compact" id="pb_user_avatar_locked_toggle" data-toggle="buttons">
                            <label class="{user_avatar_change_allowed}" onclick="pbUserAvatarLockToggle(0, this);">Разрешена</label>
                            <label class="{user_avatar_change_locked}" onclick="pbUserAvatarLockToggle(1, this);">Запрещена</label>
                        </div>
                        <input type="hidden" id="pb_user_avatar_locked" value="{user_avatar_locked}">
                    </div>
                </div>
                <div style="margin-top:14px;display:flex;align-items:center;gap:12px;">
                    <button type="button" class="dc-btn dc-btn--primary" style="width:auto;padding:0 20px;" onclick="pb_save_user_avatar_settings();"><span class="glyphicon glyphicon-floppy-disk"></span> Сохранить настройки</button>
                    <div id="pb_user_avatar_settings_result" class="dc-result"></div>
                </div>
            </div>
        </div>

    </div>
    <div class="dc-col">

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--slate"><span class="glyphicon glyphicon-search"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Поиск и фильтр</div>
                        <div class="dc-card__subtitle">По логину или ID пользователя</div>
                    </div>
                </div>
            </div>
            <div class="dc-card__body">
                <div class="dc-form-grid">
                    <div class="dc-form-col-8 dc-form-field">
                        <label>Логин или ID</label>
                        <div class="dc-field">
                            <input type="text" id="search_login" name="search_login" placeholder="Введите логин пользователя">
                            <button class="dc-field__btn dc-field__btn--primary" onclick="admin_search_login({start})" type="button"><span class="glyphicon glyphicon-search"></span> Найти</button>
                        </div>
                    </div>
                    <div class="dc-form-col-4 dc-form-field">
                        <label>Группа</label>
                        <select id="groups" class="form-control" onchange="group_change();">
                            {groups}
                        </select>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>

    <div class="dc-wrap dc-wrap--single" style="margin-top:18px;">
    <div class="dc-col">

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--green"><span class="glyphicon glyphicon-list-alt"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Пользователи</div>
                    </div>
                </div>
            </div>
            <div class="dc-card__body">
                <div id="users" class="dc-user-grid">
                    <center><img src="{site_host}templates/admin/img/loader.gif"></center>
                </div>
                <div id="pagination2" style="margin-top:14px;"><center>{pagination}</center></div>
            </div>
        </div>

    </div>
    </div>
</div>

<div class="dc-drawer-overlay" id="dc-drawer-overlay"></div>
<div class="dc-drawer dc-drawer--wide" id="dc-drawer">
    <div class="dc-drawer__head">
        <div class="dc-drawer__title-wrap">
            <span class="dc-drawer__icon" id="dc-drawer-icon"><span class="glyphicon glyphicon-user"></span></span>
            <div>
                <h4 class="dc-drawer__title" id="dc-drawer-title">Пользователь</h4>
                <div class="dc-drawer__subtitle" id="dc-drawer-subtitle"></div>
            </div>
        </div>
        <div class="dc-drawer__head-actions">
            <div class="dc-danger-menu" id="dc-danger-menu">
                <button type="button" class="dc-danger-menu__trigger" id="dc-danger-trigger" title="Опасная зона"><span class="glyphicon glyphicon-warning-sign"></span></button>
                <div class="dc-danger-menu__panel" id="dc-danger-panel">
                    <div class="dc-danger-menu__title">Опасная зона</div>
                    <select id="clear_type">
                        <option value="2">Очистить активность пользователя</option>
                        <option value="3">Удалить все сообщения из чата</option>
                        <option value="4">Удалить все сообщения и темы с форума</option>
                        <option value="5">Удалить все комментарии</option>
                        <option value="1">Удалить пользователя</option>
                    </select>
                    <button type="button" class="dc-danger-menu__submit" id="dc-danger-submit"><span class="glyphicon glyphicon-trash"></span> Выполнить</button>
                </div>
            </div>
            <button type="button" class="dc-drawer__close" id="dc-drawer-close">&times;</button>
        </div>
    </div>
    <div class="dc-drawer__body" id="dc-drawer-body">
        <div class="dc-drawer-content-wrap" id="user-drawer-content"></div>
    </div>
</div>

<script src="{site_host}templates/admin/js/users_engine_admin.js?v={cache}"></script>
<script>
    set_enter('#search_login', 'admin_search_login({start})');
    admin_load_users("{start}");

    function pb_save_user_avatar_settings(silent) {
        var token = $('#token').val() || '{token}';
        var fd = new FormData();
        fd.append('phpaction', '1');
        fd.append('token', token);
        fd.append('save_user_avatar_settings', '1');
        fd.append('user_avatar_max_mb', $('#pb_user_avatar_max_mb').val());
        fd.append('user_default_avatar_scope', $('#pb_user_default_avatar_scope').val());
        fd.append('user_avatar_locked', $('#pb_user_avatar_locked').val());
        var file = $('#pb_user_default_avatar_file')[0].files[0];
        if(file) {
            fd.append('user_default_avatar_file', file);
        }
        if(!silent) {
            $('#pb_user_avatar_settings_result').html('<span style="color:var(--uix-muted,#64748b);">Сохраняю...</span>');
        }
        $.ajax({
            url: '../ajax/actions_panel.php',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(result) {
                if(result.status == 1) {
                    $('#pb_user_avatar_settings_result').html('<span style="color:#059669;">' + result.data + '</span>');
                    if(result.avatar) {
                        $('#pb_user_default_avatar_preview').attr('src', '../' + result.avatar + '?v=' + Date.now());
                    }
                    $('#pb_user_default_avatar_file').val('');
                } else {
                    $('#pb_user_avatar_settings_result').html('<span style="color:#dc2626;">' + result.data + '</span>');
                }
            },
            error: function() {
                $('#pb_user_avatar_settings_result').html('<span style="color:#dc2626;">Ошибка сохранения настроек.</span>');
            }
        });
    }

    function pbUserAvatarLockToggle(value, el) {
        $('#pb_user_avatar_locked_toggle label').removeClass('active');
        $(el).addClass('active');
        $('#pb_user_avatar_locked').val(value);
        pb_save_user_avatar_settings(true);
    }

    function group_change() {
        var group = $('#groups').val();
        location.href = 'users?group='+group+'&page=1';
    }
</script>
