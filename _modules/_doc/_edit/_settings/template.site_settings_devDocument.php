<?
//	+function site_settings_devDocument_update
function site_settings_devDocument_update(&$ini)
{
	if (!hasAccessRole('developer')) return;
	
	$rules		= array();
	$docTypes	= getCacheValue(':docTypes') or array();
	$r			= getValue('docRules') or array();
	foreach($r as $v)
	{
		if (!$v['docType']) continue;
		
		if (!$v['name1']) $v['name1']	= "$v[docType]:$v[docTemplate]";
		if (!$v['name2']) $v['name2']	= $v['name1'];
		
		$rules["$v[docType]:$v[docTemplate]"]	= "$v[name1]:$v[name2]:$v[renderFn]:$v[renderPage]";
		$docTypes["$v[docType]:$v[docTemplate]"]= "$v[name1]:$v[name2]:$v[renderFn]:$v[renderPage]";
	}
	
//	setIniValue(':docRules', $rules);
	$ini[':docRules']	= $rules;
	setCacheValue('docTypes', $docTypes);
}
function site_settings_devDocument($ini)
{
	if (!hasAccessRole('developer')) return;
?>
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
$fnTemplates	= module("findTemplates:^(doc_page_.*)");

$namesPage	= array();
$pages		= getCacheValue('pages') or array();
foreach($pages as $name => $val){
	if (!preg_match('#^page\.(.*)#', $name, $v)) continue;
	$namesPage[$v[1]]	= $v[1];
}

$types			= array();
$docTypes		= getCacheValue(':docTypes') or array();
foreach($docTypes as $name => $val){
	list($type, $template) = explode(':', $name);
	$types[$type] = docType($type);
}

$rules			= getIniValue(':docRules') or array();
$rules['new']	= '';
$rules['new2']	= '';
foreach($rules as $rule => $values)
{
	$docType	= $docTemplate = '';
	$renderFn	= $renderPage = $name1 = $name2 = '';
	
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
<? foreach($fnTemplates as $name => $val){ ?>
    <option value="{$name}"{selected:$name==$renderFn}>{$name}</option>
<? } ?>
</select>
     </td>
      <td>
<select name="docRules[{$rule}][renderPage]" class="input w100">
    <option value="">-- стандартная --</option>
<? foreach($namesPage as $name => $val){ ?>
    <option value="{$name}"{selected:$name==$renderPage}>{$name}</option>
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
<? return 'Документы'; } ?>