<?
	header('Content-Type: text/html; charset=utf-8');

	$installTitle	= 'Установка сайта';
	$installAction	= "install_$_GET[action]";
	if (!function_exists($installAction)) 	$installAction = 'install_start';
	if (!class_exists('ZipArchive'))		$installAction = 'install_noZIP';	
	
	ob_start();
	$installAction($installTitle);
	$ctx	= ob_get_clean();
/******************************/
function getInstallFiles()
{
	$files	= array();
	$dir	= opendir('./');
	while($file = readdir($dir)){
		if (!preg_match('#\.zip$#', $file)) continue;
		$files[]	= $file;
	}
	closedir($dir);
	return $files;
}
function getInstallFile($files)
{
	foreach($files as $zipFile)
	{
		$sites	= getInstallSites($zipFile);
		foreach($sites as $site)
		{
			$backups	= getInstallBackups($zipFile, $site);
			if ($backups) return $zipFile;
		}
	}
}
function getZipFiles($zipFile, $filter)
{
	$files	= array();
	$zip	= new ZipArchive();
	$zip->open($zipFile);
	for($i = 0; $i < $zip->numFiles; $i++)
	{
		$entry = $zip->getNameIndex($i);
		if ($filter && !preg_match("#$filter#", $entry)) continue;
		//	Append to extract array
		$files[]	= $entry;
	}
	$zip->close();
	return $files;
}
function readZipFile($zipFile, $path)
{
	$zip	= new ZipArchive();
	$zip->open($zipFile);
	$ctx	= $zip->getFromName($path);
	$zip->close();
	return $ctx;
}
function expandZipFile($zipFile, $exclude = '')
{
	if (is_array($exclude)) $exclude = implode('|', $exclude);
	
	$zip	= new ZipArchive();
	$zip->open($zipFile);

	$extractFiles	= array();
	for($i = 0; $i < $zip->numFiles; $i++)
	{
		$entry = $zip->getNameIndex($i);
		if ($exclude && preg_match("#($exclude)#", $entry)) continue;
		//	Append to extract array
		$extractFiles[]	= $entry;
	}
	//	Extract files to ROOT folder
	if ($extractFiles) $zip->extractTo('./', $extractFiles);
	//	CLode archive
	$zip->close();
	
	return count($extractFiles);
}
function getInstallSites($zipFile)
{
	return getZipFiles($zipFile, '^_sites/([^/]+)/$');
}

function getInstallBackups($zipFile, $site)
{
	$site	= preg_quote($site, '#');
	return getZipFiles($zipFile, "^$site".'_backup/([\d\-]+)/$');
}

function checkAccess($zipFile)
{
	$bOK		= false;
	$passw		= $_POST['backupPassword'];
	$sites		= getInstallSites($zipFile);
	foreach($sites as $site)
	{
		$backups	= getInstallBackups($zipFile, $site);
		foreach($backups as $backup)
		{
			if (md5($passw) != readZipFile($zipFile, $backup . "password.bin"))
				return false;
			$bOK	= true;
		}
	}
	
	return $bOK;
}
/******************************/
function install_start(&$installTitle)
{
	$files	= getInstallFiles();
	$file	= getInstallFile($files);
	$sites	= getInstallSites($file);

	if ($_POST['backupPassword'] && checkAccess($file) && install_install($installTitle))
		return;
?>
<h2>Добро пожаловать на страницу установки сайта.</h2>

<? if (!extension_loaded("zip")){ ?>
<div  class="warning">Расширение ZIP не установлено, для продолжения установки настройте WEB сервер.</div>
<? return; } ?>

<? if (count ($files) == 0){ ?>
<div  class="warning">Не найдено файлов для установки сайта, скопируйте файлы на сайт.</div>
<? return; }else if (count($files) > 1){ ?>
<div  class="warning">Найдено больше одного файла для установки сайта, для продолжения работы остаться должен только один.</div>
<? return; } ?>

<? if (count($sites) == 0){ ?>
<div  class="warning">Не найдено сайтов для установки.</div>
<? return; } ?>

<p>Установка сайта будет производится из файла <b><?= htmlspecialchars($file)?></b></p>
Найденные сайты:
<ul>
<? foreach($sites as $path){ ?>
	<li><?= htmlspecialchars($path)?></li>
<? } ?>
</ul>
<form action="" class="loginForm" method="post">
    <p>Для продолжения установки введите пароль</p>
    <? if ($_POST['backupPassword'] && !checkAccess($file)){ ?>
    <div class="warning">
    Вы ввели неверный пароль.<br />
	Попробуйте вспомнить правильный пароль.
    </div>
    <? } ?>
    <p><input type="text" name="backupPassword" placeholder="Введите пароль" class="input w100" value="<?= htmlspecialchars($_POST['backupPassword']) ?>" /></p>
    <p><input type="submit" value="Продолжить установку" class="button w100" /></p>
</form>
<? }?>


