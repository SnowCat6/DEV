<?
class xmlWrite
{
	//	Вывести на экран массив, как XML документ
	//	Использовать знак '@' в дочерних нодах для записи как аттрибуты
	static function write(&$xml, $date = NULL)
	{
		// Prevent the browser from caching the result.
		if (!$date)
		{
			// Date in the past
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT') ;
			// HTTP/1.1
			header('Cache-Control: no-store, no-cache, must-revalidate') ;
			header('Cache-Control: post-check=0, pre-check=0', false) ;
			// HTTP/1.0
			header('Pragma: no-cache') ;
			// always modified
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT') ;
		}else{
			//	Дата изменения
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $date) . ' GMT') ;
		}
		// Set the response format.
		header( 'Content-Type:text/xml; charset=UTF-8' ) ;
		self::echoEncode('<?xml version="1.0" encoding="UTF-8"?>');
	
		self::writeXMLtag($xml);
	}
	
	static function echoEncode($value){
		echo $value;//iconv("windows-1251", "utf-8", $value);
	}
	
	static function writeXMLtag(&$xml)
	{
		foreach($xml as $tag => &$child)
		{
			if (is_int($tag)){
				self::writeXMLtag($child);
				continue;
			}
			if (!is_array($child)){
				if ($tag[0] == '!'){
					$tag = substr($tag, 1);
					self::echoEncode("<$tag><![CDATA[$child]]></$tag>");
				}else{
					self::echoEncode("<$tag>".htmlspecialchars($child)."</$tag>");
				}
				continue;
			}
			
			$tags = array();
			self::echoEncode("<$tag");

			foreach($child as $name => &$value)
			{
				if ($name[0] != '@'){
					$tags[$name] = $value;
					continue;
				}
				$name	= substr($name, 1);
				$name	= $name;
				$valu	= htmlspecialchars($value);
				self::echoEncode(" $name=\"$value\"");
			}
			if ($tags){
				self::echoEncode(">");
				self::writeXMLtag($tags);
				self::echoEncode("</$tag>");
			}else self::echoEncode("/>");
		}
	}
}
?>