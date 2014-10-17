<? function user_loginPage(){
	m('script:ajaxLink');
	m('user:enter', getValue('login'));
?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
{{page:title=Вход на сайт}}

<? if ($id = userID()){ ?>
{{user:page:$id}}
<? return; }; ?>

{push}
{{user:loginForm:page}}
{pop:loginForm}
{{display:message}}

<? if (!module('loginza:check')){ ?>
<h2>Введите логин и пароль</h2>
{{display:loginForm}}
<? }else{ ?>
<table border="0" cellspacing="0" cellpadding="5">
<tr>
    <td valign="top">
<h2>Введите логин и пароль</h2>
{{display:loginForm}}
    </td>
    <td valign="top"><h2>или</h2></td>
    <td valign="top">
<h2>Войти через OpenID</h2>
{{loginza:page}}
    </td>
</tr>
</table>
<? } ?>
<? } ?>
