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

	if (!$type){
		$db->sortByKey('sort', getValue('documentOrder'), 'doc_type IN ("page", "catalog")');
	}

	$docType = docType($type, 1);
?>
<h1>Список {$docType}</h1>
<?
	$sql	= array();
	$search	= array('type'=>$type);
	doc_sql($sql, $search);
	$db->order = 'sort';
	$db->open($sql);
	
	if ($db->rows() == 0){
		module('message:error', 'Нет документов');
		module('display:message');
	}
	$urlType = $type?"_$type":'';
?>
<form action="{{getURL:page_all$urlType}}" method="post" class="admin ajaxForm ajaxReload">
<table class="table" cellpadding="0" cellspacing="0">
<tbody id="sortable">
<?	
	while($data = $db->next()){
		$id		= $db->id();
		$url	= getURL($db->url());
?>
<tr>
    <td>
<input type="hidden" name="documentOrder[]" value= "{$id}" />
<input type="checkbox" name="documentDelete[]" value="{$id}" />
    </td>
    <td><a href="{{getURL:page_edit_$id}}" id="ajax_edit"><b>{$id}</b></a></td>
    <td width="100%"><a href="{!$url}" id="ajax">{$data[title]}</a></td>
</tr>
<?	} ?>
</tbody>
</table>
<p><input type="submit" class="button" value="Сохранить" /> Все выделенные документы будут удалены</p>
</form>
<?
if (!$type){
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