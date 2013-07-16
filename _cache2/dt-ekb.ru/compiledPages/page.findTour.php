<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? module("page:style", 'baseStyle.css') ?>
<? module("page:style", '../../style.css') ?>
<? ob_start(); ?>
</head>

<body style="min-width:inherit">
<? module("display"); ?>
</body>
</html><? $p = ob_get_clean(); module("page:header"); echo $p; ?>