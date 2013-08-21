<? function merlion_catalogs($val, &$data)
{
	m('script:ajaxLink');
	
	if (getValue('item'))	return merlionProductInfo(getValue('parent'), getValue('item'));
	if (testValue('price') && getValue('parent')) return merlionProductPrice(getValue('parent'));
	if (getValue('parent')) return merlionProduct(getValue('parent'));
	
	merlionCatalogs();
}
?>
<? function merlionCatalogs()
{
	m("page:title", "Каталог Merlion");
	
	$data	= array();
	$xml	= module('soap:exec:getCatalog', $data);
	if (!$xml) return messageBox('No XML response');
	
	$items		= array();
	$parents	= array();
	foreach($xml as &$item){
		$items[$item->ID] = $item;
		$parents[$item->ID_PARENT][$item->ID] = $item->ID;
	}
	
	if (getValue('merlionPrice')){
		merlionSynchCatalog($items);
	}
	
	$db	= module('doc');
	$db->sql	=	'';
	$s			= array();
	$s['prop'][':import']	= 'merlion';
	$s['type']				= 'catalog';

	$db->open(doc2sql($s));
	$avalible = array();
	while($data = $db->next()){
		$id		= $db->id();
		$prop	= module("prop:get:$id");
		$parent	= $prop[':parent']['property'];
		$prop	= $data['fields']['any']['merlion'];
		$article=$prop[':merlion_itemID'];
		if ($article) $avalible[$article] = array($id, $prop, $parent);
	}

	$ini		= getCacheValue('ini');
	$iniMerlion	= $ini[':merlion'];
?>
<form action="{{getURL:import_merlion}}" method="post">
<p><input type="submit" class="button" value="Импорт" /></p>
<table border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td>Методы открузки</td>
    <td>
<select name="merlionShipmentMethods" class="input w100">
<?
@$thisValue	= $iniMerlion['ShipmentMethod'];
$m			= getShipmentMethods();
foreach($m as $code => &$val){
	if (isset($thisValue)){
		$class = $thisValue == $code?' selected="selected" class="current"':'';
	}else{
		$class = $val['IsDefault']?' selected="selected" class="current"':'';
	}
?><option value="{$code}"{!$class}>{$val[Description]}</option><? } ?>
</select>
    </td>
  </tr>
  <tr>
    <td>Даты отгрузки</td>
    <td>
<select name="merlionShipmentDates" class="input w100">
<?
@$thisValue	= $iniMerlion['ShipmentDate'];
$m			= getShipmentDates();
foreach($m as &$Date){
	$class = $thisValue == $Date?' selected="selected" class="current"':'';
?><option value="{$Date}"{!$class}>{$Date}</option><? } ?>
</select>
    </td>
  </tr>
  <tr>
    <td>Валюта и курс</td>
    <td>
<select name="merlionShipmentCurrency" class="input w100">
<?
@$thisValue	= $iniMerlion['Currency'];
$m			= getCurrencyRate();
foreach($m as $Code => $val){
	$class = $thisValue == $Code?' selected="selected" class="current"':'';
?><option value="{$Code}:{$val}"{!$class}>{$Code}: {$val}</option><? } ?>
</select>
    </td>
    </tr>
</table>
<style>
td.deep1{
	padding-left:25px;
	font-weight:bold;
}
td.deep2{
	padding-left:50px;
}
td.deep3{
	padding-left:75px;
}
.merlion.table td{
	padding-top:1px;
	padding-bottom:1px;
}
.merlion.table .input{
	font-size:12px;
	padding:1px 4px;
}
.merlion .exist td{
	background:#eee;
}
</style>

<? merlionCatalog($items, $parents, $avalible, '') ?>
<p><input type="submit" class="button" value="Импорт" /></p>
</form>
<? } ?>
<? function merlionCatalog(&$items, &$parents, &$avalible, $parentID, $deep = 0){
	@$childs = $parents[$parentID];
	if (!$childs) return;
?>
<? if (!$deep){ ?>
<table border="0" cellspacing="0" cellpadding="1" class="merlion table">
<tr>
    <th>Структура</th>
    <th>&nbsp;</th>
    <th title="Пример: 50(0-1000), 25(1000-5000), 10">Наценка (%)</th>
    <th>x</th>
</tr>
<? } ?>
<? foreach($childs as $parentID){
	$item	= $items[$parentID];
	$name	= $item->Description;
	$link	= getURL("import_merlion", "parent=".urlencode($parentID));
	$link2	= getURL("import_merlion", "price&parent=".urlencode($parentID));
	
	$data	= $avalible[$parentID];
	list($data, $prop) = $data;
	$p		= $prop[':merlion_synch'];
	$class	= $p == 'yes'?' checked="checked"':'';
	$class2	= $data?' class="exist"':'';
	
	$price	= $prop[':merlion_price'];
	if (!$price) $price = '';
?>
<tr {!$class2}>
    <td class="deep{$deep}">
<input type="checkbox" name="merlionCatalog[{$parentID}]" value="{$parentID}" {!$class} />
<a href="{!$link}" id="ajax">{$name}</a>
    </td>
    <td><a href="{!$link2}" id="ajax">цены</a></td>
    <td><input name="merlionPrice[{$parentID}]" type="text" class="input" value="{$price}" size="60"  /></td>
    <td><? if ($data){ ?><input name="merlionDelete[{$data}]" type="checkbox" value="{$data}" /><? } ?></td>
</tr>
<? merlionCatalog($items, $parents, $avalible, $parentID, $deep + 1) ?>
<? } ?>
<? if (!$deep){ ?>
</table>
<? } ?>
<? } ?>
<? function merlionProduct($parentID)
{
	m("page:title", "Товары Merlion");
	$data = array();
	$data['Cat_id'] = $parentID;
	$xml = module('soap:exec:getItems', $data);
	if (!$xml) return messageBox('No XML response');
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th>Бренд</th>
    <th>Наименование</th>
  </tr>
<? foreach($xml as &$item)
{
	$itemID	= $item->No;
	$link	= getURL("import_merlion", "item=".urlencode($itemID)."&parent=".urlencode($parentID));
	$name	= $item->Name;
	$brand	= $item->Brand;
?>
  <tr>
    <td>{$brand}</td>
    <td><a href="{!$link}" id="ajax">{$name}</a></th>
  </tr>
<? } ?>
</table>
<? } ?>

