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
	
?>
<link rel="stylesheet" type="text/css" href="css/bask.css" />
<?
	$db			= module('doc');

	$s			= array();
	$s['type']	= 'product';
	$s['id']	= array_keys($bask);
	
	$cont	= 0;
	$sql	= array();
	doc_sql($sql, $s);
	
	$db->open($sql);
	if (!$db->rows()) return;

	$items	= array();
	while($data = $db->next()){
		$items[$db->id()] = $data;
	}

	module('script:ajaxLink');
	module('script:ajaxForm');
	m('script:preview');
?>
<link rel="stylesheet" type="text/css" href="css/bask.css">
<div class="bask">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th>&nbsp;</th>
    <th width="100%" nowrap="nowrap">Название товара</th>
    <th nowrap="nowrap">Кол-во</th>
    <th nowrap="nowrap">Цена</th>
    <th nowrap="nowrap">Стоимость</th>
    <th nowrap="nowrap">&nbsp;</th>
</tr>
<?
foreach($bask as $baskID => $count)
{
	$id = $mode = '';
	list($id, $mode)	= explode(':', $baskID);
	$id		= (int)$id;

	$data	= $items[$id];
	$db->setData($data);
	
	$url	= getURL($db->url());
	$class	= testValue('ajax')?' id="ajax"':'';
	
	$price		= docPrice($data);
	$priceName	= priceNumber($price) . ' руб.';
	
	$itemDetail	= '';
	$ev			= array(
		'id'	=> $id,
		'mode'	=> &$mode,
		'price' => &$price,
		'priceName'	=> &$priceName,
		'detail'	=> &$itemDetail
		);
	event('bask.item', $ev);
?>
<tr>
    <td>{{doc:titleImage:$id=size:50x50}}</td>
    <td>
		<a href="{!$url}" id="ajax" class="preview">{$data[title]}</a>
		<div class="baskDetail">{!$itemDetail}</div>
	</td>
    <td nowrap="nowrap"><input type="text" name="baskSet[{$id}]" class="input" value="{$count}" size="2"  /> шт.</td>
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
    <td nowrap="nowrap"><a href="{{getURL:bask_delete$id=mode:$mode}}"{!$class}>удалить</a></td>
</tr>
<? } ?>
</table>
</div>
<? return true; } ?>
