<? function user_page($db, $id, $data){ ?>
<h1>Добро пожаловать на сайт {{user:name}}.</h1>
<a href="{{getURL=logout}}" class="button">Выйти</a> <a href="{{url:user_edit_$id}}" id="ajax">Изменить свои данные</a>
<? event('user.page', $id)?>
<? } ?>
