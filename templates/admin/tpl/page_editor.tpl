<input type="hidden" id="token" value="{token}">

<div class="page">

    <div class="pe-tabs" id="pe-tabs">
        <button type="button" class="pe-tab is-active" data-pe-tab="pe-public" onclick="load_pages(1, 'public');">
            <span class="glyphicon glyphicon-globe"></span>
            <span>Публичные</span>
        </button>
        <button type="button" class="pe-tab" data-pe-tab="pe-service" onclick="load_pages(1, 'service');">
            <span class="glyphicon glyphicon-cog"></span>
            <span>Служебные</span>
        </button>
        <button type="button" class="pe-tab" data-pe-tab="pe-user" onclick="load_pages(2);">
            <span class="glyphicon glyphicon-file"></span>
            <span>Дополнительные</span>
        </button>
        <button type="button" class="pe-tab" data-pe-tab="pe-create">
            <span class="glyphicon glyphicon-plus"></span>
            <span>Создать</span>
        </button>
        <button type="button" class="pe-tab" data-pe-tab="pe-classes" onclick="load_classes(2);">
            <span class="glyphicon glyphicon-folder-open"></span>
            <span>Категории</span>
        </button>
        <button type="button" class="pe-tab" data-pe-tab="pe-default-image">
            <span class="glyphicon glyphicon-picture"></span>
            <span>Картинка по умолчанию</span>
        </button>
        <button type="button" class="pe-tab" data-pe-tab="pe-paginator">
            <span class="glyphicon glyphicon-th-list"></span>
            <span>Пагинатор</span>
        </button>
    </div>

    <div class="pe-pane is-active" id="pe-public">
        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--blue"><span class="glyphicon glyphicon-globe"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Публичные страницы сайта</div>
                        <div class="dc-card__subtitle">Меню, витрина, футер и открытые разделы проекта</div>
                    </div>
                </div>
            </div>
            <div class="dc-card__body">
                <div class="dc-state dc-state--info" style="margin-bottom:14px;">
                    <span class="dc-state__icon glyphicon glyphicon-info-sign"></span>
                    <div>
                        <b>Что здесь настраивается</b>
                        <small>«Включена» — страница доступна на сайте. «Выключена» — маршрут не откроется. Для скрытых системных маршрутов используйте вкладку «Служебные».</small>
                    </div>
                </div>

                <div class="pe-toolbar">
                    <div class="pe-search">
                        <span class="glyphicon glyphicon-search"></span>
                        <input type="text" id="public_pages_search" autocomplete="off" placeholder="Поиск по публичным страницам">
                    </div>
                    <div class="pe-chips" id="public_pages_filters">
                        <button type="button" class="pe-chip is-active" data-filter-state="all">Все</button>
                        <button type="button" class="pe-chip" data-filter-state="enabled">Включенные</button>
                        <button type="button" class="pe-chip" data-filter-state="disabled">Выключенные</button>
                    </div>
                </div>

                <div id="public_pages" class="admin-pages-grid">
                    <div class="admin-loading-card"><img src="{site_host}templates/admin/img/loader.gif"></div>
                    <script>load_pages(1, 'public');</script>
                </div>
            </div>
        </div>
    </div>

    <div class="pe-pane" id="pe-service">
        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--slate"><span class="glyphicon glyphicon-cog"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Кабинет и служебные страницы</div>
                        <div class="dc-card__subtitle">Маршруты профиля, настроек и внутренних сценариев движка</div>
                    </div>
                </div>
            </div>
            <div class="dc-card__body">
                <div class="dc-state dc-state--warning" style="margin-bottom:14px;">
                    <span class="dc-state__icon glyphicon glyphicon-alert"></span>
                    <div>
                        <b>Будьте внимательны</b>
                        <small>Скрытая страница не означает выключенная. Для служебных маршрутов показ в меню не обязателен — правьте только то, назначение чего понятно.</small>
                    </div>
                </div>

                <div class="pe-toolbar">
                    <div class="pe-search">
                        <span class="glyphicon glyphicon-search"></span>
                        <input type="text" id="service_pages_search" autocomplete="off" placeholder="Поиск по служебным страницам">
                    </div>
                    <div class="pe-chips" id="service_pages_filters">
                        <button type="button" class="pe-chip is-active" data-filter-state="all">Все</button>
                        <button type="button" class="pe-chip" data-filter-state="enabled">Включенные</button>
                        <button type="button" class="pe-chip" data-filter-state="disabled">Выключенные</button>
                    </div>
                </div>

                <div id="service_pages" class="admin-pages-grid">
                    <div class="admin-loading-card"><img src="{site_host}templates/admin/img/loader.gif"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="pe-pane" id="pe-user">
        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--green"><span class="glyphicon glyphicon-file"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Дополнительные страницы</div>
                        <div class="dc-card__subtitle">Созданные вручную страницы, сгруппированные по категориям</div>
                    </div>
                </div>
            </div>
            <div class="dc-card__body">
                <div class="pe-toolbar">
                    <div class="pe-search">
                        <span class="glyphicon glyphicon-search"></span>
                        <input type="text" id="user_pages_search" autocomplete="off" placeholder="Поиск по дополнительным страницам">
                    </div>
                    <div class="pe-chips" id="user_pages_filters">
                        <button type="button" class="pe-chip is-active" data-filter-state="all">Все</button>
                        <button type="button" class="pe-chip" data-filter-state="enabled">Включенные</button>
                        <button type="button" class="pe-chip" data-filter-state="disabled">Выключенные</button>
                    </div>
                </div>

                <div id="user_pages" class="admin-pages-grid">
                    <div class="admin-loading-card"><img src="{site_host}templates/admin/img/loader.gif"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="pe-pane" id="pe-create">
        <div class="dc-wrap">
            <div class="dc-col">
                <div class="dc-card">
                    <div class="dc-card__head">
                        <div class="dc-card__head-main">
                            <span class="dc-card__icon dc-card__icon--amber"><span class="glyphicon glyphicon-plus"></span></span>
                            <div class="dc-card__titles">
                                <div class="dc-card__title">Параметры страницы</div>
                                <div class="dc-card__subtitle">Адрес, мета-теги и режим доступа</div>
                            </div>
                        </div>
                    </div>
                    <div class="dc-card__body">
                        <div class="dc-grid2">
                            <div class="dc-tile">
                                <div class="dc-tile__head">
                                    <span class="dc-tile__icon"><span class="glyphicon glyphicon-folder-open"></span></span>
                                    <div>
                                        <div class="dc-tile__label">Категория</div>
                                        <div class="dc-tile__hint">Раздел, к которому относится страница.</div>
                                    </div>
                                </div>
                                <div class="dc-field">
                                    <select id="input_class">{classes}</select>
                                </div>
                            </div>

                            <div class="dc-tile">
                                <div class="dc-tile__head">
                                    <span class="dc-tile__icon"><span class="glyphicon glyphicon-lock"></span></span>
                                    <div>
                                        <div class="dc-tile__label">Приватность</div>
                                        <div class="dc-tile__hint">Кому доступна страница.</div>
                                    </div>
                                </div>
                                <div class="dc-field">
                                    <select id="input_privacy" onchange="change_privacy();">
                                        <option value="2">Всем пользователям</option>
                                        <option value="1">Только авторизованным</option>
                                    </select>
                                </div>
                            </div>

                            <div class="dc-tile">
                                <div class="dc-tile__head">
                                    <span class="dc-tile__icon"><span class="glyphicon glyphicon-search"></span></span>
                                    <div>
                                        <div class="dc-tile__label">Индексация</div>
                                        <div class="dc-tile__hint">Показывать ли страницу поисковикам.</div>
                                    </div>
                                </div>
                                <div class="dc-field">
                                    <select id="input_robots" onchange="change_robots();">
                                        <option value="1">Индексируется</option>
                                        <option value="2">Не индексируется</option>
                                    </select>
                                </div>
                            </div>

                            <div class="dc-tile">
                                <div class="dc-tile__head">
                                    <span class="dc-tile__icon"><span class="glyphicon glyphicon-flash"></span></span>
                                    <div>
                                        <div class="dc-tile__label">Активность</div>
                                        <div class="dc-tile__hint">Доступна ли страница на сайте.</div>
                                    </div>
                                </div>
                                <div class="dc-field">
                                    <select id="input_active">
                                        <option value="1">Включить</option>
                                        <option value="2">Выключить</option>
                                    </select>
                                </div>
                            </div>

                            <div class="dc-tile dc-span2">
                                <div class="dc-tile__head">
                                    <span class="dc-tile__icon"><span class="glyphicon glyphicon-link"></span></span>
                                    <div>
                                        <div class="dc-tile__label">Адрес страницы</div>
                                        <div class="dc-tile__hint">Латиницей, без пробелов. Пример: <b>about</b></div>
                                    </div>
                                </div>
                                <div class="dc-field">
                                    <input type="text" id="input_url" maxlength="30" autocomplete="off" placeholder="about">
                                </div>
                            </div>

                            <div class="dc-tile dc-span2">
                                <div class="dc-tile__head">
                                    <span class="dc-tile__icon"><span class="glyphicon glyphicon-header"></span></span>
                                    <div>
                                        <div class="dc-tile__label">Заголовок</div>
                                        <div class="dc-tile__hint">Тег title — виден во вкладке браузера и в поиске.</div>
                                    </div>
                                </div>
                                <div class="dc-field">
                                    <input type="text" id="input_title" maxlength="80" autocomplete="off" placeholder="О проекте">
                                </div>
                            </div>

                            <div class="dc-tile dc-span2">
                                <div class="dc-tile__head">
                                    <span class="dc-tile__icon"><span class="glyphicon glyphicon-align-left"></span></span>
                                    <div>
                                        <div class="dc-tile__label">Описание</div>
                                        <div class="dc-tile__hint">Тег description — краткая аннотация для поиска.</div>
                                    </div>
                                </div>
                                <div class="dc-field">
                                    <input type="text" id="input_description" maxlength="150" autocomplete="off" placeholder="Краткое описание страницы">
                                </div>
                            </div>

                            <div class="dc-tile dc-span2">
                                <div class="dc-tile__head">
                                    <span class="dc-tile__icon"><span class="glyphicon glyphicon-tags"></span></span>
                                    <div>
                                        <div class="dc-tile__label">Ключевые слова</div>
                                        <div class="dc-tile__hint">Тег keywords — через запятую.</div>
                                    </div>
                                </div>
                                <div class="dc-field">
                                    <input type="text" id="input_keywords" maxlength="150" autocomplete="off" placeholder="проект, сервер, игра">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dc-col">
                <div class="dc-card">
                    <div class="dc-card__head">
                        <div class="dc-card__head-main">
                            <span class="dc-card__icon dc-card__icon--img"><span class="glyphicon glyphicon-picture"></span></span>
                            <div class="dc-card__titles">
                                <div class="dc-card__title">Изображение страницы</div>
                                <div class="dc-card__subtitle">Превью для соцсетей и каталога</div>
                            </div>
                        </div>
                    </div>
                    <div class="dc-card__body">
                        <div class="pe-image">
                            <div class="pe-image__preview">
                                <img id="img" src="../{default_image}" alt="">
                            </div>
                            <div class="pe-image__side">
                                <div class="dc-hint" style="margin-bottom:10px;">Загружайте изображения с небольшим весом — это ускоряет сайт.</div>
                                <form enctype="multipart/form-data" action="ajax/actions_panel.php" method="POST" id="img_form">
                                    <input type="hidden" name="token" value="{token}">
                                    <input type="hidden" name="load_page_image_2" value="1">
                                    <input type="hidden" name="phpaction" value="1">
                                    <label class="pe-file">
                                        <span class="glyphicon glyphicon-folder-open"></span>
                                        <span>Выбрать файл</span>
                                        <input type="file" accept="image/*" name="image">
                                    </label>
                                    <button type="submit" class="dc-btn dc-btn--primary" style="margin-top:8px;">
                                        <span class="glyphicon glyphicon-upload"></span> Загрузить
                                    </button>
                                    <div id="img_result" class="dc-result"></div>
                                </form>
                                <input value="{default_image}" type="hidden" id="input_image" maxlength="255">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dc-card" style="margin-top:14px;">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--blue"><span class="glyphicon glyphicon-edit"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Содержимое страницы</div>
                        <div class="dc-card__subtitle">Текст, изображения и разметка</div>
                    </div>
                </div>
            </div>
            <div class="dc-card__body">
                <div class="pe-editor">
                    <textarea name="input_content" id="input_content" rows="10" cols="80"></textarea>
                </div>

                <div class="dc-version-actions" style="margin-top:14px;">
                    <button onclick="create_page();" type="button" class="btn btn-primary" style="border-radius:8px;font-weight:700;">
                        <span class="glyphicon glyphicon-ok"></span> Создать страницу
                    </button>
                </div>
                <div id="create_page_result" class="dc-result"></div>
            </div>
        </div>
    </div>

    <div class="pe-pane" id="pe-classes">
        <div class="dc-wrap">
            <div class="dc-col">
                <div class="dc-card">
                    <div class="dc-card__head">
                        <div class="dc-card__head-main">
                            <span class="dc-card__icon dc-card__icon--green"><span class="glyphicon glyphicon-plus"></span></span>
                            <div class="dc-card__titles">
                                <div class="dc-card__title">Новая категория</div>
                                <div class="dc-card__subtitle">Группировка дополнительных страниц</div>
                            </div>
                        </div>
                    </div>
                    <div class="dc-card__body">
                        <div class="dc-field">
                            <input type="text" id="class_name" maxlength="20" value="" autocomplete="off" placeholder="Название категории">
                            <button type="button" class="dc-field__btn dc-field__btn--primary" onclick="add_class(2);">
                                <span class="glyphicon glyphicon-plus"></span> Добавить
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dc-col">
                <div class="dc-card">
                    <div class="dc-card__head">
                        <div class="dc-card__head-main">
                            <span class="dc-card__icon dc-card__icon--slate"><span class="glyphicon glyphicon-folder-open"></span></span>
                            <div class="dc-card__titles">
                                <div class="dc-card__title">Категории</div>
                                <div class="dc-card__subtitle">Список созданных разделов</div>
                            </div>
                        </div>
                    </div>
                    <div class="dc-card__body" id="classes">
                        <div class="admin-loading-card"><img src="{site_host}templates/admin/img/loader.gif"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pe-pane" id="pe-default-image">
        <div class="dc-wrap">
            <div class="dc-col">
                <div class="dc-card">
                    <div class="dc-card__head">
                        <div class="dc-card__head-main">
                            <span class="dc-card__icon dc-card__icon--img"><span class="glyphicon glyphicon-picture"></span></span>
                            <div class="dc-card__titles">
                                <div class="dc-card__title">Картинка страниц по умолчанию</div>
                                <div class="dc-card__subtitle">Используется, когда у страницы не задано своё изображение</div>
                            </div>
                        </div>
                        <span class="dc-card__badge" id="pdi_used_badge">—</span>
                    </div>
                    <div class="dc-card__body">
                        <div class="dc-state dc-state--info" style="margin-bottom:14px;">
                            <span class="dc-state__icon glyphicon glyphicon-info-sign"></span>
                            <div>
                                <b>Как это работает</b>
                                <small>Это превью подставляется новым страницам и показывается в соцсетях при репосте. Страницы с собственной картинкой останутся без изменений, если не включить перенос ниже.</small>
                            </div>
                        </div>

                        <div class="pe-image">
                            <div class="pe-image__preview pe-image__preview--wide">
                                <img id="pdi_preview" src="../files/miniatures/pbgame_ui.jpg" alt="">
                            </div>
                            <div class="pe-image__side">
                                <div class="dc-hint" style="margin-bottom:10px;">
                                    Рекомендуемый размер — 1200×630 пикселей. Форматы: JPG, PNG, GIF или WEBP, до 5 МБ.
                                </div>

                                <label class="pe-file">
                                    <span class="glyphicon glyphicon-folder-open"></span>
                                    <span id="pdi_file_label">Выбрать изображение</span>
                                    <input type="file" id="pdi_file" accept="image/png,image/jpeg,image/gif,image/webp">
                                </label>

                                <div class="dc-tile__label" style="margin-top:12px;margin-bottom:6px;">Применить к существующим страницам</div>
                                <div class="pe-modes" id="pdi_modes">
                                    <button type="button" class="pe-mode is-active" data-mode="0">
                                        <b>Не менять</b>
                                        <small>Только для новых страниц</small>
                                    </button>
                                    <button type="button" class="pe-mode" data-mode="1">
                                        <b>Где стоит дефолт</b>
                                        <small>Не тронет страницы со своей картинкой</small>
                                    </button>
                                    <button type="button" class="pe-mode" data-mode="2">
                                        <b>Заменить везде</b>
                                        <small>Перезапишет картинку у всех страниц</small>
                                    </button>
                                </div>

                                <div class="dc-version-actions" style="margin-top:12px;">
                                    <button type="button" class="btn btn-primary" id="pdi_save" style="border-radius:8px;font-weight:700;">
                                        <span class="glyphicon glyphicon-ok"></span> Сохранить
                                    </button>
                                    <button type="button" class="btn btn-default" id="pdi_reset" style="border-radius:8px;font-weight:700;">
                                        <span class="glyphicon glyphicon-refresh"></span> Вернуть стандартную
                                    </button>
                                </div>
                                <div id="pdi_result" class="dc-result"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pe-pane" id="pe-paginator">
        <div class="dc-card">
            <div class="dc-card__head">
                <div class="dc-card__head-main">
                    <span class="dc-card__icon dc-card__icon--slate"><span class="glyphicon glyphicon-th-list"></span></span>
                    <div class="dc-card__titles">
                        <div class="dc-card__title">Пагинатор</div>
                        <div class="dc-card__subtitle">Количество элементов на страницах сайта</div>
                    </div>
                </div>
            </div>
            <div class="dc-card__body">
                <div class="dc-grid2">
                    <div class="dc-tile">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-user"></span></span>
                            <div><div class="dc-tile__label">Все пользователи</div></div>
                        </div>
                        <div class="dc-field"><input type="number" id="users_lim" maxlength="3" autocomplete="off" value="{users_lim}"></div>
                    </div>
                    <div class="dc-tile">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-ban-circle"></span></span>
                            <div><div class="dc-tile__label">Банлист</div></div>
                        </div>
                        <div class="dc-field"><input type="number" id="bans_lim" maxlength="3" autocomplete="off" value="{bans_lim}"></div>
                    </div>
                    <div class="dc-tile">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-open"></span></span>
                            <div><div class="dc-tile__label">Заявки на разбан</div></div>
                        </div>
                        <div class="dc-field"><input type="number" id="bans_lim2" maxlength="3" autocomplete="off" value="{bans_lim2}"></div>
                    </div>
                    <div class="dc-tile">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-volume-off"></span></span>
                            <div><div class="dc-tile__label">Мутлист</div></div>
                        </div>
                        <div class="dc-field"><input type="number" id="muts_lim" maxlength="3" autocomplete="off" value="{muts_lim}"></div>
                    </div>
                    <div class="dc-tile">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-list-alt"></span></span>
                            <div><div class="dc-tile__label">Каталог новостей</div></div>
                        </div>
                        <div class="dc-field"><input type="number" id="news_lim" maxlength="3" autocomplete="off" value="{news_lim}"></div>
                    </div>
                    <div class="dc-tile">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-stats"></span></span>
                            <div><div class="dc-tile__label">Статистика</div></div>
                        </div>
                        <div class="dc-field"><input type="number" id="stats_lim" maxlength="3" autocomplete="off" value="{stats_lim}"></div>
                    </div>
                    <div class="dc-tile">
                        <div class="dc-tile__head">
                            <span class="dc-tile__icon"><span class="glyphicon glyphicon-flag"></span></span>
                            <div><div class="dc-tile__label">Жалобы</div></div>
                        </div>
                        <div class="dc-field"><input type="number" id="complaints_lim" maxlength="3" autocomplete="off" value="{complaints_lim}"></div>
                    </div>
                </div>

                <div class="dc-version-actions" style="margin-top:14px;">
                    <button class="btn btn-primary" type="button" onclick="edit_paginator();" style="border-radius:8px;font-weight:700;">
                        <span class="glyphicon glyphicon-ok"></span> Сохранить
                    </button>
                </div>
                <div id="edit_paginator_result" class="dc-result"></div>
            </div>
        </div>
    </div>

