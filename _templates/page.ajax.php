<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="ajax.css"/>
{{!page:header}}
</head>

<body>
<div class="ajaxHolder">
	<span class="ajaxClose"><a href="#">X</a></span>
	<h1>{{page:title}}</h1>
    <div class="ajaxScroll">
        <div class="ajaxDocument shadow">
    		{{display}}
        </div>
    </div>
</div>
{{script:jq}}
<script language="javascript" type="application/javascript">
$(function(){
	$(".ajaxLoading").remove();
	$(".ajaxClose a").click(function(){
		$("#fadeOverlayLayer").remove();
		$("#fadeOverlayHolder").remove();
		return false;
	});
	$(".ajaxDocument .seek a").click(function(){
		$('<div />').overlay('ajaxLoading')
			.css({position:'absolute', top:0, left:0, right:0, bottom: 0})
			.load($(this).attr('href'), 'ajax=ajax');
		return false;
	});
});
</script>
</body>
</html>