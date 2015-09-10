<? function site_settings_packages_update(&$ini)
{
	if (!hasAccessRole('developer')) return;

	$packages	= &$ini[':packages'];
	if (!is_array($packages)) $packages = array();
	
	$files	= findPackages();
	foreach($packages as $name => &$path){
		if ($path) $path = $files[$name];
		else unset($packages[$name]);
	}
	
	$i	= getCacheValue('ini');
	$ip	= $i[':packages'];
	if (serialize($ip) == serialize($packages)) return;
	
	$i[':packages']	= $packages;
	setIniValues($i);

	define('systemClearCacheCode', true);
	echo "{@systemClearCacheCode}";
}
//	+function module_systemClearCacheCode
function module_systemClearCacheCode($val, $data)
{
	if (!defined('systemClearCacheCode')) return;
	$site	= siteFolder();
	$msg	= execPHP("index.php clearCacheCode $site");
	messageBox($msg);
}
function site_settings_packages($ini)
{
	if (!hasAccessRole('developer')) return;
	
	$pkg		= array();
	$modules	= array();
	$files		= findPackages();
	
	foreach($files as $name => $path)
	{
		$s		= readIniFile("$path/config.ini");
		if (!$s) $s = array();
		
		$type	= $s['about']['type'];
		if (!$type) $type = 'main';
		
		$pkg[$type][$name]	= $s;
	}
	$packages	= array();
	$names		= array();
	$names['Основные']		= 'main';
	$names['Инстументы']	= 'tools';
	$names['Магазин']		= 'shop';
	$names['Реклама']		= 'adv';
	$names['Библиотеки']		= 'lib';
	
	foreach($names as $name=>$type){
		if (!$pkg[$type]) continue;
		$packages[$name]	= $pkg[$type];
		unset($pkg[$type]);
	}
	foreach($pkg as $name=>$val){
		$packages[$name]	= $val;
	}
?>
<style>
.moduleDescription{ display:none;}
.moduleDescription.current{ display:block; }
#moduleSelect{ padding-right:20px;}
.moduleSelect:hover{
	background:#333;
	background-color:rgba(255, 255, 255, 0.3);
}
.moduleSelect blockquote{
	margin:0 5px 10px 25px;
	font-size:12px;
}
</style>
{{script:jq}}
{{script:adminTabs}}
<script>
$(function(){
	$(".moduleSelect").hover(function(){
		$("#moduleDescription div").removeClass("current");
		$("#moduleDescription #"+$(this).attr("id")).addClass("current");
	}, function(){
		$("#moduleDescription div").removeClass("current");
	});
});
</script>
<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
<? foreach($packages as $name=>$v){ ?>
    <li class="ui-corner-top"><a href="#package_{$name}">{$name}</a></li>
<? } ?>
</ul>

<? foreach($packages as $name=>$v){ ?>
<div id="package_{$name}">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td width="50%" valign="top" id="moduleSelect">
<?
foreach($files as $name => $path)
{
	$s		= $v[$name];
	if (is_null($s)) continue;
	
	$thisName	= $s['about']['name'];
	if (!$thisName) $thisName = $name;
	
	$modules[$name]	= $s;
	$iid	= md5($name);
	$class	= isset($ini[':packages'][$name])?' checked="checked"':'';
?>
<div id="module_{$iid}" class="moduleSelect"><label>
    <input type="hidden" name="settings[:packages][{$name}]" value="" {!$class} />
    <input type="checkbox" name="settings[:packages][{$name}]" value="{$name}" {!$class} />{$thisName}
 <? if($s['about']['description']){ ?>
    <blockquote>{$s[about][description]}</blockquote>
<? } ?>
</label>
</div>
<? } ?>
    </td>
    <td width="50%" valign="top" id="moduleDescription">
<? foreach($modules as $name => $s){
	$iid	= md5($name);
?>
<div id="module_{$iid}" class="moduleDescription">
<? foreach($s as $name2 => $val2){ ?>
    <h3><b>{$name2}</b></h3>
<? foreach($val2 as $name3 => $val3){ ?>
    <div>{$name3}: {$val3}</div>
<? } ?>
<? } ?>
</div>
<? } ?>
    </td>
  </tr>

</table>
</div>
<? } ?>

</div>
<? return '8-Модули'; } ?>