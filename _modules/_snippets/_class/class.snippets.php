<?
class snippets
{
	/*********************/
	function compile($content){
		return preg_replace_callback('#\[\[([^\]]+)\]\]#u', 'parsePageSnippletsFnInit', $content);
	}
};

function parsePageSnippletsFnInit($matches)
{
	$baseCode	= $matches[1];
	$snippets	= getIniValue(':snippets');
	$code		= $snippets[$baseCode];
	if ($code) return $code;

	$snippets	= getCacheValue('localSnippets');
	return $snippets[$baseCode];
}
?>