<? function doc_page_product(&$db, &$menu, &$data)
{
	$data	= $db->data;
	$id		= $db->id();
	$folder	= $db->folder();
	$price	= docPrice($data);
	if ($price) $price = priceNumber($price);
	$p = m('prop:read:table:2', array('id'=>$id));

	m('script:lightbox');
	m('script:jq');
	if (!testValue('ajax')) setTemplate('product');
?><? module("page:style", '../../style.css') ?><? beginAdmin() ?>
<div class="product page">
<p class="path"><? module("doc:path"); ?></p>
<h1><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></h1>
<? module("rating:show:$id"); ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<?  if (beginCompile($data, "productPageImage")){ ?>
    <th width="250" valign="top">
<? if ($title = docTitleImage($id)){ ?>
<div>
<a href="<? if(isset($title)) echo htmlspecialchars($title) ?>" rel="lightbox[<? if(isset($id)) echo htmlspecialchars($id) ?>]" title="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>"><? displayThumbImage($title, array(320, 176), ' class="thumb"') ?></a>
</div>
<? } ?><? module('gallery:small', array('src' => "$folder/Gallery", 'id' => $id)) ?>
	</th>
<?  endCompile($data, "productPageImage"); } ?>
    <td valign="top"><div class="preview"></div>
<table border="0" cellspacing="0" cellpadding="0" class="warranty2">
  <tr>
    <td><div class="icon i1"><div class="ctx"><? module("read:productDelivery"); ?></div></div></td>
    <td><div class="icon i2"><div class="ctx"><? module("read:productService"); ?></div></div></td>
    <td><div class="icon i3"><div class="ctx"><? module("read:productWarranty"); ?></div></div></td>
    <td><div class="icon i4"><div class="ctx"><? module("read:productPay"); ?></div></div></td>
  </tr>
</table>

<? $module_data = array(); $module_data[] = "bottom"; moduleEx("read:productInfo", $module_data); ?>
<table border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td width="50%"><span class="priceName"><big><? if(isset($price)) echo $price ?></big> руб.</span></td>
    <td width="50%">&nbsp;</td>
  </tr>
  <tr>
    <td class="button2"><? $module_data = array(); $module_data[] = "Добавить<br />в корзину"; moduleEx("bask:button:$id", $module_data); ?></td>
    <td>&nbsp;</td>
  </tr>
</table>
<? module("callback"); ?>
    </td>
</tr>
</table>
<div class="info menu">
<ul class="tabs">
	<li class="current"><a href="#property">Характеристики</a></li>
	<li><a href="#document">Описание</a></li>
	<li><a href="#feedback">Отзывы покупателей</a></li>
    <div></div>
</ul>
<div class="content current" id="property"><? if(isset($p)) echo $p ?></div>
<div class="content" id="document"><? document($data) ?></div>
<div class="content" id="feedback"><? event('document.comment',	$id)?></div>
</div>
</div>
<? endAdmin($menu, true) ?>
<script>
$(function(){
	$(".info .tabs a").click(function(){
		$(".info .tabs li, .info .content").removeClass("current");
		$(this).parent().addClass("current");
		$(".info " + $(this).attr("href")).addClass("current");
		return false;
	});
	$("a[rel*=lightbox]")
	.hover(function(){
		$("#preview").remove();
		$(".preview").css({"position": "relative", "z-index": 999})
			.append("<div id='preview'><div><img src='"+ this.href +"' alt='Image preview' /></div></div>")
			.children()
		.css({
			"position": "absolute",
			"background": "#f8f8f8",
			"z-index": 999, "background": "#f0f0f0",
			"left": 0, "right": 0, "height": 300,
			"display": "none", "overflow": "hidden"
		}).fadeIn("fast");
	}, function(){
		$("#preview").fadeOut("fast", function(){
			$(this).remove();
		});
	}).mousemove(function(ev)
	{
		var src = $($(this).find("img"));
		var parentOffset = $(this).parent().offset();;
		var zoomX	= (ev.pageX - parentOffset.left)/src.width();
		var zoomY	= (ev.pageY - parentOffset.top)/src.height();
		if (zoomY > 1) zoomY = 0;

		var preview = $("#preview");
		var previewImage = $("#preview img");
		
		var x = previewImage.width()>preview.width()?(previewImage.width()-preview.width())*zoomX:0;
		var y = previewImage.height()>preview.height()?(previewImage.height()-preview.height())*zoomY:0;
		
		$("#preview div").css({
			"position": "absolute",
			"left": -x, "top": -y
		});
	});
});
</script>
<? } ?>