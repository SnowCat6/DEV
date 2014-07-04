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
	$table1	= $db->table();
	$table2	= $db->dbValues->table();

	//	SQL запрос, передаются идентификатоы выбираемых документов
	$sql					= $evData[1];
	$sql[':from'][]			= 'p';
	$sql[':from'][$table1]	= 'pn';
	$sql[':from'][$table2]	= 'pv';
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

	//	Получить свойство из кеша по названию
	$data	= propertyGetInt($db, $propName);
	$propID	= $db->id();

	//	Названия таблиц
	$table	= $db->dbValue->table();
	$table2	= $db->dbValues->table();
	$s		= "SELECT doc_id FROM $table AS p, $table2 AS pv, ($docSQL) AS ids WHERE p.`prop_id`=$propID AND p.`values_id`=pv.`values_id` AND pv.`valueDigit`=ids.`iid`";
	$sql[':from']["($s)"]	= "ids$id";
	$sql[]	= "`doc_id`=ids$id.`doc_id`";
}
?>
<?
//	function module_price_parentHelp
function price_parentHelp($val, &$evData){ ?>
Группирует свойства по названию родительского каталога (цифровое свойство ":parent").
<? } ?>