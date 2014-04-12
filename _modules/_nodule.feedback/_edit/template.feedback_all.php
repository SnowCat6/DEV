<? function feedback_all($val, $data)
{
	if (!access('read', 'feedback:')) return;
//	if (!hasAccessRole('admin,developer,writer')) return;
	
	m('page:title', 'Формы обратной связи');
	m('script:ajaxLink');
	
	$delete = getValue('feedbackDelete');
	if (is_array($delete)){
		foreach($delete as $formName){
			$formName = basename($formName);
			@unlink(images."/feedback/form_$formName.txt");
			$v = '';
			setCacheValue("form_$formName", $v);
		}
	}

	$forms = getFiles(images."/feedback", 'form_.*.txt');
	$forms2= getFiles(cacheRootPath."/feedback", 'form_.*.txt');
	dataMerge($forms, $forms2);
?>
<form action="{{url:feedback_all}}" method="post">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th nowrap>&nbsp;</th>
    <th nowrap>Название</th>
    <th width="100%">Заголовок</th>
  </tr>
<? foreach($forms as $name => $path){
	$form = readIniFile($path);

	@$title	= $form[':']['title'];
	if (!$title) @$title = $form[':']['formTitle'];
	
	$url	= explode('_', $name, 2);
	$url	= basename($url[1], '.txt');
?>
  <tr>
    <td>
<? if (!strpos($path, 'siteFiles')){ ?><input type="checkbox" name="feedbackDelete[]" value="{$url}" /><? } ?></td>
    <td nowrap><a href="{{getURL:feedback_edit_$url}}" title="{$path}" id="ajax">{$name}</a></td>
    <td width="100%">{$title}</td>
  </tr>
<? } ?>
</table>
<p><input type="submit" value="Удалить выбранные" class="button" /><a href="{{url:feedback_edit}}" id="ajax">Добавить новую</a></p>
</form>
<? } ?>
