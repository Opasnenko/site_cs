<script>
  $(".monitoring").remove();
  $("#hidden-menu").addClass("with-border");
</script>
</div>
</div>

<?php echo function_exists('pb_premium_render_profile_video_overlay') ? pb_premium_render_profile_video_overlay((int)'{profile_id}') : ''; ?>
<script>
  (function() {
    var overlay = document.getElementById('premium_video_intro');
    if (!overlay) {
      return;
    }
    var video = document.getElementById('premium_video_intro_video');
    var skipBtn = document.getElementById('premium_video_intro_skip');
    var unmuteBtn = document.getElementById('premium_video_intro_unmute');
    var closed = false;

    function closeOverlay() {
      if (closed) {
        return;
      }
      closed = true;
      overlay.classList.remove('is-visible');
      overlay.classList.add('is-closing');
      setTimeout(function() {
        overlay.remove();
        document.body.style.overflow = '';
      }, 500);
    }

    document.body.style.overflow = 'hidden';

    if (!video) {
      closeOverlay();
      return;
    }

    video.addEventListener('loadeddata', function() {
      requestAnimationFrame(function() {
        overlay.classList.add('is-visible');
      });
    });

    video.addEventListener('ended', closeOverlay);
    video.addEventListener('error', closeOverlay);
    skipBtn.addEventListener('click', closeOverlay);

    var SOUND_CONSENT_KEY = 'premium_video_intro_sound_ok';
    var hasSoundConsent = false;
    try {
      hasSoundConsent = localStorage.getItem(SOUND_CONSENT_KEY) === '1';
    } catch (e) {}

    function rememberSoundConsent() {
      try {
        localStorage.setItem(SOUND_CONSENT_KEY, '1');
      } catch (e) {}
    }

    function unmuteNow() {
      video.muted = false;
      video.volume = 1;
      video.play().catch(function() {});
      if (unmuteBtn) {
        unmuteBtn.classList.remove('is-visible');
      }
      rememberSoundConsent();
    }

    var docUnlockHandler = function() {
      if (!closed && video.muted) {
        unmuteNow();
      }
      document.removeEventListener('pointerdown', docUnlockHandler, true);
      document.removeEventListener('keydown', docUnlockHandler, true);
    };

    function showUnmuteHint() {
      if (unmuteBtn) {
        unmuteBtn.classList.add('is-visible');
      }
      document.addEventListener('pointerdown', docUnlockHandler, true);
      document.addEventListener('keydown', docUnlockHandler, true);
    }

    function tryPlayWithSound() {
      video.muted = false;
      video.volume = 1;
      var playPromise = video.play();
      if (playPromise && typeof playPromise.then === 'function') {
        playPromise.then(function() {
          rememberSoundConsent();
        }).catch(function() {
          video.muted = true;
          video.play().catch(function() {});
          if (hasSoundConsent) {
            docUnlockHandler = function() {
              if (!closed) {
                unmuteNow();
              }
              document.removeEventListener('pointerdown', docUnlockHandler, true);
              document.removeEventListener('keydown', docUnlockHandler, true);
            };
            document.addEventListener('pointerdown', docUnlockHandler, true);
            document.addEventListener('keydown', docUnlockHandler, true);
          }
          showUnmuteHint();
        });
      }
    }

    if (unmuteBtn) {
      unmuteBtn.addEventListener('click', unmuteNow);
    }

    tryPlayWithSound();
  })();
</script>

<div class="container">

      <div class="heading_block">
        <div class="double_heading">
          <i class='bx bx-user'></i>
          <div class="heading_cap">
            <span class="heading">Профиль</span>
            <span class="second_heading">Личная информация</span>
          </div>
        </div>
        <div class="flex_profile_top">
          {if(is_worthy("m") || is_worthy("c") || is_worthy("f") || is_admin())}
          <div class="btn_admin_menu" data-target="#admin_menu" data-toggle="modal"><i class='bx bx-edit' ></i></div>
       	{/if}
          <div class="activity_name">{last_activity}</div>
          </div>
      </div>

