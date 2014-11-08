<? function user_page($db, $id, $data){ ?>
<link rel="stylesheet" type="text/css" href="css/userLogin.css">

<div class="userPage">
    <h1>Добро пожаловать на сайт {{user:name}}.</h1>
    <div class="userPageOptions">
        <a href="{{getURL=logout}}">Выйти</a>
        <a href="{{url:user_edit_$id}}" id="ajax">Изменить свои данные</a>
    </div>
    <? event('user.page', $id)?>
</div>
<? } ?>
