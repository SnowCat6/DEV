<? function admin_update()
{
	m('script:jq');
?>
<script type="text/javascript" src="script/adminUpdate.js"></script>
{{page:title=Обновление сайта}}
<div class="adminUpdateMessage">
    <div class="message">
        <p>Выполняется запрос обновления...</p>
    </div>
</div>
<? } ?>

<?
//	+function admin_update_check
function admin_update_check()
{
	setTemplate('');

	$thisCMSversion	= cmsUpdate::getLocalVersion();
	$check			= cmsUpdate::getServerInfo();
?>

<? if (!$check){ ?>
<div class="message error">
	Нет соединения с сервером
    <p><?= serverUpdateHost ?></p>
</div>
<?	return; } ?>

<h2>Версия CMS</h2>

<? if (version_compare($thisCMSversion, $check['DEV_CMS_VERSION']) >= 0){ ?>

Текущая версия CMS <b>{$thisCMSversion}</b>.
<span style="color:green">Обновления не требуется.</span>

<? }else{ ?>

Текущая версия CMS <b>{$thisCMSversion}</b>.
<span style="color:red">Обновление до <b>{$check[DEV_CMS_BUILD]}-{$check[DEV_CMS_VERSION]}</b></span>

<?
if (basename(cmsUpdate::getLocalFileUpdate()) == basename($check['DEV_CMS_UPDATE'])){ ?>
    <a href="#" class="cmsUpdateLink">обновить систему</a>
<? }else{ ?>
    <a href="{$check[DEV_CMS_UPDATE]}" class="cmsDownloadUpdateLink">загрузить обновление</a>
<? } ?>

<? } ?>

<? } ?>

<?
//	+function admin_update_download
function admin_update_download()
{
	setTemplate('');

	$updateFile	= cmsUpdate::getServerFileUpdate();
	$fileSize	= round(filesize($updateFile) / 1024, 2);
	$fileName	= basename($updateFile);
?>
<h2>Обновление загружено и готово к установке</h2>
Загружен файл <b>{$updateFile}</b> размером  <b>{$fileSize}</b> Mb.

<p><a href="#" class="button cmsUpdateLink">обновить систему</a></p>
<? } ?>

<?
//	+function admin_update_install
function admin_update_install()
{
	setTemplate('');
	
	$updateFile	= cmsUpdate::getLocalFileUpdate();
	cmsUpdate::update($updateFile);
} ?>

<?
//	+function module_server_update_get
function module_server_update_get()
{
	setTemplate('');
	
	echo json_encode(cmsUpdate::getServerUpdateInfo(getValue('build')));
}
?>
