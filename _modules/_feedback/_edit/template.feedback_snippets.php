<? function feedback_snippets($val)
{

$n			= '{{feedback:display:';
$nLen		= strlen($n);

foreach(snippetsWrite::getLocal() as $name=>$code)
{
	if(strncmp($n, "$code", $nLen)) continue;
	snippetsWrite::deleteLocal($name);
}

$files		= array();
$adminFiles	= getFiles(cacheRootPath."/feedback/", "txt$");
$userFiles	= getFiles(images."/feedback/", "txt$");

foreach($adminFiles as $name => $path){
	$name = preg_replace('#\..*#', '', $name);
	$files[$name] = $path;
}
foreach($userFiles as $name => $path){
	$name = preg_replace('#\..*#', '', $name);
	$files[$name] = $path;
}
foreach($files as $path)
{
	$form		= readIniFile($path);
	$snippetName= $form[':']['snippetName'];
	$formName	= trim($form[':']['name']);
	snippetsWrite::addLocal($snippetName, "{"."{feedback:display:$formName}"."}");
}

}?>