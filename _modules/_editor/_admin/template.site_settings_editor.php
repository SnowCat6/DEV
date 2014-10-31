<? function site_settings_editor($ini)
{
	$i	= $ini[':editor'];
?>
<h2>Настройки форматирования</h2>
<div>
	<label>
        <input type="hidden" name="settings[:editor][MS_WORD_disable]"  />
        <input type="checkbox" name="settings[:editor][MS_WORD_disable]" value="yes" {checked:$i[MS_WORD_disable]==yes} />
        Не удалять MS WORD стили
    </label>
</div>
<div>
	<label>
        <input type="hidden" name="settings[:editor][HTML_comments_disable]"  />
        <input type="checkbox" name="settings[:editor][HTML_comments_disable]" value="yes" {checked:$i[HTML_comments_disable]==yes} />
        Не удалять HTML комментарии
    </label>
</div>
<div>
	<label>
        <input type="hidden" name="settings[:editor][STYLE_font_disable]"  />
        <input type="checkbox" name="settings[:editor][STYLE_font_disable]" value="yes" {checked:$i[STYLE_font_disable]==yes} />
        Не удалять STYLE font-family
    </label>
</div>
<div>
	<label>
        <input type="hidden" name="settings[:editor][STYLE_color_disable]"  />
        <input type="checkbox" name="settings[:editor][STYLE_color_disable]" value="yes" {checked:$i[STYLE_color_disable]==yes} />
        Не удалять STYLE color
    </label>
</div>
<div>
	<label>
        <input type="hidden" name="settings[:editor][STYLE_font-size_disable]"  />
        <input type="checkbox" name="settings[:editor][STYLE_font-size_disable]" value="yes" {checked:$i[STYLE_font-size_disable]==yes} />
        Не удалять STYLE font-size
    </label>
</div>
<div>
  <label>
        <input type="hidden" name="settings[:editor][STYLE_nbsp_disable]"  />
        <input type="checkbox" name="settings[:editor][STYLE_nbsp_disable]" value="yes" {checked:$i[STYLE_nbsp_disable]==yes} />
    Не заменять <b>&amp;nbsp</b> на пробелы </label>
</div>
<? return 'Визуальный редактор'; } ?>