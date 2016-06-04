<?
//	+function doc_docConfig
function doc_docConfig($ini)
{
	if (!hasAccessRole('developer')) return;
//	if (getValue('docRules')) doc_docConfig_update();
	$delete	= getValue('typeDelete');
	if ($delete){
		docConfig::deleteTemplate($delete);
	}
?>
<module:script:ajaxForm />
<module:page:title @="Настройки отображения видов страниц" />
<link rel="stylesheet" type="text/css" href="css/docType.css">

<table class="table adminDocType" width="100%" cellpadding="0" cellspacing="0">
<?
$rules	= docConfig::getTemplates();
foreach($rules as $type => $data){ ?>
<tr>
	<td width="100%">
        <h2><a id="ajax" href="{{url:admin_doctype=type:$type}}">Изменить шаблон</a> {$data[NameOne]}</h2>
        <p>{$type}</p>
        <blockquote>{$data[note]}</blockquote>
    </td>
    <td nowrap>
<? if ($data['type'] == 'internal'){ ?>
        <a href="{{url:admin_docconfig=typeDelete:$type}}" id="ajax">сбросить настройки</a>
<? }else{ ?>
        <a href="{{url:admin_docconfig=typeDelete:$type}}" id="ajax">удалить</a>
<? } ?>
    </td>
</tr>
<? } ?>
</table>

<p>
    <a href="{{url:admin_doctype}}" id="ajax" class="button">Добавить новый тип документа</a>
</p>
<? } ?>


<?
//	+function doc_toolsConfig
function doc_toolsConfig($db, $val, &$menu)
{
	if (!access('write', 'doc:')) return;
	if (!hasAccessRole('developer')) return;
	
	$menu["Типы документов#ajax"]	= getURL('admin_docConfig');
}
?>