<?
function module_bask($fn, &$data)
{
	if (!defined('bask'))
	{
		define('bask', true);
		
		$bask	= array();
		$b		= explode(';', $_COOKIE['bask']);
		foreach($b as $row)
		{
			$row	= explode('=', $row, 2);
			$id = $mode = '';
			list($id, $mode) = explode(':', $row[0], 2);
			$id		= (int)$id;
			$count	= (int)$row[1];
			if ($id > 0 && $count >= 0){
				if ($mode) $bask["$id:$mode"] = $count;
				else $bask[$id] = $count;
			}
		}
		config::set('bask', $bask);
	}
	if (!$fn) return config::get('bask');

	@list($fn, $val)  = explode(':', $fn, 2);
	$fn = getFn("bask_$fn");
	return $fn?$fn(config::get('bask'), $val, $data):NULL;
}

function bask_count($bask, $val, $data)
{
	echo '<span class="baskItemCount">{@bask:countEx}</span>';
}
//	Full page replace
function bask_countEx($bask, $val, &$sitePage)
{
	$count		= 0;
	foreach($bask as $c) $count += $c;
	echo $count;
}

function bask_button($bask, $id, $data)
{
	m('page:style', 'css/bask.css');
	m('script:ajaxLink');
	$url	= getURL("bask_add$id");
	if ($data){
		$action = (is_array($data))?implode('', $data):$data;
	}else{
		$action	= @$bask[$id]?'Добавить +1':'Купить';
	}
	echo "<a href=\"$url\" id=\"ajax\" class=\"baskButton\">$action</a>";
}

function setBaskCookie($bask)
{
	module('nocache');
	event('bask.queryFilter', $bask);

	$val = array();
	foreach($bask as $id => $count)
	{
		if ($id < 1 || $count < 0){
			unset($bask[$id]);
			continue;
		}
		$val[] = "$id=$count";
	}
	
	config::set('bask', $bask);
	cookieSet('bask', implode(';', $val));
}

function bask_update($bask, $val, $data)
{
	$id		= $data[1];
	$mode	= getValue('mode');
	if ($mode) $id = "$id:$mode";
	
	switch($val)
	{
	case 'set':
		$bask[$id] = 1;
		module('message', 'Товар добавлен');
		break;
	case 'add':
		$bask[$id] += 1;
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
function bask_items($bask, $val, $data)
{
	if (is_array($data)){
		$bask = $data;
		event('bask.queryFilter', $bask);
		if (!$bask) return array();
	}else{
		event('bask.queryFilter', $bask);
		setBaskCookie($bask);
		if (!$bask) return array();
	}

	$db			= module('doc');
	$s			= array();
	$s['type']	= 'product';
	$s['id']	= array_keys($bask);
	event('bask.query', $s);
	
	$sql	= array();
	doc_sql($sql, $s);
	
	$db->open($sql);
	$items	= array();
	while($data = $db->next())
		$items[$db->id()] = $data;
	if (!$items) return array();

	$result	= array();
	foreach($bask as $baskID => $count)
	{
		$id = $mode = '';
		list($id, $mode)	= explode(':', $baskID, 2);
		$id		= (int)$id;
		if (!$id) continue;

		$data	= $items[$id];
		$db->setData($data);
		
		$price		= docPrice($data);
		$data['priceName']	= priceNumber($price) . ' руб.';
		
		$data['itemClass']	= 'preview';
		$data['count']		= $count;
		$data['baskID']		= $baskID;
		$data['mode']		= $mode;
		$ev			= array(
			'id'	=> $id,
			'baskID'=> $baskID,
			'mode'	=> &$data['mode'],
			'price' => &$data['price'],
			'priceName'	=> &$data['priceName'],
			'detail'	=> &$data['itemDetail'],
			'itemTitle'	=> &$data['title'],
			'itemClass'	=> &$data['itemClass']
			);
		event('bask.item', $ev);
		$result[$baskID] 	= $data;
	};
	return $result;
}
?>