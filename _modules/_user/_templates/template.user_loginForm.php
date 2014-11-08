<?
function user_loginForm($db, $val, $data){
	$login = getValue('login');
?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
<link rel="stylesheet" type="text/css" href="css/userLogin.css">
{{script:ajaxLink}}
<? if (!defined('userID')){ ?>
<form method="post" action="{{getURL:user_login}}" class="userLoginForm">

<div class="loginHolder">
    <div class="loginPlace"><input name="login[login]" type="text" class="input w100" placeholder="Логин" title="Логин" value="{$login[login]}" /></div>
    <div class="passwPlace"><input name="login[passw]" type="password" class="input password w100" placeholder="Пароль" title="Пароль" value="{$login[passw]}" /></div>
	<div class="loginButton"> <input type="submit" value="OK"/></div>
</div>

<div class="loginOptions">
<? if ($val){?>
<div>
    <label>
        <input type="checkbox" name="login[remember]" class="checkbox" id="loginRemember" value="1" {checked:$login[remember]} />
        Помнить
    </label>
</div>
<? } ?>
<? if (access('register', '')){ ?>
	<div><a href="{{getURL:user_register}}" id="ajax">Регистрация</a></div>
<? } ?>
<? if ($val){ ?>
    <div><a href="{{getURL:user_lost}}" id="ajax">Напомнить пароль?</a></div>
<? } ?>
</div>
</form>
<? }else{ ?>
<div class="userLogout">
    <a href="{{getURL=logout}}">Выход</a>
</div>
<? } ?>
<? } ?>