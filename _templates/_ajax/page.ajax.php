<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../baseStyle.css"/>
<link rel="stylesheet" type="text/css" href="css/ajax.css"/>

{head}
</head>

<body class="ajaxBodyTag">
<!--
ajax content layout with title, background, close button
-->
<div class="ajaxHolder">
	<div class="ajaxBody">
    	<div class="ajaxHead">
            <span class="ajaxClose"><a href="#">X</a></span>
            <h1 class="ajaxTitle">{{page:title}}</h1>
        </div>
        <div class="ajaxScroll">
            <div class="ajaxDocument shadow">
                {{display}}
            </div>
        </div>
    </div>
</div>
</body>
</html>