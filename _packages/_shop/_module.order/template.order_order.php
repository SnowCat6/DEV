<?
function order_order($db, $val, $data)
{
	$formName	= 'order';
	$form		= module("feedback:get:$formName");
	if (!$form) return;
	
	noCache();
	
	$formData = getValue($formName);
	if (is_array($formData))
	{
		$formData[':bask']	= module('bask');
		$id = module('order:add', $formData);
		if ($id){
			$bask	= array();
			setBaskCookie($bask);
			redirect(getURL("order$id", 'key='.md5("order$id")));
		}
	}
	
	$class		= $form[':']['class'];
	if (!$class) $class="feedback";
	
	@$title2 	= $form[':']['formTitle'];
	$buttonName	= $form[':']['button'];

	module('script:ajaxLink');
	module('script:ajaxForm');
	
	m('page:title', $title2);
	
	if (!module('bask')){
		m('message', 'Нет товаров в корзине');
		return module('display:message');
	}
?>
<form action="{{url:bask}}" method="post" class="ajaxForm ajaxReload">
{{display:message}}
{{bask:full}}
<link rel="stylesheet" type="text/css" href="../../_modules/_nodule.feedback/feedback/feedback.css"/>
<div class="{$class}">
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
    <td><? feedbackText($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?>
<? case 'textarea':	//	textarea field?>
<tr>
    <th colspan="2">{!$name}{!$note}</th>
</tr>
<tr>
  <th colspan="2"><? feedbackTextArea($fieldName, $thisValue, $values)?></th>
</tr>
<? break; ?>
<? case 'phone':	//	text field?>
<tr>
    <th>{!$name}{!$note}</th>
    <td><? feedbackPhone($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?>
<? case 'radio':	//	radio field?>
<tr>
    <th valign="top">{!$name}{!$note}</th>
    <td><? feedbackRadio($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?>
<? case 'checkbox':	//	checkbox field?>
<tr>
    <th valign="top">{!$name}{!$note}</th>
    <td><? feedbackCheckbox($fieldName, $thisValue, $values)?></td>
</tr>
<? break; ?>
<? case 'select':	//	select field?>
<tr>
    <th valign="top">{!$name}{!$note}</th>
    <td><? feedbackSelect($fieldName, $thisValue, $values)?> </td>
</tr>
<? break; ?>
<? }//	switch ?>
<? }//	foreach ?>
</table>
<p><input type="submit" value="{$buttonName}" class="button" /></p>
{{read:orderAfter}}
<? } ?>
</div>
</form>
<? } ?>

