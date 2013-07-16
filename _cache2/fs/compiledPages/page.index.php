<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<? module("page:style", 'style.css') ?>

<? ob_start(); ?>
<? module("page:style", 'baseStyle.css') ?>
<? module("page:style", 'style.css') ?>
</head>

<body>
<? ob_start(); ?>
<center>
<div class="body">
	<div class="body2">
   	  <div class="head">
<div class="logo">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <th align="center"> <a href="<? module("getURL"); ?>"><img src="design/logo.gif" width="358" height="86" border="0" /></a>
    <h2>КУЛИНАРНЫЕ ПРИКЛЮЧЕНИЯ</h2></th>
    <td width="100%" class="corp"><? $module_data = array(); $module_data[] = "bottom"; moduleEx("read:corp", $module_data); ?></td>
  </tr>
</table>
</div>
<? module("read:title"); ?>
        </div>
    </div>

<div class="master"> <? $module_data = array(); $module_data["prop"]["!place"] = "master"; moduleEx("doc:read:master", $module_data); ?></div>
<div class="page">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td width="33%" valign="top" class="left"><div><? module("read:index1"); ?></div>
        <? module("read:index2"); ?> </td>
      <td width="33%" valign="top" class="center"> <? $module_data = array(); $module_data["prop"]["!place"] = "indexNews"; moduleEx("doc:read:news2", $module_data); ?> </td>
      <td width="33%" valign="top" class="right"> <? $module_data = array(); $module_data["prop"]["!place"] = "indexNews2"; moduleEx("doc:read:news2", $module_data); ?> </td>
    </tr>
  </table>
</div>

<div class="copyright">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td width="25%" valign="top"><? module("read:copyright1"); ?></td>
      <td width="25%" valign="top"><? module("read:copyright2"); ?></td>
      <td width="25%" valign="top"><? module("read:copyright3"); ?></td>
      <td width="25%" valign="bottom"><? module("read:copyright4"); ?></td>
      </tr>
    </table>
</div>
</div>
</center>
</body>
</html><? $p = ob_get_clean(); module("admin:toolbar"); echo $p; ?><? $p = ob_get_clean(); module("page:header"); echo $p; ?>