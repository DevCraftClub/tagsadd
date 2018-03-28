<?php

//	===============================
//	Настройки модуля | главная
//	===============================
//	Автор: Maxim Harder
//	Сайт: https://maxim-harder.de
//	Телеграм: http://t.me/MaHarder
//	===============================
//	Ничего не менять
//	===============================

echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array('' => $name) );

echo <<<HTML
<div class="ui centered cards">
	<div class="ui card">
		<div class="content">
			<div class="header">Список</div>
		</div>
		<div class="content">
			<h4 class="ui sub header">Все теги списком</h4>
			<div class="ui small feed">
				<div class="event">
					<div class="content">
						<div class="summary">
							<i class="list alternate outline icon"></i> Показывает все теги
						</div>
					</div>
				</div>
				<div class="event">
					<div class="content">
						<div class="summary">
							<i class="list alternate outline icon"></i> Возможность редактировать теги
						</div>
					</div>
				</div>
				<div class="event">
					<div class="content">
						<div class="summary">
							<i class="list alternate outline icon"></i> Возможность добавлять теги сразу в новость
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="extra content">
			<a href="{$adminlink}&do=list" class="ui button">перейти</a>
		</div>
	</div>
	<div class="ui card">
		<div class="content">
			<div class="header">Настройки</div>
		</div>
		<div class="content">
			<h4 class="ui sub header">Все настройки модуля</h4>
			<div class="ui small feed">
				<div class="event">
					<div class="content">
						<div class="summary">
							<i class="cog icon"></i> Основные настройки
						</div>
					</div>
				</div>
				<div class="event">
					<div class="content">
						<div class="summary">
							<i class="cog icon"></i> Настройки списка
						</div>
					</div>
				</div>
				<div class="event">
					<div class="content">
						<div class="summary">
							<i class="cog icon"></i> Шаблоны писем
						</div>
					</div>
				</div>
				<div class="event">
					<div class="content">
						<div class="summary">
							<i class="cog icon"></i> Версия модуля и изменения в версиях
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="extra content">
			<a href="{$adminlink}&do=settings" class="ui button">перейти</a>
		</div>
	</div>
</div>
HTML;

?>