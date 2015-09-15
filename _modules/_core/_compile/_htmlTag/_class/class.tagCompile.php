<?
class tagCompile
{
	var $tags;
////////////////////////////////
	function tagCompile($tagReg = '')
	{
		$this->setTagReg($tagReg);
	}
	function setTagReg($tagReg)
	{
		$this->tags = $tagReg;
	}
	function compile($content, $options = NULL)
	{
		$thisElm	= $this;
		$tagReg		= $this->tags;
		$content	= preg_replace_callback("#<(($tagReg)[^\s>]*)([^>]*)/>#ismu",
		function($val) use ($thisElm, $options){
			$val[] = '';
			return  $thisElm->onTagParse($val, $options);
		},
		$content);

		$content	= preg_replace_callback("#<(($tagReg)[^\s>]*)([^>]*)>(.*?)</\\1>#ismu",
		function($val) use ($thisElm, $options){
			return  $thisElm->onTagParse($val, $options);
		},
		$content);

		return $content;
	}
////////////////////////////////
	function onTagCompile($tagName, $propery, $content, $options)
	{
		return $content;
	}
////////////////////////////////
	function onTagParse($val, $options)
	{
		$ix		= count($val);
		$name	= $val[1];
		$prop	= $val[$ix-2];
		$ctx	= $val[$ix-1];
		$props	= self::parseHtmlProperty($prop);

		$ctx	= $this->onTagCompile($name, $props, $ctx, $options);
		if (is_string($ctx)) return $ctx;
		return $val[0];
	}
////////////////////////////////
	static function parseHtmlProperty($property)
	{
		$pattern= '#([^\s=\"\']+)\s*(=\s*([\"\'])(.*?)\3)#';
		preg_match_all($pattern, $property, $var);
	
		$props	= array();
		foreach($var[1] as $ix => $name)
		{
			$name			= strtolower($name);
			$props[$name]	= $var[4][$ix];
		}
			
		return $props;
	}
	static function makeTag($tagName, $property, $content = '')
	{
		$prop	= array();
		foreach($property as $name => $value){
			$value	= htmlspecialchars($value);
			if ($value) $prop[]	= "$name=\"$value\"";
		}
		$prop	= implode(' ', $prop);

		if ($content) return "<$tagName $prop>$content</$tagName>";
		return "<$tagName $prop/>";
	}
};
?>