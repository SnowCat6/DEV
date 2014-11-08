<? function  user_registerPage($db, $val, $data){ ?>
<? if (!access('register', '')) return; ?>
<link rel="stylesheet" type="text/css" href="css/userLogin.css">
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
{{page:title=Регистрация на сайте}}
{push}
{{user:registerForm}}
{pop:registerForm}
{{display:message}}
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top">{{display:registerForm}}</td>
    <td valign="top">Регистрация на сайте дает Вам мнгого дополнительных удобств</td>
  </tr>
</table>
<? } ?>
