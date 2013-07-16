<? function order_show($db, $val, $search)
{
	$sql	= array();
	if (isset($search['userID'])){
		$userID	= makeIDS($search['userID']);
		$sql[]	= "`user_id` = $userID";
	}
	if (isset($search['status'])){
		$status	= makeIDS($search['status']);
		$sql[]	= "`orderStatus` IN($status)";
	}
	$data	= $db->open($sql);
	if (!$data) return messageBox('Нет заказов');

	$ddb		= module('doc');	
	$orderTypes	= getCacheValue('orderTypes');
	m('script:orderShow');
	m('script:ajaxLink');
?>
<? module("page:style", 'useroffice.css') ?>
<? module("page:style", 'order.css') ?>

<div class="order office">
<? while($data = $db->next()){
	$id		= $db->id();
	$date	= date('d.m.Y H:i', makeDate($data['orderDate']));
	$bask		= unserialize($data['orderBask']);
	$typeName	= $orderTypes[$data['orderStatus']];
?>
<h3 id="order<? if(isset($id)) echo htmlspecialchars($id) ?>"><span>+</span><b>№ <? if(isset($id)) echo htmlspecialchars($id) ?></b></h3>
<div class="fullInfo" id="order<? if(isset($id)) echo htmlspecialchars($id) ?>">
<div class="detail">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?
foreach($bask as $iid => &$d){
	$title	= docTitleImage($iid);
	$price	= priceNumber($d['orderPrice']);
	$url	= getURL($ddb->url($iid));
?>
  <tr>
    <td><? displayThumbImage($title, array(64, 42), '', '', $title) ?></td>
    <td><a href="/<? if(isset($url)) echo $url ?>" id="ajax"><? if(isset($d["title"])) echo htmlspecialchars($d["title"]) ?></a></td>
    <td nowrap><? if(isset($d["orderCount"])) echo htmlspecialchars($d["orderCount"]) ?> шт.</td>
    <td nowrap class="priceName"><? if(isset($price)) echo htmlspecialchars($price) ?> руб.</td>
  </tr>
<? } ?>
  <tr>
    <th>&nbsp;</th>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td  class="common" align="right">
 <table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <th>Итого</th>
    <td class="priceName"><? if(isset($data["totalPrice"])) echo htmlspecialchars($data["totalPrice"]) ?> руб.</td>
  </tr>
  <tr>
    <th>Статус заказа</th>
    <td class="status orderStatus_<? if(isset($data["orderStatus"])) echo htmlspecialchars($data["orderStatus"]) ?>"><? if(isset($typeName)) echo htmlspecialchars($typeName) ?></td>
  </tr>
</table>
   </td>
  </tr>
</table>
</div>
</div>
<div class="compactInfo" id="order<? if(isset($id)) echo htmlspecialchars($id) ?>">
<? foreach($bask as $iid => &$d){
	$url	= getURL($ddb->url($iid));
?>
<div><a href="/<? if(isset($url)) echo $url ?>" id="ajax"><? if(isset($d["title"])) echo htmlspecialchars($d["title"]) ?></a></div>
<? } ?>
</div>
<? } ?>
</div>
<? } ?>
<? function script_orderShow($val){ m('script:jq'); ?>
<script>
$(function(){
	$(".order .fullInfo").hide();
	$(".order .compactInfo").show();
	$(".order h3").click(function(){
		var id = $(this).attr("id");
		
		$(".order .fullInfo").hide();
		$(".order .compactInfo").show();
		
		if ($(this).hasClass('showFull')){
			$(".order .compactInfo#" + id).show();
			$(".order h3").removeClass("showFull");
		}else{
			$(".order .fullInfo#" + id).show();
			$(".order .compactInfo#" + id).hide();
			$(".order h3").removeClass("showFull");
			$(this).addClass("showFull");
		}
	});
});
</script>
<? } ?>