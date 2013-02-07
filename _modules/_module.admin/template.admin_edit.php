<?
function admin_edit($val, &$data)
{
	@$layout= $data[':layout'];
	@$bTop	= $data[':useTopMenu'];
	@$dragID= $data[':draggable'];
	if ($dragID) module('script:draggable');
	module('script:ajaxLink');
?>
<link rel="stylesheet" type="text/css" href="admin.css"/>
<? if ($dragID){ ?><div class="draggable" id="drag-{$dragID}"><? } ?>
<div class="adminEditArea">
<? if ($bTop){ ?>
<div class="adminEditMenu adminTopMenu">
<?
foreach($data as $name => $url)
{
	$iid = '';
	if ($name[0] == ':') continue;
	list($name, $iid) = explode('#', $name);
	if ($iid) $iid = " id=\"$iid\"";
?><a href="{!$url}"{!$iid}>{$name}</a>
<? } ?>
</div>
<?= $layout ?>
<? }else{ ?>
<?= $layout ?>
<div class="adminEditMenu">
<?
foreach($data as $name => $url)
{
	$iid = '';
	if ($name[0] == ':') continue;
	list($name, $iid) = explode('#', $name);
	if ($iid) $iid = " id=\"$iid\"";
?><a href="{!$url}"{!$iid}>{$name}</a>
<? } ?>
</div>
<? } ?>
</div>
<? if ($dragID){ ?></div><? } ?>
<? } ?>
