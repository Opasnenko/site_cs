(function ($) {
  var overlay, drawer, drawerBody, drawerIcon, drawerTitle, drawerSubtitle, drawerContent;
  var currentUserId = null;

  function token() { return $('#token').val() || ''; }

  function pushMsg(message, alertType) {
    if (typeof push === 'function') { push(message, alertType || 'info'); }
    else if (message) { alert(message); }
  }

  function post(data, done) {
    data.token = token();
    $.ajax({
      type: 'POST',
      url: window.location.origin + '/ajax/users_actions.php',
      dataType: 'json',
      data: data,
      success: done,
      error: function () { pushMsg('Ошибка запроса', 'error'); }
    });
  }

  function escapeHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
    });
  }

  $(function () {
    overlay = document.getElementById('dc-drawer-overlay');
    drawer = document.getElementById('dc-drawer');
    drawerBody = document.getElementById('dc-drawer-body');
    drawerIcon = document.getElementById('dc-drawer-icon');
    drawerTitle = document.getElementById('dc-drawer-title');
    drawerSubtitle = document.getElementById('dc-drawer-subtitle');
    drawerContent = document.getElementById('user-drawer-content');

    function openDrawer() {
      if (!drawer || !overlay) { return; }
      document.body.style.overflow = 'hidden';
      overlay.classList.add('active');
      drawer.classList.add('active');
    }

    function closeDrawer() {
      if (!drawer || !overlay) { return; }
      overlay.classList.remove('active');
      drawer.classList.remove('active');
      document.body.style.overflow = '';
      currentUserId = null;
      if (window.history && window.history.replaceState) {
        var url = new URL(window.location.href);
        url.searchParams.delete('open_user');
        window.history.replaceState({}, '', url);
      }
    }

    var closeBtn = document.getElementById('dc-drawer-close');
    if (closeBtn) { closeBtn.addEventListener('click', closeDrawer); }
    if (overlay) { overlay.addEventListener('click', closeDrawer); }
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && drawer && drawer.classList.contains('active')) { closeDrawer(); }
    });

    window.openUserDrawer = function (id) {
      id = parseInt(id, 10);
      if (!id) { return; }
      currentUserId = id;

      if (drawerContent) {
        drawerContent.innerHTML = '<div class="dc-tile__hint" style="padding:24px;text-align:center;"><span class="glyphicon glyphicon-refresh lib-loader active" style="display:inline-block;"></span> Загрузка данных пользователя...</div>';
      }
      drawerTitle.textContent = 'Загрузка...';
      drawerSubtitle.textContent = '';
      openDrawer();

      post({ users_drawer_bootstrap: 1, id: id }, function (result) {
        if (!result || result.alert !== 'success') {
          if (drawerContent) {
            drawerContent.innerHTML = '<div class="dc-notice dc-notice--danger">' + escapeHtml((result && result.message) || 'Не удалось загрузить пользователя') + '</div>';
          }
          return;
        }

        drawerTitle.textContent = result.login || ('Пользователь #' + id);
        drawerSubtitle.textContent = (result.group || '') + (result.regdate ? ' · рег. ' + result.regdate : '');
        if (drawerIcon) {
          drawerIcon.innerHTML = '<img src="../' + escapeHtml(result.avatar) + '?v=' + Date.now() + '" style="width:100%;height:100%;object-fit:cover;border-radius:11px;">';
        }
        if (drawerContent) {
          drawerContent.innerHTML = result.html;
        }

        balanceHistoryLoaded = false;
        inventoryLoaded = false;
      });
    };

    window.closeUserDrawer = closeDrawer;

    $(document).on('click', '.dc-user-card__edit', function () {
      window.openUserDrawer($(this).data('id'));
    });

    var dangerMenu = document.getElementById('dc-danger-menu');
    var dangerTrigger = document.getElementById('dc-danger-trigger');
    var dangerSubmit = document.getElementById('dc-danger-submit');

    if (dangerTrigger) {
      dangerTrigger.addEventListener('click', function (e) {
        e.stopPropagation();
        if (dangerMenu) { dangerMenu.classList.toggle('open'); }
      });
    }
    document.addEventListener('click', function (e) {
      if (dangerMenu && dangerMenu.classList.contains('open') && !dangerMenu.contains(e.target)) {
        dangerMenu.classList.remove('open');
      }
    });
    if (dangerSubmit) {
      dangerSubmit.addEventListener('click', function () {
        if (!currentUserId) { return; }
        if (dangerMenu) { dangerMenu.classList.remove('open'); }
        dell_user(currentUserId, 1);
      });
    }

    var params = new URLSearchParams(window.location.search);
    var openUserId = params.get('open_user');
    if (openUserId) {
      window.openUserDrawer(openUserId);
    }
  });

  var balanceHistoryLoaded = false;
  var inventoryLoaded = false;

  function loadBalanceHistory(id) {
    var el = document.getElementById('ud-balance-history');
    if (!el) { return; }
    post({ users_balance_history: 1, id: id }, function (result) {
      if (result && result.alert === 'success') { el.innerHTML = result.html; }
    });
  }

  function loadInventory(id) {
    var el = document.getElementById('ud-inventory-list');
    if (!el) { return; }
    post({ users_inventory_list: 1, id: id, offset: 0, limit: 20 }, function (result) {
      if (result && result.alert === 'success') { el.innerHTML = result.html; }
    });
  }

  window.loadBalanceHistoryOnce = function (id) {
    if (balanceHistoryLoaded) { return; }
    balanceHistoryLoaded = true;
    loadBalanceHistory(id);
  };

  window.loadInventoryOnce = function (id) {
    if (inventoryLoaded) { return; }
    inventoryLoaded = true;
    loadInventory(id);
  };

  var PANEL_LABELS = {
    'ud-balance-history': { icon: 'glyphicon-list-alt', show: 'Показать транзакции' },
    'ud-inventory-list': { icon: 'glyphicon-shopping-cart', show: 'Показать инвентарь' }
  };

  window.pb_toggle_panel = function (panelId, id, loadFn) {
    var panel = document.getElementById(panelId);
    var toggleBtn = document.getElementById(panelId + '-toggle');
    var meta = PANEL_LABELS[panelId] || { icon: 'glyphicon-list-alt', show: 'Показать' };
    if (!panel) { return; }
    var isHidden = panel.style.display === 'none' || !panel.style.display;
    if (isHidden) {
      panel.style.display = 'flex';
      if (loadFn) { loadFn(id); }
    } else {
      panel.style.display = 'none';
    }
    if (toggleBtn) {
      toggleBtn.innerHTML = '<span class="glyphicon ' + meta.icon + '"></span> ' + (isHidden ? 'Скрыть' : meta.show);
    }
  };

  window.admin_change_birth_date = function (id) {
    var dateVal = $('#user_birth_date').val();
    if (!dateVal) { return; }
    var parts = dateVal.split('-');
    var t = token();
    NProgress.start();
    $.ajax({
      type: 'POST',
      url: '../ajax/actions_z.php',
      data: 'phpaction=1&token=' + t + '&admin_change_birth=1&birth_year=' + parts[0] + '&birth_month=' + parts[1] + '&birth_day=' + parts[2] + '&id=' + id,
      dataType: 'json',
      success: function (result) {
        NProgress.done();
        if (result.status == 1) { setTimeout(show_ok, 300); }
        else { setTimeout(show_error, 300); }
      }
    });
  };

  window.pb_set_gag = function (id, value) {
    change_value2('users', 'gag', value, id);
  };

  window.pb_set_verification = function (id, value) {
    value = parseInt(value, 10);
    var resultEl = document.getElementById('verification_result');
    post({ users_verification_set: 1, id: id, value: value }, function (result) {
      if (!result) { return; }
      if (resultEl) {
        resultEl.innerHTML = '<span style="color:' + (result.alert === 'success' ? '#059669' : '#dc2626') + ';">' + escapeHtml(result.message || '') + '</span>';
      }
      pushMsg(result.message, result.alert);
      var badge = document.getElementById('ud-verification-badge');
      if (badge && result.alert === 'success') {
        badge.textContent = result.label;
        badge.className = 'dc-badge ' + (value === 1 ? 'dc-badge--success' : (value === 2 ? 'dc-badge--warning' : 'dc-badge--muted'));
      }
    });
  };

  window.pb_set_allowed_groups = function (id) {
    var select = document.getElementById('user_allowed_groups');
    var resultEl = document.getElementById('user_allowed_groups_result');
    if (!select) { return; }
    var values = Array.prototype.slice.call(select.selectedOptions).map(function (o) { return o.value; });
    post({ users_allowed_groups_set: 1, id: id, group_ids: values }, function (result) {
      if (!result) { return; }
      if (resultEl) {
        resultEl.innerHTML = '<span style="color:' + (result.alert === 'success' ? '#059669' : '#dc2626') + ';">' + escapeHtml(result.message || '') + '</span>';
      }
      pushMsg(result.message, result.alert);
    });
  };

  window.pb_gametime_save = function (id) {
    var value = $('#ud-gametime-input').val();
    var resultEl = document.getElementById('ud-gametime-result');
    post({ users_gametime_set: 1, id: id, value: value }, function (result) {
      if (!result) { return; }
      if (resultEl) {
        resultEl.innerHTML = '<span style="color:' + (result.alert === 'success' ? '#059669' : '#dc2626') + ';">' + escapeHtml(result.message || '') + '</span>';
      }
      pushMsg(result.message, result.alert);
      var tile = document.getElementById('ud-gametime-value');
      if (tile && result.alert === 'success') { tile.textContent = result.hours + ' ч.'; }
    });
  };

  window.pb_regdate_save = function (id) {
    var value = $('#ud-regdate-input').val();
    var resultEl = document.getElementById('ud-regdate-result');
    if (!value) {
      if (resultEl) { resultEl.innerHTML = '<span style="color:#dc2626;">Укажите дату</span>'; }
      return;
    }
    post({ users_regdate_set: 1, id: id, value: value }, function (result) {
      if (!result) { return; }
      if (resultEl) {
        resultEl.innerHTML = '<span style="color:' + (result.alert === 'success' ? '#059669' : '#dc2626') + ';">' + escapeHtml(result.message || '') + '</span>';
      }
      pushMsg(result.message, result.alert);
      if (result.alert === 'success' && drawerSubtitle && result.regdate) {
        var group = drawerSubtitle.textContent.split(' · ')[0] || '';
        drawerSubtitle.textContent = group + ' · рег. ' + result.regdate;
      }
    });
  };

  window.pb_balance_adjust = function (id, sign) {
    var amount = parseFloat($('#ud-balance-amount').val());
    var resultEl = document.getElementById('ud-balance-result');
    if (!amount || amount <= 0) {
      if (resultEl) { resultEl.innerHTML = '<span style="color:#dc2626;">Укажите сумму больше нуля</span>'; }
      return;
    }

    var action = sign > 0 ? 'give_money' : 'pick_up_money';
    var t = token();
    var data = { phpaction: 1, token: t, id: id, money: amount };
    data[action] = 1;

    $.ajax({
      type: 'POST',
      url: '../ajax/actions_z.php',
      data: data,
      dataType: 'json',
      success: function (result) {
        if (!result || result.status != 1) {
          if (resultEl) { resultEl.innerHTML = '<span style="color:#dc2626;">Не удалось выполнить операцию</span>'; }
          return;
        }
        if (resultEl) { resultEl.innerHTML = '<span style="color:#059669;">Готово, новый баланс: ' + result.res + '</span>'; }
        var big = document.getElementById('ud-balance-big');
        var tile = document.getElementById('ud-balance-value');
        if (big) { big.textContent = result.res; }
        if (tile) { tile.textContent = result.res; }
        loadBalanceHistory(id);
      },
      error: function () {
        if (resultEl) { resultEl.innerHTML = '<span style="color:#dc2626;">Ошибка запроса</span>'; }
      }
    });
  };

  window.pb_inventory_take = function (id, purchaseId) {
    if (!confirm('Забрать этот предмет у пользователя? Действие необратимо.')) { return; }
    post({ users_inventory_take: 1, id: id, purchase_id: purchaseId }, function (result) {
      pushMsg(result && result.message, result && result.alert);
      if (result && result.alert === 'success') {
        loadInventory(id);
      }
    });
  };

  $(document).on('change', '#user_avatar', function () {
    var label = document.getElementById('user_avatar_filename');
    if (!label) { return; }
    var file = this.files && this.files[0];
    label.textContent = file ? file.name : 'Выберите изображение или перетащите файл';
  });

  $(document).on('submit', '#edit_user_avatar_form', function (event) {
    event.preventDefault();
    var $form = $(this);
    var $submitBtn = $form.find('button[type="submit"]');
    if ($submitBtn.prop('disabled')) { return false; }

    var data = new FormData(this);
    $submitBtn.prop('disabled', true);

    $.ajax({
      type: 'POST',
      url: '../ajax/actions_z.php',
      data: data,
      contentType: false,
      processData: false,
      beforeSend: function () { $('#edit_user_loader').show(); }
    }).done(function (html) {
      $('#edit_user_avatar_result').empty().append(html);
      $form[0].reset();
      var label = document.getElementById('user_avatar_filename');
      if (label) { label.textContent = 'Выберите изображение или перетащите файл'; }
    }).fail(function () {
      $('#edit_user_avatar_result').html('<p class="text-danger">Ошибка загрузки, попробуйте ещё раз</p>');
    }).always(function () {
      $submitBtn.prop('disabled', false);
      $('#edit_user_loader').hide();
    });
    return false;
  });
})(jQuery);
