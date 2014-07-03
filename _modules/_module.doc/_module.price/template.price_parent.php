<?
/***************************************/
//	Сгруппировать товары по диапазонам значений
//	function module_price_parent
function price_parent($val, &$evData)
{
	//	data[0]	- Объект базы данных текущей выборки
	//	data[1]	- SQL запрос выборки документов
	//	data[2]	- возвратить SQL запрос выборки

	//	Данные текущего свойства
	$db		= $evData[0];
	$id		= $db->id();
	$data	= $db->data;

	$name	= $data['name'];
	$name	= dbEncString($db, $name);
	$sort	= $data['sort'];

	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();

	//	SQL запрос, передаются идентификатоы выбираемых документов
	$sql							= $evData[1];
	$sql[':from'][]					= 'p';
	$sql[':from']['prop_name_tbl']	= 'pn';
	$sql[':from']['prop_values_tbl']= 'pv';
	$sql[]		= 'p.`values_id`=pv.`values_id`';

	$propName	= ':parent';
	$propName	= dbEncString($db, $propName);
	$sql[]		= "pn.`prop_id`=`prop_id` AND pn.`name`=$propName";

	$db->dbValue->group		= '';
	$db->dbValue->fields	= 'pv.valueDigit AS iid';
	$join	= $db->dbValue->makeSQL($sql);
	
	$sql		= array();
	$sql[':join']["($join) AS ids"]	= '`doc_id`=ids.`iid`';

	//	Подготовить SQL запрос
	$ddb		= module('doc');
	$ddb->fields= "$name AS name, `title` AS value, $sort AS sort, 0 AS sort2, count(*) AS cnt";
	$ddb->group	= 'value';
	$ddb->sql	= '';
	//	Вернуть запрос
	//	Выбрать все документы по запросу и выделить диапазоны значений
	$evData[2]	= $ddb->makeSQL($sql);
}
//	Собственно выборка документов по запросу
//	Создать SQL запросы для выборки документов
//	function module_price_parentSQL
function price_parentSQL($val, &$evData)
{
	//	data[0]	- Объект базы данных текущей выборки
	//	data[1]	- Массив значений для выборки
	//	data[2]	- SQL запрос выборки документов
	$db		= $evData[0];
	$id		= $db->id();
	$data	= $db->data;

	$values	= &$evData[1];
	$sql	= &$evData[2];

	//	Подготовить SQL запрос
	$ddb		= module('doc');
	$propName	= ':parent';

	foreach($values as &$value)
	{
		$value	= dbEncString($db, $value);
	}
	$val	= implode(',', $values);
	
	$ids		= array();
	$key		= $ddb->key();
	$ddb->fields= "$key AS iid";
	$docSQL		= $ddb->makeSQL("title IN ($val)");
	$docSQL		= str_replace('`', '', $docSQL);
/*
	$ddb->open("title IN ($val)");
	while($ddb->next()){
		$ids[]	= $ddb->id();
	}
	$ids	= implode(',', $ids);
*/	
//	$table	= $ddb->table();
//	$sql[':join']["(SELECT doc_id FROM $table WHERE title IN ($val)) AS docTitle"]	= '';
	//	Получить свойство из кеша по названию
	$data	= propertyGetInt($db, $propName);
	$propID	= $db->id();

	//	Названия таблиц
	$table		= $db->dbValue->table();
	$table2		= $db->dbValues->table();
	$s	= "SELECT doc_id AS iid$id FROM $table AS p, $table2 AS pv INNER JOIN ($docSQL) AS ids ON pv.`valueDigit`=ids.`iid` WHERE p.`prop_id`=$propID AND p.`values_id`=pv.`values_id`";
	$sql[':join']["($s) AS ids$id"]	= "doc_id=ids$id.iid$id";
	
//	$sql[':IN'][$propID][]	= "pv.valueDigit IN ($ids)";
}
?>
<?
//	function module_price_parentHelp
function price_parentHelp($val, &$evData){ ?>
Группирует свойства по названию родительского каталога (цифровое свойство ":parent").
<? } ?>