<? function script_propertyFields(){
	m('script:jq_ui');
?>
<script>
var propertyFields = new Array();
$(function(){
	var prop = new Array();
	$("[rel*=property]").each(function(){
		prop.push($(this).attr("rel").split(':', 2)[1]);
	});
	$.ajax('{{url:property_getAjax}}', {data: $.param({names: prop})})
	.done(function(data){
		propertyFields = $.parseJSON(data);
		$("[rel*=property]")
		.each(function(){
			var name = $(this).attr("rel").split(':', 2)[1];
			$(this).autocomplete({source: function(request, respond){
				respond(propertyFields[name]);
			}, minLength: 0});
		})
		.focus(function(){
			$(this).autocomplete("search", this.value);
		});
	});
});
</script>
<? } ?>