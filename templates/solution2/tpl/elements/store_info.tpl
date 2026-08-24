<div class="mst-info">
	{if('{active}' == '2')}
	<div class="mst-banner mst-banner--danger" id="active{id}">
		<div class="mst-banner__icon"><i class='bx bx-lock-alt'></i></div>
		<div class="mst-banner__body">
			<b>Услуга заблокирована</b>
			<span>Причина: {cause_text}</span>
			{if('{warns}' != '0')}
			<span class="mst-banner__warns{if('{warns_at_limit}' == '1')} is-limit{/if}">
				<i class='bx bx-error-circle'></i> Выговоры: {warns} из {warns_limit}{if('{warns_at_limit}' == '1')} — лимит исчерпан{/if}
			</span>
			{if('{warns_reason}' != '')}<span class="mst-banner__warns-reason">За что: {warns_reason}</span>{/if}
			{/if}
			{if('{link}' != '')}<a href="{link}" target="_blank" rel="noopener">Посмотреть доказательства</a>{/if}
		</div>
		<button id="on_{id}" onclick="start_srote({id});" class="mst-btn mst-btn--accent" type="button">
			<i class='bx bx-lock-open-alt'></i> Разблокировать — {price} {currency}
		</button>
	</div>
	{else}
	<div class="mst-row" id="active{id}">
		<div class="mst-row__icon"><i class='bx bx-station'></i></div>
		<div class="mst-row__body">
			<span class="mst-row__title">Активность</span>
			<span class="mst-row__label">Текущий статус услуги</span>
		</div>
		{if('{pause}' != '0')}
			<span class="mst-badge mst-badge--warning"><i class='bx bx-pause-circle'></i> Приостановлен</span>
		{else}
			<span class="mst-badge mst-badge--success"><i class='bx bx-check-circle'></i> Активен</span>
		{/if}
	</div>
	{/if}

	{if('{warns}' != '0')}
	<div class="mst-banner mst-banner--warning mst-banner--warns">
		<div class="mst-banner__icon"><i class='bx bx-error-circle'></i></div>
		<div class="mst-banner__body">
			<b>Выговоры: {warns} из {warns_limit}</b>
			{if('{warns_at_limit}' == '1')}
			<span>Лимит исчерпан — привилегия может быть снята.</span>
			{else}
			<span>При достижении лимита привилегия снимается автоматически.</span>
			{/if}
			{if('{warns_reason}' != '')}<span class="mst-banner__warns-reason">За что: {warns_reason}</span>{/if}
		</div>
	</div>
	{/if}

	<div class="mst-row">
		<div class="mst-row__icon"><i class='bx bx-package'></i></div>
		<div class="mst-row__body">
			<span class="mst-row__label">Тип привязки</span>
			<select id="store_type_{id}" class="mst-select" onchange="local_change_admin_type({id});" {disabled}>
				{if('{peg_1}' != '2')}<option {if('{type}' == 'a')} selected {/if} value="1">Ник + пароль</option>{/if}
				{if('{peg_2}' != '2')}<option {if('{type}' == 'ce')} selected {/if} value="2">STEAM ID</option>{/if}
			</select>
		</div>
		<button class="mst-btn" type="button" {if('{active}' != '2')}onclick="edit_store({id}, 'type');"{/if} {disabled}>Изменить</button>
	</div>

	<div class="mst-row" id="input_name{id}">
		<div class="mst-row__icon"><i class='bx bx-id-card'></i></div>
		<div class="mst-row__body">
			<span class="mst-row__label">Идентификатор</span>
			<input id="player_name_{id}" class="mst-input" maxlength="32" value="{name}" type="text" placeholder="Введите идентификатор" {disabled}>
		</div>
		<button class="mst-btn" type="button" {if('{active}' != '2')}onclick="edit_store({id}, 'name');"{/if} {disabled}>Изменить</button>
	</div>

	<div class="mst-row" id="input_pass{id}">
		<div class="mst-row__icon"><i class='bx bx-key'></i></div>
		<div class="mst-row__body">
			<span class="mst-row__label">Пароль</span>
			<input id="player_pass_{id}" class="mst-input" maxlength="32" value="{pass}" type="password" placeholder="Введите пароль" {disabled}>
		</div>
		<button class="mst-btn mst-btn--icon" type="button" title="Показать пароль" onclick="var i=document.getElementById('player_pass_{id}');i.type=(i.type=='password'?'text':'password');this.firstElementChild.className=(i.type=='password'?'bx bx-show':'bx bx-hide');">
			<i class='bx bx-show'></i>
		</button>
		<button class="mst-btn" type="button" {if('{active}' != '2')}onclick="edit_store({id}, 'pass');"{/if} {disabled}>Изменить</button>
	</div>

	<script>local_change_admin_type({id});</script>

	<div class="mst-services-head">
		<span>Услуги</span>
		<small>Купленные привилегии</small>
	</div>

	<div class="mst-services" id="admins_services{id}">
		{services}
	</div>
</div>
