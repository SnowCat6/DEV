<?
function doc_read_menu2(&$db, $val, &$search)
{
	if (!$db->rows()) return $search;
	
	$bUseID	= $val == 'id';
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
	if ($ddb->rows())
	{
		$split2	= ' id="first"';
		echo '<ul>';
		while($d = $ddb->next()){
			$iid	= $ddb->id();
			$class	= currentPage() == $iid?'current ':'';
			$hasCurrent |= $class != '';
			if ($bUseID)$class .= "m$iid ";
			@$fields	= $d['fields'];
			if (@$c	= $fields['class']) $class .= " $c";
			if ($class)	$class = " class=\"$class\"";
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
		$class	= 'current ';
	}else{
		$class	= $p && $hasCurrent?'parent ':'';
	}
	if ($bUseID) $class .= "m$id ";
	@$fields	= $data['fields'];
	if (@$c	= $fields['class']) $class .= " $c";
	if ($class) $class = " class=\"$class\"";
?>
<li {!$class}{!$split}>
<a href="{$url}" title="{$data[title]}"{!$draggable}>{$data[title]}</a>
{!$p}
</li>
<? $split = ''; } ?>
</ul>
<? return $search; } ?>
