<?php
/**
 * Date: 16.08.2021
 * Time: 21:46
 */

namespace Views\_PreviewMaker\Models;

use Views\_Globals\Models\General;
use Views\_Globals\Models\ProgressCounter;
use Views\_Globals\Models\User;
use Views\_SaveModel\Models\ImageConverter;
use Views\vendor\core\Files;


class PreviewMaker extends General
{

    /**
     * PreviewMaker constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->connectToDB();
    }


    protected $totalImages = 0;
    protected $currentImagesCount = 0;
    /**
     * @return array
     * @throws \Exception
     */
    public function getImageNames()
    {

        $sql = " SELECT img.img_name as imgName, img.pos_id, s.number_3d  
                 FROM images as img
                    LEFT JOIN stock as s ON s.id = img.pos_id";

        $arr = $this->findAsArray($sql);

        $images = [];

        foreach ( $arr as $image )
        {
            $images[$image['pos_id']]['names'][] = $image['imgName'];
            $images[$image['pos_id']]['pos_id'] = $image['pos_id'];
            $images[$image['pos_id']]['number_3d'] = $image['number_3d'];
            $this->totalImages++;
        }

        return $images;
    }


    /**
     * @param array $images
     * @param ProgressCounter $progressCounter
     * @return void
     * @throws \Exception
     */
    public function proceed( array $images, ProgressCounter &$progressCounter ) : void
    {

        $id = $images['pos_id'];
        $num3D = $images['number_3d'];

        $path = $num3D . '/' . $id . '/images/';
        $fullPath = _stockDIR_ . $num3D . '/' . $id . '/images/';

        foreach ( $images['names'] as $imgName )
        {
            $this->currentImagesCount++;
            if ( !file_exists($fullPath . $imgName) )
                continue;

            $mess = '';
            /*
            if (ImageConverter::makePrev($path,$imgName))
            {
                $mess = '.............Preview created!';
            } else {
                $mess = '.............Preview not created!';
            }
            */

            /*
            //
            if (ImageConverter::optimizeUpload($path.$imgName))
            {
                $mess = '.............image optimized!';
            } else {
                $mess = '.............im not touch it!';
            }
            */

            $overallProgress = ceil(( $this->currentImagesCount * 100 ) / $this->totalImages);
            $progressCounter->progressResponse['message']['progressMessage'] = 'Файл: ' . $path . $imgName . $mess;
            $progressCounter->progressResponse['message']['progressBarPercent'] = $overallProgress;
            $progressCounter->progressCount($overallProgress);
        }
    }

    /**
     * @param string $tabID
     * @return string
     * @throws \Exception
     */
    public function startOperation( string $tabID )
    {
        $startTime = time();
        $progressCounter = new ProgressCounter();
        $progressCounter->progressResponse['message'] = [];
        $progressCounter->progressResponse['message']['prevMakerData'] = 1;
        $progressCounter->setProgress(User::getFIO(), $tabID);
        $progressCounter->progressResponse['user'] = User::getFIO();

        $allImages = $this->getImageNames();
        foreach ( $allImages as $modelImages )
        {
            $this->proceed($modelImages, $progressCounter);
        }

        sleep(0.5);

        $endTime = time();
        $totalSpendTime = $endTime - $startTime;

        $progressCounter->progressResponse['message']['progressMessage'] = 'Обработано ' . $this->currentImagesCount . ' файлов. Затрачено: ' . timeElapsed($totalSpendTime);
        $progressCounter->progressResponse['message']['progressBarPercent'] = 100;
        $progressCounter->progressCount(100);

        return "DONE! " . $tabID;
    }


}