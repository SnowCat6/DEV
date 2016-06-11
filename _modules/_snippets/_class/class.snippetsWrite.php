<?
class snippetsWrite extends snippets
{
	/*********************/
	static function get()
	{
		$snippets	= self::getUsers();
		dataMerge($snippets, self::getLocal());
		return $snippets;
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
		
		$snippets	= self::get();
		$undo		= $snippets[$snippetName];

		$snippets	= self::getUsers();
		$snippets[$snippetName]	= $value;
		setIniValue(':snippets', self::encode($snippets));
	
		if ($undo){
			undo::add("$snippetName изменен", "snippets:$snippetName",
				array('action' => "snippets:undo_add:$snippetName", $undo)
			);
		}else{
			undo::add("$snippetName добавлен", "snippets:$snippetName",
				array('action' => "snippets:undo_delete:$snippetName", $undo)
			);
		}

	}
	/*********************/
	static function addLocal($snippetName, $value)
	{
		if (!$snippetName) return;
		
		$snippets = self::getLocal();
		$snippets[$snippetName]	= $value;
		setCacheValue('localSnippets', $snippets);
	}
	/*********************/
	static function delete($snippetName)
	{
		$snippets	= self::getUsers();
		$undo		= $snippets[$snippetName];
		$snippets[$snippetName]	= '';
		unset($snippets[$snippetName]);
		setIniValue(':snippets', self::encode($snippets));

		if ($undo){
			undo::add("$snippetName удален", "snippets:$snippetName",
				array('action' => "snippets:undo_add:$snippetName", $undo)
			);
		}
	}
	static function deleteLocal($snippetName)
	{
		$snippets	= self::getLocal();
		$snippets[$snippetName]	= '';
		unset($snippets[$snippetName]);
		setCacheValue('localSnippets', $snippets);
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