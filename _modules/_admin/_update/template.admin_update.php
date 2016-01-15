<?
//	+admin_update
function admin_update($val, $data)
{
	if (!access('read', 'update')) return;
	
	m('script:jq');
	m('script:ajaxLink');
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
	if (!access('read', 'update')) return;

	if (testValue('build'))
		cmsUpdate::setBuildFilter(getValue('build'));
		
	$check	= cmsUpdate::getServerInfo();
?>

<div>
<? if (cmsUpdate::getBuildFilter() == 'stable'){ ?>
<a href="{{url:admin_update_check=build:beta,stable}}" id="ajax">Проверять стабильные и тестовые версии</a>
<? }else{ ?>
<a href="{{url:admin_update_check=build:stable}}" id="ajax">Проверять стабильные версии</a>
<? } ?>
</div>


<? if (!$check){ ?>
<div class="message error">
	Нет соединения с сервером, проверте исходящее соединение на хостинге провайдера.
    <div><?= serverUpdateHost ?></div>
</div>
<?	return; } ?>

<h2>Версия CMS</h2>

<? if (cmsUpdate::checkVersion($check)){ ?>

Текущая версия CMS <b><?= cmsUpdate::getLocalVersion() ?></b>.
<span style="color:green">Обновления не требуется.</span>

{!$check[DEV_CMS_UPDATE_NOTE]|tag:blockquote}

<? }else{ ?>

Текущая версия CMS <b><?= cmsUpdate::getLocalVersion() ?></b>.
<span style="color:red">Обновление до <b>{$check[DEV_CMS_BUILD]}-{$check[DEV_CMS_VERSION]}</b></span>
<a href="{$check[DEV_CMS_UPDATE]}" class="cmsDownloadUpdateLink">загрузить обновление</a>
{!$check[DEV_CMS_UPDATE_NOTE]|tag:blockquote}

<? } ?>

<? } ?>

<?
//	+function admin_update_download
function admin_update_download()
{
	if (!access('write', 'update')) return;

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
	if (!access('write', 'update')) return;
	cmsUpdate::update();
} ?>

<?
//	+function module_server_update_get
function module_server_update_get()
{
	setTemplate('');
	echo json_encode(cmsUpdate::getServerUpdateInfo(getValue('build')));
}
?>
