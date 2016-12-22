<?
function module_widgetGenerator($mode, &$data)
{
	list($fn, $val)	= explode(':', $mode);
	$fn	= getFn("widgetGenerator_$fn");
	if ($fn) return $fn($val, $data);
}
?>
<?
function widgetGenerator_widgets($val, &$widgets)
{
	$pass	= array();
	$modules= getCacheValue('templates');
	foreach($modules as $name => $path)
	{
		if (!preg_match("#^widget_#", $name)) continue;
		$name		= preg_replace('#_config$#', '', $name);
		if ($pass[$name]) continue;
		$pass[$name]	= true;
		
		$fnConfig	= getFn($name . "_config");
		if ($fnConfig) $fnConfig($val, $widgets);
	}
}
?>
<?
//	Предкомпиляция свойств для упрощения использования виджета
function widgetGenerator_update($widgetID, &$widget)
{
	//	Свойства всего виджета, его размер и прочие аттрибуты
	//	all widget style and size
	$style	= $widget['data']['style'];
	if (!is_array($style)) $style = array();
	
	if ($val = $style['size'])
	{
		$w = $h = '';
		list($w, $h) = explode('x', $val);
		unset($style['size']);
		if ($w) $style['width']	= $w;
		if ($h) $style['height']= $h;
	}
	
	if ((int)$style['width'] || (int)$style['height'])
		$widget['data']['size']	= (int)$style['width'] . 'x' . (int)$style['height'];
		
	if ($style['width']){
		$style['width']			= makeStyleSize($style['width'], false);//(int)$style['width'] . 'px';
		$widget['data']['width']= makeStyleSize($style['width'], true);//(int)$style['width'];
	}
	if ($style['height']){
		$style['height']			= makeStyleSize($style['height'], false);//(int)$style['height']. 'px';
		$widget['data']['height']	= makeStyleSize($style['height'], true);//(int)$style['width'];
	}
	
	$widget['data']['style']	= makeStyle($style);
	//	В некоторых виджетах нужен размер отдельного элемента, вот это для него
	//	one element style and size
	$style	= $widget['data']['elmStyle'];
	if (!is_array($style)) $style = array();

	if ($val = $style['size'])
	{
		$w = $h = '';
		list($w, $h) = explode('x', $val);
		unset($style['size']);
		if ($w) $style['width']	= $w;
		if ($h) $style['height']= $h;
		$widget['data']['elmSize']	= $w . 'x' . $h;
	}

	if ((int)$style['width'] || (int)$style['height'])
		$widget['data']['elmSize']	= (int)$style['width'] . 'x' . (int)$style['height'];
	
	if ($style['width']){
		$style['width']				= makeStyleSize($style['width'], false);//(int)$style['width'] . 'px';
		$widget['data']['elmWidth']	= makeStyleSize($style['width'], true);//(int)$style['width'];
	}
	if ($style['height']){
		$style['height']			= makeStyleSize($style['height'], false);//(int)$style['height']. 'px';
		$widget['data']['elmHeight']= makeStyleSize($style['height'], true);//(int)$style['width'];
	}

	$widget['data']['elmStyle']	= makeStyle($style);
	
	//	Классы
	$class	= $widget['data']['class'];
	if (is_array($class)) $class = implode(' ' , $class);
	if ($class) $widget['data']['class']	= " class=\"$class\"";
	
	//	Название места хранения и путь к файлам хранения для виджета
	//	folder name and folder path for storage data and images
	$folder			= "widgets/$widgetID";
	$imageFolder	= images . "/$folder";
	$widget['data']['folder']		= $folder;
	$widget['data']['imageFolder']	= $imageFolder;
}
function makeStyleSize($val, $bNumber)
{
	if (preg_match('#^\d+$|^\d+px$#', $val))
		return $bNumber?(int)$val:(int)$val . 'px';
	return $bNumber?NULL:$val;
}
?>
<?
//	Удалить данные виджета при удалении
function widgetGenerator_delete($widgetID, &$data)
{
	if ($data['imageFolder'])
		m("file:unlink", $data['imageFolder']);
}
?>
<?
//	Показать предварительный вид виджета
function widgetGenerator_preview($widgetID, &$data)
{
/*
	$widget	= module("holderAdmin:getWidget:$widgetID");
	if ($widget[':exec']['code']){
		return module($widget[':exec']['code'], $widget[':exec']['data']);
	}
*/
	$image	= getSiteFile($data['image']);
	if ($image){
		$p	= array('src' => $image);
		return module("image:display", $p);
	}
}
?>

