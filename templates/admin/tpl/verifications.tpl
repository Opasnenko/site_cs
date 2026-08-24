

<div class="page">
	<div class="row">

		<div class="col-md-4">
			<div class="block very-info-card mb-4">
				<div class="block_head">Что такое верификация</div>
				<div class="very-info-body">
					<p>Верификация — значок рядом с именем пользователя, подтверждающий что аккаунт официально одобрен администрацией проекта.</p>
					<hr class="very-info-sep">
					<h6><i class="bx bx-code-block" style="color:#6366f1;"></i> Вывод значка в .tpl</h6>
					<code class="very-code">&lt;?=render_user_verification((int)'{user_id}');?&gt;</code>
					<p style="margin:8px 0 4px;">Или через класс:</p>
					<code class="very-code">$v = new Verification($pdo);
echo $v-&gt;render_badge($user_id);</code>
					<hr class="very-info-sep">
					<h6><i class="bx bx-folder" style="color:#6366f1;"></i> Файлы модуля</h6>
					<ul class="very-info-list">
						<li><b>Класс:</b> <code style="font-size:11px;color:#1d4ed8;">inc/classes/class.verification.php</code></li>
						<li><b>Значки:</b> <code style="font-size:11px;color:#1d4ed8;">templates/solution2/img/verification/</code></li>
						<li><b>AJAX (юзер):</b> <code style="font-size:11px;color:#1d4ed8;">ajax/addons/verification/actions.php</code></li>
						<li><b>AJAX (админ):</b> <code style="font-size:11px;color:#1d4ed8;">ajax/addons/verification/actions_admin.php</code></li>
					</ul>
					<hr class="very-info-sep">
					<h6><i class="bx bx-data" style="color:#6366f1;"></i> Поле <code style="font-size:11px;color:#1d4ed8;">users.verification</code></h6>
					<ul class="very-info-list">
						<li><span class="very-badge-tag">0</span> нет верификации</li>
						<li><span class="very-badge-tag" style="background:#dcfce7;color:#15803d;">1</span> верифицирован</li>
					</ul>
				</div>
			</div>
		</div>

		<div class="col-md-8">

			<div class="block mb-4">
				<div class="block_head">Иконка верификации</div>

				<b>Текущий значок</b>
				<div class="very-current-badge mt-2">
					<img src="{if('{very_img}' != '')}{very_img}{else}/templates/solution2/img/verification/bange_1.png{/if}" id="very_img_preview" alt="Значок">
					<div>
						<div class="vcb-name" id="very_img_name">{if('{very_img}' != '')}Кастомный значок{else}Стандартный (bange_1.png){/if}</div>
						<div class="vcb-label" id="very_img_path">{if('{very_img}' != '')}{very_img}{else}/templates/solution2/img/verification/bange_1.png{/if}</div>
					</div>
					{if('{very_img}' != '')}
					<button class="btn btn-default btn-xs ml-auto" type="button" onclick="reset_very_img();" style="margin-left:auto;white-space:nowrap;">Сбросить</button>
					{/if}
				</div>

				<hr style="margin:16px 0;">

				<b>Готовые варианты</b>
				<div class="very-preset-grid mt-2">
					<?php
					$presets = [
						'bange_1.png' => 'Стандартный',
						'v_1.gif'     => 'Вариант 1',
						'v_2.gif'     => 'Вариант 2',
						'v_3.gif'     => 'Вариант 3',
						'v_4.gif'     => 'Вариант 4',
						'v_5.gif'     => 'Вариант 5',
						'v_6.gif'     => 'Вариант 6',
						'v_7.gif'     => 'Вариант 7',
						'v_8.gif'     => 'Вариант 8',
						'v_9.gif'     => 'Вариант 9',
						'v_10.gif'    => 'Вариант 10',
						'v_11.gif'    => 'Вариант 11',
						'v_12.gif'    => 'Вариант 12',
						'v_13.png'    => 'Вариант 13',
					];
					$currentImg = '{very_img}';
					foreach($presets as $file => $label):
						$path = '/templates/solution2/img/verification/' . $file;
						$isActive = ($currentImg === $path) ? ' active' : '';
						if($isActive === '' && $currentImg === '' && $file === 'bange_1.png') $isActive = ' active';
					?>
					<div class="very-preset-card<?=$isActive;?>" onclick="select_preset('<?=$path;?>', '<?=$label;?>', this);">
						<div class="very-preset-check"></div>
						<img src="<?=$path;?>" alt="<?=$label;?>" loading="lazy">
						<span><?=$label;?></span>
					</div>
					<?php endforeach; ?>
				</div>

				<hr style="margin:16px 0;">

				<b>Загрузить свой значок</b>
				<div class="very-upload-zone mt-2 mb-2" onclick="$('#very_img_file').click();" style="cursor:pointer;">
					<i class="bx bx-upload" style="font-size:28px;color:#94a3b8;display:block;margin-bottom:6px;"></i>
					<div style="font-size:13px;font-weight:600;color:var(--uix-muted,#475569);">Нажмите чтобы выбрать файл</div>
					<div style="font-size:12px;color:#94a3b8;margin-top:3px;">PNG, JPG, SVG, GIF, WEBP — до 2 МБ</div>
					<div id="very_upload_filename" style="margin-top:8px;font-size:12px;font-weight:600;color:#6366f1;display:none;"></div>
				</div>
				<input type="file" id="very_img_file" accept="image/*" style="display:none;">

				<div class="mt-3" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
					<button class="btn btn-primary" type="button" onclick="save_very_settings();">
						<i class="bx bx-save" style="margin-right:4px;"></i>Сохранить
					</button>
					<span id="very_save_hint" style="font-size:12px;color:#94a3b8;">Выберите готовый вариант или загрузите свой файл</span>
				</div>
			</div>

			<div class="block mb-4">
				<div class="block_head">Подтверждённые профили</div>
				<div class="table-responsive mb-0">
					<table class="table table-bordered admins">
						<thead>
							<tr>
								<td>#</td>
								<td>Пользователь</td>
								<td>Параметры</td>
							</tr>
						</thead>
						<tbody>
							<?php
								$sth = pdo()->query("SELECT * FROM `users` WHERE `verification`='1'");
								if(!$sth->rowCount()):
							?>
								<tr><td colspan="3">Пользователей нет</td></tr>
							<?php
							else:
								while($row = $sth->fetch(PDO::FETCH_OBJ)):
							?>
								<tr>
									<td><?=$row->id;?></td>
									<td><?=("<a href=\"/profile?id={$row->id}\" target=\"_blank\">" . htmlspecialchars($row->login, ENT_QUOTES, 'UTF-8') . "</a>");?></td>
									<td onclick="off_very(<?=$row->id;?>);" style="cursor:pointer;">Забрать</td>
								</tr>
							<?php endwhile; endif; ?>
						</tbody>
					</table>
				</div>
			</div>

		</div>
	</div>
