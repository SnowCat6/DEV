<? function banner_edit($name, $path)
{
	if (!hasAccessRole('admin')) return;
	$data	= readIniFile($path);
	if (is_array($banner = getValue('banner')))
	{
		dataMerge($banner, $data);
		$banner['content']['html']	= urlencode($banner['content']['html']);
		$data	= $banner;

		@$bgImage	= basename($data['background']['image']);
		@$fgImage	= basename($data['image']['image']);

		$f		= $_FILES['banner'];
		if (is_file($val = $f['tmp_name']['image'])){
			$fn		= $f['name']['image'];
			$file	= dirname($path)."/$name/";
			makeDir($file);
			move_uploaded_file($val, $file.$fn);
			$data['image']['image']	= images."/banners/$name/$fn";
		}else{
			if (testValue('bannerRemoveImage'))		$data['image']['image']	= '';
		}
		if (is_file($val = $f['tmp_name']['background'])){
			$fn		= $f['name']['background'];
			$file	= dirname($path)."/$name/";
			makeDir($file);
			move_uploaded_file($val, $file.$fn);
			$data['background']['image']	= images."/banners/$name/$fn";
		}else{
			if (testValue('bannerRemoveBackground'))$data['background']['image']	= '';
		}
		writeIniFile($path, $data);
		setCacheValue("banner/$name", $data);
	}
	m('page:title', $name);
	@$data['content']['html'] = urldecode($data['content']['html']);
	
	@$bgImage	= basename($data['background']['image']);
	@$fgImage	= basename($data['image']['image']);
	
	define('bannerEdit', true);
	m('script:jq');
?>
<style>
.bannerEdit{
	position:relative;
	border:solid 1px gray;
}
.bannerEdit .bannerImage{
	min-height:300px;
}
.bannerPanel{
	ddisplay:none;
	color:black;
	position:absolute;
	min-width:550px;
	top:0; left:0;
}
.bannerEdit:hover .bannerPanel{
	display:block;
}
.bannerPanel .input{
	margin:5px 0;
}
.bannerPanel .fileField{
	overflow:hidden;
	width:25px; background:#FC0;
}
.bannerPanel .fileField input{
	opacity: 0; filter: alpha(opacity=0); opacity: 0;
}
</style>
<div class="bannerEdit">
    {{banner:show:$name}}
	<div class="bannerPanel shadow">
    <form action="{{url:banner_edit_$name}}" method="post" enctype="multipart/form-data">

<div id="bannerTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#bannerContent">Содержание</a></li>
    <li class="ui-corner-top"><a href="#bannerImage">Изображения</a></li>
    <li class="ui-corner-top"><a href="#bannerFeedback">Форма связи</a></li>
    <li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="bannerContent" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td> Заголовок баннера </td>
      <td nowrap="nowrap" width="10"><label for="hideBanner">Скрыть</label></td>
      <td width="10">
<input type="hidden" name="banner[content][hide]" value="" />
<input type="checkbox" name="banner[content][hide]" id="hideBanner"<?= $data['content']['hide']?' checked="checked"':''?> value="1" />
      </td>
    </tr>
  </table>
  <div><input type="text" name="banner[content][name]" class="input w100" value="{$data[content][name]}" /></div>
HTML код баннера
<div><textarea name="banner[content][html]" cols="" rows="8" class="input w100">{$data[content][html]}</textarea></div>
</div>

<div id="bannerImage" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td colspan="3" nowrap="nowrap">Фоновая картинка</td>
        <td width="50%" nowrap="nowrap">Стиль фона</td>
        </tr>
      <tr>
        <td width="25"><input name="bannerRemoveBackground" type="checkbox" title="Удалить" /></td>
        <td width="25"><div class="fileField"><input type="file" name="banner[background]" /></div></td>
        <td><a href="{$data[background][image]}" target="_blank">{$bgImage}</a></td>
        <td><input type="text" class="input w100" name="banner[background][style]" value="{$data[background][style]}"  /></td>
        </tr>
      <tr>
        <td colspan="3" nowrap="nowrap">Изображение</td>
        <td nowrap="nowrap">Стиль изображения</td>
        </tr>
      <tr>
        <td width="25"><input name="bannerRemoveImage" type="checkbox" title="Удалить" /></td>
        <td width="25"><div class="fileField"><input type="file" name="banner[image]" /></div></td>
        <td><a href="{$data[image][image]}" target="_blank">{$fgImage}</a></td>
        <td><input type="text" class="input w100" name="banner[image][style]" value="{$data[image][style]}" /></td>
        </tr>
    </table>
</div>

<div id="bannerFeedback" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
Название формы
<div><input type="text" class="input w100" name="banner[feedback][name]" value="{$data[feedback][name]}"  /></div>
Название класса формы
<div><input type="text" class="input w100" name="banner[feedback][class]" value="{$data[feedback][class]}"  /></div>
</div>

</div>
    </form>
    </div>
</div>
<script>
$(function(){
	$("#bannerTabs").tabs();
	$(".bannerEdit .fileField input").change(function(){
		var id		= $(this).attr("name");
		var image	= $(this).val();
		if (image) image = 'url(' + image + ')';
		switch(id){
		case 'banner[background]':
			$(".bannerEdit .bannerBackground").css('background', image);
		break;
		case 'banner[image]':
			$(".bannerEdit .bannerImage").css('background', image);
		break;
		}
	});
});
</script>
<? } ?>
