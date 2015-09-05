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
			$snippets	= getIniValue(':snippets');
			$code		= $snippets[$baseCode];
			if ($code) return $code;
		
			$snippets	= getCacheValue('localSnippets');
			return $snippets[$baseCode];
		}, $content);
	}
};

?>