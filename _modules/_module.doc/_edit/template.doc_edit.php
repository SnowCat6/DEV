<?
function doc_edit(&$db, $val, $data)
{
	$id		= (int)$data[1];
	$data	= $db->openID($id);
	if (!$data) return;
	
	$bAjax = testValue('ajax');
	if (testValue('delete')){
		$url = getURL("page_edit_$id", 'deleteYes');
		module('message', "Удалить? <a href=\"$url\" id=\"popup\">подтверждаю</a>");
		module('display:message');
		module('script:popupWindow');
		return;
	}
	if (testValue('deleteYes')){
		return module("doc:update:$id:delete");
	}
	
	$doc	= getValue('doc');
	if (is_array($doc))
	{
		dataMerge($doc, $data);
		module('prepare:2local', &$doc);
		module('admin:tabUpdate:doc_property', &$doc);
		$iid = module("doc:update:$id:edit", &$doc);
		//	document added
		if ($bAjax){
			if ($iid) module('message', 'Документ записан');
			return;
		}
		if ($iid) redirect(getURL($db->url($iid)));
	}
	
	$folder = $db->folder();
	module('prepare:2public', &$data);
	module("editor:$folder");
?>
<form action="<?= getURL("page_edit_$id")?>" method="post" class="ajaxForm">
<? module('admin:tab:doc_property', &$data)?>
</form>
<? } ?>