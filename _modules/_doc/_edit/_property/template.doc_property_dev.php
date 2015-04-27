<?
function doc_property_dev_update($data)
{
	if (!hasAccessRole('developer')) return;

	$rules		= array();
	$r			= getValue('docRules') or array();
	$docTypes	= getCacheValue('docTypes') or array();
	foreach($r as $v)
	{
		if (!$v['docType']) continue;
		
		if (!$v['name1']) $v['name1']	= "$v[docType]:$v[docTemplate]";
		if (!$v['name2']) $v['name2']	= $v['name1'];
		$rules["$v[docType]:$v[docTemplate]"]	= "$v[name1]:$v[name2]:$v[renderFn]:$v[renderPage]";
		$docTypes["$v[docType]:$v[docTemplate]"]= "$v[name1]:$v[name2]:$v[renderFn]:$v[renderPage]";
	}
	
	setIniValue(':docRules', $rules);
	setCacheValue('docTypes', $docTypes);
}
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
		$namesPage[$v[1]] = $v[1];
	}

	$types		= array();
	$templates	= getCacheValue('docTypes') or array();
	foreach($templates as $name => $val){
		list($type, $template) = explode(':', $name);
		$types[$type] = docType($type);
	}


	$fnTemplates= array();
	$templates	= getCacheValue('templates') or array();
	foreach($templates as $name => &$val){
		if (!preg_match('#^(doc_page_.*)#', $name, $v)) continue;
		$fnTemplates[$v[1]] = $v[1];
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
$templates	= getCacheValue('templates') or array();
foreach($templates as $name => &$val){
	if (!preg_match('#^(doc_read|doc_page)_([^_]+)_(.*)#', $name, $v)) continue;
	$names[$v[3]] = $v[3];
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
    <td nowrap="nowrap">Вид страницы</td>
    <td>
  <select name="doc[fields][page]" class="input w100">
    <option value="">-- стандартная --</option>
  <?
$template = $data['fields']['page'];
foreach($namesPage as $name => $val){
	$class = $template == $name?' selected="selected" class="current"':'';
?>
    <option value="{$name}"{!$class}>{$val}</option>
  <? } ?>
  </select>
    </td>
  </tr>
</table>


<h2 class="ui-state-default">Настройки всех документов</h2>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
    <tr>
      <th>Тип документа</th>
      <th>Шаблон</th>
      <th>Вид контента</th>
      <th>Вид страницы</th>
      <th>Ед. число</th>
      <th>Мн. число</th>
    </tr>
<?
$docTypes		= getCacheValue('docTypes') or array();
$rules			= getIniValue(':docRules') or array();
$rules['new']	= '';
foreach($rules as $rule => $values)
{
	$docType = $docTemplate = '';
	$renderFn = $renderPage = $name1 = $name2 = '';
	
	list($docType, $docTemplate) = explode(':', $rule);
	list($name1, $name2, $renderFn, $renderPage) = explode(':', $values);
	
	$docTypes["$docType:$docTemplate"] = '';
	unset($docTypes["$docType:$docTemplate"]);
?>
    <tr>
      <td>
 <select name="docRules[{$rule}][docType]" class="input w100">
    <option value="">-- нет --</option>
<? foreach($types as $type => $name){ ?>
    <option value="{$type}"{selected:$type==$docType}>{$name}</option>
<? } ?>
</select>
      </td>
      <td><input name="docRules[{$rule}][docTemplate]" type="text" class="input w100" value="{$docTemplate}" size="8" /></td>
      <td>
 <select name="docRules[{$rule}][renderFn]" class="input w100">
    <option value="">-- стандартная --</option>
<? foreach($fnTemplates as $val => $name){ ?>
    <option value="{$val}"{selected:$val==$renderFn}>{$name}</option>
<? } ?>
</select>
     </td>
      <td>
<select name="docRules[{$rule}][renderPage]" class="input w100">
    <option value="">-- стандартная --</option>
<? foreach($namesPage as $val => $name){ ?>
    <option value="{$val}"{selected:$val==$renderPage}>{$name}</option>
<? } ?>
</select>
      </td>
      <td><input type="text" name="docRules[{$rule}][name1]" value="{$name1}" class="input w100" /></td>
      <td><input type="text" name="docRules[{$rule}][name2]" value="{$name2}" class="input w100" /></td>
    </tr>
<? } ?>
<? foreach($docTypes as $rule => $values)
{
	$docType = $docTemplate = '';
	$renderFn = $renderPage = $name1 = $name2 = '';
	
	list($docType, $docTemplate) = explode(':', $rule);
	list($name1, $name2, $renderFn, $renderPage) = explode(':', $values);
	
	$d	= array(
		'doc_type'	=> $docType,
		'template'	=> $docTemplate
	);
	$fn	= module('doc:pageRule', $d);
?>
    <tr>
      <td>{$docType}</td>
      <td>{$docTemplate}</td>
      <td>{$fn[fn]}</td>
      <td>{$fn[page]}</td>
      <td>{$name1}</td>
      <td>{$name2}</td>
    </tr>
<? } ?>
  </tbody>
</table>

<p>
Название в шаблоне документа можно разделить точкой для создания подкласса дакументов.<br>
Для подклассов используются все правила работы как у основного класса но используются свои "Вид конетнта" и "Вид страницы".

</p>

<? return '99-Разработчик'; } ?>