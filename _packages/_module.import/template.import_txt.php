<? function import_txt($val, &$process)
{
	$rows = 0;
	//	Открвть импортируемый файл
	$f	= fopen($process['importFile'], 'r');
	//	Переместить точку чтения на предыдущую позицию
	fseek($f, $off = $process['offset']);
	while(!feof($f) && sessionTimeout() > 5)
	{
		$line = fgets($f);
		$row = explode("\t", $line);
		//	Первая строка, пропустить, названия колонок
		if ($off == 0){
			$off = ftell($f);
			continue;
		}
		@$name = trim($row[0]);
		@$article = trim($row[1]);
		if (!$name || !$article) continue;
		
		$prop = array();
		$prop['id']		= ":$article";
		$prop['name']	= $name;
		importProduct($process, $prop);

		//	Каждые 100 строк обновлять файл импорта
		if ((++$rows % 200) == 0){
			//	Если запись не удалась, значит задача отменена
			if (!setImportProcess($process, false))
				return true;
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