<div class="pbgprof2-page">

  <div class="pbgprof2-banner">
    {profile_bg_html}
    <div class="pbgprof2-banner-actions">
      <div class="pbgprof2-idchip">
        <span>ID: <span class="id">{profile_id}</span></span> <button class="copyref pbgprof2-idchip-btn" onclick="Copy('toCopy')" title="Скопировать ID"><i class='bx bxs-copy-alt'></i></button>
      </div>
      <span class="premium-login-badge-slot"><?php echo function_exists('pb_premium_login_badge_html') ? pb_premium_login_badge_html((int)'{profile_id}') : ''; ?></span>
      <div id="toCopy">{profile_id}</div>
      {if(is_auth() && '{profile_id}' == $_SESSION['id'])}
      <a href="../settings" class="pbgprof2-btn pbgprof2-btn--primary"><i class='bx bx-cog'></i> Настройки</a>
      <a href="../exit" class="pbgprof2-btn pbgprof2-btn--ghost"><i class="bx bxs-door-open"></i></a>
      {/if}
      {if((is_auth() && $_SESSION['id'] != '{profile_id}' && '{dell}' != '1'))}
      <a href="../messages?create_id={profile_id}" class="pbgprof2-btn"><i class='bx bx-message-square-detail'></i> Написать сообщение</a>
      {if('{isFriend}' == 'false' && '{issetFriendRequestFromMe}' == 'false' && '{issetFriendRequestFromHim}' == 'false')}
      <a onclick="add_new_friend({profile_id}, function(message) { alert(message) }); $(this).fadeOut();" class="pbgprof2-btn"><i class='bx bx-user-plus'></i> Добавить в друзья</a>
      {/if}
      {if('{isFriend}' == 'true')}
      <a onclick="dell_friend({profile_id}, function(message) { alert(message) }); $(this).fadeOut();" class="pbgprof2-btn"><i class='bx bx-user-x'></i> Удалить из друзей</a>
      {/if}
      {if('{issetFriendRequestFromMe}' == 'true')}
      <a onclick="cancel_friend({profile_id}, function(message) { alert(message) }); $(this).fadeOut();" class="pbgprof2-btn">Отменить заявку</a>
      {/if}
      {if('{issetFriendRequestFromHim}' == 'true')}
      <a onclick="take_friend({profile_id}, function(message) { alert(message) }); $(this).fadeOut(); $(this).next().fadeOut();" class="pbgprof2-btn pbgprof2-btn--accent">Принять заявку</a>
      {/if}
      {if(isOnMyBlacklist($pdo, $id))}
      <button class="razblock pbgprof2-btn pbgprof2-btn--ghost" onclick="removeFromBlackList({profile_id}, function(message) { alert(message) }); $(this).fadeOut();"><i class='bx bx-lock-open-alt'></i></button>
      {else}
      <button class="zablock pbgprof2-btn pbgprof2-btn--danger" type="Заблокировать" onclick="addToBlackList({profile_id}, function(message) { alert(message) }); $(this).fadeOut();"><i class='bx bx-lock-alt'></i> Заблокировать</button>
      {/if}
      {/if}
    </div>
  </div>

  <div class="pbgprof2-row">
    <div class="pbgprof2-idcard">
      <span class="pb-steam-avatar-shell pbgprof2-idcard-avatar-shell">
        <?php echo render_user_avatar((int)'{profile_id}', '../{avatar}', 'avatar_profile', 'pb-avatar-wrap pb-avatar-profile-idcard-wrap', "alt=\"{login}\""); ?>
        {steam_level_badge}
      </span>
      <div class="pbgprof2-idcard-name-row">
        <span class="username pbgprof2-idcard-name" data-verify-user="{profile_id}" style="color: {group_color}" data-verify-size="18px">{login}</span>
      </div>
      <div class="pbgprof2-idcard-group" style="color: {group_color}">{group}</div>

      {if(is_auth() && '{profile_id}' == $_SESSION['id'])}
      <div class="pbgprof2-idcard-status pbgprof2-idcard-status--editable" id="pbgprof2-status-view" data-empty-text="Установить статус" {if('{status}' == '')}data-is-empty="1"{/if}>
        <span id="pbgprof2-status-text">{if('{status}' != '')}{status}{else}Установить статус{/if}</span>
        <i class='bx bx-pencil'></i>
      </div>
      <div class="pbgprof2-idcard-status-edit" id="pbgprof2-status-edit" style="display:none;">
        <div class="pbgprof2-status-row">
          <input type="text" id="pbgprof2-status-input" class="pbgprof2-idcard-status-input" maxlength="20" placeholder="Ваш статус" value="{status}">
          <button type="button" class="pbgprof2-idcard-status-save" id="pbgprof2-status-save" title="Сохранить"><i class='bx bx-check'></i></button>
          <button type="button" class="pbgprof2-idcard-status-cancel" id="pbgprof2-status-cancel" title="Отмена"><i class='bx bx-x'></i></button>
        </div>
        <div class="pbgprof2-status-hint" id="pbgprof2-status-hint">Максимум 20 символов</div>
      </div>
      <script>
        (function() {
          var view = document.getElementById('pbgprof2-status-view');
          var textEl = document.getElementById('pbgprof2-status-text');
          var editBox = document.getElementById('pbgprof2-status-edit');
          var input = document.getElementById('pbgprof2-status-input');
          var saveBtn = document.getElementById('pbgprof2-status-save');
          var cancelBtn = document.getElementById('pbgprof2-status-cancel');
          if (!view || !editBox || !input) {
            return;
          }

          function openEdit() {
            view.style.display = 'none';
            editBox.style.display = 'flex';
            input.focus();
            input.select();
          }
          function closeEdit() {
            editBox.style.display = 'none';
            view.style.display = 'flex';
          }

          var currentValue = view.getAttribute('data-is-empty') === '1' ? '' : (textEl.textContent || '');

          view.addEventListener('click', openEdit);
          cancelBtn.addEventListener('click', function() {
            input.value = currentValue;
            closeEdit();
          });
          input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
              saveBtn.click();
            } else if (e.key === 'Escape') {
              cancelBtn.click();
            }
          });

          saveBtn.addEventListener('click', function() {
            var value = input.value.slice(0, 20);
            saveBtn.disabled = true;
            $.ajax({
              url: '../ajax/actions_a.php',
              type: 'POST',
              data: {
                phpaction: 1,
                token: $('#token').val(),
                edit_user_status: 1,
                user_status: value
              },
              success: function(response) {
                saveBtn.disabled = false;
                var isOk = response.indexOf('icon-ok') !== -1;
                var message = $('<div>').html(response).text().trim();
                if (typeof window.push === 'function') {
                  window.push(message, isOk ? 'success' : 'error');
                }
                if (isOk) {
                  currentValue = value;
                  if (value === '') {
                    textEl.textContent = 'Установить статус';
                    view.setAttribute('data-is-empty', '1');
                  } else {
                    textEl.textContent = value;
                    view.removeAttribute('data-is-empty');
                  }
                  closeEdit();
                }
              },
              error: function() {
                saveBtn.disabled = false;
                if (typeof window.push === 'function') {
                  window.push('Не удалось сохранить статус', 'error');
                }
              }
            });
          });
        })();
      </script>
      {else}
      <div class="pbgprof2-idcard-status">
        <span>{if('{status}' != '')}{status}{else}Статус не установлен{/if}</span>
      </div>
      {/if}

      <script>
        function get_user_steam_info_profile(steamApi) {
          $.ajax({
            type: 'POST',
            url: '../ajax/fast_actions.php',
            data: { phpaction: 1, get_user_steam_info: 1, token: $('#token').val(), steam_api: steamApi },
            dataType: 'json',
            success: function(data) {
              var card = document.getElementById('steam_user');
              if (!card || !data) { return; }
              var img = card.querySelector('.pbgprof2-idcard-contact_logo img');
              var em = card.querySelector('.pbgprof2-idcard-contact_text em');
              if (img && data.avatar) { img.setAttribute('src', data.avatar); img.style.display = ''; }
              if (em && data.login) { em.textContent = data.login; }
            }
          });
        }

        function pbCopyDiscordTag(btn, tag) {
          var done = function() {
            if (typeof window.push === 'function') { window.push('Discord-ник скопирован: ' + tag, 'success'); }
          };
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(tag).then(done, done);
          } else {
            var ta = document.createElement('textarea');
            ta.value = tag;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); } catch(e) {}
            document.body.removeChild(ta);
            done();
          }
        }
      </script>

      {if('{telegram_bound}' == '1' || '{steam_api}' != '0' || '{discord_bound}' == '1')}
      <div class="pbgprof2-idcard-contacts">
          {if('{telegram_bound}' == '1')}
                <a href="https://t.me/{telegram_username}" class="pbgprof2-idcard-contact pbgprof2-idcard-contact--tg" target="_blank" title="Открыть в Telegram">
                  <span class="pbgprof2-idcard-contact_logo"><img src="/templates/solution2/img/profile_menu/pbg_tg.png" alt="Telegram"></span>
                  <span class="pbgprof2-idcard-contact_text">
                    <b>Telegram</b>
                    <em>@{telegram_username}</em>
                  </span>
                  <i class='bx bx-link-external pbgprof2-idcard-contact_arrow'></i>
                </a>
          {/if}
            {if('{steam_api}' != '0')}
                <a target="_blank" href="https://steamcommunity.com/profiles/{steam_api}/" id="steam_user" class="pbgprof2-idcard-contact pbgprof2-idcard-contact--steam" title="Открыть в Steam">
                  <span class="pbgprof2-idcard-contact_logo"><img style="display:none;"></span>
                  <span class="pbgprof2-idcard-contact_text">
                    <b>Steam</b>
                    <em>Загрузка...</em>
                  </span>
                  <i class='bx bx-link-external pbgprof2-idcard-contact_arrow'></i>
                </a>
                <script>
                  get_user_steam_info_profile('{steam_api}');
                </script>
          {/if}
            {if('{discord_bound}' == '1')}
                <button type="button" class="pbgprof2-idcard-contact pbgprof2-idcard-contact--discord" title="Скопировать никнейм Discord" onclick="pbCopyDiscordTag(this, '{discord_username}');">
                  <span class="pbgprof2-idcard-contact_logo"><img src="/templates/solution2/img/profile_menu/pbg_ds.png" alt="Discord"></span>
                  <span class="pbgprof2-idcard-contact_text">
                    <b>Discord</b>
                    <em>{discord_username}</em>
                  </span>
                  <i class='bx bx-copy pbgprof2-idcard-contact_arrow'></i>
                </button>
          {/if}
      </div>
      {/if}
    </div>

    <div class="pbgprof2-main">
     <div class="pbgprof2-tabs-wrap">
       <button type="button" class="pbgprof2-tabs-arrow pbgprof2-tabs-arrow--left" id="pbgprof2-tabs-arrow-left" aria-label="Прокрутить влево"><i class='bx bx-chevron-left'></i></button>
       <div class="pb-profile-tabs pbgprof2-tabs" id="pbgprof2-tabs">
          <a href="#pb-profile-info" class="pb-profile-tab pbgprof2-tab active"><i class='bx bx-home-alt'></i><span>Информация</span></a>
          <a href="#profile_friends" class="pb-profile-tab pbgprof2-tab"><i class='bx bx-group'></i><span>Друзья</span></a>
          <a href="#profile_privileges" class="pb-profile-tab pbgprof2-tab"><i class='bx bx-crown'></i><span>Привилегии</span></a>
          {if(isModuleActive('achievements'))}
          <a href="#profile_progress_game" class="pb-profile-tab pbgprof2-tab"><i class='bx bx-line-chart'></i><span>Игровой прогресс</span></a>
           {/if}
          <a href="#profile_wall" class="pb-profile-tab pbgprof2-tab"><i class='bx bx-message-rounded-dots'></i><span>Стена</span></a>
           {if(isModuleActive('gift'))}
          <a href="#profile_gifts" class="pb-profile-tab pbgprof2-tab"><i class='bx bx-gift'></i><span>Подарки</span></a>
           {/if}
       </div>
       <button type="button" class="pbgprof2-tabs-arrow pbgprof2-tabs-arrow--right" id="pbgprof2-tabs-arrow-right" aria-label="Прокрутить вправо"><i class='bx bx-chevron-right'></i></button>
     </div>
     <script>
       (function() {
         var scroller = document.getElementById('pbgprof2-tabs');
         var leftBtn = document.getElementById('pbgprof2-tabs-arrow-left');
         var rightBtn = document.getElementById('pbgprof2-tabs-arrow-right');
         if (!scroller || !leftBtn || !rightBtn) {
           return;
         }

         function updateArrows() {
           var maxScroll = scroller.scrollWidth - scroller.clientWidth;
           leftBtn.classList.toggle('is-hidden', scroller.scrollLeft <= 2);
           rightBtn.classList.toggle('is-hidden', scroller.scrollLeft >= maxScroll - 2);
           leftBtn.parentElement.classList.toggle('pbgprof2-tabs-wrap--no-scroll', maxScroll <= 2);
         }

         leftBtn.addEventListener('click', function() {
           scroller.scrollBy({ left: -160, behavior: 'smooth' });
         });
         rightBtn.addEventListener('click', function() {
           scroller.scrollBy({ left: 160, behavior: 'smooth' });
         });

         scroller.addEventListener('scroll', updateArrows);
         window.addEventListener('resize', updateArrows);
         updateArrows();
       })();
     </script>

     <div class="pb-profile-tab-content pbgprof2-tab-content">
     <div id="pb-profile-tab-info" class="pb-profile-pane pbgprof2-pane active">
     <div id="pb-profile-info" class="pbgprof2-info-panel">
        <div class="pbgprof2-info-grid">
               {if(is_worthy("m") || is_admin())}
          <div class="pbgprof2-info-item">
            <i class='bx bx-wallet'></i>
            <div class="pbgprof2-info-item-text">
              <span>Баланс</span>
              <strong>{shilings} {{sys()->currency()->lang}}</strong>
            </div>
          </div>
          <div class="pbgprof2-info-item">
            <i class='bx bx-purchase-tag'></i>
            <div class="pbgprof2-info-item-text">
              <span>Скидка</span>
              <strong>{proc}%</strong>
            </div>
          </div>
           	{/if}
          <div class="pbgprof2-info-item">
            <i class='bx bx-joystick'></i>
            <div class="pbgprof2-info-item-text">
              <span>Игровой ник</span>
              <strong>{nick}</strong>
            </div>
          </div>
          <div class="pbgprof2-info-item">
            <i class='bx bx-calendar'></i>
            <div class="pbgprof2-info-item-text">
              <span>Регистрация</span>
              <strong>{regdate}</strong>
            </div>
          </div>
        </div>
     </div>
       <?php
       $pbSiteAchievementsProfile = $_SERVER['DOCUMENT_ROOT'].'/modules_extra/site_achivments/base/profile_include.php';
       if (is_file($pbSiteAchievementsProfile)) {
           try {
               include $pbSiteAchievementsProfile;
           } catch (Throwable $e) {
               if (function_exists('write_log')) {
                   write_log('Profile optional module error [site_achivments]: ' . $e->getMessage());
               }
           }
       }
       ?>
     </div>

   <div id="pb-profile-tab-wall" class="pb-profile-pane pbgprof2-pane">
   <div id="comments" class="mt-3">
		<div class="loader"></div>
	</div>
	<script>load_users_comments({profile_id}, 'first');</script>

      <div id="accordionExample" class="accordion shadow">
        <div class="card">
          <div id="headingTwo">
            <h2 class="mb-0">
              <button type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false"
                aria-controls="collapseTwo"
                class="send_comment">ОСТАВИТЬ КОММЕНТАРИЙ</button>
            </h2>
          </div>
          <div id="collapseTwo" aria-labelledby="headingTwo" data-parent="#accordionExample" class="collapse">
            <div class="card-body p-5">
             <div class="block-profile-stena">
    		 {if('{dell}' != '1')}
                    {if('{checker}' != '1')}
						<div id="add_new_comments">
							<mymytextarea id="text" maxlength="1000"></mymytextarea>

							<div class="smile_input_forum mt-3" style="display: flex;align-items: center;">
								<input id="send_btn" class="send_comment_btn" type="button" onclick="send_user_comment({profile_id});" value="Отправить">
								<div id="smile_btn" class="smile_btn pbg-smiles-trigger" data-target="text" data-tinymce="1" data-tinymce-id="text" data-send-fn="send_user_comment" data-send-args="{profile_id}"></div>
							</div>
						</div>
						<script>
                          (function() {
                            var pbWallEditorReady = false;
                            function pbInitWallEditor() {
                              if (pbWallEditorReady) {
                                return;
                              }
                              pbWallEditorReady = true;
                              init_tinymce(
                                'text', 'lite', "{file_manager_theme}", "{file_manager}", "{{md5($conf->code)}}");
                            }

                            /*
                              #text живёт внутри Bootstrap .collapse (аккордеон
                              "Оставить комментарий"). TinyMCE не может корректно
                              измерить iframe редактора, пока контейнер скрыт
                              (display:none), из-за чего getContent() при отправке
                              комментария падал — и сообщение появлялось только
                              после перезагрузки страницы. Инициализируем редактор
                              в момент реального раскрытия аккордеона.
                            */
                            $('#collapseTwo').on('shown.bs.collapse', pbInitWallEditor);
                            if ($('#collapseTwo').hasClass('show')) {
                              pbInitWallEditor();
                            }
                          })();
						</script>
                    {/if}
            	{/if}
    		 </div>
            </div>
          </div>
        </div>
        </div>
	</div>
   <div id="pb-profile-tab-other" class="pb-profile-pane pbgprof2-pane"></div>
   <div id="pb-profile-tab-progress" class="pb-profile-pane pbgprof2-pane">
      <?php
      $pbGameTimeProfile = $_SERVER['DOCUMENT_ROOT'] . '/modules_extra/game_time/base/index.php';
      if (is_file($pbGameTimeProfile)) {
          try {
              include $pbGameTimeProfile;
          } catch (Throwable $e) {
              if (function_exists('write_log')) {
                  write_log('Profile optional module error [game_time]: ' . $e->getMessage());
              }
          }
      }
      ?>
  <script>
    if (typeof window.get_user_achievs !== 'function') {
      window.get_user_achievs = function() {
        $('#achievs').html('');
        $('.pb-profile-tab[href="#profile_progress_game"]').hide();
      };
    }
  </script>
  <div class="profile_progress">

    <div class="ach-header">
        <div class="ach-header-left">
            <div class="ach-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"></path>
                </svg>
            </div>
            <div>
                <h3 class="ach-title">Достижения</h3>
                <p class="ach-subtitle">Награды на сервере</p>
            </div>
        </div>
        <div class="ach-header-right">
            <div class="ach-badge-compact" id="ach_progress_badge"></div>
            <div class="ach-controls">
                <button id="btn_show_achieves_all" onclick="get_user_achievs('<?php echo $profile->steam_id ?>', 'y');" class="ach-btn">Все</button>
                <button id="btn_show_achieves" onclick="get_user_achievs('<?php echo $profile->steam_id ?>', 'n');" class="ach-btn active" style="display: none;">Полученные</button>
            </div>
        </div>
    </div>
    <div id="achievs" class="ach-grid"></div>
