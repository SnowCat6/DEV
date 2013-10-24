<?
m('script:jq');
$db			= module('doc');
$id			= currentPage();
$data		= $db->openID($id);
$bgImage	= docTitleImage($id);
$menu		= doc_menu($id, $data);

if (hasAccessRole('admin,writer') && testValue('managerID')){
	$d	= array();
	$d['fields']['any']['managerID']	= (int)getValue('managerID');
	m("doc:update:$id:edit", $d);
	$data	= $db->openID($id);
}

$dbUser		= module('user');
$userID		= $data['fields']['any']['managerID'];
$userData	= $dbUser->openID($userID);
$folder		= $dbUser->folder();

$managerName= m('user:name:full', $userData);

$person = userPerson($userData);
$phone	= $person['phone'];
if ($phone) $phone = "<h2>$phone</h2>";

$files	= getFiles("$folder/Title", '');
@list($titleImage, $titleImagePath)	= each($files);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta charset="utf-8">
<? module("page:style", '../../style.css') ?><? ob_start(); ?>
<style>
img{
	border:0;
}
.promoBody{
	text-align:center;
}
.promoHead{
	position:relative;
	background: url("<?= $bgImage?>") top center repeat-x;
	color:white;
}
.promoHead2{
	min-height:850px;
	background:url(../../design/volnaBG.png) bottom center repeat-x;
}
.promoLogo{
	background:rgb(60, 60, 60);
	background: rgba(0, 0, 0, 0.4);
	border-radius:10px;
	padding:10px 20px;
	width:500px;
	min-width:500px;
	margin:auto; margin-bottom:10px;
	font-size:18px;
	text-align:center;
}
.promoLogo *{
	margin:0;
}
.promoHeadContent{
	width:960px;
	min-width:960px;
	margin:auto;
}
.promoTitle{
	margin:0;
	font-size:55px;
	font-weight:normal;
	padding:20px 0;
	text-shadow:2px 2px 4px #000;
	font-family:Gotham, "Helvetica Neue", Helvetica, Arial, sans-serif;
	font-style:italic;
}
.promoHeadInfo td{
	width:33%;
}

.promoDocument
{
	display:block;
	width:960px;
	min-width:960px;
	margin:auto;
	text-align:left;
}

