<? function snippets_all($val, $data)
{
	if (!hasAccessRole('admin,developer,writer')) return;

	module('script:clone');
	module('script:ajaxForm');
	
	if (is_array($snippName = getValue('snippetsName')))
	{
		$ini		= readIniFile(localHostPath."/".configName);
		$snippValue	= getValue('snippetsValue');
		$ini[':snippets'] = array();
		foreach($snippName as $ix => $name){
			if (!$name) continue;
			@$ini[':snippets'][$name] = $snippValue[$ix];
		}
		setIniValues($ini);
	};
	
	$ini		= getCacheValue('ini');
	@$snippets	= $ini[':snippets'];
	if (!is_array($snippets)) $snippets = array();
?>
{{page:title=Все сниппеты}}
{{display:message}}
<form action="{{getURL:snippets_all}}" method="post" class="admin ajaxForm ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
  <th nowrap>&nbsp;</th>
    <th width="30%" nowrap>Название</th>
    <th width="70%">Код</th>
  </tr>
<? 

foreach($snippets as $name => $code){
?>
<tr>
    <td><a class="delete" href="">X</a></td>
    <td><input name="snippetsName[]" type="text" class="input w100" value="{$name}" /></td>
    <td><input name="snippetsValue[]" type="text" class="input w100" value="{$code}" /></td>
</tr>
<? } ?>
<tr class="adminReplicate" id="addSnippet">
    <td><a class="delete" href="">X</a></td>
    <td><input name="snippetsName[]" type="text" class="input w100" value="" /></td>
    <td><input name="snippetsValue[]" type="text" class="input w100" value="" /></td>
</tr>
</table>
<p></p>
<div style="float:right"><input type="submit" class="ui-button ui-widget ui-state-default ui-corner-all" value="Сохранить" /></div>
<div><input type="button" class="button adminReplicateButton" id="addSnippet" value="Добавть сниппет" /></div>
<p>Для показа сниппета в документах напишите <strong>[[название сниппета]]</strong> и при отображении на сайте, он заменится на код, указвнный вами. </p>
<p>Если в качестве кода использовать <strong>{<span>{</span>название модуля=параметры}}</strong>, то будет вызван модуль с заданными параметрами.</p>
<p>К примеру, код модуля <strong>{<span>{</span>doc:searchPage}}</strong> покажет окно поиска по сайту.</p>
</form>
<? } ?>