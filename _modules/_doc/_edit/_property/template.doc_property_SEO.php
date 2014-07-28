<? function doc_property_SEO_update(&$data)
{
	if (!hasAccessRole('SEO')) return;

	$links 			= getValue('documetntLinks');
	$data[':links'] = $links;
	
	$SEO	= getValue('SEO');
	$newSEO	= getValue('nameSEO');
	$newSEOv= getValue('valueSEO');
	if (is_array($newSEO)){
		foreach($newSEO as $ndx => $name){
			$name = trim($name);
			if (!$name) continue;
			@$SEO[$name] = trim($newSEOv[$ndx]);
		}
	}
	
	if (!is_array($SEO)) return;
	$data['fields']['SEO'] = $SEO;
} ?>
<? function doc_property_SEO($data){?>
<?
	if (!hasAccessRole('SEO')) return;

	$db		= module('doc', $data);
	$id		= $db->id();
	$type	= $data['doc_type'];
	@$fields= $data['fields'];
	@$SEO	= $fields['SEO'];
	if (!is_array($SEO)) $SEO = array();
	module('script:jq_ui');
?>
<div id="seoTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#seoSEO">SEO</a></li>
    <li class="ui-corner-top"><a href="#seoTAGS">Метатеги</a></li>
    <li class="ui-corner-top"><a href="#seoLINKS">Ссылки</a></li>
</ul>
<div id="seoSEO" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
    Заголовок (title), перезаписывает автоматически сгенерированный
    <div><input name="SEO[title]" type="text" value="{$SEO[title]}" class="input w100" /></div>
    Ключевые слова (keywords metatag)
    <div><input name="SEO[keywords]" type="text" value="{$SEO[keywords]}" class="input w100" /></div>
    Описание (description metatag)
    <div><textarea name="SEO[description]" cols="" rows="5" class="input w100">{$SEO[description]}</textarea></div>
    <div>Класс стиля ссылки на страницу (пример: <b>icon i12</b>)</div>
    <div><input name="doc[fields][class]" type="text" class="input w100" value="{$fields[class]}" size="" /></div>
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
        if ($name == 'keywords' || $name == 'description' || $name == 'title')
            continue;
    ?>
    <tr>
        <td><a class="delete" href="">X</a></td>
        <td>{$name}</td>
        <td width="100%"><input name="SEO[{$name}]" type="text" value="{$val}" class="input w100" /></td>
    </tr>
    <? } ?>
    <tr class="adminReplicate" id="addMeta">
        <td><a class="delete" href="">X</a></td>
        <td><input name="nameSEO[]" type="text" value="" class="input w100" /></td>
        <td width="100%"><input name="valueSEO[]" type="text" value="" class="input w100" /></td>
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
    <td width="100%"><input type="text" name="documetntLinks[]" class="input w100" value="{$link}" /></td>
</tr>
<? } ?>
</tbody>
<tr class="adminReplicate" id="addLink">
    <td style="width:16px; min-width:16px"></td>
    <td width="100%">
        <input type="text" name="documetntLinks[]" class="input w100" />
    </td>
</tr>
</table>
    <p><input type="button" class="button adminReplicateButton" id="addLink" value="Добавть ссылку"></p>
    </div>
</div>
</div>
<script>
$(function() {
	$("#seoTabs").tabs();
	$('#seoTabs .sortable').sortable({
		axis: 'y'
	});
});
</script>
<? return '99-SEO'; } ?>