<?
function doc_edit(&$db, $val, $data)
{
	$id		= (int)$data[1];
	$data	= $db->openID($id);
	if (!$data) return;
	
	
	$bAjax = testValue('ajax');
	if (testValue('delete')){
		$url = getURL("page_edit_$id", 'deleteYes');
		echo "<h1>Удаление документа</h1>";
		module('message', "Удалить? <a href=\"$url\" id=\"popup\">подтверждаю</a>");
		module('display:message');
		module('script:ajaxLink');
		return;
	}
	if (testValue('deleteYes')){
		return module("doc:update:$id:delete");
	}
	
	$doc	= getValue('doc');
	if (is_array($doc))
	{
		$doc['doc_id']	= $id;
		if (!isset($doc['doc_type'])) $doc['doc_type']	= $data['doc_type'];
		if (!isset($doc['template'])) $doc['template']	= $data['template'];
		$template		= $doc['template'];

		moduleEx('prepare:2local', $doc);
		moduleEx("admin:tabUpdate:doc_property:$template", $doc);

		if (getValue('saveAsCopy') == 'doCopy'){
			$iid = moduleEx("doc:update:$id:copy", $doc);
		}else{
			$iid = moduleEx("doc:update:$id:edit", $doc);
		}

		if ($iid){
			if (!testValue('ajax')) redirect(getURL($db->url($iid)));
			module('message', 'Документ сохранен');
			module('display:message');
			currentPage($iid);
			return module("doc:page:$iid");
		}
	}
	
	$template	= $data['template'];
	$docType	= docType($data['doc_type']);
	$folder		= $db->folder();
	moduleEx('prepare:2public', $data);
	module("editor:$folder");
?>
<? $module_data = array(); $module_data[] = "Изменить $docType"; moduleEx("page:title", $module_data); ?>
<? module("display:message"); ?>
<form action="<?= getURL("page_edit_$id")?>" method="post" enctype="multipart/form-data" class="admin ajaxForm ajaxReload">
<? moduleEx("admin:tab:doc_property:$template", $data)?>
</form>
<? } ?>