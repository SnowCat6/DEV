<? function module_advBannerEdit(&$val, &$data)
{
	if (!hasAccessRole('admin,writer,developer')) return;
	$name	= getValue('edit');
	if (!$name) return;

	m('styleLoad', 'css/advBanner.css');
	m('page:title', "Редактирование $name");
	
	$folder	= images."/advImage";
	module('editor', "$folder/$name");
	
	$data	= readData("$folder/adv.bin");
	$doc	= $data[$name];
	
	if (testValue('deleteTitleImage')){
		unlink("$folder/$name/$doc[titleImage]");
		$doc['titleImage'] = '';
	}

	$d		= getValue('doc');
	if (is_array($d)){
		dataMerge($d, $doc);
		$f	= $_FILES['bannerBk'];
		if (is_file($f['tmp_name']))
		{
			$n	= $f['name'];
			mEx('translit', $n);
			
			makeDir("$folder/$name");
			move_uploaded_file($f['tmp_name'], "$folder/$name/$n");
			if ($doc['titleImage'] != $n) unlink("$folder/$name/$doc[titleImage]");
			$d['titleImage']	= $n;
		}
		
		$data[$name]	= $d;
		$doc			= $d;
		writeData("$folder/adv.bin", $data);
		setCache("advBanner$name");
	}
	
	$titleImage	= $doc['titleImage'];
	$titlePath	= "$folder/$name/$titleImage";
	
	if ($doc['show'] != 'no') $doc['show'] = 'yes';
	
	$ed	= array();
	$ed	['class']	= "advContent";
	if (is_file($titlePath)){
		$ed['css']['background']	= "url($titlePath) 50% top";
	}
	$rel= json_encode($ed);
?>
<form method="post" action="{{url:advBanner}}?edit={$name}" class="ajaxReload" enctype="multipart/form-data">
<table width="100%" border="0">
  <tr>
    <td nowrap="nowrap">
    <label>
        <input type="hidden" name="doc[show]" value="no" />
        <input type="checkbox" name="doc[show]" value="yes" {checked:$doc[show]=="yes"}/>
        Показывать баннер
    </label>
    </td>
    <td width="100%" align="right">
<label>Фоновая картинка <input type="file" name="bannerBk" /></label>
    </td>
    <td nowrap="nowrap">
<? if ($titleImage){ ?>
<label><input type="checkbox" name="deleteTitleImage" />
	Удалить фоновую картинку <b><a href="{$titlePath}" target="_blank">{$titleImage}</a></b>
</label>
<? } ?>
    </td>
  </tr>
</table>
<div><textarea name="doc[document]" cols="" rows="30" class="input w100 editor" rel="{$rel}">{$doc[document]}</textarea></div>
</form>
<? } ?>