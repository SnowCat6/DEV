<?
function doc_all(&$db, $val, &$data){
	@$type	= $data[1];
	module('script:ajaxLink');
	module('script:ajaxForm');
	
	$documentDelete = getValue('documentDelete');
	if (is_array($documentDelete)){
		foreach($documentDelete as $iid){
			module("doc:update:$iid:delete");
		}
	}

	if ($type){
		$db->sortByKey('sort', getValue('documentOrder'));
	}
	$docType= docType($type, 1);
?>
{{page:title=Список $docType}}
<?
	$sql	= array();
	
	$search	= getValue('search');
	if (!is_array($search)) $search = array();
	$search['type'] = $type;
	
	doc_sql($sql, $search);
	$db->order = 'sort';
	$db->open($sql);
	
	if ($db->rows() == 0){
		module('message:error', 'Нет документов');
		module('display:message');
	}
	$urlType = $type?"_$type":'';
?>
<form action="{{getURL:page_all$urlType}}" method="post" class="form ajaxForm ajaxReload">
<table class="table" cellpadding="0" cellspacing="0" width="100%">
<tr class="search">
  <td colspan="2">Поиск</td>
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
    <td>
<input type="hidden" name="documentOrder[]" value= "{$id}" />
<input type="checkbox" name="documentDelete[]" value="{$id}" />
    </td>
    <td><a href="{{getURL:page_edit_$id}}" id="ajax_edit"><b>{$id}</b></a></td>
    <td width="100%"><a href="{!$url}" id="ajax"{!$drag}>{$data[title]}</a></td>
</tr>
<?	} ?>
</tbody>
</table>
<p><input type="submit" class="button" value="Сохранить" /> Все выделенные документы будут удалены</p>
</form>
<? if ($type){
	module('script:jq_ui');
?>
<script language="javascript" type="text/javascript">
$(function(){
	$( "#sortable" ).sortable();
	$( "#sortable" ).disableSelection();
});
</script>
<? } ?>
<? } ?>