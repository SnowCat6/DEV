<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? module("page:style", 'baseStyle.css') ?><? ob_start(); ?>
<style>
body{
	background:#131312;
	color:#fff;
	padding:0; margin: 0;
	font-family:Verdana, Geneva, sans-serif;
	font-size:12px;
}
h1{
	font-size:36px;
}
a{
	color:white;
}
.content{
	background:#0c3352;
	border-top:solid 1px white;
	border-bottom:solid 1px white;
	padding-top:20px; padding-bottom:20px;
}
.padding{
	padding-left:50px;
	padding-right:50px;
}
.content .padding{
	text-align:left;
	min-height:300px;
	max-width:1100px;
}
.content .login th{
	color:white;
}
.copyright{
	padding:20px;
}
</style>
</head>

<body>
<center>
    <div class="header padding">
	    <h1><? module("page:title"); ?></h1>
    </div>
    <div class="content">
	    <div class="padding"><? module("display"); ?></div>
    </div>
    <div class="copyright">
    (c) 2012-<?= date('Y')?> ООО "Виртуальный проект"<br />
	<a href="/mailto:vpro@vpro.ru">vpro@vpro.ru</a>
    </div>
</center>
</body>
</html><? $p = ob_get_clean(); module("page:header"); echo $p; ?>