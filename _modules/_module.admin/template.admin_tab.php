<?
//	filelist
function admin_tab($filter, &$data)
{
	$d		= array();
	$modules= getCacheValue('templates');
	foreach($modules as $name => $path){
		if (!preg_match("#$filter#", $name)) continue;
		$d[$name] = $path;
	}
	
	$tabs = array();
	foreach($d as $file => $path)
	{
		
		ob_start();
		include($path);
		if (function_exists($file)) $name = $file(&$data);
		$ctx = trim(ob_get_clean());
		
		if ($ctx == '') continue;
		if (!$name) $name = $file;
		$tabs[$name] = $ctx;
	}
	
	if (!$tabs) return;
	module('script:jq_ui');
	
	echo '<div id="adminTabArea">';
	echo '<ul>';
	foreach($tabs as $name => &$ctx){
		$name = htmlspecialchars($name);
		echo "<li><a href=\"#tab_$name\">$name</a></li>";
	}
	echo '<li style="float:right"><input name="docSave" type="submit" value="Сохранить"/></li>';
	echo '</ul>';
	
	foreach($tabs as $name => &$ctx){
		$name = htmlspecialchars($name);
		echo "<div id=\"tab_$name\">$ctx</div>";
	}
	echo '</div>';
}
?>
<script>
$(function() {
	$( "#adminTabArea" ).tabs();
	$( "#adminTabArea input[type=submit]").button();
	$( "#adminTabArea input[type=button]").button();
	$("input.adminReplicate").click(function(){
		var id = $(this).attr('id');
		var o = $("div.adminReplicate#" + id);
		o.clone().insertAfter(o).removeClass("adminReplicate");
	});
});
</script>
