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
    <td><a href="{{url:import_merlion=parent:$itemID}}" id="ajax">{$merlion[:merlion_itemID]}</a></td>
  </tr>
  <tr>
    <td>Родительский каталог</td>
    <td><a href="{{url:import_merlion=parent:$parentID}}" id="ajax">{$merlion[:merlion_parentID]}</a></td>
  </tr>
  <tr>
    <td>Наценка</td>
    <td><input name="doc[fields][any][merlion][:merlion_price]" type="text" value="{$merlion[:merlion_price]}" class="input" /></td>
  </tr>
</table>
<? }else{ ?>
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>Код товара</td>
    <td><a href="{{url:import_merlion=item:$itemID;parent:$parentID}}" id="ajax">{$merlion[:merlion_itemID]}</a></td>
  </tr>
  <tr>
    <td>Родительский каталог</td>
    <td><a href="{{url:import_merlion=parent:$parentID}}" id="ajax">{$merlion[:merlion_parentID]}</a></td>
  </tr>
  <tr>
    <td>Цена</td>
    <td>базовая: {$merlion[:PriceClient]}, рассчитанная: rule({$merlion[:PriceClient]})*{$merlion[:PriceCurrency]}={$data[price_merlion]}</td>
  </tr>
  <tr>
    <td>Правило наценки</td>
    <td>{$merlion[:PriceRule]}</td>
  </tr>
  <tr>
    <td>Курс</td>
    <td>{$merlion[:PriceCurrency]}, дата импорта: {$date}</td>
  </tr>
  <tr>
    <td>Наличие</td>
    <td>{$merlion[:AvailableClient]}</td>
  </tr>
</table>
<? } ?>
<? return 'Merlion';} ?>
