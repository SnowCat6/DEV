<?
//	filelist
function admin_tab($filter, &$data)
{
	$ix		= testValue('ajax')?md5($filter):md5("ajax_$filter");
	$tabID	= "adminTab_$ix";
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
		include_once($path);
		if (function_exists($file)) $name = $file(&$data);
		$ctx = trim(ob_get_clean());
		
		if ($ctx == '') continue;
		if (!$name) $name = $file;
		$tabs[$name] = $ctx;
	}
	
	if (!$tabs) return;
	module('script:jq_ui');
	
	echo "<div id=\"$tabID\" class=\"ui-tabs ui-widget ui-widget-content ui-corner-all\">";
	echo '<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">';

	ksort($tabs);
	foreach($tabs as $name => &$ctx){
		$tabIID	= md5($name);
		$name	= preg_replace('#^([\d+-]+)#', '', $name);
		$name	= htmlspecialchars($name);
		echo "<li class=\"ui-corner-top\">";
		echo "<a href=\"#tab_$tabIID\">$name</a></li>";
	}
	if ($data || is_array($data)) echo '<li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>';
	echo '</ul>';
	
	foreach($tabs as $name => &$ctx){
		$tabIID	= md5($name);
		$name	= htmlspecialchars($name);
		echo "<!-- $name -->\r\n";
		echo "<div id=\"tab_$tabIID\" class=\"ui-tabs-panel ui-widget-content ui-corner-bottom\">$ctx</div>\r\n";
	}
	echo '</div>';
?>
<script language="javascript" type="text/javascript">
$(function(){
	$("#<?= $tabID?>").tabs();
	$("#<?= $tabID?> input[type=submit]").button();
	$("input.adminReplicateButton").click(function(){
		return adminCloneByID($(this).attr('id'));
	}).removeClass("adminReplicateButton");
});
function adminCloneByID(id)
{
	var o = $(".adminReplicate#" + id);
	o.clone().insertBefore(o).removeClass("adminReplicate");
	$(".adminReplicate#" + id + " input").val("");
	
	$('#' + id + ' a').click(function(){
		$(this).parents("tr").remove();
		return false;
	});
}
</script>
<? } ?>
