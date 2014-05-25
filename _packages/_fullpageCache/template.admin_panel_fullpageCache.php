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
    <input  type="submit" value="Сохранить" class="button" />
</p>
<p>
    <input type="hidden" name="fullpageCache[{$thisPage}]" value="" />
    <input type="radio" name="fullpageCache[{$thisPage}]" value="noCheck" {checked:$ini[:fullpageCache][$thisPage]=='noCheck'} title="Кешировать без проверки параметров" />
    <label>
        <input type="radio" name="fullpageCache[{$thisPage}]" value="full" {checked:$ini[:fullpageCache][$thisPage]=='full'} title="Кешировать с проверкой параметров" />
        Кешировать эту страницу <a href="{$thisPage}">{$thisPage}</a>
<? if ($ini[':fullpageCache'][$thisPage]=='noCheck'){ ?>
статическое кеширование, параметры не учитываются
<? } ?>
    </label>
</p>
<? foreach($pages as $thisPage=>$type){ ?>
<div>
    <input type="hidden" name="fullpageCache[{$thisPage}]" value="" />
    <input type="radio" name="fullpageCache[{$thisPage}]" value="noCheck" {checked:$ini[:fullpageCache][$thisPage]=='noCheck'} title="Кешировать без проверки параметров" />
    <label>
        <input type="radio" name="fullpageCache[{$thisPage}]" value="full" {checked:$ini[:fullpageCache][$thisPage]=='full'} title="Кешировать с проверкой параметров" />
        Кешировать эту страницу <a href="{$thisPage}">{$thisPage}</a> 
    </label>
<? if ($ini[':fullpageCache'][$thisPage]=='noCheck'){ ?>
статическое кеширование, параметры не учитываются
<? } ?>
</div>
<? } ?>
</form>
<? return 'Кеш страниц'; } ?>