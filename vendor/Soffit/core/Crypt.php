<?php

namespace soffit;


class Crypt
{

	protected static $secretKey = "OMGSecretKey123!";
    protected static $ENCRYPTION_KEY = 'b34x6dy34trj6d14w34e3!f086780b617_c43';

	protected static $algo1 = "sha1";
    protected static $algo2 = "sha3-256";

    protected static $algorithm = 2;
    protected static $algorithms = [
        'schoolBoy' => 1,
        'openssl' => 2
    ];

    public static function setAlgorithm( string $algorithmName ) : void
    {
        if ( empty($algorithmName) ) return;

        if ( array_key_exists($algorithmName, static::$algorithms) )
            static::$algorithm = static::$algorithms[$algorithmName];
    }

    /**
     * Алогоритм одного школьника
     * Для начала берем строку, который нужно зашифровать, переводим его в base64 (так как base64 ключ состоит только из символов a-z, A-Z, 0-9).
     * Затем с каждого символа получаем md5-хэш, для удобства кладем в массив.
     * С каждого хэша берем символ №3,6,1,2 (можно другие, я решил что эти подойдут) и склеиваем их.

     * Чтобы расшифровать проходимся циклом по коду, заменяем хэш[3,6,1,2] на символ, которому он соответствует.
     * Ну, и потом декодируем из base64.
     * @param string $unEncoded
     * @return string
     */
    protected static function schoolBoyEncode( string $unEncoded ) : string
    {
        $key = static::$secretKey;
        $algo1 = static::$algo1;
        $algo2 = static::$algo2;

        //Шифруем
        //debug($unencoded,'origin');
        $string = base64_encode($unEncoded);
        //debug($string,'base64_decode');

        $arr = [];
        $newStr = '';
        for ( $x = 0; $x < strlen($string); $x++ )
        {
            $arr[$x] = hash( $algo1,$key . hash($algo2,$string[$x] . $key ) );
            //debug($arr[$x],'$arr[$x]');

            //Склеиваем символы
            $newStr .= $arr[$x][7].$arr[$x][4].$arr[$x][9].$arr[$x][2];
        }
        //debug($newStr,'$newStr');
        return $newStr;
    }
    protected static function schoolBoyDecode( string $encoded ) : string
    {
        $key = static::$secretKey;
        $algo1 = static::$algo1;
        $algo2 = static::$algo2;
        //Символы, из которых состоит base64-ключ
        $strOfSym="qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM=";
        for ( $x = 0; $x < strlen($strOfSym); $x++ )
        {
            // шифруем каждый символ
            //Хеш, который соответствует символу, на который его заменят.
            $tmp = hash($algo1, $key . hash($algo2,$strOfSym[$x] . $key) );
            //Заменяем №3,6,1,2 из хеша на символ
            $encoded = str_replace($tmp[7].$tmp[4].$tmp[9].$tmp[2], $strOfSym[$x], $encoded);
            //debug($encoded,'$encoded');
        }
        return base64_decode($encoded);
    }

    /**
     * OPENSSL
     * @param $plaintext
     * @return string
     */
    protected static function opensslEncrypt( string $plaintext ) : string
    {
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $cipherText_raw = openssl_encrypt($plaintext, $cipher, static::$ENCRYPTION_KEY, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $cipherText_raw, static::$ENCRYPTION_KEY, $as_binary=true);
        return base64_encode( $iv.$hmac.$cipherText_raw );
    }
    protected static function opensslDecrypt( string $cipherText ) : string
    {
        $c = base64_decode($cipherText);
        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len=32);
        $cipherText_raw = substr($c, $ivlen+$sha2len);
        $plaintext = openssl_decrypt($cipherText_raw, $cipher, static::$ENCRYPTION_KEY, $options=OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $cipherText_raw, static::$ENCRYPTION_KEY, $as_binary=true);
        if (hash_equals($hmac, $calcmac))
        {
            return $plaintext;
        }
        return false;
    }


	public static function strEncode( string $plaintext ) : string
    {
        switch (static::$algorithm)
        {
            case 1:
                return self::schoolBoyEncode( $plaintext );
                break;
            case 2:
                return self::opensslEncrypt( $plaintext );
                break;
            default:
                return self::opensslEncrypt( $plaintext );
        }
    }

    public static function strDecode( string $cipherText ) : string
    {
        switch (static::$algorithm)
        {
            case 1:
                return self::schoolBoyDecode( $cipherText );
                break;
            case 2:
                return self::opensslDecrypt( $cipherText );
                break;
            default:
                return self::opensslDecrypt( $cipherText );
        }
    }
}