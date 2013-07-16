<? function doc_property_workdata_update(&$data)
{
	$workData = getValue('workData');
	if (!is_array($workData)) $workData = array();
	$data['fields']['workData']	= $workData;
}?>
<? function doc_property_workdata($data){ ?>
<?
if ($data['template'] != 'workdata') return;
if ($data['doc_type'] != 'article') return;

$db		= module('doc', $data);
$type	= $data['doc_type'];

@$f	= $data['fields'];
@$w	= $f['workData'];
?>

<div id="workTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
<?
$ddb = module('doc');
$search['template']	= 'work';
$search['type']		= 'page';
$search['prop']['workData'] = 'yes';
$ddb->order			= 'sort';
$ddb->open(doc2sql($search));
while($d = $ddb->next()){
	$workID	=	$ddb->id();
?>
    <li class="ui-corner-top"><a href="#workData<? if(isset($workID)) echo htmlspecialchars($workID) ?>"><? if(isset($d["title"])) echo htmlspecialchars($d["title"]) ?></a></li>
<? } ?>
</ul>
<?
$ddb->seek(0);
while($d = $ddb->next()){
	$workID	=	$ddb->id();
?>
<div id="workData<? if(isset($workID)) echo htmlspecialchars($workID) ?>">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th nowrap="nowrap">Ф.И.О.</th>
<? for($month = 1; $month <= 12; ++$month){ ?>
    <th nowrap="nowrap"><? if(isset($month)) echo htmlspecialchars($month) ?></th>
<? } ?>
</tr>
<?
$ddb2 = module('doc');
$search = array();
$search['template']	= 'person';
$search['type']		= 'article';
$search['prop'][':workParent'] = $workID;
$ddb2 -> open(doc2sql($search));
while($d2 = $ddb2->next()){
	$personID	= $ddb2->id();
?>
<tr>
    <td nowrap="nowrap"><? if(isset($d2["title"])) echo htmlspecialchars($d2["title"]) ?></td>
<? for($month = 1; $month <= 12; ++$month){
	@$val	= $w[$workID];
	@$val	= $val[$personID];
?>
    <td><input name="workData[<? if(isset($workID)) echo htmlspecialchars($workID) ?>][<? if(isset($personID)) echo htmlspecialchars($personID) ?>][<? if(isset($month)) echo htmlspecialchars($month) ?>]" type="text" class="input w100" value="<? if(isset($val["$month"])) echo htmlspecialchars($val["$month"]) ?>" /></td>
<? } ?>
</tr>
<? } ?>
</table>
</div>
<? } ?>
</div>

<script>
$(function() {
	$("#workTabs").tabs();
});
</script>
<? return '1-Показатели'; } ?>