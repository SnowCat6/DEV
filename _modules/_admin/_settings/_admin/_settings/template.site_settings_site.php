<? function site_settings_site($ini)
{
	$settings	= array();
	$settings['Использовать кеш']	= array(
		'name'	=> 'settings[:][useCache]',
		'value'	=> '1',
		'checked'	=> $ini[':']['useCache']
	);
	$settings['Пооверять изменения файлов']	= array(
		'name'	=> 'settings[:][checkCompileFiles]',
		'value'	=> '1',
		'checked'	=> $ini[':']['checkCompileFiles']
	);
	$settings['Использовать сжатие страниц']	= array(
		'name'	=> 'settings[:][compress]',
		'value'	=> 'gzip',
		'checked'	=> $ini[':']['compress']=='gzip'
	);
	$settings['Объеденять CSS файлы']	= array(
		'name'	=> 'settings[:][unionCSS]',
		'value'	=> 'yes',
		'checked'	=> $ini[':']['unionCSS']=='yes'
	);
	$settings['Объеденять JavaScript файлы']	= array(
		'name'	=> 'settings[:][unionJScript]',
		'value'	=> 'yes',
		'checked'	=> $ini[':']['unionJScript']=='yes'
	);
	$settings['Разрешить мобильны вид']	= array(
		'name'	=> 'settings[:][mobileView]',
		'value'	=> 'yes',
		'checked'	=> $ini[':']['mobileView']=='yes'
	);
	$settings['Оптимизировать PHP код']	= array(
		'name'	=> 'settings[:][optimizePHP]',
		'value'	=> 'yes',
		'checked'	=> $ini[':']['optimizePHP']=='yes'
	);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
    <tr>
      <td width="33%" valign="top">{{admin:settingsMenu:admin.settings.site=$settings}}</td>
      <td width="33%" valign="top">
<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr>
    <th nowrap="nowrap">URL сайта</th>
    <td nowrap="nowrap">httP://<input type="text" name="settings[:][url]" class="input" value="{$ini[:][url]}"></td>
    </tr>
<tr>
  <th nowrap="nowrap">Вы на сайте</th>
  <td nowrap="nowrap"><b>http://<?= $_SERVER['HTTP_HOST']?></b></td>
  </tr>
<tr>
  <th nowrap="nowrap">
  <?
$gIni	= getGlobalCacheValue('ini');
$gDb	= $gIni[':db'];
if (!$gDb) $gDb = array();

$db		= $ini[':db'];
if (!$db) $db = array();

$names	= explode(',', 'host,db,prefix,login,passw');
foreach($names as $name)
{
	$val	= htmlspecialchars($db[$name]);
	if ($name == 'passw'){
		if (access('write', 'admin:global')){
			if (!$val) $val = '<i>blank password</i>';
		}else $val = '***';
	}else
	if ($name == 'prefix'){
		$d		= new dbRow();
		$val	= $d->dbLink->dbTablePrefix();
	}
	if ($name == 'db'){
		$d		= new dbRow();
		$val	= $d->dbLink->dbName();
	}
	
	if ($val){
		$val = "$val";
	}else{
		$val	= htmlspecialchars($gDb[$name]);
		if ($val) $val = "<span style=\"color:red\">$val</span>";
	}
	$db[$name]	= $val;
} ?>База данных</th>
  <td>{!$db[host]}/{!$db[db]}</td>
  </tr>
<tr>
  <th nowrap="nowrap">Логин БД</th>
  <td>{!$db[login]}:{!$db[passw]}</td>
  </tr>
<tr>
  <th nowrap="nowrap">Префикс БД</th>
  <td>{!$db[prefix]}</td>
  </tr>
<tr>
  <th nowrap="nowrap">&nbsp;</th>
  <td>&nbsp;</td>
</tr>
</table>
      </td>
      <td width="33%" align="right" valign="top">
{{admin:menu:admin.tools.siteTools}}
      </td>
    </tr>
  </tbody>
</table>
<? return '1-Настройки сайта'; } ?>