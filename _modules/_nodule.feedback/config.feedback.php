<?
addUrl('feedback_all',					'feedback:all');
addUrl('feedback_edit_([a-zA-Z\d]+)',	'feedback:edit');
addUrl('feedback_edit',					'feedback:edit:new');
addUrl('feedback_([a-zA-Z\d]+)',		'feedback:display');

addEvent('admin.tools.settings',	'feedback:tools');
addAccess('feedback:(.*)',			'feedback_access');

$files		= array();
$adminFiles	= getFiles(localCacheFolder."/siteFiles/feedback/", "txt$");
$userFiles	= getFiles(images."/feedback/", "txt$");

foreach($adminFiles as $name => $path){
	$name = preg_replace('#\..*#', '', $name);
	$files[$name] = $path;
}
foreach($userFiles as $name => $path){
	$name = preg_replace('#\..*#', '', $name);
	$files[$name] = $path;
}
foreach($files as $path){
	$form		= readIniFile($path);
	$snippetName= $form[':']['snippetName'];
	$formName	= trim($form[':']['name']);
	if ($snippetName) addSnippet($snippetName, "{"."{feedback:$formName}"."}");
}

?>