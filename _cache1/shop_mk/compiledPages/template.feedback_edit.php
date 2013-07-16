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
		if ($formName && $formName != 'new'){
			writeIniFile($localPath, $form);
			setCacheValue("form_$formName", $form);
			messageBox('Форма сохранена');
		}else{
			messageBox('Введите название файла для формы');
		}
	}
	m('page:title', "Форма: $formName");
	
	$form['Новое поле'] = array();

	m('script:jq_ui');
	m('script:ajaxForm');
?>
<form action="<? module("url:feedback_edit_$formName"); ?>" method="post" class="ajaxForm ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="edit">
  <tr>
    <td width="250" nowrap="nowrap">Заголовок формы</td>
    <td width="100%"><input name="form[:][title]" type="text" class="input w100" value="<? if(isset($form[":"]["title"])) echo htmlspecialchars($form[":"]["title"]) ?>" /></td>
  </tr>
<tbody class="edit">
  <tr>
    <td nowrap="nowrap">Название файла</td>
    <td><input name="form[:][name]" type="text" class="input w100" value="<? if(isset($formName)) echo htmlspecialchars($formName) ?>" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Заголовок формы в эл. почте</td>
    <td><input name="form[:][formTitle]" type="text" class="input w100" value="<? if(isset($form[":"]["formTitle"])) echo htmlspecialchars($form[":"]["formTitle"]) ?>" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Надпись на кнопке</td>
    <td><input name="form[:][button]" type="text" class="input w100" value="<? if(isset($form[":"]["button"])) echo htmlspecialchars($form[":"]["button"]) ?>" /></td>
  </tr>
  <tr class="noBorder">
    <td nowrap="nowrap">Название стиля формы</td>
    <td><input name="form[:][class]" type="text" class="input w100" value="<? if(isset($form[":"]["class"])) echo htmlspecialchars($form[":"]["class"]) ?>" /></td>
  </tr>
  </tbody>
</table>

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
<select name="form[<? if(isset($name)) echo htmlspecialchars($name) ?>][:type]" class="input" style="width:230px">
<?
$thisType	= getFormFeedbackType($row);
@$thisValue	= $row[$thisType];
foreach(getFormFeedbackTypes() as $name2 => $type){
	$class = $thisType == $type?' selected="selected"':'';
?>
<option value="<? if(isset($type)) echo htmlspecialchars($type) ?>"<? if(isset($class)) echo $class ?>><? if(isset($name2)) echo htmlspecialchars($name2) ?></option>
<? } ?>
</select>
    </td>
    <td width="100%"><input name="form[<? if(isset($name)) echo htmlspecialchars($name) ?>][name]" type="text" class="input w100" value="<? if(isset($thisName)) echo htmlspecialchars($thisName) ?>" /></td>
    <td width="10" nowrap="nowrap"><? if (!$bNewField){ ?><label><input name="form[<? if(isset($name)) echo htmlspecialchars($name) ?>][:delete]" type="checkbox"  value="1" /> Удалить</label><? } ?></td>
</tr>
<tbody class="edit">
<tr>
  <td nowrap="nowrap">&nbsp;</td>
    <td nowrap="nowrap">Значение по умолчанию</td>
    <td colspan="2"><input name="form[<? if(isset($name)) echo htmlspecialchars($name) ?>][default]" type="text" class="input w100" value="<? if(isset($row["default"])) echo htmlspecialchars($row["default"]) ?>" /></td>
    </tr>
<tr>
  <td nowrap="nowrap" valign="top">&nbsp;</td>
    <td nowrap="nowrap" valign="top">Значения
    </td>
    <td colspan="2" valign="top">
      <input name="form[<? if(isset($name)) echo htmlspecialchars($name) ?>][:typeValue]" type="text" class="input w100" value="<? if(isset($thisValue)) echo htmlspecialchars($thisValue) ?>" />
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
    <input name="form[<? if(isset($name)) echo htmlspecialchars($name) ?>][mustBe][<? if(isset($name)) echo htmlspecialchars($name) ?>]" type="hidden" value="" />
    <input name="form[<? if(isset($name)) echo htmlspecialchars($name) ?>][mustBe][<? if(isset($name)) echo htmlspecialchars($name) ?>]" type="checkbox"  value="<? if(isset($name)) echo htmlspecialchars($name) ?>" <? if(isset($class)) echo $class ?> /> <? if(isset($name)) echo htmlspecialchars($name) ?>
  </label>
  <?
foreach($form as $name2 => &$row2){
	if ($name2[0] == ':')	continue;
	if ($name2 == $name)	continue;
	$class = is_int(array_search($name2, $thisValue))?' checked="checked"':'';
?>
  <b>или</b>
  <label>
    <input name="form[<? if(isset($name)) echo htmlspecialchars($name) ?>][mustBe][<? if(isset($name2)) echo htmlspecialchars($name2) ?>]" type="hidden" value="" />
    <input name="form[<? if(isset($name)) echo htmlspecialchars($name) ?>][mustBe][<? if(isset($name2)) echo htmlspecialchars($name2) ?>]" type="checkbox"  value="<? if(isset($name2)) echo htmlspecialchars($name2) ?>" <? if(isset($class)) echo $class ?> /> <? if(isset($name2)) echo htmlspecialchars($name2) ?>
  </label>
  <? } ?>
    </td>
    </tr>
<tr class="noBorder">
<td valign="top" nowrap="nowrap">&nbsp;</td>
    <td valign="top" nowrap="nowrap">Комментарий</td>
    <td colspan="2"><textarea name="form[<? if(isset($name)) echo htmlspecialchars($name) ?>][note]" rows="3" class="input w100"><? if(isset($row["note"])) echo htmlspecialchars($row["note"]) ?></textarea></td>
</tr>
</tbody>
</table>
<? } ?>
</div>
<p><input type="submit" class="button" value="Сохранить" /></p>
</form>
<script language="javascript" type="text/javascript">
$(function(){
	$(".sortable" ).sortable({axis: 'y'}).disableSelection();
	$(".edit .edit").hide();
	$(".edit input, .edit select, .edit textarea").focus(function(){
		$(this).parents("table.edit").find(".edit").show();
	}).blur(function(){
		$(this).parents("table.edit").find(".edit").hide();
	});
});
</script>
<? } ?>