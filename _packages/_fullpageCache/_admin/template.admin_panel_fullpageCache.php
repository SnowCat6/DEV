<?
//	+admin_panel_fullPageCache
function admin_panel_fullPageCache($val)
{
	if (!hasAccessRole('admin,developer,writer'))
		return;
		
	return array(
		'name'	=> 'Кеш страниц',
		'URL'	=> getURL('admin_fullpagecache')
	);
}
//	+function module_fullPageCacheTab
function module_fullPageCacheTab($val)
{
	if (!hasAccessRole('admin,developer,writer')) return;
	
	$ini		= getCacheValue('ini');
	$thisPage	= getURL('#');
	
	if (is_array($val = getValue('fullpageCache')))
	{
		removeEmpty($val);
		$ini[':fullpageCache']	= $val;
		setIniValues($ini);
	}
	$pages	= $ini[':fullpageCache'];
	if (!is_array($pages))	$pages = array();
	if ($pages[$thisPage])	unset($pages[$thisPage]);
?>

<module:script:ajaxForm />
<module:script:adminTabs />
<module:script:jq />
<script src="script/fullPageCache.js"></script>

<form method="post" action="{{url:#}}" class="ajaxFormNow ajaxReload">
<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#fullPageCacheThis">Текущая страница</a></li>
    <li class="ui-corner-top"><a href="#fullPageCacheNo">Не учитывая параметров</a></li>
    <li class="ui-corner-top"><a href="#fullPageCacheYes">С учетом параметров</a></li>
    <li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="fullPageCacheThis">
<p>
<label>
    <input type="radio" name="fullpageCache[{$thisPage}]" value="" {checked:$ini[:fullpageCache][$thisPage]==''} title="Не кешировать" />
    Не кешировать
</label>
</p>

<p>
<label>
    <input type="radio" name="fullpageCache[{$thisPage}]" value="noCheck" {checked:$ini[:fullpageCache][$thisPage]=='noCheck'} title="Кешировать без проверки параметров" />
    Кешировать эту страницу не учитывая параметров
</label>
</p>

<p>
<label>
    <input type="radio" name="fullpageCache[{$thisPage}]" value="full" {checked:$ini[:fullpageCache][$thisPage]=='full'} title="Кешировать с проверкой параметров" />
    Кешировать эту страницу с учетом параметров
</label>
</p>
</div>

<div id="fullPageCacheNo">
<? showCachePages($pages, 'noCheck') ?>
</div>

<div id="fullPageCacheYes">
<? showCachePages($pages, 'full') ?>
</div>

</div>
</form>
<? } ?>

<? function showCachePages(&$pages, $thisType){
?>
<? foreach($pages as $thisPage => $type){
	if ($type!= $thisType) continue;
?>
<div>
    <input type="radio" name="fullpageCache[{$thisPage}]" value="" title="Не кешировать" /> - 
    <input type="radio" name="fullpageCache[{$thisPage}]" value="noCheck" {checked:$type=='noCheck'} title="Кешировать без проверки параметров" />
    <input type="radio" name="fullpageCache[{$thisPage}]" value="full" {checked:$type=='full'} title="Кешировать с проверкой параметров" />
 
    Кешировать эту страницу <a href="{$thisPage}">{$thisPage}</a>
</div>
<? } ?>
<? } ?>

