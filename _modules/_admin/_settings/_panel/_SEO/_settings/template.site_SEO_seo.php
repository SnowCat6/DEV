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
	}
	
	$sitemap	= file_get_contents(getSiteFile('sitemap.xml'));
?>
 	<a href="{{urlEx}}sitemap.xml" target="new">{{urlEx}}sitemap.xml</a>
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
