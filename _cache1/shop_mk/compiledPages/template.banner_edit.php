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
.bannerPanel .input{
	margin:5px 0;
}
.bannerPanel{
	display:none;
	background:white;
	color:black;
	position:absolute;
	min-width:100px; min-height:50px;
	top:0; left:0;
	padding:10px;
}
.bannerEdit:hover .bannerPanel{
	display:block;
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
    <? module("banner:show:$name"); ?>
	<div class="bannerPanel shadow">
    <form action="<? module("url:banner_edit_$name"); ?>" method="post" enctype="multipart/form-data">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
    <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td width="250" nowrap="nowrap">Заголовок баннера </td>
        <td colspan="3" nowrap="nowrap">Фоновая картинка</td>
        <td nowrap="nowrap">Стиль фона</td>
        </tr>
      <tr>
        <td width="25"><input type="text" name="banner[content][name]" class="input w100" value="<? if(isset($data["content"]["name"])) echo htmlspecialchars($data["content"]["name"]) ?>" /></td>
        <td width="25"><input name="bannerRemoveBackground" type="checkbox" title="Удалить" /></td>
        <td width="25"><div class="fileField"><input type="file" name="banner[background]" /></div></td>
        <td><a href="/<? if(isset($data["background"]["image"])) echo htmlspecialchars($data["background"]["image"]) ?>" target="_blank"><? if(isset($bgImage)) echo htmlspecialchars($bgImage) ?></a></td>
        <td><input type="text" class="input w100" name="banner[background][style]" value="<? if(isset($data["background"]["style"])) echo htmlspecialchars($data["background"]["style"]) ?>"  /></td>
        </tr>
      <tr>
        <td nowrap="nowrap"> Ссылка баннера </td>
        <td colspan="3" nowrap="nowrap">Изображение</td>
        <td nowrap="nowrap">Стиль изображения</td>
        </tr>
      <tr>
        <td width="25"><input type="text" name="banner[content][url]" class="input w100" value="<? if(isset($data["content"]["url"])) echo htmlspecialchars($data["content"]["url"]) ?>" /></td>
        <td width="25"><input name="bannerRemoveImage" type="checkbox" title="Удалить" /></td>
        <td width="25"><div class="fileField"><input type="file" name="banner[image]" /></div></td>
        <td><a href="/<? if(isset($data["image"]["image"])) echo htmlspecialchars($data["image"]["image"]) ?>" target="_blank"><? if(isset($fgImage)) echo htmlspecialchars($fgImage) ?></a></td>
        <td><input type="text" class="input w100" name="banner[image][style]" value="<? if(isset($data["image"]["style"])) echo htmlspecialchars($data["image"]["style"]) ?>" /></td>
        </tr>
    </table></td>
  </tr>
</table>
HTML код баннера
<div><textarea name="banner[content][html]" cols="" rows="4" class="input w100"><? if(isset($data["content"]["html"])) echo htmlspecialchars($data["content"]["html"]) ?></textarea></div>
<div align="right"><input type="submit" class="button" value="Сохранить" /></div>
    </form>
    </div>
</div>
<script>
$(function(){
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
