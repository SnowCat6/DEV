<? function site_SEO_meta()
{
	if (!access('write', 'admin:SEO')) return;

	$SEO	= getValue('SEO');
	if (is_array($SEO))
	{
		$newSEO	= getValue('nameSEO');
		$newSEOv= getValue('valueSEO');
		if (is_array($newSEO))
		{
			foreach($newSEO as $ndx => $name)
			{
				$name		= trim($name);
				if (!$name) continue;
				$SEO[$name]	= trim($newSEOv[$ndx]);
			}
		}
		setIniValue(':SEO', $SEO);
	}

	$SEO	= getIniValue(':SEO');
	if (!is_array($SEO)) $SEO = array();
?>

Собственные метатеги для всех страниц сайта
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="table">
<tr>
    <th></th>
    <th nowrap="nowrap">Название метатега (name)</th>
    <th nowrap="nowrap">Значение метатега (content)</th>
</tr>
<?
foreach($SEO as $name => $val)
{
	if ($name == 'keywords' ||
		$name == 'description' ||
		$name == 'title' ||
		$name == 'titleEmpty')
		continue;
?>
<tr>
    <td><a class="delete" href="">X</a></td>
    <td>{$name}</td>
    <td width="100%"><input name="SEO[{$name}]" type="text" value="{$val}" class="input w100" /></td>
</tr>
<? } ?>
<tr class="adminReplicate" id="addMeta">
    <td><a class="delete" href="">X</a></td>
    <td><input name="nameSEO[]" type="text" value="" class="input w100" /></td>
    <td width="100%"><input name="valueSEO[]" type="text" value="" class="input w100" /></td>
</tr>
</table>
<p><input type="button" class="button adminReplicateButton" id="addMeta" value="Добавть метатег" /></p>


<? return '100-Метатеги (global)'; }  ?>