<? function merlionProductInfo($parentID, $itemID)
{
	$data = array();
	$data['Cat_id']	= $parentID;
	$data['Item_id']= $itemID;
	$xml = module('soap:exec:getItemsProperties', $data);
	if (!$xml){
		messageBox('No XML response');
		return merlionProduct($parentID);
	}
	
	m('page:title', "Код товара: $itemID");
	$url	= getURL('import_merlion', "parent=".urlencode($parentID));
?>
<a href="{!$url}" id="ajax">Каталог</a>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th>Характеристика</th>
    <th>Значение</th>
  </tr>
<? foreach($xml as &$prop){
	$name	= $prop->PropertyName;
	$value	= $prop->Value;
?>
  <tr>
    <td>{$name}</td>
    <td>{$value}</td>
  </tr>
<? } ?>
</table><br />
<?
	$data = array();
	$data['Cat_id']	= '';//	$parentID;
	$data['Item_id']= $itemID;
	$xml = module('soap:exec:getItemsImages', $data);
	if (!$xml) return;

?>
<h2>Изображения товара</h2>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th>Название</th>
    <th>Тип</th>
    <th>Размер</th>
  </tr>
<? foreach($xml as &$image){
	$name	= $image->FileName;
	$szie	= $image->SizeType;
	$type	= $image->ViewType;
?>
  <tr>
    <td><a href="http://img.merlion.ru/items/{$name}" target="_blank">{$name}</a></td>
    <td>{$type}</td>
    <td>{$size}</td>
  </tr>
<? } ?>
</table>
<? } ?>
<? function merlionProductPrice($parentID)
{
	m('page:title', 'Цены товаров');

	merlionLogin();
	$ini				= getCacheValue('ini');
	@$merlion			= $ini[':merlion'];
	
	@$ShipmentMethod= $merlion['ShipmentMethod'];
	@$ShipmentDate	= $merlion['ShipmentDate'];
	if (!$ShipmentMethod || !$ShipmentDate) return;

	$data = array();
	$data['cat_id'] 		= $parentID;
	$data['shipment_method']= $ShipmentMethod;
	$data['shipment_date']	= $ShipmentDate;
//	$data['item_id'] 		= NULL;
	$data['only_avail']		= 1;
	$xml = module('soap:exec:getItemsAvail', $data);
	if (!$xml){
		messageBox('No XML response');
		return;
	}
	$item = next($xml);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
<? foreach($item as $name => $val){ ?>
    <th>{$name}</th>
<? } ?>
  </tr>
<? foreach($xml as &$item){ ?>
  <tr>
<? foreach($item as $name => &$val){ ?>
    <td>{$val}</td>
<? } ?>
  </tr>
<? } ?>
</table>
<? } ?>

