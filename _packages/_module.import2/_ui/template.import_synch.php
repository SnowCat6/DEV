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
	if (getValue('doImportSynch')){
		doImportSynch($db, $ddb, $import);
	}
	
	$updates= array();
	$table	= $db->table();
	$db->exec("SELECT count(*) AS cnt, `doc_type`, `doc_id` = 0 AS isAdd FROM $table WHERE `ignore`=0 AND `updated`=0 GROUP BY `doc_type`, `isAdd`");
	while($data =  $db->next()){
		$updates[$data['doc_type']][$data['isAdd']]	= $data['cnt'];
	}
?>
{{ajax:template=ajaxResult}}
<form action="{{url:#}}" method="post">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top"><table border="0" cellpadding="2" cellspacing="0">
      <tr>
        <td valign="top" nowrap>Новых каталогов</td>
        <td align="right" valign="top"><?= (int)$updates['catalog'][1]?></td>
        <td valign="top" nowrap><label>
          <input type="hidden" name="importSynch[noAddCatalog]" value="">
          <input type="checkbox" name="importSynch[noAddCatalog]" {checked:$import[noAddCatalog]}>
          не добавлять </label></td>
        </tr>
      <tr>
        <td valign="top" nowrap>Обновленных каталогов</td>
        <td align="right" valign="top"><?= (int)$updates['catalog'][0]?></td>
        <td valign="top" nowrap><label>
          <input type="hidden" name="importSynch[noUpdateCatalog]" value="">
          <input type="checkbox" name="importSynch[noUpdateCatalog]" {checked:$import[noUpdateCatalog]}>
          не обновлять </label></td>
        </tr>
      <tr>
        <td valign="top" nowrap>&nbsp;</td>
        <td valign="top">&nbsp;</td>
        <td valign="top" nowrap>&nbsp;</td>
        </tr>
      <tr>
        <td valign="top" nowrap>Новых товаров</td>
        <td align="right" valign="top"><?= (int)$updates['product'][1]?></td>
        <td valign="top" nowrap><label>
          <input type="hidden" name="importSynch[noAddProduct]" value="">
          <input type="checkbox" name="importSynch[noAddProduct]"  {checked:$import[noAddProduct]}>
          не добавлять </label></td>
        </tr>
      <tr>
        <td valign="top" nowrap>Обновленных товаров</td>
        <td align="right" valign="top"><?= (int)$updates['product'][0]?></td>
        <td valign="top" nowrap><label>
          <input type="hidden" name="importSynch[noUpdateProduct]" value="">
          <input type="checkbox" name="importSynch[noUpdateProduct]" {checked:$import[noUpdateProduct]}>
          не  обновлять </label></td>
        </tr>
    </table></td>
    <td valign="top"><table border="0" cellpadding="2" cellspacing="0">
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
        <td><input type="hidden" name="importSynch[replaceProperty]" value="">
          <input type="checkbox" name="importSynch[replaceProperty]" {checked:$import[replaceProperty]}></td>
      </tr>
    </table></td>
  </tr>
</table>
<p>
  <input type="submit" value="Обновить сайт" name="doImportSynch" class="button" />
</p>
</form>
<? } ?>

