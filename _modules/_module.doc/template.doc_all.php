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
?>
<h1>Список документов</h1>
<?
	$sql	= array();
	$search	= array('type'=>$type);
	doc_sql($sql, $search);
	$db->open($sql);
	
	if ($db->rows() == 0){
		module('message:error', 'Нет документов');
		module('display:message');
	}
?>
<form action="{{getURL:page_all}}" method="post" class="admin ajaxForm ajaxReload">
<table class="table" cellpadding="0" cellspacing="0">
<?	
	while($data = $db->next()){
		$id		= $db->id();
		$url	= getURL($db->url());
?>
<tr>
<td><input type="checkbox" name="documentDelete[]" value="{$id}" /></td>
<td><a href="{{getURL:page_edit_$id}}" id="ajax_edit"><b>{$id}</b></a></td>
<td width="100%"><a href="{!$url}" id="ajax">{$data[title]}</a></td>
</tr>
<?	} ?>
</table>
<p><input type="submit" class="button" value="Удалить выделенные документы" /></p>
</form>
<? } ?>