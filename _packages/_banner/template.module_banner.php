<? function module_banner($fn, $data)
{
	$basePath	= images."/banners";
	@list($fn, $name) = explode(':', $fn, 2);

	$fn		= getFn("banner_$fn");
	if ($fn){
		if (!$name) $name = $data[1];
		$path	= images."/banners/$name.txt";
		return $fn?$fn($name, $path):NULL;
	}
	if (!$name) $name = 'banner';
	m('script:jq');
	m('script:banner');
	
	$bAdmin = hasAccessRole('admin');
	$banners = array();
?>
<div class="bannerHolder">
	<div class="bannerTitle inline">
<?
	for($ix=0; $ix<5; ++$ix){
		$class	= $ix?'':' class="current"';
		$path	= "$basePath/$name$ix.txt";
		$data	= getCacheValue("banner/$name$ix");
		if (!is_array($data)){
			$data	= readIniFile($path);
			if (!is_array($data)) $data = array();
			setCacheValue("banner/$name$ix", $data);
		}
		$bannerName	= $data['content']['name'];
		$bHide		= $data['content']['hide'];
		if (!$bannerName || $bHide){
			if (!$bAdmin) continue;
			if ($bannerName) $bannerName = "<strike>$bannerName</strike>";
			else $bannerName = "$name$ix";
		}
		$url	= $data['content']['url'];
		if (!$url) $url = '#';
		echo "<a href=\"$url\" id=\"$name$ix\"$class>$bannerName</a>";
		$banners[] = $ix;
	}
?>
	</div>
<?
	foreach($banners as $ix){
		$class	= $ix?'':' current';
		echo "<div class=\"bannerContent$class\" id=\"$name$ix\">";
		$path	= "$basePath/$name$ix.txt";
		module("banner:show:$name$ix");
		echo '</div>';
	}
?>
</div>
<? } ?>
<?
function banner_show($name, $path)
{
	$data	= getCacheValue("banner/$name");
	if (!is_array($data)){
		$data	= readIniFile($path);
		if (!is_array($data)) $data = array();
		setCacheValue("banner/$name", $data);
	}
	m('script:banner');

	$bk			= $data['background'];
	$bkImage	= htmlspecialchars($bk['image']);
	$bkStyle	= $bk['style'];
	if ($bkImage) $bkStyle = "background: url($bkImage); $bkStyle";

	$image		= $data['image'];
	$fgImage	= htmlspecialchars($image['image']);
	$fgStyle	= $image['style'];
	if ($fgImage) $fgStyle = "background: url($fgImage) no-repeat center center;$fgStyle";

	$ctx	= $data['content'];	
	$html	= urldecode($ctx['html']);
	$url	= $ctx['url'];

	$feedback		= $data['feedback'];
	$feedbackName	= $feedback['name'];
	if ($feedbackName){
		$class		= $feedback['class'];
		if (!$class) $class = 'bannerFeedback';
		$feedback	= m("feedback:display:$feedbackName");
		if ($feedback) $feedback = "<div class=\"$class\">$feedback</div>";
	}else{
		$feedback = '';
	}
	
	$bAdmin = hasAccessRole('admin');
	$menu	= array();
	if ($bAdmin && !defined('bannerEdit')){
		$menu['Изменить#ajax']	= getURL("banner_edit_$name");
	}
?>
{beginAdmin}
<div class="bannerBackground" style="{!$bkStyle}">
<? if($url){?><a href="{!$url}"><? } ?>
	<div class="bannerImage" style="{!$fgStyle}">
    {!$html}
    </div>
<? if($url){?></a><? } ?>
    {!$feedback}
</div>
{endAdminBottom}
<? } ?>
<? function script_banner(){ ?>
<style>
.bannerContent{	display:none; }
.bannerContent.current{ display:block; }
.bannerTitle{ text-align:center; }
.bannerTitle a{
	padding:2px 5px 0 2px;
	margin:4px 10px 2px 10px;
	text-decoration:none;
	color:#004b91;
	border-bottom:solid 4px #fff;
}
.bannerTitle a.current{
	border-bottom:solid 4px #ddd;
}
.bannerBackground{
	position:relative;
	background-position: center center;
	background-size:cover;
}
.bannerBackground .bannerFeedback{
	position:absolute;
	right:10px; top:10px;
	width:450px;
}
.bannerBackground .bannerFeedback .button{
	float:right;
}
.bannerImage{
	background-repeat:no-repeat;
}
</style>
<script>
var bannerTimeout = 0;
$(function(){
	$(".bannerTitle a").hover(function(){
		clearTimeout(bannerTimeout);
		showThisBanner($(this));
	});
	$(".bannerHolder").hover(0, function(){
		clearTimeout(bannerTimeout);
		bannerTimeout = setTimeout(showNextBanner, 3*1000);
	});
	bannerTimeout = setTimeout(showNextBanner, 3*1000);
});
function showThisBanner(now)
{
		var id = now.attr("id");
		$(".bannerContent").hide().removeClass("current");
		$(".bannerTitle a").removeClass("current");
		now.addClass("current");
		$(".bannerContent#" + id).addClass("current").show();
}
function showNextBanner(){
	var next = $(".bannerTitle a.current").next();
	if (next.length == 0) next = $($(".bannerTitle a").get(0));
	showThisBanner(next);
	clearTimeout(bannerTimeout);
	bannerTimeout = setTimeout(showNextBanner, 3*1000);
}
</script>
<? } ?>
