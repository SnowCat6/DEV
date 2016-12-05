<? function doc_tools($db, $val, &$menu)
{
	if (!access('write', 'doc:')) return;
	$types	= array(
		'Разделы'	=> '(page|catalog):',
		'Документы'	=> '(article|product):',
	);
?>
<module:script:jq />
<link rel="stylesheet" type="text/css" href="css/adminDocTools.css">
<script src="script/adminDocTools.js"></script>

<div class="adminDocToolsHolder">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<?
$bFirst = true;
foreach($types as $name => $filter)
{
	$rules	= docConfig::getTemplates($filter);
	if (!$rules) continue;
	$type	= md5($filter);
	$class	= $bFirst?'selected':'';
	$bFirst	= false;
?>
	<td>
        <h2 class="ui-state-default {$class}">
            <a href="#{$type}"><span>{$name}</span></a>
        </h2>
    </td>
<? } ?>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="adminDocTools">
<?
$bFirst = true;
foreach($types as $name => $filter)
{
	$rules	= docConfig::getTemplates($filter);
	if (!$rules) continue;
	$type	= md5($filter);
	$class	= $bFirst?'selected':'';
	$bFirst	= false;
?>
<tbody id="{$type}" class="adminDocList {$class}">
<?
foreach($rules as $docType => $data)
{
	list($type, $template) = explode(':', $docType, 2);
	if (!access('add', "doc:$type")) continue;
	$name	= $data['NameOther'];
?>
<tr>
    <td class="left">
        <a href="{{url:page_all_$type=template:$template}}" id="ajax" title="{$type}">{$name}</a>
    </td>
    <td class="right">
        <a href="{{url:page_add=type:$type;template:$template}}" id="ajax_edit">новый</a>
    </td>
</tr>
<? } ?>
</tbody>
<? } ?>
</table>
</div>
<p><a href="{{url:page_all}}" id="ajax">Список разделов и каталогов</a></p>
<p><a href="{{url:page_map}}">Карта сайта</a></p>
<? } ?>

