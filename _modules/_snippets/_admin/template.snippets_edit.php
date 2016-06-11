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
			snippetsWrite::delete($name);
			snippetsWrite::add($newName, $snippet);
			messageBox('Данные сохранены');
			$name	= $newName;
		}else{
			messageBox('Введите название сниппета', true);
		}
	}
	
	$snippets	= snippetsWrite::get();
	$data		= $snippets[$name];
	$n			= htmlspecialchars($name);
?>
{{script:ajaxForm}}
{{ajax:template=ajax_edit}}
{{page:title=Редактирование сниппета $n}}

<form style="padding-right:15px" action="{{url:admin_snippet_edit}}" method="post" class="ajaxForm ajaxReload">
	<input type="hidden" name="name" value="{$name}" />
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
    <td width="100%" style="padding-right:20px">
        <b>Название сниппета</b>
        <input type="text" name="newName" value="{$name}" class="input w100" />
    </td>
    <td>
        <input type="submit" class="button" value="Сохранить" />
    </td>
    </tr>
    </table>
    <b>Комментарий</b>
    <input type="text" name="snippet[note]" value="{$data[note]}" class="input w100" />
    <b>Код сниппета</b>
    <textarea name="snippet[code]" class="w100" rows="25" style="padding:5px">{$data[code]}</textarea>
</form>
<? } ?>
