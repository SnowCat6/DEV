<?
function module_ajax($val, $data)
{
	setTemplate('');
	$fn = getFn("ajax_$val");
	return $fn?$fn($data):NULL;
}
function ajax_read($data){
	@$template = $data[1];
	module("doc:read:$template", getValue('search'));
}
?>
<? function script_ajaxLayout($val){ module('script:jq'); ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
var ajaxLayoutHTML = new Array();
$(function(){
	$(".ajaxLayout input").keyup(function(){
		loadAjaxLayout($(this).parents("form.ajaxLayout"));
	});
});
function loadAjaxLayout(f)
{
	if (f.hasClass("loading")) return f.addClass("needUpdate");

	var ajaxLayoutEmpty = true;
	$(f.find("input")).each(function(){
		if ($(this).attr("type").toLowerCase() == "submit") return;
		if ($(this).attr("type").toLowerCase() == "hidden") return;

		if ($(this).val() != ""){
			ajaxLayoutEmpty = false;
		}
	});

	var id = f.attr("id");
	var layout = $("div#" + id);
	var ctx = layout.find(".layoutContent");
	if (ctx.length == 0) ctx = layout;
	
	if (ajaxLayoutHTML[id] == null){
		ajaxLayoutHTML[id] = layout.html();
		if (ajaxLayoutEmpty) return;
	}else{
		if (ajaxLayoutEmpty){
			layout.html(ajaxLayoutHTML[id]);
			return;
		}
	}
	
	var url = "ajax_read_" + layout.attr("template") + ".htm";
	var data = f.serialize();
	var r = ("" + f.attr("replace")).split(":");
	if (r.length==2) data = data.replace(r[0],r[1]);
	
	f.addClass("loading");
	
	
	$(layout.find(".layoutTitle")).show();
	$(layout.find(".layoutError")).hide();
	ctx.text("Загрузка...");
	ctx.load(url, data, function(text){
		//	on load
		f.removeClass("loading");
		if (f.hasClass("needUpdate")){
			return loadAjaxLayout(f.removeClass("needUpdate"));
		}
		if (text.replace(/\s+/, '') == ""){
			$(layout.find(".layoutError")).show();
		}
	});
}
 /*]]>*/
</script>
<style>
.layoutError, .layoutTitle{
	display:none;
}
</style>
<? } ?>