﻿<?
function doc_editable($db, $val, &$data)
{
	if ($val == 'edit') return  doc_editableEdit($db, $data);

	list($id, $name) = explode(':', $val, 2);
	if (!$name) return;

	$id		= alias2doc($id);
	$data	= $db->openID($id);

	if (!$data) return;

	$menu	= array();
	if (access('write', "doc:$id")){
		$menu['Изменить#ajax_edit']	= getURL("page_edit_$id"."_$name");
	}
	
	beginAdmin();
	if (beginCompile($data, "editable_$name"))
	{
		$doc	= $data['fields'];
		$doc	= $doc['any'];
		echo $doc["editable_$name"];
		endCompile($data);
	}
	endAdmin($menu);
}

function doc_editableEdit($db, &$data)
{
	$id		= $data[1];
	$name	= $data[2];
	if (!$name) return;
	if (!access('write', "doc:$id")) return;
	
	$doc	= getValue('doc');
	if (is_array($doc))
	{
		mEx('prepare:2local', $doc);
		$d		= array();
		$d['fields']['any']["editable_$name"]	= $doc["editable_$name"];
		$iid	= moduleEx("doc:update:$id:edit", $d);
		if ($iid){
			m("doc:clear:$id");
			redirect(getURL($db->url($id)));
		}
	}
	
	$data	= $db->openID($id);
	$folder	= $db->folder();
	$url	= "page_edit_$id"."_$name";
	
	m('page:title', "Изменить $name");
	mEx('prepare:2public', $data);
	module("editor", $folder);
?>
<form method="post" action="{{url:$url}}" class="admin ajaxForm ajaxReload pageEdit">
{{display:message}}
{{editor:images:document=$folder/Image;$folder/Gallery}}
<div><textarea name="doc[editable_{$name}]" cols="" rows="35" class="input w100 editor">{$data[fields][any][editable_$name]}</textarea></div>
</form>
<? } ?>