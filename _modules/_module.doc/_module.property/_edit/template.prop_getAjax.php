<? function prop_getAjax(){
	setTemplate('');
	$names	= getValue('names');
	if (is_array($names)) $names = implode(',', $names);
	$props	= module("prop:value:$names");
	echo json_encode($props);
}?>
