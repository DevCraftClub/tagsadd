<?php

//	===============================
//	Настройки модуля | список
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

$list = $tagsconf['listcount'] ? $tagsconf['listcount'] : $config['news_number'];
$limit = $tagsconf['limit'] ? $tagsconf['limit'] : 5;

if(!intval($_GET['page']) || $_GET['page'] == 1) {
	$steps = intval($list);
	$start = 0;
} else {
	$steps = $_GET['page']*intval($list);
	$start = (intval($_GET['page'])-1)*$list;
}
$sort = $tagsconf['listsort'] ? $tagsconf['listsort'] : "ASC";
$order = $tagsconf['listsort2'] ? $tagsconf['listsort2'] : "id";

$webseite = $config['http_home_url'].$config['admin_path'];

$spisok = $db->query( "SELECT * FROM " . PREFIX . "_tagsadd ORDER BY ".$order." ".$sort." LIMIT $start,$steps " );
$result_count = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_tagsadd" );

if($result_count == 0)
	echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, '' => 'Список предложений') );

$liste = array();
$liste[] = "<table class=\"ui celled striped selectable table customized\"><thead><tr><th>ID</th><th>Название новости</th><th>Пользователь</th><th>Добавлено</th><th>Теги</th><th>Действия</th></tr></thead><tbody>";

if($result_count > 0) {
	while ( $row = $db->get_row( $spisok ) ) {

		$news = $db->super_query( "SELECT * FROM " . PREFIX . "_post WHERE id = ".$row['news_id']."" );
		if($row['user_id'] == 0) {
		    $user = array();
		    $user = [
		        'id' => 0,
		        'name' => "Гость",
            ];
        } else
		    $user = $db->super_query( "SELECT * FROM " . PREFIX . "_users WHERE user_id = ".$row['user_id']."" );
		$id = intval($row['id']);

		if( $langformatdate ) {
			$itemdate = date( $langformatdate, strtotime( $row['date'] ) );
		} else {
			$itemdate = date( "d.m.Y", strtotime( $row['date'] ) );
		}
		$title = $news['title'];

		$title = htmlspecialchars( stripslashes( $title ), ENT_QUOTES, $config['charset'] );
		$title = str_replace("&amp;","&", $title );

		$editnews = "<a href=\"{$webseite}?mod=editnews&action=editnews&id={$news['id']}\">{$title}</a>";
		if($user['id'] == 0)
		    $edituser = $user['name'];
		else
		    $edituser = "<a href=\"{$webseite}?mod=editusers&action=edituser&id={$user['user_id']}\">{$user['name']}</a>";

		if( $config['allow_alt_url'] ) {
			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				if( intval( $news['category'] ) and $config['seo_type'] == 2 ) {
					$full_link = $config['http_home_url'] . get_url( intval( $news['category'] ) ) . "/" . $news['id'] . "-" . $news['alt_name'] . ".html";
				} else {
					$full_link = $config['http_home_url'] . $news['id'] . "-" . $news['alt_name'] . ".html";
				}
			} else {
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', strtotime( $news['date'] ) ) . $news['alt_name'] . ".html";
			}
		} else {
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $news['id'];
		}
		$newsSite = "<a href=\"{$full_link}\" target=\"_blank\" alt=\"Откроет новость на сайте\" title=\"Откроет новость на сайте\">Открыть на сайте</a>";

		$dropdown = array();
		$dropdown[] = "<div class=\"ui fluid selection dropdown\"><i class=\"dropdown icon\"></i><div class=\"text\">Действие</div><div class=\"menu\">";
		$dropdown[] = "<div class=\"item\"><a href=\"{$adminlink}&do=edittag&id={$id}\" alt=\"На отдельной странице вы сможете увидеть уже добавленные теги в новость, отредактировать и одобрить предложенные, а так-же отказать в добавлении и удалить\" title=\"На отдельной странице вы сможете увидеть уже добавленные теги в новость, отредактировать и одобрить предложенные, а так-же отказать в добавлении и удалить\">Отредактировать</a></div>";
		$dropdown[] = "<div class=\"item\">{$newsSite}</div>";
		$dropdown[] = "<div class=\"item\"><a href=\"#\" class=\"delete_tag\" data-id=\"{$id}\" alt=\"В модальном окне указывает причину в отказе и удаляете предложенные теги\" title=\"В модальном окне указывает причину в отказе и удаляете предложенные теги\">Отказать и удалить</a></div>";
		$dropdown[] = "<div class=\"item\"><a href=\"{$adminlink}&do=inserttag&id={$id}\" alt=\"Вставляет предложенные теги без редактирования в саму новость\" title=\"Вставляет предложенные теги без редактирования в саму новость\">Вставить в новость</a></div>";
		$dropdown[] = "</div></div>";

		$output = "<tr>";
		$output .= "<td class=\"ui center aligned header\"> #" . $id ."</td>";
		$output .= "<td>" . $editnews . "</td>";
		$output .= "<td>".$edituser. "</td>";
		$output .= "<td>".$itemdate. "</td>";
		$output .= "<td>".$row['tags'] . "</td>";
		$output .= "<td>".implode('', $dropdown)."</td>";
		$output .= "</tr>";

		$liste[] = $output;
	}
} else {
	$liste[] = "<tr><td>##</td><td colspan=\"5\">Пока нет новых предложений!</td></tr>";
}

$liste[] = "</tbody><tfoot><tr><th colspan=\"3\"><div class=\"ui left\">Всего: {$result_count['count']} предложений</div></th><th colspan=\"3\">";
if($result_count['count'] > $list) {
	if(!$_GET['page']) $nowPage = 1;
	else $nowPage = $_GET['page'];

	echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, $adminlink . '&do=list' => 'Список предложений', '' => "Страница ".$nowPage) );

	$pageNav = new pagination();
	$liste[] = $pageNav->getLinks( $result_count['count'], $list, $start, $limit, 'page' );

	$liste[] = $next_link."</div>";
} else {
	echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, '' => 'Список предложений') );
}
$liste[] = "</th></tr></tfoot></table>";
$liste[] = <<<HTML
<div class="ui tiny modal deleteMod" style="height: 255px">
  <i class="close icon"></i>
  <div class="header">
    Причина отказа
  </div>
  <div class="image content">
    <div class="image">
      <i class="trash alternate icon"></i>
    </div>
    <div class="description" style="min-width: 85%;">
        <form class="ui form" method="POST" action="{$adminlink}&do=delete">
          <div class="field">
            <textarea rows="2" id="delreason" name="delreason" placeholder="Причина удаления / отказа"></textarea>
          </div>
          <input name="tagidM" id="tagidM" type="hidden">
          <button class="ui red button" type="submit"><i class="trash alternate icon"></i> Удалить</button>
          <a href="#" class="ui grey button cancel">Закрыть  <i class="window close outline icon"></i></a>
      </form>
    </div>
  </div>
</div>

<script>
    $('.delete_tag').on('click', function() {
        var tagid = $(this).data('id');
        $('#tagidM').val(tagid);
        $('.deleteMod').modal({centered: false, blurring: true}).modal('show');
    });
    $('.deleteMod .cancel').on('click', function() {
        $('.deleteMod').modal('hide');
    });
</script>
<style>
    .deleteMod {
        height: 255px !important;
        margin-top: 20% !important;
    }
    .cancel .icon{
        margin-left: 7px !important;
        margin-right: 0 !important;
        color: white;
    }
</style>
HTML;


echo implode("", $liste);
?>