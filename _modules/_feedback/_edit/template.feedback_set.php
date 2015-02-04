<?
function feedback_set($formName, $form)
{
	if (!access('write', "feedback:$formName")) return;

	$undo	= feedback_get($formName, $data);

	addUndo("'$formName' изменен", "feedback:$formName", array(
		'action'=> "feedback:undo:$formName", 'data'	=> $undo)
	);

	writeIniFile(images."/feedback/form_$formName.txt", $form);
	setCacheValue("form_$formName", $form);
	m('feedback:snippets');
}
?>