<script>
  $(".monitoring").remove();
  $("#hidden-menu").addClass("with-border");
</script>
</div>
</div>

<div class="container">
  <div class="row mt-5 settings_cols_row">
    <div class="col-lg-3">

      <!-- Переключатель вкладок настроек — вертикальное меню (solution2 style) -->
      <div class="settings_tabs" id="settings_tabs">
        <!-- Заголовок внутри блока меню -->
        <div class="settings_heading settings_heading--menu">
          <div class="settings_heading_ic"><i class='bx bx-slider-alt'></i></div>
          <div class="settings_heading_cap">
            <span class="settings_heading_title">Разделы настроек</span>
            <span class="settings_heading_sub">Выберите раздел настроек</span>
          </div>
        </div>
        <button type="button" class="settings_tab is-active" data-set-tab="info">
          <span class="settings_tab_ic"><i class='bx bx-user-circle'></i></span>
          <span class="settings_tab_label">Информация</span>
          <i class='bx bx-chevron-right settings_tab_arrow'></i>
        </button>
        <button type="button" class="settings_tab" data-set-tab="privacy">
          <span class="settings_tab_ic"><i class='bx bx-lock-alt'></i></span>
          <span class="settings_tab_label">Приватность</span>
          <i class='bx bx-chevron-right settings_tab_arrow'></i>
        </button>
        <button type="button" class="settings_tab" data-set-tab="notice">
          <span class="settings_tab_ic"><i class='bx bx-bell'></i></span>
          <span class="settings_tab_label">Уведомления</span>
          <i class='bx bx-chevron-right settings_tab_arrow'></i>
        </button>
        <button type="button" class="settings_tab" data-set-tab="contacts">
          <span class="settings_tab_ic"><i class='bx bx-book-content'></i></span>
          <span class="settings_tab_label">Контакты</span>
          <i class='bx bx-chevron-right settings_tab_arrow'></i>
        </button>
        <button type="button" class="settings_tab" data-set-tab="group">
          <span class="settings_tab_ic"><i class='bx bx-group'></i></span>
          <span class="settings_tab_label">Группа</span>
          <i class='bx bx-chevron-right settings_tab_arrow'></i>
        </button>
        <span class="settings_tab_glow" aria-hidden="true"></span>
      </div>

      <div class="settings_pane settings_pane_side" data-pane="contacts">
      <div class="contacts_setting_block">
        <div class="flex_top_setting">
          <i class='bx bx-book-content' style="color: #ffff;background: var(--GLOBAL, radial-gradient(100% 100% at 50% 0, #4fea9f 0, #38644f 100%));text-shadow: #00000082 1px 0 10px;"></i>
          <div class="flex_top_usr">
            <span class="name_top_usr">Контакты</span>
            <span class="desp_top_usr">Удобная связь</span>
          </div>
        </div>
        <a id="social_accounts_area"></a>
        {conf_mess_social}
        <div class="pbsoc-grid">
          {if('{social_google_enabled}' == '1')}
            {if('{social_google_bound}' == '1')}
            <div class="pbsoc-card pbsoc-card--bound pbsoc-card--google">
              <div class="pbsoc-card_logo"><img src="/templates/solution2/img/profile_menu/pbg_goo.png" alt="Google"></div>
              <div class="pbsoc-card_text">
                <span class="pbsoc-card_name">Google</span>
                <span class="pbsoc-card_status pbsoc-card_status--ok">Аккаунт привязан</span>
              </div>
              <button class="pbsoc-card_action" type="button" onclick="$('#google_unlink_modal').modal('show');" title="Отвязать"><i class='bx bx-x'></i></button>
            </div>
            {else}
            <a id="google_attach_link" href="{social_google_attach_url}" class="pbsoc-card pbsoc-card--google">
              <div class="pbsoc-card_logo"><img src="/templates/solution2/img/profile_menu/pbg_goo.png" alt="Google"></div>
              <div class="pbsoc-card_text">
                <span class="pbsoc-card_name">Google</span>
                <span class="pbsoc-card_status">Привязать аккаунт</span>
              </div>
              <i class='bx bx-link-alt pbsoc-card_arrow'></i>
            </a>
            {/if}
          {/if}

          {if('{social_telegram_enabled}' == '1')}
            {if('{social_telegram_bound}' == '1')}
            <div class="pbsoc-card pbsoc-card--bound pbsoc-card--telegram">
              <div class="pbsoc-card_logo"><img src="/templates/solution2/img/profile_menu/pbg_tg.png" alt="Telegram"></div>
              <div class="pbsoc-card_text">
                <span class="pbsoc-card_name">Telegram</span>
                <span class="pbsoc-card_status pbsoc-card_status--ok">{social_telegram_value}</span>
              </div>
              <button class="pbsoc-card_action" type="button" onclick="$('#telegram_unlink_modal').modal('show');" title="Отвязать"><i class='bx bx-x'></i></button>
            </div>
            {else}
            <div class="pbsoc-card pbsoc-card--telegram pbsoc-card--tg-visible">
              <div class="pbsoc-card_logo"><img src="/templates/solution2/img/profile_menu/pbg_tg.png" alt="Telegram"></div>
              <div class="pbsoc-card_text">
                <span class="pbsoc-card_name">Telegram</span>
                <span class="pbsoc-card_status">Привязать аккаунт</span>
              </div>
              <div class="pbsoc-card_tg-widget pbsoc-card_tg-widget--visible">
                <script>(function(){window.pbSettingsTelegramAuth=function(user){var url="{social_telegram_auth_url}";var q=[];for(var k in user){if(Object.prototype.hasOwnProperty.call(user,k)&&user[k]!==undefined&&user[k]!==null){q.push(encodeURIComponent(k)+"="+encodeURIComponent(user[k]));}}window.location.href=url+(url.indexOf("?")===-1?"?":"&")+q.join("&");};})();</script>
                <script async src="https://telegram.org/js/telegram-widget.js?22" data-telegram-login="{social_telegram_bot_username}" data-size="small" data-radius="10" data-onauth="pbSettingsTelegramAuth(user)" data-request-access="write"></script>
              </div>
            </div>
            {/if}
          {/if}

          {if('{social_discord_enabled}' == '1')}
            {if('{social_discord_bound}' == '1')}
            <div class="pbsoc-card pbsoc-card--bound pbsoc-card--discord">
              <div class="pbsoc-card_logo"><img src="/templates/solution2/img/profile_menu/pbg_ds.png" alt="Discord"></div>
              <div class="pbsoc-card_text">
                <span class="pbsoc-card_name">Discord</span>
                <span class="pbsoc-card_status pbsoc-card_status--ok">{social_discord_value}</span>
              </div>
              <button class="pbsoc-card_action" type="button" onclick="$('#discord_unlink_modal').modal('show');" title="Отвязать"><i class='bx bx-x'></i></button>
            </div>
            {else}
            <a id="discord_attach_link" href="{social_discord_attach_url}" class="pbsoc-card pbsoc-card--discord">
              <div class="pbsoc-card_logo"><img src="/templates/solution2/img/profile_menu/pbg_ds.png" alt="Discord"></div>
              <div class="pbsoc-card_text">
                <span class="pbsoc-card_name">Discord</span>
                <span class="pbsoc-card_status">Привязать аккаунт</span>
              </div>
              <i class='bx bx-link-alt pbsoc-card_arrow'></i>
            </a>
            {/if}
          {/if}

          {if($auth_api->steam_api != '2')} {if('{steam_api}' == '0')}
          <a id="steam_attach_link" href="#" onclick="if(typeof attach_user_steam==='function'){attach_user_steam();}" class="pbsoc-card pbsoc-card--steam">
            <div class="pbsoc-card_logo"><img src="/templates/solution2/img/profile_menu/pbg_steam.png" alt="Steam"></div>
            <div class="pbsoc-card_text">
              <span class="pbsoc-card_name">Steam</span>
              <span class="pbsoc-card_status">Привязать аккаунт</span>
            </div>
            <i class='bx bx-link-alt pbsoc-card_arrow'></i>
          </a>
          {else}
          <div class="pbsoc-card pbsoc-card--bound pbsoc-card--steam">
            <div class="pbsoc-card_logo"><img src="/templates/solution2/img/profile_menu/pbg_steam.png" alt="Steam"></div>
            <div class="pbsoc-card_text">
              <span class="pbsoc-card_name">Steam</span>
              <span class="pbsoc-card_status pbsoc-card_status--ok"><a target="_blank" href="https://steamcommunity.com/profiles/{steam_api}/" id="steam_user"><img style="display:none;"><span>Загрузка...</span></a></span>
            </div>
            <button class="pbsoc-card_action" type="button" onclick="unset_steam();" title="Отвязать"><i class='bx bx-x'></i></button>
            <script>
              get_user_steam_info('{steam_api}');
            </script>
          </div>
          <div id="unset_steam_result"></div>
          {/if} {conf_mess2} {/if}
        </div>
      </div>

      <div id="telegram_unlink_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
          <div class="modal-content" style="background:#242431;border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.08);">
              <h5 class="modal-title">Отвязать Telegram?</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
            </div>
            <div class="modal-body">
              <p style="margin:0;color:#cfd2e6;line-height:1.45;">Telegram будет отвязан от вашего профиля. После этого вход через Telegram для этого аккаунта станет недоступен, пока вы не привяжете его снова.</p>
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.08);display:flex;gap:10px;">
              <button type="button" class="save_signature" data-dismiss="modal" style="flex:1;text-align:center;">Отмена</button>
              <form id="pb_telegram_unlink_form" method="post" action="/ajax/actions_a.php" style="flex:1;margin:0;">
                <input type="hidden" name="token" value="{token}">
                <input type="hidden" name="phpaction" value="1">
                <input type="hidden" name="pb_unlink_telegram" value="1">
                <button type="submit" class="save_all" style="width:100%;text-align:center;">Отвязать</button>
              </form>
            </div>
            <div id="pb_telegram_unlink_result" style="padding:0 18px 14px;color:#cfd2e6;"></div>
          </div>
        </div>
      </div>

      <div id="discord_unlink_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
          <div class="modal-content" style="background:#242431;border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.08);">
              <h5 class="modal-title">Отвязать Discord?</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
            </div>
            <div class="modal-body">
              <p style="margin:0;color:#cfd2e6;line-height:1.45;">Discord-аккаунт будет отвязан от вашего профиля. После этого вход через Discord для этого аккаунта станет недоступен, пока вы не привяжете его снова.</p>
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.08);display:flex;gap:10px;">
              <button type="button" class="save_signature" data-dismiss="modal" style="flex:1;text-align:center;">Отмена</button>
              <form id="pb_discord_unlink_form" method="post" action="/ajax/actions_a.php" style="flex:1;margin:0;">
                <input type="hidden" name="token" value="{token}">
                <input type="hidden" name="phpaction" value="1">
                <input type="hidden" name="pb_unlink_discord" value="1">
                <button type="submit" class="save_all" style="width:100%;text-align:center;">Отвязать</button>
              </form>
            </div>
            <div id="pb_discord_unlink_result" style="padding:0 18px 14px;color:#cfd2e6;"></div>
          </div>
        </div>
      </div>

      <script>
        $(function(){
          $('#pb_telegram_unlink_form').off('submit.pbTelegramUnlink').on('submit.pbTelegramUnlink', function(e){
            e.preventDefault();
            var form = $(this);
            var btn = form.find('button[type="submit"]');
            var oldText = btn.html();
            var result = $('#pb_telegram_unlink_result');
            var token = form.find('input[name="token"]').val() || $('#token').val() || '';
            btn.prop('disabled', true).html('Отвязываем...');
            result.html('');
            $.ajax({
              url: '/ajax/actions_a.php',
              type: 'POST',
              data: { phpaction: 1, token: token, pb_unlink_telegram: 1 },
              success: function(html){ result.html(html); },
              error: function(xhr){
                result.html('<span class="m-icon icon-remove"></span> Не удалось выполнить запрос. Код: '+xhr.status);
              },
              complete: function(){ btn.prop('disabled', false).html(oldText); }
            });
          });

          $('#pb_discord_unlink_form').off('submit.pbDiscordUnlink').on('submit.pbDiscordUnlink', function(e){
            e.preventDefault();
            var form = $(this);
            var btn = form.find('button[type="submit"]');
            var oldText = btn.html();
            var result = $('#pb_discord_unlink_result');
            var token = form.find('input[name="token"]').val() || $('#token').val() || '';
            btn.prop('disabled', true).html('Отвязываем...');
            result.html('');
            $.ajax({
              url: '/ajax/actions_a.php',
              type: 'POST',
              data: { phpaction: 1, token: token, pb_unlink_discord: 1 },
              success: function(html){ result.html(html); },
              error: function(xhr){
                result.html('<span class="m-icon icon-remove"></span> Не удалось выполнить запрос. Код: '+xhr.status);
              },
              complete: function(){ btn.prop('disabled', false).html(oldText); }
            });
          });
        });
      </script>


      <div id="google_unlink_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
          <div class="modal-content" style="background:#242431;border:1px solid rgba(255,255,255,.08);border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,.08);">
              <h5 class="modal-title">Отвязать Google?</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
            </div>
            <div class="modal-body">
              <p style="margin:0;color:#cfd2e6;line-height:1.45;">Google-аккаунт будет отвязан от вашего профиля. После этого вход через Google для этого аккаунта станет недоступен, пока вы не привяжете его снова.</p>
            </div>
            <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,.08);display:flex;gap:10px;">
              <button type="button" class="save_signature" data-dismiss="modal" style="flex:1;text-align:center;">Отмена</button>
              <form id="pb_google_unlink_form" method="post" action="/ajax/actions_a.php" style="flex:1;margin:0;">
                <input type="hidden" name="token" value="{token}">
                <input type="hidden" name="phpaction" value="1">
                <input type="hidden" name="pb_unlink_google" value="1">
                <button type="submit" class="save_all" style="width:100%;text-align:center;">Отвязать</button>
              </form>
            </div>
            <div id="pb_google_unlink_result" style="padding:0 18px 14px;color:#cfd2e6;"></div>
          </div>
        </div>
      </div>


      <script>
        // PBGame CMS 2.3.4: Google unlink via standard actions_a.php handler.
        $(function(){
          $('#pb_google_unlink_form').off('submit.pbGoogleUnlink').on('submit.pbGoogleUnlink', function(e){
            e.preventDefault();

            var form = $(this);
            var btn = form.find('button[type="submit"]');
            var oldText = btn.html();
            var result = $('#pb_google_unlink_result');
            var token = form.find('input[name="token"]').val() || $('#token').val() || '';

            btn.prop('disabled', true).html('Отвязываем...');
            result.html('');

            $.ajax({
              url: '/ajax/actions_a.php',
              type: 'POST',
              data: {
                phpaction: 1,
                token: token,
                pb_unlink_google: 1
              },
              success: function(html){
                result.html(html);
              },
              error: function(xhr){
                result.html('<span class="m-icon icon-remove"></span> Не удалось выполнить запрос. Код: '+xhr.status);
                setTimeout(show_error, 500);
              },
              complete: function(){
                btn.prop('disabled', false).html(oldText);
              }
            });
          });
        });
      </script>

      <div class="signature_block_setting">
        <div class="flex_top_setting">
          <i class='bx bx-edit'></i>
          <div class="flex_top_usr">
            <span class="name_top_usr">Подпись</span>
            <span class="desp_top_usr">Личная подпись</span>
          </div>
        </div>
        <mytextarea id="signature">{signature}</mytextarea>
        <button type="button" class="save_signature" onclick="edit_signature();">
          Изменить подпись
        </button>
        <div class="js-toast-result" id="edit_signature_result"></div>

      </div>
      </div><!-- /settings_pane_side contacts -->
    </div>

    <div class="col-lg-9 profile-settings">        

      {if('{tg_reg_show_block}' == '1')}
      <div class="block" id="telegram-registration-box" style="padding: 20px; margin-bottom: 20px;">
        <div class="top_info_index" style="margin-bottom: 15px;">
          <i class='bx bxl-telegram' style="color:#5ad2ff;"></i>
          <b class="pretext_index">Telegram при регистрации</b>
        </div>
        {if('{tg_reg_required_gate}' == '1')}
        <div class="alert alert-warning" style="margin-bottom: 12px;">Для этого аккаунта Telegram обязателен. Заверши привязку, чтобы продолжить работу с сайтом.</div>
        {/if}
        {if('{tg_reg_prompt}' == '1')}
        <div class="alert alert-info" style="margin-bottom: 12px;">После регистрации можно сразу привязать Telegram и продолжить работу с аккаунтом.</div>
        {/if}
        <div class="row">
          <div class="col-md-7">
            {if('{tg_reg_feature_enabled}' != '1')}
            <div class="alert alert-info mb-0">Telegram-привязка при регистрации сейчас выключена владельцем сайта.</div>
            {else}
              {if('{tg_reg_feature_ready}' != '1')}
              <div class="alert alert-warning mb-0">Telegram-бот для привязки ещё не настроен владельцем сайта.</div>
              {else}
              <div class="alert {if('{tg_reg_bound}' == '1')}alert-success{else}alert-warning{/if} mb-0" id="tg-reg-binding-state">
                {if('{tg_reg_bound}' == '1')}Telegram уже привязан: <b>{tg_reg_bound_user}</b>{else}Telegram ещё не привязан к аккаунту.{/if}
              </div>
              {/if}
            {/if}
          </div>
          <div class="col-md-5">
            {if('{tg_reg_feature_enabled}' == '1' && '{tg_reg_feature_ready}' == '1')}
              {if('{tg_reg_bound}' == '1')}
              <button class="save_all" type="button" onclick="pbTgRegStartUnbind();">Отвязать Telegram</button>
              <div id="tg-reg-unbind-box" style="display:none; margin-top:10px;">
                <input id="tg-reg-unbind-code" type="text" maxlength="6" class="form-control" placeholder="Код из Telegram" style="margin-bottom:8px;">
                <button class="save_signature" type="button" onclick="pbTgRegConfirmUnbind();" style="width:100%; text-align:center;">Подтвердить отвязку</button>
              </div>
              <small class="input-title mt-1" id="tg-reg-result"></small>
              {else}
              <button class="save_all" type="button" onclick="pbTgRegStartBind();">Начать привязку</button>
              <a id="tg-reg-bot-link" href="#" target="_blank" class="save_signature mt-10" style="display:none; text-align:center;">Открыть бота</a>
              {if('{tg_reg_mode}' == '1')}
              <a class="save_signature mt-10" href="{profileLink}" style="text-align:center;">Пропустить и продолжить</a>
              {/if}
              <small class="input-title mt-1" id="tg-reg-result"></small>
              {/if}
            {/if}
          </div>
        </div>
      </div>
      {/if}
      <div class="block" style="padding: 20px;">

        <div class="settings_pane is-active" data-pane="info">

        <div class="block_setting_top">
          <div class="block_setting_left">
            <!-- Заголовок: Основная информация -->
            <div class="settings_heading">
              <div class="settings_heading_ic"><i class='bx bx-id-card'></i></div>
              <div class="settings_heading_cap">
                <span class="settings_heading_title">Основная информация</span>
                <span class="settings_heading_sub">Как вас видят другие пользователи</span>
              </div>
            </div>
            <div class="block_public_info">
              <label for="user_login" class="custom-input with-title with-button field-save">
                <button type="button" class="fieldsave-btn" onclick="edit_user_login();"><i class='bx bx-check'></i>Сохранить</button>
                <input id="user_login" maxlength="15" autocomplete="off" value="{login}" type="text" placeholder=" ">
                <span>Логин</span>
                <i class='bx bx-user'></i>
              </label>
              <div class="fieldsave-result" id="edit_user_login_result"></div>

              <label for="user_name" class="custom-input with-title with-button field-save">
                <button type="button" class="fieldsave-btn" onclick="edit_user_name();"><i class='bx bx-check'></i>Сохранить</button>
                <input id="user_name" maxlength="30" autocomplete="off" value="{name}" type="text" placeholder=" ">
                <span>Ваше имя</span>
                <i class='bx bx-card'></i>
              </label>
              <div class="fieldsave-result" id="edit_user_name_result"></div>

              <label for="user_nick" class="custom-input with-title with-button field-save">
                <button type="button" class="fieldsave-btn" onclick="edit_user_nick();"><i class='bx bx-check'></i>Сохранить</button>
                <input id="user_nick" maxlength="30" autocomplete="off" value="{nick}" type="text" placeholder=" ">
                <span>Ник на сервере</span>
                <i class='bx bx-joystick'></i>
              </label>
              <div class="fieldsave-result" id="edit_user_nick_result"></div>

              <label for="user_steam_id" class="custom-input with-title with-button field-save">
                <button type="button" class="fieldsave-btn" onclick="edit_user_steam_id();"><i class='bx bx-check'></i>Сохранить</button>
                <input id="user_steam_id" maxlength="32" autocomplete="off" value="{steam_id}" type="text" placeholder=" ">
                <span>Steam ID</span>
                <i class='bx bxl-steam'></i>
              </label>
              <div class="fieldsave-result" id="edit_user_steam_id_result"></div>

              <div class="block_date_edit">
                <span class="text_date_edit">Дата рождения</span>
                <div class="flex_edit_date">
                  <label for="birth_day" class="custom-select with-title">
                    <select style="background: #40404d;padding-left: 15px;" id="birth_day">{birth_day}</select>
                    <span style="left: 15px;">День</span>
                  </label>
                  <label for="birth_month" class="custom-select with-title">
                    <select style="background: #40404d;padding-left: 15px;" id="birth_month">{birth_month}</select>
                    <span style="left: 15px;">Месяц</span>
                  </label>
                  <label for="birth_year" class="custom-select with-title">
                    <select style="background: #40404d;padding-left: 15px;" id="birth_year">{birth_year}</select>
                    <span style="left: 15px;">Год</span>
                  </label>
                </div>
                <button type="button" class="fieldsave-btn fieldsave-btn--block" onclick="edit_user_birth();"><i class='bx bx-check'></i>Сохранить дату рождения</button>
                <div class="fieldsave-result js-toast-result" id="edit_user_birth_result"></div>
              </div>
            </div>
          </div>

          <div class="block_setting_right">

            <!-- Заголовок -->
            <div class="settings_heading">
              <div class="settings_heading_ic"><i class='bx bx-image-alt'></i></div>
              <div class="settings_heading_cap">
                <span class="settings_heading_title">Аватар профиля</span>
                <span class="settings_heading_sub">Изображение вашего профиля</span>
              </div>
            </div>
            <div class="block_avatar_edit">
              <div class="flex_edit_avatar_setting">
                <img id="avatar" src="/{avatar}" class="img_edit_settings">
                <div class="flex_edit_avatars">
                  {if('{avatar_locked}' == '1')}
                    <div class="js-toast-result">Смена аватара временно отключена администрацией.</div>
                  {else}
                    <form enctype="multipart/form-data" id="edit_user_avatar_form">
                      <input type="hidden" id="token" name="token" value="{token}">
                      <input type="hidden" id="edit_user_avatar" name="edit_user_avatar" value="1">
                      <input type="hidden" id="phpaction" name="phpaction" value="1">

                      <div class="custom-file mb-0">
                        <input type="file" class="custom-file-input" id="user_avatar" accept="image/*" name="user_avatar">
                        <label class="custom-file-label" for="user_avatar">Нажмите, чтобы выбрать...</label>
                      </div>

                      <input class="btn_avatar_setting" type="submit" value="Загрузить">
                    </form>
                  {/if}
                </div>
              </div>
              <div class="js-toast-result" id="edit_user_avatar_result"></div>
            </div>

          </div><!-- /block_setting_right -->
        </div><!-- /block_setting_top -->
        </div><!-- /pane info -->

        <div class="settings_pane" data-pane="privacy">
            <!-- Смена пароля -->
            <div class="privaty_block_settings">
              <div class="settings_heading">
                <div class="settings_heading_ic"><i class='bx bx-lock-alt'></i></div>
                <div class="settings_heading_cap">
                  <span class="settings_heading_title">Смена пароля</span>
                  <span class="settings_heading_sub">Защита доступа к аккаунту</span>
                </div>
              </div>
              <div class="password_setting_block">
                {if($user->password == 'none')}
                <form onsubmit="return false;" style="display:flex;flex-direction:column;gap:8px;">
                <label for="first_user_password" class="custom-input with-title">
                  <input style="background-color: #40404d;" id="first_user_password" maxlength="15" autocomplete="off" type="password" placeholder=" ">
                  <span>Новый пароль</span>
                  <i class='bx bx-lock-alt'></i>
                </label>

                <label for="first_user_password2" class="custom-input with-title">
                  <input style="background-color: #40404d;" id="first_user_password2" maxlength="15" autocomplete="off" type="password" placeholder=" ">
                  <span>Повторите новый пароль</span>
                  <i class='bx bx-lock-alt'></i>
                </label>

                <small class="input-title mt-1 js-toast-result" id="edit_first_user_password_result"></small>
                <button class="btn btn-sm mt-2 mb-0" type="button" onclick="edit_first_user_password();">Сохранить изменения</button>
                </form>
                {else}

                <form onsubmit="return false;" style="display:flex;flex-direction:column;gap:8px;">
                <label for="user_old_password" class="custom-input with-title">
                  <input style="background-color: #40404d;" id="user_old_password" autocomplete="new-password" maxlength="15" type="password" placeholder=" ">
                  <span>Текущий пароль</span>
                  <i class='bx bx-lock-alt'></i>
                </label>

                <label for="user_password" class="custom-input with-title">
                  <input style="background-color: #40404d;" id="user_password" maxlength="15" autocomplete="new-password" type="password" placeholder=" ">
                  <span>Новый пароль</span>
                  <i class='bx bx-lock-alt'></i>
                </label>

                <label for="user_password2" class="custom-input with-title mb-0">
                  <input style="background-color: #40404d;" id="user_password2" maxlength="15" autocomplete="new-password" type="password" placeholder=" ">
                  <span>Повторите новый пароль</span>
                  <i class='bx bx-lock-alt'></i>
                </label>
                <button class="password_setting" type="button" onclick="edit_user_password();">Изменить пароль</button>
                <small class="input-title mt-1 js-toast-result" id="edit_user_password_result"></small>
                </form>
                {/if}
              </div>
            </div>

            <div class="privaty_block_settings">
              <!-- Заголовок -->
              <div class="settings_heading">
                <div class="settings_heading_ic"><i class='bx bx-shield-quarter'></i></div>
                <div class="settings_heading_cap">
                  <span class="settings_heading_title">Приватность</span>
                  <span class="settings_heading_sub">Кто и как может с вами взаимодействовать</span>
                </div>
              </div>
              <span class="privaty_text">Кто может писать мне?</span>
              <div class="privaty_block">
                <div class="checkboxes__item" onclick="on_im(1);">
                  <label for="im_radio_1" class="checkbox style-h">
                    <input type="radio" id="im_radio_1" name="im_radio" {if( '{im_radio_1}'=='active' )} checked {/if}>
                    <div class="checkbox__checkmark"></div>
                    <div class="checkbox__body">Все</div>
                  </label>
                </div>
                <div class="checkboxes__item" onclick="on_im(2);">
                  <label class="checkbox style-h" for="im_radio_2">
                    <input type="radio" id="im_radio_2" name="im_radio" {if( '{im_radio_2}'=='active' )} checked {/if}>
                    <div class="checkbox__checkmark"></div>
                    <div class="checkbox__body">Друзья</div>
                  </label>
                </div>
              </div>

              <span class="privaty_text">Дублировать уведомления на почту?</span>
              <div class="privaty_block">
                <div class="checkboxes__item" onclick="on_email_notice(1);">
                  <label for="notice_radio_1" class="checkbox style-h">
                    <input type="radio" id="notice_radio_1" name="notice_radio" {if( '{notice_radio_1}'=='active' )} checked {/if}>
                    <div class="checkbox__checkmark"></div>
                    <div class="checkbox__body">Разрешить</div>
                  </label>
                </div>
                <div class="checkboxes__item" onclick="on_email_notice(2);">
                  <label class="checkbox style-h" for="notice_radio_2">
                    <input type="radio" id="notice_radio_2" name="notice_radio" {if( '{notice_radio_2}'=='active' )} checked {/if}>
                    <div class="checkbox__checkmark"></div>
                    <div class="checkbox__body">Запретить</div>
                  </label>
                </div>
              </div>

              <span class="privaty_text">Привязать cookies к IP адресу?</span>
              <div class="privaty_block">
                <div class="checkboxes__item" onclick="on_ip_protect(1);">
                  <label for="protect_radio_1" class="checkbox style-h">
                    <input type="radio" id="protect_radio_1" name="protect_radio" {if( '{protect_radio_1}'=='active' )} checked {/if}>
                    <div class="checkbox__checkmark"></div>
                    <div class="checkbox__body">Включить</div>
                  </label>
                </div>
                <div class="checkboxes__item" onclick="on_ip_protect(2);">
                  <label class="checkbox style-h" for="protect_radio_2">
                    <input type="radio" id="protect_radio_2" name="protect_radio" {if( '{protect_radio_2}'=='active' )} checked {/if}>
                    <div class="checkbox__checkmark"></div>
                    <div class="checkbox__body">Выключить</div>
                  </label>
                </div>
              </div>

              <span class="privaty_text">Скрыть геолокацию в профиле?</span>
              <div class="privaty_block">
                <div class="checkboxes__item" onclick="on_geo_hidden(1);">
                  <label for="geo_hidden_radio_1" class="checkbox style-h">
                    <input type="radio" id="geo_hidden_radio_1" name="geo_hidden_radio" {if( '{geo_hidden_radio_1}'=='active' )} checked {/if}>
                    <div class="checkbox__checkmark"></div>
                    <div class="checkbox__body">Скрыть</div>
                  </label>
                </div>
                <div class="checkboxes__item" onclick="on_geo_hidden(2);">
                  <label class="checkbox style-h" for="geo_hidden_radio_2">
                    <input type="radio" id="geo_hidden_radio_2" name="geo_hidden_radio" {if( '{geo_hidden_radio_2}'=='active' )} checked {/if}>
                    <div class="checkbox__checkmark"></div>
                    <div class="checkbox__body">Показывать</div>
                  </label>
                </div>
              </div>

              <span class="privaty_text">Геопозиция определяется по IP при входе на сайт</span>
              <div class="privaty_block">
                <button class="password_setting" type="button" onclick="update_geo_location();">Обновить геопозицию</button>
                <small class="input-title mt-1 js-toast-result" id="update_geo_location_result"></small>
              </div>
            </div>

            <script>
              function on_geo_hidden(val) {
                NProgress.start();
                $.ajax({
                  type: 'POST',
                  url: '../ajax/actions_a.php',
                  data: 'phpaction=1&on_geo_hidden=1&token=' + $('#token').val() + '&val=' + val,
                  dataType: 'json',
                  success: function(res) {
                    NProgress.done();
                    if (res.status == 1) {
                      setTimeout(show_ok, 500);
                    } else {
                      setTimeout(show_error, 500);
                    }
                  },
                  error: function() {
                    NProgress.done();
                    setTimeout(show_error, 500);
                  }
                });
              }

              function pbSetUserGroup(groupId) {
                var grid = document.getElementById('pbg_group_grid');
                var result = $('#pb_set_user_group_result');
                NProgress.start();
                $.ajax({
                  type: 'POST',
                  url: '../ajax/actions_a.php',
                  data: 'phpaction=1&pb_set_user_group=1&token=' + $('#token').val() + '&group_id=' + groupId,
                  dataType: 'json',
                  success: function(res) {
                    NProgress.done();
                    if (res.status == 1) {
                      if (grid) {
                        grid.querySelectorAll('.pbg-group-card').forEach(function(card) {
                          var isNowActive = (card.getAttribute('data-group-id') == groupId);
                          card.classList.toggle('pbg-group-card--active', isNowActive);
                          var status = card.querySelector('.pbg-group-card_status');
                          var slot = card.querySelector('.pbg-group-card_btn, .pbg-group-card_badge');
                          if (status) { status.textContent = isNowActive ? 'Текущая группа' : 'Доступна для выбора'; }
                          if (slot) {
                            if (isNowActive) {
                              var badge = document.createElement('span');
                              badge.className = 'pbg-group-card_badge';
                              badge.innerHTML = '<i class="bx bx-check"></i>';
                              slot.replaceWith(badge);
                            } else {
                              var btn = document.createElement('button');
                              btn.type = 'button';
                              btn.className = 'pbg-group-card_btn';
                              btn.textContent = 'Выбрать';
                              var gid = card.getAttribute('data-group-id');
                              btn.setAttribute('onclick', 'pbSetUserGroup(' + gid + ');');
                              slot.replaceWith(btn);
                            }
                          }
                        });
                      }
                      result.html('<span class="m-icon icon-ok"></span> ' + res.message);
                      setTimeout(show_ok, 500);
                      setTimeout(function() { window.location.reload(); }, 900);
                    } else {
                      result.html('<span class="m-icon icon-remove"></span> ' + (res.message || 'Не удалось сменить группу'));
                      setTimeout(show_error, 500);
                    }
                  },
                  error: function() {
                    NProgress.done();
                    result.html('<span class="m-icon icon-remove"></span> Не удалось выполнить запрос');
                    setTimeout(show_error, 500);
                  }
                });
              }

              function update_geo_location() {
                var result = $('#update_geo_location_result');
                NProgress.start();
                result.html('');
                $.ajax({
                  type: 'POST',
                  url: '../ajax/actions_a.php',
                  data: 'phpaction=1&geo_refresh=1&token=' + $('#token').val(),
                  dataType: 'json',
                  success: function(res) {
                    NProgress.done();
                    if (res.status == 1) {
                      var label = res.city ? (res.city + ', ' + res.country) : res.country;
                      result.html('Геопозиция обновлена: ' + label);
                      setTimeout(show_ok, 500);
                    } else {
                      result.html(res.data || 'Не удалось определить геопозицию');
                      setTimeout(show_error, 500);
                    }
                  },
                  error: function() {
                    NProgress.done();
                    result.html('Не удалось выполнить запрос');
                    setTimeout(show_error, 500);
                  }
                });
              }
            </script>
        </div><!-- /pane privacy (продолжение — чёрный список ниже) -->

        <div class="settings_pane" data-pane="notice">
            <div class="privaty_block_settings">
              <!-- Заголовок -->
              <div class="settings_heading">
                <div class="settings_heading_ic"><i class='bx bx-bell'></i></div>
                <div class="settings_heading_cap">
                  <span class="settings_heading_title">Звук уведомлений</span>
                  <span class="settings_heading_sub">Звуковое сопровождение оповещений</span>
                </div>
              </div>
              <span class="privaty_text">Звуковые оповещения (всплывающие уведомления сайта)</span>
              <div class="privaty_block">
                <div class="checkboxes__item" onclick="pbToastSoundSetEnabled(true);">
                  <label class="checkbox style-h" for="toast_sound_enabled_1">
                    <input type="radio" id="toast_sound_enabled_1" name="toast_sound_enabled">
                    <div class="checkbox__checkmark"></div>
                    <div class="checkbox__body">Включить</div>
                  </label>
                </div>
                <div class="checkboxes__item" onclick="pbToastSoundSetEnabled(false);">
                  <label class="checkbox style-h" for="toast_sound_enabled_2">
                    <input type="radio" id="toast_sound_enabled_2" name="toast_sound_enabled">
                    <div class="checkbox__checkmark"></div>
                    <div class="checkbox__body">Выключить</div>
                  </label>
                </div>
              </div>

              <div id="toast_sound_pick_wrap">
                <span class="privaty_text">Звук для каждого типа уведомления</span>
                <div class="toast-sound-grid">
                  <div class="toast-sound-row">
                    <span class="toast-sound-row-label"><i class="bx bx-check-circle" style="color:#22c55e;"></i>Успешно</span>
                    <select class="toast-sound-select" id="toast_sound_select_success" onchange="pbToastSoundSet('success', this.value); pbToastSoundPreview('success', this.value);">
                      <option value="">Без звука</option>
                    </select>
                  </div>
                  <div class="toast-sound-row">
                    <span class="toast-sound-row-label"><i class="bx bx-x-circle" style="color:#ef4444;"></i>Ошибка</span>
                    <select class="toast-sound-select" id="toast_sound_select_error" onchange="pbToastSoundSet('error', this.value); pbToastSoundPreview('error', this.value);">
                      <option value="">Без звука</option>
                    </select>
                  </div>
                  <div class="toast-sound-row">
                    <span class="toast-sound-row-label"><i class="bx bx-error" style="color:#f59e0b;"></i>Внимание</span>
                    <select class="toast-sound-select" id="toast_sound_select_warning" onchange="pbToastSoundSet('warning', this.value); pbToastSoundPreview('warning', this.value);">
                      <option value="">Без звука</option>
                    </select>
                  </div>
                  <div class="toast-sound-row">
                    <span class="toast-sound-row-label"><i class="bx bx-info-circle" style="color:#3b82f6;"></i>Информация</span>
                    <select class="toast-sound-select" id="toast_sound_select_info" onchange="pbToastSoundSet('info', this.value); pbToastSoundPreview('info', this.value);">
                      <option value="">Без звука</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="privaty_block_settings">
              <!-- Заголовок -->
              <div class="settings_heading">
                <div class="settings_heading_ic"><i class='bx bx-move'></i></div>
                <div class="settings_heading_cap">
                  <span class="settings_heading_title">Расположение уведомлений</span>
                  <span class="settings_heading_sub">Где показывать всплывающие окна</span>
                </div>
              </div>
              <span class="privaty_text">Где показывать всплывающие уведомления на экране</span>

              <div class="toast-pos-grid" id="toast_pos_grid">
                <div class="toast-pos-card" data-toast-pos="top-left" onclick="pbToastPositionSet('top-left'); pbToastPositionSyncUI();">
                  <div class="toast-pos-preview">
                    <span class="toast-pos-dot toast-pos-dot--top-left"></span>
                  </div>
                  <span class="toast-pos-card-label">Слева вверху</span>
                </div>
                <div class="toast-pos-card" data-toast-pos="top-center" onclick="pbToastPositionSet('top-center'); pbToastPositionSyncUI();">
                  <div class="toast-pos-preview">
                    <span class="toast-pos-dot toast-pos-dot--top-center"></span>
                  </div>
                  <span class="toast-pos-card-label">По центру вверху</span>
                </div>
                <div class="toast-pos-card" data-toast-pos="top-right" onclick="pbToastPositionSet('top-right'); pbToastPositionSyncUI();">
                  <div class="toast-pos-preview">
                    <span class="toast-pos-dot toast-pos-dot--top-right"></span>
                  </div>
                  <span class="toast-pos-card-label">Справа вверху</span>
                </div>
                <div class="toast-pos-card" data-toast-pos="bottom-left" onclick="pbToastPositionSet('bottom-left'); pbToastPositionSyncUI();">
                  <div class="toast-pos-preview">
                    <span class="toast-pos-dot toast-pos-dot--bottom-left"></span>
                  </div>
                  <span class="toast-pos-card-label">Слева внизу</span>
                </div>
                <div class="toast-pos-card" data-toast-pos="bottom-center" onclick="pbToastPositionSet('bottom-center'); pbToastPositionSyncUI();">
                  <div class="toast-pos-preview">
                    <span class="toast-pos-dot toast-pos-dot--bottom-center"></span>
                  </div>
                  <span class="toast-pos-card-label">По центру внизу</span>
                </div>
                <div class="toast-pos-card" data-toast-pos="bottom-right" onclick="pbToastPositionSet('bottom-right'); pbToastPositionSyncUI();">
                  <div class="toast-pos-preview">
                    <span class="toast-pos-dot toast-pos-dot--bottom-right"></span>
                  </div>
                  <span class="toast-pos-card-label">Справа внизу</span>
                </div>
              </div>

              <button type="button" class="toast-pos-test-btn" onclick="pbToastPositionTest();">
                <i class='bx bx-bell'></i> Показать тестовое уведомление
              </button>
            </div>

            <div class="privaty_block_settings">
              <!-- Заголовок -->
              <div class="settings_heading">
                <div class="settings_heading_ic"><i class='bx bx-chevrons-up'></i></div>
                <div class="settings_heading_cap">
                  <span class="settings_heading_title">Плавающая панель</span>
                  <span class="settings_heading_sub">Кнопки «Меню» и «Наверх» внизу экрана</span>
                </div>
              </div>
              <span class="privaty_text">Показывать плавающую панель при прокрутке страницы вниз</span>
              <div class="privaty_block">
                <div class="checkboxes__item" onclick="pbFloatBarSetEnabled(true);">
                  <label class="checkbox style-h" for="float_bar_enabled_1">
                    <input type="radio" id="float_bar_enabled_1" name="float_bar_enabled">
                    <div class="checkbox__checkmark"></div>
                    <div class="checkbox__body">Показывать</div>
                  </label>
                </div>
                <div class="checkboxes__item" onclick="pbFloatBarSetEnabled(false);">
                  <label class="checkbox style-h" for="float_bar_enabled_2">
                    <input type="radio" id="float_bar_enabled_2" name="float_bar_enabled">
                    <div class="checkbox__checkmark"></div>
                    <div class="checkbox__body">Скрыть</div>
                  </label>
                </div>
              </div>
            </div>
        </div><!-- /pane notice -->

        <div class="settings_pane" data-pane="info">
            <!-- Заголовок -->
            <div class="settings_heading">
              <div class="settings_heading_ic"><i class='bx bx-link'></i></div>
              <div class="settings_heading_cap">
                <span class="settings_heading_title">Персональная ссылка</span>
                <span class="settings_heading_sub">Красивый адрес вашего профиля</span>
              </div>
            </div>
            <div class="link_setting_block">
              <div class="link_text_setting">
                <i class='bx bx-info-circle'></i>
                <div class="flex_link_setting">
                  <span class="link_info_setting">Выделись среди остальных - сделай персональную ссылку на свой профиль</span>
                  <span class="link_example_setting">Пример: {profileLink}ваш-адрес</span>
                </div>
              </div>
              <label for="user_route" class="custom-input with-title with-button">
                <button style="background: linear-gradient(0deg, #5e616a99 30%, #62656e 100%);border-top: solid 1px #6d6f79;" type="button" onclick="editUserRoute();">
                  <b>Сохранить</b>
                </button>
                <input style="background-color: #3d3d4a;margin-bottom: 21px;" id="user_route" maxlength="15" autocomplete="off" value="{route}" type="text" placeholder=" ">
                <span>Персональная ссылка</span>
                <i class='bx bx-link'></i>
              </label>
              <div class="js-toast-result" id="edit_user_route_result"></div>
            </div>
        </div><!-- /pane info (продолжение) -->

        <div class="settings_pane" data-pane="privacy">
            <div class="black_list">
              <div class="black_list_left" style="gap: 0px;">
                <i class='bx bx-error-circle' style="background: #3a3a48;color: #75758d;"></i>
                <div class="black_list_text">
                  <span class="black_list_h1" style="font-size: 13px;">Черный список</span>
                  <span class="black_list_h2" style="font-size: 11px;">Заблокированные пользователи</span>
                </div>
              </div>
              <button class="black_list_button" data-target="#black-list" data-toggle="modal" onclick="getBlackList();">Открыть список</button>
            </div>
        </div><!-- /pane privacy (продолжение) -->

        <div class="settings_pane" data-pane="group">
            <div class="settings_heading">
              <div class="settings_heading_ic"><i class='bx bx-group'></i></div>
              <div class="settings_heading_cap">
                <span class="settings_heading_title">Группа</span>
                <span class="settings_heading_sub">Выберите группу, которая вам доступна</span>
              </div>
            </div>
            <div class="pbg-group-grid" id="pbg_group_grid">
              {groups_cards}
            </div>
            <div class="pbg-group-result js-toast-result" id="pb_set_user_group_result"></div>
        </div><!-- /pane group -->

      </div>

      <script>
        $('#black-list').modal('hide');
      </script>
      <div id="black-list" class="modal fade">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <div class="modal_head">
                <div class="flex_modal_head">
                  <span>Черный список</span>
                  <div class="line_title">
                    <span class="line_purse1"></span>
                    <span class="line_purse"></span>
                  </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
              </div>
            </div>
            <div class="modal-body">
              <div class="table-responsive mb-0">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <td>#</td>
                      <td>Профиль</td>
                      <td>Действие</td>
                    </tr>
                  </thead>
                  <tbody id="black-list-content">
                    <tr>
                      <td colspan="10">
                        <div class="loader"></div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
      var pbAdmin2faBindTimer = null;
      var pbAdmin2faPendingKey = 'pb_admin_2fa_pending';

      function pbAdmin2faRequest(action, payload, callback) {
        payload = payload || {};
        payload.action = action;
        payload.phpaction = action;
        payload.token = $('#token').val();
        $.ajax({
          type: 'POST',
          url: '../ajax/admin_2fa.php',
          data: payload,
          dataType: 'json',
          success: function(result) {
            if (typeof callback === 'function') {
              callback(result);
            }
          },
          error: function() {
            $('#tg-admin-2fa-result').text('Не удалось выполнить запрос.');
          }
        });
      }

      function pbAdmin2faSavePending(botLink, expires) {
        try {
          localStorage.setItem(pbAdmin2faPendingKey, JSON.stringify({
            bot_link: botLink,
            expires: parseInt(expires || 0, 10),
            created_at: Math.floor(Date.now() / 1000)
          }));
        } catch (e) {}
      }

      function pbAdmin2faGetPending() {
        try {
          var raw = localStorage.getItem(pbAdmin2faPendingKey);
          if (!raw) {
            return null;
          }
          var parsed = JSON.parse(raw);
          if (!parsed || !parsed.bot_link) {
            return null;
          }
          return parsed;
        } catch (e) {
          return null;
        }
      }

      function pbAdmin2faClearPending() {
        try {
          localStorage.removeItem(pbAdmin2faPendingKey);
        } catch (e) {}
        if (pbAdmin2faBindTimer) {
          clearInterval(pbAdmin2faBindTimer);
          pbAdmin2faBindTimer = null;
        }
      }

      function pbAdmin2faApplyBound(result) {
        pbAdmin2faClearPending();
        $('#tg-admin-2fa-binding-state').removeClass('alert-warning').addClass('alert-success').html('Telegram уже привязан: <b>' + (result.username ? result.username : 'подключено') + '</b>');
        $('#tg-admin-2fa-bot-link').hide();
        $('#tg-admin-2fa-result').text('Telegram успешно привязан. Обновляем страницу...');
        setTimeout(function(){ window.location.reload(); }, 700);
      }

      function pbAdmin2faCheckBind(silent) {
        pbAdmin2faRequest('bind_check', {}, function(result) {
          if (!silent || result.status == 1 || (result.message && result.message.indexOf('истёк') !== -1)) {
            $('#tg-admin-2fa-result').text(result.message);
          }
          if (result.status == 1) {
            pbAdmin2faApplyBound(result);
            return;
          }
          if (result.message && result.message.indexOf('истёк') !== -1) {
            pbAdmin2faClearPending();
            $('#tg-admin-2fa-bot-link').hide();
          }
        });
      }

      function pbAdmin2faStartAutoCheck() {
        if (pbAdmin2faBindTimer) {
          clearInterval(pbAdmin2faBindTimer);
        }
        pbAdmin2faBindTimer = setInterval(function() {
          pbAdmin2faCheckBind(true);
        }, 4000);
      }

      function pbAdmin2faResumePending() {
        var pending = pbAdmin2faGetPending();
        if (!pending) {
          return;
        }
        var now = Math.floor(Date.now() / 1000);
        if (pending.expires && pending.expires < now) {
          pbAdmin2faClearPending();
          return;
        }
        $('#tg-admin-2fa-bot-link').attr('href', pending.bot_link).show();
        $('#tg-admin-2fa-result').text('Откройте бота, нажмите Start. После этого Telegram привяжется автоматически.');
        pbAdmin2faStartAutoCheck();
      }

      function pbAdmin2faStartBind() {
        pbAdmin2faRequest('bind_start', {}, function(result) {
          if (result.status == 1) {
            pbAdmin2faSavePending(result.bot_link, result.expires);
            $('#tg-admin-2fa-bot-link').attr('href', result.bot_link).show();
            $('#tg-admin-2fa-result').text('Бот открыт. После нажатия Start привязка завершится автоматически.');
            pbAdmin2faStartAutoCheck();
            window.open(result.bot_link, '_blank');
          } else {
            $('#tg-admin-2fa-result').text(result.message);
          }
        });
      }

      function pbAdmin2faStartUnbind() {
        pbAdmin2faRequest('unbind_start', {}, function(result) {
          $('#tg-admin-2fa-result').text(result.message);
          if (result.status == 1) {
            $('#tg-admin-2fa-unbind-box').show();
            $('#tg-admin-2fa-unbind-code').val('').focus();
          }
        });
      }

      function pbAdmin2faConfirmUnbind() {
        var code = $.trim($('#tg-admin-2fa-unbind-code').val());
        if (!code) {
          $('#tg-admin-2fa-result').text('Введите код из Telegram.');
          $('#tg-admin-2fa-unbind-code').focus();
          return;
        }
        pbAdmin2faRequest('unbind_confirm', {code: code}, function(result) {
          $('#tg-admin-2fa-result').text(result.message);
          if (result.status == 1) {
            pbAdmin2faClearPending();
            setTimeout(function(){ window.location.reload(); }, 700);
          }
        });
      }

      $(document).ready(function() {
        pbAdmin2faResumePending();
        $(window).on('focus', function() {
          if (pbAdmin2faGetPending()) {
            pbAdmin2faCheckBind(true);
          }
        });
        document.addEventListener('visibilitychange', function() {
          if (!document.hidden && pbAdmin2faGetPending()) {
            pbAdmin2faCheckBind(true);
          }
        });
      });
    </script>


    <script>
      var pbTgRegBindTimer = null;
      var pbTgRegPendingKey = 'pb_tg_reg_pending';

      function pbTgRegRequest(action, payload, callback) {
        payload = payload || {};
        payload.action = action;
        payload.phpaction = action;
        payload.token = $('#token').val();
        $.ajax({
          type: 'POST',
          url: '../ajax/telegram_registration.php',
          data: payload,
          dataType: 'json',
          success: function(result) {
            if (typeof callback === 'function') {
              callback(result);
            }
          },
          error: function() {
            $('#tg-reg-result').text('Не удалось выполнить запрос.');
          }
        });
      }

      function pbTgRegSavePending(botLink, expires) {
        try {
          localStorage.setItem(pbTgRegPendingKey, JSON.stringify({bot_link: botLink, expires: parseInt(expires || 0, 10)}));
        } catch (e) {}
      }

      function pbTgRegGetPending() {
        try {
          var raw = localStorage.getItem(pbTgRegPendingKey);
          return raw ? JSON.parse(raw) : null;
        } catch (e) {
          return null;
        }
      }

      function pbTgRegClearPending() {
        try { localStorage.removeItem(pbTgRegPendingKey); } catch (e) {}
        if (pbTgRegBindTimer) {
          clearInterval(pbTgRegBindTimer);
          pbTgRegBindTimer = null;
        }
      }

      function pbTgRegApplyBound(result) {
        pbTgRegClearPending();
        $('#tg-reg-binding-state').removeClass('alert-warning').addClass('alert-success').html('Telegram уже привязан: <b>' + (result.username ? result.username : 'подключено') + '</b>');
        if (result.public_telegram) {
          $('#user_telegram').val(result.public_telegram);
        }
        $('#tg-reg-bot-link').hide();
        $('#tg-reg-result').text('Telegram успешно привязан. Обновляем страницу...');
        setTimeout(function(){ window.location.reload(); }, 700);
      }

      function pbTgRegCheckBind(silent) {
        pbTgRegRequest('bind_check', {}, function(result) {
          if (!silent || result.status == 1 || (result.message && result.message.indexOf('истёк') !== -1)) {
            $('#tg-reg-result').text(result.message || '');
          }
          if (result.status == 1) {
            pbTgRegApplyBound(result);
            return;
          }
          if (result.message && result.message.indexOf('истёк') !== -1) {
            pbTgRegClearPending();
            $('#tg-reg-bot-link').hide();
          }
        });
      }

      function pbTgRegStartAutoCheck() {
        if (pbTgRegBindTimer) {
          clearInterval(pbTgRegBindTimer);
        }
        pbTgRegBindTimer = setInterval(function() { pbTgRegCheckBind(true); }, 4000);
      }

      function pbTgRegResumePending() {
        var pending = pbTgRegGetPending();
        if (!pending) { return; }
        var now = Math.floor(Date.now() / 1000);
        if (pending.expires && pending.expires < now) {
          pbTgRegClearPending();
          return;
        }
        $('#tg-reg-bot-link').attr('href', pending.bot_link).show();
        $('#tg-reg-result').text('Откройте бота, нажмите Start. После этого Telegram привяжется автоматически.');
        pbTgRegStartAutoCheck();
      }

      function pbTgRegStartBind() {
        pbTgRegRequest('bind_start', {}, function(result) {
          if (result.status == 1) {
            pbTgRegSavePending(result.bot_link, result.expires);
            $('#tg-reg-bot-link').attr('href', result.bot_link).show();
            $('#tg-reg-result').text('Бот открыт. После нажатия Start привязка завершится автоматически.');
            pbTgRegStartAutoCheck();
            window.open(result.bot_link, '_blank');
          } else {
            $('#tg-reg-result').text(result.message || 'Не удалось начать привязку.');
          }
        });
      }

      function pbTgRegStartUnbind() {
        pbTgRegRequest('unbind_start', {}, function(result) {
          $('#tg-reg-result').text(result.message || '');
          if (result.status == 1) {
            $('#tg-reg-unbind-box').show();
            $('#tg-reg-unbind-code').val('').focus();
          }
        });
      }

      function pbTgRegConfirmUnbind() {
        var code = $.trim($('#tg-reg-unbind-code').val());
        if (!code) {
          $('#tg-reg-result').text('Введите код из Telegram.');
          $('#tg-reg-unbind-code').focus();
          return;
        }
        pbTgRegRequest('unbind_confirm', {code: code}, function(result) {
          $('#tg-reg-result').text(result.message || '');
          if (result.status == 1) {
            pbTgRegClearPending();
            setTimeout(function(){ window.location.reload(); }, 700);
          }
        });
      }

      $(document).ready(function() {
        pbTgRegResumePending();
        $(window).on('focus', function() {
          if (pbTgRegGetPending()) { pbTgRegCheckBind(true); }
        });
        document.addEventListener('visibilitychange', function() {
          if (!document.hidden && pbTgRegGetPending()) { pbTgRegCheckBind(true); }
        });
      });
    </script>

    <script src="/ajax/addons/verification/ajax-very.js?v={cache}-2324"></script>

    <script>
      $(document).ready(function() {
        init_tinymce("signature", "lite", "{file_manager_theme}", "", "");
      });
      $("#edit_user_avatar_form").submit(function(event) {
        NProgress.start();
        event.preventDefault();
        var data = new FormData($('#edit_user_avatar_form')[0]);
        $.ajax({
          type: "POST",
          url: "../ajax/actions_a.php",
          data: data,
          contentType: false,
          processData: false,
        }).done(function(html) {
          $("#edit_user_avatar_result").empty();
          $("#edit_user_avatar_result").append(html);
          $('#edit_user_avatar_form')[0].reset();
        });
        NProgress.done();
      });
    </script>

    <script>
      (function () {
        var VALID = ['info', 'privacy', 'notice', 'contacts', 'group'];
        // Совместимость со старыми якорями (#account/#contacts/#privacy)
        var HASH_MAP = { account: 'info', contacts: 'contacts', privacy: 'privacy', referral_program: 'info' };

        // Подсветка активного пункта — вертикальное меню (позиция = сам пункт)
        function pbSettingsMoveGlow() {
          var tabs = document.querySelectorAll('#settings_tabs .settings_tab');
          var glow = document.querySelector('#settings_tabs .settings_tab_glow');
          if (!glow || !tabs.length) { return; }
          var active = document.querySelector('#settings_tabs .settings_tab.is-active') || tabs[0];
          glow.style.top = active.offsetTop + 'px';
          glow.style.height = active.offsetHeight + 'px';
          glow.style.transform = 'translateY(0)';
        }
        window.pbSettingsMoveGlow = pbSettingsMoveGlow;

        function pbSettingsSetTab(tab) {
          if (VALID.indexOf(tab) === -1) { tab = 'info'; }

          var tabs = document.querySelectorAll('#settings_tabs .settings_tab');
          for (var t = 0; t < tabs.length; t++) {
            tabs[t].classList.toggle('is-active', tabs[t].getAttribute('data-set-tab') === tab);
          }
          pbSettingsMoveGlow();

          // Переключаем панели (их может быть несколько с одинаковым data-pane)
          var panes = document.querySelectorAll('.settings_pane');
          for (var p = 0; p < panes.length; p++) {
            panes[p].classList.toggle('is-active', panes[p].getAttribute('data-pane') === tab);
          }

          // Контейнер настроек справа скрываем, когда открыты Контакты (они в левой колонке)
          var right = document.querySelector('.col-lg-9.profile-settings');
          if (right) { right.classList.toggle('settings_hide_right', tab === 'contacts'); }

          // Режим «Контакты»: левая колонка занимает всю ширину строки
          var row = document.querySelector('.container .settings_cols_row');
          if (row) { row.classList.toggle('settings_contacts_mode', tab === 'contacts'); }

          // Пересчёт подсветки после смены раскладки (ширина меню могла измениться)
          requestAnimationFrame(pbSettingsMoveGlow);

          try { history.replaceState(null, '', '#' + tab); } catch (e) {}
        }
        window.pbSettingsSetTab = pbSettingsSetTab;

        document.addEventListener('click', function (e) {
          var btn = e.target.closest ? e.target.closest('.settings_tab') : null;
          if (!btn) { return; }
          pbSettingsSetTab(btn.getAttribute('data-set-tab'));
        });

        window.addEventListener('resize', function () {
          requestAnimationFrame(pbSettingsMoveGlow);
        });

        var raw = (window.location.hash || '').substr(1);
        var start = HASH_MAP[raw] || (VALID.indexOf(raw) !== -1 ? raw : 'info');
        pbSettingsSetTab(start);
      })();
    </script>

    <script>
      function pbToastPositionSyncUI() {
        if (typeof pbToastSoundGetPrefs !== 'function') return;
        var prefs = pbToastSoundGetPrefs();
        var grid = document.getElementById('toast_pos_grid');
        if (!grid) return;
        grid.querySelectorAll('.toast-pos-card').forEach(function(card) {
          card.classList.toggle('toast-pos-card--active', card.getAttribute('data-toast-pos') === prefs.position);
        });
      }

      function pbToastPositionTest() {
        if (typeof window.show_ok === 'function') window.show_ok();
        setTimeout(function() {
          if (typeof window.push === 'function') window.push('Так будут выглядеть уведомления', 'info');
        }, 250);
      }

      // Заполнить <select> звуков опциями из манифеста админа и выставить выбор
      function pbFillSoundSelects() {
        if (typeof pbToastSoundGetPrefs !== 'function') return;
        var prefs = pbToastSoundGetPrefs();

        ['success', 'error', 'warning', 'info'].forEach(function(type) {
          var sel = document.getElementById('toast_sound_select_' + type);
          if (!sel) return;

          // сохраняем первый пункт «Без звука», остальное — из манифеста
          sel.innerHTML = '<option value="">Без звука</option>';
          var list = (typeof pbToastSoundsForType === 'function') ? pbToastSoundsForType(type) : [];
          list.forEach(function(snd) {
            var opt = document.createElement('option');
            opt.value = snd.file;
            opt.textContent = snd.name || snd.file;
            sel.appendChild(opt);
          });

          // выставляем сохранённый выбор (если файла нет — останется «Без звука»)
          sel.value = prefs.sounds[type] || '';
        });
      }

      document.addEventListener('DOMContentLoaded', function() {
        if (typeof pbToastSoundGetPrefs !== 'function') return;

        var prefs = pbToastSoundGetPrefs();

        var enabledRadio = document.getElementById(prefs.enabled ? 'toast_sound_enabled_1' : 'toast_sound_enabled_2');
        if (enabledRadio) enabledRadio.checked = true;

        // Манифест звуков грузится асинхронно — заполняем, когда он готов
        if (typeof pbToastSoundOnManifest === 'function') {
          pbToastSoundOnManifest(pbFillSoundSelects);
        } else {
          pbFillSoundSelects();
        }

        var wrap = document.getElementById('toast_sound_pick_wrap');
        if (wrap) wrap.style.opacity = prefs.enabled ? '1' : '.45';

        pbToastPositionSyncUI();

        if (typeof pbFloatBarIsEnabled === 'function') {
          var floatBarRadio = document.getElementById(pbFloatBarIsEnabled() ? 'float_bar_enabled_1' : 'float_bar_enabled_2');
          if (floatBarRadio) floatBarRadio.checked = true;
        }
      });
    </script>

    <script>
      /* Любое действие в настройках, которое CMS-функции пишут в свой
         контейнер-результат (.fieldsave-result / .js-toast-result),
         показываем всплывающим тостером (window.push) вместо текста
         под полем. Приватность/письма/cookies уже сами дергают
         show_ok/show_error — их трогать не нужно. */
      (function () {
        var TOAST_SEL = '.fieldsave-result, .js-toast-result';

        function pbShowFieldToast(node) {
          var html = node.innerHTML || '';
          if (!html.trim()) { return; }

          // тип по иконке CMS: icon-ok → успех, icon-remove → ошибка
          var type = 'info';
          if (/icon-ok/.test(html))          type = 'success';
          else if (/icon-remove/.test(html)) type = 'error';

          // чистый текст без иконочных спанов
          var text = (node.textContent || '').replace(/\s+/g, ' ').trim();
          if (!text) { return; }

          if (typeof window.push === 'function') {
            window.push(text, type);
          }

          // очищаем, чтобы повторное действие снова триггерило тост
          node.innerHTML = '';
        }

        function pbInitFieldToasts() {
          var nodes = document.querySelectorAll(TOAST_SEL);
          if (!nodes.length || typeof MutationObserver !== 'function') { return; }

          var obs = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
              var target = mutations[i].target;
              if (target && target.matches && target.matches(TOAST_SEL)) {
                pbShowFieldToast(target);
              }
            }
          });

          nodes.forEach(function (n) {
            obs.observe(n, { childList: true, subtree: false });
          });
        }

        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', pbInitFieldToasts);
        } else {
          pbInitFieldToasts();
        }
      })();
    </script>