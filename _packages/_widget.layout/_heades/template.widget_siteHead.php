<? function widget_siteHead($id, $data)
{
	$path	= images . "/$id";
	mkDir($path);
	
	$url	= getURL();
	$size	= $data['logoSize'];
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="siteHead {$data[class]}">
  <tbody>
    <tr>
      <td valign="top" class="siteLogo" style="width: {$data[width]}; min-width: {$data[width]}">
        	{{file:image=size:$size;uploadFolder:$path;hasAdmin:top;property.href:$url}}
      </td>
      <td valign="top" class="siteInfo">
            {{holder:$id.layout}}
      </td>
    </tr>
  </tbody>
</table>

<? } ?>