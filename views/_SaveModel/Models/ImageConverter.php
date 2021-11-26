<?php
/**
 * Date: 13.08.2021
 * Time: 16:18
 */

namespace Views\_SaveModel\Models;
use Views\vendor\core\Files;

/**
 * ImageConverter - уменьшает, сжимает картинки
 * использует MagickConverter
 */
class ImageConverter
{

    /** Params  */
    const prevPostfix = "_prev";

    protected static $lastPreviewImgName;
    public static $imgName;

    public static $resize = false;
    public static $resizePercent = 30;
    public static $strip = true;
    public static $quality = false;
    public static $qualityValue = 85;

    public function __construct()
    {
        //parent::__construct();
    }


    public static function getImgPrevPostfix() : string
    {
        return self::prevPostfix;
    }

    /**
     * примет массив с параметрами для следующей конвертации
     * @param array $params
     * example
     *  [
     *     'resize' => 25,
     *     'strip' => true,
     *     'quality' => 55,
     * ];
     */
    public static function setConvertParams( array $params = [] ) : void
    {
        if ( empty($params) ) return;
    }

    protected static function resetConvertParams()
    {
        self::$resize = false;
        self::$resizePercent = 30;
        self::$strip = true;
        self::$quality = false;
        self::$qualityValue = 85;
    }


    public static function getConvertParams() : array
    {
        $result = [
            'resize' => self::$resize,
            'resizePercent' => self::$resizePercent,
            'strip' => self::$strip,
            'quality' => self::$quality,
            'qualityValue' => self::$qualityValue,
        ];

        return $result;
    }

    public static function getLastImgPrevName()
    {
        return self::$lastPreviewImgName;
    }

    protected static function makeCommand( string $imgOriginPath, string $imgPrevPath ) : string
    {
        $imgOriginPath = '"' . escapeshellcmd($imgOriginPath) . '"';
        $imgPrevPath =  '"' . escapeshellcmd($imgPrevPath) . '"';

        $resize = self::$resize ? ' -resize ' . self::$resizePercent . '% '  : '';
        $strip = self::$strip ? ' -strip ':'';
        $quality = self::$quality ? ' -quality ' . self::$qualityValue . ' ' : '';

        $command = 'convert ' . $imgOriginPath . $resize . $strip . $quality . $imgPrevPath;
        //debugAjax($command,"command",END_AB);

        self::resetConvertParams();
        return $command;
    }

    /**
     * @param string $pathOrigin - Не полный путь к файлу!!! Начинается с №3Д
     * @param string $imgName
     * @return bool
     * @throws \Exception
     */
    public static function makePrev(string $pathOrigin, string $imgName = "" ) : bool
    {
        if (empty($imgName))
        {
            if ( self::$imgName ) {
                $imgName = self::$imgName;
            } else {
                return false;
            }
        }
        if ( empty($pathOrigin) ) return false;

        // перед тем как создавать превью, нужно узнать размер ориген. файла и его разрешение.
        // если он не большого размера, то превью не нужна
        if ( !self::setConvParamsForPreview(_stockDIR_.$pathOrigin.$imgName) )
            return false;

        $ext = pathinfo($imgName, PATHINFO_EXTENSION);
        $imgBaseName = pathinfo($imgName, PATHINFO_FILENAME); // вернет имя файла без расширения

        $imgOriginPath = _stockDIR_ . $pathOrigin . $imgName;
        $lastPreviewImgName = $imgBaseName . self::prevPostfix . '.' . $ext;
        $imgPrevPath = _stockDIR_ . $pathOrigin . $lastPreviewImgName;

        $output=null;
        $retVal=null;

        $c = self::makeCommand($imgOriginPath, $imgPrevPath);
        exec( $c,$output,$retVal );
        if ( $retVal ) return false;

        self::$lastPreviewImgName = $lastPreviewImgName;
        return true;
    }

    /**
     * @param string $fullPath - Не полный путь к файлу!!! Начинается с №3Д
     * @return bool
     * @throws \Exception
     */
    public static function optimizeUpload( string $fullPath ) : bool
    {

        if ( empty($fullPath) ) return false;

        if ( !self::setConvParamsForUploadedImg(_stockDIR_.$fullPath) )
            return false;

        $c = self::makeCommand(_stockDIR_.$fullPath, _stockDIR_.$fullPath);
        exec( $c,$output,$retVal );
        if ( $retVal ) return false;

        return true;
    }

