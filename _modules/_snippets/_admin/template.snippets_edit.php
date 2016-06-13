<? function snippets_edit($val, $data)
{
	$name	= getValue('name');
	if (!access('write', "snippets:$name"))
		return;
		
	$snippet	= getValue('snippet');
	if (is_array($snippet))
	{
		$newName	= getValue('newName');
		if ($newName){
			if ($name != $newName) snippetsWrite::delete($name);
			snippetsWrite::add($newName, $snippet);
			messageBox('Данные сохранены');
			$name	= $newName;
		}else{
			messageBox('Введите название сниппета', true);
		}
	}
	
	$snippets	= snippetsWrite::get();
	$data		= $snippets[$name];
	if (!$data) $data = array();
	$n			= htmlspecialchars($name);
?>
{{script:ajaxForm}}
{{ajax:template=ajax_edit}}
{{page:title=Редактирование сниппета $n}}

<form style="padding-right:15px" action="{{url:admin_snippet_edit}}" method="post" class="ajaxForm ajaxReload">
    <input type="hidden" name="name" value="{$name}" />
    <module:admin:tab:snippetTab @="$data" />    
</form>
<? } ?>

<?
//	+function snippetTab_main
function snippetTab_main($data){
	$name	= getValue('name');
?>
<div class="snippetAdminLine">
    <b>Название сниппета</b>
    <label style="float:right">
    	<input type="hidden" name="snippet[hidden]" value="" />
    	<input type="checkbox" name="snippet[hidden]" {checked:$data[hidden]} />Отключить
    </label>
</div>
    <input type="text" name="newName" value="{$name}" class="input w100" />
    <b>Комментарий</b>
    <input type="text" name="snippet[note]" value="{$data[note]}" class="input w100" />
    <b>Код сниппета</b>
    <textarea name="snippet[code]" class="w100" rows="25" style="padding:5px">{$data[code]}</textarea>
<? return 'Редактирование сниппета'; } ?>

<?
//	+function snippetTab_notepad
function snippetTab_notepad($data){?>
    <textarea name="snippet[notepad]" class="w100" rows="25" style="padding:5px">{$data[notepad]}</textarea>
<? return 'Заметки'; } ?>