</div>

<script>
function change_privacy() {
    if($('#input_privacy').val() == '1') {
        $("#input_robots option[value=1]").prop('selected', 'false');
        $("#input_robots option[value=2]").prop('selected', 'true');
    }
}
function change_robots() {
    if($('#input_robots').val() == '1') {
        $("#input_privacy option[value=1]").prop('selected', 'false');
        $("#input_privacy option[value=2]").prop('selected', 'true');
    }
}

(function(){
    var tabs = document.getElementById('pe-tabs');
    if(!tabs){ return; }

    tabs.addEventListener('click', function(e){
        var btn = e.target.closest ? e.target.closest('.pe-tab') : null;
        if(!btn){ return; }

        var target = btn.getAttribute('data-pe-tab');
        tabs.querySelectorAll('.pe-tab').forEach(function(b){
            b.classList.toggle('is-active', b === btn);
        });
        document.querySelectorAll('.pe-pane').forEach(function(pane){
            pane.classList.toggle('is-active', pane.id === target);
        });

    });

    // Редактор поднимаем сразу: create_page() обращается к
    // CKEDITOR.instances.input_content напрямую, даже если вкладка не открывалась.
    window.pbInitPageEditor = function(){
        if(typeof CKEDITOR === 'undefined'){ return; }
        if(CKEDITOR.instances.input_content){ return; }

        var dark = document.body.getAttribute('data-admin-theme') === 'dark';
        CKEDITOR.replace('input_content', {
            height: 420,
            removePlugins: 'elementspath',
            resize_enabled: true,
            contentsCss: dark
                ? ['{site_host}modules/editors/ckeditor/contents.css', '{site_host}templates/admin/css/ckeditor-dark.css?v={cache}']
                : ['{site_host}modules/editors/ckeditor/contents.css']
        });
    };

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', window.pbInitPageEditor);
    } else {
        window.pbInitPageEditor();
    }
})();

