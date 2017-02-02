<?
//	+function site_SEO_doc_update
function site_SEO_doc_update()
{
	$id		= getValue("SEO_DOC");
	if (!$id) return;
	
	$db		= module("doc");
	$data	= $db->openID($id);
	$key	= $db->key;
	$d	= array(
		$key		=> $id,
		'doc_type'	=> $data['doc_type'],
		'template'	=> $data['template']
	);
	doc_property_SEO_update($d);

	unset($d[$key]);
	unset($d['doc_type']);
	unset($d['template']);
	
	if ($d) module("doc:update:$id:edit", $d);
}
//	+function site_SEO_doc
function site_SEO_doc()
{
	$id	= currentPage();
	if (!$id) $id = getValue('SEO_DOC');
	if (!$id) return;

	$db		= module("doc");
	$data	= $db->openID($id);
	if (!$data) return;
	
	echo makeFormInput(array('SEO_DOC' => $id));
	
	echo htmlspecialchars($data['title']);
	doc_property_SEO($data);
	
	return "0-SEO документ $id";
}


function doc_property_SEO_update(&$data)
{
	if (!access('write', 'admin:SEO')) return;

	$db	= module("doc", $data);
	$id	= $db->id();
	
	$links 			= getValue("docLinks_$id");
	$data[':links'] = $links;

	$type		= $data['doc_type'];
	$template	= $data['template'];

	setStorage("SEO_$type", getValue("SEO_$type"), 'ini');
	setStorage("SEO_$type"."_$template", getValue("SEO_$type"."_$template"), 'ini');

	$SEO	= getValue("SEO_$id");
	$newSEO	= getValue("nameSEO_$id");
	$newSEOv= getValue("valueSEO_$id");
	if (is_array($newSEO))
	{
		foreach($newSEO as $ndx => $name)
		{
			$name = trim($name);
			if (!$name) continue;
			$SEO[$name] = trim($newSEOv[$ndx]);
		}
	}
	
	if (!is_array($SEO)) return;
	$data['fields']['SEO'] = $SEO;
} ?>
<? function doc_property_SEO($data){?>
<?
	if (!access('write', 'admin:SEO')) return;

	$db		= module('doc', $data);
	$id		= $db->id();
	
	$type		= $data['doc_type'];
	$template	= $data['template'];
	$fields	= $data['fields'];
	$SEO	= $fields['SEO'];
	if (!is_array($SEO)) $SEO = array();

	module('script:jq_ui');
	
	$typeName	= docType($type, 1);
	$typeName2	= docTypeEx($type, $template, 1);
	if ($typeName2 == $typeName) $typeName2 = '';
	
	$iniType	= getStorage("SEO_$type", 'ini');
	$iniTemplate= getStorage("SEO_$type"."_$template", 'ini');
	
	$SEOReplace	= module("doc:SEOget:$id",	$data);
?>
<div id="seoTabs" class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">

    <li class="ui-corner-top"><a href="#seoSEO">SEO страницы</a></li>
<? if ($typeName2){ ?>
    <li class="ui-corner-top"><a href="#seoSEO_{$type}_{$template}">SEO всех {$typeName2}</a></li>
<? } ?>
<? if ($typeName){ ?>
    <li class="ui-corner-top"><a href="#seoSEO_{$type}">SEO всех {$typeName}</a></li>
<? } ?>

    <li class="ui-corner-top"><a href="#seoTAGS">Метатеги</a></li>
    <li class="ui-corner-top"><a href="#seoLINKS">Ссылки</a></li>
</ul>
<div id="seoSEO" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<table class="focusKeeper"><tr>
<td valign="top" width="100%">
   	Заголовок (title), перезаписывает автоматически сгенерированный
    <div><input name="SEO_{$id}[title]" type="text" value="{$SEO[title]}" class="input w100 input_SEO" /></div>
    Ключевые слова (keywords metatag)
    <div><input name="SEO_{$id}[keywords]" type="text" value="{$SEO[keywords]}" class="input w100 input_SEO" /></div>
    Описание (description metatag)
    <div><textarea name="SEO_{$id}[description]" cols="" rows="5" class="input w100 input_SEO">{$SEO[description]}</textarea></div>
    Код в HEAD секции
    <div><textarea name="SEO_{$id}[:HEAD]" cols="" rows="5" class="input w100">{$SEO[:HEAD]}</textarea></div>
    <div>Класс стиля ссылки на страницу (пример: <b>icon i12</b>)</div>
    <div><input name="doc[fields][class]" type="text" class="input w100" value="{$fields[class]}" size="" /></div>
</td>
<td valign="top"><? docSEOhelper($SEOReplace)?></td>
</tr></table>
</div>
<div id="seoTAGS" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
    Собственные метатеги
    <table width="100%" border="0" cellspacing="0" cellpadding="2" class="table">
    <tr>
        <th></th>
        <th nowrap="nowrap">Название метатега (name)</th>
        <th nowrap="nowrap">Значение метатега (content)</th>
    </tr>
<?
foreach($SEO as $name => $val){
if ($name == 'keywords' || $name == 'description' || $name == 'title' || $name == ':HEAD')
	continue;
?>
    <tr>
        <td><a class="delete" href="">X</a></td>
        <td>{$name}</td>
        <td width="100%"><input name="SEO_{$id}[{$name}]" type="text" value="{$val}" class="input w100" /></td>
    </tr>
    <? } ?>
    <tr class="adminReplicate" id="addMeta">
        <td><a class="delete" href="">X</a></td>
        <td><input name="nameSEO_{$id}[]" type="text" value="" class="input w100" /></td>
        <td width="100%"><input name="valueSEO_{$id}[]" type="text" value="" class="input w100" /></td>
    </tr>
    </table>
    <p><input type="button" class="button adminReplicateButton" id="addMeta" value="Добавть метатег"></p>
</div>
<div id="seoLINKS" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<?
    $db = module('doc', $data);
    $id = $db->id();
?>
    <div id="links">
    Показывать страницу вместо текущей
    <div><input name="doc[fields][redirect]" type="text" value="{$fields[redirect]}" class="input w100" /></div><br />

    Ссылки для отображения страницы, для примера: <b>index.htm</b>, <b>link_to_page.htm</b> или <b>http://site/link_to_page.htm</b>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tbody class="sortable">
<? foreach(module("links:get:/page$id.htm") as $link){?>
<tr>
	<td><div  class="ui-icon ui-icon-arrowthick-2-n-s"></div></td>
    <td width="100%"><input type="text" name="docLinks_{$id}[]" class="input w100" value="{$link}" /></td>
</tr>
<? } ?>
</tbody>
<tr class="adminReplicate" id="addLink">
    <td style="width:16px; min-width:16px"></td>
    <td width="100%">
        <input type="text" name="docLinks_{$id}[]" class="input w100" />
    </td>
</tr>
</table>
    <p><input type="button" class="button adminReplicateButton" id="addLink" value="Добавть ссылку"></p>
    </div>
</div>

<? if ($typeName2){ ?>
<div id="seoSEO_{$type}_{$template}">
<table class="focusKeeper"><tr>
<td valign="top" width="100%">
    Заголовок (title), перезаписывает автоматически сгенерированный
    <div><input name="SEO_{$type}_{$template}[title]" type="text" value="{$iniTemplate[title]}" class="input w100 input_SEO" /></div>
    Ключевые слова (keywords metatag)
    <div><input name="SEO_{$type}_{$template}[keywords]" type="text" value="{$iniTemplate[keywords]}" class="input w100 input_SEO" /></div>
    Описание (description metatag)
    <div><textarea name="SEO_{$type}_{$template}[description]" cols="" rows="5" class="input w100 input_SEO">{$iniTemplate[description]}</textarea></div>
    Код в HEAD секции
    <div><textarea name="SEO_{$type}_{$template}[:HEAD]" cols="" rows="5" class="input w100">{$iniTemplate[:HEAD]}</textarea></div>
</td>
<td valign="top"><? docSEOhelper($SEOReplace)?></td>
</tr></table>
</div>
<? } ?>

<? if ($typeName){ ?>
<div id="seoSEO_{$type}">
<table class="focusKeeper"><tr>
<td valign="top" width="100%">
    Заголовок (title), перезаписывает автоматически сгенерированный
    <div><input name="SEO_{$type}[title]" type="text" value="{$iniType[title]}" class="input w100 input_SEO" /></div>
    Ключевые слова (keywords metatag)
    <div><input name="SEO_{$type}[keywords]" type="text" value="{$iniType[keywords]}" class="input w100 input_SEO" /></div>
    Описание (description metatag)
    <div><textarea name="SEO_{$type}[description]" cols="" rows="5" class="input w100 input_SEO">{$iniType[description]}</textarea></div>
    Код в HEAD секции
    <div><textarea name="SEO_{$type}[:HEAD]" cols="" rows="5" class="input w100">{$iniType[:HEAD]}</textarea></div>
</td>
<td valign="top"><? docSEOhelper($SEOReplace)?></td>
</tr></table>
</div>
<? } ?>

</div>

{{script:adminTabs}}

<link rel="stylesheet" type="text/css" href="css/jq.doc_SEO.css">
<script src="script/jq.doc_SEO.js"></script>

<? return '99-SEO'; } ?>

<? function docSEOhelper($SEO)
{
	$replace	= $SEO[':replace'];
	if (!is_array($replace)) return;
?>
<div style="padding-left:10px; width: 200px; min-width: 200px; max-width:200px" class="SEOhelper">
    <p>Замена {название}, {префикс?название}, {префикс?название?постфикс}<br>
    на значение в документе</p>
<? foreach($replace as $name=>$value){ ?>
    <div><a href="#" title="{$value}">{<?= $name ?>}</a>
    <a href="#" title="{$value}">{?{$name}?}</a></div>
<? } ?>
</div>
<? } ?>