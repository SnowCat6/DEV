<?
function order_order($db, $val, $bask)
{
	noCache();
	$order = getValue('order');
	if (is_array($order)){
		$order['bask']	= $bask;
		$id = module('order:update', $order);
		if ($id){
			$bask	= array();
			setBaskCookie($bask);
			$ajax	= testValue('ajax')?'ajax&':'';
			redirect(getURL("order$id", $ajax.'key='.md5("order$id")));
		}
	}
?>
<h1>Оформление заказа:</h1>
{{read:orderBefore}}
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table">
  <tr>
    <td nowrap="nowrap">Ф.И.О.</td>
    <td width="100%"><input type="text" name="order[name]" value="{$order[name]}" class="input w100" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Контактный телефон</td>
    <td><input type="text" name="order[phone]" value="{$order[phone]}" class="input w100" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Контактный e-mail</td>
    <td><input type="text" name="order[email]" value="{$order[email]}" class="input w100" /></td>
  </tr>
</table>
<div>Ваш комментарий</div>
<div><textarea name="order[note]" rows="3" class="input w100">{$order[email]}</textarea></div>
<p><input name="doMakeOrder" type="submit" class="button" value="Оформить заказ"></p>
{{read:orderAfter}}
<? } ?>