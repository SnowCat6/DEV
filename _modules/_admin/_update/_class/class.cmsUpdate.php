<?
define('serverUpdateHost', 	'http://vpro.ru/');
define('serverUpdateFolder','_update/');

class cmsUpdate
{
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
		$cmsUpdateFile	= '';
		$cmsVersion		= '0.0.0';

		$files			= getFIles(serverUpdateFolder, '\.zip$');
		foreach($files as $fileName => $filePath)
		{
			//	dev_cms_stable-1.0.0.zip
			if (!preg_match('#([a-z]+)-(\d+\.\d+\.\d+)#', $fileName, $val)) continue;
	
			$build	= $val[1];
			if (array_search($build, $allowBuild) === false) continue;
			$ver	= $val[2];
			if (version_compare($vel, $cmsVersion) >= 0) continue;
	
			$cmsBuild		= $build;
			$cmsVersion		= $ver;
			$cmsUpdateFile	= serverUpdateHost . $filePath;
		}
		
		$responce	= array(
			'DEV_CMS_BUILD'		=> $cmsBuild,
			'DEV_CMS_VERSION'	=> $cmsVersion,
			'DEV_CMS_UPDATE'	=> $cmsUpdateFile,
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
		$backupFolder	= serverUpdateFolder . 'backup/';
		makeDir($backupFolder);
		foreach($backup as $filePath)
		{
			$backupPath	= $backupFolder . basename($filePath);
			unlink($backupPath);
			rename($filePath, $backupPath);
		}
		
		//	Copy files to new system
		copy($updateFile, $rootFolder . basename($updateFile));
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