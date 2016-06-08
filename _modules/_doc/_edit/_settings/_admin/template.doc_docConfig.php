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
<tr>
  <th nowrap>Тип</th>
  <th>Название типа документа</th>
  <th>Краткое описание</th>
  <th nowrap align="right">&nbsp;</th>
</tr>
<?
$rules	= docConfig::getTemplates();
foreach($rules as $type => $data){ ?>
<tr>
    <td nowrap width="5%"><a id="ajax" href="{{url:admin_doctype=type:$type}}">{$type}</a></td>
    <td>{$data[NameOne]}</td>
    <td>{$data[note]}</td>
    <td nowrap align="right">
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