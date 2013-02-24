<?
function doc_read_menuTree(&$db, $val, &$search){
	if (!$db->rows()) return $search;
	$ddb = module('doc');
?>
<ul>
<? while($data = $db->next())
{
	$id			= $db->id();
	$hasCurrent	= false;
	
	ob_start();
	$ddb->open(doc2sql(array('parent' => $id)));
	if ($ddb->rows()){
		echo '<ul>';
		while($d = $ddb->next()){
			$iid	= $ddb->id();
			$title	= htmlspecialchars($d['title']);
			$class	= currentPage() == $iid?' class="current"':'';
			$hasCurrent |= $class != '';
			$url	= getURL($ddb->url());
			$split	= $ddb->ndx == 1?' id="first"':'';

			echo "<li$split$class><a href=\"$url\">$title</a></li>";
		}
		echo '</ul>';
	}
	$p = ob_get_clean();
	
    $url	= getURL($db->url());
	$split	= $db->ndx == 1?' id="first"':'';
	if (currentPage() == $id){
		$class		= ' class="current"';
	}else{
		$class = $p && $hasCurrent?' class="current parent"':'';
	}

	$draggable =docDraggableID($id, $data);
?>
<li {!$class}{!$split}>
<a href="{$url}" title="{$data[title]}"{!$draggable}>{$data[title]}</a>
{!$p}
</li>
<? } ?>
</ul>
<? return $search; } ?>