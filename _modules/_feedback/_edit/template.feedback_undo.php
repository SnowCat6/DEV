<?
function feedback_undo($formName, $data)
{
	if (!access('undo', 'undo')) return;

	$localPath	= images."/feedback/form_$formName.txt";
	$undo		= readIniFile($localPath);
	
	undo::add("'$formName' изменен", "feedback:$formName", array(
		'action'=> "feedback:undo:$formName", 'data'	=> $undo)
	);

	writeIniFile($localPath, $data);
	
	return true;
}?>