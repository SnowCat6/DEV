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
?>
{beginCompile:advScrollIndex}
    <th><a href="{!$url}"><?
	$folder	= docTitle($id);
    displayThumbImageMask($folder, 'design/maskScroll.png');
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
    <h3><a href="{!$url}">{$data[title]}</a></h3>
    {endAdminTop}
    </td>
<? } ?>
</tr>
<tr>
<?
	$db->maxCount = $db->ndx = 0;
	$db->seek(0);
	while($data = $db->next()){
	$id		= $db->id();
	$price	= docPriceFormat2($data);
?>
    <td class="buy">
    {!$price}
    </td>
<? } ?>
</tr>
</table>
</div></div>
</div>

<? module('script:jq')?>
<script type="text/javascript">
/*<![CDATA[*/
	var ctx = $(".scroll .context > div");
	$(".scroll .context").css("height", ctx.height());
	ctx.css("position", "absolute");

$(".scroll").mousemove(function(e){
	//	over
	var cut = 80;
	var thisWidth = $(this).width();
	var width = $(this).find(".context > div").width();
	var widthDiff = width - thisWidth;

	var percent = (e.pageX - ($(this).offset().left + cut))/(thisWidth - cut*2);
	if (percent < 0) percent = 0;
	if (percent > 1) percent = 1;
	$(this).find(".context > div").css("left", -Math.round(percent*widthDiff));
});
 /*]]>*/
</script>

<? return $search; } ?>

