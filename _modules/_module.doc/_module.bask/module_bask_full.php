<?
function bask_full($bask, $val, &$data)
{
	
	module('script:ajaxLink');
	
	module('page:title', 'Корзина');
	
	$s			= array();
	$s['type']	= 'product';
	$s['id']	= array_keys($bask);
	
	$cont	= 0;
	$sql	= array();
	doc_sql(&$sql, $s);
	
	$db = module('doc');
	$db->open($sql);
?>
<link rel="stylesheet" type="text/css" href="bask.css" />
<div class="bask">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th>&nbsp;</th>
    <th width="100%">Название товара</th>
    <th nowrap="nowrap">Кол-во</th>
    <th nowrap="nowrap">Цена</th>
    <th nowrap="nowrap">Стоимость</th>
</tr>
<?
while($data = $db->next()){
	$id		= $db->id();
	$url	= getURL($db->url());
	$price	= docPrice($data);
	$count	= $bask[$db->id()];
	$folder	= docTitle($id);
?>
<tr>
    <td><? displayThumbImage($folder, array(50, 50), '', '', $folder)?></td>
    <td><a href="<?= $url?>" id="ajax"><?= htmlspecialchars($data['title'])?></a></td>
    <td nowrap="nowrap"><?= $count ?> шт.</td>
    <td nowrap="nowrap" class="priceName"><?= $price ?></td>
    <td nowrap="nowrap" class="priceName"><?= $price*$count ?></td>
</tr>
<? } ?>
</table>
</div>
<? } ?>