<?
class snippets
{
	/*********************/
	static function compile($content)
	{
		return preg_replace_callback('#\[\[([^\]]+)\]\]#u', 
		function($matches)
		{
			$baseCode	= $matches[1];
			$snippets	= self::decode(getIniValue(':snippets'));
			$code		= $snippets[$baseCode];
			if ($code) return $code['code'];
		
			$snippets	= self::decode(getCacheValue('localSnippets'));
			$code		= $snippets[$baseCode];
			return $code['code'];
		}, $content);
	}
	static function decode($arraySnippets)
	{
		if (!is_array($arraySnippets))
			return array();
			
		foreach($arraySnippets as $name => $data)
		{
			if (is_array($data)) continue;
			
			$newData = unserialize(base64_decode($data));
			//	emulate old code
			if (is_array($newData))
			{
				$arraySnippets[$name] = $newData;
			}else{
				$arraySnippets[$name] = array(
					'note'	=> "code: $data",
					'code'	=> $data
				);
			}
		}
		return $arraySnippets;
	}
};

?>