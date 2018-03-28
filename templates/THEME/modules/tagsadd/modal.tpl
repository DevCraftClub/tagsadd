<div class="tagsadd_form tagsadd_modal" id="{name}_modal">
    <span class="tagsadd_close">закрыть</span>
    <form method="POST" id="{name}_form" action="{AJAX}/maharder/tagsadd.php">
        <div class="box_in">
            <h4 class="title h1">Предлагаемые теги</h4>
            <div class="addform">
                <ul class="ui-form">
                    <li class="form-group">
                        <label>Ваши теги</label>
                        <input placeholder="Предлагаемые теги" type="text" name="newtags" id="newtags" class="wide" required>
                    </li>
                </ul>
                <div class="form_submit">
                    <button type="submit" class="btn btn-big" name="send_tags"><b>Отправить</b></button>
                </div>
            </div>
        </div>
        <input name="newsid" id="newsid" type="hidden" value="{news-id}">
        <input name="userid" id="userid" type="hidden" value="{user-id}">
    </form>

</div>
<div class="tagsadd_overlay"></div>