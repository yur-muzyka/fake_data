<?php
//В этот файл записываем исходный текст  
$source_text = 'text.txt';  
//Словарь, в который записываются слова, идущей после слов  
$dictionary = array();  
function load()  
{  
    global $dictionary,$source_text;  
    //Читаем исходный файл  
    $str = file_get_contents($source_text);  
    //Превращаем текст в одну строку  
    $str = preg_replace("#[\r\n]#","",$str);  
    //Выделяем все слова из строки (выражение в кавычках или в скобках считается одним словом) 
    preg_match_all("#((\"[^\"]+\")|(\([^\)]+\))|([^\(\)\"'\s]+))(\s+|\z)#",$str,$parts);  
    $words = $parts[1];  
    $count = count($words);  

    //Заполняем словарь  
    for( $i = 0; $i < $count; $i++ )  
    {  
        if( $i > 0 )  
        {  
            if( !in_array($words[$i],$dictionary[$prev_word]) )  
                $dictionary[$prev_word][] = $words[$i];  
        }  
        $prev_word = $words[$i];  
        if( empty($dictionary[$prev_word]) )  
            $dictionary[$prev_word] = array();  
    }  
    // Закольцовка
    $dictionary[$words[$i - 1]] = $dictionary[$words[0]];
    // Чистка дублей
    $dictionary = array_map('array_unique',$dictionary);
}  

//Функция для генерации текста. $count – количество слов, которое будем генерировать 
function genText($count)  {  
    global $dictionary;  
echo "<pre>"; var_dump($dictionary); die();
    $words = array_keys($dictionary);  
    $word = $words[0];  
    $text ='';  
    for( $i = 0; $i < $count; $i++ )  
    {  
        $text .= ' '.$word;  
        //Следующее слово - случайное слово из тех, что идут в исходном тексте за текущим словом 
        $word = $dictionary[$word][rand(0,count($dictionary[$word])-1)];  
    }  
    return $text;  
}  

load();  
echo genText(100); 


?>