</div>

<script src="/ajax/addons/verification/ajax-very-admin.js?v={cache}"></script>
<script>
var _verySelectedPreset = null;
var _verySelectedFile   = null;

function select_preset(path, label, el) {
	_verySelectedPreset = path;
	_verySelectedFile   = null;
	$('#very_img_file').val('');
	$('#very_upload_filename').hide().text('');
	$('.very-preset-card').removeClass('active');
	$(el).addClass('active');
	$('#very_img_preview').attr('src', path);
	$('#very_img_name').text(label);
	$('#very_img_path').text(path);
	$('#very_save_hint').text('Нажмите «Сохранить» для применения');
}

$('#very_img_file').on('change', function() {
	var file = this.files[0];
	if (!file) return;
	if (file.size > 2 * 1024 * 1024) {
		push('Файл слишком большой. Максимум 2 МБ.', 'error');
		this.value = '';
		return;
	}
	_verySelectedFile   = file;
	_verySelectedPreset = null;
	$('.very-preset-card').removeClass('active');
	$('#very_upload_filename').show().text(file.name);
	$('#very_save_hint').text('Нажмите «Сохранить» для загрузки файла');
	var reader = new FileReader();
	reader.onload = function(e) {
		$('#very_img_preview').attr('src', e.target.result);
		$('#very_img_name').text('Загружаемый файл: ' + file.name);
		$('#very_img_path').text('');
	};
	reader.readAsDataURL(file);
});

function save_very_settings() {
	var form_data = new FormData();
	form_data.append('save_very_settings', '1');

	if (_verySelectedFile) {
		form_data.append('very_img_file', _verySelectedFile);
	} else if (_verySelectedPreset) {
		form_data.append('very_preset', _verySelectedPreset);
	} else {
		push('Выберите готовый вариант или загрузите файл.', 'warning');
		return;
	}

	send_post('/ajax/addons/verification/actions_admin.php', form_data, function(result) {
		if (result && result.status == 1) {
			push(result.message || 'Значок обновлён.', 'success');
			setTimeout(function(){ location.reload(); }, 900);
		} else {
			push((result && result.message) ? result.message : 'Ошибка при сохранении.', 'error');
		}
	});
}

function reset_very_img() {
	if (!confirm('Сбросить значок на стандартный?')) return;
	var form_data = new FormData();
	form_data.append('save_very_settings', '1');
	form_data.append('very_reset_img', '1');

	send_post('/ajax/addons/verification/actions_admin.php', form_data, function(result) {
		if (result && result.status == 1) {
			push(result.message || 'Значок сброшен.', 'success');
			setTimeout(function(){ location.reload(); }, 900);
		} else {
			push((result && result.message) ? result.message : 'Ошибка.', 'error');
		}
	});
}
</script>
