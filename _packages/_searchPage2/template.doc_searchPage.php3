<?
function doc_searchPage($db, $val, $data)
{
	m('fileLoad', 'css/doc.css');
	m('page:title', 'Поиск по сайту');
	if (!testValue('ajax')) setTemplate('search');
	
	//	Попробуем взять параетры из строки
	list($type, $template) = explode(':', $val);
	//	Если типа документа нет, пробуем взять из данных
	if (!$type) @$type = $data[1];
	//	Проверить на наличие такого типа данных
	$docTypes	= getCacheValue('docTypes');
	if (!isset($docTypes[$type])) $type = '';
	//	Залать то, что показывать документы именно этого типа
	if ($type) $documentType = $type;
	else $documentType = 'news';
	//	Пробуем получить шаблон из данных
	if (!$template) $template	= $data[2];
	//	Сделаем ссылку
	$searchURL	= $type?"search_$type":'search';
	if ($template) $searchURL .= "_$template";
	else $template = 'catalog';

	//	Получить данные для поиска
	$search = getValue('search');
	removeEmpty($search);
	//	Сохранить поиск по имени
	$name	= $search['name'];
	//	Удалить возможные посторонние параетры
	if (isset($search['prop'])){
		//	Сохранить поиск по свойствам
		$search = array('prop' => $search['prop']);
	}else{
		//	Обнулить поиск
		$search = array();
	}
	//	Если был поиск по имени, восстановить
	if ($name) $search['name'] = $name;
	$sql	= array();
	$ev 	= array(&$sql, &$search);
	event('doc.sqlBefore',	$ev);

	//	Кешировать поиск без данных
	$s			= $search;
	$s['type']	= $type?$type:'product';

	$ddb	= module('prop');
	$names	= array();
	//	В зависимости от поиска, исать все параметры или только часть
	$groups	= $search?"globalSearch,globalSearch2":"globalSearch";
	//	Получить свойства и кол-во товаров со свойствами
	$props	= module("prop:name:$groups");
	$n		= implode(',', array_keys($props));
	
	if (!$search){
		$prop	= getCache('pageSearch');
		if (!$prop){
			$prop = $n?module("prop:count:$n", $s):array();
			setCache('pageSearch', $prop);
		}
	}else{
		$prop	= $n?module("prop:count:$n", $s):array();
	}

?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding-left:10px">
<tr>
    <td valign="top" class="searchPage2">
    <div class="search search2 property">
      <div class="title">
        <big>Ваш выбор:</big>
        <?
//	Выведем уже имеющиеся в поиске варианты
$s1		= NULL;
$sProp	= $search['prop'];
if (!is_array($sProp)) $sProp= array();
if ($sProp){ ?><a href="{{getURL:$searchURL}}" class="clear">очистить</a><? }

foreach($sProp as $name => $val){
	//	Сделаем ссылку поиска но без текущего элемента
	$s1		= $search;
	unset($s1['prop'][$name]);
	$url	= getURL($searchURL, makeQueryString($s1, 'search'));
	$val	= propFormat($val, $props[$name]);
	//	Покажем значение
?>
<div><a href="{!$url}" title="{$name}">{!$val}</a></div>
<? } ?>
        </div>
      <?
//	Выведем основные характеристики
$totalCount = 0;
foreach($prop as $name => &$property){
	$totalCount += count($property) + 2;
}

foreach($prop as $name => &$property)
{
	$thisVal = $search['prop'][$name];
	if ($thisVal) continue;
	$note	= $props[$name]['note'];
?>
      <div class="panel">
        <h3 title="{$note}">{$name}:</h3>
        <div>
          <?
$chars	= 0;
foreach($property as $pName => $count){
	$chars	+= strlen($pName) + 5;
}
$nColumns	= floor($chars?$chars/30:1);
$nColumns	= max(1, $nColumns);
$rowLimit	= 0;
$rowLimit = 20;

$ix			= 1;
$close		= '';
foreach($property as $pName => $count)
{
	if ($ix++ == $rowLimit){
		echo '<div class="expand"><div class="expandContent">';
		$close	= '</div></div>';
	}
	
	$s1					= $search;
	$s1['prop'][$name]	= $pName;

	$nameFormat	= propFormat($pName, $props[$name]);
	$url		= getURL($searchURL, makeQueryString($s1, 'search'));
?>
          <span><a href="{!$url}">{!$nameFormat}</a><sup>{$count}</sup></span>
          <? }//	each prperty ?>
          {!$close}
          </div>
        </div>
      <? }// each prop ?>
</div>    </td>
    <td width="100%" valign="top" style="padding-left:20px">
    <h1>{{page:title}}</h1>
<?
$sql = array();
doc_sql($sql, $search);

if ($sql){ ?>
<? if ($p = m("doc:read:$template", $s)){ ?>
    <h2>Результат поиска:</h2>
    {!$p}
<? }else{ ?>
    <h3>По вашему запросу ничего не найдено</h3>
<? } ?>
<? }else{ ?>
{{read:searchPage}}
<? } ?>

    </td>
</tr>
</table>
<? } ?>
