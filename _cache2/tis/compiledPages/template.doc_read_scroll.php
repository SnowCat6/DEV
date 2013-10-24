<? function doc_read_scroll(&$db, $val, &$search){
	if (!$db->rows()) return $search;
?><? module("page:style", 'scroll.css') ?>
<div class="scroll">
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<?
	module('script:scroll');
	$percent	= floor(100/$db->rows());
	
	$db->seek(0);
	while($data = $db->next()){
	$id		= $db->id();
	$url	= getURL($db->url());
?><?  if (beginCompile($data, "advScrollIndex")){ ?>
    <th width="<? if(isset($percent)) echo htmlspecialchars($percent) ?>%"><a href="<? if(isset($url)) echo $url ?>"><?
	$folder	= docTitleImage($id);
    displayThumbImageMask($folder, 'design/maskScroll.png');
	?></a></th>
<?  endCompile($data, "advScrollIndex"); } ?><? } ?>
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
    <? beginAdmin() ?>
    <h3><a href="<? if(isset($url)) echo $url ?>"><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a></h3>
    <? endAdmin($menu, true) ?>
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
    <? if(isset($price)) echo $price ?><? module("bask:button:$id"); ?>
    </td>
<? } ?>
</tr>
</table>
</div>
<? return $search; } ?>