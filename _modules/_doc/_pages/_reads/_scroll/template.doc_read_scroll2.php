<? function doc_read_scroll2(&$db, $val, &$search){ ?>
<link rel="stylesheet" type="text/css" href="css/scroll.css"/>
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
<a href="{!$url}">
{{doc:titleImage:$id=mask:design/maskScroll2.png}}
</a>

<h2>{$data[title]}</h2>
{!$price}
{{bask:button:$id}}
</div>
{endAdminTop}
<? } ?>
</div>
<? return $search; } ?>