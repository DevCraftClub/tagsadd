<?php

//	===============================
//	Настройки модуля | редактирование
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

$id = intval($_GET['id']);
if(!$id) return;

$row = $db->super_query( "SELECT * FROM " . PREFIX . "_tagsadd WHERE id = '".$id."'" );
$news = $db->super_query( "SELECT * FROM " . PREFIX . "_post WHERE id = '".$row['news_id']."'" );
$user = $db->super_query( "SELECT * FROM " . PREFIX . "_users WHERE user_id = '".$row['user_id']."'" );

echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, $adminlink . '&do=list' => 'Список предложений', '' => "Редактирование предложения") );

if($tagsconf['fast'] == "tags") {
    $alltags = explode(',', $news['tags']);
    $tags = array();
    foreach ($alltags as $tag) {
        $tags[] = "<span class=\"ui teal tag label\">{$tag}</span>";
    }
    $alltags = implode('', $tags);
} elseif ($tagsconf['fast'] == "xfield") {
    $xfields = $news['xfields'];
    $xfields = explode('||', $xfields);
    foreach ($xfields as $xfield) {
        $tag = explode('|', $xfield);
        $tags = array();
        if($tag[0] == $tagsconf['field']) {
            $alltags = explode(',', $tag[1]);
            foreach ($alltags as $tag) {
                $tags[] = "<span class=\"ui teal tag label\">{$tag}</span>";
            }
        }
    }
    $alltags = implode('', $tag);
}

if(count($alltags) > 0) {
    echo <<<HTML
    <div class="ui info tag message">
        <div class="header">
            К новости "{$news['title']}" уже были добавлены следующие теги:
        </div>
        <p>{$alltags}</p>
    </div>
HTML;
}

$itemdate = date( "d.m.Y, H:i", strtotime( $row['date'] ) );

echo "<form class=\"ui form\" method=\"POST\" action=\"{$adminlink}&do=savetag\">";
$output = <<<HTML
<div class="ui piled segments">
    <div class="ui segment">
    <table class="ui table">
        <tbody>
            <tr>
                <td><strong>Теги предложил</strong></td>
                <td>{$user['name']}</td>
            </tr>
            <tr>
                <td><strong>Время</strong></td>
                <td>{$itemdate}</td>
            </tr>
            <tr>
                <td><strong>Предложенные теги</strong></td>
                <td><input id="tags" name="tags" type="text" placeholder="Впишите свои теги" class="chosen" value="{$row['tags']}" required></td>
            </tr>
            <tr>
                <td><strong>Что делаем с тегами?</strong></td>
                <td>
                    <div class="ui selection dropdown">
                        <input type="hidden" name="todo" id="todo">
                        <i class="dropdown icon"></i>
                        <div class="default text">Действие</div>
                        <div class="menu">
                            <div class="item" data-value="add">Подтверждаем и сохраняем в новость</div>
                            <div class="item" data-value="delete">Удаляем</div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    </div>
</div>
<input type="hidden" name="tagid" id="tagid" value="{$id}">
<script>
    $('#todo').on('change', function(){
		if($(this).val() == "delete"){
			var html = "<tr class=\"delete\"><td>Причина отказа</td><td><div class=\"field\"><textarea rows=\"2\" id=\"delreason\" name=\"delreason\"></textarea></div></td></tr>";
			$('.table tbody').append(html);
		} else {
			$('.table tbody').find('.delete').first().remove();
		}
    });
</script>
HTML;
print $output;
saveButton();
echo "</form>";