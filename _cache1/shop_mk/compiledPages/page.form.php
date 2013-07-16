<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? module("page:style", 'baseStyle.css') ?>
<style>
body{
	color:gray;
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
}
a{
	color:gray;
}
</style>
<? ob_start(); ?>
</head>

<body><? module("display"); ?></body>
</html><? $p = ob_get_clean(); module("page:header"); echo $p; ?>