<?
//	Ссылка на редактирование текстового блока
addUrl('read_edit_(.+)','read_edit');
//	Правило доступа для текстового блока
addAccess('text:(.*)',	'read_access');
addAccess('file:(.*)',	'read_file_access');

//	migratr to new read file storage
makeDir(images . '/reads');
$files	= getFiles(images, 'html');
foreach($files as $path)
{
	$name	= basename($path, '.html');
	copy($path, images . "/reads/$name.html");
	copyFolder(images . "/$name", images . "/reads/$name/");

	unlink($path);
	delTree(images . "/$name");
	
	$ctx	= file_get_contents(images . "/reads/$name.html");
	$ctx	= str_replace(basename(images) . "/$name/", basename(images) . "/reads/$name/", $ctx);
	file_put_contents(images . "/reads/$name.html", $ctx);
}
?>