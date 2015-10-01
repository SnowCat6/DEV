<?
define('serverUpdateHost', 	'http://vpro.ru/');
define('serverUpdateFolder','_update/');

class cmsUpdate
{
	static function checkVersion($check = NULL)
	{
		if (!$check) $check = self::getServerInfo();
		
		$localMD5	= md5_file(self::getLocalFileUpdate());
		$serverMD5	= $check['DEV_CMS_UPDATE_MD5'];

		$nCompare	= version_compare(self::getLocalVersion(), $check['DEV_CMS_VERSION']);
		if ($nCompare > 0) return true;

		return ($nCompare == 0) && ($localMD5 == $serverMD5);
	}
	static function getServerFileUpdate()
	{
		$check	= self::getServerInfo();
		if (!$check) return;

		$updateURL		= $check['DEV_CMS_UPDATE'];
		$updateFileName	= basename($check['DEV_CMS_UPDATE']);
		$localPath		= serverUpdateFolder . $updateFileName;
		unlink($localPath);
		
		$curl		= new Curl();
		$updateFile	= $curl->get($updateURL);
		if (!$updateFile) return;
		
		mkdir(serverUpdateFolder);
		file_put_contents($localPath, $updateFile);
		
		$md5	= md5($updateFile);
		if ($md5 != $check['DEV_CMS_UPDATE_MD5']) return;
		file_put_contents("$localPath.md5", $md5);
		
		return $localPath;
	}
	
	static function getServerInfo()
	{
		$curl	= new Curl();
		$check	= $curl->post(serverUpdateHost . 'server_update_get.htm', array(
			'DEV_CMS_VERSION'	=> getCacheValue('DEV_CMS_VERSION'),
			'build'				=> self::localBuildFilter()	// alpha,beta,stable... any words ([a-z])+
		));
		return (array)json_decode($check);
	}

	static function getServerUpdateInfo($allowBuild)
	{
		if (!$allowBuild) $allowBuild = 'stable';
		$allowBuild = explode(',', $allowBuild);

		$cmsBuild		= '';
		$cmdFileName	= '';
		$cmsVersion		= '0.0.0';

		$files			= getFIles(serverUpdateFolder, '\.zip$');
		foreach($files as $fileName => $filePath)
		{
			//	dev_cms_stable-1.2.3.zip
			if (!preg_match('#([a-z]+)-(\d+\.\d+\.\d+)#', $fileName, $val)) continue;
	
			$build	= $val[1];
			if (array_search($build, $allowBuild) === false) continue;
			$ver	= $val[2];
			if (version_compare($vel, $cmsVersion) >= 0) continue;
	
			$cmsBuild		= $build;
			$cmsVersion		= $ver;
			$cmdFileName	= $filePath;
		}
		
		$responce	= array(
			'DEV_CMS_BUILD'		=> $cmsBuild,
			'DEV_CMS_VERSION'	=> $cmsVersion,
			'DEV_CMS_UPDATE'	=> serverUpdateHost . $filePath,
			'DEV_CMS_UPDATE_MD5'	=> file_get_contents("$cmdFileName.md5"),
			'DEV_CMS_UPDATE_NOTE'	=> nl2br(file_get_contents("$cmdFileName.txt"))
		);
		
		return $responce;
	}
/*****************************/
	static function getLocalFileUpdate(){
		$check	= self::getServerUpdateInfo(self::localBuildFilter());
		return serverUpdateFolder . basename($check['DEV_CMS_UPDATE']);
	}
	static function getLocalVersion(){
		return getCacheValue('DEV_CMS_VERSION');
	}
	static function localBuildFilter(){
		return 'stable';
	}
/*****************************/
	static function update($updateFile)
	{
		$rootFolder		= './';
		
		$md5	= md5_file($updateFile);
		if (file_get_contents("$updateFile.md5") != $md5)
			return;

		//	Check current installed version in root folder
		if (md5_file($rootFolder . basename($updateFile)) == $md5)
			return;

		//	Start update
		set_time_limit(0);
		$backupFolder	= serverUpdateFolder . 'backup/' . date('Ymd_His') . '/';
		$rootFiles		= self::getZipFiles($updateFile, '^[^/]+$');
		
		//	Check local files for CMS
		$backup	= array();
		$files	= getFiles($rootFolder, '\.zip$');
		foreach($files as $file)
		{
			//	index.php file need to be in zip archive
			$f	= self::getZipFiles($file, 'index');
			if (array_search('index.php', $f) === false) continue;
			
			$backup[]	= $file;
		}
		//	Ceck root files for backup
		foreach($rootFiles as $file)
		{
			$file	= $rootFolder.$file;
			if (is_file($file)) $backup[] = $file;
		}
		//	manual named update file
		$backup[]	= 'DEV.zip';

		//	Move files to backup folder
		makeDir($backupFolder);

		foreach($backup as $filePath)
		{
			$backupPath	= $backupFolder . basename($filePath);
			unlink($backupPath);
			rename($filePath, $backupPath);
		}
		
		//	Copy files to new system
		$newFile	= $rootFolder . basename($updateFile);
		copy($updateFile, $newFile);
		
		//	Expand root files
		$zip	= new ZipArchive();
		$zip->open($updateFile);
		$zip->extractTo($rootFolder, $rootFiles);
		$zip->close();

		//	Run once file before update
		$runOnce	= $rootFolder . 'update_run_once.php';
		if (is_file($runOnce)){
			include ($runOnce);
			unlink($runOnce);
		}
		
		//	rebuild site code
		$site	= siteFolder();
		$msg	= execPHP("index.php clearCacheCode $site");
		if ($msg){
			echo '<div class="message">';
			echo "Обновление завершено успешно!";
			echo '</div>';
			return true;
		}
		
		//	FAIL BACK TO ORIGINAL
		unlink($newFile);
		
		//	restore overrided files
		foreach($backup as $filePath)
		{
			$backupPath	= $backupFolder . basename($filePath);
			unlink($filePath);
			rename($backupPath, $filePath);
		}
		unlink($backupFolder);

		echo '<div class="message error">';
		echo "Ошибка обновления";
		echo '</div>';
		return false;
	}

	static function getZipFiles($zipFile, $filter)
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
};
?>