<? function doImportSynch(&$db, &$ddb, $import)
{
	//	Обработаные похиции
	$pass		= array();
	//	Необходимо добавить родителей
	$parentLink	= array();
	
	$sql	= array();
	$sql[]	= '`ignore`=0 AND `updated`=0';

	if ($import['noAddCatalog'])	$sql[]	= "(`doc_id`<>0 OR `doc_type`<>'catalog')";
	if ($import['noUpdateCatalog'])	$sql[]	= "(`doc_id`=0 OR `doc_type`<>'catalog')";

	if ($import['noAddProduct'])	$sql[]	= "(`doc_id`<>0 OR `doc_type`<>'product')";
	if ($import['noUpdateProduct'])	$sql[]	= "(`doc_id`=0 OR `doc_type`<>'product')";
	
	$bAddToMap			= $import['addToMap']?'map':'';
	$bReplacePropertty	= $import['replaceProperty']?true:false;
	
	$ids	= array();
	$db->open('`delete`=1');
	while($data = $db->next())
	{
		$id	= $data['doc_id'];
		if ($id) module("doc:update:$id:delete");
		$ids[]	= $db->id();
	}
	if ($ids) $db->delete($ids);

	$db->open($sql);
	while($data = $db->next())
	{
		$d			= array();
		$fields		= $data['fields'];
		
		$d['title']	= $data['name'];
		$d['price']	= parseInt($fields['price']);
		$d['fields']	= $fields[':fields'];
		$d['fields']['any']['import'][':raw']['delivery']	= $fields['delivery'];
		dataMerge($d, $d[':data']);
		
		if ($replaceProperty){
			$d['+property']	= $fields[':property'];
		}else{
			$d[':property']	= $fields[':property'];
		}
		//	Почистить перечень артикулов
		$article	= importMergeArticles(explode(',', $data['article']));
		//	Найти идентификатор документа среди возможно обновленных
		foreach($article as $v2)
		{
			$iid= $pass[$data["doc_type"]][":$v2"];
			if (!$iid) continue;
			$data['doc_id'] = $iid;
			break;
		}
		//	Попробовать подставить родителя
		$needLinkParent	= '';
		if ($data['parent_doc_id'] == 0 &&
			$fields['parent'])
			{
				$parentArticle	= $fields['parent'];
				$parentID		= $pass['catalog'][":$parentArticle"];
				if ($parentID){
					$data['parent_doc_id']	= $parentID;
				}else{
					$needLinkParent	= $parentID;
				}
		}
		//	Поместить в карту сайта, если задано настройками
		if ($bAddToMap &&
			$data['doc_type'] == 'catalog' &&
			$data['parent_doc_id'] == 0 &&
			$needLinkParent  == '')
		{
			$d['+property']['!place']	= $bAddToMap;
		}
		//	Если документ есть, обновитьь
		if ($data['doc_id'])
		{
			$doc= $ddb->openID($data['doc_id']);
			$a	= $doc['fields']['any'];
			$a	= $a['import'][':importArticle'];
			//	Объеденить артикулы
			$article	= importMergeArticles($article, explode(',', $a));
			$d['fields']['any']['import'][':importArticle']	= implode(', ', $article);
			//	Добавить родителя
			if ($data['parent_doc_id']){
				$d[':property'][':parent']	= $data['parent_doc_id'];
			}
			//	Обновить документ
			if ($iid = moduleEx("doc:update:$data[doc_id]:edit", $d))
			{
				$id	= $db->id();
				$db->setValues($id, array('updated' => 1, 'doc_id'=>$iid));
			}
		}else{
			$d['fields']['any']['import'][':importArticle']	= implode(', ', $article);
			//	Добавить документ
			if ($iid = moduleEx("doc:update:$data[parent_doc_id]:add:$data[doc_type]", $d))
			{
				$id	= $db->id();
				$db->setValues($id, array('updated' => 1, 'doc_id'=>$iid));
			}
		}
		//	Добавить в обновленные для возможного повторного прохода
		if ($iid){
			foreach($article as $v2){
				$pass[$data['doc_type']][":$v2"]	= $iid;
			}
			if ($needLinkParent){
				$parentLink[$iid][$needLinkParent]	= $needLinkParent;
			}
		}
	}
	foreach($parentLink as $iid => $parentArticle){
		$parentID	= $pass['catalog'][":$parentArticle"];
		if (!$parentID) continue;
		
		$d	= array();
		$d[':property'][':parent']	= $parentID;
		m("doc:update:$iid:edit", $d);
	}
	//	Clear all doc's caches
	m('doc:clear');
}
function importMergeArticles($a1=NULL, $a2=NULL)
{
	$ret	= array();
	if (is_array($a1)){
		foreach($a1 as $v){
			if ($v = trim($v)) $ret[$v] = $v;
		}
	}
	if (is_array($a2)){
		foreach($a2 as $v){
			if ($v = trim($v)) $ret[$v] = $v;
		}
	}
	return $ret;
}
?>
