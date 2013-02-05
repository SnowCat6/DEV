<? function doc_page_catalog(&$db, &$menu, &$data){
	$id = $db->id();
?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css"/>
{beginAdmin}
<h2>{!$data[title]}</h2>
{document}
{endAdminTop}

<p>{{doc:read:menu=parent:$id;type:catalog}}</p>
<?
$search = getValue('search');
if (!is_array($search)) $search = array();
$search = array('prop' => $search);

$s	= array();
$sql= array();
$s['parent'] = $id;
dataMerge($s, $search);
doc_sql(&$sql, $s);
?>
<? if (beginCompile($data, $searchHash = "search_".hashData($sql))){ ?>
<?
$ids = array();
$db->open($sql);
while($db->next()) $ids[] = $db->id();
$ids = makeIDS($ids);
?>
<? if ($prop = module("prop:get:$ids:Свойства товара")){ ?>
<table width="100%" cellpadding="0" cellspacing="0" class="search property">
<tr><td colspan="2" class="title">
<big>Ваш выбор:</big>
<?
foreach($search['prop'] as $name => $val){
	if (!isset($prop[$name])) continue;
	
	$s		= $search;
	unset($s['prop'][$name]);
	$url	= getURL("page$id", makeQueryString($s['prop'], 'search'));
?>
<span><a href="{!$url}">{$val}</a></span>
<? } ?>
</div>
</td><tr>
<?
$ddb = module('doc');
foreach($prop as $name => $val)
{
	if ($name[0] == ':') continue;
	
	$property = $val['property'];
	if (!$property) continue;
	if (isset($search['prop'][$name])) continue;
	
	$property = explode(', ', $property);
	@$thisVal = $search['prop'][$name];
?>
<tr>
    <th>{$name}:</th>
    <td width="100%">
<?
foreach($property as $p)
{
	$class	= $thisVal == $p?' selected="selected"':'';

	$s					= $search;
	$s['parent']		= $id;
	$s['prop'][$name]	= $p;

	$sql= array();
	doc_sql($sql, $s);
	$ddb->open($sql);
	$count	= $ddb->rows();

	$nameFormat	= propFormat($p, $val);
	$url		= getURL("page$id", makeQueryString($s['prop'], 'search'));
?>
<span><a href="{!$url}">{!$nameFormat}</a> ({$count})</span>
<? }//	each prperty ?>
	</td>
</tr>
<? }// each prop ?>
</table>
<? }// if prop ?>
<? endCompile($data, $searchHash); }// compile ?>
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