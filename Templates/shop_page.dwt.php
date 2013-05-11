<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../_templates/baseStyle.css"/>
<link rel="stylesheet" type="text/css" href="../_sites/shop/style.css"/>
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
{{!page:header}}
</head>

<body>
<div class="body">
    {{!admin:toolbar}}
    <div class="header">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td valign="top"><a href="{{getURL}}" title="Интернет Магазин Мир электроники"><img src="../_sites/shop/design/logo.gif" alt="logo" width="209" height="48" hspace="0" vspace="30" border="0" /></a></td>
            <td width="100%" valign="middle" class="topAdv">{{read:topAdv=bottom}}</td>
            <td>{{bask:compact}}</td>
        </tr>
        </table>
        <div class="menu horizontal">{{doc:read:menuTable=prop.!place:topMenu}}</div><br />
    </div>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="body2">
  <tr>
    <td valign="top" class="panel left">
<div class="menu index2">{{doc:read:menu2=prop.!place:mainCatalog;type:catalog,page}}</div>
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
