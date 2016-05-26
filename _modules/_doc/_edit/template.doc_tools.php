<? function doc_tools($db, $val, &$data)
{
	if (!access('write', 'doc:')) return;
?>
<link rel="stylesheet" type="text/css" href="css/adminDocTools.css">

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="adminDocTools">
  <tr>
    <th colspan="2" class="left">Разделы и каталоги</th>
  </tr>

<?
$types = getCacheValue('docTypes');
foreach($types as $docType => $names)
{
	list($type, $template) = explode(':', $docType);
	if ($type == 'article' || $type == 'product') continue;
	
	if (!access('add', "doc:$type")) continue;

	$name	= docTypeEx($type, $template, 1);
?>
  <tr>
    <td class="left">
        <a href="{{url:page_all_$type=template:$template}}" id="ajax" title="{$type}">{$name}</a>
    </td>
    <td class="right">
        <a href="{{url:page_add=type:$type;template:$template}}" id="ajax_edit">новый</a></td>
  </tr>
<? } ?>

  <tr>
    <th colspan="2" class="left">Документы и товары</th>
  </tr>

<?
foreach($types as $docType => $name)
{
	list($type, $template) = explode(':', $docType);
	if ($type != 'article' && $type != 'product') continue;
	
	if (!access('add', "doc:$type")) continue;
	$name = docTypeEx($type, $template, 1);
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
</table>

<p><a href="{{url:page_all}}" id="ajax">Список разделов и каталогов</a></p>
<p><a href="{{url:page_map}}">Карта сайта</a></p>
<? } ?>

<?
//	+function doc_toolsConfig
function doc_toolsConfig($db, $val, &$data)
{
	if (!access('write', 'doc:')) return;
	if (!hasAccessRole('developer')) return;
	
	$data["Документы#ajax"]	= getURL('admin_docConfig');
}
?>