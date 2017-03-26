<?
//	+function site_SEO_robot
function site_SEO_robot()
{
	if (!access('write', 'admin:SEO')) return;

	if (testValue('valueROBOTS')){
		writeSiteFile('robots.txt',	getValue('valueROBOTS'));
	}

	$robots		= file_get_contents(getSiteFile('robots.txt'));
?>
	<a href="{{urlEx}}robots.txt" target="new">{{urlEx}}robots.txt</a>
    <textarea name="valueROBOTS" cols="" rows="20" class="input w100">{!$robots}</textarea>
<? return '101-robots.txt' ; } ?>


<?
//	+function site_SEO_sitemap
function site_SEO_sitemap()
{
	if (!access('write', 'admin:SEO')) return;

	if (testValue('valueSITEMAP')){
		writeSiteFile('sitemap.xml',getValue('valueSITEMAP'));
		$ini	= getIniValue(':');
		$ini['sitemapAutoMake']	= getValue('valueAUTOMAKE')==1?'yes':'';
		setIniValue(':', $ini);
	
		m("SEO:makeSiteMap");
	}
	
	$sitemap	= file_get_contents(getSiteFile('sitemap.xml'));
	$autoMake	= getIniValue(':');
	$autoMake	= $autoMake['sitemapAutoMake']=='yes';
?>
<div>
    <a href="{{urlEx}}sitemap.xml" target="new" style="float:left">{{urlEx}}sitemap.xml</a>
    <label style="float:right">
    	<input type="hidden" name="valueAUTOMAKE" value="0" />
        <input type="checkbox" name="valueAUTOMAKE" value="1" {checked:$autoMake} />
        Автосоздание из карты сайта
    </label>
</div>
<textarea name="valueSITEMAP" cols="" rows="20" class="input w100">{!$sitemap}</textarea>
<? return '101-sitemap.xml' ; } ?>


<?
//	+function site_SEO_head
function site_SEO_head()
{
	if (!access('write', 'admin:SEO')) return;

	if (testValue('valueHEAD'))
	{
		$head			= getIniValue(':SEO-raw');
		$head['head']	= base64_encode(getValue('valueHEAD'));
		setIniValue(':SEO-raw', $head);
	}

	$head	= getIniValue(':SEO-raw');
	$head	= base64_decode($head['head']);
?>
    <textarea name="valueHEAD" cols="" rows="20" class="input w100">{!$head}</textarea>
<? return '100-Код HEAD (global)' ; } ?>
