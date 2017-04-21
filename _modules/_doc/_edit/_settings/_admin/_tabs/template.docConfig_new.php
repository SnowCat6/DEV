<?
//	+function docConfig_new_update
function docConfig_new_update(&$data)
{
	if (!hasAccessRole('developer')) return;
	$data['allowAddType']	= getValue('allowAddType');
}
?>
<?
//	+function docConfig_new
function docConfig_new($data)
{
	if (!hasAccessRole('developer')) return;
	$allowAddType	= $data['allowAddType'];
?>

<h3>Разрешить добавлять новые документы</h3>
<table cellpadding="2" cellspacing="0"><tr>
<?
$class	= docConfig::getTypeFilter();
foreach($class as $name=>$filter){ ?>
<td valign="top" width="50%">
<b>{$name}</b>
<?
$types	= docConfig::getTemplates($filter);
foreach($types as $type=>$data)
{
	$bCheck	= $allowAddType[$type];
?>
<div>
    <label>
        <input type="checkbox" name="allowAddType[{$type}]" value="{$type}" {checked:$bCheck} />
        {$data[NameOne]} ({$type})
    </label>
</div>
<? } ?>
</td>
<? } ?>
</tr></table>
<? return 'Новые документы'; } ?>