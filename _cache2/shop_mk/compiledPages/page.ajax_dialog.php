<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? ob_start(); ?>
</head>

<body>
<center class="ajaxHolder">
	<div class="ajaxBody ajaxDialog">
        <span class="ajaxClose"><a href="#">X</a></span>
        <div class="ajaxDocument shadow">
            <? module("display:message"); ?>
        </div>
	</div>
</center>
</body>
</html><? $p = ob_get_clean(); module("page:header"); echo $p; ?>