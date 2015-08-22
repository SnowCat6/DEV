<?
class snippetsWrite extends snippets
{
	/*********************/
	function get()
	{
		$snippets	= getIniValue(':snippets');
		if (is_array($snippets))
			return array_merge($snippets, $this->getLocal());
		return $this->getLocal();
	}
	function getLocal()
	{
		$snippets	= getCacheValue('localSnippets');
		if (is_array($snippets)) return $snippets;
		return array();
	}
	/*********************/
	function add($snippetName, $value)
	{
		if (!$snippetName) return;
		
		$snippets	= getIniValue(':snippets');
		
		$snippets[$snippetName]	= $value;
		if (!$value) unset($snippets[$snippetName]);
		
		setIniValue(':snippets', $snippets);
	}
	/*********************/
	function addLocal($snippetName, $value)
	{
		if (!$snippetName) return;
		
		$localSnippets = getCacheValue('localSnippets');
		
		$localSnippets[$snippetName]	= $value;
		if (!$value) unset($localSnippets[$snippetName]);
		
		setCacheValue('localSnippets', $localSnippets);
	}
	/*********************/
	function delete($snippetName, $value)
	{
		$this->add($snippetName, '');
		$this->addLocal($snippetName, '');
	}
	/*********************/
	function deleteLocal($snippetName, $value)
	{
		$this->addLocal($snippetName, '');
	}
	/*********************/
	function access($mode)
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