<?
function feedback_undo($formName, $data)
{

	$localPath	= images."/feedback/form_$formName.txt";
	$undo		= readIniFile($localPath);
	
	addUndo("'$formName' изменен", "feedback:$formName", array(
		'action'=> "feedback:undo:$formName", 'data'	=> $undo)
	);

	writeIniFile($localPath, $data);
	
	return true;
}?>