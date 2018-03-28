<?php

//	===============================
//	Исполнительный файл
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

if( !defined( 'DATALIFEENGINE' ) ) die( "Oh! You little bastard!" );

@include (ENGINE_DIR . '/data/tagsadd.php');

if($tagsconf['onof']){
    $id = intval($newsid);
    if(!$id) return;
    if(!$focus) return;

    $full = $full ? $full : "fullstory";
    $nameN = $nameN ? $nameN : "tagsadd";
    $tempUser = $_COOKIE['dle_user_id'] ? $_COOKIE['dle_user_id'] : 0;

    $is_change = false;
    if($config['allow_cache'] != '1') {
        $config['allow_cache'] = '1';
        $is_change = true;
    }

    switch ($focus) {
        case 'button':
            //Обрабтока кнопки

                $tpl_button = new dle_template();
                $tpl_button->dir = TEMPLATE_DIR;
                $tpl_button->load_template('modules/tagsadd/button.tpl');
                $tpl_button->set('{button}', $tagsconf['button']);
                $tpl_button->set('{name}', $nameN);
                $tpl_button->compile('button');
                $tpl_button->clear();
                $button = $tpl_button->result['button'];
                unset($tpl_button);

                echo $button;
            break;

        case 'modal':
            //Обработка модального окна
                $tpl_modal = new dle_template();
                $tpl_modal->dir = TEMPLATE_DIR;
                $tpl_modal->load_template('modules/tagsadd/modal.tpl');
                $tpl_modal->set('{news-id}', $id);
                $tpl_modal->set('{name}', $nameN);
                $tpl_modal->set('{user-id}', $tempUser);
                $tpl_modal->set('{AJAX}', "{$config['http_home_url']}engine/ajax");
                $tpl_modal->compile('modal');
                $tpl_modal->clear();
                $modal = $tpl_modal->result['modal'];
                unset($tpl_modal);

                echo $modal;
            break;

        case 'js':
            //Обработка скриптов
                $tpl_js = new dle_template();
                $tpl_js->dir = TEMPLATE_DIR;
                $tpl_js->load_template('modules/tagsadd/js.tpl');
                $tpl_js->set('{name}', $nameN);
                $tpl_js->set('{THEME}', "{$config['http_home_url']}templates/{$config['skin']}");
                $tpl_js->set('{AJAX}', "{$config['http_home_url']}engine/ajax");
                $tpl_js->compile('js');
                $tpl_js->clear();
                $js = $tpl_js->result['js'];
                unset($tpl_js);

                echo $js;
            break;
    }
} else return;