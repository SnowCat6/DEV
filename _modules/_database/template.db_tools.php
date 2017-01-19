<?
// +function module_db_tools
function module_db_tools($val, &$menu)
{
	if (!hasAccessRole('developer')) return;
	
$gIni	= getGlobalCacheValue('ini');
$gDb	= $gIni[':db'];
if (!$gDb) $gDb = array();

$ini	= getCacheValue('ini');
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
}
?>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
  <tr>
    <th nowrap="nowrap" width="50%">URL сайта http://</th>
    <td nowrap="nowrap">
    <input type="text" name="settings[:][url]" class="input w100" value="{$ini[:][url]}" placeholder="{$_SERVER[HTTP_HOST]}">
    </td>
    </tr>
    <th nowrap="nowrap">База данных</th>
    <td>{!$db[host]}/{!$db[db]}/{!$db[prefix]}</td>
  </tr>
  <tr>
    <th nowrap="nowrap">Логин БД</th>
    <td>{!$db[login]}:{!$db[passw]}</td>
    </tr>
  <tr>
    <th nowrap="nowrap">&nbsp;</th>
    <td>&nbsp;</td>
  </tr>
  </table>
<? } ?>