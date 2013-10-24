<? function user_page($db, $id, $data){ ?>
<h1>Добро пожаловать на сайт <? module("user:name"); ?>.</h1>
<a href="<? $module_data = array(); $module_data[] = "logout"; moduleEx("getURL", $module_data); ?>" class="button">Выйти</a> <a href="<? module("url:user_edit_$id"); ?>" id="ajax">Изменить свои данные</a>
<? event('user.page', $id)?><? } ?>