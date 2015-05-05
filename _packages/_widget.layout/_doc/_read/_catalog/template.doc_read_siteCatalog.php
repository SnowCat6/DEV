<widget:siteCatalog
    category= 'Документы'
    name	= 'Каталог документов с поиском'
    note	= 'Каталог документов с пнелью поиска'
    cap		= 'documents'
    exec	= 'doc:read:siteCatalog=[@data.selector];options.style:[data.style]'
>
<cfg:data.selector			name = 'Фильтр документов' type = 'doc_filter' default = '@!place:[id]' />
<cfg:data.style.background	name = 'Цвет фотна' type = 'color' />

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
?>
<link rel="stylesheet" type="text/css" href="../../../../_templates/baseStyle.css">
<link rel="stylesheet" type="text/css" href="css/readCatalog.css">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td>
<?
	$data	= $db->next();
	$id		= $db->id();
	$link	= getURL($db->url());
	$note	= docNote($data);
	$note	= m("doc:editable:$id", array('default' => $note));
	$menu	= doc_menu($id, $data);
?>
<div class="readCatalog">
	{{doc:titleImage:$id=clip:722x360;property.href:$link;hasAdmin:top;adminMenu:$menu}}
    <div class="info">
        <h2><a href="{!$link}" title="{$data[title]}">{$data[title]}</a></h2>
        <div>{{prop:read:plain=id:$id}}</div>
<? if ($note){ ?>
        <blockquote>{!$note}</blockquote>
<? } ?>
    </div>
</div>
    </td>
	<td class="readCatalogInfo" {!$search[options][style]}>
        {{display:searchPanel}}
    </td>
</tr>
</table>

<? if ($db->rows() == 1) return $search; ?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="readCatalogItems">
<? while($rows-- && ($data = $db->next())){ ?>
    <tr>
<? foreach($cols as $col => $class)
{
	if ($col) $data = $db->next();
	if (!$data){
		echo "<td class=\"$class\"></td>";
		continue;
	}
	
	$id		= $db->id();
	$link	= getURL($db->url());
	$note	= docNote($data);
	$menu	= doc_menu($id, $data);
?>
        <td class="{$class}">
<div class="item">
    {{doc:titleImage:$id=clip:330x200;hasAdmin:true;adminMenu:$menu;property.href:$link}}
    <h2><a href="{!$link}" title="{$data[title]}">{$data[title]}</a></h2>
	<p>{{prop:read:plain=id:$id}}</p>
<? if ($note){ ?>
    <blockquote>{!$note}</blockquote>
<? }// if note ?>
</div>
		</td>
<? }// for ?>
    </tr>
<? } // while ?>
</table>

{!$p}

<? return $search; } ?>
</widget:siteCatalog>
