<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/shop_mk_page.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../../_templates/baseStyle.css"/>
<link rel="stylesheet" type="text/css" href="style.css"/>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
{{!page:header}}
</head>

<body>
<center>
<div class="body">
    {{!admin:toolbar}}
    <div class="header">
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td width="422" valign="top"><a href="{{getURL}}"><img src="design/logo.gif" width="422" height="75" /></a></td>
            <td valign="middle" class="menu inline topMenu">{{doc:read:menu=prop.!place:topMenu}}</td>
            <td align="right" class="info paddingRight">{{read:header=bottom}}</td>
        </tr>
        </table>
<div class="searchPanel paddingRight">
<div class="menu inline">{{doc:read:menuLink=prop.!place:header}}</div>     
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <td class="catalogName"><a href="{{url}}">Каталог</a></td>
    <td width="100%" class="searchField">
    <form action="{{url:search_product}}" method="post">
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
    <a href="{{getURL:user_register}}">Регистрация</a> | <a href="{{getURL:login}}">вход</a>
    </td>
    <td class="headBask"><a href="{{url:bask}}"><img src="design/iconBask.gif" width="37" height="32" border="0" /></a>
    </td>
    <td class="headBask">{{bask:count}}</td>
  </tr>
    </table>
</div>
    </div>
<!-- InstanceBeginEditable name="body" -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td valign="top" style="padding-left:10px">
<div class="menuHolder"><div class="menuHolder2">{{doc:read:menuEx=type:catalog;prop.!place:mainCatalog}}</div></div>
<div class="adv">{{read:indexLeftAdv}}</div>
</td>
    <td width="100%" valign="top">
<div class="banner">{{banner}}</div>
<div class="saleHolder paddingRight">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:20px 0 0 20px">
      <tr>
        <td width="33%" class="iconSale icon1"><h2>Самая низкая цена</h2></td>
        <td width="33%" class="iconSale icon2"><h2>Лидер продаж</h2></td>
        <td width="33%" class="iconSale icon3"><h2>Новинка</h2></td>
      </tr>
      <tr>
        <td align="center" valign="top">{{doc:read:saleBig=prop.!place:sale1}}</td>
        <td align="center" valign="top">{{doc:read:saleBig=prop.!place:sale2}}</td>
        <td align="center" valign="top">{{doc:read:saleBig=prop.!place:sale3}}</td>
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
{{doc:read:sales=type:catalog;prop.!place:sales}}&nbsp;
      </td>
    <td valign="top" class="saleSmall" width="250">
<h2>Распродажа</h2>
{{doc:read:saleSmall=prop.!place:saleSmall}}
      </td>
    </tr>
</table>
<div class="viewHistory">{{doc:viewHistory}}</div>
 </div>
<!-- InstanceEndEditable -->
<div class="copyright">{{read:copyright}}</div>
</div>
</center>
{{read:statistic}}
</body>
<!-- InstanceEnd --></html>