</div>
<script>
    $(document).ready(function() {
        get_user_achievs('<?php echo $profile->steam_id ?>', 'n');
    });
</script>
  </div>
  <div id="pb-profile-tab-friends" class="pb-profile-pane pbgprof2-pane"></div>
  <div id="pb-profile-tab-admins" class="pb-profile-pane pbgprof2-pane"></div>
  <div id="pb-profile-tab-gifts" class="pb-profile-pane pbgprof2-pane"></div>
  </div>
</div>

    <div class="pbgprof2-aside">
      {if('{geo_visible}'=='1')}
      <div class="pbgprof2-aside-card">
        <div class="pbgprof2-aside-title"><i class='bx bx-map'></i> Местоположение</div>
        <div class="pbgprof2-stat-row">
          {if('{geo_flag}'!='')}
          <img src="../{geo_flag}" class="pbgprof2-geo-flag" alt="{geo_country}">
          {else}
          <i class='bx bx-map'></i>
          {/if}
          <div class="pbgprof2-stat-text">
            <span>{if('{geo_city}'!='')}{geo_city}, {/if}{geo_country}</span>
          </div>
        </div>
      </div>
      {/if}

      <div class="pbgprof2-aside-card">
        <div class="pbgprof2-aside-title"><i class='bx bx-line-chart'></i> Статистика профиля</div>
        <div class="pbgprof2-stat-row">
          <i class='bx bx-calendar-check'></i>
          <div class="pbgprof2-stat-text">
            <span>С нами уже</span>
            <strong>{days_with_us} <em>дн.</em></strong>
          </div>
        </div>
        <div class="pbgprof2-stat-row">
          <i class='bx bx-heart'></i>
          <div class="pbgprof2-stat-text">
            <span>Спасибок</span>
            <strong>{thanks}</strong>
          </div>
        </div>
        <div class="pbgprof2-stat-row">
          <i class='bx bx-trending-up'></i>
          <div class="pbgprof2-stat-text">
            <span>Рейтинг</span>
            <strong>{reit}</strong>
          </div>
        </div>
        <div class="pbgprof2-stat-row">
          <i class='bx bx-message-square-detail'></i>
          <div class="pbgprof2-stat-text">
            <span>Сообщений</span>
            <strong>{answers}</strong>
          </div>
        </div>
        <div class="pbgprof2-stat-row pbgprof2-stat-row--action" onclick="pbOpenWarnsModal();" role="button" tabindex="0">
          <i class='bx bx-error-circle'></i>
          <div class="pbgprof2-stat-text">
            <span>Выговоры</span>
            <strong>{warns_count} <em>из {warns_limit}</em></strong>
          </div>
          <i class='bx bx-chevron-right pbgprof2-stat-arrow'></i>
        </div>
      </div>

      <div class="pbgprof2-aside-card">
        <div class="pbgprof2-aside-title"><i class='bx bx-show'></i> Посетители профиля</div>
        {if('{visitors_count}' > '0')}
        <div class="pbgprof2-visitors-stack">
          {visitors}
        </div>
        {else}
        <span class="pbgprof2-visitors-empty">Пусто :(</span>
        {/if}
      </div>

      <?php echo function_exists('pb_premium_render_profile_music_widget') ? pb_premium_render_profile_music_widget((int)'{profile_id}') : ''; ?>
    </div>
  </div>


     <script>
       (function() {
         var widget = document.getElementById('pb_profile_music');
         if (!widget) {
           return;
         }
         var audio = document.getElementById('pb_profile_music_audio');
         var toggleBtn = document.getElementById('pb_profile_music_toggle');
         if (!audio || !toggleBtn) {
           return;
         }

         function setPlayingState(isPlaying) {
           widget.classList.toggle('is-playing', isPlaying);
           toggleBtn.innerHTML = isPlaying ? '<i class="bx bx-pause"></i>' : '<i class="bx bx-play"></i>';
         }

         audio.volume = 0.5;

         var playPromise = audio.play();
         if (playPromise && typeof playPromise.then === 'function') {
           playPromise.then(function() {
             setPlayingState(true);
           }).catch(function() {
             setPlayingState(false);
           });
         }

         toggleBtn.addEventListener('click', function() {
           if (audio.paused) {
             audio.play().then(function() {
               setPlayingState(true);
             }).catch(function() {});
           } else {
             audio.pause();
             setPlayingState(false);
           }
         });

         audio.addEventListener('pause', function() { setPlayingState(false); });
         audio.addEventListener('play', function() { setPlayingState(true); });
       })();
     </script>

  <div class="right_profile">
    <script>
function teams_send_invite(to_uid,team_id,tok){
    fetch("/modules_extra/teams/ajax/invite.php",{method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"phpaction=1&action=send_invite&to_user_id="+to_uid+"&team_id="+team_id+"&token="+encodeURIComponent(tok)
    }).then(r=>r.json()).then(r=>{
        if(typeof push==="function")push(r.msg,r.status==="ok"?"success":"error");else alert(r.msg);
        if(r.status==="ok")setTimeout(()=>location.reload(),900);
    }).catch(()=>{});
}
function teams_answer_invite(inv_id,answer,tok){
    fetch("/modules_extra/teams/ajax/invite.php",{method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"phpaction=1&action=answer_invite&invite_id="+inv_id+"&answer="+answer+"&token="+encodeURIComponent(tok)
    }).then(r=>r.json()).then(r=>{
        if(typeof push==="function")push(r.msg,r.status==="ok"?"success":"error");else alert(r.msg);
        if(r.status==="ok"){
            var el=document.getElementById("teams_inv_"+inv_id);
            if(el){el.style.opacity="0";el.style.transition=".3s";setTimeout(()=>el.remove(),300);}
            if(answer==="accept")setTimeout(()=>location.reload(),900);
        }
    }).catch(()=>{});
}
</script>
<div id="pb-profile-prime-source">
	<?php
    $pbPrimeModelsProfile = $_SERVER['DOCUMENT_ROOT'] . '/modules_extra/prime_models/base/profile_include.php';
    if (is_file($pbPrimeModelsProfile)) {
        try {
            include $pbPrimeModelsProfile;
        } catch (Throwable $e) {
            if (function_exists('write_log')) {
                write_log('Profile optional module error [prime_models]: ' . $e->getMessage());
            }
        }
    }
    ?>
</div></div>
<div id="pb-profile-gifts-source">
    <span id="profile_gifts" class="pb-profile-anchor"></span>
	<?php
    $pbGiftProfile = $_SERVER['DOCUMENT_ROOT'] . '/modules_extra/gift/base/index.php';
    if (is_file($pbGiftProfile)) {
        try {
            include $pbGiftProfile;
        } catch (Throwable $e) {
            if (function_exists('write_log')) {
                write_log('Profile optional module error [gift]: ' . $e->getMessage());
            }
        }
    }
    ?>
</div>
 	<div class="profile_r_block" id="profile_friends_block">
       <div class="flex_head_block">
     		<div class="flex_head_l">
      			<i class='bx bx-group' ></i>
                <div class="flex_top_usr">
                  <span class="name_top_usr">Друзья</span>
                  <span class="desp_top_usr">Список друзей</span>
                </div>
         	</div>
         	<a class="all_friends_btn" href="../friends?id={profile_id}">
				<span>Показать всех</span>
			</a>
    	</div>
		<div id="friends">
            {friends}
		</div>
    </div>
    
     <div class="" id="profile_activity_block">
          <div id="bc_profile_widget" data-user-id="{profile_id}"></div>
<script>
$(function(){
    var box = $('#bc_profile_widget');
    if(!box.length) return;
    <?php if (!is_file($_SERVER['DOCUMENT_ROOT'] . '/modules_extra/bounty_contracts/ajax/actions.php')): ?>
    box.html('');
    return;
    <?php endif; ?>

    $.post('/modules_extra/bounty_contracts/ajax/actions.php', {
        action: 'profile_widget',
        user_id: box.data('user-id')
    }, function(res){
        if(res && res.status === 'ok' && res.html) {
            box.html(res.html);
        } else {
            box.html('');
        }
    }, 'json').fail(function(){
        box.html('');
    });
});
</script>
       
     </div>
    
    <div class="profile_r_block" id="profile_privileges">
       <div class="flex_head_block" style="margin-bottom: 12px;">
         <div class="flex_head_l">
         	<i class='bx bx-star' ></i>
                <div class="flex_top_usr">
                  <span class="name_top_usr">Привилегии</span>
                  <span class="desp_top_usr">Купленные услуги</span>
                </div>
          	</div>
    	</div>
     <div class="table-adaptive">
		{func Widgets:user_admins('{profile_id}')}
	</div>

    <?php $pbRconShopBuys = ''; if(class_exists('Widgets')) { $pbRconShopBuys = (new Widgets($pdo, $tpl))->user_rcon_shop_buys((int) '{profile_id}'); } ?>
    <?php if($pbRconShopBuys !== ''): ?>
    <div class="flex_head_block" style="margin-bottom: 12px;margin-top: 22px;">
      <div class="flex_head_l">
        <i class='bx bx-server' ></i>
        <div class="flex_top_usr">
          <span class="name_top_usr">Услуги Rcon Shopа</span>
          <span class="desp_top_usr">Купленные услуги на серверах</span>
        </div>
      </div>
    </div>
    <div class="mst-grid"><?php echo $pbRconShopBuys; ?></div>
    <?php endif; ?>

    <div id="pb-profile-prime-slot"></div>
  </div>
</div>

<script>
$(function() {
    var tabs = {
        '#pb-profile-info': '#pb-profile-tab-info',
        '#profile_wall': '#pb-profile-tab-wall',
        '#comments': '#pb-profile-tab-wall',
        '#profile_other': '#pb-profile-tab-other',
        '#profile_friends': '#pb-profile-tab-friends',
        '#profile_privileges': '#pb-profile-tab-admins',
        '#profile_progress_game': '#pb-profile-tab-progress',
        '#profile_gifts': '#pb-profile-tab-gifts'
    };

    $('#profile_friends_block').appendTo('#pb-profile-tab-friends');
    $('#profile_privileges').appendTo('#pb-profile-tab-admins');
    $('#pb-profile-prime-source').appendTo('#pb-profile-prime-slot');
    $('#pb-profile-gifts-source').appendTo('#pb-profile-tab-gifts');
    $('#profile_activity_block').appendTo('#pb-profile-tab-other');

    $('.pb-profile-tab').on('click', function(e) {
        var target = $(this).attr('href');
        if (!tabs[target]) {
            return;
        }
        e.preventDefault();
        target = tabs[target];

        $('.pb-profile-tab').removeClass('active');
        $(this).addClass('active');
        $('.pb-profile-pane').removeClass('active');
        $(target).addClass('active');
    });
});
</script>

  <div class="row mt-5">
    {if('{dell}' != '1')}
    <script>
      function myFunction() {
        document.getElementById("myDropdown").classList.toggle("show");
      }

      window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
          var dropdowns = document.getElementsByClassName("dropdown-content");
          var i;
          for (i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
              openDropdown.classList.remove('show');
            }
          }
        }
      }
    </script>
    {else}
    <h2>Пользователь удален</h2> {/if}
    <script type="text/javascript" >
       function Copy(containerid) {
       let mymytextarea = document.createElement('mymytextarea');
       mymytextarea.id = 'temp';
       mymytextarea.style.height = 0;
       document.body.appendChild(mymytextarea);
       mymytextarea.value = document.getElementById(containerid).innerText;
       let selector = document.querySelector('#temp');
       selector.select();
       document.execCommand('copy');
       document.body.removeChild(mymytextarea);
     }
    </script>
    
    <!-- Модальное окно меню администратора -->
  <script>
    $('#voucher').modal('hide');
  </script>
  <div id="admin_menu" class="modal fade">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <div class="modal_head">
          <div class="flex_modal_head">
          <span>Панель администратора</span> 
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
       	<div class="admin_menu_block">
          <div class="left_block_admin">

                  <div class="skidka-right">
                    <span class="procent_name_menu">Баланс</span>
                    <span id="proc" class="procent_admin_menu">{shilings} {{sys()->currency()->lang}}</span>
                  </div>
                  <div class="skidka-right">
                    <span class="procent_name_menu">Установленная скидка</span>
                    <span id="proc" class="procent_admin_menu">{proc}%</span>
                  </div>
			 </div>
          
			<div class="btn_admins_menu">
              {if(is_worthy("m") || is_admin())}
              <a class="btn_admin_func" onclick="give_money({profile_id});"><i class='bx bx-plus-circle' ></i> Дать деньги</a>
              <a class="btn_admin_func" onclick="pick_up_money({profile_id});"><i class='bx bx-minus-circle' ></i> Забрать деньги</a> {/if} {if(is_worthy("c") || is_admin())}
              <a class="btn_admin_func" onclick="take_proc({profile_id});"><i class='bx bx-purchase-tag' ></i> Установить скидку</a> {/if}
              {if(is_worthy("f") || is_admin())}
              <a class="btn_admin_func" href="../edit_user?id={profile_id}"><i class='bx bx-message-square-edit' ></i> Редактировать</a>
				<a class="btn_admin_func" onclick="edit_very({profile_id});"><i class='bx bx-badge-check' ></i> Установить верификацию</a>
              {/if}
             </div>
          </div>
          </div>
        </div>
      </div>      </div>

  <div id="pbg_warns_modal" class="modal fade">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <div class="modal_head">
            <div class="flex_modal_head">
              <span>Выговоры</span>
              <div class="line_title">
                <span class="line_purse1"></span>
                <span class="line_purse"></span>
              </div>
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
        <div class="modal-body">
          <div class="pbg-warns">
            <div class="pbg-warns__summary" id="pbg_warns_summary">
              <i class='bx bx-error-circle'></i>
              <span>Активных выговоров: <b>{warns_count}</b> из {warns_limit}</span>
            </div>
            <div class="pbg-warns__buy" id="pbg_warns_buy" style="display:none;"></div>
            <div class="pbg-warns__list" id="pbg_warns_list">
              <div class="pbg-warns__empty">Загрузка…</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
