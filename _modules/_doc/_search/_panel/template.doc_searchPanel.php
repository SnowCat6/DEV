<? function doc_searchPanel(&$db, &$panelType, &$data)
{
	if (!$panelType) $panelType = 'default';

	//	Всякие настройки
	$options	= $data['options'];
	if (!is_array($options)) $options = array();
	
	$data['options']	= '';
	removeEmpty($data);
	
	//	Получить данные для поиска
	if (!$options['searchName'])
		$options['searchName']	= 'search';

	//	Подготовить данные поисковой строки	
	$search	= getValue($options['searchName']);
	if (!is_array($search)) $search = array();
	else removeEmpty($search);
	$options['search']	= $search;

	//	Сформировать данные для передачи помимо поиска
	$options['qs']	= array();
	foreach(array($options['searchName'], 'order', 'pages') as $name){
		$options['qs'][$name]	= getValue($name);
	}
	
	//	Сформировать запрос к базе данных
	$s		= $search;
	dataMerge($s, $data);

	//	Предобработка поиска пользовательскими фильтрами
	$sql	= array();
	$ev 	= array(&$sql, &$s);
	event('doc.sqlBefore',	$ev);
	$options['query']	= $s;

	//	Если имена свойств не заданы, сформировать автоматически из настроек
	if (!$options['names'])
	{
		//	В зависимости от поиска, исать все параметры или только часть
		$groups	= $options['groups'];
		if (!$groups) $groups	= $options['search']?"globalSearch,globalSearch2":"globalSearch";
		//	Получить свойства и кол-во товаров со свойствами
		$props				= module("prop:name:$groups");
		$options['names']	= implode(',', array_keys($props));
	}
	
	//	Получить свойства по параметрам
	$q		= $options['useQuery']?$data:$options['query'];
	$props	= $options['names']?module("prop:count: $options[names]", $q):array();

	//	Сформировать скрытые поля и текущий выбор
	$thisChoose		= array();
	$hiddenFields	= $search;
	foreach($props as $propertyName => $values)
	{
		$hiddenFields['prop'][$propertyName] = '';
		if ($search['prop'][$propertyName]){
			$thisChoose[$propertyName] = $search['prop'][$propertyName];
		}
	}
	removeEmpty($hiddenFields);
	if (!$options['hasChoose']) $thisChoose = array();
	$options['choose']	= $thisChoose;
	$options['hidden']	= $hiddenFields;
	
	//	Сохранить свойства для функции
	$data['options']	= $options;

	if ($options['choose'] || $props){
		//	Выполнить отображение панели поиска
		$fn	= getFn("searchPanel_$panelType");
		if ($fn) $fn($data, $props);
	}
	
	//	Вернуть строку поиска
	return $options['query'];
}
?>
