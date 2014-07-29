<? function feedback_edit($val, $data)
{
	if (!access('write', 'feedback:')) return;
//	if (!hasAccessRole('admin,developer,writer')) return;

	$formName	= $data[1];
	if (!$formName) $formName = 'new';
	
	$localPath	= images."/feedback/form_$formName.txt";
	$form		= readIniFile($localPath);
	if (!$form) $form = readIniFile(cacheRootPath."/feedback/form_$formName.txt");
	if (!is_array($form)) $form = array();
	$oldForm	= $form;

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
				case 'note':
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
		if ($formName && $formName != 'new')
		{
			writeIniFile($localPath, $form);
			setCacheValue("form_$formName", $form);
			messageBox('Форма сохранена');
			m('feedback:snippets');
		}else{
			messageBox('Введите название файла для формы');
		}
	}
	m('page:title', "Форма: $formName");
	
	$form['Новое поле'] = array();

	m('script:jq_ui');
	m('script:ajaxForm');
	m('ajax:template', 'ajax_edit');
?>
<form action="{{url:feedback_edit_$formName}}" method="post" class="ajaxForm ajaxReload">

<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#feedbackMain">Настройка формы</a></li>
    <li class="ui-corner-top"><a href="#feedbackFields">Поля формы</a></li>
	<li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="feedbackMain">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td width="250" nowrap="nowrap">Заголовок формы</td>
    <td width="100%"><input name="form[:][title]" type="text" class="input w100" value="{$form[:][title]}" /></td>
  </tr>
<tbody class="edit">
  <tr>
    <td nowrap="nowrap">Название файла</td>
    <td><input name="form[:][name]" type="text" class="input w100" value="{$formName}" /></td>
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
    <td nowrap="nowrap">Название стиля формы (class)</td>
    <td><input name="form[:][class]" type="text" class="input w100" value="{$form[:][class]}" /></td>
  </tr>
  <tr class="noBorder">
    <td nowrap="nowrap">Название сниппета</td>
    <td><input name="form[:][snippetName]" type="text" class="input w100" value="{$form[:][snippetName]}" /></td>
  </tr>
  <tr class="noBorder">
    <td nowrap="nowrap">Вертикальный стиль формы</td>
    <td><input type="checkbox" name="form[:][verticalForm]"<?= $form[':']['verticalForm']?' checked="checked"':'' ?>></td>
  </tr>
  </tbody>
</table>
</div>

<div id="feedbackFields">
<div class="sortable">
<? foreach($form as $name => &$row){
	if ($name[0] == ':') continue;
	$thisName	= $name;
	$bNewField	= $name == 'Новое поле';
	if ($bNewField) $thisName = '';
?>
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="edit">
<tr>
  <td><div  class="ui-icon ui-icon-arrowthick-2-n-s"></div></td>
    <td>
<select name="form[{$name}][:type]" class="input" style="width:230px">
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
    <td width="100%"><input name="form[{$name}][name]" type="text" class="input w100" value="{$thisName}" /></td>
    <td width="10" nowrap="nowrap"><? if (!$bNewField){ ?><label style="white-space:nowrap"><input name="form[{$name}][:delete]" type="checkbox"  value="1" />Удалить</label><? } ?></td>
</tr>
<tbody class="edit">
<tr>
  <td nowrap="nowrap">&nbsp;</td>
    <td nowrap="nowrap">Значение по умолчанию</td>
    <td colspan="2"><input name="form[{$name}][default]" type="text" class="input w100" value="{$row[default]}" /></td>
    </tr>
<tr>
  <td nowrap="nowrap" valign="top">&nbsp;</td>
    <td nowrap="nowrap" valign="top">Значения
    </td>
    <td colspan="2" valign="top">
      <input name="form[{$name}][:typeValue]" type="text" class="input w100" value="{$thisValue}" />
      Значения списков разделяются запятой с пробелом
    </td>
    </tr>
<tr>
  <td valign="top" nowrap="nowrap">&nbsp;</td>
    <td valign="top" nowrap="nowrap">Обязательное</td>
    <td colspan="2" valign="top">
  <?
@$thisValue	= explode('|', $row['mustBe']);
removeEmpty($thisValue);
$class		= $thisValue?' checked="checked"':'';
?>
  <label>
    <input name="form[{$name}][mustBe][{$name}]" type="hidden" value="" />
    <input name="form[{$name}][mustBe][{$name}]" type="checkbox"  value="{$name}" {!$class} /> {$name}
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
    <input name="form[{$name}][mustBe][{$name2}]" type="checkbox"  value="{$name2}" {!$class} /> {$name2}
  </label>
  <? } ?>
    </td>
    </tr>
<tr class="noBorder">
<td valign="top" nowrap="nowrap">&nbsp;</td>
    <td valign="top" nowrap="nowrap">Комментарий</td>
    <td colspan="2"><textarea name="form[{$name}][note]" rows="3" class="input w100">{$row[note]}</textarea></td>
</tr>
</tbody>
</table>
<? } ?>
</div>

{{script:adminTabs}}
<script>
$(function(){
	$(".sortable" ).sortable({axis: 'y'}).disableSelection();
	$(".edit .edit").hide();
	$(".edit input, .edit select, .edit textarea").focus(function(){
		$("table.edit").find(".edit").hide();
		$(this).parents("table.edit").find(".edit").show();
	});
});
</script>
</div>

</div>

</form>
<? } ?>