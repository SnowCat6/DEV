<? function site_settings_packages_update(&$ini)
{
	if (!hasAccessRole('developer')) return;

	$packages	= &$ini[':packages'];
	if (!is_array($packages)) return;
	
	$files	= getDirs('_packages');
	foreach($packages as $name => &$path){
		if ($path) $path = $files[$name];
		else unset($packages[$name]);
	}
}
function site_settings_packages($ini){
	if (!hasAccessRole('developer')) return;
?>
<style>
.moduleDescription{ display:none;}
.moduleDescription.current{ display:block; }
#moduleSelect{ padding-right:20px;}
.moduleSelect:hover{
	background:#333;
	background-color:rgba(255, 255, 255, 0.3);
}
</style>
{{script:jq}}
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
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td width="50%" valign="top" id="moduleSelect">
<?
$modules	= array();
foreach(getDirs('_packages') as $name => $path)
{
	$s		= readIniFile("$path/config.ini");
	if (!$s) $s = array();
	
	if ($s['about']['type'] == 'lib') continue;
	
	$thisName	= $s['about']['name'];
	if (!$thisName) $thisName = $name;
	
	$modules[$name]	= $s;
	$iid	= md5($name);
	$class	= isset($ini[':packages'][$name])?' checked="checked"':'';
?>
<div id="module_{$iid}" class="moduleSelect"><label>
    <input type="hidden" name="settings[:packages][{$name}]" value="" {!$class} />
    <input type="checkbox" name="settings[:packages][{$name}]" value="{$name}" {!$class} />{$thisName}
</label></div>
<? } ?>
    </td>
    <td width="50%" valign="top" id="moduleDescription">
<? foreach($modules as $name => $s){
	$iid	= md5($name);
?>
<div id="module_{$iid}" class="moduleDescription">
<? foreach($s as $name2 => $val2){ ?>
<div><b>{$name2}</b></div>
<? foreach($val2 as $name3 => $val3){ ?>
<div>{$name3}: {$val3}</div>
<? } ?>
<? } ?>
</div>
<? } ?>
    </td>
  </tr>

</table>
<? return '8-Модули'; } ?>