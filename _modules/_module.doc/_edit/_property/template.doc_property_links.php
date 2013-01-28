<?
function doc_property_links_update(&$data){
	$links = getValue('documetntLinks');
	$data[':links'] = $links;
}
?>
<?
function doc_property_links(&$data){
$db = module('doc', $data);
$id = $db->id();
?>
<div id="links">
Ссылки для отображения страницы, для примера: <b>index.htm</b>, <b>link_to_page.htm</b> или <b>http://site/link_to_page.htm</b>
<? foreach(module("links:get:/page$id.htm") as $link){?>
<div><input type="text" name="documetntLinks[]" class="input w100" value="{$link}" /></div>
<? } ?>
<div class="adminReplicate" id="addLink"><input type="text" name="documetntLinks[]" class="input w100" /></div>
<div><input type="button" class="button adminReplicate" id="addLink" value="Добавть ссылку"></div>
</div>
<? return 'Ссылки'; } ?>