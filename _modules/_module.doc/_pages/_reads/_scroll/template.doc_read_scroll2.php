<? function doc_read_scroll2(&$db, &$search, &$data){ ?>
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
{beginCompile:advScrollIndex2}
<div>
<a href="{!$url}"><?
	$folder	= docTitle($id);
    displayThumbImageMask($folder, 'design/maskScroll2.png');
?></a>
<h2>{$data[title]}</h2>
{!$price}
</div>
{endCompile:advScrollIndex2}
{endAdminTop}
<? } ?>
</div>
<? return $search; } ?>