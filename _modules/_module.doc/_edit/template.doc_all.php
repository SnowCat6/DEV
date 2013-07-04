<?
function doc_all(&$db, $val, &$data)
{
	@$type	= $data[1];
	module('script:ajaxLink');
	module('script:ajaxForm');
	module('script:jq_ui');
	
	$documentDelete = getValue('documentDelete');
	if (is_array($documentDelete)){
		foreach($documentDelete as $iid){
			module("doc:update:$iid:delete");
		}
	}

	$db->sortByKey('sort', getValue('documentOrder'), getValue('page')*15);

	$docType= docType($type, 1);
	$db2	= module('doc');
?>
{{page:title=Список $docType}}
<?
	$sql	= array();
	
	$search	= getValue('search');
	if (!is_array($search)) $search = array();
	$search['type'] = $type?$type:'page,catalog';
	if ($template = getValue('template')) $search['template'] = $template;
	
	doc_sql($sql, $search);
	
	if (getValue('documentDeleteAll') == 'yes'){
		$db->open($sql);
		while($db->next()){
			$id = $db->id();
			m("doc:update:$id:delete");
		}
		m('page:display:!message', '');
	}
	
	$db->order = '`sort`';
	$db->open($sql);

	$rows	= $db->rows();
	if ($rows == 0){
		module('message:error', 'Нет документов');
		module('display:message');
	}
	$urlType= $type?"_$type":'';
	$page	= getValue('page');
?>
<form action="{{getURL:page_all$urlType}}" method="post" class="form ajaxForm ajaxReload">
<input type="hidden" name="page" value="{$page}" />
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><input type="submit" class="button" value="Сохранить" /></td>
    <td width="100%">Все выделенные документы будут удалены</td>
    <td align="right" nowrap="nowrap">Удалить все видимые документы </td>
    <td><input name="documentDeleteAll" type="checkbox" value="yes" /></td>
  </tr>
</table>
<?= $p = dbSeek($db, 15, array('search' => $search)) ?>
<table class="table all" cellpadding="0" cellspacing="0" width="100%">
<tr class="search">
    <td colspan="3">Поиск</td>
    <td><input type="text" name="search[title]" value="{$search[title]}" class="input w100" /></td>
</tr>
<tbody id="sortable">
<?	
	while($data = $db->next()){
		$id		= $db->id();
		$url	= getURL($db->url());
		$drag	= docDraggableID($id, $data);
?>
<tr>
  <td><div  class="ui-icon ui-icon-arrowthick-2-n-s"></div></td>
    <td>
<input type="hidden" name="documentOrder[]" value= "{$id}" />
<input type="checkbox" name="documentDelete[]" value="{$id}" />
    </td>
    <td><a href="{{getURL:page_edit_$id}}" id="ajax_edit"><b>{$id}</b></a></td>
    <td width="100%">
    <a href="{!$url}" id="ajax"{!$drag}>{$data[title]}</a>
    <div><small><?
$split	= '';
$parents = getPageParents($id);
foreach($parents as $iid){
	$d		= $db2->openID($iid);
	$url	= $db2->url($iid);
	$drag	= docDraggableID($iid, $d);
?>
{!$split}<a href="{{getURL:$url}}" id="ajax"{$drag}>{$d[title]}</a>
<? $split = ' &gt; '; } ?></small></div>
    </td>
</tr>
<?	} ?>
</tbody>
</table>
{!$p}
</form>
<script language="javascript" type="text/javascript">
$(function(){
	$( "#sortable" ).sortable({axis: 'y'}).disableSelection();
});
</script>
<? } ?>