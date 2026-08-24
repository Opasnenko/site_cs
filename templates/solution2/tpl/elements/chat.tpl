 <!-- Заголовок -->

        <div class="chat_block pbg_chat_split">
          {include file="/elements/online_widget.tpl"}
          <div class="block chat pbg_chat">
<div class="heading_block_chat">
      <div class="flex_heading_chat">
        <i class="bx bx-message-detail"></i>
        <div class="heading_chat_info">
          <span>Чат проекта</span>
          <text>Общение</text>
        </div>
      </div>
      <div class="double_btns_chat">
        <span class="pbg_chat_unread" id="pbg_chat_unread" onclick="pbg_chat_scroll_to_bottom(true);">
          <i class='bx bx-chevron-down'></i>
          <b id="pbg_chat_unread_count">0</b> новых
        </span>
        <button data-target="#ruleschat" data-toggle="modal" class="btn_heading_rules">Правила чата</button>
        {if(function_exists('pb_can_moderate') && pb_can_moderate())}
        <button type="button" class="btn_heading_rules pbg_moder_panel_btn" onclick="pbg_moder_panel_open();">
          <i class='bx bx-shield-quarter'></i> Панель модератора
        </button>
        {/if}
      </div>
    </div>


	<div id="chat" class="collapse">
		<form>
			<div id="drop_zone" class="">
				<div id="drop_mask"></div>

				<div id="pbg_chat_pinned" class="pbg_chat_pinned disp-n"></div>

				<div id="chat_messages">
					<div class="loader"></div>
				</div>

				<div id="pbg_chat_typing" class="pbg_chat_typing"></div>

				<div id="chat_sound_playes"></div>
				<input id="load_val" type="hidden" value="1">
				<input id="last_mess" type="hidden" value="">
				<input id="stop_sending" type="hidden" value="0">
			</div>
			<?php $pbgChatBanInfo = (is_auth() && function_exists('pb_chat_get_ban_info')) ? pb_chat_get_ban_info($pdo, $_SESSION['id']) : null; ?>
			{if(is_auth())}
				<?php if ($pbgChatBanInfo): ?>
				<div class="pbg_chat_banned" id="pbg_chat_banned_box">
					<div class="pbg_chat_banned__icon"><i class='bx bx-block'></i></div>
					<div class="pbg_chat_banned__text">
						<span class="pbg_chat_banned__title">Вы заблокированы в чате</span>
						<span class="pbg_chat_banned__reason">Причина: <?php echo htmlspecialchars($pbgChatBanInfo['reason'] !== '' ? $pbgChatBanInfo['reason'] : 'не указана', ENT_QUOTES, 'UTF-8'); ?></span>
						<span class="pbg_chat_banned__until">Дата снятия: <?php echo $pbgChatBanInfo['expires_at'] > 0 ? date('d.m.Y H:i', $pbgChatBanInfo['expires_at']) : 'навсегда'; ?></span>
					</div>
				</div>
				<?php else: ?>
				<div class="input-group" id="pbg_chat_input_box">
					<div class="smile_input" >
						<input style="border-radius: 50px;" id="message_input" type="text" class="form-control" placeholder="Написать сообщение...">
						<div id="smile_btn" class="smile_btn pbg-smiles-trigger" data-target="message_input" data-send-fn="chat_send_message"></div>
					</div>
					<span class="input-group-btn">
						<a id="send_butto" class="btn_message" onclick="chat_send_message();"><i class='bx bxs-send' ></i></a>
					</span>
				</div>
				<?php endif; ?>
			{else}
			<div class="auth-to-write mb-3">
				<span class="d-none d-lg-inline-block">
                  <span class="chat_icon"><i class='bx bx-message-square-minus'></i></span> Чтобы общаться, Вам необходимо авторизироваться
				</span>
			</div>
			{/if}
		</form>
	</div>

</div>
          </div>


