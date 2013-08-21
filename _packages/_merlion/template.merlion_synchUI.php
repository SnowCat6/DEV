<? function merlion_synchUI($val)
{
	m("page:title", "Обновление товаров");
	$ini		= getCacheValue('ini');
	$merlion	= $ini[':merlion'];

	if (testValue('doSynchImages'))
	{
		$ini[':merlion']['synchPrice']	= (int)getValue('doSynchPrice');
		$ini[':merlion']['synchImages']	= (int)getValue('doSynchImages');
		$ini[':merlion']['synchYandex']	= (int)getValue('doSynchYandex');
		setIniValues($ini);
	}

	$synch		= new baseSynch(merlionFile);
	if (getValue('doSynchUnlock')) $synch->unlock();
	if (getValue('doSynchMerlion')) $synch->delete();

	if (testValue('ajax'))
	{
		setTemplate('ajaxResult');
		m('merlion:synch');

		$synch->read();
		merlionInfo($synch);

		return;
	}
	if (testValue('synch'))	m('merlion:synch');

	$ini		= getCacheValue('ini');
	$merlion	= $ini[':merlion'];
	$synch->read();

	m('script:jq');
	
	$bSynchPrice	= $merlion['synchPrice'];
	$bSynchImages	= $merlion['synchImages'];
	$bSynchYandex	= $merlion['synchYandex'];
?>
<form action="{{getURL:import_merlion_synch}}" method="post">
<input type="hidden" name="synch" value="1" />
<div>
<input  type="hidden" name="doSynchPrice" value="0" />
<label><input name="doSynchPrice" type="checkbox" value="1"<?= $bSynchPrice?' checked="checked"':''?>> Импортировать товары</label>
</div>
<div>
<input  type="hidden" name="doSynchYandex" value="0" />
<label><input name="doSynchYandex" type="checkbox" value="1"<?= $bSynchYandex?' checked="checked"':''?>> Создать Yandex XML</label>
<?
$yml = localHostPath.'/yandex.xml';
if (is_file($yml)){ ?> <a href="yandex.xml"><?= date('d.m.Y H:i', filemtime($yml))?></a><? } ?>
</div>
<div>
<input  type="hidden" name="doSynchImages" value="0" />
<label><input name="doSynchImages" type="checkbox" value="1"<?= $bSynchImages?' checked="checked"':''?>> Импортировать картинки</label>
</div>
<? if ($synch->data){ ?>
<p>
<label><input name="doSynchMerlion" type="checkbox" value="1"> Импортировать товары с начала</label><br />
</p>
<? } ?>
<? if ($synch->lockTimeout()){ ?>
<label><input name="doSynchUnlock" type="checkbox" value="1">Удалить триггер</label>
<? } ?>
</p>
<p>
<input type="submit" value="Обновить" class="button" id="reloadImportButton">
</p>
</form>

<div id="importProcess"><? merlionInfo($synch) ?></div>
<script>
//	Счетчик секунд до обновления
var lastImportUpdate = 0;
$(function(){
	updateImportData();
});
//	Загрузить через AJAX обновленные данные
function updateImportData()
{
	if (lastImportUpdate++ >= 5){
		$("#reloadImportButton").val("Обновляется");
		setTimeout(updateImportButton, 1000);
		try{
			$("#importProcess").load("{{getURL:import_merlion_synch=ajax}}&random=" + Math.random(), function(data)
			{
				$(document).trigger("jqReady");
				lastImportUpdate = 0;
				updateImportData();
			});
		}catch(e){
			lastImportUpdate = 0;
			updateImportData();
		}
	}else{
		$("#reloadImportButton").val("Обновить через " + (6 - lastImportUpdate) + " сек.");
		setTimeout(updateImportData, 1000);
	}
}
function updateImportButton(){
	if (lastImportUpdate < 5) return;
	$("#reloadImportButton").val("Обновляется (" + (lastImportUpdate - 5) + " / " + merlionTimeout + ") сек.");
	++lastImportUpdate;
	setTimeout(updateImportButton, 1000);
}
</script>
<? } ?>
