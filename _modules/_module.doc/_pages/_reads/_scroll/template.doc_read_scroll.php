<? function doc_read_scroll(&$db, $val, &$search){ ?>
<link rel="stylesheet" type="text/css" href="scroll.css"/>
<div class="scroll">
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<?
	module('script:scroll');
	
	$db->seek(0);
	while($data = $db->next()){
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
    {{bask:button:$id}}
    </td>
<? } ?>
</tr>
</table>
</div>
<? return $search; } ?>

