<?
function user_registerForm()
{
	if (!access('register', '')) return;
	
	if (testValue('register')){
		if (module('user:register', getValue('register'))) return user_registred();
	}
	module('script:ajaxForm');
	module('script:ajaxLink');
?><? module("page:style", 'baseStyle.css') ?>
<form method="post" action="<? module("getURL:user_register"); ?>" class="form login ajaxForm ajaxReload">
<table border="0" cellspacing="0" cellpadding="2" width="300">
<tr>
    <th nowrap="nowrap">Логин:</th>
    <td width="100%"><input name="register[login]" value="<? if(isset($_POST["register"]["login"])) echo htmlspecialchars($_POST["register"]["login"]) ?>" type="text" class="input w100" /></td>
</tr>
<tr>
    <th nowrap="nowrap">Пароль:</th>
    <td width="100%"><input name="register[passw]" type="password" value="<? if(isset($_POST["register"]["passw"])) echo htmlspecialchars($_POST["register"]["passw"]) ?>" class="input w100" /></td>
</tr>
<tr>
    <td colspan="2"><input type="submit" value="Зарегистрироваться" class="button w100" /></td>
</tr>
<tr>
  <td colspan="2">
<div><? module("loginza:enter"); ?></div>
<div><a href="<? module("getURL:user_lost"); ?>" id="ajax">Напомнить пароль?</a></div>
  </td>
</tr>
</table>
</form>
<? } ?><? function user_registred(){ ?><? $module_data = array(); $module_data[] = "Вы зарегистрированы на сайте."; moduleEx("message", $module_data); ?><? } ?>