(function(){
    var fileInput = document.getElementById('pdi_file');
    var preview = document.getElementById('pdi_preview');
    var label = document.getElementById('pdi_file_label');
    var saveBtn = document.getElementById('pdi_save');
    var resetBtn = document.getElementById('pdi_reset');
    var modesBox = document.getElementById('pdi_modes');
    var applyMode = 0;
    var result = document.getElementById('pdi_result');
    var badge = document.getElementById('pdi_used_badge');
    if(!fileInput || !saveBtn){ return; }

    var picked = null;

    function say(msg, ok){
        if(result){
            result.innerHTML = '<p class="' + (ok ? 'text-success' : 'text-danger') + ' m-0">' + msg + '</p>';
        }
        if(window.PBToast){ PBToast.show(ok ? 'success' : 'error', msg); }
    }

    function token(){
        var el = document.getElementById('token');
        return el ? el.value : '';
    }

    function refresh(){
        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: { phpaction: 1, token: token(), pb_page_default_image_get: 1 },
            dataType: 'json',
            success: function(res){
                if(!res || String(res.status) !== '1'){ return; }
                if(preview){ preview.src = '..' + res.image + '?t=' + Date.now(); }
                if(badge){ badge.textContent = res.used > 0 ? ('Страниц: ' + res.used) : 'Не используется'; }
            }
        });
    }

    if(modesBox){
        modesBox.addEventListener('click', function(e){
            var btn = e.target.closest ? e.target.closest('.pe-mode') : null;
            if(!btn){ return; }
            applyMode = parseInt(btn.getAttribute('data-mode'), 10) || 0;
            modesBox.querySelectorAll('.pe-mode').forEach(function(b){
                b.classList.toggle('is-active', b === btn);
            });
        });
    }

    fileInput.addEventListener('change', function(){
        if(!fileInput.files || !fileInput.files.length){ return; }
        picked = fileInput.files[0];
        if(label){ label.textContent = picked.name; }
        if(preview && window.URL && window.URL.createObjectURL){
            preview.src = window.URL.createObjectURL(picked);
        }
    });

    saveBtn.addEventListener('click', function(){
        if(!picked){ say('Сначала выберите изображение', false); return; }

        var fd = new FormData();
        fd.append('phpaction', '1');
        fd.append('token', token());
        fd.append('pb_page_default_image_set', '1');
        fd.append('image', picked);
        fd.append('apply_to_pages', String(applyMode));

        NProgress.start();
        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res){
                NProgress.done();
                var ok = res && String(res.status) === '1';
                say((res && res.data) || (ok ? 'Готово' : 'Не удалось сохранить'), ok);
                if(ok){
                    picked = null;
                    fileInput.value = '';
                    if(label){ label.textContent = 'Выбрать изображение'; }
                    if(preview){ preview.src = '..' + res.image + '?t=' + Date.now(); }
                    refresh();
                }
            },
            error: function(){
                NProgress.done();
                say('Ошибка соединения', false);
            }
        });
    });

    resetBtn.addEventListener('click', function(){
        if(!confirm('Вернуть стандартное изображение движка?')){ return; }

        NProgress.start();
        $.ajax({
            type: 'POST',
            url: '../ajax/actions_panel.php',
            data: { phpaction: 1, token: token(), pb_page_default_image_reset: 1 },
            dataType: 'json',
            success: function(res){
                NProgress.done();
                var ok = res && String(res.status) === '1';
                say((res && res.data) || (ok ? 'Готово' : 'Не удалось сбросить'), ok);
                if(ok){
                    picked = null;
                    fileInput.value = '';
                    if(label){ label.textContent = 'Выбрать изображение'; }
                    if(preview){ preview.src = '..' + res.image + '?t=' + Date.now(); }
                    refresh();
                }
            },
            error: function(){
                NProgress.done();
                say('Ошибка соединения', false);
            }
        });
    });

    refresh();
})();

