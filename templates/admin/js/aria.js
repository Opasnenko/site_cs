(function(){
'use strict';
if(window.PBGameARIA3Loaded) return;
window.PBGameARIA3Loaded = true;

var state={siteHost:'/',token:'',avatarUrl:'',currentId:0,conversations:[],service:{},busy:false,booted:false};
function q(s,r){return(r||document).querySelector(s)}
function qa(s,r){return Array.prototype.slice.call((r||document).querySelectorAll(s))}
function esc(v){return String(v==null?'':v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;')}
function notify(msg,ok){if(window.PBToast){try{window.PBToast.show(ok?'success':'error',msg);return}catch(e){}}if(typeof window.push==='function'){try{window.push(msg,ok?'success':'error');return}catch(e){}}if(!ok)console.error(msg)}
function fmtDate(v){if(!v)return'';var d=new Date(String(v).replace(' ','T'));if(isNaN(d.getTime()))return String(v);var now=new Date();return d.toDateString()===now.toDateString()?d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}):d.toLocaleDateString([],{day:'2-digit',month:'2-digit'})}
function inlineMd(t){var s=esc(t);s=s.replace(/`([^`]+)`/g,'<code>$1</code>');s=s.replace(/\*\*([^*]+)\*\*/g,'<strong>$1</strong>');return s}
function markdown(text){
 text=String(text==null?'':text).replace(/\r\n/g,'\n');var chunks=text.split(/```/),html='';
 chunks.forEach(function(part,i){
  if(i%2===1){var nl=part.indexOf('\n'),lang=nl>=0?part.slice(0,nl).trim():'',code=nl>=0?part.slice(nl+1):part;html+='<div class="aria-code"><div class="aria-code-head"><span>'+esc(lang||'code')+'</span><button type="button" data-aria-copy>Копировать</button></div><pre><code>'+esc(code.replace(/\n$/,''))+'</code></pre></div>';return}
  var list='',buf=[];function flush(){if(buf.length){html+='<p>'+inlineMd(buf.join('\n')).replace(/\n/g,'<br>')+'</p>';buf=[]}}function close(){if(list){html+='</'+list+'>';list=''}}
  part.split('\n').forEach(function(line){if(/^\s*$/.test(line)){flush();close();return}var h=line.match(/^#{1,3}\s+(.+)$/);if(h){flush();close();html+='<div class="aria-md-heading">'+inlineMd(h[1])+'</div>';return}var b=line.match(/^\s*[-*]\s+(.+)$/),n=line.match(/^\s*\d+[.)]\s+(.+)$/);if(b||n){flush();var wanted=b?'ul':'ol';if(list!==wanted){close();list=wanted;html+='<'+list+'>'}html+='<li>'+inlineMd((b||n)[1])+'</li>';return}close();buf.push(line)});flush();close()
 });return html||'<p></p>'
}
function api(action,extra){var body=new URLSearchParams();body.set('action',action);body.set('token',state.token);Object.keys(extra||{}).forEach(function(k){body.set(k,extra[k]==null?'':String(extra[k]))});return fetch(state.siteHost+'ajax/aria.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin',cache:'no-store',body:body.toString()}).then(function(r){return r.json().catch(function(){throw new Error('Сервер ARIA вернул некорректный ответ.')}).then(function(d){if(!r.ok||!d||Number(d.status)!==1){var err=new Error(d&&d.message?d.message:'Ошибка ARIA');err.code=d&&d.error_code?String(d.error_code):'';err.final_message=d&&d.final_message?String(d.final_message):'';throw err}return d})})}
function ariaNormalizePath(value){
 try{
  var u=new URL(String(value||''),window.location.origin);
  var p=u.pathname||'/';
  p=p.replace(/\/+$/,'');
  return p||'/';
 }catch(e){
  return String(value||'').split('?')[0].replace(/\/+$/,'')||'/';
 }
}

function pageContext(){
 var currentPath=ariaNormalizePath(window.location.pathname);
 var active='';

 /*
  * Ищем пункт меню именно по текущему URL.
  * Это надёжнее generic .active, который может принадлежать
  * вкладке, кнопке или другому элементу страницы.
  */
 qa('a[href]').some(function(el){
  var href=el.getAttribute('href')||'';

  if(ariaNormalizePath(href)!==currentPath){
   return false;
  }

  var text=String(el.textContent||'').replace(/\s+/g,' ').trim();

  if(!text){
   return false;
  }

  active=text;
  return true;
 });

 var headings=[];

 qa('h1,h2,h3,.page-title,.content-title,.admin-title').forEach(function(el){
  var t=String(el.textContent||'').replace(/\s+/g,' ').trim();

  if(
   t &&
   headings.indexOf(t)===-1 &&
   headings.length<8
  ){
   headings.push(t);
  }
 });

 var slug=currentPath.split('/').filter(Boolean).pop()||'';

 return {
  path:currentPath,
  route_slug:slug,
  title:String(document.title||'').trim(),
  active_menu:active,
  headings:headings
 };
}

function pageHelpText(){
 var c=pageContext();
 var name=c.active_menu||c.headings[0]||'текущей странице';
 return 'Я вижу, что вы сейчас в разделе «'+name+'». Могу проверить настройки этой страницы или подсказать по её работе.';
}

function trialTime(seconds){
 seconds=Math.max(0,Number(seconds)||0);
 var days=Math.floor(seconds/86400);
 var hours=Math.floor((seconds%86400)/3600);
 var mins=Math.floor((seconds%3600)/60);

 if(days>0)return days+' д '+hours+' ч';
 if(hours>0)return hours+' ч '+mins+' мин';
 return Math.max(1,mins)+' мин';
}

function servicePlanText(){
 var tier=state.service.tier||'mini';
 var trial=state.service.trial||null;

 if(tier==='mini'&&trial){
  if(trial.state==='not_started'){
   return 'Пробный период Mini · 72 часа';
  }

  if(trial.active){
   return 'Пробный период Mini · осталось '+trialTime(trial.remaining_seconds);
  }

  if(trial.state==='expired'){
   return 'Пробный период Mini завершён';
  }
 }

 if(tier==='mini')return 'ARIA Mini · лицензия активна';
 if(tier==='hard')return 'ARIA Hard · лицензия активна';
 if(tier==='premium')return 'ARIA Premium · лицензия активна';

 return '';
}

function renderPageHint(){
 var c=pageContext();
 var name=c.active_menu||(c.headings&&c.headings[0])||'';

 qa('[data-aria-greeting]').forEach(function(g){
  var hint=q('.aria-page-context-hint',g);

  if(!name){
   if(hint)hint.remove();
   return;
  }

  if(!hint){
   hint=document.createElement('div');
   hint.className='aria-page-context-hint';
   hint.innerHTML='<span class="aria-page-context-text"></span><span class="aria-page-context-caret" aria-hidden="true">|</span>';
   g.appendChild(hint);
  }

  var text=q('.aria-page-context-text',hint);
  if(!text)return;
  var sentence='Вижу, вы сейчас в разделе «'+name+'» — могу помочь разобраться с ним.';
  if(hint.dataset.ariaContextName===name&&text.textContent===sentence)return;
  hint.dataset.ariaContextName=name;
  text.textContent='';
  var i=0;
  clearTimeout(hint._ariaTypeTimer);
  function typeNext(){
   if(hint.dataset.ariaContextName!==name)return;
   text.textContent=sentence.slice(0,i);
   if(i<sentence.length){
    i++;
    hint._ariaTypeTimer=setTimeout(typeNext,i<12?24:14);
   }else{
    hint.classList.add('is-typed');
   }
  }
  hint.classList.remove('is-typed');
  hint._ariaTypeTimer=setTimeout(typeNext,180);
 });
}

function tierLabel(){return state.service.tier_label||({mini:'ARIA Mini',hard:'ARIA Hard',premium:'ARIA Premium'}[state.service.tier]||'ARIA Mini')}
function tierClass(){var t=state.service.tier||'mini';return t==='premium'?'premium':(t==='hard'?'hard':'mini')}
function formatAriaDate(value){
 if(!value)return '';
 var d=new Date(String(value).replace(' ','T'));
 if(isNaN(d.getTime()))return String(value);
 return d.toLocaleDateString('ru-RU',{day:'2-digit',month:'2-digit',year:'numeric'});
}
function licenseSummaryText(){
 var service=state.service||{};
 var lic=service.license||null;
 var trial=service.trial||null;
 if(lic){
  var bits=[(lic.tier_label||tierLabel())+' активна'];
  if(lic.expires_at)bits.push('до '+formatAriaDate(lic.expires_at));
  if(Number(lic.remaining_days)>=0)bits.push('осталось '+Number(lic.remaining_days)+' д.');
  return bits.join(' · ')+'. Лицензия работает только пока домен и текущая установка остаются привязанными на pbgame.top.';
 }
 if(trial&&trial.active)return 'Активен 72-часовой Trial ARIA Mini · осталось '+trialTime(trial.remaining_seconds)+'. После Trial потребуется лицензия Mini, Hard или Premium.';
 if(trial&&trial.state==='expired')return 'Trial ARIA Mini завершён. Для продолжения работы активируйте лицензию Mini, Hard или Premium.';
 return 'После привязки домена доступен 72-часовой Trial ARIA Mini. После Trial для работы ARIA требуется лицензия Mini, Hard или Premium.';
}
function serviceLocked(){return state.service.service_online===false||state.service.can_chat===false}
function ariaImportant(el,name,value){
 try{el.style.setProperty(name,value,'important')}catch(e){el.style[name]=value}
}
function styleAriaNotices(){
 var code=String((state.service||{}).error_code||'');
 qa('[data-aria-service-banner]').forEach(function(el){
  el.classList.toggle('aria-service-banner--revoked',code==='license_revoked');
  el.classList.toggle('aria-service-banner--blocked',code!=='license_revoked');
  ariaImportant(el,'opacity','1');
  ariaImportant(el,'font-weight','700');
  ariaImportant(el,'line-height','1.5');
  ariaImportant(el,'padding','11px 13px');
  ariaImportant(el,'border-radius','9px');
  ariaImportant(el,'box-sizing','border-box');
  ariaImportant(el,'text-shadow','none');
  if(code==='license_revoked'){
   ariaImportant(el,'background','#7f1d1d');
   ariaImportant(el,'border','1px solid #b91c1c');
   ariaImportant(el,'color','#ffffff');
   ariaImportant(el,'box-shadow','0 2px 10px rgba(127,29,29,.18)');
  }else{
   ariaImportant(el,'background','#7c4a03');
   ariaImportant(el,'border','1px solid #b7791f');
   ariaImportant(el,'color','#ffffff');
   ariaImportant(el,'box-shadow','0 2px 10px rgba(124,74,3,.16)');
  }
 });
 qa('[data-aria-error]').forEach(function(el){
  el.classList.add('aria-error--danger');
  ariaImportant(el,'opacity','1');
  ariaImportant(el,'background','#7f1d1d');
  ariaImportant(el,'border','1px solid #b91c1c');
  ariaImportant(el,'color','#ffffff');
  ariaImportant(el,'font-weight','700');
  ariaImportant(el,'line-height','1.5');
  ariaImportant(el,'padding','11px 13px');
  ariaImportant(el,'border-radius','9px');
  ariaImportant(el,'box-sizing','border-box');
  ariaImportant(el,'text-shadow','none');
  ariaImportant(el,'box-shadow','0 2px 10px rgba(127,29,29,.18)');
 });
}
function renderService(){
 qa('[data-aria-tier]').forEach(function(el){el.textContent=tierLabel();el.className='aria-tier aria-tier--'+tierClass()});
 qa('[data-aria-license-tier]').forEach(function(el){el.textContent=tierLabel()});
 qa('[data-aria-license-summary]').forEach(function(el){el.textContent=licenseSummaryText()});qa('[data-aria-upgrade-link]').forEach(function(el){var url=String((state.service||{}).upgrade_url||'');el.hidden=!url;if(url)el.href=url});
 qa('[data-aria-plan-text]').forEach(function(el){
  var text=servicePlanText();
  el.textContent=text;
  el.style.display=text?'':'none';
 });
 qa('[data-aria-plan-tab]').forEach(function(el){el.classList.toggle('is-active',el.getAttribute('data-aria-plan-tab')===tierClass())});
 var locked=serviceLocked();
 qa('[data-aria-fab-status]').forEach(function(el){el.classList.toggle('is-offline',locked)});
 qa('[data-aria-service-banner]').forEach(function(el){el.hidden=!locked;var msg=state.service.message||'ARIA недоступна до активации.';if(state.service.error_code==='domain_not_bound')msg='ARIA остановлена. Домен установки отвязан или не подтверждён на pbgame.top. Восстановите привязку домена, чтобы продолжить работу.';if(state.service.error_code==='license_revoked')msg=state.service.message||'Лицензия ARIA отозвана. Помощница остановлена.';el.textContent=locked?msg:''});
 qa('[data-aria-send]').forEach(function(b){b.disabled=locked||state.busy});
 qa('[data-aria-input]').forEach(function(i){i.disabled=locked||state.busy});
 qa('[data-aria-new]').forEach(function(b){b.disabled=locked;b.setAttribute('aria-disabled',locked?'true':'false');b.classList.toggle('is-disabled',locked)});
 qa('[data-aria-suggest]').forEach(function(b){b.disabled=locked;b.setAttribute('aria-disabled',locked?'true':'false');b.classList.toggle('is-disabled',locked)});
 qa('.aria-fullpage-root,[data-aria-widget-panel]').forEach(function(el){el.classList.toggle('aria-is-locked',locked)});
 styleAriaNotices();
}
function setTitle(title){qa('[data-aria-title]').forEach(function(el){el.textContent=title||'Новый диалог'})}
function setBusy(on){state.busy=!!on;var locked=serviceLocked();qa('[data-aria-send]').forEach(function(b){b.disabled=locked||!!on});qa('[data-aria-input]').forEach(function(i){i.disabled=locked||!!on})}
function setError(msg){qa('[data-aria-error]').forEach(function(el){el.hidden=!msg;el.textContent=msg||''});styleAriaNotices()}
function scrollBottom(){qa('[data-aria-messages]').forEach(function(el){el.scrollTop=el.scrollHeight})}
function makeAvatar(){var av=document.createElement('div');av.className='aria-message-avatar';var img=document.createElement('img');img.src=state.avatarUrl;img.alt='ARIA';av.appendChild(img);return av}
function makeMessage(role,content,meta){
 var wrap=document.createElement('div');wrap.className='aria-message aria-message--'+(role==='user'?'user':'assistant');if(role!=='user')wrap.appendChild(makeAvatar());
 var body=document.createElement('div');body.className='aria-message-body';if(role!=='user'){var n=document.createElement('div');n.className='aria-message-name';n.textContent='ARIA';body.appendChild(n)}
 var c=document.createElement('div');c.className='aria-message-content';c.innerHTML=markdown(content);body.appendChild(c);
 if(role!=='user'&&meta){var bits=[];if(meta.model&&/^ARIA\s/i.test(String(meta.model)))bits.push(meta.model);else if(meta.tier_label)bits.push(meta.tier_label);if(bits.length){var m=document.createElement('div');m.className='aria-message-meta';m.textContent=bits.join(' · ');body.appendChild(m)}}wrap.appendChild(body);return wrap
}
function renderMessages(list){qa('[data-aria-messages]').forEach(function(box){qa('.aria-message,.aria-typing',box).forEach(function(el){el.remove()});var empty=q('[data-aria-empty]',box);if(empty)empty.style.display=list&&list.length?'none':'flex';(list||[]).forEach(function(m){box.appendChild(makeMessage(m.role==='assistant'?'assistant':'user',m.content||'',m))})});scrollBottom()}
function appendMessage(role,content,meta){qa('[data-aria-messages]').forEach(function(box){var e=q('[data-aria-empty]',box);if(e)e.style.display='none';box.appendChild(makeMessage(role,content,meta))});scrollBottom()}
function typing(on){qa('[data-aria-typing]').forEach(function(el){el.remove()});if(!on)return;qa('[data-aria-messages]').forEach(function(box){var e=q('[data-aria-empty]',box);if(e)e.style.display='none';var w=document.createElement('div');w.className='aria-message aria-message--assistant aria-typing';w.setAttribute('data-aria-typing','1');w.appendChild(makeAvatar());var body=document.createElement('div');body.className='aria-message-body';body.innerHTML='<div class="aria-message-name">ARIA</div><div class="aria-message-content"><i></i><i></i><i></i></div>';w.appendChild(body);box.appendChild(w)});scrollBottom()}
function renderConversations(){qa('[data-aria-conversations]').forEach(function(box){box.innerHTML='';if(!state.conversations.length){box.innerHTML='<div class="aria-list-placeholder">Диалогов пока нет</div>';return}state.conversations.forEach(function(c){var row=document.createElement('button');row.type='button';row.className='aria-conversation'+(Number(c.id)===Number(state.currentId)?' is-active':'');row.innerHTML='<span>'+esc(c.title||'Новый диалог')+'</span><small>'+esc(fmtDate(c.last_message_at||c.updated_at))+'</small>';row.addEventListener('click',function(){loadConversation(Number(c.id))});box.appendChild(row)})});qa('[data-aria-delete]').forEach(function(b){b.disabled=!state.currentId})}
function bootstrap(){if(state.booted)return Promise.resolve();return api('bootstrap').then(function(r){state.booted=true;state.conversations=(r.data&&r.data.conversations)||[];state.service=(r.data&&r.data.service)||{};renderConversations();renderService();var notice=(state.service&&state.service.restoration_notice)||(state.service&&state.service.revocation_notice);if(notice&&Number(notice.conversation_id)>0){state.currentId=Number(notice.conversation_id);return loadConversation(state.currentId)}}).catch(function(e){state.service={tier:'mini',tier_label:'ARIA Mini',service_online:false,can_chat:false,domain_active:false,error_code:e.code||'',message:e.message};renderService()})}
function refreshService(){if(!state.booted||state.busy)return;api('service_status').then(function(r){var d=(r&&r.data)||{};var restored=d.restoration_notice||null;state.service=d;renderService();if(restored&&Number(restored.conversation_id)>0){state.currentId=Number(restored.conversation_id);loadConversation(state.currentId);api('bootstrap').then(function(b){state.conversations=(b.data&&b.data.conversations)||state.conversations;renderConversations()}).catch(function(){});notify('Лицензия ARIA восстановлена',true);return}var rn=d.revocation_notice;if(rn&&Number(rn.conversation_id)>0&&Number(state.currentId)!==Number(rn.conversation_id)){state.currentId=Number(rn.conversation_id);loadConversation(state.currentId);api('bootstrap').then(function(b){state.conversations=(b.data&&b.data.conversations)||state.conversations;renderConversations()}).catch(function(){})}}).catch(function(e){if(e.code==='license_revoked'){state.service.can_chat=false;state.service.error_code='license_revoked';state.service.message=e.message;renderService()}})}
function loadConversation(id){if(!id){state.currentId=0;setTitle('Новый диалог');renderMessages([]);renderConversations();return}api('load_conversation',{conversation_id:id}).then(function(r){state.currentId=id;setTitle(r.data.conversation.title);renderMessages(r.data.messages||[]);renderConversations()}).catch(function(e){notify(e.message,false)})}
function newConversation(){if(serviceLocked())return;api('new_conversation').then(function(r){state.currentId=Number(r.data.conversation_id)||0;state.conversations.unshift({id:state.currentId,title:r.data.title||'Новый диалог',last_message_at:new Date().toISOString()});setTitle('Новый диалог');renderMessages([]);renderConversations();focusInput()}).catch(function(e){notify(e.message,false)})}
function deleteConversation(){if(!state.currentId)return;if(!window.confirm('Удалить этот диалог ARIA?'))return;var id=state.currentId;api('delete_conversation',{conversation_id:id}).then(function(){state.conversations=state.conversations.filter(function(c){return Number(c.id)!==Number(id)});state.currentId=0;setTitle('Новый диалог');renderMessages([]);renderConversations()}).catch(function(e){notify(e.message,false)})}
function focusInput(){var panel=q('[data-aria-widget-panel]');var input=panel&&!panel.hidden?q('[data-aria-input]',panel):q('.aria-fullpage-root [data-aria-input]');if(input&&!input.disabled)setTimeout(function(){input.focus()},50)}
function sendFrom(input){if(state.busy||serviceLocked())return;var message=String(input.value||'').trim();if(!message)return;setError('');input.value='';autoGrow(input);appendMessage('user',message);typing(true);setBusy(true);api('send_message',{
 conversation_id:state.currentId,
 message:message,
 page_context:JSON.stringify(pageContext())
}).then(function(r){typing(false);var d=r.data||{};state.currentId=Number(d.conversation_id)||state.currentId;state.service.tier=d.tier||state.service.tier;state.service.tier_label=d.tier_label||state.service.tier_label;if(d.trial)state.service.trial=d.trial;if(Number(d.remaining)>=0)state.service.remaining=Number(d.remaining);appendMessage('assistant',d.answer||'',{tier_label:d.tier_label,usage:d.usage});var found=false;state.conversations.forEach(function(c){if(Number(c.id)===Number(state.currentId)){c.title=d.title||c.title;c.last_message_at=new Date().toISOString();found=true}});if(!found)state.conversations.unshift({id:state.currentId,title:d.title||message.slice(0,52),last_message_at:new Date().toISOString()});setTitle(d.title||'Диалог');renderConversations();renderService()}).catch(function(e){typing(false);if(e.code==='domain_not_bound'){state.service.service_online=false;state.service.can_chat=false;state.service.domain_active=false;state.service.error_code='domain_not_bound';state.service.message=e.message;renderService();setError('');appendMessage('assistant','ARIA остановлена: домен этой установки отвязан или не подтверждён на pbgame.top. Восстановите привязку домена — после этого действующая лицензия снова будет проверена автоматически.',{tier_label:tierLabel()})}else if(e.code==='license_revoked'){var fm=String(e.final_message||e.message||'Лицензия ARIA отозвана. Помощница остановлена.');state.service.service_online=true;state.service.can_chat=false;state.service.error_code='license_revoked';state.service.message=fm;renderService();setError('');appendMessage('assistant',fm,{tier_label:tierLabel()})}else{setError(e.message);appendMessage('assistant','Не удалось получить ответ: '+e.message,{tier_label:tierLabel()})}}).finally(function(){setBusy(false);focusInput()})}
function autoGrow(el){el.style.height='auto';el.style.height=Math.min(el.scrollHeight,150)+'px'}
function hideGreeting(){qa('[data-aria-greeting]').forEach(function(g){g.hidden=true})}
function maybeGreeting(){renderPageHint();setTimeout(function(){var g=q('[data-aria-greeting]');var p=q('[data-aria-widget-panel]');if(g&&(!p||p.hidden))g.hidden=false},850)}
function openWidget(){renderPageHint();hideGreeting();var p=q('[data-aria-widget-panel]');if(p){p.hidden=false;document.body.classList.add('aria-widget-open');bootstrap().then(focusInput)}}
function closeWidget(){var p=q('[data-aria-widget-panel]');if(p){p.hidden=true;document.body.classList.remove('aria-widget-open')}}
function expandFull(){
 try{sessionStorage.setItem('pb_aria_return_url',window.location.href)}catch(e){}
 api('launch_full').then(function(r){if(r.data&&r.data.url)window.location.href=r.data.url}).catch(function(e){notify(e.message,false)})
}
function closeFull(){
 var target='';
 try{target=sessionStorage.getItem('pb_aria_return_url')||'';sessionStorage.removeItem('pb_aria_return_url')}catch(e){}
 if(target&&target!==window.location.href){window.location.href=target;return}
 if(document.referrer){
  try{var ref=new URL(document.referrer,window.location.href);if(ref.origin===window.location.origin&&ref.href!==window.location.href){window.location.href=ref.href;return}}catch(e){}
 }
 if(window.history.length>1){window.history.back();return}
 window.location.href=state.siteHost+'admin/';
}
function openLicense(){qa('[data-aria-license-modal]').forEach(function(m){m.hidden=false});qa('[data-aria-license-key]').forEach(function(i){i.value=''});qa('[data-aria-license-result]').forEach(function(r){r.hidden=true;r.textContent=''})}
function closeLicense(){qa('[data-aria-license-modal]').forEach(function(m){m.hidden=true})}
function activateLicense(btn){var modal=btn.closest('[data-aria-license-modal]')||document,input=q('[data-aria-license-key]',modal),result=q('[data-aria-license-result]',modal),key=input?input.value.trim():'';if(!key){if(result){result.hidden=false;result.className='aria-license-result is-error';result.textContent='Введите ключ лицензии.'}return}btn.disabled=true;api('activate_license',{license_key:key,conversation_id:state.currentId||0}).then(function(r){var d=r.data||{};state.service=d||state.service;renderService();if(input)input.value='';closeLicense();notify('Лицензия ARIA активирована',true);var message=String(d.activation_message||'').trim(),cid=Number(d.conversation_id)||state.currentId||0,title=String(d.conversation_title||((d.tier_label||tierLabel())+' активирована'));if(cid){var switched=Number(state.currentId)!==cid;state.currentId=cid;if(switched){setTitle(title);renderMessages([])}if(message)appendMessage('assistant',message,{tier_label:d.tier_label||tierLabel()});var found=false;state.conversations.forEach(function(c){if(Number(c.id)===cid){c.title=title||c.title;c.last_message_at=new Date().toISOString();found=true}});if(!found)state.conversations.unshift({id:cid,title:title,last_message_at:new Date().toISOString()});renderConversations()}else if(message){appendMessage('assistant',message,{tier_label:d.tier_label||tierLabel()})}focusInput()}).catch(function(e){if(result){result.hidden=false;result.className='aria-license-result is-error';result.textContent=e.message}}).finally(function(){btn.disabled=false})}
function bind(){
 var widget=q('#pb-aria-widget'),full=q('.aria-fullpage-root');if(full&&widget)widget.style.display='none';var root=full||widget;if(!root)return;
 state.siteHost=(root.getAttribute('data-aria-site-host')||(widget&&widget.getAttribute('data-aria-site-host'))||'/');if(state.siteHost.slice(-1)!=='/')state.siteHost+='/';state.avatarUrl=state.siteHost+'templates/admin/img/aria-avatar.png';var token=q('#token');state.token=token?token.value:'';
 qa('[data-aria-widget-toggle]').forEach(function(b){b.addEventListener('click',openWidget)});qa('[data-aria-widget-close]').forEach(function(b){b.addEventListener('click',closeWidget)});qa('[data-aria-greeting-close]').forEach(function(b){b.addEventListener('click',function(e){e.stopPropagation();hideGreeting()})});
 qa('[data-aria-expand]').forEach(function(b){b.addEventListener('click',expandFull)});qa('[data-aria-full-close]').forEach(function(b){b.addEventListener('click',closeFull)});qa('[data-aria-new]').forEach(function(b){b.addEventListener('click',newConversation)});qa('[data-aria-delete]').forEach(function(b){b.addEventListener('click',deleteConversation)});
 qa('[data-aria-license-open]').forEach(function(b){b.addEventListener('click',openLicense)});qa('[data-aria-license-close]').forEach(function(b){b.addEventListener('click',closeLicense)});qa('[data-aria-license-activate]').forEach(function(b){b.addEventListener('click',function(){activateLicense(b)})});
 qa('[data-aria-suggest]').forEach(function(b){b.addEventListener('click',function(){if(serviceLocked())return;var scope=b.closest('.aria-widget-panel')||b.closest('.aria-fullpage-root')||document,input=scope.querySelector('[data-aria-input]');if(input&&!input.disabled){input.value=b.getAttribute('data-aria-suggest')||'';sendFrom(input)}})});
 qa('[data-aria-input]').forEach(function(input){input.addEventListener('input',function(){autoGrow(input)});input.addEventListener('keydown',function(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendFrom(input)}})});qa('[data-aria-send]').forEach(function(b){b.addEventListener('click',function(){var area=(b.closest('.aria-widget-panel')||b.closest('.aria-fullpage-root')||document).querySelector('[data-aria-input]');if(area)sendFrom(area)})});
 document.addEventListener('click',function(e){var copy=e.target.closest('[data-aria-copy]');if(copy){var code=q('code',copy.closest('.aria-code'));if(code&&navigator.clipboard)navigator.clipboard.writeText(code.textContent||'').then(function(){copy.textContent='Скопировано';setTimeout(function(){copy.textContent='Копировать'},1200)})}});
 if(full)document.addEventListener('keydown',function(e){if(e.key==='Escape'&&!q('[data-aria-license-modal]:not([hidden])'))closeFull()});
 if(full)bootstrap();else maybeGreeting()
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',bind);else bind();
setInterval(refreshService,30000);
})();
