<div class="page">

    <div class="dc-wrap">
        <div class="dc-col">
            <div class="dc-card">
                <div class="dc-card__head">
                    <div class="dc-card__head-main">
                        <span class="dc-card__icon dc-card__icon--blue"><span class="glyphicon glyphicon-pencil"></span></span>
                        <div class="dc-card__titles">
                            <div class="dc-card__title">Параметры страницы</div>
                            <div class="dc-card__subtitle">Адрес, мета-теги и режим доступа</div>
                        </div>
                    </div>
                    <span class="dc-card__badge">ID {id}</span>
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
                                    <option value="2" {if('{privacy}' == '2')} selected {/if}>Всем пользователям</option>
                                    <option value="1" {if('{privacy}' == '1')} selected {/if}>Только авторизованным</option>
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
                                    <option value="1" {if('{robots}' == '1')} selected {/if}>Индексируется</option>
                                    <option value="2" {if('{robots}' == '2')} selected {/if}>Не индексируется</option>
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
                                    <option value="1" {if('{active}' == '1')} selected {/if}>Включить</option>
                                    <option value="2" {if('{active}' == '2')} selected {/if}>Выключить</option>
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
                                <input type="text" id="input_url" maxlength="30" autocomplete="off" value="{url}">
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
                                <input type="text" id="input_title" maxlength="80" autocomplete="off" value="{title}">
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
                                <input type="text" id="input_description" maxlength="150" autocomplete="off" value="{description}">
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
                                <input type="text" id="input_keywords" maxlength="150" autocomplete="off" value="{keywords}">
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
                            <img id="img" src="../{image}" alt="">
                        </div>
                        <div class="pe-image__side">
                            <div class="dc-hint" style="margin-bottom:10px;">Загружайте изображения с небольшим весом — это ускоряет сайт.</div>
                            <form enctype="multipart/form-data" action="ajax/actions_panel.php" method="POST" id="img_form">
                                <input type="hidden" name="token" value="{token}">
                                <input type="hidden" name="load_page_image_2" value="1">
                                <input type="hidden" name="phpaction" value="1">
                                <input type="hidden" name="id" value="{id}">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dc-card" style="margin-top:14px;">
        <div class="dc-card__head">
            <div class="dc-card__head-main">
                <span class="dc-card__icon dc-card__icon--green"><span class="glyphicon glyphicon-edit"></span></span>
                <div class="dc-card__titles">
                    <div class="dc-card__title">Содержимое страницы</div>
                    <div class="dc-card__subtitle">Текст, изображения и разметка</div>
                </div>
            </div>
        </div>
        <div class="dc-card__body">
            <div class="pe-editor">
                <textarea name="input_content" id="input_content" rows="10" cols="80">{content}</textarea>
            </div>

            <div class="dc-version-actions" style="margin-top:14px;">
                <button onclick="page_edit({id});" type="button" class="btn btn-primary" style="border-radius:8px;font-weight:700;">
                    <span class="glyphicon glyphicon-ok"></span> Сохранить изменения
                </button>
                <a href="{site_host}admin/page_editor" class="btn btn-default" style="border-radius:8px;font-weight:700;">
                    <span class="glyphicon glyphicon-arrow-left"></span> К списку страниц
                </a>
            </div>
            <div id="edit_page_result" class="dc-result"></div>
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
    function initEditor(){
        if(typeof CKEDITOR === 'undefined'){ return; }
        if(CKEDITOR.instances.input_content){ return; }

        var dark = document.body.getAttribute('data-admin-theme') === 'dark';
        CKEDITOR.replace('input_content', {
            height: 460,
            removePlugins: 'elementspath',
            resize_enabled: true,
            contentsCss: dark
                ? ['{site_host}modules/editors/ckeditor/contents.css', '{site_host}templates/admin/css/ckeditor-dark.css?v={cache}']
                : ['{site_host}modules/editors/ckeditor/contents.css']
        });
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', initEditor);
    } else {
        initEditor();
    }
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
</script>
