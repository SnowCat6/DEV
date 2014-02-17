<? function script_ajaxLink($val){ module('script:overlay'); m('page:style', 'ajax.css') ?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
$(function(){
	$(document).on("jqReady ready", function()
	{
		$('a[id*="ajax"]')
		.unbind("click.ajaxLoad")
		.on("click.ajaxLoad", function(){
			var ajaxTemplateName = $(this).attr('id');
			$("body").attr("ajaxTemplateName", ajaxTemplateName);
			ajaxLoad($(this).attr('href'));
			return false;
		});
		ajaxClose();
		$(".ajaxDocument .seek a, .ajaxDocument .seekLink a, .ajaxDocument a.seekLink")
		.unbind("click.ajaxLoad")
		.on("click.ajaxLoad", function(){
			return ajaxLoad($(this).attr('href'));
		});
	});
});
function ajaxClose(){
	$(".ajaxClose a")
	.unbind("click.ajaxLoad")
	.on("click.ajaxLoad", function()
	{
		$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
		return false;
	});
}
function ajaxLoad(url)
{
	var data = 'ajax=' + $("body").attr("ajaxTemplateName");
	$('<div />')
		.overlay('ajaxLoading')
		.load(url, data, function()
		{
			$(".ajaxLoading").removeClass("ajaxLoading");
			ajaxClose();
			$(document).trigger("jqReady");
		});
	return false;
}
 /*]]>*/
</script>
<? } ?>
