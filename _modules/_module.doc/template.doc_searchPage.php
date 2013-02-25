<?
function doc_searchPage($db, $val, $search){
	$names	= explode(',', 'Бренд,Цена,Цвет');
	$search = getValue('search');
	if (!is_array($search)) $search = array();
	if (!is_array($search['prop'])) $search['prop'] = array();
?>
<form action="{{getURL:search}}" method="post" class="searchForm">
<input name="search[type]" type="hidden" value="product" />
<h2>Поиск товаров по сайту:</h2>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%"><input name="search[name]" type="text" class="input w100" value="{$search[name]}" /></td>
    <td><input type="submit" name="button" class="button" value="Искать" /></td>
</tr>
</table>
<div>
<?
foreach($search['prop'] as $propName => $val)
{
	if (!is_int(array_search($propName, $names))) continue;
	$val	= htmlspecialchars($val);

	$name	= htmlspecialchars($propName);
	echo "<h3>$name: </h3>";

	$s						= $search;
	$s['prop'][$propName]	= '';
	unset($s['prop'][$propName]);
	$url	= getURL('search', makeQueryString($s, 'search'));
?><a href="{$url}">{$val}</a>
<? } ?>
</div>
<div class="search property">
<? 
$ddb	= module('prop');
foreach($names as $ix => &$name) makeSQLValue($name);
$names	= implode(', ', $names);

$ddb->open("`name` IN ($names)");
while($data = $ddb->next())
{
	$iid		= $ddb->id();
	$propName	= $data['name'];
	if (isset($search['prop'][$propName])) continue;
	
	$valueType	= $data['valueType'];
	$typeField	= makeField($valueType);


	$ddb->dbValue->fields	= $typeField;
	$ddb->dbValue->group	= $typeField;
	$ddb->dbValue->order	= $typeField;
	$ddb->dbValue->open("`prop_id` = $iid");
	if (!$ddb->dbValue->rows()) continue;
	
	$name	= htmlspecialchars($propName);
	$bName	= true;
	
	while($d = $ddb->dbValue->next())
	{
		$propValue	= $d[$valueType];
		
		$sql					= array();
		$s						= $search;
		$s['prop'][$propName]	= $propValue;
		doc_sql($sql, $s);
		$db->fields = 'count(*) as cnt';
		$db->open($sql);
		$d		= $db->next();
		@$count	= $d['cnt'];
		if (!$count) continue;

		if ($bName){
			echo "<h3>$name: </h3>";
			$bName = false;
		}
		$name 		= htmlspecialchars($propValue);

		$url	= getURL('search', makeQueryString($s, 'search'));
		echo "<span><a href=\"$url\">$name</a> ($count)</span>";
	}
}
?>
</div>
</form>
<div class="product list">
<? module('doc:read:catalog2', $search);?>
</div>
<? } ?>
