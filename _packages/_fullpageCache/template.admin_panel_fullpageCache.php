<?
function admin_panel_fullpageCache(&$val)
{
	if (!hasAccessRole('admin,developer,writer')) return;
	
	$ini		= getCacheValue('ini');
	$thisPage	= getURL('#');
	
	if (is_array($val = getValue('fullpageCache'))){
		removeEmpty($val);
		$ini[':fullpageCache']	= $val;
		setIniValues($ini);
	}
	$pages	= $ini[':fullpageCache'];
	if (!is_array($pages))	$pages = array();
	if ($pages[$thisPage])	unset($pages[$thisPage]);
?>
<form method="post" action="{{url:#}}">
<p>
    <label>
        <input type="hidden" name="fullpageCache[{$thisPage}]" value="" />
        <input type="checkbox" name="fullpageCache[{$thisPage}]" value="full" {checked:$ini[:fullpageCache][$thisPage]=='full'} />Кешировать эту страниу <u>{$thisPage}</u> 
    </label>
</p>
<? foreach($pages as $thisPage=>$type){ ?>
<div>
    <label>
        <input type="hidden" name="fullpageCache[{$thisPage}]" value="" />
        <input type="checkbox" name="fullpageCache[{$thisPage}]" value="{$type}" {checked:$ini[:fullpageCache][$thisPage]=='full'} />Кешировать эту страниу <u>{$thisPage}</u> 
    </label>
</div>
<? } ?>
<p>
    <input  type="submit" value="Сохранить" class="button" />
</p>
</form>
<? return 'Кеш страниц'; } ?>