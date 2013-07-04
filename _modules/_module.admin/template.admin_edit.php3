<?
function admin_edit($val, &$data)
{
	@$layout= $data[':layout'];
	@$bTop	= $data[':useTopMenu'];
	@$dragID= $data[':draggable'];
	if ($dragID) module('script:draggable');
	module('script:ajaxLink');
	@define('noCache', true);
?>
<link rel="stylesheet" type="text/css" href="admin.css"/>
<div class="adminEditArea">
<? if ($bTop){ ?>
<div class="adminEditMenu">
<? if ($dragID){ ?><div class="ui-icon ui-icon-arrow-4-diag"{!$dragID}></div><? } ?>
<? foreach($data as $name => $url){
	$iid = '';
	if ($name[0] == ':') continue;
	list($name, $iid) = explode('#', $name);
	if ($iid) $iid = " id=\"$iid\"";
?><a href="{!$url}"{!$iid}>{$name}</a><? } ?>
</div>
<?= $layout ?>
<? }else{ ?>
<?= $layout ?>
<div class="adminEditMenu adminBottom"{!$dragID}>
<?
foreach($data as $name => $url){
	$iid = '';
	if ($name[0] == ':') continue;
	list($name, $iid) = explode('#', $name);
	if ($iid) $iid = " id=\"$iid\"";
?><a href="{!$url}"{!$iid}>{$name}</a><? } ?>
</div>
<? } ?>
</div>
<? } ?>
