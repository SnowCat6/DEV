<widget:gallerySlider
    category= 'Информация'
    name	= 'Слайд фотографий'
    desc	= 'Коллекция фотографий с переключением'
	cap		= "gallery"
>
<cfg:data.style.size name="Размер фотографии (WxH)" default="250x150" />

<wbody>

<module:script:CrossSlide />

<module:gallery:fileUpload upload="$data[imageFolder]" message="добавить фото" />

<div {!$data[style]|style} class="CrossFadeEx slider">

<? foreach(getFiles($data['imageFolder']) as $filePath)
{
	$menu	= imageAdminMenu($filePath);
?>
<div class="itemElm">
{beginAdmin}
	<module:file:image
    	src		= "$filePath"
        clip	= "$data[size]"
        hasAdmin= ""
    />
{endAdmin}
</div>
<? } ?>

</div>

</wbody>

</widget:gallerySlider>