    /**
     * Задает параметры конвертауии дял превьюшки
     * getimagesize = Array
     * (
     * [0] => 1080
     * [1] => 919
     * [2] => 2
     * [3] => width="1080" height="919"
     * [bits] => 8
     * [channels] => 3
     * [mime] => image/jpeg
     * )
     * @param string $pathOrigin - полный путь к файлу
     * @return bool
     * @throws \Exception
     */
    protected static function setConvParamsForPreview( string $pathOrigin ) : bool
    {
        $result = false;

        $files = Files::instance();

        /** проверим mime-type для указанного файла */
        $mimeType = $files->getFileMimeType($pathOrigin);

        if ( mb_stripos($mimeType, 'image') === false )
            return false;

        /** проверим разрешение картинки, если картинка болшая - поставим параметр 'resize' в exec комманду */
        $imgInfo = getimagesize($pathOrigin);
        $imgWidth = (int)$imgInfo[0];
        $imgHeight = (int)$imgInfo[1];

        $totalPixels = $imgWidth * $imgHeight;
        if ( $totalPixels > 160000 ) // 300 x 300
        {
            self::$resize = true; // добавим параметр "resize" в exec комманду
            // уменьшим по большей стороне до 300 пикс, и высчитаем процент на который уменьшать
            $highest = $imgWidth > $imgHeight ? $imgWidth : $imgHeight;
            $coefficient = round($highest / 300,2); // target 300px
            self::$resizePercent = (int)(100 / $coefficient);

            $result = true;
        }

        /** Выставим качество превьюшки, в зависимости от размера оригинала */
        $size = $files->getFileSize($pathOrigin, 'kb', 1);
        if ( $size > 100 )
        {
            self::$quality = true; // добавим параметр "качество" в exec комманду

            if ( $size > 100 && $size < 300  )
                self::$qualityValue = 75;
            if ( $size > 300 && $size < 700  )
                self::$qualityValue = 65;
            if ( $size > 700 )
                self::$qualityValue = 50;

            $result = true;
        }

        return $result;
    }

    /**
     * Задает параметры конв. для только что загружаемой картинки
     * @param string $pathOrigin - полный путь к файлу
     * @return bool
     * @throws \Exception
     */
    protected static function setConvParamsForUploadedImg( string $pathOrigin ) : bool
    {
        $result = false;

        $files = Files::instance();

        /** проверим mime-type для указанного файла */
        $mimeType = $files->getFileMimeType($pathOrigin);

        if ( mb_stripos($mimeType, 'image') === false )
            return false;

        $size = $files->getFileSize($pathOrigin, 'kb', 1);
        if ( $size > 150 )
        {
            self::$quality = true; // добавим параметр "качество" в exec комманду

            if ( mb_stripos($mimeType, 'jp') || mb_stripos($mimeType, 'webp') || mb_stripos($mimeType, 'gif') )
            {
                if ( $size > 150 && $size < 500  )
                    self::$qualityValue = 70;
                if ( $size > 500  )
                    self::$qualityValue = 60;

            } elseif ( mb_stripos($mimeType, 'png') ) {
                // png не ужимается сильно, но если выставить quality близкое к 100, немного ужмется
                // png можно конвертить в jpg!
                self::$qualityValue = 100;
            }

            $result = true;
        }

        return $result;
    }





    /**
     * Поока не нужно
     * @param array $totalImages
     * @return string
     */
    public static function findDecentImg( array &$totalImages ) : string
    {
        if (empty($totalImages)) return "";

        $imgName = "";
        $foundOne = false;
        foreach ( $totalImages as $tImage )
        {
            if ( $tImage['main'] == 1 )
            {
                $imgName = $tImage['img_name'];
                $foundOne = true;
                break;
            }
            if ( $tImage['sketch'] == 1 )
            {
                $imgName = $tImage['img_name'];
                $foundOne = true;
            }
        }
        if ( !$foundOne ) $imgName = $totalImages[0]['img_name'];

        return self::$imgName = $imgName;
    }


}