<? function user_lostPage($db, $val, $data){ ?>
<? module("page:style", 'baseStyle.css') ?>
<? $module_data = array(); $module_data[] = "Восстановление пароля"; moduleEx("page:title", $module_data); ?>
<? ob_start() ?>
<? module("user:lostForm"); ?>
<? module("page:display:lostForm", ob_get_clean()) ?>
<? module("display:message"); ?>
<? module("display:lostForm"); ?>
<? } ?>