<? function install_install(&$installTitle)
{
	$files	= getInstallFiles();
	$file	= getInstallFile($files);
	if (!checkAccess($file)) return;
	
	$sites	= getInstallSites($file);
	if (!$sites) return;
	
	if (extension_loaded("phar"))
	{
		//	Если есть PHAR, то оставим основные файлы в архиве
		$count	= expandZipFile($file, array(
			'^install_restore\.txt',
			'^_modules',
			'^_templates',
			'^_packages'
		));
	}else{
		//	Если на сайте не установлен PHAR, разархивируем все системные файлы
		$count	= expandZipFile($file, array(
			'^install_restore\.txt'
		));
	}
	if ($count == 0) return;

	define('STDIN', true);
	foreach($sites as $site)
	{
		$siteName	= substr($site, strlen('_sites'));
		$siteName	= trim($siteName, '/');
		
//		$backupName	= basename($file, '.zip');
		$backups	= getInstallBackups($file, $site);
		$backupName	= basename($backups[0]);

		$globalRootURL	= substr($_SERVER['PHP_SELF'], 0, -strlen(basename($_SERVER['PHP_SELF'])));
		$globalRootURL	= trim($globalRootURL, '/');
		if ($globalRootURL) $globalRootURL = '/' . $globalRootURL;
		
		header("Location: http://$_SERVER[HTTP_HOST]$globalRootURL/index.php?URL=backup_$backupName.htm");
		
//		$argv	= array();
//		$argv[]	= 'index.php';
//		$argv[]	= $siteName;
//		$argv[]	= "backup_$backupName.htm";

//		include('index.php');
		unlink('install.php');
		die;
	}
	
	return true;
}?>

<? function install_noZIP(){ ?>
	<h2>ZIP extension not installed!</h2>
    <p>Please install ZIP module.</p>
<? } ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?= $installTitle?></title>
<style>
body{
	background:#131312;
	color:#fff;
	padding:0; margin: 0;
	font-family:Verdana, Geneva, sans-serif;
	font-size:14px;
}
h1{
	font-size:36px;
	font-weight:normal;
}
h2{
	font-size:24px;
	font-weight:normal;
}
a{
	color:white;
}
.content{
	background:#0c3352;
	border-top:solid 1px white;
	border-bottom:solid 1px white;
	padding-top:20px; padding-bottom:20px;
}
.padding{
	padding-left:50px;
	padding-right:50px;
}
.content .padding{
	text-align:left;
	min-height:300px;
	max-width:1100px;
}
.content .login th{
	color:white;
}
.copyright{
	padding:20px;
}
.map a{
	color:#ccc;
}
/****************************/
.loginForm{
	width:350px;
	border:solid 1px #fff;
	padding:10px 20px;
	background:#165E98;
	box-shadow:0 0 40px rgba(0, 0, 0, 0.8);
}
/****************************/
.w100{
	width:100%;
}
.input{
	font-size:18px;
	padding:5px 0;
}
/****************************/
.warning{
	background:red;
	color:white;
	padding:5px 10px;
}
</style>
</head>

<body>
<center>
    <div class="header padding">
	    <h1><?= $installTitle?></h1>
    </div>
    <div class="content">
	    <div class="padding"><?= $ctx ?></div>
    </div>
    <div class="copyright">
    (c) 2012-<?= date('Y')?> ООО "Виртуальный проект"<br />
	<a href="mailto:vpro@vpro.ru">vpro@vpro.ru</a>
    </div>
</center>
</body>
</html>