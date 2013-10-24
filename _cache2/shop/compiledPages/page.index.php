<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? module("page:style", 'baseStyle.css') ?><? module("page:style", 'style.css') ?><? ob_start(); ?>
</head>

<body>
<div class="body">
    <? ob_start(); ?>
    <div class="header">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td valign="top"><a href="<? module("getURL"); ?>" title="Интернет Магазин Мир электроники"><img src="/design/logo.gif" alt="logo" width="209" height="48" hspace="0" vspace="30" border="0" /></a></td>
            <td width="100%" valign="middle" class="topAdv"><? $module_data = array(); $module_data[] = "bottom"; moduleEx("read:topAdv", $module_data); ?></td>
            <td><? module("bask:compact"); ?></td>
        </tr>
        </table>
        <div class="menuTop"><? $module_data = array(); $module_data["prop"]["!place"] = "topMenu"; moduleEx("doc:read:menuTable", $module_data); ?></div><br />
    </div>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="body2">
  <tr>
    <td valign="top" class="panel left">
<div class="menu index2"><? $module_data = array(); $module_data["prop"]["!place"] = "mainCatalog"; $module_data["type"] = "catalog,page"; moduleEx("doc:read:menu2", $module_data); ?></div>
<div><? $module_data = array(); $module_data["prop"]["!place"] = "advLeft"; moduleEx("doc:read:scroll2", $module_data); ?></div>
<img src="/design/spacer.gif" width="200" height="1" />
    </td>
    <td width="100%" valign="top">
    <? module("advRoller"); ?><? module("doc:searchPage:product:catalog"); ?><? $module_data = array(); $module_data["prop"]["!place"] = "indexHot"; moduleEx("doc:read:scroll", $module_data); ?><? module("doc:page:index"); ?><? $module_data = array(); $module_data["prop"]["!place"] = "indexHotBottom"; moduleEx("doc:read:scroll", $module_data); ?>
	</td>
  </tr>
</table>
<div class="copyright"><? module("read:copyright"); ?></div>
</div>
</body>
</html>
<? $p = ob_get_clean(); module("admin:toolbar"); echo $p; ?><? $p = ob_get_clean(); module("page:header"); echo $p; ?>