<script>
	if(get_cookie('chat_collapse') != 2) {
		$("#chat").addClass('show');
	}

	function set_chat_cookie() {
		if(get_cookie('chat_collapse') == 1) {
			set_cookie('chat_collapse', 2);
		} else {
			set_cookie('chat_collapse', 1);
		}
	}

    //drag&drop
	$(document).ready(function(){
		var dropZone = $('#drop_zone');
		var dropMask = $('#drop_mask');
		var maxFileSize = 2*1024*1024; //2мб макс размер
		var drop_file = true; //false - выкл , true - вкл | загрузка изображений в чат

		if (typeof(window.FileReader) != 'undefined' && drop_file) {
			dropZone[0].ondragover = function(event) {
				dropZone.addClass('hover');
				dropMask.show();
				return false;
			};

			dropMask[0].ondragleave = function() {
				dropZone.removeClass('hover');
				dropMask.hide();
				return false;
			};

			dropMask[0].ondrop = function(event) {
				event.preventDefault();
				dropMask.hide();
				dropZone.removeClass('hover');
				dropZone.addClass('loader');

				if(event.dataTransfer.files[0] == undefined) {
					dropZone.removeClass('loader');
					show_noty('Down', 'error', '<a>Неверный тип файла</a>', '3000');
					dropZone.addClass('error');
					setTimeout(function(){
						dropZone.removeClass('error');
					}, 2000);
					return false;
				} else {
					var file = event.dataTransfer.files[0];
				}

				if (file.size > maxFileSize) {
					dropZone.removeClass('loader');
					show_noty('Down', 'error', '<a>Файл слишком большой</a>', '3000');
					dropZone.addClass('error');
					setTimeout(function(){
						dropZone.removeClass('error');
					}, 2000);
					return false;
				}

				var data = new FormData;
				data.append("file", file);
				data.append("token", $('#token').val());
				data.append("drop_img", '1');
				data.append("phpaction", '1');
				data.append("id", '{id}');

				clearInterval(chat_interval);
				NProgress.start();
				$.ajax({
					type: "POST",
					url: "../ajax/chat_actions.php",
					data: data,
					processData: false,
					contentType: false,
					dataType: "json",

					success: function(result) {
						dropZone.removeClass('loader');
						NProgress.done();
						chat_get_messages(1);
						if(result.status == 1){
							setTimeout(show_ok, 500);
						} else {
							setTimeout(show_error, 500);
							show_noty('Down', 'error', '<a>'+result.data+'</a>', '3000');
							dropZone.addClass('error');
							setTimeout(function(){
								dropZone.removeClass('error');
							}, 2000);
						}
					},
					error: function(result){
						dropZone.removeClass('loader');
						NProgress.done();
						chat_get_messages(1);
					}
				});
			};
		}
	});

	//init chat
	chat_first_messages();

	// PBGame CMS 3.1.0.7: initial chat load must always land on the newest message.
	// Browsers may restore a nested scroll position after reload, so keep the initial
	// bottom position only until the first message batch is rendered or the user interacts.
	(function(){
		var box = document.getElementById('chat_messages');
		if (!box) return;
		var userInteracted = false;
		var stopped = false;
		var stop = function(){ userInteracted = true; };
		box.addEventListener('wheel', stop, {passive:true});
		box.addEventListener('touchstart', stop, {passive:true});
		box.addEventListener('pointerdown', stop, {passive:true});

		function forceBottom(){
			if (stopped || userInteracted) return;
			box.scrollTop = box.scrollHeight;
		}

		var observer = new MutationObserver(function(){
			if (box.querySelector('.chat_message')) {
				forceBottom();
				setTimeout(forceBottom, 80);
				setTimeout(forceBottom, 300);
				setTimeout(forceBottom, 800);
			}
		});
		observer.observe(box, {childList:true, subtree:false});
		window.addEventListener('pageshow', function(){
			setTimeout(forceBottom, 0);
			setTimeout(forceBottom, 250);
		});
		setTimeout(function(){ stopped = true; observer.disconnect(); }, 4000);
	})();

	set_enter('#message_input', 'chat_send_message()');

	var block = document.getElementById("chat_messages");
	var load_val = $('#load_val').val();
	block.onscroll = function() {
		if((block.scrollTop < 300) && (load_val == $('#load_val').val())) {
			$('#load_val').val(+load_val + 1);
			chat_load_messages();
		}
	}

	idleTimer = null;
	idleState = false;
	idleWait = 600000;

	$(document).ready(function(){
		$(document).bind('mousemove keydown scroll', function(){
			clearTimeout(idleTimer);
			if(idleState == true){ 
				reset_page();
			}

			idleState = false;
			idleTimer = setTimeout(function(){ 
				clearInterval(chat_interval);
				$("#chat").fadeOut();
				$("#passive").fadeIn();
				$("#passive b").append(idleWait/1000/60);
				idleState = true; 
			}, idleWait);
		});
		$("body").trigger("mousemove");
	});
