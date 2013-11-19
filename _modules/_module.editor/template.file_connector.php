<?
/*
IN Command
[GetFolders] - Type,ServerPath
	<Folders>
	<Folder name="" />
	</Folders>
[GetFoldersAndFiles] - Type,ServerPath
[CreateFolder] - Type,ServerPath
[FileUpload]
*/
function file_connector($val)
{
	noCache();
	if ($val == 'fck') return	FCKFinderConnector();
	if ($val == 'fck2') return	FCKFinderConnector();

	$Type			= getValue('Type'); 
	$Command		= getValue('Command');
	$ServerPath		= getValue('ServerPath'); 
	$CurrentFolder	= getValue('CurrentFolder');
	$NewFolderName	= getValue('NewFolderName'); 

	$CurrentFolder = normalFilePath($CurrentFolder);
	
	$path = trim($ServerPath, '/');
	$path = trim("$path/$Type/$CurrentFolder", '/');
	$path = normalFilePath($path);
	
	setTemplate('');
	
	$xml = array(
	'Connector'=>array(
		'CurrentFolder'=>array(
			'@path'=>$CurrentFolder,
			'@url'=>globalRootURL."/$path/"
		),
		'@command'=>$Command,
		'@resourceType'=>$Type,
		)
	);
	
	switch($Command){
	/* FCKEditor commands */
	case 'GetFolders':
		$xml['Connector']['Folders']=getFileFolders($path);
		break;
	case 'GetFoldersAndFiles':
		$xml['Connector']['Folders']=getFileFolders($path);
		$xml['Connector']['Files']	=getFileFiles($path);
		break;
	case 'CreateFolder':
		$xml['Connector']['Error']	=getFileCreateFolder($path, $NewFolderName);
		break;
	case 'FileUpload':
		@$tmpName	= $_FILES['NewFile']['tmp_name'];
		@$fileName	= $_FILES['NewFile']['name'];
		$errorNumber= getFileUpload($path, $tmpName, $fileName);
		$message	= str_replace( '"', '\\"', $fileName );
		echo '<script type="text/javascript">';
		echo "window.parent.frames[\"frmUpload\"].OnUploadCompleted($errorNumber,\"$message\") ;";
		echo '</script>';
		return;
	default:
		return;
	}
	moduleEx('xmlWrite', $xxml);
}
function getFileFolders($path){
	$xml	= array();
	$files 	= getDirs($path, '');
	foreach($files as $file => $path){
		$xml[]['Folder'] = array('@name' => $file);
	}
	return $xml;
}
function getFileFiles($path){
	$xml	= array();
	$files 	= getFiles($path, '');
	foreach($files as $file => $path){
		$xml[]['File'] = array('@name' => $file, '@size'=>round(filesize($path)/1024));
	}
	return $xml;
}
function getFileCreateFolder($path, $newFolder){
	if (!$newFolder)					return array('@number'=>102, '@originalDescription'=>'No folder name!');
	if (!is_writable($path))	return array('@number'=>103, '@originalDescription'=>'Write denied!');
	
	$newFolder	= makeFileName($newFolder, true);
	$path		= normalFilePath("$path/$newFolder");
	if (is_file($path)) 		return array('@number'=>110, '@originalDescription'=>'File exists!');
	if (!canEditFile($path))	return array('@number'=>103, '@originalDescription'=>'Write denied!');
	
	makeDir($path);
	if (!is_dir($path)) 	return array('@number'=>110, '@originalDescription'=>'System error!');
	
	return array('@number'=>0);
}
function getFileUpload($path, $tmpName, $fileName){
	if (!is_file($tmpName) || !$fileName) return 202;
	
	$fileName 	= makeFileName($fileName);
	$path 		= normalFilePath("$path/$fileName");	
	if (!canEditFile($path))	return 202;
	
	makeDir(dirname($path));
	unlinkFile($path);
	if (!move_uploaded_file($tmpName, $path)) return 202;
	fileMode($path);
	return 0;
}

