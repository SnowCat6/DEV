<? function  user_registerPage($db, $val, $data){ ?><? if (!access('register', '')) return; ?><? module("page:style", 'baseStyle.css') ?><? $module_data = array(); $module_data[] = "Регистрация на сайте"; moduleEx("page:title", $module_data); ?><? ob_start() ?><? module("user:registerForm"); ?><? module("page:display:registerForm", ob_get_clean()) ?><? module("display:message"); ?>
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top"><? module("display:registerForm"); ?></td>
    <td valign="top">Регистрация на сайте дает Вам мнгого дополнительных удобств</td>
  </tr>
</table>
<? } ?>
