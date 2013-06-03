<? function feedback_edit($val, $data)
{
	$formName = $data[1];
	m('page:title', "Форма: $formName");
	
	$form = readIniFile(images."/feedback/form_$formName.txt");
	if (!$form) $form = readIniFile(localCacheFolder."/siteFiles/feedback/form_$formName.txt");
	if (!is_array($form)) $form = array();
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
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
  <tr>
    <td nowrap="nowrap">Название стиля формы</td>
    <td><input name="form[:][class]" type="text" class="input w100" value="{$form[:][class]}" /></td>
  </tr>
</table>

<? } ?>