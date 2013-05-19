<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
{{!page:header}}
<link rel="stylesheet" type="text/css" href="../_sites/fs/style.css"/>
</head>

<body>
{{!admin:toolbar}}
<center>
<div class="body">
	<div class="body2">
   	  <div class="head">
<div class="logo">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <th align="center"> <a href="{{getURL}}"><img src="../_sites/fs/design/logo.gif" width="358" height="86" border="0" /></a>
    <h2>КУЛИНАРНЫЕ ПРИКЛЮЧЕНИЯ</h2></th>
    <td width="100%" class="corp">{{read:corp=bottom}}</td>
  </tr>
</table>
</div>
{{read:title}}
        </div>
    </div>
<!-- TemplateBeginEditable name="body" -->
<div class="master"> {{doc:read:master=prop.!place:master}}</div>
<div class="page"> {{display}}
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td width="33%" valign="top" class="left"><div>{{read:index1}}</div>
        {{read:index2}} </td>
      <td width="33%" valign="top" class="center"> {{doc:read:news2=prop.!place:indexNews}} </td>
      <td width="33%" valign="top" class="right"> {{doc:read:news2=prop.!place:indexNews2}} </td>
    </tr>
  </table>
</div>
<!-- TemplateEndEditable -->
<div class="copyright">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td width="25%" valign="top">{{read:copyright1}}</td>
      <td width="25%" valign="top">{{read:copyright2}}</td>
      <td width="25%" valign="top">{{read:copyright3}}</td>
      <td width="25%" valign="bottom">{{read:copyright4}}</td>
      </tr>
    </table>
</div>
</div>
</center>
</body>
</html>