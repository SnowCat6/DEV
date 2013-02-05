<? function doc_page_catalog(&$db, &$menu, &$data){
	$id = $db->id();
?>
{beginAdmin}
<h2>{!$data[title]}</h2>
{document}
{endAdminTop}

<p>{{doc:read:menu=parent:$id;type:catalog}}</p>
<?
$search = getValue('search');
if (!is_array($search)) $search = array();

$s	= array();
$sql= array();
$s['parent'] = $id;
dataMerge($s, $search);
doc_sql(&$sql, $s);

$ids = array();
$db->open($sql);
while($db->next()) $ids[] = $db->id();
$ids = makeIDS($ids);

$prop = module("prop:get:$ids:Свойства товара");
?>
<? if ($prop){ ?>
<form action="{{getURL:page$id}}" method="post">
<div>
<?

foreach($prop as $name => $val){
	if ($name[0] == ':') continue;
	$property = $val['property'];
	if (!$property) continue;
	$property = explode(', ', $property);
	
	@$thisVal = $search['prop'][$name];
?>
<div>
{$name}:
<div>
<select name="search[prop][{$name}]">
	<option value="">любое значение</option>
<?
foreach($property as $p){
	$class = $thisVal == $p?' selected="selected"':''
?>
	<option value="{$p}"{!$class}><?= propFormat($p, $val)?></option>
<? } ?>
</select>
</div>
</div>
<? } ?>
</div>
<p><input type="submit" value="Поиск" class="button" /></p>
</form>
<? } ?>
<div class="product list">
<?
	$search = getValue('search');
	if ($search){
?>
<h2>Поиск по каталогу</h2>
<?
		$search['parent'] = $id;
		module('doc:read:catalog', $search);
?>
<? }else{ ?>
{{doc:read:catalog=parent:$id;type:product}}
<? } ?>
</div>
<? } ?>