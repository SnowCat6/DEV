<? function mail_templatesEdit($db, $val, $data)
{
	if (!access('write', 'mail:')) return;
//	if (!hasAccessRole('admin,developer,writer')) return;

	$files		= array();
	$adminFiles	= getFiles(cacheRootPath."/mailTemplates");
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
		$undo	= array();
		$undo['plain']	= file_get_contents("$thisPath/$template.txt");
		$undo['html']	= file_get_contents("$thisPath/$template.txt.html");
		$undo['SMS']	= file_get_contents("$thisPath/$template.SMS.txt");
		
		addUndo("'$template' изменен", "mail:$template",
			array('action' => "mail:undo:$template", 'data' => $undo)
		);
		
		moduleEx('prepare:2local', $mailTemplate);
		file_put_contents_safe("$thisPath/$template.txt", 		@$mailTemplate['plain']);
		file_put_contents_safe("$thisPath/$template.txt.html",	@$mailTemplate['html']);
		file_put_contents_safe("$thisPath/$template.SMS.txt",	@$mailTemplate['SMS']);
		
		module("message", 'Шаблон сохранен');
		return module('mail:templates');
	}

	module('script:jq_ui');
	module('script:ajaxLink');
	module('script:ajaxForm');
	module("editor", "$thisPath/$template");
	
	@$plain	= file_get_contents("$path/$template.txt");
	@$html	= file_get_contents("$path/$template.txt.html");
	@$SMS	= file_get_contents("$path/$template.SMS.txt");

	moduleEx('prepare:2public', $plain);
	moduleEx('prepare:2public', $html);
?>
<form action="{{getURL:admin_mailTemplates_$template}}" method="post" class="admin ajaxFormNow ajaxReload">
{{page:title=Шаблон $template}}
{{display:message}}

<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#mailHtml">HTML</a></li>
    <li class="ui-corner-top"><a href="#mailText">Текст</a></li>
    <li class="ui-corner-top"><a href="#mailSMS">СМС</a></li>
	<li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="mailHtml" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<textarea name="mailTemplate[html]" rows="35" class="input w100 editor">{$html}</textarea>
</div>

<div id="mailText" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<textarea name="mailTemplate[plain]" rows="25" class="input w100">{$plain}</textarea>
</div>

<div id="mailSMS" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<div id="editorSMScounter">Всего символов: <span></span></div>
<textarea name="mailTemplate[SMS]" rows="10" class="input w100 editorSMS">{$SMS}</textarea>
</div>

{{script:adminTabs}}
<script>
/*<![CDATA[*/
$(function() {
	$(".editorSMS").keyup(onSMSchange);
	onSMSchange();
});

function onSMSchange(){
	var sms = $(".editorSMS").val();
	$("#editorSMScounter span").text(sms.length);
}
 /*]]>*/
</script>
</div>
<? } ?>