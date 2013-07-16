<? function doc_read_scroll2(&$db, $val, &$search){ ?>
<? module("page:style", 'scroll.css') ?>
<div class="scroll2">
<?
	$db->seek(0);
	while($data = $db->next()){
	$id		= $db->id();
	$url	= getURL($db->url());
	$menu	= doc_menu($id, $data, true);
	$price	= docPriceFormat2($data);
?>
<? beginAdmin() ?>
<div>
<?  if (beginCompile($data, "advScrollIndex2")){ ?>
<a href="/<? if(isset($url)) echo $url ?>"><?
	$folder	= docTitleImage($id);
    displayThumbImageMask($folder, 'design/maskScroll2.png');
?></a>
<?  endCompile($data, "advScrollIndex2"); } ?>
<h2><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></h2>
<? if(isset($price)) echo $price ?>
<? module("bask:button:$id"); ?>
</div>
<? endAdmin($menu, true) ?>
<? } ?>
</div>
<? return $search; } ?>