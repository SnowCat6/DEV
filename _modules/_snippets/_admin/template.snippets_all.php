<? function snippets_all($val, $data)
{

	$delete	= getValue('snippetDelete');
	if (access('write', "snippets:$delete") && $delete)
	{
		snippetsWrite::delete($delete);
	};

	$id		= rand()*10000;
	$ini	= getCacheValue('ini');
?>
{{ajax:template=ajax_edit}}
{{script:jq_ui}}
{{script:ajaxForm}}
{{page:title=Все сниппеты}}
{{display:message}}
<form action="{{getURL:snippets_all}}" method="post" class="admin ajaxForm ajaxReload">

<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#snippet1">Все сниппеты</a></li>

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
  
<? foreach(snippetsWrite::get() as $name => $data){ ?>
<tr>
    <td><a href="{{url:admin_snippet_edit=name:$name}}" id="ajax"><?= '['?>[{$name}]<?=']'?></a></td>
    <td>{$data[note]}</td>
</tr>
<? } ?>

</table>
</div>

<? if (access('write', 'snippets:')){ ?>
<div id="snippet2">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th width="30%" nowrap>Название</th>
    <th width="70%">Код</th>
    <th width="70%">&nbsp;</th>
  </tr>
<?
	foreach(snippetsWrite::getUsers() as $name => $data){
?>
<tr>
    <td><a href="{{url:admin_snippet_edit=name:$name}}" id="ajax"><?= '['?>[{$name}]<?=']'?></a></td>
    <td>{$data[note]}</td>
    <td>
<? if(access('write', "snippets:$name")){ ?>
    <a href="{{url:#=snippetDelete:$name}}" id="ajax">удалить</a>
<? } ?>
    </td>
</tr>
<? } ?>
</table>
<p><a href="{{url:admin_snippet_edit}}" class="button" id="ajax">Добавть сниппет</a></p>
<p>Для показа сниппета в документах напишите <strong>[[название сниппета]]</strong> и при отображении на сайте, он заменится на код, указвнный вами. </p>
<p>Если в качестве кода использовать <strong>{<span>{</span>название модуля=параметры}}</strong>, то будет вызван модуль с заданными параметрами.</p>
<p>К примеру, код модуля <strong>{<span>{</span>doc:searchPage}}</strong> покажет окно поиска по сайту.</p>
</div>
<? } ?>

{{script:adminTabs}}
</div>


</form>
<? } ?>