<?
	$installTitle	= 'Установка сайта';
	$installAction	= "install_$_GET[action]";
	if (!function_exists($installAction)) $installAction = 'install_start';
	
	ob_start();
	$installAction($title);
	$ctx	= ob_get_clean();
?>

<? function getInstallFiles()
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
	if (count($files) != 1) return;
	list(, $file) = each($files);
	return $file;
}
function getInstallSites($files)
{
	$file	= getInstallFile($files);
	if (!$file) return array();
	
	$files	= array();
	$zip	= new ZipArchive();
	$zip->open($file);
	for($i = 0; $i < $zip->numFiles; $i++)
	{
		$entry = $zip->getNameIndex($i);
		if (!preg_match('#^_sites/([^/]+)/$#', $entry)) continue;
		//	Append to extract array
		$files[]	= $entry;
	}
	$zip->close();
	return $files;
}
function checkAccess()
{
	$passw	= $_POST['password'];
	return false;
}
?>

<? function install_start(&$action)
{
	$files	= getInstallFiles();
	$file	= getInstallFile($files);
	$sites	= getInstallSites($files);
?>
<h2>Добро пожаловать на страницу установки сайта.</h2>

<? if (count ($files) == 0){ ?>
<div  class="warning">Не найдено файлов для установки сайта, скопируйте файлы на сайт.</div>
<? return; }else if (count($files) > 1){ ?>
<div  class="warning">Найдено больше одного файла для установки сайта, для продолжения работы остаться должен только один.</div>
<? return; } ?>

<? if (count($sites) == 0){ ?>
<div  class="warning">Не найдено сайтов для установки.</div>
<? return; } ?>

<p>Установка сайта будет производится из файла <b><?= htmlspecialchars($file)?></b></p>
<ul>
<? foreach($sites as $path){ ?>
	<li><?= htmlspecialchars($path)?></li>
<? } ?>
</ul>
<form action="?action=start" class="loginForm" method="post">
    <p>Для продолжения установки введите пароль</p>
    <? if ($_POST['password'] && !checkAccess()){ ?>
    <div class="warning">
    Вы ввели неверный пароль.<br />
	Попробуйте вспомнить правильный пароль.
    </div>
    <? } ?>
    <p><input type="text" name="password" placeholder="Введите пароль" class="input w100" value="<?= htmlspecialchars($_POST['password']) ?>" /></p>
    <p><input type="submit" value="Продолжить установку" class="button w100" /></p>
</form>
<? }?>


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
	font-size:12px;
}
h1{
	font-size:36px;
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
	width:320px;
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