<? function snippets_all($val, $data)
{
	module('script:clone');
	module('script:ajaxForm');
	
	if (access('write', 'snippets:') &&
		is_array($snippName = getValue('snippetsName')))
	{
		$ini		= readIniFile(localConfigName);
		$snippValue	= getValue('snippetsValue');
		$ini[':snippets'] = array();
		foreach($snippName as $ix => $name){
			if (!$name) continue;
			@$ini[':snippets'][$name] = $snippValue[$ix];
		}
		setIniValues($ini);
	};

	$id		= rand()*10000;
	$ini	= getCacheValue('ini');
?>
{{ajax:template=ajax_edit}}
{{script:jq_ui}}
{{page:title=Все сниппеты}}
{{display:message}}
<form action="{{getURL:snippets_all}}" method="post" class="admin ajaxForm ajaxReload">

<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#snippet1">Встроенные</a></li>

<? if (access('write', 'snippets:')){ ?>
    <li class="ui-corner-top"><a href="#snippet2">Пользовательские</a></li>
<? } ?>

	<li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="snippet1">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th width="30%" nowrap>Название</th>
    <th width="70%">Код</th>
</tr>
  
<? foreach(snippetsWrite::get() as $name => $code){ ?>
<tr>
    <td><?= '['?>[{$name}]<?=']'?></td>
    <td>{$code}</td>
</tr>
<? } ?>

</table>
</div>

<? if (access('write', 'snippets:')){ ?>
<div id="snippet2">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
  <th nowrap>&nbsp;</th>
    <th width="30%" nowrap>Название</th>
    <th width="70%">Код</th>
  </tr>
<?
	@$snippets	= $ini[':snippets'];
	if (!is_array($snippets)) $snippets = array();
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
<p><input type="button" class="button adminReplicateButton" id="addSnippet" value="Добавть сниппет" /></p>
<p>Для показа сниппета в документах напишите <strong>[[название сниппета]]</strong> и при отображении на сайте, он заменится на код, указвнный вами. </p>
<p>Если в качестве кода использовать <strong>{<span>{</span>название модуля=параметры}}</strong>, то будет вызван модуль с заданными параметрами.</p>
<p>К примеру, код модуля <strong>{<span>{</span>doc:searchPage}}</strong> покажет окно поиска по сайту.</p>
</div>
<? } ?>

{{script:adminTabs}}
</div>


</form>
<? } ?>