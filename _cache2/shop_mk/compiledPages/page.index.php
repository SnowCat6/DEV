<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? module("page:style", 'baseStyle.css') ?><? module("page:style", 'style.css') ?><? ob_start(); ?>
</head>

<body>
<center>
<div class="body">
    <? ob_start(); ?>
    <div class="header">
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td width="422" valign="top"><a href="<? module("getURL"); ?>"><img src="/design/logo.gif" width="422" height="75" /></a></td>
            <td valign="middle" class="menu inline topMenu"><? $module_data = array(); $module_data["prop"]["!place"] = "topMenu"; moduleEx("doc:read:menu", $module_data); ?></td>
            <td align="right" class="info paddingRight"><? $module_data = array(); $module_data[] = "bottom"; moduleEx("read:header", $module_data); ?></td>
        </tr>
        </table>
<div class="searchPanel paddingRight">
<div class="menu inline"><? $module_data = array(); $module_data["prop"]["!place"] = "header"; moduleEx("doc:read:menuLink", $module_data); ?></div>     
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <td class="catalogName"><a href="<? module("url"); ?>">Каталог</a></td>
    <td width="100%" class="searchField">
    <form action="<? module("url:search_product"); ?>" method="post">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="left">Поиск</td>
    <td width="100%" class="center"><input name="search[name]" type="text" value="<? $s = getValue('search'); echo htmlspecialchars($s['name']); ?>" class="input w100" id="search[name]" /></td>
    <td class="right"><input type="submit" name="button" class="button2" value="" /></td>
  </tr>
</table>
</form>
    </td>
    <td nowrap="nowrap" class="headLogin">
    <a href="<? module("getURL:user_register"); ?>">Регистрация</a> | <a href="<? module("getURL:login"); ?>">вход</a>
    </td>
    <td class="headBask"><a href="<? module("url:bask"); ?>"><img src="/design/iconBask.gif" width="37" height="32" border="0" /></a>
    </td>
    <td class="headBask"><? module("bask:count"); ?></td>
  </tr>
    </table>
</div>
    </div>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td valign="top" style="padding-left:10px">
<div class="menuHolder"><div class="menuHolder2"><? $module_data = array(); $module_data["type"] = "catalog"; $module_data["prop"]["!place"] = "mainCatalog"; moduleEx("doc:read:menuEx", $module_data); ?></div></div>
<div class="adv"><? module("read:indexLeftAdv"); ?></div>
</td>
    <td width="100%" valign="top">
<div class="banner"><? module("banner"); ?></div>
<div class="saleHolder paddingRight">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:20px 0 0 20px">
      <tr>
        <td width="33%" class="iconSale icon1"><h2>Самая низкая цена</h2></td>
        <td width="33%" class="iconSale icon2"><h2>Лидер продаж</h2></td>
        <td width="33%" class="iconSale icon3"><h2>Новинка</h2></td>
      </tr>
      <tr>
        <td align="center" valign="top"><? $module_data = array(); $module_data["prop"]["!place"] = "sale1"; moduleEx("doc:read:saleBig", $module_data); ?></td>
        <td align="center" valign="top"><? $module_data = array(); $module_data["prop"]["!place"] = "sale2"; moduleEx("doc:read:saleBig", $module_data); ?></td>
        <td align="center" valign="top"><? $module_data = array(); $module_data["prop"]["!place"] = "sale3"; moduleEx("doc:read:saleBig", $module_data); ?></td>
      </tr>
    </table>
</div>
    </td>
</tr>
</table>
<div class="paddingRight" style="padding-left:10px">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top">
<? $module_data = array(); $module_data["type"] = "catalog"; $module_data["prop"]["!place"] = "sales"; moduleEx("doc:read:sales", $module_data); ?>&nbsp;
      </td>
    <td valign="top" class="saleSmall" width="250">
<h2>Распродажа</h2>
<? $module_data = array(); $module_data["prop"]["!place"] = "saleSmall"; moduleEx("doc:read:saleSmall", $module_data); ?>
      </td>
    </tr>
</table>
<div class="viewHistory"><? module("doc:viewHistory"); ?></div>
</div>

<div class="copyright"><? module("read:copyright"); ?></div>
</div>
</center>
<? module("read:statistic"); ?>
</body>
</html>
<? $p = ob_get_clean(); module("admin:toolbar"); echo $p; ?><? $p = ob_get_clean(); module("page:header"); echo $p; ?>