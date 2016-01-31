<?
function import_synch(&$val)
{
	$import	= new importBulk();
	$db		= $import->db();
	$ddb	= module('doc');
	
	$ini	= getCacheValue('ini');
	
	$import	= getValue('importSynch');
	if (is_array($import)){
		$ini[':import']	= $import;
		setIniValues($ini);
	}
	
	$import	= $ini[':import'];
	if (getValue('doImportSynch'))
	{
		//	Выполнить сопоставление товаров
//		m('import:commitSynch');
		//	Синхронизировать с базой сайта
//		doImportSynch($db, $ddb, $import);
		importSynch::doSynch($import);
	}
	
	m('import:commitSynch');
	
	$updates= array();
	$table	= $db->table();
	$db->exec("SELECT count(*) AS cnt, `doc_type`, IF (`pass` = 0, 'raw', IF (`updated` = 1, 'skip', IF (`doc_id` = 0, 'new', 'updated'))) AS isAdd FROM $table WHERE `ignore`=0  GROUP BY `doc_type`, `isAdd`");
	while($data =  $db->next()){
		$updates[$data['doc_type']][$data['isAdd']]		= $data['cnt'];
	}
	
	$reload	= 0;
	$action	= 'Обновить сайт';
	$synch	= importCommit::getSynch();
	$synch->read();

	if ($synch->getValue('synchStatus') != NULL)
	{
		if ($val = $synch->lockTimeout())
		{
			$max	= $synch->lockMaxTimeout() - $val;
			$action	= "Продолжить через $max сек.";
			$reload	= $max;
		}else
		if ($val = $synch->getValue('synchStatus'))
		{
			$reload	= 5;
			switch($val)
			{
			case 'complete':
				$action	= 'Завершено';
				$reload	= 0;
				break;
			default:
				$action	= "Продолжить обработку";
			}
		}
	}
?>
{{ajax:template=ajaxResult}}
<script src="script/jqImportCommit.js"></script>

<form action="{{url:#}}" method="post">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%" valign="top"><table border="0" cellpadding="2" cellspacing="0">
      <tr>
        <td valign="top" nowrap>Новых каталогов</td>
        <td align="right" valign="top">{$updates[catalog][new]}</td>
        <td valign="top" nowrap><label>
          <input type="hidden" name="importSynch[noAddCatalog]" value="">
          <input type="checkbox" name="importSynch[noAddCatalog]" {checked:$import[noAddCatalog]}>
          не добавлять </label></td>
        </tr>
      <tr>
        <td valign="top" nowrap>Обновленных каталогов</td>
        <td align="right" valign="top">{$updates[catalog][updated]}</td>
        <td valign="top" nowrap><label>
          <input type="hidden" name="importSynch[noUpdateCatalog]" value="">
          <input type="checkbox" name="importSynch[noUpdateCatalog]" {checked:$import[noUpdateCatalog]}>
          не обновлять </label></td>
        </tr>
      <tr>
        <td valign="top" nowrap>Не измененных</td>
        <td align="right" valign="top">{$updates[catalog][skip]}</td>
        <td valign="top" nowrap></td>
      </tr>
      <tr>
        <td valign="top" nowrap>Не обработанных</td>
        <td align="right" valign="top">{$updates[catalog][raw]}</td>
        <td valign="top" nowrap>&nbsp;</td>
      </tr>
      <tr>
        <td valign="top" nowrap>&nbsp;</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" nowrap>&nbsp;</td>
        </tr>
      <tr>
        <td valign="top" nowrap>Новых товаров</td>
        <td align="right" valign="top">{$updates[product][new]}</td>
        <td valign="top" nowrap><label>
          <input type="hidden" name="importSynch[noAddProduct]" value="">
          <input type="checkbox" name="importSynch[noAddProduct]"  {checked:$import[noAddProduct]}>
          не добавлять </label></td>
        </tr>
      <tr>
        <td valign="top" nowrap>Обновленных товаров</td>
        <td align="right" valign="top">{$updates[product][updated]}</td>
        <td valign="top" nowrap><label>
          <input type="hidden" name="importSynch[noUpdateProduct]" value="">
          <input type="checkbox" name="importSynch[noUpdateProduct]" {checked:$import[noUpdateProduct]}>
          не  обновлять </label></td>
        </tr>
      <tr>
        <td valign="top" nowrap>Не измененных</td>
        <td align="right" valign="top">{$updates[product][skip]}</td>
        <td valign="top" nowrap></td>
      </tr>
      <tr>
        <td valign="top" nowrap>Удаленных</td>
        <td align="right" valign="top"><?= count(importCommit::getDeleted())?></td>
        <td valign="top" nowrap></td>
      </tr>
      <tr>
        <td valign="top" nowrap>Не обработанных</td>
        <td align="right" valign="top">{$updates[product][raw]}</td>
        <td valign="top" nowrap></td>
      </tr>
    </table></td>
    <td width="50%" valign="top"><table border="0" cellpadding="2" cellspacing="0">
      <tr>
        <td valign="top" nowrap>Автоматически помещать в карту сайта<br>
          корневые каталоги</td>
        <td>&nbsp;</td>
        <td><input type="hidden" name="importSynch[addToMap]" value="">
          <input type="checkbox" name="importSynch[addToMap]" {checked:$import[addToMap]}></td>
      </tr>
      <tr>
        <td valign="top" nowrap>Заменять свойства товаров</td>
        <td>&nbsp;</td>
        <td>
<input type="hidden" name="importSynch[replaceProperty]" value="">
<input type="checkbox" name="importSynch[replaceProperty]" {checked:$import[replaceProperty]}>
          </td>
      </tr>
      <tr>
        <td valign="top" nowrap>Заменять названия товаров</td>
        <td>&nbsp;</td>
        <td>
<input type="hidden" name="importSynch[replaceName]" value="">
<input type="checkbox" name="importSynch[replaceName]" {checked:$import[replaceName]}>
        </td>
      </tr>
    </table></td>
  </tr>
</table>
<p>
  <input type="submit" value="{$action}" name="doImportSynch" class="button" reload="{$reload}" />
</p>
</form>
<? } ?>
