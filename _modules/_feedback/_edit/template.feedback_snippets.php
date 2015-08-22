<? function feedback_snippets($val)
{
	
$snippets	= new snippetsWrite();
$n			= '{{feedback:display:';
$nLen		= strlen($n);

$snippets2	= $snippets->getLocal();
foreach($snippets2 as $name=>$code)
{
	if(strncmp($n, $code, $nLen)) continue;
	$snippets->deleteLocal($name);
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
	$snippets->addLocal($snippetName, "{"."{feedback:display:$formName}"."}");
}

}?>