<? function merlionSynchCatalog(&$items)
{
	$bUpdate	= false;
	$ini		= getCacheValue('ini');
	if (getValue('merlionShipmentMethods')){
		$bUpdate = true;
		$ini[':merlion']['ShipmentMethod'] = getValue('merlionShipmentMethods');
	}
	if (getValue('merlionShipmentDates')){
		$bUpdate = true;
		$ini[':merlion']['ShipmentDate'] = getValue('merlionShipmentDates');
	}
	if (getValue('merlionShipmentCurrency')){
		$bUpdate = true;
		$ini[':merlion']['Currency'] = getValue('merlionShipmentCurrency');
	}
	if ($bUpdate){
		setIniValues($ini);
	}
	
	$delete			= getValue('merlionDelete');
	$merlionPrice	= getValue('merlionPrice');
	$merlionCatalog	= getValue('merlionCatalog');

	$db			= module('doc');
	$db->sql	=	'';
	$s			= array();
	$s['prop'][':import']	= 'merlion';
	$s['type']				= 'catalog';

	$pass		= array();
	$db->open(doc2sql($s));
	while($data = $db->next())
	{
		$id		= $db->id();
		$prop	= $data['fields']['any']['merlion'];
		if (!is_array($prop)) $prop = array();
		$itemID	=$prop[':merlion_itemID'];
		if ($itemID) $pass[$itemID] = $id;
		
		if ($delete[$id]){
			m("doc:update:$id:delete");
			continue;
		}

		$bUpdate= false;
		$d		= array();
		
		$item	= $items[$itemID];
		if (!$merlionCatalog[$itemID] || !$item)
		{
			$merlionCatalog[$itemID]	= NULL;
			unset($itemID);
			$bUpdate 				= true;
			$prop[':merlion_synch']	= 'no';
		}else
		if($prop[":merlion_synch"] != 'yes'){
			$bUpdate 				= true;
			$prop[':merlion_synch']	= 'yes';
		}
		
		$price		= $prop[":merlion_price"];
		$newPrice	= $merlionPrice[$itemID];
		if ($price != $newPrice) {
			$bUpdate 				= true;
			$prop[':merlion_price']	= $newPrice;
		}
		
			if ($bUpdate)	$d['fields']['any']['merlion']	= $prop;
//			Не обновлять родителький элемент, если изменен на сайте, можно сделать опцию - привести в соответствие
//			if ($p) $d[':property'][':parent'] = $p;
			if ($d) module("doc:update:$id:edit", $d);
/*
			$d['title']					= $item->Description;
			$d[':property'][':import']	= 'merlion';
			$prop[':merlion_itemID']	= $parentID;
			$d['fields']['any']['merlion']	= $prop;
			$prop[':merlion_parentID']	= $item->ID_PARENT;
			$iid = module("doc:update:$p:add:catalog", $d);
			if (!$iid) continue;
*/	}
	foreach($items as &$item)
	{
		$itemID		= $item->ID;
		if ($pass[$itemID]) continue;
		if (!$merlionCatalog[$itemID]) continue;
		
		$prop		= array();
		$parentID	= $item->ID_PARENT;
		$name		= $item->Description;
		$newPrice	= $merlionPrice[$itemID];
		$parent		= $pass[$parentID];
		if ($delete[$parent]) $parent = 0;

		$d['title']					= $name;
		$d[':property'][':import']	= 'merlion';
		$prop[':merlion_synch']		= 'yes';
		$prop[':merlion_itemID']	= $itemID;
		$prop[':merlion_price']		= $newPrice;
		$prop[':merlion_parentID']	= $parentID;
		$d['fields']['any']['merlion']	= $prop;
		$iid = module("doc:update:$parent:add:catalog", $d);
		
		$pass[$itemID]	= $iid;
	}
}?>
<? function merlionSynchproduct(){
}?>