function pbOpenWarnsModal() {
    var list = document.getElementById('pbg_warns_list');
    if (list) { list.innerHTML = '<div class="pbg-warns__empty">Загрузка…</div>'; }
    $('#pbg_warns_modal').modal('show');

    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function(ch){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch];
        });
    }

    $.ajax({
        type: 'POST',
        url: '{site_host}ajax/actions_a.php',
        data: {
            phpaction: 1,
            token: $('#token').val(),
            pb_profile_warns: 1,
            user_id: '{profile_id}'
        },
        dataType: 'json',
        success: function(res) {
            if (!list) { return; }
            if (!res || String(res.status) !== '1') {
                list.innerHTML = '<div class="pbg-warns__empty">Не удалось загрузить данные</div>';
                return;
            }

            var summary = document.getElementById('pbg_warns_summary');
            if (summary) {
                summary.innerHTML = "<i class='bx bx-error-circle'></i><span>Активных выговоров: <b>" + res.active + "</b> из " + res.limit + "</span>";
            }

            var buyBox = document.getElementById('pbg_warns_buy');
            if (buyBox) {
                if (res.can_buy) {
                    buyBox.style.display = '';
                    buyBox.innerHTML = '<div class="pbg-warns__buy-text">' +
                            '<b>Снять выговор досрочно</b>' +
                            '<small>Спишется ' + res.price + ' с баланса. Снимается самый старый активный выговор.</small>' +
                        '</div>' +
                        '<button type="button" class="pbg-warns__buy-btn" onclick="pbBuyWarnRemoval();">' +
                            "<i class='bx bx-purchase-tag'></i> Снять за " + res.price +
                        '</button>';
                } else {
                    buyBox.style.display = 'none';
                    buyBox.innerHTML = '';
                }
            }

            if (!res.rows || !res.rows.length) {
                list.innerHTML = '<div class="pbg-warns__empty">Выговоров нет</div>';
                return;
            }

            var html = '';
            res.rows.forEach(function(w){
                html += '<div class="pbg-warn' + (w.active ? '' : ' is-removed') + '">' +
                    '<div class="pbg-warn__top">' +
                        '<span class="pbg-warn__status' + (w.active ? '' : ' is-off') + '">' + (w.active ? 'Активен' : 'Снят') + '</span>' +
                        '<span class="pbg-warn__date">' + esc(w.date) + '</span>' +
                    '</div>' +
                    '<div class="pbg-warn__reason">' + esc(w.reason) + '</div>' +
                    '<div class="pbg-warn__meta"><i class="bx bx-user"></i> Выдал: <b>' + esc(w.issued_by || '—') + '</b></div>' +
                    (w.active ? '' :
                        '<div class="pbg-warn__meta pbg-warn__meta--off"><i class="bx bx-check-circle"></i> Снял: <b>' + esc(w.removed_by || '—') + '</b>' +
                        (w.removed_at ? ' · ' + esc(w.removed_at) : '') +
                        (w.removed_reason ? '<br><span>Причина: ' + esc(w.removed_reason) + '</span>' : '') + '</div>') +
                    '</div>';
            });
            list.innerHTML = html;
        },
        error: function() {
            if (list) { list.innerHTML = '<div class="pbg-warns__empty">Ошибка соединения</div>'; }
        }
    });
}

function pbBuyWarnRemoval() {
    if (!confirm('Снять один выговор за указанную сумму? Средства спишутся с баланса.')) { return; }

    var btn = document.querySelector('.pbg-warns__buy-btn');
    if (btn) { btn.disabled = true; }

    $.ajax({
        type: 'POST',
        url: '{site_host}ajax/actions_a.php',
        data: {
            phpaction: 1,
            token: $('#token').val(),
            pb_warn_buy_removal: 1
        },
        dataType: 'json',
        success: function(res) {
            var ok = res && String(res.status) === '1';
            if (window.PBToast) {
                PBToast.show(ok ? 'success' : 'error', (res && res.data) || (ok ? 'Готово' : 'Не удалось снять выговор'));
            }
            if (ok) {
                pbOpenWarnsModal();
                setTimeout(function(){ window.location.reload(); }, 1200);
            } else if (btn) {
                btn.disabled = false;
            }
        },
        error: function() {
            if (btn) { btn.disabled = false; }
            if (window.PBToast) { PBToast.error('Ошибка соединения'); }
        }
    });
}
</script>
