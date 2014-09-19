<? function module_advBanner(&$val)
{
	m('script:advBanner');
	m('styleLoad', 'css/advBanner.css');
	if (!$val) $val = "adv";
	
	$folder	= images."/advImage/";
	$data	= readData("$folder/adv.bin");
	$bAdmin	= hasAccessRole('admin,writer,developer');
	$bFirst	= true;
?>
    <div class="advBackground">
<?	for($ix=0; $ix<10; ++$ix){
	if (showAdvBanner("$val-$ix", $bFirst, $bAdmin, $data)){
		$bFirst = false;
	}
}?>
    </div>
    <div class="advSeek"></div>
<? } ?>
<? function script_advBanner(&$val){
	m('script:jq');
	$menu	= '';
	if (hasAccessRole('admin,writer,developer')){
		m('script:ajaxLink');
		$menu = '<div><a href="#edit" id="ajax_edit">Редактировать</a></div>';
	}
?>
<script>
var advTimer = null;
$(function(){
	var c = $(".advBackground .content").length;
	if (c > 1){
		for(ix=0; ix<c; ++ix){
			$(".advSeek").append((ix?'<span></span>':'')+"<div>" + (ix+1) + '{!$menu}' + "</div>");
		}
	}
<? if ($menu){?>
		$(".advSeek div div a").each(function(ix, em){
			var ix = parseInt($(this).parent().parent().text()) - 1;
			var e = $($(".advBackground .content").get(ix));
			$(this).attr('href', '{{url:advBanner}}?edit=' + e.attr("rel"));
		});
		$(document).trigger("jqReady");
<? } ?>
	$(".advSeek > div").hover(function(){
		setAdvIndex(parseInt($(this).text()) - 1);
		clearTimeout(advTimer);
		advTimer = null;
	}, function(){
		advTimer = setTimeout(setAdvNext, 5000);
	});
	$(".advBackground .content").hover(function(){
		clearTimeout(advTimer);
		advTimer = null;
	}, function(){
		advTimer = setTimeout(setAdvNext, 5000);
	});
	setAdvIndex(0);
	advTimer = setTimeout(setAdvNext, 5000);
});
function setAdvNext(){
	var ix = $(".advSeek > div.current").index();
	setAdvIndex(ix+1, true);
	clearTimeout(advTimer);
	advTimer = setTimeout(setAdvNext, 5000);
}
function setAdvIndex(ix, useAnimate)
{
	var seek = $(".advSeek > div");
	ix = ix % seek.length;
	if (isNaN(ix)) ix = 0;

	if (useAnimate)
	{
		$(".advBackground .content.current")
			.css({"z-index": -1, "opacity": 1})
			.animate({"opacity": 0}, 'slow');
			
		$($(".advBackground .content").get(ix))
			.addClass("current")
			.css({"z-index": 0, "opacity": 0})
			.animate({"opacity": 1}, 'slow', function(){
				$(".advBackground .content.current").removeClass("current");
				$(this).addClass("current");
			});
	}else{
		$($(".advBackground .content").removeClass("current").get(ix))
		.addClass("current")
		.css({"z-index": 0, "opacity": 1})
	}
	$(seek.removeClass("current").get(ix)).addClass("current");
}
</script>
<? } ?>
<? function showAdvBanner($name, $bCurrent, $bAdmin, &$data)
{
	$doc	= $data[$name];
	if (!$bAdmin && $doc['show']!='yes') return;
	$current	= $bCurrent?' current':'';
	$titleImage	= $doc['titleImage'];
	if ($titleImage){
		$titleImage	= imagePath2local(images."/advImage/$name/$titleImage");
		$titleImage = "style=\"background-image: url($titleImage)\"";
	}
?>
    <div class="content{$current}" rel="{$name}" {!$titleImage}>
    	<div class="advContent"><?
if (beginCache("advBanner$name"))
{
	$document	= $doc['document'];
	event('document.compile', $val);
	echo $document;
	endCache();
}
		?></div>
    </div>
<? return true; } ?>