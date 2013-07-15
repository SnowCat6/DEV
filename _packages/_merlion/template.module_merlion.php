<? function module_merlion($fn, &$data)
{
	if (!hasAccessRole('admin,developer')) return;

	merlionLogin();

	if ($fn){
		@list($fn, $val) = explode(':', $fn, 2);
		$fn = getFn("merlion_$fn");
		return $fn?$fn($val, $data):NULL;
	}
	
	m('script:ajaxLink');
	
	if (getValue('item'))	return merlionProductInfo(getValue('parent'), getValue('item'));
	if (testValue('price') && getValue('parent')) return merlionProductPrice(getValue('parent'));
	if (getValue('parent')) return merlionProduct(getValue('parent'));
	
	merlionCatalogs();
}
function merlionLogin()
{
	$ini	= getCacheValue('ini');
	$merlion= $ini[':merlion'];
    $params = array
	(
	   'wsdl'	=> "https://api-iz.merlion.ru/mlservice.php?wsdl",
	   'login'	=> "$merlion[code]|$merlion[login]",
	   'password' => $merlion['passw']
    );
	m('soap:login', $params);
}

function getShipmentMethods(){
	merlionLogin();
	$xml = module('soap:exec:getShipmentMethods', array('Code'=>''));
	if (!$xml) return array();
	
	$res = array();
	foreach($xml as &$val){
		$res[$val->Code] = array('Description'=>$val->Description, 'IsDefault'=>$val->IsDefault);
	}
	return $res;
}
function getShipmentDates(){
	merlionLogin();
	$xml = module('soap:exec:getShipmentDates', array('Code'=>''));
	if (!$xml) return array();
	
	$res = array();
	foreach($xml as &$val){
		$res[$val->Date] = $val->Date;
	}
	return $res;
}
function getCurrencyRate(){
	merlionLogin();
	$xml = module('soap:exec:getCurrencyRate', array('Date'=>''));
	if (!$xml) return array();
	
	$res = array();
	foreach($xml as &$val){
		$res[$val->Code] = $val->ExchangeRate;
	}
	return $res;
}
?>
<? function merlionCatalogs()
{
	m("page:title", "Каталог Merlion");
	
	$delete = getValue('merlionDelete');
	if (is_array($delete)){
		foreach($delete as $id){ m("doc:update:$id:delete"); }
	}
	
	$data	= array();
	$xml	= module('soap:exec:getCatalog', $data);
	if (!$xml) return messageBox('No XML response');
	
	$items		= array();
	$parents	= array();
	foreach($xml as &$item){
		$items[$item->ID] = $item;
		$parents[$item->ID_PARENT][$item->ID] = $item->ID;
	}
	
	$db	= module('doc');
	$s	= array();
	$s['prop'][':import']	= 'merlion';
	$s['type']				= 'catalog';
	$db->open(doc2sql($s));
	
	$avalible = array();
	while($data = $db->next()){
		$id		= $db->id();
		$prop	= module("prop:get:$id");
		@$parent= $prop[':parent']['property'];
		@$prop	= $data['fields']['any']['merlion'];
		@$article	=$prop[':merlion_itemID'];
		if ($article) $avalible[$article] = array($id, $prop, $parent);
	}

	$merlionCatalog = getValue('merlionCatalog');
	if (is_array($merlionCatalog)) merlionSynchCatalog($merlionCatalog, $avalible, $items);

	$ini		= getCacheValue('ini');
	@$iniMerlion= $ini[':merlion'];
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

<? merlionCatalog($items, $parents, $avalible, '') ?>
<p><input type="submit" class="button" value="Импорт" /></p>
</form>
<? } ?>
<? function merlionCatalog(&$items, &$parents, &$avalible, $parentID, $deep = 0){
	@$childs = $parents[$parentID];
	if (!$childs) return;
?>
<? if (!$deep){ ?>
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
	
	@$data	= $avalible[$parentID];
	@list($data, $prop) = $data;
	@$p		= $prop[':merlion_synch'];
	$class	= $p == 'yes'?' checked="checked"':'';
	$class2	= $data?' class="exist"':'';
	
	@$price	= $prop[':merlion_price'];
	if (!$price) $price = '';
?>
<tr {!$class2}>
    <td class="deep{$deep}">
<input type="hidden" name="merlionCatalog[{$parentID}]" value="0" />
<input type="checkbox" name="merlionCatalog[{$parentID}]" value="1" {!$class} />
<a href="{!$link}" id="ajax">{$name}</a>
    </td>
    <td><a href="{!$link2}" id="ajax">цены</a></td>
    <td>
<input type="text" class="input w00" name="merlionPrice[{$parentID}]" value="{$price}"  />
    </td>
    <td><? if ($data){ ?><input name="merlionDelete[{$parentID}]" type="checkbox" value="{$data}" /><? } ?></td>
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

<? function merlionSynchCatalog(&$merlionCatalog, &$avalible, &$items)
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

	$db				= module('doc');
	$merlionPrice	= getValue('merlionPrice');
	foreach($merlionCatalog as $parentID => $bImport)
	{
		@$data = $avalible[$parentID];
		if (!$data && $bImport != 1) continue;
		
		$bUpdate= false;
		$d		= array();
		list($id, $prop, $parent) = $data;
		if (!$prop) $prop = array();
		
		@$item = $items[$parentID];
		if (!$bImport || !$item)
		{
			$bUpdate 				= true;
			$prop[':merlion_synch']	= 'no';
		}else
		if(@$prop[":merlion_synch"] != 'yes'){
			$bUpdate 				= true;
			$prop[':merlion_synch']	= 'yes';
		}
		
		if (!$id){
			$bUpdate 					= true;
			$d['title']					= $item->Description;
			$d[':property'][':import']	= 'merlion';
			$prop[':merlion_itemID']	= $parentID;
			$prop[':merlion_parentID']	= $item->ID_PARENT;
		}
		
		$price		= $prop[":merlion_price"];
		@$newPrice	= $merlionPrice[$parentID];
		if ($price != $newPrice) {
			$bUpdate 				= true;
			$prop[':merlion_price']	= $newPrice;
		}
		
		$p	= $item?$item->ID_PARENT:NULL;
		if (isset($merlionCatalog[$p])){
			@$p = $avalible[$p];
			if ($p) list($p, ) = $p;
			if ($p == $parent) $p = NULL;
		}else{
			$p = NULL;
		}

		if ($bUpdate)
			$d['fields']['any']['merlion']	= $prop;
		
		if ($id){
//			Не обновлять родителький элемент, если изменен на сайте, можно сделать опцию - привести в соответствие
//			if ($p) $d[':property'][':parent'] = $p;
			if (!$d) continue;
			module("doc:update:$id:edit", $d);
		}else{
			$iid = module("doc:update:$p:add:catalog", $d);
			if (!$iid) continue;
			$avalible[$parentID][0] = $iid;
		}
		$avalible[$parentID][1] = $prop;
		$avalible[$parentID][2] = $p;
	}
}?>
<? function merlionSynchproduct(){
	$db = module('doc');
}?>
<? function merlion_tools($val, $data){ ?>
<p>
<h2>Мерлион</h2>
<a href="{{getURL:import_merlion}}">Каталоги</a> <a href="{{getURL:import_merlion_synch}}">Товары</a>
</p>
<? } ?>


