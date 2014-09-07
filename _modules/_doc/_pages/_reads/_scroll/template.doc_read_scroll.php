<? function doc_read_scroll(&$db, $val, &$search){
	if (!$db->rows()) return $search;
?>
<link rel="stylesheet" type="text/css" href="css/scroll.css"/>
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
?>
    <th width="184">
    <a href="{!$url}">{{doc:titleImage:$id:mask=mask:design/maskScroll.png;hasAdmin:bottom}}</a>
    </th>
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
    <td width="184">
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

