<? function widget_siteBottom($id, $data)
{
	$path	= images . "/$id";
	mkDir($path);
	
	$url	= getURL();
	$size	= $data['logoSize'];
	
	$width	= 'width: ' . (int)$size . 'px';
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="siteBottom {$data[class]}">
  <tbody>
    <tr>
      <td valign="top" style="width: {$data[width]}; min-width: {$data[width]}">
		    <div class="siteBottomLogo">
        		{{file:image=size:$size;uploadFolder:$path;hasAdmin:top;property.href:$url}}
            </div>
            <div class="siteBottomLeft" style="{$width}">
	            {{holder:$id.left}}
            </div>
      </td>
      <td valign="top" class="siteBottomInfo">
            {{holder:$id.layout}}
      </td>
    </tr>
  </tbody>
</table>

<? } ?>