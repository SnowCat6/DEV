<?
/***************************************/
//	Сгруппировать товары по диапазонам цен
//	+function price_query
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
	$name	= dbEncString($db, $name);

	foreach($names as $n => $q){
		$n		= dbEncString($db, $n);
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
//	+function price_querySQL
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
			$value	= dbEncString($db, $value);
			$sql[]	= "round(`price`) = $value";;
		}
	}
}
?>
<?
//	+function price_queryHelp
function price_queryHelp($val, &$evData){ ?>
Группирует цены товаров в диапазоны значений, или округляет до целого числа, при отсутствии значений настройки.<br>
<b>Название цены:диапазон цены.</b> <br>
Пример: от 1000 до 2000 рублей:1000-2000<br>
<i>Диапазон указываеться как: -1000 (до тысячи), 1000-5000 (от тысячи до пяти тысяч), 5000- (от пяти тысяч) и так далее, в каждой строке по одному правилу.</i>
<? } ?>
