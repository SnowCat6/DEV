<?
class system_packages
{
	static function findPackages()
	{
		$packages	= array();
		$folders	= array();
	
		$files		= findPharFiles('./');
		foreach($files as $path)	$folders[]	= "$path/_packages";
	
		$files		= findPharFiles('_packages');
		foreach($files as $path)	$folders[]	= $path;
	
		$folders[]	= '_packages';
		
		foreach(getDirs($folders) as $name => $path) $packages[$name] = $path;
	
		return $packages;
	}
	static function loadPackages(&$siteFS)
	{
		system_init::addExcludeRegExp('#config.ini$#');

		$packs	= self::findPackages();
		$pass	= array();

		$ini		= getCacheValue('ini');
		$packages	= $ini[":packages"];
		while($packages)
		{
			list($name, $path)	= each($packages);
			unset($packages[$name]);
	
			if (!$path) continue;
			
			$path = $packs[$name];
			if ($pass[$path]) continue;
			$pass[$name]	= $path;
			
			$package		= readIniFile("$path/config.ini");
			$use			= $package['use'];
			if (!$use) $use = array();
			foreach($use as $package => $require){
				$packages[$package] = $packs[$package];
			}
			initialize::collectFiles($siteFS, $path);
		}
		setCacheValue('packages', $pass);
	}
}
?>