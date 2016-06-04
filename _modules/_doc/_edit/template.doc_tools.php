<? function doc_tools($db, $val, &$menu)
{
	if (!access('write', 'doc:')) return;
	$types	= array(
		'Разделы и каталоги'		=> '(page|catalog):',
		'Документы и товары'	=> '(article|product):',
	);
?>
<link rel="stylesheet" type="text/css" href="css/adminDocTools.css">

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="adminDocTools">
<? foreach($types as $name=>$filter)
{
	$rules	= docConfig::getTemplates($filter);
	if (!$rules) continue;
?>
<tr>
    <th colspan="2" class="left">{$name}</th>
</tr>
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
<? } ?>
</table>

<p><a href="{{url:page_all}}" id="ajax">Список разделов и каталогов</a></p>
<p><a href="{{url:page_map}}">Карта сайта</a></p>
<? } ?>

