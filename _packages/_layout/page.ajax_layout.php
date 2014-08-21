<!doctype html>
<html>
<head>
<meta charset="utf-8">
{head}
<style>
</style>
</head>

<body>
{{script:jq_ui}}
<script>
$(function(){
	$(".layoutEditor").appendTo("body");
	$().overlay("hide");
});
</script>
{{display}}
</body>
</html>