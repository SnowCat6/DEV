<? function mail_templatesEdit($db, $val, $data)
{
	$files		= array();
	$adminFiles	= getFiles(localCacheFolder."/siteFiles/mailTemplates");
	$userFiles	= getFiles(images."/mailTemplates");
	
	foreach($adminFiles as $name => $path){
		$name = preg_replace('#\..*#', '', $name);
		$files[$name] = $path;
	}
	foreach($userFiles as $name => $path){
		$name = preg_replace('#\..*#', '', $name);
		$files[$name] = $path;
	}
	
	@$template	= $data[1];
	@$path		= dirname($files[$template]);
	$thisPath	= images."/mailTemplates";
	
	if (is_array($mailTemplate = getValue('mailTemplate')))
	{
		module('prepare:2local', &$mailTemplate);
		file_put_contents_safe("$thisPath/$template.txt", 		@$mailTemplate['plain']);
		file_put_contents_safe("$thisPath/$template.txt.html",	@$mailTemplate['html']);
		module("message", 'Шаблон сохранен');
		return module('mail:templates');
	}

	module('script:jq_ui');
	module('script:ajaxLink');
	module('script:ajaxForm');
	module("editor:$thisPath/$template");
	
	@$plain	= file_get_contents("$path/$template.txt");
	@$html	= file_get_contents("$path/$template.txt.html");

	module('prepare:2public', &$plain);
	module('prepare:2public', &$html);
?>
<form action="{{getURL:admin_mailTemplates_$template}}" method="post" class="admin ajaxFormNow ajaxReload">
{{page:title=Шаблон $template}}
{{display:message}}

<div id="mailTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#mailText">Текст</a></li>
    <li class="ui-corner-top"><a href="#mailHtml">HTML</a></li>
	<li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="mailText" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<textarea name="mailTemplate[plain]" rows="25" class="input w100">{$plain}</textarea>
</div>

<div id="mailHtml" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<textarea name="mailTemplate[html]" rows="35" class="input w100 editor">{$html}</textarea>
</div>
<script>
$(function() {
	$("#mailTabs").tabs();
});
</script>
</div>
<? } ?>