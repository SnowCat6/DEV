<?
function doc_add(&$db, $val, $data)
{
	$template			= '';
	$baseDocumentTitle	= '';
	$type	= getValue('type');
	$doc	= getValue('doc');

	$id	= (int)$data[1];
	if (!access('add', "doc:$id:$type")) return;

	if ($id){
		$dataParent = $db->openID($id);
		if (!$dataParent) return module('message:error', 'Нет родительского документа');
		$baseDocumentTitle	= " к: $dataParent[title]";
		$template			= $dataParent['template'];
	}else{
		$template			= getValue('template');
	}

	if (is_array($doc) && $type)
	{
		$doc['doc_type'] = $type;
		if (!isset($doc['template'])) $doc['template']	= $template;
		moduleEx('prepare:2local', $doc);
		moduleEx("admin:tabUpdate:doc_property:$template", $doc);

		$iid = moduleEx("doc:update:$id:add:$type", $doc);
		//	document added
		if ($iid){
			m('doc:recompile');
			memClear();	
			/*if (!testValue('ajax'))*/
			module("redirect", getURL($db->url($iid)));
/*
			module('message', 'Документ создан');
			module('display:message');
			currentPage($iid);
			return module("doc:page:$iid");
*/		}
		$data= $doc;
	}else{
		$doc = array();
		$data['template']	= $template;
		$data['visible']	= 1;
	}

	$docType			= docTypeEx($type, $template);
	$data['doc_type']	= $type;

	$folder				= $db->folder();
	moduleEx('prepare:2public', $data);
	module("editor", $folder);
?>
{{ajax:template=ajax_edit}}
{{page:title=Добавить $docType $baseDocumentTitle}}
{{display:message}}
<form action="<?= getURL("page_add_$id", "type=$type")?>" method="post" class="admin">
<? moduleEx("admin:tab:doc_property:$template", $data)?>
</form>
<? } ?>