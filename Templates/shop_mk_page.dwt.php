<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../_templates/baseStyle.css"/>
<link rel="stylesheet" type="text/css" href="../_sites/shop_mk/style.css"/>
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
{{!page:header}}
</head>

<body>
<center>
<div class="body">
    {{!admin:toolbar}}
    <div class="header">
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td width="422" valign="top"><a href="{{getURL}}"><img src="../_sites/shop_mk/design/logo.gif" width="422" height="75" /></a></td>
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
    <td class="headBask"><a href="{{url:bask}}"><img src="../_sites/shop_mk/design/iconBask.gif" width="37" height="32" border="0" /></a>
    </td>
    <td class="headBask">{{bask:count}}</td>
  </tr>
    </table>
</div>
    </div>
<!-- TemplateBeginEditable name="body" -->
<div class="document">{{display}}</div>
<!-- TemplateEndEditable -->
<div class="copyright">{{read:copyright}}</div>
</div>
</center>
{{read:statistic}}
</body>
</html>
