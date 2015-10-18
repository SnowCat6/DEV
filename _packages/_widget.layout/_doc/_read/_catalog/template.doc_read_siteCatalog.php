<widget:siteCatalog
    category= 'Документы'
    name	= 'Каталог документов с поиском'
    note	= 'Каталог документов с пнелью поиска'
    cap		= 'documents'
    exec	= 'doc:read:siteCatalog=[@data.selector];options:[data]'
>
<cfg:data.selector			name = 'Фильтр документов' type = 'doc_filter' default = '@!place:[id]' />
<cfg:data.style.background	name = 'Цвет фотна' type = 'color' />
<cfg:data.width				name = 'Ширина' default = '1100' />

<?
function doc_read_siteCatalog_before($db, &$val, &$search)
{
	$options		= $search['options'];
	ob_start();
	$search			= module('doc:searchPanel:default2', $search);
	$search['page']	= getValue('page');
	module('display:searchPanel',  ob_get_clean());
	$search['options']	= $options;
}
function doc_read_siteCatalog($db, &$val, &$search)
{
	if (!$db->rows()) return $search;
	
	$rows	= 3;
	$max	= $db->rows() - 1;
	$p		= dbSeek($db, 3*$rows+1, array('search' => getValue('search')));
	
	$cols	= array('left', 'center', 'right');
	$width	= $search['options']['width'];
	
	$panelWidth	= floor($width / 3);
	
	$titleWidth	= $width - $panelWidth;
	$titleHeight= floor($titleWidth *2 / 4);
	$titleSize	= $titleWidth . 'x' . $titleHeight;

	$elmWidth	= floor(($width - 10) / 3);
	$elmHeight	= floor($elmWidth * 2 / 3);
	$elmSize	= $elmWidth . 'x' . $elmHeight;

	$panelWidth	-= 40;
?>
<link rel="stylesheet" type="text/css" href="../../../../_templates/baseStyle.css">
<link rel="stylesheet" type="text/css" href="css/readCatalog.css">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="{$titleWidth}">
<?
	$data	= $db->next();
	$id		= $db->id();
	$link	= getURL($db->url());
	$menu	= doc_menu($id, $data);
	$note	= m("doc:editable:$id", array('default' => docNote($data)));
?>
<div class="readCatalog">
	<module:doc:titleImage + = ":$id"
    	clip	= "$titleSize"
        hasAdmin= "top"
        adminMenu		= "$menu"
        property.href	= "$link"
    />
    <div class="info">
        <h2><a href="{!$link}" title="{$data[title]}">{$data[title]}</a></h2>
        <div><module:prop:read:plain id = "$id" /></div>
        {!$note|tag:blockquote}
    </div>
</div>
    </td>
	<td class="readCatalogInfo" {!$search[options][style]|style}>
<div style="width:{$panelWidth}px; overflow:hidden">
	{{display:searchPanel}}
</div>
    </td>
</tr>
</table>

<? if ($db->rows() == 1) return $search; ?>

<table border="0" cellpadding="0" cellspacing="0" class="readCatalogItems" width="{$width}">
<each source="$db" rows="3">
    <tr>
<eachrow>
        <td>
<? if ($data){ ?>
<?	$id		= $data->itemId();
	$link	= $data->itemURL();
	$menu	= doc_menu($id, $data);
?>
<div class="image">
	<module:doc:titleImage + = ":$id"
    	clip	= "$elmSize"
        hasAdmin= "top"
        adminMenu		= "$menu"
        property.href 	= "$link"
    />
</div>
<? } ?>
		</td>
</eachrow>
</tr>
<tr>
<eachrow>
        <td>
<? if ($data){ ?>
<?	$id		= $data->itemId();
	$link	= $data->itemURL();
	$note	= m("doc:editable:$id", array('default' => docNote($data)));
	$menu	= doc_menu($id, $data);
?>
<div class="item" style="width:{$elmWidth}px">
    <h2><a href="{!$link}" title="{$data[title]}">{$data[title]}</a></h2>
	<p><module:prop:read:plain id="$id" /></p>
    {!$note|tag:blockquote}
<? } ?>
</div>
		</td>
</eachrow>
    </tr>
</each>
</table>

{!$p}

<? return $search; } ?>
</widget:siteCatalog>
