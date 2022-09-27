<?php
/*
Для начала берем строку, который нужно зашифровать, переводим его в base64 (так как base64 ключ состоит только из символов a-z, A-Z, 0-9).
Затем с каждого символа получаем md5-хэш, для удобства кладем в массив.
С каждого хэша берем символ №3,6,1,2 (можно другие, я решил что эти подойдут) и склеиваем их.

Чтобы расшифровать проходимся циклом по коду, заменяем хэш[3,6,1,2] на символ, которому он соответствует.
Ну, и потом декодируем из base64.
*/
/*
function myencode($unencoded, $key)
{
    //Шифруем
    debug($unencoded,'origin');
    $string = base64_encode($unencoded);
    debug($string,'base64_decode');

    $arr = [];
    $newStr = '';
    for ( $x = 0; $x < strlen($string); $x++ )
    {
        $arr[$x] = md5( $key . md5($string[$x] . $key ) );
        debug($arr[$x],'$arr[$x]');

        //Склеиваем символы
        $newStr .= $arr[$x][3].$arr[$x][6].$arr[$x][1].$arr[$x][2];
    }
    debug($newStr,'$newStr');
    return $newStr;
}
function mydecode( $encoded, $key )
{
    //Символы, с которых состоит base64-ключ
    $strofsym="qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM=";
    for ( $x = 0; $x < strlen($strofsym); $x++ )
    {
        //Хеш, который соответствует символу, на который его заменят.
        $tmp = md5($key . md5($strofsym[$x] . $key) );
        //Заменяем №3,6,1,2 из хеша на символ
        $encoded = str_replace($tmp[3].$tmp[6].$tmp[1].$tmp[2], $strofsym[$x], $encoded);
        debug($encoded,'$encoded');
    }
    return base64_decode($encoded);
}

$enc = encode('350', '1s=+Djsyt-e3405v');
$dec = decode($enc, '1s=+Djsyt-e3405v');
var_dump((int)$dec);


$key = hash('sha256', 'this is a secret key', true);
$input = "Let us meet at 9 o'clock at the secret place.";
*/
/*
define('ENCRYPTION_KEY', 'ab86d144e3f080b61c7c2e43');
// Encrypt
function Encrypt11($plaintext)
{
    //$plaintext = "Тестируем обратимое шифрование на php 7";
    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, ENCRYPTION_KEY, $options=OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, ENCRYPTION_KEY, $as_binary=true);
    $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
    return $ciphertext;
}

// Decrypt
function Decrypt11($ciphertext)
{
    $c = base64_decode($ciphertext);
    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, $sha2len=32);
    $ciphertext_raw = substr($c, $ivlen+$sha2len);
    $plaintext = openssl_decrypt($ciphertext_raw, $cipher, ENCRYPTION_KEY, $options=OPENSSL_RAW_DATA, $iv);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, ENCRYPTION_KEY, $as_binary=true);
    if (hash_equals($hmac, $calcmac))
    {
        return $plaintext;
    }
    return false;
}
*/
function NUMtoSTRING($NUMBER, $ALPHA = '0123456789aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZаАбБвВгГдДеЕёЁжЖзЗиИйЙкКлЛмМнНоОпПрРсСтТуУфФхХцЦчЧшШщЩъЪыЫьЬэЭюЮяЯ'){
    $ALPHA_LEN = mb_strlen($ALPHA, 'UTF-8');
    $STRNG = '';
    while($NUMBER > $ALPHA_LEN){
        $STRNG = mb_substr($ALPHA, $NUMBER % $ALPHA_LEN, 1, 'UTF-8') . $STRNG;
        $NUMBER = floor($NUMBER / $ALPHA_LEN);
    }
    $STRNG = mb_substr($ALPHA, $NUMBER, 1, 'UTF-8') . $STRNG;
    return $STRNG;
}
//echo NUMtoSTRING(1526892347543); // "rxC11G" - 6 символов

function STRINGtoNUM($STRNG, $ALPHA = '0123456789aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZаАбБвВгГдДеЕёЁжЖзЗиИйЙкКлЛмМнНоОпПрРсСтТуУфФхХцЦчЧшШщЩъЪыЫьЬэЭюЮяЯ'){
    $ALPHA_LEN = mb_strlen($ALPHA, 'UTF-8');
    $STRNG_LEN = mb_strlen($STRNG, 'UTF-8');
    $NUMBER = 0;
    for($S_i = 0; $S_i < $STRNG_LEN; $S_i++)
        $NUMBER += mb_strpos($ALPHA, mb_substr($STRNG, $S_i, 1, 'UTF-8'), 0, 'UTF-8') * pow($ALPHA_LEN, $STRNG_LEN - $S_i - 1);
    return $NUMBER;
}
//echo STRINGtoNUM('rxC11G'); // вернет нам наши 1526892347543