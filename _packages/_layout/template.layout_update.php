<?
function layout_update(&$val, &$data)
{
	if (!hasAccessRole('developer')) return;
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