<? function user_page($db, $id, $data)
{
	m('script:jq');

	$userLogin	= getValue('userLogin');
	$userData	= userData();
	if (is_array($userLogin)){
		if ($userLogin['passw'] != $userLogin['passw2']){
			m('message:error', 'Введите правильно действующий пароль');
		}else
		if (!$userLogin['passwNew']){
			m('message:error', 'Введите новый пароль');
		}else
		if (!module('user:checkLogin', array('login'=>$userData['login'], 'passw'=>$userLogin['passw']))){
			m('message:error', 'Введите правильно действующий пароль');
		}else{
			m("user:update:$id:edit",  array('login'=>$userData['login'], 'passw'=>$userLogin['passwNew']));
			m('message', 'Пароль изменен');
		}
	}
	
?><? $module_data = array(); $module_data[] = "Личный кабинет"; moduleEx("page:title", $module_data); ?><? module("page:style", 'useroffice.css') ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="order select">
  <tr>
    <td>
<table wborder="0" cellspacing="0" cellpadding="0">
  <tr>
    <th>Заказы</th>
    <td><a href="#order_new" class="current">Принятые</a></td>
    <td><a href="#order_complete"  class="center">Выполенные</a></td>
    <td><a href="#order_rejected" >Отмененные</a></td>
  </tr>
</table>
    </td>
    <td align="right">
<table wborder="0" cellspacing="0" cellpadding="0">
  <tr>
<!--
    <td><a href="#order_bill">Платежи</a></td>
    <td><a href="#order_bonus"  class="center">Баллы и скидка</a></td>
-->
    <td><a href="#order_settings" >Настройки</a></td>
  </tr>
</table>
    </td>
  </tr>
</table>

<div class="order">
    <div class="content current" id="order_new"><? $module_data = array(); $module_data["userID"] = "$id"; $module_data["status"] = "new,received,delivery,wait"; moduleEx("order:show", $module_data); ?></div>
    <div class="content" id="order_complete"><? $module_data = array(); $module_data["userID"] = "$id"; $module_data["status"] = "complete"; moduleEx("order:show", $module_data); ?></div>
    <div class="content" id="order_rejected"><? $module_data = array(); $module_data["userID"] = "$id"; $module_data["status"] = "rejected"; moduleEx("order:show", $module_data); ?></div>

    <div class="content" id="order_settings">
<div class="login">
<? module("page:message"); ?>
<form action="" method="post" class="ajaxFormNow">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <th>Действующий пароль</th>
  </tr>
  <tr>
    <td><input name="userLogin[passw]" type="text" class="input w100" /></td>
  </tr>
  <tr>
    <th>Подтвердите пароль</th>
  </tr>
  <tr>
    <td><input name="userLogin[passw2]" type="text" class="input w100" /></td>
  </tr>
  <tr>
    <th>Новый пароль</th>
  </tr>
  <tr>
    <td><input name="userLogin[passwNew]" type="text" class="input w100" /></td>
  </tr>
  <tr>
    <td><input name="" type="submit" class="button" value="Подтвердить" /></td>
  </tr>
</table>
</form>
</div>
    </div>
</div>
<script>
$(function(){
	$(".order.select a").click(function()
	{
		var id = $(this).attr("href");
		$(".order.select a").removeClass("current");
		$(".order .content").removeClass("current");
		$(this).addClass("current");
		$(".order .content" + id).addClass("current");
		return false;
	});
});
</script>
<? event('user.page', $id)?><? } ?>