function FCKFinderConnector()
{
	setTemplate('');
	
	$type			= getValue('type');
	$currentFolder	= getValue('currentFolder');
	$ServerPath		= getValue('ServerPath');

	$errorNo		= 0;
	$xml 			= array();
	$filePath 		= normalFilePath("$ServerPath/$type/$currentFolder");
	$currentFolder	= normalFilePath($currentFolder);
	$currentFolder	= $currentFolder?"/$currentFolder/":'/';

	if ($type=='Common'){
		$ServerPath		= '';
		$currentFolder 	= '';
		$filePath 		= images;
	}
	
	switch(getValue('command'))
	{
	case 'Init':		//	none
		$filePath 		= normalFilePath("$ServerPath$currentFolder");
		FinderInit($xml, $filePath, $currentFolder);
		break;
	case 'GetFiles':	//	type=Files&currentFolder=%2F
		FinderFiles($xml, $filePath, $currentFolder);
		break;
	case 'GetFolders':	//	type=Files&currentFolder=%2F
		FinderFolders($xml, $filePath, $currentFolder);
		break;
	case 'DeleteFolder'://	type=Files&currentFolder=%2F
		FinderDeleteFolder($xml, $filePath, $currentFolder);
		break;
	case 'DeleteFile':	//	type=Files&currentFolder=%2F&FileName
		FinderDeleteFile($xml, $filePath, $currentFolder);
		break;
	case 'CreateFolder'://	type=Files&currentFolder=%2F&NewFolderName=xxx
		FinderCreateFolder($xml, $filePath, $currentFolder);
		break;
	case 'FileUpload':	//	type=Files&currentFolder=%2F
		FinderUpload($xml, $filePath, $currentFolder);
		break;
	case 'RenameFile':	//	type=Files&currentFolder=%2F&fileName=&newFileName=
		FinderRenameFile($xml, $filePath, $currentFolder);
		break;
	case 'RenameFolder'://	type=Files&currentFolder=%2F&fileName=&newFileName=
		FinderRenameFolder($xml, $filePath, $currentFolder);
		break;
	case 'Thumbnail':	//	type=Image&currentFolder=%2F&FileName=koteiko_0111.jpg
		FinderThumbnail($xml, $filePath, $currentFolder);
		break;
	case 'DownloadFile':	//	command=DownloadFile&type=Image&currentFolder=%2F&FileName=cat.jpg
		FinderDownloadFile($xml, $filePath, $currentFolder);
		break;
	}
	
	if ($xml){
		$x['Connector']['@resourceType'] 		= $type;
		$x['Connector']['Error']['@number'] 	= $errorNo;
		$x['Connector'][]						= $xml;
		moduleEx('xmlWrite', $x);
	}
}
function FinderInit(&$xml, $filePath, $currentFolder)
{
	$ServerPath = getValue('ServerPath');
	
	if (trim(@$ServerPath, '/'))
		$folders = explode(',', 'Image:Картинки,Gallery:Галерея,File:Файлы,Title,Common:Все файлы');
	else
		$folders = explode(',', 'Image:Картинки,Common:Все файлы');

	$xml['CurrentFolder']=array(
		'@path'=>$currentFolder,
		'@url'=>globalRootURL."/$filePath",
		'@acl'=>255,
	);
	$xml['ConnectorInfo']=array(
		'@enabled'	=>'true',
		'@s'		=>'www.fckeditor.net',	//	ab.eo // host name www.fckeditor.net
		'@c'		=>'LN47',				//	ab.bW // serial LN47
		'@thumbsEnabled			'=>'true',
		'@thumbsDirectAccess'	=> 'true',
		'@thumbsWidth'		=> '96',
		'@thumbsHeight'		=> '96',
		'@imgWidth'			=> '1000',
		'@imgHeight'		=> '1000',
		'@uploadMaxSize'	=> '1048576',	//	1MB
		'@uploadCheckImages'=> 'false',
		'@plugins'			=> ''
	);

	while(list(, $name)=each($folders))
	{
		@list($name, $n)=explode(':', $name);
		if (!$n); $n = $name;
		
		$view 	= 'List';
		$url	= globalRootURL."/$filePath/$name/";
		$acl	= 255;
		
		switch($name){
		case 'Title':
		case 'Image':
		case 'Gallery':
			$view		= 'Thumbnails';
			$folderRoot	= "$filePath/$name";
			break;
		case 'Common':
			$acl = 0;
			$url = globalRootURL.'/'.images.'/';
			break;
		}
		
		$files = getDirs($folderRoot, '');
		$xml['ResourceTypes'][]['ResourceType'] = array(
			'@name'	=> $n,
			'@url'	=> $url,
			'@allowedExtensions'=> '',
			'@deniedExtensions'	=> '',
			'@defaultView'	=> $view,
			'@acl'			=> $acl,
			'@hasChildren'	=> $files?'true':'false',
			'@hash'			=> '',
			'@maxSize'		=> ''
		);
	}
}
function FinderFiles(&$xml, $filePath, $currentFolder)
{
	$type = getValue('type');
/*
	<Connector resourceType="Files">
	<Error number="0"/>
	<CurrentFolder path="/" url="/alfa2/images/File/" acl="255"/>
	<Files>
		<File name="071126_158x70_brera.swf" date="20071127172627" size="10"/>
	</Files>
	</Connector>}
*/
	$acl = 255;
	$url = "/$filePath/";
	
	if ($type=='Common')
	{
		$acl= 0;
		$url= globalRootURL.'/'.images.'/';
		$currentFolder = '/';
		$f	= array();
		getFilesCommon(images, '', $f);
//		$f 	= getFilesCommon(images, '');//(jpg|gif|png|doc|rtf|xls|zip|rar|swf)$
	}else $f= getFiles($filePath, '');
	
	$xml['CurrentFolder']=array(
		'@path'=>$currentFolder,
		'@url'=>$url,
		'@acl'=>$acl,
	);

	$nStart = strlen(images.'/');
	foreach($f as $name => $path)
	{
		$name = basename($path);
		if (preg_match('#(html|shtm|txt)$#', $name)) continue;
		if (strpos($path, '/thumb')) continue;
		
		if (@$type=='Common')
			$name = substr($path, $nStart);
			
		$xml['Files'][]['File'] = array(
			'@name'=>$name,
			'@date'=>date('YmdHis', filemtime($path)),
			'@size'=>round(filesize($path)/1024)
		);
	}
}
function FinderFolders(&$xml, $filePath, $currentFolder)
{
	$type = getValue('type');
	
	$xml['CurrentFolder']=array(
		'@path'=>$currentFolder,
		'@url'=>globalRootURL."/$filePath/",
		'@acl'=>255,
	);
	
	$f = getDirs($filePath, '');
	while(list($name, $path)=each($f)){
		$xml['Folders'][]['Folder'] = array(
			'@name'=>$name,
			'@hasChildren'=>getDirs($path, '')?'true':'false',
			'@acl'=>255
		);
	}
}
function FinderDeleteFolder(&$xml, $filePath, $currentFolder){
/*
<Connector resourceType="Images">
	<Error number="0"/>
	<CurrentFolder path="/FFolder/SubFolder/" url="/alfa2/images/Image/FFolder/SubFolder/" acl="255"/>
</Connector>
*/
	$type = getValue('type');
	
	if ($type=='Common') return;
	
	delTree($filePath);
	$xml['CurrentFolder']=array(
		'@path'=>$currentFolder,
		'@url'=>globalRootURL."/$filePath/",
		'@acl'=>255,
	);
}
function FinderDeleteFile(&$xml, $filePath, $currentFolder){
/*
<Connector resourceType="Images">
	<Error number="0"/>
	<CurrentFolder path="/" url="/alfa2/images/Image/" acl="255"/>
	<DeletedFile name="kvs_news_1.jpg"/>
</Connector>
*/
	$type		= getValue('type');
	$FileName	= getValue('FileName'); 
	
	if ($type=='Common') return;
	
	$filePath = normalFilePath("$filePath/$FileName");
	if (!canEditFile($filePath)) return 1;
	
	@unlink($filePath);

	$xml['CurrentFolder']=array(
		'@path'=>$currentFolder,
		'@url'=>globalRootURL."/$filePath/",
		'@acl'=>255,
	);
	$xml['DeletedFile']['@name']=$FileName;
}
function FinderUpload(&$xml, $filePath, $currentFolder){
/*
IN
$_FILES
    [NewFile] => Array
        (
            [name] => koteiko_0111.jpg
            [type] => image/jpeg
            [tmp_name] => C:\WINDOWS\TEMP\php2CF.tmp
            [error] => 0
            [size] => 158914
        )
OUT
<script type="text/javascript">window.parent.OnUploadCompleted(12,'koteiko_0111.jpg') ;</script>
*/
	$type		= getValue('type');
	$FileName	= getValue('FileName'); 
	
	if ($type=='Common') return;

	$FileName = makeFileName(@$_FILES['NewFile']['name'], true);
	$filePath = normalFilePath("$filePath/$FileName");
	if (!canEditFile($filePath)) return 1;
	
	@makeDir(dirname($filePath));
	@move_uploaded_file($_FILES['NewFile']['tmp_name'], $filePath);
	fileMode($filePath);

	$name 	= str_replace("'", "\\'", $FileName);
	echo "<script type=\"text/javascript\">window.parent.OnUploadCompleted(0,'$name') ;</script>";
}
function FinderCreateFolder(&$xml, $filePath, $currentFolder){
/*
<Connector resourceType="Gallery">
	<Error number="0"/>
	<CurrentFolder path="/aaa/" url="/alfa2/images/Gallery/aaa/" acl="255"/>
	<NewFolder name="bbb"/>
</Connector>
*/
	$type			= getValue('type');
	$NewFolderName	= getValue('NewFolderName'); 
	
	if ($type=='Common') return;

	$NewFolderName	= makeFileName($NewFolderName, true);
	$filePath		= normalFilePath("$filePath/$NewFolderName");
	if (!canEditFile($filePath)) return 1;
	@makeDir($filePath);

	$xml['CurrentFolder']=array(
		'@path'=>$currentFolder,
		'@url'=>globalRootURL."/$filePath/",
		'@acl'=>255,
	);
	$xml['NewFolder']['@name']=$NewFolderName;
}
function FinderRenameFile(&$xml, $filePath, $currentFolder){
/*
<Connector resourceType="Images">
	<Error number="102"/>
	<CurrentFolder path="/" url="/alfa2/images/Image/" acl="255"/>
	<RenamedFile name="071126_158x70_brera.swf" newName="1071126_158x70_brera.swf"/>
</Connector>
*/
	$type			= getValue('type');
	$fileName		= getValue('fileName'); 
	$newFileName	= getValue('newFileName'); 
	
	if (@$type=='Common') return;
	
	$oldName = normalFilePath("$filePath/$fileName");
	$newName = normalFilePath("$filePath/$newFileName");
	if (!canEditFile($oldName) || !canEditFile(images.'/'.$newName)) return 1;
	@rename($oldName, $newName);
	
	$xml['CurrentFolder']=array(
		'@path'=>$currentFolder,
		'@url'=>globalRootURL."/$filePath/",
		'@acl'=>255,
	);
	$xml['RenamedFile']['@name']	=$fileName;
	$xml['RenamedFile']['@newName']	=$newFileName;
}

