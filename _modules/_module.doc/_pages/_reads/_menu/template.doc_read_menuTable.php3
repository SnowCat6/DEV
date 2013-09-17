<?
function doc_read_menuTable_beginCache(&$db, $val, &$search)
{
	module('script:menu');
	if (userID()) return;
	$search['currentPage']	= currentPage();
	return	 hashData($search);
}
function doc_read_menuTable(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;

	$percent= floor(100/$db->rows());
	$ddb	= module('doc');
	$split	= ' id="first"';
	module('script:menu');
?>
<table class="menu popup" cellpadding="0" cellspacing="0" width="100%">
<tr>
<? while($data = $db->next()){
	$id			= $db->id();
    $url		= getURL($db->url());
	$class		= currentPage() == $id?' class="current"':'';
	$draggable	= docDraggableID($id, $data);
?>
<td {!$class}{!$split} width="{$percent}%">
<a href="{$url}"{!$draggable} title="{$data[title]}">{$data[title]}</a>
<?
$split	= ' id="first"';
$ddb->open(doc2sql(array('parent' => $id, 'type'=>array('page', 'catalog'))));
if ($ddb->rows()){
	echo '<ul>';
	while($data = $ddb->next()){
		$id			= $ddb->id();
		$title		= htmlspecialchars($data['title']);
		$url		= getURL($ddb->url());
		$draggable	=docDraggableID($id, $data);
		echo "<li$split><a href=\"$url\"$draggable>$title</a></li>";
	}
	echo '</ul>';
}
$split = '';
?>
</td>
<? } ?>
</tr>
</table>
<? return $search; } ?>