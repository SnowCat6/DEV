<? function site_SEO_fields()
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
Заголовок (title) для всех страниц сайта без заголовка
<div><input name="SEO[titleEmpty]" type="text" value="{$SEO[titleEmpty]}" class="input w100" /></div>
Заголовок (title) для всех страниц сайта, знак % заменяется на заголовок документов
<div><input name="SEO[title]" type="text" value="{$SEO[title]}" class="input w100" /></div>
Ключевые слова (keywords metatag) для всех старниц сайта
<div><input name="SEO[keywords]" type="text" value="{$SEO[keywords]}" class="input w100" /></div>
Описание (description metatag) для всех старниц сайта
<div><textarea name="SEO[description]" cols="" rows="5" class="input w100">{$SEO[description]}</textarea></div>
<? return '100-SEO (global)'; } ?>
