<?
function doc_edit(&$db, $val, $data)
{
	$id		= (int)$data[1];
	if (!access('write', "doc:$id")) return;
	
	$data	= $db->openID($id);
	if (!$data) return;
	
	$bAjax = testValue('ajax');
	if (testValue('delete')){
		$url = getURL("page_edit_$id", 'deleteYes');
		echo "<h1>Удаление документа</h1>";
		m('message', "Удалить? <a href=\"$url\" id=\"popup\">подтверждаю</a>");
		module('display:message');
		m('script:ajaxLink');
		return;
	}
	if (testValue('deleteYes')){
		m("doc:update:$id:delete");
		module('display:message');
		return;
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
			memClear();	
			if (!testValue('ajax')) redirect(getURL($db->url($iid)));
			module('message', 'Документ сохранен');
			module('display:message');
			currentPage($iid);
			return module("doc:page:$iid");
		}
	}
	
	$template	= $data['template'];
	$docType	= docTypeEx($data['doc_type'], $data['template']);
	$folder		= $db->folder();
	moduleEx('prepare:2public', $data);
	module("editor:$folder");
?>
{{page:title=$docType}}
{{display:message}}
<form action="<?= getURL("page_edit_$id")?>" method="post" enctype="multipart/form-data" class="admin ajaxForm ajaxReload">
<? moduleEx("admin:tab:doc_property:$template", $data)?>
</form>
<? } ?>