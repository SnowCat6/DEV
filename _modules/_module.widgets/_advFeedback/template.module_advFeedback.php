<? function module_advFeedback($val)
{
	module('script:jq');
	$adv = getCacheValue("adv/$val");
	if ($adv) return print ($adv);

	@$adv = file_get_contents(localCacheFolder.'/siteFiles/'.$val);
	if (!$adv) return;
	
	ob_start();
	event('page.compile', &$adv);
	
	$valRoot	= dirname($val);
	$adv		= preg_replace('#(src\s*=\s*[\'"])#i', "\\1$valRoot/", $adv);
	$adv		= preg_replace('#(url\()#i', "\\1$valRoot/", $adv);
	$adv		= preg_replace('#<link[^>]*>#i', '', $adv);
?>
<div class="advFeedback"><? eval('?>'.$adv.'<?')?><div class="seek"></div></div>
<script>
var advFeedbackTimer = 0;
$(function(){
	$(".advFeedback").hover(function(){
		clearTimeout(advFeedbackTimer);
		advFeedbackTimer = 0;
	}, function(){
		advFeedbackTimer = setTimeout(nextAdvFeedback, 5000);
	});

	$(".advFeedback .adv").each(function(){
		$(".advFeedback .seek").append('<a href="#" />');
	});

	$(".advFeedback .seek a").hover(function(){
		setFeedbackAdv($(this).index());
	});

	setFeedbackAdv(0, true);
	advFeedbackTimer = setTimeout(nextAdvFeedback, 5000);
});
function nextAdvFeedback(){
	setFeedbackAdv($(".advFeedback .seek a.current").index()+1);
	advFeedbackTimer = setTimeout(nextAdvFeedback, 5000);
}
function setFeedbackAdv(ndx, bNoFade)
{
	ndx = ndx % $(".advFeedback .seek a").length;
	if ($(".advFeedback .seek a.current").index() == ndx) return;

	clearTimeout(advFeedbackTimer);
	advFeedbackTimer = 0;

	if (bNoFade != true){
		$(".advFeedback .adv.current").fadeOut().removeClass("current");
		$(".advFeedback .seek a.current").removeClass("current");
	}
	
	$($(".advFeedback .adv")[ndx]).addClass('current').fadeIn();
	$($(".advFeedback .seek a")[ndx]).addClass("current");
}
</script>
<? setCacheValue("adv/$val", $adv = ob_get_clean()); echo $adv; ?>
<? } ?>