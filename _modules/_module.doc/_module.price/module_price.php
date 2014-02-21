<?
function module_price($fn, &$data)
{
	@list($fn, $val) = explode(':', $fn, 2);
	//	База данных пользователей
	$fn	= getFn("price_$fn");
	return $fn?$fn($val, $data):NULL;
}
function docPrice(&$data, $name = ''){
	if ($data['doc_type'] != 'product') return;
	if ($name == '') $name = 'base';
	switch($name){
	case 'old':		@$price	= $data['price_old'];	break;
	case 'base':	@$price	= $data['price'];		break;
	}
	return (float)$price;
}
function priceNumber($price){
	$price = str_replace(' ', '', $price);
	if ($price == (int)$price) return number_format($price, 0, '', ' ');
	return number_format($price, 2, '.', ' ');
}
function docPriceFormat(&$data, $name = ''){
	$price = docPrice($data, $name);
	if (!$price) return;
	
	$price = priceNumber($price);
	if ($name == 'old') return "<span class=\"price old\">$price</span>";
	return "<span class=\"price\">$price</span>";
}
function docPriceFormat2(&$data, $name = ''){
	$price = docPriceFormat($data, $name);
	if ($price) return "<span class=\"priceName\">Цена: $price руб.</span>";
}
function price_update($val, &$evData)
{
	$d		= &$evData[0];
	$data	= &$evData[1];
	
	if (isset($data['price']))
	{
		$price = (float)$data['price'];
		$d['price']		= $price;
		$price = (float)$data['price_old'];
		$d['price_old']	= $price;
	}
}
/***************************************/
//	Сгруппировать товары по диапазонам цен
function price_query($val, &$evData)
{
	//	data[0]	- Объект базы данных текущей выборки
	//	data[1]	- SQL запрос выборки документов
	//	data[2]	- возвратить SQL запрос выборки
	
	//	Данные текущего свойства
	$db		= $evData[0];
	$id		= $db->id();
	$data	= $db->data;
	//	SQL запрос, передаются идентификатоы выбираемых документов
	$sql	= $evData[1];

	$names	= array();
	foreach(explode("\r\n", $data['query']) as $row){
		$name	= $q = NULL;
		@list($name, $q)= explode(':', $row);
		$q		= makePropertySQL('`price`', trim($q));
		if ($name && $q) $names[$name]	= $q;
	};

	$sort	= $data['sort'];
	$sort2	= 0;

	$fields	= "round(`price`)";
	$fields2= $sort;

	$name	= $data['name'];
	makeSQLValue($name);

	foreach($names as $n => $q){
		makeSQLValue($n);
		$fields = "IF($q, $n, $fields)";
		$fields2= "IF($q, $sort2, $fields2)";
		++$sort2;
	}
	
	//	Подготовить SQL запрос
	$ddb		= module('doc');
	$sort		= $data['sort'];
	$ddb->fields= "$name AS name, $fields AS value, $sort AS sort, $fields2 AS sort2, count(*) AS cnt";
	$ddb->group	= 'value';
	//	Вернуть запрос
	//	Выбрать все документы по запросу и выделить диапазоны значений
	$evData[2]	= $ddb->makeSQL($sql);
}
//	Собственно выборка документов по запросу
function price_querySQL($val, &$evData)
{
	//	data[0]	- Объект базы данных текущей выборки
	//	data[1]	- Массив значений для выборки
	//	data[2]	- SQL запрос выборки документов
	$db		= $evData[0];
	$id		= $db->id();
	$data	= $db->data;
	
	$names	= array();
	foreach(explode("\r\n", $data['query']) as $row){
		$name	= $q = NULL;
		@list($name, $q)= explode(':', $row);
		$q		= makePropertySQL('`price`', trim($q));
		if ($name && $q) $names[$name]	= $q;
	};
	
	$values	= &$evData[1];
	$sql	= &$evData[2];
	
	foreach($values as $value)
	{
		if ($q = $names[$value]){
			$sql[]	= $q;
		}else{
			makeSQLValue($value);
			$sql[]	= "round(`price`) = $value";;
		}
	}
}
function makePropertySQL($field, $q)
{
	list($q1, $q2) = explode('-', $q);
	$q1 = (int)$q1;
	$q2 = (int)$q2;
	
	if ($q1 && $q2){
		return "($field >= $q1 AND $field < $q2)";
	}else
	if ($q1){
		return "$field >= $q1";
	}else
	if ($q2){
		return "$field <= $q2";
	}
}
/***************************************/
//	Сгруппировать товары по диапазонам цен
function price_round($val, &$evData)
{
	//	data[0]	- Объект базы данных текущей выборки
	//	data[1]	- SQL запрос выборки документов
	//	data[2]	- возвратить SQL запрос выборки

	//	Данные текущего свойства
	$db		= $evData[0];
	$id		= $db->id();
	$data	= $db->data;
	
	$names	= array();
	foreach(explode("\r\n", $data['query']) as $row){
		$name	= $q = NULL;
		@list($name, $q)= explode(':', $row);
		$q		= makePropertySQL("pv$id.`$data[valueType]`", trim($q));
		if ($name && $q) $names[$name]	= $q;
	};

	//	SQL запрос, передаются идентификатоы выбираемых документов
	$sql	= $evData[1];
	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();

	$name	= $data['name'];
	makeSQLValue($name);
	$sort	= $data['sort'];
	$sort2	= 0;

	$fields	= "round(pv$id.`$data[valueType]`)";
	$fields2= $sort;

	foreach($names as $n => $q){
		makeSQLValue($n);
		$fields = "IF($q, $n, $fields)";
		$fields2= "IF($q, $sort2, $fields2)";
		++$sort2;
	}
	if (!$names){
		$fields	= "round(pv$id.`$data[valueType]`)";
		$fields2= $fields;
	}

	$sql[':join']["$table2 AS pv$id"]	= "p$id.`values_id` = pv$id.`values_id`";
	$sql[':where']	= "p$id.`prop_id`=$id";
	$sql[':from'][]	= "p$id";
	
	$db->dbValue->fields	= "$name AS name, $fields AS value, $sort AS sort, $fields2 AS sort2, count(*) AS cnt";
	$db->dbValue->group		= "value";
	//	Вернуть запрос
	//	Выбрать все документы по запросу и выделить диапазоны значений
	$evData[2]	= $db->dbValue->makeSQL($sql);
}
//	Собственно выборка документов по запросу
//	Создать SQL запросы для выборки документов
function price_roundSQL($val, &$evData)
{
	//	data[0]	- Объект базы данных текущей выборки
	//	data[1]	- Массив значений для выборки
	//	data[2]	- SQL запрос выборки документов
	$db		= $evData[0];
	$id		= $db->id();
	$data	= $db->data;
	
	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();
	
	$names	= array();
	foreach(explode("\r\n", $data['query']) as $row){
		$name	= $q = NULL;
		@list($name, $q)= explode(':', $row);
		$q		= makePropertySQL("pv$id.`$data[valueType]`", trim($q));
		if ($name && $q) $names[$name]	= "p$id.`prop_id`=$id AND $q";
	};
	
	$values	= &$evData[1];
	$sql	= &$evData[2];
	
	foreach($values as $value)
	{
		if ($q = $names[$value]){
			$sql[]	= $q;
		}else{
			makeSQLValue($value);
			$sql[]	= "p$id.`prop_id`=$id AND round(pv$id.`$data[valueType]`) = round($value)";
		}
	}

	$sql[':join']["$table AS p$id"]		= "`doc_id` = p$id.`doc_id`";
	$sql[':join']["$table2 AS pv$id"]	= "p$id.`values_id` = pv$id.`values_id`";
}
?>
<? function price_queryHelp($val, &$evData){ ?>
Группирует цены товаров в диапазоны значений, или округляет до целого числа, при отсутствии значений настройки.<br>
<b>Название цены:диапазон цены.</b> <br>
Пример: от 1000 до 2000 рублей:1000-2000<br>
<i>Диапазон указываеться как: -1000 (до тысячи), 1000-5000 (от тысячи до пяти тысяч), 5000- (от пяти тысяч) и так далее, в каждой строке по одному правилу.</i>
<? } ?>
<? function price_roundHelp($val, &$evData){ ?>
Группирует свойства в диапазоны значений, или округляет до целого числа, при отсутствии значений настройки.<br>
<b>Название группы значений:диапазон значений.</b> <br>
Пример: 15 до 19 дюймов:15-19<br>
<i>Диапазон указываеться как: -1000 (до тысячи), 1000-5000 (от тысячи до пяти тысяч), 5000- (от пяти тысяч) и так далее, в каждой строке по одному правилу.</i>
<? } ?>