</script>

<script>
(function() {
	var CHAT_SOUND_URL = '{site_host}ajax/addons/toasts/sounds/sound-18.mp3';
	var TYPING_IDLE_MS = 2500;
	var TYPING_PING_MS = 2000;
	var UNREAD_KEY = 'pbg_chat_last_read_id';

	var messagesBox = document.getElementById('chat_messages');
	var typingBox = document.getElementById('pbg_chat_typing');
	var unreadBadge = document.getElementById('pbg_chat_unread');
	var unreadCount = document.getElementById('pbg_chat_unread_count');
	var input = document.getElementById('message_input');

	var pbgUnread = 0;
	var pbgTypingPingTimer = null;
	var pbgIsTyping = false;

	function isNearBottom() {
		if (!messagesBox) return true;
		return (messagesBox.scrollHeight - messagesBox.scrollTop - messagesBox.clientHeight) < 120;
	}

	function pbgEscapeHtmlAttr(str) {
		var div = document.createElement('div');
		div.textContent = str == null ? '' : String(str);
		return div.innerHTML;
	}

	window.pbgShowBannedBox = function(reason, expiresAt) {
		var inputBox = document.getElementById('pbg_chat_input_box');
		if (!inputBox) return;
		var box = document.createElement('div');
		box.className = 'pbg_chat_banned';
		box.id = 'pbg_chat_banned_box';
		box.innerHTML =
			'<div class="pbg_chat_banned__icon"><i class="bx bx-block"></i></div>' +
			'<div class="pbg_chat_banned__text">' +
				'<span class="pbg_chat_banned__title">Вы заблокированы в чате</span>' +
				'<span class="pbg_chat_banned__reason">Причина: ' + pbgEscapeHtmlAttr(reason || 'не указана') + '</span>' +
				'<span class="pbg_chat_banned__until">Дата снятия: ' + pbgEscapeHtmlAttr(expiresAt || 'навсегда') + '</span>' +
			'</div>';
		inputBox.replaceWith(box);
	};

	window.pbg_chat_scroll_to_bottom = function(smooth) {
		if (!messagesBox) return;
		if (smooth && messagesBox.scrollTo) {
			messagesBox.scrollTo({ top: messagesBox.scrollHeight, behavior: 'smooth' });
		} else {
			messagesBox.scrollTop = messagesBox.scrollHeight;
		}
		pbgUnread = 0;
		updateUnreadBadge();
		var lastMess = $('#last_mess').val();
		if (lastMess) {
			try { localStorage.setItem(UNREAD_KEY, lastMess); } catch (e) {}
		}
	};

	function updateUnreadBadge() {
		if (!unreadBadge) return;
		if (pbgUnread > 0) {
			unreadCount.textContent = pbgUnread;
			unreadBadge.classList.add('is-visible');
		} else {
			unreadBadge.classList.remove('is-visible');
		}
	}

	function pbgPlayChatSound() {
		try {
			var prefs = (typeof pbToastSoundGetPrefs === 'function') ? pbToastSoundGetPrefs() : null;
			if (prefs && prefs.enabled === false) return;
			var audio = new Audio(CHAT_SOUND_URL);
			audio.volume = 0.55;
			audio.play().catch(function(){});
		} catch (e) {}
	}

	document.addEventListener('click', function(e) {
		document.querySelectorAll('.pbg_chat_menu.is-visible').forEach(function(menu) {
			if (!menu.contains(e.target)) menu.classList.remove('is-visible');
		});
	});

	window.pbg_chat_toggle_menu = function(e, id) {
		e.stopPropagation();
		var menu = document.getElementById('pbg_chat_menu_' + id);
		if (!menu) return;
		var wasVisible = menu.classList.contains('is-visible');
		document.querySelectorAll('.pbg_chat_menu.is-visible').forEach(function(m) { m.classList.remove('is-visible'); });
		if (!wasVisible) menu.classList.add('is-visible');
	};

	var pbgEditOriginalText = {};

	window.pbg_chat_menu_edit = function(id) {
		var menu = document.getElementById('pbg_chat_menu_' + id);
		if (menu) menu.classList.remove('is-visible');
		var textarea = document.getElementById('message_text_e_' + id);
		if (textarea) pbgEditOriginalText[id] = textarea.value;
		$('#message_text_' + id).addClass('disp-n');
		$('#message_edit_box_' + id).removeClass('disp-n');
		if (textarea) textarea.focus();
	};

	window.pbg_chat_menu_cancel = function(id) {
		var textarea = document.getElementById('message_text_e_' + id);
		if (textarea && pbgEditOriginalText.hasOwnProperty(id)) {
			textarea.value = pbgEditOriginalText[id];
		}
		$('#message_edit_box_' + id).addClass('disp-n');
		$('#message_text_' + id).removeClass('disp-n');
	};

	window.pbg_chat_menu_save = function(id) {
		var textarea = document.getElementById('message_text_e_' + id);
		var text = textarea ? textarea.value : '';
		if (typeof NProgress !== 'undefined') NProgress.start();
		$.ajax({
			type: 'POST',
			url: '../ajax/actions_b.php',
			data: 'phpaction=1&token=' + $('#token').val() + '&save_chat_message=1&id=' + id + '&text=' + encodeURIComponent(text),
			dataType: 'json',
			success: function(res) {
				if (typeof NProgress !== 'undefined') NProgress.done();
				if (res && res.status == 1) {
					var textChat = document.querySelector('#message_text_' + id + ' .text_chat');
					if (textChat) textChat.innerHTML = res.text;
					pbgEditOriginalText[id] = text;
					if (typeof show_ok === 'function') setTimeout(show_ok, 300);
				} else if (typeof show_error === 'function') {
					setTimeout(show_error, 300);
				}
				$('#message_edit_box_' + id).addClass('disp-n');
				$('#message_text_' + id).removeClass('disp-n');
			},
			error: function() {
				if (typeof NProgress !== 'undefined') NProgress.done();
				$('#message_edit_box_' + id).addClass('disp-n');
				$('#message_text_' + id).removeClass('disp-n');
			}
		});
	};

	window.pbg_chat_menu_delete = function(id) {
		var menu = document.getElementById('pbg_chat_menu_' + id);
		if (menu) menu.classList.remove('is-visible');
		if (typeof dell_chat_message === 'function') dell_chat_message(id);
	};

	function pbgApplyModeratorState(userId, grant) {
		if (!messagesBox) return;
		var authorLinks = messagesBox.querySelectorAll('.avatar_login a[href*="id=' + userId + '"]');
		authorLinks.forEach(function(authorLink) {
			var avatarLogin = authorLink.closest('.avatar_login');
			if (!avatarLogin) return;
			var msgEl = avatarLogin.closest('.chat_message');
			if (!msgEl) return;

			var existingBadge = avatarLogin.querySelector('.pbg_moder_badge');
			if (grant == 1 && !existingBadge) {
				var badge = document.createElement('span');
				badge.className = 'pbg_moder_badge';
				badge.textContent = 'MODER';
				var manageBtn = avatarLogin.querySelector('.pbg_chat_manage');
				if (manageBtn) {
					avatarLogin.insertBefore(badge, manageBtn);
				} else {
					avatarLogin.appendChild(badge);
				}
			} else if (grant != 1 && existingBadge) {
				existingBadge.remove();
			}

			var moderatorMenuItem = msgEl.querySelector('[onclick^="pbg_chat_menu_moderator"]');
			if (moderatorMenuItem) {
				if (grant == 1) {
					moderatorMenuItem.setAttribute('onclick', "pbg_chat_menu_moderator('" + userId + "', 0);");
					moderatorMenuItem.innerHTML = "<i class='bx bx-user-x'></i> Снять модератора";
				} else {
					moderatorMenuItem.setAttribute('onclick', "pbg_chat_menu_moderator('" + userId + "', 1);");
					moderatorMenuItem.innerHTML = "<i class='bx bx-shield-plus'></i> Выдать модератора";
				}
			}
		});
	}

	window.pbg_chat_menu_moderator = function(userId, grant) {
		$.ajax({
			type: 'POST',
			url: '../ajax/actions_b.php',
			data: 'phpaction=1&token=' + $('#token').val() + '&chat_set_moderator=1&id=' + userId + '&grant=' + grant,
			dataType: 'json',
			success: function(res) {
				if (res && res.status == 1) {
					if (typeof show_ok === 'function') show_ok();
					pbgApplyModeratorState(userId, grant);
				} else if (typeof show_error === 'function') {
					show_error();
				}
			}
		});
	};

	window.pbg_chat_toggle_ban_box = function(e, id) {
		e.stopPropagation();
		var box = document.getElementById('pbg_chat_ban_box_' + id);
		if (!box) return;
		document.querySelectorAll('.pbg_chat_ban_box').forEach(function(b) {
			if (b !== box) b.classList.add('disp-n');
		});
		box.classList.toggle('disp-n');
	};

	window.pbg_chat_ban_user = function(userId, id) {
		var dateInput = document.getElementById('pbg_chat_ban_date_' + id);
		var reasonInput = document.getElementById('pbg_chat_ban_reason_' + id);
		var dateVal = dateInput ? dateInput.value : '';
		var reason = reasonInput ? reasonInput.value : '';

		if (!dateVal) {
			if (typeof show_noty === 'function') {
				show_noty('Down', 'error', '<a>Укажите дату окончания блокировки</a>', '3000');
			}
			return;
		}

		$.ajax({
			type: 'POST',
			url: '../ajax/actions_b.php',
			data: 'phpaction=1&token=' + $('#token').val() + '&chat_ban_user=1&id=' + userId + '&expires=' + encodeURIComponent(dateVal) + '&reason=' + encodeURIComponent(reason),
			dataType: 'json',
			success: function(res) {
				if (res && res.status == 1) {
					if (typeof show_ok === 'function') show_ok();
					var box = document.getElementById('pbg_chat_ban_box_' + id);
					if (box) box.classList.add('disp-n');
					var menu = document.getElementById('pbg_chat_menu_' + id);
					if (menu) menu.classList.remove('is-visible');
				} else if (typeof show_error === 'function') {
					show_error();
				}
			}
		});
	};

	var pbgPinnedBox = document.getElementById('pbg_chat_pinned');
	var pbgPinnedMessageId = 0;

	function pbgRenderPinned(data) {
		if (!pbgPinnedBox) return;
		if (!data || !data.message_id) {
			pbgPinnedMessageId = 0;
			pbgPinnedBox.classList.add('disp-n');
			pbgPinnedBox.innerHTML = '';
			return;
		}
		pbgPinnedMessageId = data.message_id;
		pbgPinnedBox.innerHTML =
			'<i class="bx bxs-pin pbg_chat_pinned__icon"></i>' +
			'<div class="pbg_chat_pinned__body">' +
				'<span class="pbg_chat_pinned__login">' + pbgEscapeHtmlAttr(data.login || '') + '</span>' +
				'<span class="pbg_chat_pinned__text">' + pbgEscapeHtmlAttr(data.text || '') + '</span>' +
			'</div>' +
			'<span class="pbg_chat_pinned__unpin" onclick="pbg_chat_unpin();" title="Открепить"><i class="bx bx-x"></i></span>';
		pbgPinnedBox.classList.remove('disp-n');
	}

	function pbgUpdatePinMenuItems() {
		document.querySelectorAll('[id^="pbg_chat_pin_item_"]').forEach(function(el) {
			var msgId = el.id.replace('pbg_chat_pin_item_', '');
			if (pbgPinnedMessageId && String(pbgPinnedMessageId) === String(msgId)) {
				el.innerHTML = "<i class='bx bx-x'></i> Открепить";
				el.setAttribute('onclick', "pbg_chat_unpin();");
			} else {
				el.innerHTML = "<i class='bx bx-pin'></i> Закрепить";
				el.setAttribute('onclick', "pbg_chat_menu_pin('" + msgId + "');");
			}
		});
	}

	function pbgPollPinned() {
		$.ajax({
			type: 'POST',
			url: '../ajax/chat_data.php',
			data: 'phpaction=1&token=' + $('#token').val() + '&chat_get_pinned=1',
			dataType: 'json',
			success: function(res) {
				if (res && res.status == 1) {
					pbgRenderPinned(res);
				} else {
					pbgRenderPinned(null);
				}
				pbgUpdatePinMenuItems();
			}
		});
	}

	window.pbg_chat_menu_pin = function(id) {
		var menu = document.getElementById('pbg_chat_menu_' + id);
		if (menu) menu.classList.remove('is-visible');
		$.ajax({
			type: 'POST',
			url: '../ajax/actions_b.php',
			data: 'phpaction=1&token=' + $('#token').val() + '&chat_pin_message=1&id=' + id,
			dataType: 'json',
			success: function(res) {
				if (res && res.status == 1) {
					if (typeof show_ok === 'function') show_ok();
					pbgPollPinned();
				} else if (typeof show_error === 'function') {
					show_error();
				}
			}
		});
	};

	window.pbg_chat_unpin = function() {
		$.ajax({
			type: 'POST',
			url: '../ajax/actions_b.php',
			data: 'phpaction=1&token=' + $('#token').val() + '&chat_unpin_message=1',
			dataType: 'json',
			success: function(res) {
				if (res && res.status == 1) {
					if (typeof show_ok === 'function') show_ok();
					pbgRenderPinned(null);
					pbgUpdatePinMenuItems();
				} else if (typeof show_error === 'function') {
					show_error();
				}
			}
		});
	};

	pbgPollPinned();
	setInterval(pbgPollPinned, 5000);

	if (messagesBox) {
		messagesBox.addEventListener('scroll', function() {
			if (isNearBottom()) {
				pbgUnread = 0;
				updateUnreadBadge();
			}
		});
	}

	var pbgOriginalGetMessages = window.chat_get_messages;
	window.chat_get_messages = function(e) {
		var wasNearBottom = isNearBottom();
		var countBefore = messagesBox ? messagesBox.querySelectorAll('.chat_message').length : 0;
		if (typeof pbgOriginalGetMessages === 'function') {
			pbgOriginalGetMessages(e);
		}
		setTimeout(function() {
			pbgUpdatePinMenuItems();
			var countAfter = messagesBox ? messagesBox.querySelectorAll('.chat_message').length : 0;
			var gotNew = countAfter > countBefore;
			if (!gotNew) return;
			if (e != 1) {
				if (!wasNearBottom) {
					pbgUnread += (countAfter - countBefore);
					updateUnreadBadge();
				}
				pbgPlayChatSound();
			}
		}, 250);
	};

	window.chat_send_message = function(e) {
		if ($('#stop_sending').val() !== '0') return;

		if (pbgIsTyping) {
			pbgIsTyping = false;
			clearTimeout(pbgTypingPingTimer);
			$.ajax({ type: 'POST', url: '../ajax/chat_actions.php', data: 'phpaction=1&token=' + $('#token').val() + '&chat_typing_stop=1' });
		}

		clearInterval(window.chat_interval);
		$('#stop_sending').val('1');
		var sendBtn = $('#send_butto,#send_button');
		var token = $('#token').val();
		var text = (e != null) ? e : encodeURIComponent($('#message_input').val());
		var prevValue = $('#message_input').val();
		sendBtn.addClass('disabled').attr('onclick', '');

		$.ajax({
			type: 'POST',
			url: '../ajax/chat_actions.php',
			data: 'phpaction=1&token=' + token + '&chat_send_message=1&message_text=' + text,
			dataType: 'json',
			success: function(res) {
				if (res && res.status == 1) {
					if (e == null) $('#message_input').val('');
					chat_get_messages(1);
					setTimeout(function() { chat_get_messages(1); }, 500);
				} else {
					if (e == null) $('#message_input').val(prevValue);
					if (res && res.status == 3) {
						if (typeof show_noty === 'function') {
							show_noty('Down', 'error', '<a>' + (res.data || 'Вы заблокированы в чате') + '</a>', '3000');
						} else if (typeof show_error === 'function') {
							show_error();
						}
						pbgShowBannedBox(res.reason, res.expires_at);
					} else if (typeof show_error === 'function') {
						show_error();
					}
				}
			},
			error: function() {
				if (e == null) $('#message_input').val(prevValue);
			},
			complete: function() {
				setTimeout(function() {
					sendBtn.removeClass('disabled').attr('onclick', 'chat_send_message();');
					$('#stop_sending').val('0');
				}, 300);
			}
		});
	};

	if (input) {
		input.addEventListener('input', function() {
			if (!pbgIsTyping) {
				pbgIsTyping = true;
				$.ajax({ type: 'POST', url: '../ajax/chat_actions.php', data: 'phpaction=1&token=' + $('#token').val() + '&chat_typing_ping=1' });
			}
			clearTimeout(pbgTypingPingTimer);
			pbgTypingPingTimer = setTimeout(function() {
				pbgIsTyping = false;
				$.ajax({ type: 'POST', url: '../ajax/chat_actions.php', data: 'phpaction=1&token=' + $('#token').val() + '&chat_typing_stop=1' });
			}, TYPING_IDLE_MS);
		});

		window.addEventListener('beforeunload', function() {
			if (pbgIsTyping) {
				navigator.sendBeacon && navigator.sendBeacon('../ajax/chat_actions.php', new URLSearchParams({
					phpaction: '1', token: $('#token').val(), chat_typing_stop: '1'
				}));
			}
		});
	}

	function pbgEscapeHtml(str) {
		var div = document.createElement('div');
		div.textContent = str;
		return div.innerHTML;
	}

	function pbgRenderTyping(list) {
		if (!typingBox) return;
		if (!list || !list.length) {
			typingBox.classList.remove('is-visible');
			typingBox.innerHTML = '';
			return;
		}
		var names = list.map(function(u) { return pbgEscapeHtml(u.login); });
		var text = '';
		if (names.length === 1) {
			text = names[0] + ' печатает';
		} else if (names.length === 2) {
			text = names[0] + ' и ' + names[1] + ' печатают';
		} else {
			text = names.length + ' человек печатают';
		}
		typingBox.innerHTML = '<span class="pbg_chat_typing__text">' + text + '</span>' +
			'<span class="pbg_chat_typing__dots"><i></i><i></i><i></i></span>';
		typingBox.classList.add('is-visible');
	}

	function pbgPollTyping() {
		$.ajax({
			type: 'POST',
			url: '../ajax/chat_data.php',
			data: 'phpaction=1&token=' + $('#token').val() + '&chat_typing_list=1',
			dataType: 'json',
			success: function(list) {
				pbgRenderTyping(list);
			}
		});
	}

	setInterval(pbgPollTyping, 2500);
})();
</script>