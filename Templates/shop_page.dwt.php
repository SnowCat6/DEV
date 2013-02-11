<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../_templates/baseStyle.css"/>
<link rel="stylesheet" type="text/css" href="../_sites/shop/_templates/style.css"/>
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
        <td valign="top"><a href="{{getURL}}" title="Интернет Магазин Мир электроники"><img src="../_sites/shop/_templates/design/logo.gif" alt="logo" width="209" height="48" hspace="0" vspace="30" border="0" /></a></td>
        <td width="100%" valign="middle" class="topAdv">{{read:topAdv}}</td>
        <td valign="top">&nbsp;</td>
    </tr>
    </table>
    <div class="menu horizontal">
    {{doc:read:menu=prop.place:topMenu}}
    <div class="clear"></div>
    </div>
</div>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="body2">
  <tr>
    <td valign="top" class="panel left">
<div class="menu index">
    <h2>Электроника</h2>
    {{doc:read:menu2=prop.place:indexMenu}}
    <h2>Техника</h2>
    {{doc:read:menu2=prop.place:indexMenuTech}}
    <h2>Полезное</h2>
    {{doc:read:menu2=prop.place:indexMenuOther}}
</div>    
    {{doc:read:scroll2=prop.place:advLeft}}
    </td>
    <td width="100%" valign="top"><!-- TemplateBeginEditable name="body" -->{{display}}<!-- TemplateEndEditable --></td>
  </tr>
</table>
<div class="copyright">{{read:copyright}}</div>
</div>
</body>
</html>
