<? function module_scriptTools(&$val, &$menu)
{
	$ini = getCacheValue('ini');
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <th nowrap="nowrap" width="50%">Стиль диалогов</th>
        <td >
<select name="settings[:][jQueryUI]" class="input w100">
<?
$jQuery		= getCacheValue('jQuery');
$ver		= $jQuery['jQueryUIVersion'];
$styleBase	= cacheRootPath."/script/$ver/themes";
@$thisValue	= $ini[':']['jQueryUI'];
if (!$thisValue) $thisValue = $jQuery['jQueryUIVersionTheme'];
foreach(getDirs($styleBase) as $name=>$path){
?>
  <option value="{$name}"{selected:$name==$thisValue}>{$name}</option>
<? } ?>
  </select>
          </td>
        </tr>
    </table>
<? } ?>