$("#img_form").submit(function (event){
    NProgress.start();
    event.preventDefault();
    var data = new FormData($('#img_form')[0]);
    $.ajax({
        type: "POST",
        url: "../ajax/actions_panel.php",
        data: data,
        contentType: false,
        processData: false,
    }).done(function (html) {
        $("#img_result").empty();
        $("#img_result").append(html);
        $('#img_form')[0].reset();
    });
    NProgress.done();
});

function pbAdminFilterPages(containerId, inputId, filterRootId){
    var root = document.getElementById(containerId);
    var input = document.getElementById(inputId);
    if(!root || !input){ return; }
    var query = (input.value || '').toLowerCase().trim();
    var activeFilter = 'all';
    var filterRoot = filterRootId ? document.getElementById(filterRootId) : null;
    if(filterRoot){
        var activeBtn = filterRoot.querySelector('.pe-chip.is-active');
        if(activeBtn){ activeFilter = activeBtn.getAttribute('data-filter-state') || 'all'; }
    }
    var groups = root.querySelectorAll('.admin-page-group');
    for(var g = 0; g < groups.length; g++){
        var group = groups[g];
        var cards = group.querySelectorAll('.admin-page-card');
        var visibleCount = 0;
        for(var i = 0; i < cards.length; i++){
            var card = cards[i];
            var text = (card.textContent || '').toLowerCase();
            var state = card.getAttribute('data-page-active') || '';
            var matchQuery = (!query || text.indexOf(query) !== -1);
            var matchState = (activeFilter === 'all' || activeFilter === state);
            var visible = (matchQuery && matchState);
            card.style.display = visible ? '' : 'none';
            if(visible){ visibleCount++; }
        }
        group.style.display = visibleCount > 0 ? '' : 'none';
    }
}
function pbAdminBindPageFilters(filterRootId, containerId, inputId){
    var root = document.getElementById(filterRootId);
    if(!root){ return; }
    root.addEventListener('click', function(event){
        var btn = event.target.closest('.pe-chip');
        if(!btn){ return; }
        root.querySelectorAll('.pe-chip').forEach(function(node){ node.classList.remove('is-active'); });
        btn.classList.add('is-active');
        pbAdminFilterPages(containerId, inputId, filterRootId);
    });
}
document.addEventListener('input', function(event){
    if(!event.target){ return; }
    if(event.target.id === 'public_pages_search'){ pbAdminFilterPages('public_pages', 'public_pages_search', 'public_pages_filters'); }
    if(event.target.id === 'service_pages_search'){ pbAdminFilterPages('service_pages', 'service_pages_search', 'service_pages_filters'); }
    if(event.target.id === 'user_pages_search'){ pbAdminFilterPages('user_pages', 'user_pages_search', 'user_pages_filters'); }
});
pbAdminBindPageFilters('public_pages_filters', 'public_pages', 'public_pages_search');
pbAdminBindPageFilters('service_pages_filters', 'service_pages', 'service_pages_search');
pbAdminBindPageFilters('user_pages_filters', 'user_pages', 'user_pages_search');
</script>
