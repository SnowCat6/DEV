<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? module("page:style", 'baseStyle.css') ?>
<? module("page:style", 'cmsStyle.css') ?>
<? ob_start(); ?>
</head>

<body>
<? ob_start(); ?>
<div class="header">
  <div style="float:right">
	<? module("user:loginForm"); ?>
</div>
<div class="menu horizontal"><? $module_data = array(); $module_data["prop"]["place"] = "topMenu"; moduleEx("doc:read:menu", $module_data); ?></div>
<div class="clear"></div>
</div>

<div class="body shadow">
<? module("display"); ?>
</div>

<div class="copyright"><? module("read:copyright"); ?></div>
</body>
</html>
<? $p = ob_get_clean(); module("admin:toolbar"); echo $p; ?><? $p = ob_get_clean(); module("page:header"); echo $p; ?>