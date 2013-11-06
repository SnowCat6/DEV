<?
function module_translit(&$val, &$name)
{
	$name		= utf8_to_win($name);
	
	$translit	= new Translit();
	$name		= $translit->UrlTranslit($name);
	
	return $name;
}
function utf8_to_win($string)
{
	if (function_exists('iconv')) return iconv('UTF-8', 'windows-1251', $string);
	elseif (function_exists('mb_convert_encoding')) return mb_convert_encoding($string, 'UTF-8', 'windows-1251');

	$out = '';
	for ($c=0;$c<strlen($string);$c++){
		$i=ord($string[$c]);
		if ($i <= 127) @$out .= $string[$c];
		if (@$byte2){
			$new_c2=($c1&3)*64+($i&63);
			$new_c1=($c1>>2)&5;
			$new_i=$new_c1*256+$new_c2;
			if ($new_i==1025){
				$out_i=168;
			} else {
				if ($new_i==1105){
					$out_i=184;
				} else {
					$out_i=$new_i-848;
				}
			}
			@$out .= chr($out_i);
			$byte2 = false;
		}
		if (($i>>5)==6) {
			$c1 = $i;
			$byte2 = true;
		}
	}
	return $out;
}
?>
<?php
/*
  Translit PHP class.
  v.1.0
  2005 !!!!!!
  24 October 2004

  ---------

  Транслитерация ссылок (приведение их в соответствие с форматом URL).
  Латинские буквы и цифры остаются, а русские + знаки препинания преобразуются
  одним из способов (способы нужны каждый для своей задачи)

  Подробнее: http://pixel-apes.com/translit

  ---------

  Методы этого класса можно использовать как статические, 
  например, Translit::UrlTranslit("Свежая новость из цирка")

  ---------

  * UrlTranslit( $string, $allow_slashes = TR_NO_SLASHES ) 
    -- преобразовать строку в "красивый читаемый URL"

  * Supertag( $string, $allow_slashes = TR_NO_SLASHES )    
    -- преобразовать строку в "супертаг" -- короткий простой 
       идентификатор, состоящий из латинских букв и цифр.

  * BiDiTranslit( $string, $direction=TR_ENCODE, $allow_slashes = TR_NO_SLASHES ) 
    -- преобразовать строку в "формально правильный URL"
       с возможностью восстановления.
       Другое значение $direction позволяет восстановить
       строку обратно с незначительными потерями

  * во всех функциях параметр $allow_slashes управляет тем, игнорировать ли символ "/",
    пропуская его неисправленным, либо удалять его из строки

=============================================================== (Kukutz)

  TODO:
  * strtolower replacement for non-locale systems
*/

define("TR_ENCODE", 0);
define("TR_DECODE", 1);
define("TR_NO_SLASHES", 0);
define("TR_ALLOW_SLASHES", 1);

class Translit
{

  //пустой конструктор, чтобы методы могли работать через ::
  function Translit() {}

  //URL transliterating
  function UrlTranslit($string, $allow_slashes = TR_NO_SLASHES)
  {
   $slash = "";
   if ($allow_slashes) $slash = "\\/";

   $LettersUpper= utf8_to_win("АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ");
   $LettersLower= utf8_to_win("абвгдеёжзийклмнопрстуфхцчшщъыьэюя");

   $LettersFrom	= utf8_to_win("абвгдезиклмнопрстуфыэйхё");
   $LettersTo	= "abvgdeziklmnoprstufyejxe";
   $Consonant	= utf8_to_win("бвгджзйклмнпрстфхцчшщ");
   $Vowel		= utf8_to_win("аеёиоуыэюя");
   $Vowel2		= utf8_to_win("ь|ъ");
   
   $BiLetters2	= array( 
     "ж" => "zh", "ц"=>"ts", "ч" => "ch", 
     "ш" => "sh", "щ" => "sch", "ю" => "ju", "я" => "ja",
   );
   $BiLetters	= array();
   foreach($BiLetters2 as $key => $val){
	   $key	= utf8_to_win($key);
	   $BiLetters[$key] = $val;
   }

   $string = preg_replace("/[_\s,?!\[\](){}]+/", "_", $string);
   $string = preg_replace("/-{2,}/", "--",	$string);
   $string = preg_replace("/_-+_/", "--",	$string);
   $string = preg_replace("/[_\-]+$/", "",	$string);
   
   $string = strtolower($string);
   $string = strtr($string, $LettersUpper,	$LettersLower);
   //here we replace ъ/ь 
   $string = preg_replace("/($Vowel2)([$Vowel])/",	"j\\2",	$string);
   $string = preg_replace("/($Vowel2)/", 			"",		$string);
   //transliterating
   $string = strtr($string, $LettersFrom,	$LettersTo);
   $string = strtr($string, $BiLetters);

   $string = preg_replace("/j{2,}/", "j", $string);

   $string = preg_replace("/[^".$slash."0-9a-z_\-.]+/", "", $string);
 
   return $string;
  }
}
?>
