<? function module_advBanner(&$val)
{
	m('script:advBanner');
	m('scriptLoad','script/advBanner.js');
	m('styleLoad', 'css/advBanner.css');
	if (!$val) $val = "adv";
	
	$folder	= images."/advImage";
	$data	= readData("$folder/adv.bin");
	$bAdmin	= hasAccessRole('admin,writer,developer');
	$bFirst	= true;
?>
    <div class="advBackground">
<?	for($ix=0; $ix<10; ++$ix){
	if (showAdvBanner("$val-$ix", $bFirst, $bAdmin, $data)){
		$bFirst = false;
	}
}?>
    </div>
    <div class="advSeek"></div>
<? } ?>
<? function script_advBanner(&$val){
	m('script:jq');
	$menu	= '';
	if (hasAccessRole('admin,writer,developer')){
		m('script:ajaxLink');
		$menu = '<div><a href="#edit" id="ajax_edit">Редактировать</a></div>';
	}
?>
<script>
var advAdmin = '{!$menu}';
</script>
<? } ?>
<? function showAdvBanner($name, $bCurrent, $bAdmin, &$data)
{
	$doc	= $data[$name];
	if (!$bAdmin && $doc['show']!='yes') return;
	
	$current	= $bCurrent?' current':'';
	$titleImage	= $doc['titleImage'];
	if ($titleImage){
		$titleImage	= imagePath2local(images."/advImage/$name/$titleImage");
		$titleImage = "style=\"background-image: url($titleImage)\"";
	}
?>
    <div class="content{$current}" rel="{$name}" {!$titleImage}>
    	<div class="advContent"><?
if (beginCache("advBanner$name"))
{
	$document	= $doc['document'];
	event('document.compile', $val);
	echo $document;
	endCache();
}
		?></div>
    </div>
<? return true; } ?>