<? function feedback_display($name, $data)
{
	$form = readIniFile(localHostPath."/feedback/form_$name.txt");
	if (!$form) $form = readIniFile(localCacheFolder."/siteFiles/feedback/form_$name.txt");
	if (!$form) return;

	$formName	= 'feedback';
	$formData	= getValue($formName);
	
	@$title	= $form[':']['title'];

	@$class	= $form[':']['class'];
	if (!$class) $class="feedback";

	@$url	= $form[':']['url'];
	if (!$url) $url="#";

	@$buttonName	= $form[':']['button'];
	if (!$buttonName) $buttonName = 'Отправить';
	
?>
<link rel="stylesheet" type="text/css" href="feedback/feedback.css">
<div class="{$class}">
<form action="{!$url}" method="post" enctype="multipart/form-data">
<? if ($title){ ?><h2>{$title}</h2><? } ?>
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

$type	= '';
if (isset($data['select']))		$type = 'select';
if (isset($data['checkbox']))	$type = 'checkbox';
if (isset($data['radio']))		$type = 'radio';
if (isset($data['textarea']))	$type = 'textarea';

@$default	= $data['default'];
@$values	= explode(',', $data[$type]);
if (is_array($formData)) @$thisValue = $formData[$thisField];
else $thisValue = $default;
?>
<? switch($type){ ?>
<? default:	//	text field?>
<tr>
    <th>{!$name}{!$note}</th>
    <td><input name="{$fieldName}" type="text" class="input w100" value="{$thisValue}" /></td>
</tr>
<? break; ?>
<? case 'textarea':	//	textarea field?>
<tr>
    <th colspan="2">{!$name}{!$note}</th>
</tr>
<tr>
  <th colspan="2"><textarea name="{$fieldName}" rows="5" class="input w100">{$thisValue}</textarea></th>
</tr>
<? break; ?>
<? case 'radio':	//	radio field?>
<tr>
    <th valign="top">{!$name}{!$note}</th>
    <td>
<? foreach($values as $name => $value){
	$class = $thisValue == $value?' checked="checked"':'';
?>
    <div><label><input name="{$fieldName}" type="radio" value="{$value}"{!$class} /> {$value}</label></div>
<? } ?>
    </td>
</tr>
<? break; ?>
<? case 'checkbox':	//	checkbox field?>
<tr>
    <th valign="top">{!$name}{!$note}</th>
    <td>
<?
if (!is_array($thisValue)) $thisValue = explode(',', $thisValue);
$thisValue = array_values($thisValue);

foreach($values as $name => $value){
	$class = is_int(array_search($value, $thisValue))?' checked="checked"':'';
?>
    <div><label><input name="{$fieldName}[{$value}]" type="checkbox" value="{$value}"{!$class} /> {$value}</label></div>
<? } ?>
    </td>
</tr>
<? break; ?>
<? case 'select':	//	select field?>
<tr>
    <th valign="top">{!$name}{!$note}</th>
    <td>
<select name="{$fieldName}" class="input w100">
<? foreach($values as $name => $value){
	$class = $thisValue == $value?' selected="selected"':'';
?>
	<option value="{$value}"{!$class}>{$value}</option>
<? } ?>
</select>
    </td>
</tr>
<? break; ?>
<? }//	switch ?>
<? }//	foreach ?>
</table>
<p><input type="submit" value="{$buttonName}" class="button" /></p>
</form>
</div>
<? } ?>