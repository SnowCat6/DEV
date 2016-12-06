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
    <div>
      № <strong>{$id}</strong> от {{date:%d.%m.%Y %H:%i=$data[orderDate]}}, стоимость <b>{$price} руб.</b>
    </div>
     <big>
        <a href="{{getURL:order_edit$id}}" id="ajax">{$name}</a>
    </big>
<? if ($note || $note2){ ?>
    <blockquote>
<? if ($note2){ ?>
    <div class="orderNote manager">
    	{!$note2}
    </div>
<? } ?>
<? if ($note){ ?>
    <div class="orderNote">
	<? foreach($note as $name => $val)
		$val = nl2br(htmlspecialchars($val));
	{?>
    	<div><b>{$name}:</b></div>{!$val}
	<? } ?>
    </div>
<? } ?>
	</blockquote>
<? } ?>
</div>
<? } ?>
</div>
<? } ?>