.promoDocument h1{
	display:block;
	background:url(design/promoIcon.png) no-repeat -20px -225px;
	padding-left:45px;
	font-size:35px;
	color:#c80303;
	font-weight:normal;
	margin-bottom:10px;
}
.quote .promoDocument h1{
	color:#49a3be;
	background:url(design/promoIcon.png) no-repeat -20px -295px;
}
.promoCopyright{
	background:#ff9c00;
	padding:10px;
	color: #ad3100;
	text-shadow:0 1px 1px #ffd490;
}
.promoCopyright a{
	color: #ad3100;
}
.promoCopyright h2{
	color:white;
	text-shadow:0 1px 1px #a40;
	font-size:28px;
	font-weight:normal;
	margin:5px 0;
}
.promoDocument .tabs{
	width:450px;
}
.promoDocument #tabMap img{
	width:100%;
	height:auto;
}
.promoSlot{
	background:rgb(60, 60, 60);
	background:rgba(0, 0, 0, 0.4);
	border-radius:10px;
	padding:5px;
	width:280px;
	margin:auto;
	text-shadow:0 2px 4px #000;
	margin-top:10px;
	font-size:28px;
	height:210px;
	min-height:210px;
	position:relative;
}
.promoSlot *{
	margin:0;
}
.promoSlot h2{
	margin:0; margin-bottom:10px;
	font-weight:normal;
	font-size:25px;
}
.promoSlot strong{
	font-size:50px;
	font-weight:normal;
}
.promoSlot p{
	margin:0;
}
.promoSlotImage{
	height:301px;
}
.promoSlotImage *{
	margin:0;
}
.promoButton{
	display:block;
	background:#ff8400;
	padding:5px;
	border-radius:10px;
	color:white;
	text-decoration:none;
	text-shadow:0 2px 4px #a05300;
	font-size:24px;
	margin-top:10px;
	position:absolute;
	bottom: 5px; left:5px; right: 5px;
}
.promoContent1 *{
	margin:0;
}
.promoContent1 h2{
	color:#ff8400;
}
.promoContent1 td{
	padding:0 5px;
	vertical-align:top;
}
.promoBody .icon{
	width:70px;
	height:70px;
	margin:auto;
	background:url(design/promoIcon.png) no-repeat;
}
.promoBody .icon2{
	background-position:0 -70px;
}
.promoBody .icon3{
	background-position:0 -140px;
}
.promoBody .icon6{
	background-position:0 -340px;
}
.promoContent2 *{
	margin:0;
}
.promoContent2 th{
	padding:20px 0;
	vertical-align:central;
}
.promoContent2 td{
	padding-left:20px;
}
.promoManager h3{
	margin:0;
	color:#49a3be;
	font-weight:normal;
}
.promoManager h2{
	margin:0;
	color:#49a3be;
	font-size:48px;
}
.promoManager .promoInfo *{
	color:#888;
	font-size:14px;
	font-weight:normal;
	margin:0;
}
.promoManager .promoInfo h2{
	font-size:24px;
	margin-top:10px;
}
.promoManager .feedback h3{
	font-size:24px;
}
.promoManager .feedback hr{
	margin:0;
	padding-top:0;
	border-bottom:solid 1px #49a3be;
}
.promoManager .feedback strong{
	color: #49a3be;
}
.promoOrder{
	position:fixed;
	left:0; right:0;
	top:0; bottom:0;
	background:#000;
	background:rgba(0, 0, 0, 0.7);
}
.promoOrder .dialog{
	position:absolute;
	left:50%; top:50%;
	margin-left:-250px;
	margin-top:-250px;
	width:500px;
	background:white;
	padding:40px 20px;
}
.promoOrder h2{
	margin:0 0 20px 0;
}
.promoOrder .close{
	display:block;
	position:absolute;
	right:0; top:-17px;
	background:white;
	padding:0 6px;
	text-decoration:none;
	color:#49a3be;
}
.promoCall h2{
	text-align:center;
	font-style:italic;
	font-size:24px;
	color:#49a3be;
}
.promoCall .feedback th b{
	display:block;
	color:#797979;
	font-weight:normal;
	text-align:center;
	font-size:16px;
}
.promoCall .feedback td{
	padding:4px 0;
	width:100%;
}
.promoCall .feedback .input{
	background:#cee4e9;
	border:solid 1px #aaa;
	border-radius:10px;
	font-size:24px;
	padding:5px 0;
	text-align:center;
	color:#555;
	width:100%;
}
.promoCall .button{
	background:#ff8400;
	padding:5px 10px;
	border: none;
	border-radius:10px;
	color:white;
	text-decoration:none;
	text-shadow:0 2px 4px #a05300;
	margin-top:10px;
	bottom: 5px; left:5px; right: 5px;
	-webkit-box-shadow: none;
	box-shadow:none;
	font-size:18px;
}
.promoCall p{
	text-align:center;
}
/*******************************/
.logo a{
	display:block;
	background:url(design/logos-800.png) no-repeat;
}
.logo a	{ width:145px; height:55px; }
.default.logo li{
	margin-right:55px;
	height:52px; width:135px;
	padding:5px;
}

.l0 	a{ background-position:0 0; width:70px; height:80px; }
.l10	a{ background-position:-450px 0; width:135px; height:38px;  }
.l11	a{ background-position:-750px 0; width:110px; height:42px; }
.l12	a{ background-position:-150px 0; width:125px; height:38px;}
.l13	a{ background-position:-300px 0; width:95px; height:50px;}	
.l14	a{ background-position:-600px 0; width:115px; height:52px;}

</style>
</head>
<body>
<? ob_start(); ?>
<div class="promoBody">
    <div class="promoHead">
    	<div class="promoHead2">
        	<div class="promoHeadContent">
            	<? beginAdmin() ?><h1 class="promoTitle"><? module("page:title"); ?></h1><? endAdmin($menu) ?>
                <div class="promoLogo">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td><a href="<? module("url"); ?>"><img src="/design/promoLogo.png" width="78" height="89"  alt="" /></a></td>
    <td width="100%" style="padding-left:10px"><? module("read:promo1"); ?></td>
