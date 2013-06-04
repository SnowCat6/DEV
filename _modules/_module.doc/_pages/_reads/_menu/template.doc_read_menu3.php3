<?
function doc_read_menu3(&$db, $val, &$search)
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
	$ddb->open(doc2sql(array('parent' => $id, 'type' => @$search['type'])));
	if ($ddb->rows()){
		$split2	= ' id="first"';
		echo '<ul>';
		while($d = $ddb->next())
		{
			$iid	= $ddb->id();
			$class	= currentPage() == $iid?' class="current"':'';
			$hasCurrent |= $class != '';
			$url	= getURL($ddb->url());
			
			$s	= array('parent'=>$iid, 'type' => @$search['type']);
			$p3	= m("doc:read:menu", $s);
?>
	<li {!$split2}{!$class}><a href="{!$url}">{$d[title]}</a>{!$p3}</li>
<?			$split2	= '';
		}
		echo '</ul>';
	}
	$p2	 = ob_get_clean();
    $url= getURL($db->url());
	if (currentPage() == $id){
		$class	= ' class="current"';
	}else{
		$class	= $p2 && $hasCurrent?' class="parent"':'';
	}
?>
<li {!$class}{!$split}>
<a href="{$url}" title="{$data[title]}"{!$draggable}>{$data[title]}</a>{!$p2}
</li>
<? $split = ''; } ?>
</ul>
<? return $search; } ?>
