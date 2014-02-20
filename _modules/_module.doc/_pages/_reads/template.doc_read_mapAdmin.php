<?
function doc_read_mapAdmin($db, $val, $search)
{
	if (!access('write', 'doc:0')) return;
	
	$sort	= getValue('sort');
	if (is_array($sort)){
		$ddb	= module('doc');
		foreach($sort as $parent => $order){
			$order	= explode(',', $order);
			$ddb->sortByKey('sort', $order);
		}
	}
	m('script:adminMap');
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
<? function script_adminMap($val){ m('script:jq_ui'); ?>
<style>
#adminMap li{
	clear:both;
}
</style>
<script>
$(function(){
	$( "#adminMap ul").sortable({
		axis: 'y',
		update: function(e, ui){
			var elm	= new Array();
			var parent	= $(ui.item).parent().parent().find('> a').attr("rel");
			if (undefined == parent) parent = 0;

			var thisElm = $(ui.item).parent().find("> li > a");
			thisElm.each(function(){
				elm.push($(this).attr("rel"));
			});
			$.ajax('{{url:page_map}}', {data: 'sort[' + parent + ']=' + elm.join(',')})
			.done(function(data){
//				alert(data);
			});
		}
	}).disableSelection();
});
</script>
<? } ?>