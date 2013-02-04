<?
function doc_add(&$db, $val, $data)
{
	$baseDocumentTitle = '';
	@$id	= (int)$data[1];
	if ($id){
		$dataParent = $db->openID($id);
		if (!$dataParent) return module('message:error', 'Нет главного документа');
		$baseDocumentTitle = " к: $dataParent[title]";
	}

	$type	= getValue('type');
	$doc	= getValue('doc');
	
	if (is_array($doc) && $type)
	{
		module('prepare:2local', &$doc);
		module('admin:tabUpdate:doc_property', &$doc);
		if ($id) $doc[':property'][':parent'] = $id;
		$iid = module("doc:update:$id:add:$type", $doc);
		//	document added
		if ($iid){
			if (!testValue('ajax')) redirect(getURL($db->url($iid)));
			module('message', 'Документ создан');
			module('display:message');
			return module("doc:page:$iid");
		}
	}else{
		$doc = array();
	}
	
	$docType			= docType($type);
	$data				= $doc;
	$data['doc_type']	= $type;
	$folder				= $db->folder();
	module('prepare:2public', &$data);
	module("editor:$folder");
?>
<h1>Создать новый {$docType}{$baseDocumentTitle}</h1>
{{display:message}}
<form action="<?= getURL("page_add_$id", "type=$type")?>" method="post" class="admin ajaxForm ajaxReload">
<? module('admin:tab:doc_property', &$data)?>
</form>
<? } ?>