<? function admin_cacheLog($val, $data)
{
	m('page:title', 'Лог кеша');
	m('script:jq_ui');
?>
{{ajax:template=ajax_edit}}
<div id="cacheTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-state-default ui-corner-top"><a href="#localCache">Локальный кеш</a></li>
    <li class="ui-state-default ui-corner-top"><a href="#memCache">Memcache</a></li>
</ul>

<div id="memCache">
<? if (defined('memcache')){ ?>
<div class="memCacheLog">
<?
global $memcacheObject;
$url	= getSiteURL();
$f		= "#^$url:#";

$allSlabs	= $memcacheObject->getExtendedStats('slabs');
$items		= $memcacheObject->getExtendedStats('items');
foreach($allSlabs as $server => &$slabs) {

	foreach($slabs AS $slabId => &$slabMeta) {
		if (!is_int($slabId)) continue;
		
		$cdump = $memcacheObject->getExtendedStats('cachedump', $slabId);
		$bFirst= true;
		foreach($cdump AS $keys => &$arrVal) {
			if (!is_array($arrVal)) continue;
			
			foreach($arrVal AS $key => $v) {  
				if (!preg_match($f, $key)) continue;
				if ($bFirst){
					echo "<ul><a href='@'>slabId: $slabId</a>";
					$bFirst = false;
				}
				echo '<li>';
				$v	= $memcacheObject->get($key);
				if (is_array($v)){
					echo '<a href="#">', htmlspecialchars($key), '</a>';
					showCacheLog($v);
				}else{
					echo htmlspecialchars($key);
					echo ', ', gettype($v), ' ', strlen($v);
				}
				echo '</li>';
			}
		}
		if (!$bFirst) echo '</ul>';
	}
}
?>
</div>
<? }else{ ?>
<? messageBox('Модуль Memcache не используется', true)?>
<? } ?>
</div>


<div id="localCache">
<div class="cacheLog">
<? global $_CACHE; showCacheLog($_CACHE); ?>
</div>
</div>

</div>
<script>
$(function(){
	$("#cacheTabs").tabs();
	$(".cacheLog ul ul, .memCacheLog > ul > li, .memCacheLog ul ul").hide();

	$(".cacheLog a, .memCacheLog a").click(function(){
		var i = $(this).parent().find("> ul,> li");
		if (i.hasClass("open")) i.hide().removeClass("open");
		else i.show().addClass("open");
		return false;
	});
});
</script>
<? } ?>
<? function showCacheLog(&$log){
echo '<ul>';
foreach($log as $name => &$val){
	echo '<li>';
	if (is_array($val) && $val){
		echo '<a href="#">', htmlspecialchars($name), '</a>';
		showCacheLog($val);
	}else{
		echo htmlspecialchars($name);
		showCacheLog($val);
		echo ', ', gettype($val) , ' ', strlen($val);
	}
	echo '</li>';
}
echo '</ul>';
}?>