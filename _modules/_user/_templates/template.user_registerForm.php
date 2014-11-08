<?
function user_registerForm()
{
	if (!access('register', '')) return;
	
	if (testValue('register')){
		if (module('user:register', getValue('register'))) return user_registred();
	}
	module('script:ajaxForm');
	module('script:ajaxLink');
?>
<link rel="stylesheet" type="text/css" href="css/userLogin.css">
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
<form method="post" action="{{getURL:user_register}}" class="form login ajaxForm ajaxReload">
<table border="0" cellspacing="0" cellpadding="2" width="300">
<tr>
    <th nowrap="nowrap">Логин:</th>
    <td width="100%"><input name="register[login]" value="{$_POST[register][login]}" type="text" class="input w100" /></td>
</tr>
<tr>
    <th nowrap="nowrap">Пароль:</th>
    <td width="100%"><input name="register[passw]" type="password" value="{$_POST[register][passw]}" class="input w100" /></td>
</tr>
<tr>
    <td colspan="2"><input type="submit" value="Зарегистрироваться" class="button w100" /></td>
</tr>
<tr>
  <td colspan="2">
<div>{{loginza:enter}}</div>
<div><a href="{{getURL:user_lost}}" id="ajax">Напомнить пароль?</a></div>
  </td>
</tr>
</table>
</form>
<? } ?>
<? function user_registred(){ ?>
{{message=Вы зарегистрированы на сайте.}}
<? } ?>