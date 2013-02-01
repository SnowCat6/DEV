<?
function admin_edit($val, &$data)
{
	@$layout= $data[':layout'];
	@$bTop	= $data[':useTopMenu'];
	module('script:ajaxLink');
?>
<link rel="stylesheet" type="text/css" href="admin.css"/>
<div class="adminEditArea">
<? if ($bTop){ ?>
<div class="adminEditMenu adminTopMenu"><span>
<?
foreach($data as $name => $url)
{
	$iid = '';
	if ($name[0] == ':') continue;
	list($name, $iid) = explode('#', $name);
	if ($iid) $iid = " id=\"$iid\"";
?><a href="{!$url}"{!$iid}>{$name}</a>
<? } ?>
</span></div>
<?= $layout ?>
<? }else{ ?>
<?= $layout ?>
<div class="adminEditMenu"><span>
<?
foreach($data as $name => $url)
{
	$iid = '';
	if ($name[0] == ':') continue;
	list($name, $iid) = explode('#', $name);
	if ($iid) $iid = " id=\"$iid\"";
?><a href="{!$url}"{!$iid}>{$name}</a>
<? } ?>
</span></div>
<? } ?>
</div>
<? } ?>