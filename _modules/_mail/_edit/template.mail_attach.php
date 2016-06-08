<?
function mail_attach($db, $val, $data)
{
	$id		= getValue('id');
	$data	= $db->openID($id);
	if (!$data) return;
	
	$fileName	= getValue('fileName');
	$binaryData	= $data['document'][':attach64'][$fileName];
	if (!$binaryData) return;
	
	setTemplate('');

	$mime	= cmsMail::mimeType($fileName);
	header("Content-Type: $mime");
//	header("Content-Disposition: attachment; filename=\"$fileName\"");
	echo base64_decode($binaryData);
}
?>