<?
addEvent('site.end', 'cookie');

function module_cookie(){
	//	Записать куки
	foreach($GLOBALS['_CONFIG']['coocieSet'] as $name => $val){
		if ($val){
			setcookie($name, $val, time()+30*24*60*60);
		}else{
			$val = 'xx';
			setcookie($name, $val, time()+30*24*60*60);
		}
	}
	
	foreach($GLOBALS['_CONFIG']['coocieSet2'] as $name => $val){
		if ($val){
			setcookie($name, $val);
		}else{
			$val = 'xx';
			setcookie($name, $val);
		}
	}
}

function cookieSet($name, $val, $bStore = true)
{
	if (!defined('cookie')){
		define('cookie', true);
		$GLOBALS['_CONFIG']['coocieSet']	= array();	//	Установленные или удаленные куки
		$GLOBALS['_CONFIG']['coocieSet2']	= array();	//	Установленные или удаленные куки / сессия
	}

	if ($bStore) $GLOBALS['_CONFIG']['coocieSet'][$name] = $val;
	else $GLOBALS['_CONFIG']['coocieSet2'][$name] = $val;
	
	$_COOKIE[$name] = $val;
}

?>
