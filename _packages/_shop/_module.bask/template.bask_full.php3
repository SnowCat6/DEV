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
	event('bask.queryFilter', $bask);
	if (!$bask) return;
?>
<link rel="stylesheet" type="text/css" href="css/bask.css" />
<?


	$db			= module('doc');

	$s			= array();
	$s['type']	= 'product';
	$s['id']	= array_keys($bask);
	event('bask.query', $s);
	
	$cont	= 0;
	$sql	= array();
	doc_sql($sql, $s);
	
	$db->open($sql);
	$items	= array();
	while($data = $db->next())
		$items[$db->id()] = $data;
	if (!$items) return;

	module('script:ajaxLink');
	module('script:ajaxForm');
	m('script:preview');
?>
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
foreach($bask as $baskID => $count)
{
	$id = $mode = '';
	list($id, $mode)	= explode(':', $baskID, 2);
	$id		= (int)$id;

	$data	= $items[$id];
	$db->setData($data);
	
	$url	= getURL($db->url());
	$class	= testValue('ajax')?' id="ajax"':'';
	
	$price		= docPrice($data);
	$priceName	= priceNumber($price) . ' руб.';
	
	$itemTitle	= $data['title'];
	$itemDetail	= '';
	$itemClass	= 'preview';
	$ev			= array(
		'id'	=> $id,
		'baskID'=> $baskID,
		'mode'	=> &$mode,
		'price' => &$price,
		'priceName'	=> &$priceName,
		'detail'	=> &$itemDetail,
		'itemTitle'	=> &$itemTitle,
		'itemClass'	=> &$itemClass
		);
	event('bask.item', $ev);
	$totalPrice += $price*$count;
?>
<tr>
    <td>
    	<module:doc:titleImage +=":$id" size="50x50" />
    </td>
    <td>
		<a href="{!$url}" id="ajax" class="{$itemClass}">{$itemTitle}</a>
		<div class="baskDetail">{!$itemDetail}</div>
	</td>
    <td nowrap="nowrap"><input type="text" name="baskSet[{$baskID}]" class="input" value="{$count}" size="2"  /> шт.</td>
    <td nowrap="nowrap" class="priceName">
<? if ($price){ ?>
	<?= $priceName ?>
<? } ?>
	</td>
    <td nowrap="nowrap" class="priceName">
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
  <td >&nbsp;</td>
  <td colspan="2" class="baskTotalPrice">
      <h2>Итого:</h2>
      <?= priceNumber($totalPrice) ?> руб.
  </td>
  <td>&nbsp;</td>
</tr>
</table>
</div>
<? return true; } ?>
