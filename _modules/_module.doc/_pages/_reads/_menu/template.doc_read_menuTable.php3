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
	
	$ids	= array();
	while($db->next()) $ids[] = $db->id();
	$db->seek(0);
	
	$childs	= array();
	$ddb	= module('doc:find', array('parent' => $ids, 'type'=>array('page', 'catalog')));
	while($d = $ddb->next()){
		$iid	= $ddb->id();
		$prop	= module("prop:get:$iid");
		$parent	= (int)$prop[':parent'];
		if ($parent) $childs[$parent][$iid]	= $d;
	}
	
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
if ($c	= &$childs[$id]){
	echo '<ul>';
	foreach($c as $iid => &$data){
//		$ddb->setCacheData($iid, $data);
		$ddb->setData($data);
		$ddb->data	= $data;
		$title		= htmlspecialchars($data['title']);
		$url		= getURL($ddb->url());
		$draggable	= docDraggableID($iid, $data);
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