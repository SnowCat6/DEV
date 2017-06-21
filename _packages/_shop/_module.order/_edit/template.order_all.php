<?
function order_all($db, $val, $data)
{
	m("ajax:template", "ajax_edit");
	$filters	= array(
		'Новые'			=> getURL('order_all_filter', 'filter=new'),
		'В обработке'	=> getURL('order_all_filter', 'filter=received'),
		'Завершенные'	=> getURL('order_all_filter', 'filter=completed'),
		'Удаленные'		=> getURL('order_all_filter', 'filter=rejected'),
	);
	module('script:jq');
	module('script:ajaxLink');
	module('script:ajaxForm');
?>
<module:script:adminTabs />
<script src="script/order_edit.js"></script>

<link rel="stylesheet" type="text/css" href="../css/order.css">
<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
    <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
<? foreach($filters as $name=>$url){ ?>
        <li class="ui-corner-top"><a href="{$url}">{$name}</a></li>
<? } ?>
    </ul>
</div>
<? } ?>

<?
//	+function order_all_filter
function order_all_filter($db, $val, $data)
{
	setTemplate('ajaxResult');
	$filter	= getValue('filter', 'new');


	$search	= getValue('search');
	if (!is_array($search))	$search = array();
	if (!$search['status']) $search['status'] = $filter;
	
	$s		= $search;
	switch($search['status']){
		case 'received':
		$s['status']	= 'received,delivery,wait';
	}
	
	$sql	= array();
	if (@$val = $s['name']){
		$val	= $db->escape_string($val);
		$sql[]	= "`searchField` LIKE ('%$val%')";
	}
	if (@$val = $s['id']){
		$val	= makeIDS($val);
		$sql[]	= "order_id IN ($val)";
	}
	if (@$val = $s['date']){
		$val	= makeDateStamp($val);
		$val	= dbEncDate($db, $val);
		$sql[]	= "orderDate <= $val";
	}
	if (isset($s['status'])){
		$status	= $s['status'];
		$status	= makeIDS($status);
		$sql[]	= "`orderStatus` IN($status)";
	}
?>
<table width="100%">
<td valign="top">
<div class="shop_orders">
<?
$db->order = 'orderDate DESC';
$db->open($sql);
while($data = $db->next())
{
	$id			= $db->id();
	@$orderData	= $data['orderData'];

	@$price	= priceNumber($data['totalPrice']);
	@$name	= trim(implode(' ', $orderData['name']));
	if (!$name) $name = 'no name';
	@$note	= $orderData['textarea'];
	if (!is_array($note)) $note = array();
	$note2	= nl2br(htmlspecialchars($data['orderNote']));
?>
<div class="item">
		<small>
            <strong>№ {$id}</strong> {{date:%d.%m.%Y %H:%i=$data[orderDate]}}
            <strong class="orderPrice">{$price} руб.</strong>
        </small>
        <div class="orderName">
	        <a href="{{getURL:order_edit$id}}">{$name}</a>
        </div>
</div>
<? } ?>
</div>
</td>
<td width="100%" valign="top">
	<div class="shop_order">
    </div>
</td>
</table>
<? } ?>