<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? ob_start(); ?>
</head>

<body>
<center>
	<div style="padding:100px 00px; width:500px">
<div class="shadow" style="padding:20px">
<h1><? module("page:title"); ?></h1>
<? module("display"); ?>
</div>
	</div>
</center>
</body>
</html><? $p = ob_get_clean(); module("page:header"); echo $p; ?>