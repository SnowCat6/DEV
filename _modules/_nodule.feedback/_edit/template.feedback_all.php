<? function feedback_all($val, $data)
{
	m('page:title', 'Формы обратной связи');
	m('script:ajaxLink');

	$forms = getFiles(images."/feedback", 'form_.*.txt');
	$forms2= getFiles(localCacheFolder."/siteFiles/feedback", 'form_.*.txt');
	dataMerge($forms, $forms2);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th nowrap>Название</th>
    <th width="100%">Заголовок</th>
  </tr>
<? foreach($forms as $name => $path){
	$form = readIniFile($path);

	@$title	= $form[':']['title'];
	if (!$title) @$title = $form[':']['formTitle'];
	
	$url	= split('_', $name, 2);
	$url	= basename($url[1], '.txt');
?>
  <tr>
    <td nowrap><a href="{{getURL:feedback_edit_$url}}" title="{$path}" id="ajax">{$name}</a></td>
    <td>{$title}</td>
  </tr>
<? } ?>
</table>

<? } ?>
