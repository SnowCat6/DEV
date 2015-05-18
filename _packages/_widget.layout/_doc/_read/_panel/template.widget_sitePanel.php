<widget:sitePanel
	category= "Документы"
	name	= "Документы в плитке"
    cap		= "documents"
    exec	= "doc:read:sitePanel=[@data.selector];options:[data]"
>
<cfg:data.selector 		name = "Выбор документов" default="@!place:[id]" />
<cfg:data.style.width 	name = "Ширина" default="1100" />
<?
//	+function doc_read_sitePanel
function doc_read_sitePanel($db, $val, &$search)
{
	if (!$db->rows()) return $search;
	
	$data	= array();
	$margin	= 5;
	$height	= 415;
	$width	= (int)$search['options']['width'];
	if (!$width) $width = 1100;
	
	$w1		= $width;
	$w3		= floor(($width - $margin*2) / 3);
	$w2		= $w3*2 + $margin;
	
	$data['width']	= $width;
	$data['w1']		= $w1;
	$data['w2']		= $w2;
	$data['w3']		= $w3;
	
	$data['h1']		= $height;
	$data['h2']		= $height - 195;
	$data['h3']		= floor(($height - $margin) / 2);
?>
<link rel="stylesheet" type="text/css" href="css/sitePanel.css">
<?
	$rows	= $db->rows() - 1;
	while($rows >= 0){
?>
<div class="sitePanel" {!$search[options][style]|style}>
<?
		switch($row = $rows % 4){
			case 0: read_sitePanel1($db, $data); break;
			case 1: read_sitePanel2($db, $data); break;
			case 2: read_sitePanel3($db, $data); break;
			default:read_sitePanel4($db, $data); break;
		}
?>
</div>
<?
		$rows -= $row + 1;
	}
?>
<? return $search; } ?>

<? /********************************************/
function read_sitePanel1($db, $data){
?>
<div class="slot big" style="width: {$data[w1]}px">
    <? sitePanelInfo($db, $data['w1'] . 'x' . $data['h1'])?>
</div>
<? } ?>
<? /*******************************************/
function read_sitePanel2($db, $data){?>
<div class="slot big" style="width: {$data[w2]}px">
    <? sitePanelInfo($db, $data['w2'] . 'x' . $data['h1'])?>
</div>
<div class="slot" style="width: {$data[w3]}px">
    <? sitePanelInfo($db, $data['w3'] . 'x' . $data['h2'])?>
</div>
<? } ?>
<? /*********************************************/
function read_sitePanel3($db, $data){?>
<div class="slot" style="width: {$data[w3]}px">
    <? sitePanelInfo($db, $data['w3'] . 'x' . $data['h2'])?>
</div>
<div class="slot" style="width: {$data[w3]}px">
    <? sitePanelInfo($db, $data['w3'] . 'x' . $data['h2'])?>
</div>
<div class="slot" style="width: {$data[w3]}px">
    <? sitePanelInfo($db, $data['w3'] . 'x' . $data['h2'])?>
</div>
<? } ?>
<? /****************************************************/
function read_sitePanel4($db, $data){?>
<div class="slot" style="width: {$data[w3]}px">
    <? sitePanelInfo($db, $data['w3'] . 'x' . $data['h2'])?>
</div>
<div class="slot" style="width: {$data[w3]}px">
    <? sitePanelInfo($db, $data['w3'] . 'x' . $data['h2'])?>
</div>
<div class="slot small" id="first" style="width: {$data[w3]}px">
    <? sitePanelInfo($db, $data['w3'] . 'x' . $data['h3'])?>
</div>
<div class="slot small" style="width: {$data[w3]}px">
    <? sitePanelInfo($db, $data['w3'] . 'x' . $data['h3'])?>
</div>
<? } ?>
<?
/************************************************/
function sitePanelAccept($db){
	$link	= getURL($db->url());
?>
<div class="sitePanelAccept">
    <a href="{$link}" class="bg left">Узнать подробности</a>
    <a href="{$link}" class="bg3 right">Хочу участвовать!</a>
</div>
<? } ?>
<? /***********************************************/
function sitePanelInfo($db, $size)
{
	$data	= $db->next();
	$id		= $db->id();
	$link	= getURL($db->url());
	$note	= docNote($data);
	$menu	= doc_menu($id, $data, false);
	list($w, $h) = explode('x', $size);
?>
<div itemscope itemtype="http://schema.org/Event">
    <div class="image" style="min-height:{$h}px">
        {{doc:titleImage:$id=clip:$size;hasAdmin:true;adminMenu:$menu;property.itemprop:image;property.href:$link}}
    </div>
    <div class="holder">
{beginAdmin}
        <div class="info2 bg2">
            <h2><a href="{$link}" title="{$data[title]}" itemprop="name">{$data[title]}</a></h2>
            <p><a href="{$link}" itemprop="description">{{doc:editable:$id=default:$note}}</a></p>
        </div>
{endAdmin}
        <? sitePanelAccept($db) ?>
    </div>
</div>
<? } ?>
</widget:sitePanel>
