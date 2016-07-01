$(function(){
	$("#fullPageCacheThis label input").change(function(){
		$(this).parents("form").submit();
	});
});
