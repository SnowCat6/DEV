<?
function mail_undo($db, $template, $data)
{
	if (!access('undo', 'undo')) return;

	$thisPath	= images."/mailTemplates";
	
	$undo	= array();
	$undo['plain']	= file_get_contents("$thisPath/$template.txt");
	$undo['html']	= file_get_contents("$thisPath/$template.txt.html");
	$undo['SMS']	= file_get_contents("$thisPath/$template.SMS.txt");
	
	undo::add("'$template' изменен", "mail:$template",
		array('action' => "mail:undo:$template", 'data' => $undo)
	);

	file_put_contents_safe("$thisPath/$template.txt", 		@$data['plain']);
	file_put_contents_safe("$thisPath/$template.txt.html",	@$data['html']);
	file_put_contents_safe("$thisPath/$template.SMS.txt",	@$data['SMS']);
	
	return true;
}
?>