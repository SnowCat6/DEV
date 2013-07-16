<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? module("page:style", 'baseStyle.css') ?>
<? module("page:style", 'style.css') ?>

<? ob_start(); ?>
</head>

<body>
<div class="body">
<? ob_start(); ?><br />
<div class="padding">
<div><? $module_data = array(); $module_data["prop"]["!place"] = "topMenu"; moduleEx("doc:read:menuTable", $module_data); ?></div>
<br />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
	<td width="100%" valign="top">
<? module("doc:searchPage"); ?>
<br />
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%" valign="top">
<h2><a href="<? module("getURL:calendar"); ?>">Ближайшие события</a><? module("doc:admin:add:calendar:article"); ?></h2>
<? $module_data = array(); $module_data["parent"] = "calendar"; $module_data["max"] = "5"; moduleEx("doc:read:any", $module_data); ?>
    </td>
    <td valign="top">&nbsp;</td>
    <td width="50%" valign="top">
<h2><a href="<? module("getURL:news"); ?>">НОВОСТИ</a><? module("doc:admin:add:news:article"); ?></h2>
<? $module_data = array(); $module_data["parent"] = "news"; $module_data["max"] = "5"; moduleEx("doc:read:any", $module_data); ?>
    </td>
  </tr>
</table>
</td>
    <td valign="top" width="300">
<? module("doc:searchPerson"); ?>
<img src="design/spacer.gif" width="300" height="1" />
    </td>
  </tr>
</table>

</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="copyright gradient">
  <tr>
    <td width="33%"><? module("read:copyright-tis"); ?></td>
    <td width="33%"><? module("read:copyright-rbc"); ?></td>
    <td width="33%"><? module("read:copyright-calls"); ?></td>
  </tr>
</table>
</div>
</body>
</html><? $p = ob_get_clean(); module("admin:toolbar"); echo $p; ?><? $p = ob_get_clean(); module("page:header"); echo $p; ?>