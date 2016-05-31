<? function doc_editable(&$db, &$val, &$data)
{
	if (!is_array($data))	$data = array();
	if ($val == 'edit')		return  doc_editableEdit($db, $data);

	list($id, $name) = explode(':', $val, 2);
	if (!$name) $name = 'note';
	
	$id		= alias2doc($id);
	$d		= $db->openID($id);
	if (!$d) return;
	
	$menu	= array();
	if (access('edit', "doc:$id")){
		$menu	= $data['adminMenu'];
		if (!is_array($menu)) $menu = array();
		$menu['Изменить блок#ajax_edit']	= getURL("page_edit_$id"."_$name");
		$menu	= doc_menu_inlineEx($menu, $d, "fields.any.editable_$name");
	}

	beginAdmin($menu);
	if (beginCompile($d, "editable_$name"))
	{
		$ctx	= $d['fields'];
		$ctx	= $ctx['any'];
		$ctx	= $ctx["editable_$name"];
		if (!$ctx) $ctx	= $data['default'];

		$fn		= $data['fn'];
		if (function_exists($fn)) $fn($ctx);

		$fx		= $data['fx'];
		if ($fx) $ctx	= module("text:$fx", $ctx);

		show($ctx);

		endCompile();
	}
	endAdmin();
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
			module("redirect", getURL($db->url($id)));
		}
	}
	
	$data	= $db->openID($id);
	$url	= "page_edit_$id"."_$name";
	$folder			= $db->folder();
	$uploadFolders	= array("$folder/Title", "$folder/Image");
	
	m('page:title', "Изменить $name");
	mEx('prepare:2public', $data);
	module("editor", $folder);
	
	$docName	= "doc[editable_$name]";
?>
<form method="post" action="{{url:$url}}" class="admin ajaxForm ajaxReload pageEdit">
{{display:message}}

<div class="adminEditTools">
{{editor:tools:doc[document]=folder:$uploadFolders}}
</div>
<div>
	<textarea name="{$docName}" {{editor:data:$folder}} cols="" rows="35" class="input w100 editor">{$data[fields][any][editable_$name]}</textarea>
</div>
</form>
<? } ?>