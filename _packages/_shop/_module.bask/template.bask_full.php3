<?
function bask_full($bask, $val, &$data)
{
	module('nocache');

	$action = getValue('baskSet');
	if (is_array($action))
	{
		foreach($action as $id => $count) $bask[$id] = $count;
		setBaskCookie($bask);
	}
	$items	= module("bask:items");
	if (!$items) return;

	module('script:ajaxLink');
	module('script:ajaxForm');
	m('script:preview');
?>
<link rel="stylesheet" type="text/css" href="css/bask.css" />
<link rel="stylesheet" type="text/css" href="css/bask.css">
<div class="bask">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table baskTable">
<tr>
    <th>&nbsp;</th>
    <th width="100%" nowrap="nowrap">Название товара</th>
    <th nowrap="nowrap">Кол-во</th>
    <th nowrap="nowrap">Цена</th>
    <th nowrap="nowrap">Стоимость</th>
    <th nowrap="nowrap">&nbsp;</th>
</tr>
<?
$totalPrice	= 0;
$db			= module('doc');
foreach($items as $baskID => $data)
{
	$db->setData($data);
	$url	= getURL($db->url());
	$id		= $db->id();
	$mode	= $data['mode'];
	$price 	= $data['price'];
	$count	= $data['count'];
	$totalPrice += $price*$count;
	$class	= testValue('ajax')?' id="ajax"':'';
?>
<tr>
    <td>
    	<module:doc:titleImage +=":$id" size="50x50" />
    </td>
    <td>
		<a href="{$url}" id="ajax" class="{$data[itemClass]}">{$data[title]}</a>
		<div class="baskDetail">{!$data[itemDetail]}</div>
	</td>
    <td nowrap="nowrap">
    	<input type="text" name="baskSet[{$baskID}]" class="input" value="{$data[count]}" size="2" price="{$price}"  /> шт.
     </td>
    <td nowrap="nowrap" class="priceName">
		{$data[priceName]}
	</td>
    <td nowrap="nowrap" class="priceName pricePrice">
<? if ($price && $count){ ?>
	<?= priceNumber($price*$count) ?> руб.
<? } ?>
	</td>
    <td nowrap="nowrap">
        <a href="{{getURL:bask_delete$id=mode:$mode}}"{!$class}>удалить</a>
    </td>
</tr>
<? } ?>
<tr>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
  <td colspan="2" class="baskTotalPrice">
      <h2>Итого:</h2>
      <span class="priceTotal"><?= priceNumber($totalPrice) ?></span> руб.
  </td>
  <td>&nbsp;</td>
</tr>
</table>
</div>
<? return true; } ?>
