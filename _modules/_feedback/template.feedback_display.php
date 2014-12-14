<? function feedback_display($formName, &$data)
{
	m('script:maskInput');
	m('script:feedback');
	
	$bShowTitle		= $formName == '';
	@list($formName, $template) = explode(':', $formName);

	if (!$formName){
		$formName	= @$data[1];
		$data		= NULL;
	}
	if (!$formName) $formName = 'feedback';
	
	$form = module("feedback:get:$formName");
	if (!$form) return;
	if ($formName && is_array($data)){
		dataMerge($data, $form);
		$form = $data;
	}
	
	@$class	= $form[':']['class'];
	if (!$class) $class="feedback";
	$form[':']['class'] = $class;

	@$url	= $form[':']['url'];
	if (!$url) $url	= getURL("#");
	$form[':']['url'] = $url;

	@$buttonName	= $form[':']['button'];
	if (!$buttonName) $buttonName = 'Отправить';
	$form[':']['button'] = $buttonName;
	
	@$title	= $form[':']['title'];
	if ($title && $bShowTitle) module("page:title", $title);
	
	$menu = array();
	if (hasAccessRole('admin,developer,writer')){
		$menu['Изменить#ajax'] = getURL("feedback_edit_$formName");
		$menu[':class'] = 'adminGlobalMenu';
	}
	if (!$template && $form[':']['verticalForm']) $template = 'vertical';
	if (isPhone() && !$template) $template = 'vertical';
	
	$fn = getFn("feedback_display_$template");
	if ($fn){
		beginAdmin($menu);
		$fn($formName, $form);
		endAdmin();
		return;
	}
	
	beginAdmin($menu);
	$formData = getValue($formName);
	if (feedbackSend($formName, $formData, $form)){
		module('display:message');
		endAdmin();
		return;
	}
	
	@$title2 = $form[':']['formTitle'];
?>
<link rel="stylesheet" type="text/css" href="css/feedback.css">
<div class="{$class}">
<form action="{$url}" method="post" enctype="application/x-www-form-urlencoded" id="{$formName}" class="feedbackForm">
<? if ($title2){ ?><h2>{$title2}</h2><? } ?>
{{display:message}}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<? foreach($form as $name => $data){ ?>
<?
if ($name[0] == ':') continue;

$thisField	= $name;
$fieldName	= $formName."[$thisField]";

$name	= htmlspecialchars($name);
$bMustBe= $data['mustBe'] != false;
if ($bMustBe) $name = "<b>$name<span>*</span></b>";
$mustBeClass	= $bMustBe?' class="fieldMustBe"':'';

$note	= htmlspecialchars($data['note']);
if ($note) $note = "<div>$note</div>";

$type		= getFormFeedbackType($data);
@$values	= explode(',', $data[$type]);

if (is_array($formData)) @$thisValue = $formData[$thisField];
else @$thisValue = $data['default'];
?>
<? switch($type){ ?>
<? case 'hidden': ?>
<? break; ?>
<? case 'textarea':	//	textarea field?>
<tr>
    <th colspan="2">{!$name}{!$note}</th>
</tr>
<tr>
  <th colspan="2"{!$mustBeClass}>{{feedback:elm:$type=name:$fieldName;value:$thisValue;values:$values}}</th>
</tr>
<? break; ?>
<? default:	//	text field?>
<tr>
    <th valign="top">{!$name}{!$note}</th>
    <td valign="top" {!$mustBeClass}>{{feedback:elm:$type=name:$fieldName;value:$thisValue;values:$values}}</td>
</tr>
<? break; ?>
<? }//	switch ?>
<? }//	foreach ?>
</table>
<p><input type="submit" value="{$buttonName}" class="button" /></p>
</form>
</div>
<?  endAdmin(); } ?>


<? function script_feedback(&$val){
	m('script:jq_ui');
?>
<script>
$(function(){
	$(".feedbackForm").submit(function(){
		var bOK = true;
		$(this).find(".fieldMustBe input, .fieldMustBe select, .fieldMustBe textarea")
		.each(function(){
			if ($(this).val()) return;
			if (bOK) $(this).focus().addClass('doImputField');
			bOK = false;
		})
		return bOK;
	})
	.find(".fieldMustBe input, .fieldMustBe select, .fieldMustBe textarea")
		.attr("title", "Обязательное для заполениия поле").tooltip()
		.keydown(function(){
			$(this).removeClass('doImputField').tooltip("close");
		});
});
</script>
<? } ?>