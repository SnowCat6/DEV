<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../_templates/baseStyle.css"/>
<link rel="stylesheet" type="text/css" href="../_sites/tah/style.css"/>
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
{{!page:header}}
</head>

<body>
{{!admin:toolbar}}
<center><div style="width:1000px; text-align:left; position:relative">
<div class="header">
<div class="menu horizontal gradient" style="width:680px">{{doc:read:menuTable:bottom=prop.!place:topMenu}}</div>
<div class="searchBox">
<form action="{{getURL:search}}" method="post">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%"><input name="search[name]" class="input w100" type="text" /></td>
    <th><input name="submit" type="image" src="../_sites/tah/design/icon_search.gif" /></th>
</tr>
</table>
</form>

</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td valign="top">
<a href="{{getURL}}"><img src="../_sites/tah/design/logoAni.gif" alt="logo" width="250" height="200" /></a>
    </td>
    <td width="50%" valign="bottom" style="padding-left:70px">{{read:header=bottom}}</td>
    <td width="50%" valign="bottom" style="padding-left:20px">{{read:header2=bottom}}</td>
</tr>
</table>
</div>
<div class="topMenu menu horizontal gradient">{{doc:read:menuTable=prop.!place:midMenu}}</div>
<!-- TemplateBeginEditable name="body" -->
<div class="topMenu menu horizontal gradient">{{doc:read:menuTable=prop.!place:midMenu2}}</div>
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
<div class="bottom menu horizontal">{{doc:read:menu:bottom=prop.!place:bottomMenu}}</div>
<div class="copyright gradient">{{read:copyright}}</div>
</div></center>
</body>
</html>