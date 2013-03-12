<?
function doc_read_menu2(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;
	
	$ddb	= module('doc');
	$split	= ' id="first"';
?>
<ul>
<? while($data = $db->next())
{
	$id			= $db->id();
	$draggable	= docDraggableID($id, $data);
	$hasCurrent	= false;
	
	ob_start();
	$ddb->open(doc2sql(array('parent' => $id)));
	if ($ddb->rows()){
		$split2	= ' id="first"';
		echo '<ul>';
		while($d = $ddb->next()){
			$iid	= $ddb->id();
			$class	= currentPage() == $iid?' class="current"':'';
			$hasCurrent |= $class != '';
			$url	= getURL($ddb->url());
?>
	<li {!$split2}{!$class}><a href="{!$url}">{$d[title]}</a></li>
<?			$split2	= '';
		}
		echo '</ul>';
	}
	$p = ob_get_clean();
	
    $url	= getURL($db->url());
	if (currentPage() == $id){
		$class	= ' class="current"';
	}else{
		$class	= $p && $hasCurrent?' class="current parent"':'';
	}
?>
<li {!$class}{!$split}>
<a href="{$url}" title="{$data[title]}"{!$draggable}>{$data[title]}</a>{!$p}
</li>
<? $split = ''; } ?>
</ul>
<? return $search; } ?>