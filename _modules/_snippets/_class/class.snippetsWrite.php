<?
class snippetsWrite extends snippets
{
	/*********************/
	static function get()
	{
		return array_merge(self::getUsers(), self::getLocal());
	}
	static function getLocal()
	{
		return self::decode(getCacheValue('localSnippets'));
	}
	static function getUsers()
	{
		return self::decode(getIniValue(':snippets'));
	}
	/*********************/
	static function add($snippetName, $value)
	{
		if (!$snippetName) return;
		
//		$snippets	= getStorage(':snippets', 'ini');
		$snippets	= self::getUsers();
		
		$snippets[$snippetName]	= $value;
		if (!$value) unset($snippets[$snippetName]);
		
		setIniValue(':snippets', self::encode($snippets));
//		setStorage(':snippets', $snippets, 'ini');
	}
	/*********************/
	static function addLocal($snippetName, $value)
	{
		if (!$snippetName) return;
		
		$localSnippets = self::getLocal();
		
		$localSnippets[$snippetName]	= $value;
		if (!$value) unset($localSnippets[$snippetName]);

		setCacheValue('localSnippets', $localSnippets);
	}
	/*********************/
	static function delete($snippetName)
	{
		snippetsWrite::add($snippetName, '');
//		snippetsWrite::addLocal($snippetName, '');
	}
	/*********************/
	static function deleteLocal($snippetName)
	{
		snippetsWrite::addLocal($snippetName, '');
	}
	/*********************/
	static function access($mode)
	{
		switch($acccess)
		{
		case 'write':
			return hasAccessRole('developer');
		}
		return hasAccessRole('admin,developer,writer');
	}
	static function encode($arraySnippets)
	{
		if (!is_array($arraySnippets))
			return array();
			
		foreach($arraySnippets as $name => $data){
			if (is_array($data)){
				$arraySnippets[$name] = base64_encode(serialize($data));
			}
		}
		return $arraySnippets;
	}
};
?>