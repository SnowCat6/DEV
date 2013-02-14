<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../_templates/baseStyle.css"/>
<link rel="stylesheet" type="text/css" href="../_sites/tah/_templates/style.css"/>
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
{{!page:header}}
</head>

<body>
{{!admin:toolbar}}
<div class="header">
<div class="menu horizontal gradient">{{doc:read:menu2:bottom=prop.!place:topMenu}}</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td valign="top">
<a href="{{getURL}}"><img src="../_sites/tah/_templates/design/logo.gif" alt="logo" width="250" height="200" /></a>
    </td>
    <td width="50%" valign="bottom" style="padding-left:20px">{{read:header}}</td>
    <td width="50%" valign="bottom" style="padding-left:20px">{{read:header2}}</td>
</tr>
</table>
</div>
<div class="topMenu menu horizontal gradient">{{doc:read:menu2=prop.!place:midMenu}}</div>
<!-- TemplateBeginEditable name="body" -->
<div class="topMenu menu horizontal gradient">{{doc:read:menu2=prop.!place:midMenu2}}</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="panel left"> {{doc:read:newsHeader}}
      {{doc:read:newsHeader}}
      <div><img src="../_templates/design/spacer.gif" width="250" height="1" /></div></td>
    <td width="100%" class="document">{{display}}</td>
    <td class="panel right"> {{doc:read:newsHeader}}
      {{doc:read:newsHeader}}
      <div><img src="../_templates/design/spacer.gif" width="250" height="1" /></div></td>
  </tr>
</table>
<!-- TemplateEndEditable -->
<div class="copyright gradient">{{read:copyright}}</div>
</body>
</html>