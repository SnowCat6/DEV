<? function module_scriptTools(&$val, &$menu)
{
	$ini = getCacheValue('ini');
?>
<table border="0" cellpadding="0" cellspacing="0">
      <tr>
        <th nowrap="nowrap">Стиль диалогов</th>
        <td >
<select name="settings[:][jQueryUI]" class="input">
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