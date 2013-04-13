<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{{!page:header}}
</head>

<body>
<div class="ajaxHolder ajaxDialog">
	<span class="ajaxClose"><a href="#">X</a></span>
    <div class="ajaxDocument shadow">
{{display:message}}
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
});
</script>
</body>
</html>