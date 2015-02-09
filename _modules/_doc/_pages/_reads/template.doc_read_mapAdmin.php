<?
function doc_read_mapAdmin($db, $val, $search)
{
	if (access('write', 'doc:0'))
	{
		$search[':sortable']	= array(
			'select'=> 'ul',
			'axis'	=> 'y',
			'itemFilter'=> 'a',
		);
	}

	m('style:adminMap');
	echo '<div id="adminMap">';

	$url	= getURL('page_all');
	messageBox("Перетащите разделы из <a href=\"$url\" id=\"ajax\">окна редактирования</a> для создания карты сайта. Двигая разделы по карте сайта, отсортируйте их в нужном вам порядке.");
	showMapTreeAdmin($db, 0, 2);

	echo '</div>';
	return $search;
}
function showMapTreeAdmin(&$db, $deep, $maxDeep, $parent = 0)
{
	if (!$db->rows()) return;
	
	$ddb	= module('doc');
	$ddb->order	= '`sort`';
	$icon	= '<span class="ui-icon ui-icon-arrowthick-2-n-s admin_sort_handle" style="float:left"></span>';
	
	echo $deep?'<ul>':'<ul class="menu map">';
	while($data = $db->next())
	{
		$id		= $db->id();
		$name	= htmlspecialchars($data['title']);
		$url	= getURL($db->url());
		$drag	= docDraggableID($id, $data, array('drop_unset[parent]'=>$parent));;
		echo "<li>$icon<a href=\"$url\" sort_index=\"doc:$id\"$drag>$name</a>";
		
		if ($deep < $maxDeep)
		{
			$iid	= $db->id();
			$s		= array('parent'=>$iid, 'type' => 'page,catalog');
			$ddb->open(doc2sql($s));
			
			if ($deep == $maxDeep - 2){
				showMapTreeAdmin($ddb, $deep+1, $maxDeep, $iid);
			}else{
				showMapTreeAdmin($ddb, $deep+1, $maxDeep, $iid);
			}
		}
		echo '</li>';
	}
	echo '</ul>';
}
?>
<? function style_adminMap($val){ ?>
<style>
#adminMap li{
	clear:both;
}
</style>
<? } ?>
