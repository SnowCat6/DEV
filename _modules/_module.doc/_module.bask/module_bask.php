<?
function module_bask($fn, $data)
{
	if (!defined('bask')){
		define('bask', true);
		
		$bask	= array();
		@$b		= explode(';', $_COOKIE['bask']);
		foreach($b as $row){
			$row	= explode('=', $row);;
			@$id	= (int)$row[0];
			@$count	= (int)$row[1];
			if ($id && $count > 0)
				$bask[$id] = $count;
		}
		$GLOBALS['_CONFIG']['bask'] = $bask;
	}else{
		$bask = $GLOBALS['_CONFIG']['bask'];
	}
	
	if (!$fn) return $bask;

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("bask_$fn");
	return $fn?$fn($bask, $val, $data):NULL;
}

function bask_button($bask, $id){
	$url = getURL("bask_add$id");
	$action = @$bask[$id]?'Добавить +1':'Купить';
	echo "<a href=\"$url\" id=\"ajax\" class=\"baskButton\">$action</a>";
}

function setBaskCookie($bask)
{
	$GLOBALS['_CONFIG']['bask'] = $bask;

	$val = array();
	foreach($bask as $id => $count){
		if ($id < 1 && $count < 1) continue;
		$val[] = "$id=$count";
	}
	cookieSet('bask', implode(';', $val));
}

function bask_add($bask, $val, $data)
{
	$id = $data[1];
	@$bask[$id] += 1;
	setBaskCookie($bask);
	module('message', 'Товар добавлен');
	module('display:message');
	module('bask:full');
}
?>