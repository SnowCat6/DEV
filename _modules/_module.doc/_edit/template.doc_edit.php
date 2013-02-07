<?
function doc_edit(&$db, $val, $data)
{
	$id		= (int)$data[1];
	$data	= $db->openID($id);
	if (!$data) return;
	
	if (getValue('ajax') == 'reload')
	{
		$s			= getValue('data');
		if ($s['parent']) $s['prop'][':parent'] = $s['parent'];
		
		if (is_array(@$s['prop']))
		{
			$prop		= module("prop:get:$id");
			foreach($s['prop'] as $name => &$val){
				@$v = $prop[$name];
				if (!$v) continue;
				$val = "$val, $v[property]";
			}
			@$s[':property'] = $s['prop'];
			
			module("doc:update:$id:edit", $s);
			module('display:message');
		}
		
		setTemplate('');
		$template	= getValue('template');
		return module("doc:read:$template",  getValue('data'));
	}
	
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
		dataMerge($doc, $data);
		module('prepare:2local', &$doc);
		module('admin:tabUpdate:doc_property', &$doc);
		$iid = module("doc:update:$id:edit", &$doc);

		if ($iid){
			if (!testValue('ajax')) redirect(getURL($db->url($iid)));
			module('message', 'Документ сохранен');
			module('display:message');
			return module("doc:page:$iid");
		}
	}
	
	$docType= docType($data['doc_type']);
	$folder = $db->folder();
	module('prepare:2public', &$data);
	module("editor:$folder");
?>
<h1>Изменить {$docType}</h1>
<form action="<?= getURL("page_edit_$id")?>" method="post" enctype="multipart/form-data" class="admin ajaxForm ajaxReload">
<? module('admin:tab:doc_property', &$data)?>
</form>
<? } ?>