</tr>
</table>
                </div>
                <div class="promoHeadInfo">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td align="center" valign="top">
<div class="promoSlotImage"><? module("doc:editable:$id:slot1image"); ?></div>
<div class="promoSlot">
    <div><? module("doc:editable:$id:slot1"); ?></div>
    <a href="<? $module_data = array(); $module_data["order"] = "slot1"; moduleEx("url:#", $module_data); ?>" class="promoButton">Заказать подбор тура</a>
</div>
    </td>
    <td align="center" valign="top">
<div class="promoSlotImage"><? module("doc:editable:$id:slot2image"); ?></div>
<div class="promoSlot">
    <? module("doc:editable:$id:slot2"); ?>
    <a href="<? $module_data = array(); $module_data["order"] = "slot2"; moduleEx("url:#", $module_data); ?>" class="promoButton">Заказать подбор тура</a>
</div>
    </td>
    <td align="center" valign="top">
<div class="promoSlotImage"><? module("doc:editable:$id:slot3image"); ?></div>
<div class="promoSlot">
    <? module("doc:editable:$id:slot3"); ?>
    <a href="<? $module_data = array(); $module_data["order"] = "slot3"; moduleEx("url:#", $module_data); ?>" class="promoButton">Заказать подбор тура</a>
</div>
    </td>
</tr>
</table>

                </div>
            </div>
        </div>
    </div>
	<div class="promoDocument">
<h1>Полезная информация</h1>
	  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="promoContent1">
	    <tr>
	      <td><div class="icon icon1"></div></td>
	      <td><div class="icon icon2"></div></td>
	      <td><div class="icon icon3"></div></td>
	      <td rowspan="3" valign="top" class="promoCall">
<? displayThumbImageMask($titleImagePath, 'design/maskPromoManager.png') ?>
<h2>Закажите звонок менеджера</h2>
<?
$form	= array();
$form[':']['mailTitle']	= "Заказ звонка промо-страницы: $data[title]";
$form[':']['url']		= getURL('#');
module("feedback:display:promoCall:vertical", $form);
?>
</td>
        </tr>
	    <tr>
	      <td nowrap="nowrap"><? module("doc:editable:$id:promoh1"); ?></td>
	      <td nowrap="nowrap"><? module("doc:editable:$id:promoh2"); ?></td>
	      <td nowrap="nowrap"><? module("doc:editable:$id:promoh3"); ?></td>
        </tr>
	    <tr>
	      <td width="25%"><? module("doc:editable:$id:promo1"); ?></td>
	      <td width="25%"><? module("doc:editable:$id:promo2"); ?></td>
	      <td width="25%"><? module("doc:editable:$id:promo3"); ?></td>
        </tr>
      </table>
<h1>Обратите внимание!</h1>
</div>
    <div class="quote">
        <div class="promoDocument">
<? module("doc:editable:$id:promoContent2Header"); ?>
          <table width="100%" border="0" cellspacing="0" cellpadding="0" class="promoContent2">
            <tr>
    <th><img src="/design/promoDoc.jpg" width="163" height="161"  alt=""/></th>
    <td width="100%">
<? module("doc:editable:$id:promoContent21"); ?>
    </td>
  </tr>
  <tr>
    <th><div class="icon icon6"></div></th>
    <td>
<div class="balloon"><? module("doc:editable:$id:promoContent22"); ?></div>
    </td>
  </tr>
  <tr>
    <th><img src="/design/promoDoc.jpg" width="163" height="161"  alt=""/></th>
    <td>
<? module("doc:editable:$id:promoContent23"); ?>
    </td>
  </tr>
  <tr>
    <th><div class="icon icon6"></div></th>
    <td>
<div class="balloon"><? module("doc:editable:$id:promoContent24"); ?></div>
    </td>
  </tr>
  <tr>
    <th><img src="/design/promoDoc.jpg" width="163" height="161"  alt=""/></th>
    <td>
<? module("doc:editable:$id:promoContent25"); ?>
    </td>
  </tr>
  <tr>
    <th><div class="icon icon6"></div></th>
    <td>
<div class="balloon"><? module("doc:editable:$id:promoContent26"); ?></div>
    </td>
  </tr>