function FinderRenameFolder(&$xml, $filePath, $currentFolder){
/*
<Connector resourceType="Images">
	<Error number="0"/>
	<CurrentFolder path="/aaa/" url="/alfa2/images/Image/aaa/" acl="255"/>
	<RenamedFolder newName="bbb" newPath="/bbb/" newUrl="/alfa2/images/Image/bbb/"/>
</Connector>
*/
	$type			= getValue('type');
	$fileName		= getValue('fileName'); 
	$NewFolderName	= getValue('NewFolderName'); 
	
	if (@$type=='Common') return;
	
	$oldName = $filePath;
	$newName = normalFilePath("$type/$NewFolderName");
	if (!canEditFile($oldName) || !canEditFile($newName)) return 1;
	@rename($oldName, $newName);
	
	$xml['CurrentFolder']=array(
		'@path'=>$currentFolder,
		'@url'=>globalRootURL."/$filePath/",
		'@acl'=>255,
	);
	$xml['RenamedFolder']['@newName']	= $NewFolderName;
	$xml['RenamedFolder']['@newPath']	= "/$NewFolderName/";
	$xml['RenamedFolder']['@newUrl']	= "/$newName/";
}
//	100x100 image
function FinderThumbnail(&$xml, $filePath, $currentFolder)
{
	$type		= getValue('type');
	$FileName	= getValue('FileName'); 
	if ($type == 'Common') $filePath = images;
	$filePath	= normalFilePath("$filePath/$FileName");
	$exts = explode('.', $FileName) ;
	$exts = strtolower($exts[count($exts)-1]); 

	switch($exts){
	case 'jpeg':
	case 'jpg': $gd = imagecreatefromjpeg($filePath); 	break;
	case 'png': $gd = imagecreatefrompng($filePath); 	break;
	case 'gif': $gd = imagecreatefromgif($filePath);	break;
	}

	if (!@$gd) return;
	
	$sz = 100;
	$sx = imagesx($gd);
	$sy = imagesy($gd);
	
	if ($sx<$sz && $sy<$sz){
		$gd2= $gd;
	}else{
		$z 	= $sx>$sy?$sz/$sx:$sz/$sy;
		$x	= round($sx*$z);
		$y	= round($sy*$z);
		
		$gd2 = imagecreatetruecolor($x, $y);
		imagecopyresampled($gd2, $gd, 0, 0, 0, 0, $x, $y, $sx, $sy);
	}

	header('Content-type: image/jpeg');
	imagejpeg($gd2, NULL,60);
}
function FinderDownloadFile(&$xml, $filePath, $currentFolder)
{
	$FileName	= getValue('FileName'); 
	if ($type == 'Common') $filePath = images;
	$filePath	= normalFilePath("$filePath/$FileName");
	if (!is_file($filePath)) return;

	$length = filesize($filePath);
	$time	= gmdate('r', filemtime($filePath));
	
	header('Content-type: application/octet-stream');
	header("Content-Disposition: attachment; filename=$FileName");
	header("Last-Modified: $time");
	header("Content-Length: $length");
	readfile($filePath);
}
function getFilesCommon($path, $filter, &$res)
{
	$res = array_merge($res, getFiles($path, $filter));
	foreach(getDirs($path, '') as $path){
		getFilesCommon($path, $filter, $res);
	}
}
?>
<? function FCKFinderConnector2()
{
	setTemplate('');
	//	command=Init&type=Images
	$cmd 	= getValue('command');
	$type	= getValue('type');
	$xml	= array();
	
	switch($cmd){
	case 'Init':
		$errorNo = Finder2Init($xml);
	break;
	}
	
	if ($xml){
		$x									= array();
		$x['Connector']['Error']['@number'] = (int)$errorNo;
		$x['Connector'][]					= $xml;
		moduleEx('xmlWrite', $x);
	}
}
function Finder2Init(&$xml){
//	<ConnectorInfo enabled="true" s="cksource.com" c="3H35UQNX2" thumbsEnabled="true" thumbsUrl="/userfiles/_thumbs/" thumbsDirectAccess="true" thumbsWidth="96" thumbsHeight="96" imgWidth="1600" imgHeight="1200" uploadMaxSize="8388608" uploadCheckImages="false" plugins="imageresize,zip,fileeditor" /> 
	$xml['ConnectorInfo'] = array(
		'@enabled'	=> 'true',
		'@s'		=> 'cksource.com',	//	a.ed
		'@c'		=> '3H35UQNX2',		//	a.bF
		'@thumbsEnabled'		=> 'true',
		'@thumbsDirectAccess'	=> 'true',
		'@thumbsWidth'		=> '96',
		'@thumbsHeight'		=> '96',
		'@imgWidth'			=> '1000',
		'@imgHeight'		=> '1000',
		'@uploadMaxSize'	=> '1048576',	//	1MB
		'@uploadCheckImages'=> 'false',
		'@plugins'			=> ''
	);
	$xml['PluginsInfo'] = array(
	);
//	ResourceTypes
	if (trim(@$ServerPath, '/'))
		$folders = explode(',', 'Image:Картинки,Gallery:Галерея,File:Файлы,Title,Common:Все файлы');
	else
		$folders = explode(',', 'Image:Картинки,Common:Все файлы');

	foreach($folders as $folder)
	{
		list($folder, $name) = explode(':', $folder);
		$thisFolder = array(
			'@name' => $folder,
			'@url'	=> images."/$folder",
			'@defaultView'		=> 'Thumbnails',
			'@hasChildren' 		=> 'false',
			'@allowedExtensions'=> '',
			'@deniedExtensions'	=> '',
			'@acl'				=> 'acl',
			'@hash'				=> '',
			'@maxSize'			=> ''
		);
		$xml['ResourceTypes'][]['ResourceType'] = $thisFolder;
	}
}
?>