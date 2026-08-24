document.addEventListener('DOMContentLoaded', function() {
  var overlay    = document.getElementById('promo-modal-overlay');
  var modal      = document.getElementById('promo-modal');
  var closeBtn   = document.getElementById('promo-modal-close');
  var input      = document.getElementById('promo-code-input');
  var submitBtn  = document.getElementById('promo-submit-btn');
  var result     = document.getElementById('promo-result');
  var resultInner= document.getElementById('promo-result-inner');
  var inputWrap  = document.getElementById('promo-input-wrap');
  var promoHideTimer = null;

  if (!overlay) return;

  if (closeBtn) closeBtn.addEventListener('click', promoModalClose);
  overlay.addEventListener('click', function(e) {
    if (e.target === overlay) promoModalClose();
  });
  if (input) input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') window.promoActivate();
  });
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && overlay && overlay.classList.contains('is-open')) promoModalClose();
  });

  window.promoModalOpen = function() {
    if (!overlay) return;
    overlay.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    setTimeout(function() { if (input) input.focus(); }, 200);
  };

  function promoModalClose() {
    if (overlay) overlay.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  function promoPlaySound(ok) {
    var src = ok
      ? '/ajax/addons/toasts/sounds/sound-17.mp3'
      : '/ajax/addons/toasts/sounds/sound-7.mp3';
    try { var a = new Audio(src); a.volume = 0.6; a.play(); } catch(e) {}
  }

  function promoShowResult(ok, text) {
    if (!result || !resultInner || !inputWrap || !modal) return;
    if (promoHideTimer) { clearTimeout(promoHideTimer); promoHideTimer = null; }
    var stateClass = ok ? 'promo-state--ok' : 'promo-state--err';
    var icon = ok
      ? '<div class="promo-state-icon"><i class="bx bx-check-double"></i></div>'
      : '<div class="promo-state-icon"><i class="bx bx-error-circle"></i></div>';
    resultInner.innerHTML = '<div class="promo-state ' + stateClass + '">' + icon + '<div class="promo-state-text">' + text + '</div></div>';
    result.classList.add('is-visible');
    promoPlaySound(ok);
    if (ok) {
      modal.classList.add('is-success');
      setTimeout(function() { if (modal) modal.classList.remove('is-success'); }, 1000);
    } else {
      inputWrap.classList.add('is-shaking');
      setTimeout(function() { if (inputWrap) inputWrap.classList.remove('is-shaking'); }, 500);
    }
    promoHideTimer = setTimeout(function() {
      if (result) result.classList.remove('is-visible');
      setTimeout(function() { if (resultInner) resultInner.innerHTML = ''; }, 320);
      promoHideTimer = null;
    }, ok ? 12000 : 8000);
  }

  window.promoActivate = function() {
    var code = input ? input.value.trim().toUpperCase() : '';
    if (!code) {
      if (inputWrap) {
        inputWrap.classList.add('is-shaking');
        setTimeout(function() { if (inputWrap) inputWrap.classList.remove('is-shaking'); }, 500);
      }
      return;
    }
    if (promoHideTimer) { clearTimeout(promoHideTimer); promoHideTimer = null; }
    if (submitBtn) submitBtn.disabled = true;
    $.ajax({
      type: 'POST',
      url: '../ajax/actions_a.php',
      data: { phpaction: 1, token: $('#token').val() || '', promo_activate: 1, code: code },
      dataType: 'json',
      success: function(r) {
        if (submitBtn) submitBtn.disabled = false;
        if (r && r.status == 1) {
          promoShowResult(true, r.message || 'Промокод успешно активирован!');
          if (input) input.value = '';
        } else {
          promoShowResult(false, r.message || 'Промокод не найден или уже использован');
        }
      },
      error: function() {
        if (submitBtn) submitBtn.disabled = false;
        promoShowResult(false, 'Ошибка соединения. Попробуйте ещё раз.');
      }
    });
  };
});
