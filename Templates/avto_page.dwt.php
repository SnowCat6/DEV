<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../_templates/baseStyle.css"/>
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
{{!page:header}}
<style>
body{
	padding:0;
	margin:0;
	background:white;
	color:black;
	font-family:Arial, Helvetica, sans-serif;
}
.header p, .banner p{
	margin:0;
}
.header{
	position:relative;
	background-position: center top;
	text-align:center;
}
.logo{
	position:absolute;
	left:50%; top:0;
	margin-left:-111px;
}
.body{
	text-align:left;
	width:1100px;
	box-shadow:0 0 40px #aaa;
}
.banner{
	background:black;
	color:white;
	padding:2px;
	font-weight:bold;
	font-size:22px;
	text-align:center;
}
.borders{
	background:#eee;
	padding:3px 0;
	border-bottom:solid 2px gray;
}
.borders div{
	border-top:solid 2px gray;
	border-bottom:solid 2px gray;
	padding:2px 0;
}
.menuTop{
	background:#eee;
	padding:5px 0;
	font-weight:bold;
}
.menuTop a{
	font-family:"Times New Roman", Times, serif;
	font-size:22px;
	font-style:italic;
	color:#333;
	margin:0 40px;
	text-shadow:1px 1px 1px white;
}
.document{
	background:white;
	padding:20px 40px;
	text-align:left;
}
.copyright{
	background:#eee;
	border-top:solid 1px gray;
	border-bottom:solid 1px gray;
	padding:1px 0;
}
.copyright .copyright{
	padding:15px;
}
h2{
	font-size:32px;
	font-style:italic;
	color:#333;
	font-family:"Times New Roman", Times, serif;
	margin:0;
}
h2:before{
	content:"*";
	font-size:32px;
	font-style:italic;
	color:#333;
	padding-right:10px;
	font-family:"Times New Roman", Times, serif;
}
</style>
</head>

<body>
{{!admin:toolbar}}
<center>
    <div class="body">
        <div class="header">
            {{read:header=bottom}}
        	<div class="logo"><a href="{{getURL}}"><img src="../_sites/avto/design/logo.gif" alt="Бетоно Плюс" width="222" height="261" border="0" /></a></div>
        </div>
        <div class="banner">{{read:title}}</div>
        <div class="borders"><div></div></div>
        <div class="menuTop menu horizontal">{{doc:read:menu=prop.!place:menu}}</div>
        <div class="document"><!-- TemplateBeginEditable name="body" -->{{display}}<!-- TemplateEndEditable --></div>
      <div class="copyright"><div class="copyright">{{read:copyright}}</div></div>
    </div>
</center>
</body>
</html>