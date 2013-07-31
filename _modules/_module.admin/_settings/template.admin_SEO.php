<?
function admin_SEO(&$data)
{
	if (!access('write', 'admin:SEO')) return;
//	if (!hasAccessRole('SEO')) return;
	
	$SEO	= getValue('SEO');
	if (is_array($SEO))
	{
		$newSEO	= getValue('nameSEO');
		$newSEOv= getValue('valueSEO');
		if (is_array($newSEO)){
			foreach($newSEO as $ndx => $name){
				$name = trim($name);
				if (!$name) continue;
				@$SEO[$name] = trim($newSEOv[$ndx]);
			}
		}
		
		$ini	= getCacheValue('ini');
		$ini[':SEO']	= $SEO;
		setIniValues($ini);
		
		file_put_contents_safe(localHostPath.'/robots.txt', 			getValue('valueROBOTS'));
		file_put_contents_safe(localCacheFolder.'/siteFiles/robots.txt',getValue('valueROBOTS'));
		
		file_put_contents_safe(localHostPath.'/sitemap.xml', 				getValue('valueSITEMAP'));
		file_put_contents_safe(localCacheFolder.'/siteFiles/sitemap.xml',	getValue('valueSITEMAP'));
		
		module('message', 'Конфигурация сохранена');
	}

	$ini	= getCacheValue('ini');
	@$SEO	= $ini[':SEO'];
	if (!is_array($SEO)) $SEO = array();
	
	module('script:ajaxForm');
	module('script:clone');
	module('script:jq_ui');
	
	$robots		= file_get_contents(localCacheFolder.'/siteFiles/robots.txt');
	$sitemap	= file_get_contents(localCacheFolder.'/siteFiles/sitemap.xml');
?>
{{page:title=Настройки SEO}}
<form action="{{getURL:admin_SEO}}" method="post" class="admin ajaxForm">
<div id="seoTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-state-default ui-corner-top"><a href="#seoSEO">SEO</a></li>
    <li class="ui-corner-top"><a href="#seoROBOTS">robots.txt</a></li>
    <li class="ui-corner-top"><a href="#seoSITEMAP">sitemap.xml</a></li>
	<li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="seoSEO" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
Заголовок (title) для всех страниц сайта без заголовка
<div><input name="SEO[titleEmpty]" type="text" value="{$SEO[titleEmpty]}" class="input w100" /></div>
Заголовок (title) для всех страниц сайта, знак % заменяется на заголовок документов
<div><input name="SEO[title]" type="text" value="{$SEO[title]}" class="input w100" /></div>
Ключевые слова (keywords metatag) для всех старниц сайта
<div><input name="SEO[keywords]" type="text" value="{$SEO[keywords]}" class="input w100" /></div>
Описание (description metatag) для всех старниц сайта
<div><textarea name="SEO[description]" cols="" rows="5" class="input w100">{$SEO[description]}</textarea></div>
Собственные метатеги для всех старниц сайта
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="table">
<tr>
    <th></th>
    <th nowrap="nowrap">Название метатега (name)</th>
    <th nowrap="nowrap">Значение метатега (content)</th>
</tr>
<?
foreach($SEO as $name => $val){
	if ($name == 'keywords' ||
		$name == 'description' ||
		$name == 'title' ||
		$name == 'titleEmpty')
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
<p><input type="button" class="button adminReplicateButton" id="addMeta" value="Добавть метатег" /></p>
</div>

<div id="seoROBOTS" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
    <textarea name="valueROBOTS" cols="" rows="20" class="input w100">{!$robots}</textarea>
</div>

<div id="seoSITEMAP" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
    <textarea name="valueSITEMAP" cols="" rows="20" class="input w100">{!$sitemap}</textarea>
</div>
</div>
</form>
<script>
$(function() {
	$("#seoTabs").tabs();
});
</script>
<? return '5-SEO'; } ?>