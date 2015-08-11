<?
function doc_property_dev($data)
{
	if (!hasAccessRole('developer')) return;

	$db		= module('doc', $data);
	$id		= $db->id();
	$type	= $data['doc_type'];
	$fields	= $data['fields'];

	$namesPage	= array();
	$pages		= getCacheValue('pages') or array();
	foreach($pages as $name => $val){
		if (!preg_match('#^page\.(.*)#', $name, $v)) continue;
		$namesPage[$v[1]]	= $v[1];
	}

	$types		= array();
	$templates	= getCacheValue(':docTypes') or array();
	foreach($templates as $name => $val){
		list($type, $template) = explode(':', $name);
		$types[$type] = docType($type);
	}


	$pagesFn	= array();
	$fnTemplates= array();
	$templates	= getCacheValue('templates') or array();
	foreach($templates as $name => &$val){
		if (!preg_match('#^(doc_page_.*)#', $name, $v)) continue;
		$fnTemplates[$v[1]] = $v[1];
		$pagesFn[$v[0]]		= $v[0];
	}
?>

<h2 class="ui-state-default">Настройки документа</h2>
<table border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td nowrap="nowrap"><label for="docAccessPage">Разрешить подкаталоги</label></td>
    <td align="right">
<input type="hidden" name="doc[fields][access][page]" value="0" />
<input type="checkbox" id="docAccessPage" name="doc[fields][access][page]" value="1"<?= $fields['access']['page']?' checked="checked"':''?> />
    </td>
    <td nowrap="nowrap">Тип документа</td>
    <td>
  <select name="doc[doc_type]" class="input w100">
  <? foreach($types as $type => $name){?>
  <option value="{$type}"{selected:$type==$data[doc_type]}>{$name}</option>
  <? } ?>
  </select>
    </td>
  </tr>
  <tr>
    <td nowrap="nowrap"><label for="docAccessArticle">Разрешить документы</label></td>
    <td align="right">
<input type="hidden" name="doc[fields][access][article]" value="0" />
<input type="checkbox" id="docAccessArticle" name="doc[fields][access][article]" value="1"<?= $fields['access']['article']?' checked="checked"':''?> />
      </td>
    <td nowrap="nowrap">Шаблон</td>
    <td><select name="doc[template]" class="input w100">
      <option value="">-- стандартный --</option>
  <?
$names		= array();
$templates	= module("findTemplates:^(doc_read|doc_page)_", '(cache|beginCache)$');
foreach($templates as $name => $val)
{
	if (!preg_match('#^(doc_read|doc_page)_([^_]+)_(.*)#', $name, $v)) continue;
	$n	= $v[3];
	$names[$n] = $n;
}

$templates	= getCacheValue('docTypes') or array();
foreach($templates as $name => &$val){
	list($name, $template) = explode(':', $name);
	if ($template) $names[$template] = docTypeEx($name, $template);
}

$template = $data['template'];
foreach($names as $name => $titleName){
	$class = $template == $name?' selected="selected" class="current"':'';
?><option value="{$name}"{!$class}>{$titleName}</option>
  <? } ?>
    </select></td>
  </tr>
  <tr>
    <td nowrap="nowrap"><label for="docAccessComment">Разрешить комментарии</label></td>
    <td align="right">
<input type="hidden" name="doc[fields][access][comment]" value="0" />
<input type="checkbox" id="docAccessComment" name="doc[fields][access][comment]" value="1"<?= $fields['access']['comment']?' checked="checked"':''?> />
      </td>
    <td nowrap="nowrap">Вид контента</td>
    <td><select name="doc[fields][pageFn]" class="input w100">
      <option value="">-- стандартная --</option>
<?
$template = $data['fields']['pageFn'];
foreach($pagesFn as $name => $val){
?>
      <option value="{$name}" {selected:$template==$name}>{$val}</option>
      <? } ?>
    </select></td>
  </tr>
  <tr>
    <td nowrap="nowrap">&nbsp;</td>
    <td align="right">&nbsp;</td>
    <td nowrap="nowrap">Вид страницы</td>
    <td><select name="doc[fields][page]" class="input w100">
      <option value="">-- стандартная --</option>
      <?
$template = $data['fields']['page'];
foreach($namesPage as $name => $val){
?>
      <option value="{$name}" {selected:$template==$name}>{$val}</option>
      <? } ?>
    </select></td>
  </tr>
</table>



<? return '99-Разработчик'; } ?>