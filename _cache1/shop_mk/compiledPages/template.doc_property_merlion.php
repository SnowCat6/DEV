<? function doc_property_merlion_update(&$data)
{
	$db	= module('doc', $data);
	$id	= $db->id();
	$d	= $db->openID($id);
	dataMerge($data['fields']['any']['merlion'], $d['fields']['any']['merlion']);
}
function doc_property_merlion(&$data){
	@$fields	= $data['fields'];
	@$merlion	= $fields['any']['merlion'];
	if (!$merlion) return;
	
	m('script:ajaxLink');
	
	$itemID		= $merlion[':merlion_itemID'];
	$parentID	= $merlion[':merlion_parentID'];
	$date		= $merlion[':priceDate'];
	if ($date) $date = date('d.m.Y H:i:s');
?>
<? if ($data['doc_type'] != 'product'){ ?>
<table border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td>Код каталога</td>
    <td><a href="/<? $module_data = array(); $module_data["parent"] = "$itemID"; moduleEx("url:import_merlion", $module_data); ?>" id="ajax"><? if(isset($merlion[":merlion_itemID"])) echo htmlspecialchars($merlion[":merlion_itemID"]) ?></a></td>
  </tr>
  <tr>
    <td>Родительский каталог</td>
    <td><a href="/<? $module_data = array(); $module_data["parent"] = "$parentID"; moduleEx("url:import_merlion", $module_data); ?>" id="ajax"><? if(isset($merlion[":merlion_parentID"])) echo htmlspecialchars($merlion[":merlion_parentID"]) ?></a></td>
  </tr>
  <tr>
    <td>Наценка</td>
    <td><input name="doc[fields][any][merlion][:merlion_price]" type="text" value="<? if(isset($merlion[":merlion_price"])) echo htmlspecialchars($merlion[":merlion_price"]) ?>" class="input" /></td>
  </tr>
</table>
<? }else{ ?>
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>Код товара</td>
    <td><a href="/<? $module_data = array(); $module_data["item"] = "$itemID"; $module_data["parent"] = "$parentID"; moduleEx("url:import_merlion", $module_data); ?>" id="ajax"><? if(isset($merlion[":merlion_itemID"])) echo htmlspecialchars($merlion[":merlion_itemID"]) ?></a></td>
  </tr>
  <tr>
    <td>Родительский каталог</td>
    <td><a href="/<? $module_data = array(); $module_data["parent"] = "$parentID"; moduleEx("url:import_merlion", $module_data); ?>" id="ajax"><? if(isset($merlion[":merlion_parentID"])) echo htmlspecialchars($merlion[":merlion_parentID"]) ?></a></td>
  </tr>
  <tr>
    <td>Цена</td>
    <td>базовая: <? if(isset($merlion[":PriceClient"])) echo htmlspecialchars($merlion[":PriceClient"]) ?>, рассчитанная: rule(<? if(isset($merlion[":PriceClient"])) echo htmlspecialchars($merlion[":PriceClient"]) ?>)*<? if(isset($merlion[":PriceCurrency"])) echo htmlspecialchars($merlion[":PriceCurrency"]) ?>=<? if(isset($data["price_merlion"])) echo htmlspecialchars($data["price_merlion"]) ?></td>
  </tr>
  <tr>
    <td>Правило наценки</td>
    <td><? if(isset($merlion[":PriceRule"])) echo htmlspecialchars($merlion[":PriceRule"]) ?></td>
  </tr>
  <tr>
    <td>Курс</td>
    <td><? if(isset($merlion[":PriceCurrency"])) echo htmlspecialchars($merlion[":PriceCurrency"]) ?>, дата импорта: <? if(isset($date)) echo htmlspecialchars($date) ?></td>
  </tr>
  <tr>
    <td>Наличие</td>
    <td><? if(isset($merlion[":AvailableClient"])) echo htmlspecialchars($merlion[":AvailableClient"]) ?></td>
  </tr>
</table>
<? } ?>
<? return 'Merlion';} ?>
