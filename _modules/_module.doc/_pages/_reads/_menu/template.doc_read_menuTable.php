<?
function doc_read_menuTable_beginCache(&$db, $val, &$search)
{
	if (userID()) return;
	$search['currentPage']	= currentPage();
	return	 hashData($search);
}
function doc_read_menuTable(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;
	
	$percent= floor(100/$db->rows());
	$split	= ' id="first"';
?>
<table class="menu" cellpadding="0" cellspacing="0" width="100%">
<tr>
<? while($data = $db->next()){
	$id			= $db->id();
    $url		= getURL($db->url());
	$class		= currentPage() == $id?' class="current"':'';
	$draggable	= docDraggableID($id, $data);
?>
<td {!$class}{!$split} width="{$percent}%">
<a href="{$url}"{!$draggable} title="{$data[title]}">{$data[title]}</a>
</td>
<? } ?>
</tr>
</table>
<? return $search; } ?>