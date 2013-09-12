<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- TemplateBeginEditable name="doctitle" -->
<!-- TemplateEndEditable -->
<link rel="stylesheet" type="text/css" href="../_templates/baseStyle.css"/>
{{!page:header}}
<style type="text/css">
body,td,th {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 12px;
	color: #FFF;
}
body {
	background-color: #131312;
	padding:0; margin:0;
}
a{
	color:white;
}
.body{
	background:#0c3352;
	border:solid 1px white;
	border-left:none;
	border-right:none;
	padding:20px;
}
.padding{
	padding-left:60px;
	padding-right:60px;
}
.copyright{
	padding-top:20px;
	padding-bottom:20px;
}
.copyright td{
	vertical-align:top;
}
.content{
	max-width:1100px;
	text-align:left;
	line-height:150%;
}
.header{
	padding:20px 0;
}
.login th{
	color:white;
}
</style>
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
</head>

<body>
{{!admin:toolbar}}
<center>
<div class="padding">
<div class="header content">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%" valign="top">&nbsp;</td>
    <td width="581" valign="top"><a href="{{url}}"><img src="../_sites/vpro.ru/design/title.gif" width="581" height="103" border="0" /></a></td>
    <td width="50%" valign="top">&nbsp;</td>
  </tr>
</table>
</div>
</div>
<div class="body padding">
<div class="content">
  <!-- TemplateBeginEditable name="body" -->
{{display}}
  <!-- TemplateEndEditable --></div>
</div>
<div class="copyright padding">
<div class="content">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%">{{read:copyright}}</td>
    <td nowrap="nowrap">{{read:counters}}</td>
  </tr>
</table>
</div>
</div>
</center>
</body>
</html>
