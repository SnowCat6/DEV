<?
function import_xml($val, &$process)
{
	global $importTags;
	$importTags = array();
	
	event('import.xml', $process);
	//	Накопительный текст тега
	@$ctx	= &$process['ctx'];
	//	Кол-во обработанных тегов, для сброса состояния импорта
	$row	= 0;
	//	Открвть импортируемый файл
	$f	= fopen($process['importFile'], 'r');
	//	Переместить точку чтения на предыдущую позицию
	fseek($f, $process['offset']);
	//	Считывать файл большими кусками пока он не закончится
	//	Или пока не истечет время импорта
	while(!feof($f) && sessionTimeout() > 5)
	{
		//	Запомним смещение относительно начала файла
		$thisOffset	= ftell($f);
		//	Прочитаем кусок файла
		$val		= fread($f, 2*1024*1024);
		//	Установим позицию парсинга в начало
		$nParse		= 0;
		//	Пока позволяет время, разюираем текст
		while($val && sessionTimeout() > 5)
		{
			//	Пока не найден тег, пытаемся его найти
			if ($ctx == '')
			{
				//	Ищем начало тега
				$nPos = strpos($val, '<', $nParse);
				//	Найти открывающуюся скобку
				if (!is_int($nPos)){
					//	Если не найдено начало, сохраняем межтеговый текст и считываем следующую порцию файла
					$process['tagCtx'] .= substr($val, $nParse);
					$val				= '';
					continue;
				}
				//	Начало тега найдено, запоминаем теж теговый текст
				$process['tagCtx']	.= substr($val, $nParse, $nPos - $nParse);
			}else{
				//	Начало тега найдено
				//	Такой случай может быть только, если тег считывается с начала чтения буффера
				$nPos = 0;
			}
			//	Найти конец тега
			$nPosEnd = strpos($val, '>', $nPos);
			if (!is_int($nPosEnd))
			{
				//	Еслм конца тега не найдено, сохраняем данные и считываем файл дальше
				$ctx	.= substr($val, $npos);
				$val	= '';
				continue;
			}
			//	Получить строку, содержащую весь тег
			$ctx	.= substr($val, $nPos, $nPosEnd - $nPos + 1);
			$text	= $process['tagCtx'];
			
			//	Перевести в UTF8
//			$ctx	= iconv('windows-1251', 'utf-8', $ctx);
//			$text	= iconv('windows-1251', 'utf-8', $text);
			//	Декодировать текст
			$text	= html_entity_decode($text);
			//	Обработать тег
			makeImportTag($process, $ctx, $text);

			//	Сместить позицию чтения
			$nParse	= $nPosEnd + 1;
			//	Задать смещение для дальнейшего считывания
			$process['offset']	= $thisOffset + $nParse;
			//	Удалить межтеговый текст
			$process['tagCtx']	= '';
			//	Удалить содержимое тега
			$ctx				= '';
			//	Каждые 100 строк обновлять файл импорта
			if ((++$row % 200) == 0){
//				echo $process['offset'], '=>';
				//	Если запись не удалась, значит задача отменена
				if (!setImportProcess($process, false))
					return true;
			}
		}
	}
	//	Если достигнут конец файла
	if ($bEnd = feof($f)){
		//	Задатть смещение на конец файла
		$process['offset'] = ftell($f);
	}
	//	Закрыть файл
	fclose($f);
	
	return $bEnd;
}
?>
<?
function importAddTagFn(&$tags){
	global $importTags;
	$importTags = array_merge($importTags, $tags);
}
?>
<?
//	Обработать найденый тег
function makeImportTag(&$process, &$ctx, &$text)
{
	//	true если тег закрывающий
	$bClose	= false;
	//	true если тег одиночный, и сразу закрывающий
	$bEndTag= false;
	//	Функция вызова
//	$fn		= '';
	
	//	Найти пробел после названия тега
	$nPos	= strpos($ctx, ' ');
	if (!$nPos){
		//	Или найти закрывающие символы
		$nPos = strpos($ctx, '/>');
		if ($nPos) $bClose = true;
	}
	if (!$nPos) $nPos = strpos($ctx, '>');
	if (!$nPos) return;
	
	if (!$bClose && strpos($ctx, '/>')) $bClose = true;

	//	Close tag
	if ($ctx[1] == '/'){
		$bEndTag= true;
		//	Получить имя тега
		$tag	= substr($ctx, 2, $nPos - 2);
		//	Название аункции закрывающего тега
//		$fn		= $tag.'_close';
	}else{
		//	Получить имя тега
		$tag	= substr($ctx, 1, $nPos - 1);
		//	Название функции открывающего тега
//		$fn		= $tag;
		$prop	= array();
		//	Получить все свойства тега
		if (preg_match_all('#(\w+)\s*=\s*[\'\"]([^\'\"]*)#u', $ctx, $vars)){
			foreach($vars[1] as $ix => $name){
				$val = $vars[2][$ix];
				//	Сохранить в массиве
				$prop[$name] = html_entity_decode($val);
			}
		}
	}
	
	global $importTags;
	$tagFn	= $importTags[$tag];
	if ($bClose){
		if ($tagFn){
			$text = '';
			$tagFn($process, $tag, $prop, $text, false);
			$process['tagStack'][] = $tag;
	
			$tagFn($process, $tag, $prop, $text, true);
			array_pop($process['tagStack']);
		}
	}else{
		if ($tagFn){
			$tagFn($process, $tag, $prop, $text, $bEndTag);
		}

		if ($bEndTag){
			//	Если тег закрывается, удалить из стека
			array_pop($process['tagStack']);
		}else{
			//	Если тег открывающийся, добавить в стек
			$process['tagStack'][] = $tag;
		}
	}
}?>

