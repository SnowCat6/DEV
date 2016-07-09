<?
function order_order($db, $val, $data)
{
	$formName	= 'order';
	$form		= module("feedback:get:$formName");
	if (!$form) return;
	
	module('nocache');
	setNoCache();
	
	$formData = getValue($formName);
	if (is_array($formData))
	{
		$formData[':bask']	= module('bask');
		$id = module('order:add', $formData);
		if ($id){
			$bask	= array();
			setBaskCookie($bask);
			module("redirect", getURL("order$id", 'key='.md5("order$id")));
		}
	}
	
	$class		= $form[':']['class'];
	if (!$class) $class="feedback";
	
	@$title2 	= $form[':']['formTitle'];
	$buttonName	= $form[':']['button'];

	module('script:ajaxLink');
	module('script:ajaxForm');
	
	m('page:title', $title2);
?>
<? 	if (!module('bask')){ ?>
<module:read:baskEmpty default="@">
<?
		m('message', 'Нет товаров в корзине');
		module('display:message');
?>
</module:read:baskEmpty>
<module:holder:baskEmpty />
<? return; } ?>

<link rel="stylesheet" type="text/css" href="../../_modules/_nodule.feedback/css/feedback.css"/>
<form action="{{url:bask}}" method="post" class="ajaxReload">

{{display:message}}
{{bask:full}}

<div class="pageContent">
<div class="{$class}">
<h2>{$title2}</h2>

<? if (module('bask')){ ?>
{{read:orderBefore}}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<? foreach($form as $name => $data){ ?>
<?
if ($name[0] == ':') continue;

$thisField	= $name;
$fieldName	= $formName."[$thisField]";

$name	= htmlspecialchars($name);
$bMustBe= $data['mustBe'] != false;
if ($bMustBe) $name = "<b>$name<span>*</span></b>";

$note	= htmlspecialchars($data['note']);
if ($note) $note = "<div>$note</div>";

$type		= getFormFeedbackType($data);
@$default	= $data['default'];
@$values	= explode(',', $data[$type]);

if (is_array($formData)) @$thisValue = $formData[$thisField];
else $thisValue = $default;
?>
<? switch($type){ ?>
<? default:	//	text field?>
<tr>
    <th>{!$name}{!$note}</th>
    <td>{{feedback:elm:$type=name:$fieldName;value:$thisValue;values:$values}}</td>
</tr>
<? break; ?>
<? case 'textarea':	//	textarea field?>
<tr>
    <th colspan="2">{!$name}{!$note}</th>
</tr>
<tr>
  <th colspan="2">{{feedback:elm:$type=name:$fieldName;value:$thisValue;values:$values}}</th>
</tr>
<? break; ?>
<? }//	switch ?>
<? }//	foreach ?>
</table>
<p><input type="submit" value="{$buttonName}" class="button" /></p>
{{read:orderAfter}}
<? } ?>
</div>
</div>
</form>
<? } ?>

