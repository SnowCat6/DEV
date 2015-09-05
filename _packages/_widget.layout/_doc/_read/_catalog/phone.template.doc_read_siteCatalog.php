<?
function phone_doc_read_siteCatalog_beginCache($db, &$val, &$search)
{
	$options		= $search['options'];
	ob_start();
	$search			= module('doc:searchPanel:default2', $search);
	$search['page']	= getValue('page');
	module('display:searchPanel',  ob_get_clean());
	$search['options']	= $options;

	$s	= getValue('search');
	$search['prop'] = $s['prop'];
	$search['page']	= getValue('page');
	
	return hashData($search);
}

function phone_doc_read_siteCatalog($db, &$val, &$search)
{
	$p		= dbSeek($db, 6, array('search' => getValue('search')));
?>
<link rel="stylesheet" type="text/css" href="css/readCatalog.css">

<div class="documentHolder">
{{display:searchPanel}}
</div>

{!$p}
<? while($data = $db->next())
{
	$id		= $db->id();
	$link	= getURL($db->url());
	$note	= docNote($data);
?>
<div class="readCatalogItems">
	<div class="image">
        {{doc:titleImage:$id=clip:330x200;property.href:$link}}
    </div>
    <div class="content">
        <h2><a href="{!$link}" title="{$data[title]}">{$data[title]}</a></h2>
        <p>{{prop:read:plain=id:$id}}</p>
        <blockquote>{!$note}</blockquote>
    </div>
</div>
<? } ?>
{!$p}
<? } ?>
