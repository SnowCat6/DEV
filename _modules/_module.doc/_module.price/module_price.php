<?
function module_price($fn, &$data)
{
	@list($fn, $val) = explode(':', $fn, 2);
	//	База данных пользователей
	$fn	= getFn("price_$fn");
	return $fn?$fn($val, $data):NULL;
}
function priceRate(){
	if (!defined('priceRate')){
		$ini	= getCacheValue('ini');
		$rate	= $ini[':priceRate'];
		$rate	= (float)$rate['rate'];
		if ($rate <= 0) $rate = 1;
		define('priceRate', $rate);
	}
	return priceRate;
}
function docPrice(&$data, $name = ''){
	if ($data['doc_type'] != 'product') return;
	if ($name == '') $name = 'base';
	switch($name){
	case 'old':		@$price	= $data['price_old'];	break;
	case 'base':	@$price	= $data['price'];		break;
	}
	return (float)$price*priceRate();
}
function priceNumber($price){
	$price = str_replace(' ', '', $price);
	if ($price == (int)$price) return number_format($price, 0, '', ' ');
	return number_format($price, 2, '.', ' ');
}
function docPriceFormat(&$data, $name = '', $postfix='')
{
	$price = docPrice($data, $name);
	if ($price) $price = priceNumber($price);
	else{
		$postfix	= '';
		$price 		= '';
	}
	
	if ($name == 'old') return "<span class=\"price old\">$price</span>$postfix";
	return "<span class=\"price\">$price</span>$postfix";
}
function docPriceFormat2(&$data, $name = ''){
	$price = docPriceFormat($data, $name);
	if ($price) return "<span class=\"priceName\">Цена: $price руб.</span>$postfix";
}
//	Аернуть статус заказа
function docPriceDelivery(&$data, $bFormat = true)
{
	$import		= isset($data['fields']['any']['import'][':raw'])?$data['fields']['any']['import'][':raw']:array();
	$delivery	= $import['delivery'];
	if ($delivery != 'под заказ' && docPrice($data)) return;
	
	return  '<span class="priceDelivery">под заказ</span>';
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
//	Сформировать запрос для диапазона, rate используется для коррекции цифрового значения в записимости от курса
function makePropertySQL($field, $q, $rate = 1)
{
	list($q1, $q2) = explode('-', $q);
	$q1 = (int)$q1 / $rate;
	$q2 = (int)$q2 / $rate;
	
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
		$q		= makePropertySQL('`price`', trim($q), priceRate());
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
	$ddb->sql	= '';
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
		$q		= makePropertySQL('`price`', trim($q), priceRate());
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
/***************************************/
//	Сгруппировать товары по диапазонам значений
function price_round($val, &$evData)
{
	//	data[0]	- Объект базы данных текущей выборки
	//	data[1]	- SQL запрос выборки документов
	//	data[2]	- возвратить SQL запрос выборки

	//	Данные текущего свойства
	$db		= $evData[0];
	$id		= $db->id();
	$data	= $db->data;

	$filedType	= 'valueDigit';
//	$filedType	= $data['valueType'];
	
	$names	= array();
	foreach(explode("\r\n", $data['query']) as $row){
		$name	= $q = NULL;
		@list($name, $q)= explode(':', $row);
		$q		= makePropertySQL("pv.`$filedType`", trim($q));
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

	$fields	= "round(pv.`$filedType`)";
	$fields2= $sort;

	foreach($names as $n => $q){
		makeSQLValue($n);
		$fields = "IF($q, $n, $fields)";
		$fields2= "IF($q, $sort2, $fields2)";
		++$sort2;
	}
	if (!$names){
		$fields	= "round(pv.`$filedType`)";
		$fields2= $fields;
	}
	$sql[':from'][]					= "p";
	$sql[':from']["prop_values_tbl"]= 'pv';
	$sql[]	= '`values_id`=pv.`values_id`';
	$sql[]	= "`prop_id`=$id";
	
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
	
	$filedType	= 'valueDigit';
//	$filedType	= $data['valueType'];

	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();
	
	$names	= array();
	foreach(explode("\r\n", $data['query']) as $row){
		$name	= $q = NULL;
		@list($name, $q)= explode(':', $row);
		$q		= makePropertySQL("pv.`$filedType`", trim($q));
		if ($name && $q) $names[$name]	= "prop_id=$id AND $q";
	};
	
	$values	= &$evData[1];
	$sql	= &$evData[2];

	$thisSQL= array();
	foreach($values as $value)
	{
		//	Диапазон значений по правилам
		if ($q = $names[$value]){
			$sql[':IN'][]	= $q;
		}else{
			//	Округленное до целого значение
			$value	= round($value);
			makeSQLValue($value);
			$sql[':IN'][]	= "prop_id=$id AND round(pv.`$filedType`) = $value";
		}
	}
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