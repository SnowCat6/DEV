<? function admin_cacheLog($val, $data)
{
	m('page:title', 'Лог кеша');
	m('script:jq');
?>
<h2>Memcache</h2>
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
			foreach($arrVal AS $key => &$v) {  
				if (!preg_match($f, $key)) continue;
				if ($bFirst){
					echo "<ul><a href='@'>slabId: $slabId</a>";
					$bFirst = false;
				}
				echo '<li>';
				echo htmlspecialchars($key);
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
<h2>Локальный кеш</h2>
<div class="cacheLog">
<?
global $_CACHE;
showCacheLog($_CACHE);
?>
</div>
<script>
$(function(){
	$(".cacheLog ul ul").hide();
	$(".memCacheLog ul li").hide();

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
	if (is_array($val)){
		echo '<a href="#">' . htmlspecialchars($name) . '</a>';
		showCacheLog($val);
	}else{
		echo htmlspecialchars($name);
		showCacheLog($val);
		echo ', '. strlen($val);
	}
	echo '</li>';
}
echo '</ul>';
}?>