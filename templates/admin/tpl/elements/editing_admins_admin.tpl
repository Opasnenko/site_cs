<tr id="admin{id}" {if('{comment}' != '')} title="{comment}" {/if} class="adm-row{if('{active}' == '2')} adm-row--off{/if}{if('{pause}' != '0')} adm-row--paused{/if}">
	<td class="adm-table__num">
		<span class="adm-row__index">{i}</span>
		<div id="admin_modal{id}" class="modal fade admin-quick-modal bs-example-modal-lg">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Подробная информация</h4>
					</div>
					<div class="modal-body" id="admin_info{id}"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
					</div>
				</div>
			</div>
		</div>
	</td>
	<td id="new_user_{id}">
		{if('{user_id}' == '0')}
		<span class="adm-user adm-user--empty">
			<img src="../files/avatars/no_avatar.jpg" alt="">
			<span class="adm-user__name">Неизвестно</span>
		</span>
		{else}
		<a class="adm-user" target="_blank" href="../profile?id={user_id}">
			<img src="../{avatar}" alt="{login}">
			<span class="adm-user__name">{login}</span>
		</a>
		{/if}
	</td>
	<td id="new_name_{id}">
		<span class="adm-ident">{name}</span>
		{if('{comment}' != '')}<span class="adm-note glyphicon glyphicon-tag" title="{comment}"></span>{/if}
	</td>
	<td id="new_services_{id}">
		<span class="adm-services">{services}</span>
	</td>
	<td>
		<span class="adm-date">{ending_date}</span>
	</td>
	<td class="adm-table__actions">
		<div class="adm-actions">
			<button type="button" class="adm-btn" onclick="get_admin_info({id});" data-target="#admin_modal{id}" data-toggle="modal" title="Подробнее">
				<span class="glyphicon glyphicon-cog"></span> Опции
			</button>
			<button type="button" class="adm-btn adm-btn--warn{if('{warns}' != '0')} is-active{/if}" onclick="pbOpenWarns({id}, '{name}');" title="Выговоры">
				<span class="glyphicon glyphicon-warning-sign"></span> Выговоры{if('{warns}' != '0')} <b class="adm-btn__count">{warns}</b>{/if}
			</button>
		</div>
	</td>
</tr>
