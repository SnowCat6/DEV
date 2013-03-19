<? function doc_read_scroll2(&$db, $val, &$search){ ?>
<link rel="stylesheet" type="text/css" href="scroll.css"/>
<div class="scroll2">
<?
	$db->seek(0);
	while($data = $db->next()){
	$id		= $db->id();
	$url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
	$price	= docPriceFormat2($data);
?>
{beginAdmin}
<div>
{beginCompile:advScrollIndex2}
<a href="{!$url}"><?
	$folder	= docTitleImage($id);
    displayThumbImageMask($folder, 'design/maskScroll2.png');
?></a>
{endCompile:advScrollIndex2}
<h2>{$data[title]}</h2>
{!$price}
{{bask:button:$id}}
</div>
{endAdminTop}
<? } ?>
</div>
<? return $search; } ?>