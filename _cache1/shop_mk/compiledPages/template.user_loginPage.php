<? function user_loginPage(){
	m('script:ajaxLink');
?>
<? module("page:style", 'baseStyle.css') ?>
<? $module_data = array(); $module_data[] = "Вход на сайт"; moduleEx("page:title", $module_data); ?>
<? if ($id = userID()){ ?>
<? module("user:page:$id"); ?>
<? }else{ ?>
<? ob_start() ?>
<? module("user:loginForm:page"); ?>
<? module("page:display:loginForm", ob_get_clean()) ?>
<? module("display:message"); ?>
<table border="0" cellspacing="0" cellpadding="5">
<tr>
    <td valign="top">
<h2>Введите логин и пароль</h2>
<? module("display:loginForm"); ?>
    </td>
<? if (module('loginza:check')){ ?>
    <td valign="top"><h2>или</h2></td>
    <td valign="top">
<h2>Войти через OpenID</h2>
<? module("loginza:page"); ?>
    </td>
<? } ?>
</tr>
</table>
<? } ?>
<? } ?>
