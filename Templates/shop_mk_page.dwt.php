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
<div class="body">
    {{!admin:toolbar}}
    <div class="header">
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td width="400" valign="top"><a href="{{getURL}}"><img src="../_sites/shop_mk/design/logo.gif" width="390" height="42" /></a></td>
            <td valign="middle" nowrap="nowrap" class="menu horizontal topMenu">{{doc:read:menu=prop.!place:topMenu}}</td>
            <td width="250">{{read:header}}</td>
        </tr>
        </table>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="searchPanel">
  <tr>
    <td class="catalogSelect">
<div class="catalogName">Каталог</div>
<div class="catalogMenu">{{doc:read:menuEx=prop.!place:mainCatalog;type:catalog,page}}</div>
    </td>
    <td width="100%" class="searchField">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="left">Поиск</td>
    <td width="100%" class="center"><input type="text" name="textfield" class="input w100" /></td>
    <td class="right"><input type="submit" name="button" class="button2" value="" /></td>
  </tr>
</table>

    
    </td>
    <td nowrap="nowrap" class="headLogin"><a href="#">Регистрация</a> | <a href="#">вход</a></td>
    <td class="headBask">
    <img src="../_sites/shop_mk/design/iconBask.gif" width="37" height="32" />
    </td>
    <td class="headBask">0</td>
  </tr>
</table>

    </div>
<div class="banner"></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="body2">
  <tr>
    <td valign="top" class="panel left">
<div>{{doc:read:scroll2=prop.!place:advLeft}}</div>
<img src="../_templates/design/spacer.gif" width="200" height="1" />
    </td>
    <td width="100%" valign="top"><!-- TemplateBeginEditable name="body" -->{{display}}<!-- TemplateEndEditable --></td>
  </tr>
</table>
<div class="copyright">{{read:copyright}}</div>
</div>
</body>
</html>
