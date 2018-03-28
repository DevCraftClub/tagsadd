<?php

//	===============================
//	Настройки модуля | сохраняем
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array($adminlink => $name, $adminlink . '&do=list' => 'Список предложений', '' => "Сохранение предложения") );

$id = intval($_POST['tagid']);
if(!$id) return;
$doAction = $_POST['todo'];
if($doAction) {

    $row = $db->super_query("SELECT * FROM " . PREFIX . "_tagsadd WHERE id = '" . $id . "'");
    $news = $db->super_query("SELECT * FROM " . PREFIX . "_post WHERE id = '" . $row['news_id'] . "'");
    $title = $news['title'];
    $title = htmlspecialchars(stripslashes($title), ENT_QUOTES, $config['charset']);
    $title = str_replace("&amp;", "&", $title);
    if ($config['allow_alt_url']) {
        if ($config['seo_type'] == 1 OR $config['seo_type'] == 2) {
            if (intval($news['category']) and $config['seo_type'] == 2) {
                $full_link = $config['http_home_url'] . get_url(intval($news['category'])) . "/" . $news['id'] . "-" . $news['alt_name'] . ".html";
            } else {
                $full_link = $config['http_home_url'] . $news['id'] . "-" . $news['alt_name'] . ".html";
            }
        } else {
            $full_link = $config['http_home_url'] . date('Y/m/d/', strtotime($news['date'])) . $news['alt_name'] . ".html";
        }
    } else {
        $full_link = $config['http_home_url'] . "index.php?newsid=" . $news['id'];
    }

    if ($row['user_id'] > 0) {
        $user = $db->super_query("SELECT * FROM " . PREFIX . "_users WHERE user_id = '" . $row['user_id'] . "'");

        include_once ENGINE_DIR . '/classes/parse.class.php';
        $parse = new ParseFilter();

        $from = $member_id['name'];
        $to = $user['user_id'];
        $folder = 'inbox';
        $subject = '';
        $message = '';
        $time = time();

        $userfields = $user['xfields'];
        $userfields = explode("||", $userfields);
        $param = '';
        foreach ($userfields as $fields) {
            $tempField = explode('|', $fields);
            if ($tempField[0] == $tagsconf['userinform']) {
                $param = $tempField[1];
                break;
            }
        }

        switch ($doAction) {
            case 'add':
                if ($param != 'none') {
                    if ($param == 'all' || $param == 'onadd') {
                        $subject = str_replace('%title%', $news['title'], $tagsconf['usermailtitle2']);
                        $subject = str_replace('%user%', $user['name'], $subject);

                        $message = str_replace('%title%', $news['title'], $tagsconf['usermail2']);
                        $message = str_replace('%user%', $user['name'], $message);
                        $message = str_replace('%link%', $full_link, $message);
                        $message = str_replace('%tags%', $row['tags'], $message);
                        $message = $parse->BB_Parse($message, false);
                    }
                }
                $tags = array();
                if ($tagsconf['fast'] == "tags") {
                    $alltags = explode(',', $news['tags']);
                    foreach ($alltags as $tag) {
                        $tags[] = $tag;
                    }
                } elseif ($tagsconf['fast'] == "xfield") {
                    $xfields = $news['xfields'];
                    $xfields = explode('||', $xfields);
                    foreach ($xfields as $xfield) {
                        $tag = explode('|', $xfield);
                        if ($tag[0] == $tagsconf['field']) {
                            $alltags = explode(',', $tag[1]);
                            foreach ($alltags as $tag) {
                                $tags[] = $tag;
                            }
                        }
                    }
                }
                $sugTags = explode(',', $_POST['tags']);
                foreach ($sugTags as $newTag) {
                    $tags[] = $newTag;
                }

                $newTags = implode(',', $tags);

                if ($tagsconf['fast'] == "tags") {
                    $db->query("UPDATE " . PREFIX . "_post SET tags = '{$newTags}' WHERE id = '{$row['news_id']}'");
                    foreach ($tags as $tag) {
                        if($tag == '') continue;
                        $tagC = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_tags WHERE news_id = '{$row['news_id']}' AND tag = '{$tag}'");
                        if ($tagC['count'] == 0)
                            $db->query("INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES ({$row['news_id']}, '{$tag}')");
                    }
                } elseif ($tagsconf['fast'] == "xfield") {
                    $xfields = $news['xfields'];
                    $xfields = explode('||', $xfields);
                    $allXF = array();
                    foreach ($xfields as $xfield) {
                        $xf = explode('|', $xfield);
                        if ($xf[0] == $tagsconf['field'])
                            $allXF[] = $xf[0] . "|" . $newTags;
                        else
                            $allXF[] = $xf[0] . "|" . $xf[0];
                    }
                    unset($xfields);
                    $xfields = implode('||', $allXF);
                    $db->query("UPDATE " . PREFIX . "_post SET xfields = '{$xfields}' WHERE id = '{$row['news_id']}'");
                    if ($tagsconf['xflink']) {
                        foreach ($sugTags as $tag)
                            if($tag == '') continue;
                            $tagC = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_xfsearch WHERE news_id = '{$row['news_id']}' AND tagname = '{$tagsconf['field']}' AND tagvalue = '{$tag}'");
                            if ($tagC['count'] == 0)
                                $db->query("INSERT INTO " . PREFIX . "_xfsearch (news_id, tagname, tagvalue) VALUES ({$row['news_id']}, '{$tagsconf['field']}', '{$tag}')");
                    }
                }
                break;

            case 'delete':
                if ($param != 'none') {
                    if ($param == 'all' || $param == 'ondel') {
                        $subject = str_replace('%title%', $news['title'], $tagsconf['usermailtitle3']);
                        $subject = str_replace('%user%', $user['name'], $subject);

                        $message = str_replace('%title%', $news['title'], $tagsconf['usermail3']);
                        $message = str_replace('%user%', $user['name'], $message);
                        $message = str_replace('%link%', $full_link, $message);
                        $message = str_replace('%tags%', $row['tags'], $message);
                        $message = str_replace('%reason%', $_POST['delreason'], $message);
                        $message = $parse->BB_Parse($message, false);
                    }
                }
                break;
        }

        if ($param != 'none') $db->query("INSERT INTO " . PREFIX . "_pm (subj, text, user, user_from, date, folder) VALUES ('{$subject}', '{$message}', {$row['user_id']}, '{$from}', {$time}, '{$folder}')");
        if ($param != 'none') $db->query("UPDATE " . PREFIX . "_users SET pm_all = pm_all+1, pm_unread=pm_unread+1 WHERE user_id = '{$row['user_id']}'");

    }
    messageOut("Изменения сохранены", "Теперь вы можете выбрать следующие опции.", array($adminlink . '&do=settings' => "Настройки", $adminlink . '&do=list' => "Список новостей", $adminlink => "Главная", $full_link => "Открыть на сайте", $adminlink . '&do=edittag&id='.$id => "Вернуться к редактированию", $_SERVER['PHP_SELF'] .'?mod=editnews&action=editnews&id='.$row['news_id'] => "Редактировать новость"));
    $db->query("DELETE FROM " . PREFIX . "_tagsadd WHERE id = '{$id}'");
} else
        messageOut("Ошибка", "Видать что-то не заполнили. Либо <a href=\"#\" onclick=\"window.history.back();return false;\">вернуться назад</a>, либо ...", array($adminlink . '&do=settings' => "Настройки", $adminlink . '&do=list' => "Список новостей", $adminlink => "Главная",));
