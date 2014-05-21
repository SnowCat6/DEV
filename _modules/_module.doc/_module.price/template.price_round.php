<?
/***************************************/
//	Сгруппировать товары по диапазонам значений
//	function module_price_round
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
	$name	= dbEncString($db, $name);
	$sort	= $data['sort'];
	$sort2	= 0;

	$fields	= "round(pv.`$filedType`)";
	$fields2= $sort;

	foreach($names as $n => $q){
		$n		= dbEncString($db, $n);
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
//	function module_price_roundSQL
function price_roundSQL($val, &$evData)
{
	//	data[0]	- Объект базы данных текущей выборки
	//	data[1]	- Массив значений для выборки
	//	data[2]	- SQL запрос выборки документов
	$db		= $evData[0];
	$id		= $db->id();
	$data	= $db->data;
	
	$filedType	= 'valueDigit';

	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();
	
	$names	= array();
	foreach(explode("\r\n", $data['query']) as $row){
		$name	= $q = NULL;
		@list($name, $q)= explode(':', $row);
		$q		= makePropertySQL("$filedType", trim($q));
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
			$sql[':IN'][]	= "prop_id=$id AND round($filedType)=$value";
		}
	}
}
?>
<?
//	function module_price_roundHelp
function price_roundHelp($val, &$evData){ ?>
Группирует свойства в диапазоны значений, или округляет до целого числа, при отсутствии значений настройки.<br>
<b>Название группы значений:диапазон значений.</b> <br>
Пример: 15 до 19 дюймов:15-19<br>
<i>Диапазон указываеться как: -1000 (до тысячи), 1000-5000 (от тысячи до пяти тысяч), 5000- (от пяти тысяч) и так далее, в каждой строке по одному правилу.</i>
<? } ?>