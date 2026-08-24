<div class="page">

    <div class="dc-card" style="margin-bottom:18px;">
        <div class="dc-card__head">
            <div class="dc-card__head-main">
                <span class="dc-card__icon dc-card__icon--green"><span class="glyphicon glyphicon-piggy-bank"></span></span>
                <div class="dc-card__titles">
                    <div class="dc-card__title">Монетизация</div>
                    <div class="dc-card__subtitle">Баланс банка и статистика поступлений</div>
                </div>
            </div>
            <span class="dc-card__badge">Billing</span>
        </div>
        <div class="dc-card__body">
            <div class="dc-grid3">
                <div class="dc-tile">
                    <div class="dc-tile__head">
                        <span class="dc-tile__icon"><span class="glyphicon glyphicon-stats"></span></span>
                        <div>
                            <div class="dc-tile__label">За всё время</div>
                            <div class="dc-tile__hint">Общий баланс банка</div>
                        </div>
                    </div>
                    <a class="td-u c-p dc-row__value" title="Подробнее" onclick="load_bank_info('1');" style="display:block;font-size:18px;font-weight:800;">{bank1} {{sys()->currency()->lang}}</a>
                </div>
                <div class="dc-tile">
                    <div class="dc-tile__head">
                        <span class="dc-tile__icon"><span class="glyphicon glyphicon-calendar"></span></span>
                        <div>
                            <div class="dc-tile__label">За данный месяц</div>
                            <div class="dc-tile__hint">Поступления с начала месяца</div>
                        </div>
                    </div>
                    <a class="td-u c-p" title="Подробнее" onclick="load_bank_info('2');" style="display:block;font-size:18px;font-weight:800;">{bank2} {{sys()->currency()->lang}}</a>
                </div>
                <div class="dc-tile">
                    <div class="dc-tile__head">
                        <span class="dc-tile__icon"><span class="glyphicon glyphicon-time"></span></span>
                        <div>
                            <div class="dc-tile__label">За прошлый месяц</div>
                            <div class="dc-tile__hint">Поступления за предыдущий месяц</div>
                        </div>
                    </div>
                    <a class="td-u c-p" title="Подробнее" onclick="load_bank_info('3');" style="display:block;font-size:18px;font-weight:800;">{bank3} {{sys()->currency()->lang}}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="dc-wrap">
    <div class="dc-col">

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon"><span class="glyphicon glyphicon-cog"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Основные настройки</div>
                        <div class="dc-card__subtitle">Стартовый баланс пользователя</div>
                    </div>
                </div>
                <span class="dc-card__badge">System</span>
            </div>
            <div class="dc-card__body">
                <div class="dc-grid2">
                    <div class="dc-tile dc-span2">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-usd"></span></span>
                            <div>
                                <div class="dc-tile__label">Начальный баланс пользователя</div>
                                <div class="dc-tile__hint">Сумма, которую получает новый пользователь при регистрации.</div>
                            </div>
                        </div>
                        <div class="dc-field">
                            <input type="number" id="stand_balance" maxlength="5" autocomplete="off" placeholder="от 0 до 99999" value="{stand_balance}">
                            <button class="dc-field__btn dc-field__btn--primary" type="button" onclick="edit_stand_balance();">Изменить</button>
                        </div>
                        <div id="edit_stand_balance_result" class="dc-result"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--blue"><span class="glyphicon glyphicon-random"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Реферальная программа</div>
                        <div class="dc-card__subtitle">Вознаграждение за приглашённых пользователей</div>
                    </div>
                </div>
                <span class="dc-card__badge">Referral</span>
            </div>
            <div class="dc-card__body">
                <div class="dc-grid2">
                    <div class="dc-tile dc-span2">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-flash"></span></span>
                            <div>
                                <div class="dc-tile__label">Статус программы</div>
                                <div class="dc-tile__hint">Начисление бонуса пригласившему пользователю.</div>
                            </div>
                        </div>
                        <div class="dc-toggle dc-toggle--onoff dc-toggle--compact" data-toggle="buttons">
                            <label class="{ref_act}" onclick="change_value('config__prices','referral_program ','1','1');">Вкл</label>
                            <label class="{ref_act2}" onclick="change_value('config__prices','referral_program ','2','1');">Выкл</label>
                        </div>
                    </div>

                    <div class="dc-tile dc-span2">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-scale"></span></span>
                            <div>
                                <div class="dc-tile__label">Процент от пополнения</div>
                                <div class="dc-tile__hint">Какой процент от пополнения реферала получит пригласивший.</div>
                            </div>
                        </div>
                        <div class="dc-field">
                            <input type="number" id="referral_percent" maxlength="2" autocomplete="off" placeholder="от 0 до 99" value="{referral_percent}">
                            <button class="dc-field__btn dc-field__btn--primary" type="button" onclick="edit_referral_percent();">Изменить</button>
                        </div>
                        <div id="edit_referral_percent_result" class="dc-result"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--amber"><span class="glyphicon glyphicon-ban-circle"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Цены на разбан</div>
                        <div class="dc-card__subtitle">Стоимость платного снятия бана по срокам</div>
                    </div>
                </div>
                <span class="dc-card__badge">Pricing</span>
            </div>
            <div class="dc-card__body">
                <div class="dc-grid3">
                    <div class="dc-tile">
                        <div class="dc-tile__label">До 7 дней</div>
                        <div class="dc-tile__hint">0 — выключить</div>
                        <div class="dc-field" style="margin-top:8px;">
                            <input type="number" id="price1" maxlength="5" autocomplete="off" value="{price1}">
                        </div>
                    </div>
                    <div class="dc-tile">
                        <div class="dc-tile__label">Более 7 дней</div>
                        <div class="dc-tile__hint">0 — выключить</div>
                        <div class="dc-field" style="margin-top:8px;">
                            <input type="number" id="price2" maxlength="5" autocomplete="off" value="{price2}">
                        </div>
                    </div>
                    <div class="dc-tile">
                        <div class="dc-tile__label">Перманентный бан</div>
                        <div class="dc-tile__hint">0 — выключить</div>
                        <div class="dc-field" style="margin-top:8px;">
                            <input type="number" id="price3" maxlength="5" autocomplete="off" value="{price3}">
                        </div>
                    </div>
                </div>
                <button class="dc-btn dc-btn--primary" style="margin-top:12px;" type="button" onclick="edit_unban();">Сохранить цены</button>
                <div id="edit_unban_result" class="dc-result"></div>
            </div>
        </div>

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--amber"><span class="glyphicon glyphicon-volume-off"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Цены на размут</div>
                        <div class="dc-card__subtitle">Стоимость платного снятия мута по срокам</div>
                    </div>
                </div>
                <span class="dc-card__badge">Pricing</span>
            </div>
            <div class="dc-card__body">
                <div class="dc-grid3">
                    <div class="dc-tile">
                        <div class="dc-tile__label">До 7 дней</div>
                        <div class="dc-tile__hint">0 — выключить</div>
                        <div class="dc-field" style="margin-top:8px;">
                            <input type="number" id="price2_1" maxlength="5" autocomplete="off" value="{price2_1}">
                        </div>
                    </div>
                    <div class="dc-tile">
                        <div class="dc-tile__label">Более 7 дней</div>
                        <div class="dc-tile__hint">0 — выключить</div>
                        <div class="dc-field" style="margin-top:8px;">
                            <input type="number" id="price2_2" maxlength="5" autocomplete="off" value="{price2_2}">
                        </div>
                    </div>
                    <div class="dc-tile">
                        <div class="dc-tile__label">Перманентный мут</div>
                        <div class="dc-tile__hint">0 — выключить</div>
                        <div class="dc-field" style="margin-top:8px;">
                            <input type="number" id="price2_3" maxlength="5" autocomplete="off" value="{price2_3}">
                        </div>
                    </div>
                </div>
                <button class="dc-btn dc-btn--primary" style="margin-top:12px;" type="button" onclick="edit_unmute();">Сохранить цены</button>
                <div id="edit_unmute_result" class="dc-result"></div>
            </div>
        </div>

    </div>

    <div class="dc-col">

        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon"><span class="glyphicon glyphicon-list-alt"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Операции пользователей</div>
                        <div class="dc-card__subtitle">Последние транзакции по банку сайта</div>
                    </div>
                </div>
            </div>
            <div class="dc-card__body">
                <div class="table-responsive mb-0">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <td>#</td>
                                <td>Тип</td>
                                <td>Сумма</td>
                                <td>Пользователь</td>
                                <td>Дата</td>
                            </tr>
                        </thead>
                        <tbody id="operations">
                            <tr>
                                <td colspan="5"><br><center><img src="{site_host}templates/admin/img/loader.gif"></center><br></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <script>get_shilings_operations('first');</script>
            </div>
        </div>

    </div>
    </div>

</div>

<div id="bank1" class="modal fade">
	<div class="modal-dialog modal-lg2">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Статистика за все время ({bank1}{{sys()->currency()->lang}})</h4>
			</div>
			<div class="modal-body">
				<div id="bank_info1"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="bank2" class="modal fade">
	<div class="modal-dialog modal-lg2">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Статистика за данный месяц ({bank2}{{sys()->currency()->lang}})</h4>
			</div>
			<div class="modal-body">
				<div id="bank_info2"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="bank3" class="modal fade">
	<div class="modal-dialog modal-lg2">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Статистика за прошлый месяц ({bank3}{{sys()->currency()->lang}})</h4>
			</div>
			<div class="modal-body">
				<div id="bank_info3"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>
