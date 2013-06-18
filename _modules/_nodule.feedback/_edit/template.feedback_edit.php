<? function feedback_edit($val, $data)
{
	if (!hasAccessRole('admin,developer,writer')) return;

	$formName	= $data[1];
	if (!$formName) $formName = 'new';
	
	$localPath	= images."/feedback/form_$formName.txt";
	$form		= readIniFile($localPath);
	if (!$form) $form = readIniFile(localCacheFolder."/siteFiles/feedback/form_$formName.txt");
	if (!is_array($form)) $form = array();

	$thisForm = getValue('form');
	if (is_array($thisForm))
	{
		$formName	= trim($thisForm[':']['name']);
		$formName	= preg_replace('#[^a-zA-Z\d]#', '', $formName);
		$localPath	= images."/feedback/form_$formName.txt";
		
		$form		= array();
		@$form[':']	= $thisForm[':'];
		foreach($thisForm as $name => &$row){
			if ($name[0] == ':') continue;
			if ($row[':delete']) continue;
			
			$thisName	= trim($row['name']);
			if (!$thisName) continue;

			foreach($row as $name2 => $val)
			{
				switch($name2){
				case 'default':
					if ($val) $form[$thisName][$name2] = $val;
					break;
				case 'mustBe':
					removeEmpty($val);
					if ($val){
						$nField = array_search($name, $val);
						if ($nField) $val[$nField] = $thisName;
						$form[$thisName][$name2] = implode('|', $val);
					}
					break;
				case ':type':
					if ($val) @$form[$thisName][$val] = $row[':typeValue'];
					break;
				}
			}
		}
		if ($formName && $formName != 'new'){
			writeIniFile($localPath, $form);
			setCacheValue("form_$formName", $form);
			messageBox('Форма сохранена');
		}else{
			messageBox('Введите название файла для формы');
		}
	}
	m('script:ajaxForm');
	m('page:title', "Форма: $formName");
	
	$form['Новое поле'] = array();

	module('script:jq_ui');
?>
<form action="{{url:feedback_edit_$formName}}" method="post" class="ajaxForm ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <td nowrap="nowrap">Название файла</td>
    <td><input name="form[:][name]" type="text" class="input w100" value="{$formName}" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Заголовок формы</td>
    <td width="100%"><input name="form[:][title]" type="text" class="input w100" value="{$form[:][title]}" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Заголовок формы в эл. почте</td>
    <td><input name="form[:][formTitle]" type="text" class="input w100" value="{$form[:][formTitle]}" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Надпись на кнопке</td>
    <td><input name="form[:][button]" type="text" class="input w100" value="{$form[:][button]}" /></td>
  </tr>
  <tr class="noBorder">
    <td nowrap="nowrap">Название стиля формы</td>
    <td><input name="form[:][class]" type="text" class="input w100" value="{$form[:][class]}" /></td>
  </tr>
</table>
<p><input type="submit" class="button" value="Сохранить" /></p>

<div class="sortable">
<? foreach($form as $name => &$row){
	if ($name[0] == ':') continue;
	$thisName	= $name;
	$bNewField	= $name == 'Новое поле';
	if ($bNewField) $thisName = '';
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
  <th colspan="2">
<? if (!$bNewField){ ?>
<label><input name="form[{$name}][:delete]" type="checkbox"  value="1" /> Удалить  "{$name}"</label>
<? } ?>
  </th>
</tr>
<tr>
    <td nowrap="nowrap">Название поля</td>
    <td width="100%"><input name="form[{$name}][name]" type="text" class="input w100" value="{$thisName}" /></td>
</tr>
<tr>
    <td nowrap="nowrap">Значение по умолчанию</td>
    <td width="100%"><input name="form[{$name}][default]" type="text" class="input w100" value="{$row[default]}" /></td>
</tr>
<tr>
    <td valign="top" nowrap="nowrap">Обязательное</td>
    <td width="100%" valign="top">
<?
@$thisValue	= explode('|', $row['mustBe']);
removeEmpty($thisValue);
$class		= $thisValue?' checked="checked"':'';
?>
<label>
    <input name="form[{$name}][mustBe][{$name}]" type="hidden" value="" />
    <input name="form[{$name}][mustBe][{$name}]" type="checkbox"  value="{$name}" {!$class} />
    {$name}
</label>
<?
foreach($form as $name2 => &$row2){
	if ($name2[0] == ':')	continue;
	if ($name2 == $name)	continue;
	$class = is_int(array_search($name2, $thisValue))?' checked="checked"':'';
?>
<b>или</b>
<label>
    <input name="form[{$name}][mustBe][{$name2}]" type="hidden" value="" />
    <input name="form[{$name}][mustBe][{$name2}]" type="checkbox"  value="{$name2}" {!$class} />
    {$name2}
</label>
<? } ?>
    </td>
</tr>
<tr class="noBorder">
    <td nowrap="nowrap" valign="top">
<select name="form[{$name}][:type]" class="input">
<?
$thisType	= getFormFeedbackType($row);
@$thisValue	= $row[$thisType];
foreach(getFormFeedbackTypes() as $name2 => $type){
	$class = $thisType == $type?' selected="selected"':'';
?>
<option value="{$type}"{!$class}>{$name2}</option>
<? } ?>
</select>
    </td>
    <td width="100%" valign="top">
    <input name="form[{$name}][:typeValue]" type="text" class="input w100" value="{$thisValue}" />
    Значения списков разделяются запятой с пробелом
    </td>
</tr>
</table>
<? } ?>
</div>
<p><input type="submit" class="button" value="Сохранить" /></p>
</form>
<script language="javascript" type="text/javascript">
$(function(){
	$( ".sortable" ).sortable({axis: 'y'}).disableSelection();
});
</script>
<? } ?>