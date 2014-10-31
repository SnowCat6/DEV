<?
function doc_read_mapAdmin($db, $val, $search)
{
	if (access('write', 'doc:0'))
	{
		$search[':sortable']	= array(
			'select'=> 'ul',
			'axis'	=> 'y',
			'itemFilter'=> 'a',
			'itemData'	=> 'rel'
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
function showMapTreeAdmin(&$db, $deep, $maxDeep)
{
	if (!$db->rows()) return;
	$ddb	= module('doc');
	$ddb->order	= '`sort`';
	$icon	= '<span class="ui-icon ui-icon-arrowthick-2-n-s" style="float:left"></span>';
	
	echo $deep?'<ul>':'<ul class="menu map">';
	while($data = $db->next())
	{
		$id		= $db->id();
		$name	= htmlspecialchars($data['title']);
		$url	= getURL($db->url());
		echo "<li><a href=\"$url\" rel=\"$id\">$icon$name</a>";
		
		if ($deep < $maxDeep)
		{
			$iid	= $db->id();
			$s		= array('parent'=>$iid, 'type' => 'page,catalog');
			$ddb->open(doc2sql($s));
			
			if ($deep == $maxDeep - 2){
				showMapTreeAdmin($ddb, $deep+1, $maxDeep);
			}else{
				showMapTreeAdmin($ddb, $deep+1, $maxDeep);
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