</table>
<h1>Познакомтесь с нашим специалистом по стране</h1>
        </div>
    </div>
	<div class="promoDocument promoManager">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top">
<? displayThumbImageMask($titleImagePath, 'design/maskPromoManager.png') ?>
    </td>
    <td width="100%" valign="top" style="padding-left:40px">
<? if (hasAccessRole('admin,writer')){ ?>
<form action="<? module("url:#"); ?>" method="post">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr><td width="100%">
<select name="managerID" class="input w100">
	<option value="">--- нет ---</option>
<?
$dbUser->open('`operator_id` > 0');
while($userData = $dbUser->next()){
	$uID		= $dbUser->id();
	$name		= m('user:name:full', $userData);
	$operatorID	= $userData['operator_id'];
	
	$d		= $db->openID($operatorID);
	$class	= $userID == $uID?' selected="selected"':'';
?>
	<option value="<? if(isset($uID)) echo htmlspecialchars($uID) ?>"<? if(isset($class)) echo $class ?>><? if(isset($name)) echo htmlspecialchars($name) ?> - <? if(isset($d["title"])) echo htmlspecialchars($d["title"]) ?></option>
<? } ?>
</select>
</td>
<td><input type="submit" value="OK" class="button" /></td>
</tr>
</table>
</form>
<? } ?><? module("doc:editable:$id:promoManager"); ?>
<h2><? if(isset($managerName)) echo htmlspecialchars($managerName) ?></h2>
<div class="promoInfo">
<? module("doc:editable:$id:promoManagerCall"); ?><? if(isset($phone)) echo $phone ?>
</div>
    </td>
  </tr>
</table>
<div class="promoManager feedback">
<? module("doc:editable:$id:promoManagerFeedback"); ?>
</div>
    </div>
    <hr />
    <div class="promoDocument page">
    <center style="padding:20px 0">
<table border="0"><tr>
    <td><a href="<? module("url"); ?>" class="logoLink"></a></td>
    <td style="color:#c31313; font-size:24px; padding-top:45px; padding-left:30px">(343) 287-70-70</td>
</tr></table><br />
<? module("read:promoAddress"); ?>
    </center>
<ul class="default logo">
    <li class="l10"><a href="<? module("getURL:natalie-tours"); ?>"></a></li>
    <li class="l11"><a href="<? module("getURL:coraltravel"); ?>"></a></li>
    <li class="l12"><a href="<? module("getURL:teztour"); ?>"></a></li>
    <li class="l13"><a href="<? module("getURL:anextour"); ?>"></a></li>
    <li class="l14"><a href="<? module("getURL:pangeya-travel"); ?>"></a></li>
</ul><br />

    <? module("read:contacts"); ?><? module("doc:editable:$id:promo4"); ?>
    </div>
    <div class="promoCopyright"> <a href="<? module("url"); ?>"><img src="/design/promoLogo2.gif" width="200" height="66"  alt="" /></a>
    <? module("read:promo2"); ?>
    </div>
</div>
<?
$order	= getValue('order');
$p		= m("doc:editable:$id:$order");
if (!$p) $order = '';
$style	= $p?'':' style="display:none"';
?>
<div class="promoOrder promoCall" <? if(isset($style)) echo $style ?>>
	<div class="dialog">
    <a href="<? module("url:#"); ?>" class="close">закрыть</a>
    <div class="promoSlotOrder"><h2>Закажите звонок менеджера</h2></div>
<?
$form	= array();
$form[':']['mailTitle']		= "Заказ промо-страницы: $data[title], $order";
if ($p) $form[':']['url']	= getURL('#', "order=$order");
module("feedback:display:promoCall", $form);
?>
    </div>
</div>
<script>
$(function(){
	$(".promoOrder .close").click(function(){
		$(".promoOrder").hide();
		return false;
	});
	$(".promoButton").click(function(){
		$(".promoOrder").show();
		var promo = $(this).parent().find("> div").html();
		$(".promoSlotOrder").html(promo);
		$(".promoOrder form").attr("action", $(this).attr("href"));
		return false;
	});
});
</script>
</body>
</html><? $p = ob_get_clean(); module("admin:toolbar"); echo $p; ?><? $p = ob_get_clean(); module("page:header"); echo $p; ?>