<?
function module_bask($fn, &$data)
{
	if (!defined('bask')){
		define('bask', true);
		
		$bask	= array();
		@$b		= explode(';', $_COOKIE['bask']);
		foreach($b as $row){
			$row	= explode('=', $row);;
			@$id	= (int)$row[0];
			@$count	= (int)$row[1];
			if ($id && $count >= 0)
				$bask[$id] = $count;
		}
		$GLOBALS['_CONFIG']['bask'] = $bask;
	}
	if (!$fn) return $GLOBALS['_CONFIG']['bask'];

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("bask_$fn");
	return $fn?$fn($GLOBALS['_CONFIG']['bask'], $val, $data):NULL;
}

function bask_count($bask, $val, $data)
{
	echo '{{bask:count}}';
}
//	Full page replace
function bask_countEx($bask, $val, &$sitePage)
{
	$count		= 0;
	foreach($bask as $c) $count += $c;
	$sitePage	= str_replace('{{bask:count}}', $count, $sitePage);
//	echo $count;
}

function bask_button($bask, $id, $data){
	m('page:style', 'bask.css');
	m('script:ajaxLink');
	$url	= getURL("bask_add$id");
	if ($data){
		$action = (is_array($data))?implode('', $data):$data;
	}else{
		$action	= @$bask[$id]?'Добавить +1':'Купить';
	}
	echo "<a href=\"$url\" id=\"ajax\" class=\"baskButton\">$action</a>";
}

function setBaskCookie(&$bask)
{
	noCache();
	$val = array();
	foreach($bask as $id => $count){
		if ($id < 1 || $count < 0){
			unset($bask[$id]);
			continue;
		}
		$val[] = "$id=$count";
	}
	
	$GLOBALS['_CONFIG']['bask'] = $bask;
	cookieSet('bask', implode(';', $val));
}

function bask_update($bask, $val, $data)
{
	@$id = $data[1];
	switch($val){
	case 'set':
		$bask[$id] = 1;
		module('message', 'Товар добавлен');
		break;
	case 'add':
		@$bask[$id] += 1;
		module('message', 'Товар добавлен');
		break;
	case 'delete':
		$bask[$id] = -1;
		module('message', 'Товар удален');
		break;
	case 'clear':
		$bask = array();
		module('message', 'Корзина очищена');
		break;
	}
	
	setBaskCookie($bask);
	module('order:order');
}
?>