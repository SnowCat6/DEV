<? function doc_read_scroll(&$db, &$search, &$data){ ?>
<link rel="stylesheet" type="text/css" href="scroll.css"/>
<div class="scroll">
<div class="context"><div id="window">
<table border="0" cellspacing="0" cellpadding="0" class="scrollTable">
<tr>
<?
	$db->seek(0);
	while($db->next()){
	$id		= $db->id();
	$url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
?>
{beginCompile:advScrollIndex}
    <th><a href="{!$url}"><?
	$folder	= docTitle($id);
    displayThumbImageMask($folder, 'design/mask.png');
	?></a></th>
{endCompile:advScrollIndex}
<? } ?>
</tr>
<tr>
<?
	$db->maxCount = $db->ndx = 0;
	$db->seek(0);
	while($data = $db->next()){
	$id		= $db->id();
	$menu	= doc_menu($id, $data, true);
	$url	= getURL($db->url());
?>
    <td>
    {beginAdmin}
    <a href="{!$url}">{$data[title]}</a>
    {endAdminTop}
    </td>
<? } ?>
</tr>
</table>
</div></div>
<a href="" class="left"></a>
<a href="" class="right"></a>
</div>

<? module('script:jq')?>
<script type="text/javascript">
//	scroll
$(function() {
	var ctx = $(".scroll .context > div");
	var hScroll = ctx.height();
	var wScroll = ctx.width();
	var cellWidth = 182*3;
	$(".scroll .context").css("height", hScroll);
	ctx.css("position", "absolute");
	ctx.css("left", 0);           
	
	$(".scroll .left").click(function(){
		var ctx = $(this).parent(".scroll").find(".context > div");
		var now = Math.min(0, parseInt(ctx.css("left")) + cellWidth);
		if (now > -cellWidth/2) now = 0;               
		ctx.animate({left: now});
		$(ctx).focus();
		return false;
	});
	$(".scroll .right").click(function(){
		var ctx = $(this).parent(".scroll").find(".context > div");
		var maxScroll = ctx.width() - $(this).parent(".scroll").width() + 30;
		var now = Math.max(-maxScroll, parseInt(ctx.css("left")) - cellWidth);
		if (now + maxScroll < cellWidth/2) now = -maxScroll;
		ctx.animate({left: now});
		$(ctx).focus();
		return false;
	});
});
</script>

<? return $search; } ?>

