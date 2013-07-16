<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? module("page:style", 'baseStyle.css') ?>
<? module("page:style", 'style.css') ?>


<? ob_start(); ?>
</head>

<body>
<? ob_start(); ?>
<center><div style="width:1000px; text-align:left; position:relative">
<div class="header">
<div class="menu horizontal gradient" style="width:680px"><? $module_data = array(); $module_data["prop"]["!place"] = "topMenu"; moduleEx("doc:read:menuTable:bottom", $module_data); ?></div>
<div class="searchBox">
<form action="<? module("getURL:search"); ?>" method="post">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%"><input name="search[name]" class="input w100" type="text" /></td>
    <th><input name="submit" type="image" src="design/icon_search.gif" /></th>
</tr>
</table>
</form>

</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td valign="top">
<a href="<? module("getURL"); ?>"><img src="design/logoAni.gif" alt="logo" width="250" height="200" /></a>
    </td>
    <td width="50%" valign="bottom" style="padding-left:70px"><? $module_data = array(); $module_data[] = "bottom"; moduleEx("read:header", $module_data); ?></td>
    <td width="50%" valign="bottom" style="padding-left:20px"><? $module_data = array(); $module_data[] = "bottom"; moduleEx("read:header2", $module_data); ?></td>
</tr>
</table>
</div>
<div class="topMenu menu horizontal gradient"><? $module_data = array(); $module_data["prop"]["!place"] = "midMenu"; moduleEx("doc:read:menuTable", $module_data); ?></div>

<div class="indexBest"><? module("read:indexBest"); ?></div>
<? $module_data = array(); $module_data["prop"]["!place"] = "news"; moduleEx("doc:read:newsSelector", $module_data); ?>
<div class="topMenu menu horizontal gradient" style="margin:10px 0px"><? $module_data = array(); $module_data["prop"]["!place"] = "midMenu2"; moduleEx("doc:read:menuTable", $module_data); ?></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top" class="panel left">
<div><img src="design/spacer.gif" width="240" height="1" /></div>
<h2 class="hot">Продукция</h2>
<div class="news menu"><? $module_data = array(); $module_data["parent"] = "production"; moduleEx("doc:read:menuTree", $module_data); ?></div>
      </td>
    <td width="100%" rowspan="3" valign="top" class="document"><? module("display"); ?></td>
    <td valign="top" class="panel right">
<div><img src="design/spacer.gif" width="240" height="1" /></div><h2>Новости</h2>
<div class="news"><? $module_data = array(); $module_data["parent"] = "news"; moduleEx("doc:read", $module_data); ?></div>
<p><a href="<? module("getURL:news"); ?>">Все новости</a></p>

	</td>
  </tr>
  <tr>
    <td valign="top" class="panel left"><h2>Законодательство</h2>
      <div class="news"><? $module_data = array(); $module_data["parent"] = "zak"; moduleEx("doc:read", $module_data); ?></div>
      <p><a href="<? module("getURL:zak"); ?>">Все законодательство</a></p>
      </td>
    <td valign="top" class="panel right"><h2>Спец. предложения</h2>
      <div class="news"><? $module_data = array(); $module_data["parent"] = "super"; moduleEx("doc:read", $module_data); ?></div>
      <p><a href="<? module("getURL:super"); ?>">Все спец. предложения</a></p>
      </td>
  </tr>
  <tr>
    <td valign="top" class="panel left"><h2>Полезная информация</h2>
      <div class="news"><? $module_data = array(); $module_data["parent"] = "info"; moduleEx("doc:read", $module_data); ?></div>
      <p><a href="<? module("getURL:info"); ?>">Вся информация</a></p>
      </td>
    <td valign="top" class="panel right"><h2>Вакансии</h2>
      <div class="news"><? $module_data = array(); $module_data["parent"] = "vak"; moduleEx("doc:read", $module_data); ?></div>
      <p><a href="<? module("getURL:vak"); ?>">Все вакансии</a></p>
      </td>
  </tr>
</table>

<div class="bottom menu inline"><? $module_data = array(); $module_data["prop"]["!place"] = "bottomMenu"; moduleEx("doc:read:menu:bottom", $module_data); ?></div>
<div class="copyright gradient"><? module("read:copyright"); ?></div>
</div></center>
</body>
</html><? $p = ob_get_clean(); module("admin:toolbar"); echo $p; ?><? $p = ob_get_clean(); module("page:header"); echo $p; ?>