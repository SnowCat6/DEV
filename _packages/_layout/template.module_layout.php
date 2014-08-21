<?
function module_layout(&$val, &$data)
{
	$fn	= getFn("layout_$val");
	return $fn?$fn($val, $data):NULL;
}
function layout_tools($fn, &$data)
{
	if (!hasAccessRole('developer')) return;
	$data['Изменить стиль страницы#ajax_layout']	= getURL('layout_admin');
}
function layout_render(&$val, &$content)
{
	if (getSiteFile('userStyle.css'))
		m('page:style', 'userStyle.css');
}
function layout_update(&$val, &$data)
{
	setTemplate('');
	
	$ini	= getCacheValue('ini');
	$rules	= getValue('rules');
	if (true || is_array($rules)){
		$ini[':layoutStyle']['rules']	= serialize($rules);
		setIniValues($ini);
	}else{
		$rules	= unserialize($ini[':layoutStyle']['rules']);
	}
	if (!is_array($rules)) $rules = array();
	
	$css	= '';
	foreach($rules as $ruleName=>$styles){
		$css .= "$ruleName { ";
		foreach($styles as $ruleName => $val){
			$css .= "$ruleName: $val; ";
		}
		$css .= "}\r\n";
	}
	writeSiteFile('userStyle.css', $css);
}
?>