<widget:specialLayout
	category= "Документы"
	name	= "Баннер с текстом и другие документы"
    note	= "Документы с основной картинкой и списком других документов"
    cap		= "documents"
	exec	= "widget:specialLayout:[id]=[@data.selector];options:[data]"
>
<cfg:data.selector		name = 'Фильтр документов' type = 'doc_filter' default = '@!place:[id]' />
<cfg:data.elmSize		name = 'Размер изображения (ШxВ)' default = '1100x420' />
<cfg:data.tablSelector	name = 'Фильтр вкладок' type = 'doc_filter' default = '@!place:[id]' />

<wbody>

<module:doc:read:specialLayout +=":$id" @="$data" />

</wbody>

</widget:specialLayout>

<?
//	+function doc_read_specialLayout
function doc_read_specialLayout($db, $widgetID, $search)
{
	$options	= $search['options'];
	$elmSize	= $options['elmSize'];
	list($w, $h)= explode('x', $elmSize);
	$index		= 0;
?>
<link rel="stylesheet" type="text/css" href="css/specialLayout.css">
<link rel="stylesheet" type="text/css" href="../_catalog/css/readCatalog.css">
<?
$data = $db->next();
if ($data){
	$id		= $db->id();
	$url	= getURL($db->url());
	$menu	= doc_menu($id, $data);
?>
<div class="specialLayoutBanner">
    <div class="image">
           	<module:doc:titleImage +=":$id"
            	clip		="$elmSize"
                adminMenu	="$menu"
                property.href 	="$url"
                property.title 	="$data[title]"
              />
    </div>
</div>
<? } ?>

<? if ($db->rows() < 2) return $search; ?>

<div class="specialLayoutPage clearfix">
<div class="specialLayoutShadow"></div>

<div class="specialLayoutMenu menu inline">
	<module:doc:read:menu @!place="$widgetID"/>
</div>

<div class="specialLayoutInfo">
	<module:read +=":$options[folder]" default="@">
		<h2>Все специальные предложения</h2>
    </module:read>
</div>

<div class="specialLayoutItems readCatalogItems clearfix">
<each source="$db">

<?
	$id		= $db->id();
	$url	= getURL($db->url());
	$menu	= doc_menu($id, $data);
	$note	= docNote($data);
	$ix		= ($index++) % 3;
?>

<div class="item" style="width:330px" id="item_{$ix}">
	<div class="image">
        <module:doc:titleImage +=":$id"
        	clip="330x200"
            property.href="$url"
            adminMenu="$menu"
            />
    </div>
    <div class="content">
        <h2><a href="{$url}" title="{$data[title]}">{$data[title]}</a></h2>
        <p>{{prop:read:plain=id:$id}}</p>
        <blockquote>{!$note}</blockquote>
    </div>
</div>

</each>
</div>

</div>

<? return $search; } ?>

<?
//	+function phone_doc_read_specialLayout
function phone_doc_read_specialLayout($db, $widgetID, $search)
{
	$options	= $search['options'];
	$elmSize	= $options['elmSize'];
	list($w, $h)= explode('x', $elmSize);
	$index		= 0;
?>
<link rel="stylesheet" type="text/css" href="css/specialLayout.css">
<link rel="stylesheet" type="text/css" href="../_catalog/css/readCatalog.css">

<div class="clearfix">

<div class="">
	<module:doc:read:menu @!place="$widgetID"/>
</div>

<div class="">
	<module:read +=":$options[folder]" default="@">
		<h2>Все специальные предложения</h2>
    </module:read>
</div>

<div class="specialLayoutItems readCatalogItems clearfix">
<each source="$db">

<?
	$id		= $db->id();
	$url	= getURL($db->url());
	$menu	= doc_menu($id, $data);
	$note	= docNote($data);
	$ix		= ($index++) % 2;
?>

<div class="item" style="width:330px" id="item_{$ix}">
	<div class="image">
        <module:doc:titleImage +=":$id"
        	clip="330x200"
            property.href="$url"
            adminMenu="$menu"
            />
    </div>
    <div class="content">
        <h2><a href="{$url}" title="{$data[title]}">{$data[title]}</a></h2>
        <p>{{prop:read:plain=id:$id}}</p>
        <blockquote>{!$note}</blockquote>
    </div>
</div>

</each>
</div>

</div>

<? return $search; } ?>
