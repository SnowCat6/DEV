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
<link rel="stylesheet" type="text/css" href="bask.css" />
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

	module('script:ajaxLink');
	module('script:ajaxForm');
	m('script:preview');
?>
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
while($data = $db->next()){
	$id		= $db->id();
	$url	= getURL($db->url());
	$price	= docPrice($data);
	$count	= $bask[$db->id()];
	$folder	= docTitleImage($id);
	$class	= testValue('ajax')?' id="ajax"':'';
?>
<tr>
    <td><? displayThumbImage($folder, array(50, 50), '', '', $folder)?></td>
    <td><a href="{!$url}" id="ajax" class="preview">{$data[title]}</a></td>
    <td nowrap="nowrap"><input type="text" name="baskSet[{$id}]" class="input" value="{$count}" size="2"  /> шт.</td>
    <td nowrap="nowrap" class="priceName"><?= priceNumber($price) ?> руб.</td>
    <td nowrap="nowrap" class="priceName"><?= priceNumber($price*$count) ?> руб.</td>
    <td nowrap="nowrap"><a href="{{getURL:bask_delete$id}}"{!$class}>удалить</a></td>
</tr>
<? } ?>
</table>
</div>
<? return true; } ?>
