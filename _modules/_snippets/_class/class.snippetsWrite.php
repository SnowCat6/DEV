<?
class snippetsWrite extends snippets
{
	/*********************/
	static function get()
	{
		$snippets	= getIniValue(':snippets');
		if (is_array($snippets))
			return array_merge($snippets, snippetsWrite::getLocal());
		return snippetsWrite::getLocal();
	}
	static function getLocal()
	{
		$snippets	= getCacheValue('localSnippets');
		if (is_array($snippets)) return $snippets;
		return array();
	}
	static function getUsers()
	{
		$snippets	= getIniValue(':snippets');
		if (is_array($snippets)) return $snippets;
		return array();
	}
	/*********************/
	static function add($snippetName, $value)
	{
		if (!$snippetName) return;
		
//		$snippets	= getStorage(':snippets', 'ini');
		$snippets	= getIniValue(':snippets');
		
		$snippets[$snippetName]	= $value;
		if (!$value) unset($snippets[$snippetName]);
		
		setIniValue(':snippets', $snippets);
//		setStorage(':snippets', $snippets, 'ini');
	}
	/*********************/
	static function addLocal($snippetName, $value)
	{
		if (!$snippetName) return;
		
		$localSnippets = getCacheValue('localSnippets');
		
		$localSnippets[$snippetName]	= $value;
		if (!$value) unset($localSnippets[$snippetName]);
		
		setCacheValue('localSnippets', $localSnippets);
	}
	/*********************/
	static function delete($snippetName, $value)
	{
		snippetsWrite::add($snippetName, '');
		snippetsWrite::addLocal($snippetName, '');
	}
	/*********************/
	static function deleteLocal($snippetName, $value)
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
};
?>