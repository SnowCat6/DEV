<?
function gallery_default_before($val, &$data)
{
	m('script:jq');
	m('script:justifedGallery');

	$fn	= getFn('gallery_plain');
	$fn2= getFn('gallery_plain_before');
	if ($fn2) $fn2($val, $data);
}
function gallery_default($val, &$data)
{
?>
<div class="jGallery">
<?
	$fn	= getFn('gallery_plain');
	if ($fn) $fn($val, $data);
?>
</div>
<? } ?>
<? function script_justifedGallery(&$val){
	m('scriptLoad',	'script/justifedGallery/js/jquery.justifiedGallery.min.js');
	m('styleLoad',	'script/justifedGallery/css/justifiedGallery.min.css');
?>
<script>
$(function()
{
	$(document).on("ready jqReady", function()
	{
		$(".jGallery .imageContent, .jGallery .adminEditMenu").remove()
		$(".jGallery .adminEditArea").each(function(){
			$(this).replaceWith($(this).contents());
		});
	
		$(".jGallery .flat")
		.removeClass('flat')
		.justifiedGallery({
			margins: 4,
			rowHeight: 200,
			sizeRangeSuffixes: {'lt100':'',  'lt240':'',  'lt320':'',  'lt500':'',  'lt640':'', 'lt1024':''}		
		});
	});
});
</script>
<? } ?>
