<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/ajax.css"/>
{head}
</head>

<body class="ajaxBodyTag">
<!--
ajax content message layout for messages and close button
-->
<div class="ajaxHolder">
	<div class="ajaxBody ajaxDialog">
	    <div class="ajaxDialogHolder">
	    	<div class="ajaxHead">
                <span class="ajaxClose"><a href="#">X</a></span>
                <h1 class="ajaxTitle">{{page:title}}</h1>
			</div>
            {{display}}
        </div>
	</div>
</div>
</body>
</html>