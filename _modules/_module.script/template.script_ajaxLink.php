<? function script_ajaxLink($val){ module('script:overlay'); m('page:style', 'ajax.css') ?>
<script type="text/javascript" language="javascript">
/*<![CDATA[*/
$(function(){
	$(document).on("jqReady ready", function()
	{
		$('a[id*="ajax"]').unbind("click.ajaxLoad").on("click.ajaxLoad", function(){
			var templateName = $(this).attr('id');
			$("body").attr("templateName", templateName);
			ajaxLoad($(this).attr('href'), 'ajax=' +  templateName);
			return false;
		});
		ajaxClose();
		$(".ajaxDocument .seek a").unbind("click.ajaxLoad").on("click.ajaxLoad", function(){
			return ajaxLoad($(this).attr('href'), $("#fadeOverlayHolder").attr("rel"));
		});
	});
});
function ajaxClose(){
	$(".ajaxClose a").unbind("click.ajaxLoad").on("click.ajaxLoad", function()
	{
		$("#fadeOverlayLayer, #fadeOverlayHolder").remove();
		return false;
	});
}
function ajaxLoad(url, data)
{
	if (!data) return;
	$('<div />').overlay('ajaxLoading')
		.css({position:'absolute', top:0, left:0, right:0, bottom: 0})
		.attr("rel", data)
		.load(url, data, function()
		{
			$(".ajaxLoading").remove();
			ajaxClose();
			$(document).trigger("jqReady");
		});
	return false;
}
 /*]]>*/
</script>
<? } ?>
