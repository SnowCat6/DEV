<? function doc_property_dev($data){?>
<?
	if (!hasAccessRole('admin,developer')) return;

	$db		= module('doc', $data);
	$id		= $db->id();
	$type	= $data['doc_type'];
	@$fields= $data['fields'];
?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td nowrap="nowrap"><label for="docAccessPage">Разрешить подкаталоги</label></td>
    <td align="right">
<input type="hidden" name="doc[fields][access][page]" value="0" />
<input type="checkbox" id="docAccessPage" name="doc[fields][access][page]" value="1"<?= $fields['access']['page']?' checked="checked"':''?> />
    </td>
    <td nowrap="nowrap">Шаблон</td>
    <td width="100%"><?
$names		= array();
$templates	= getCacheValue('templates');
if (!is_array($templates)) $templates = array();
foreach($templates as $name => &$val){
	if (!preg_match('#^(doc_read|doc_page)_([^_]+)_(.*)#', $name, $v)) continue;
	$names[$v[3]] = $v[3];
}

$templates	= getCacheValue('docTypes');
if (!is_array($templates)) $templates = array();
foreach($templates as $name => &$val){
	list($name, $template) = explode(':', $name);
	if ($template) $names[$template] = docTypeEx($name, $template);
}
?>
<select name="doc[template]" class="input w100">
<option value="">-- стандартный --</option>
<?
@$template = $data['template'];
foreach($names as $name => $titleName){
	$class = $template == $name?' selected="selected" class="current"':'';
?><option value="{$name}"{!$class}>{$titleName}</option>
<? } ?>
</select></td>
  </tr>
  <tr>
    <td nowrap="nowrap"><label for="docAccessArticle">Разрешить документы</label></td>
    <td align="right">
<input type="hidden" name="doc[fields][access][article]" value="0" />
<input type="checkbox" id="docAccessArticle" name="doc[fields][access][article]" value="1"<?= $fields['access']['article']?' checked="checked"':''?> />
      </td>
    <td nowrap="nowrap">Шаблон страницы</td>
    <td><select name="doc[fields][page]" class="input w100">
      <option value="">-- стандартная --</option>
      <?
$namesPage	= array();
$pages		= getCacheValue('pages');
foreach($pages as $name => &$val){
	if (!preg_match('#^page\.(.*)#', $name, $v)) continue;
	$namesPage[$v[1]] = $v[1];
}
@$template = $data['fields']['page'];
foreach($namesPage as $name => &$val){
	$class = $template == $name?' selected="selected" class="current"':'';
?><option value="{$name}"{!$class}>{$val}</option>
<? } ?>
</select></td>
  </tr>
  <tr>
    <td nowrap="nowrap"><label for="docAccessComment">Разрешить комментарии</label></td>
    <td align="right">
<input type="hidden" name="doc[fields][access][comment]" value="0" />
<input type="checkbox" id="docAccessComment" name="doc[fields][access][comment]" value="1"<?= $fields['access']['comment']?' checked="checked"':''?> />
      </td>
    <td nowrap="nowrap">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
<? return '99-Разработчик'; } ?>