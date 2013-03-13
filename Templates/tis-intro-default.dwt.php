<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../_templates/baseStyle.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="../_sites/tis-intro/style.css"/>
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
{{!page:header}}
</head>

<body>
<div class="body">
{{!admin:toolbar}}
<div class="padding">
{{script:menu}}
<div class="header">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <th valign="top"><a href="{{getURL}}"><img src="../_sites/tis-intro/design/logo.gif" width="205" height="94" border="0" /></a></th>
    <td width="100%" valign="top" nowrap="nowrap">{{doc:read:note=prop.!place:note}}</td>
    <td valign="top" nowrap="nowrap" class="menu popup">{{doc:read:menu2=prop.!place:documentMenu}}</td>
  </tr>
</table>
</div>

<div>{{doc:read:menuTable=prop.!place:topMenu}}</div>
<br />
<!-- TemplateBeginEditable name="body" -->{{display}}<!-- TemplateEndEditable -->
</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="copyright gradient">
  <tr>
    <td width="33%">{{read:copyright-tis}}</td>
    <td width="33%">{{read:copyright-rbc}}</td>
    <td width="33%">{{read:copyright-calls}}</td>
  </tr>
</table>
</div>
</body>
</html>