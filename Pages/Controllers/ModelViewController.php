<?php
namespace controllers;

use models\{
    HandlerPrices, PushNotice, User, 
    ModelView, DocumentPDF, Statuses
};
use libs\classes\AppCodes;


class ModelViewController extends GeneralController
{

    public string $title = 'ХЮФ 3Д Модель - ';
    public $stockID = null;

    /**
     * @throws \Exception
     */
    public function beforeAction()
    {
        $request = $this->request;
        if ( $request->isAjax() )
        {
            try
            {
                if ( (int)$request->post('zipExtract') === 1 )
                    $this->actionExtractStlFiles();
                if ( (int)$request->post('zipDelete') === 1 )
                    $this->actionDellStlFiles();
                if ( (int)$request->post('zipDelete') === 1 )
                    $this->actionDellStlFiles();

                if ( $this->getQueryParam('document-pdf') )
                {
                    ini_set('max_execution_time', 180); // макс. время выполнения скрипта в секундах
                    ini_set('memory_limit','256M'); // -1 = может использовать всю память, устанавливается в байтах

                    $docPdf = new DocumentPDF($request->post('id'), $request->post('userName'), $request->post('tabID'), $request->post('document'));

                    if ( $request->post('document') === 'passport' )
                    {
                        $docPdf->printPassport();
                        $fileName = $docPdf->exportToFile('passport');

                        echo json_encode($fileName);
                    }
                    if ( $request->post('document') === 'runner' )
                    {
                        $docPdf->printRunner();
                        $fileName = $docPdf->exportToFile('runner');

                        echo json_encode($fileName);
                    }
                    if ( $request->post('document') === 'both' )
                    {
                        $docPdf->printPassport();
                        $docPdf->printRunner();
                        $fileName = $docPdf->exportToFile('passportRunner');

                        echo json_encode($fileName);
                    }
                    if ( $request->post('document') === 'picture' )
                    {
                        $docPdf->printPicture( (int)$request->post('pictID') );
                        $fileName = $docPdf->exportToFile('picture');
                        echo json_encode($fileName);
                    }
                }

                if ( $this->getQueryParam('approve') )
                    if (  trueIsset($request->post('approve')) && trueIsset($request->post('id')) )
                        $this->approves($request->post('approve'), $request->post('id'));

            } catch ( \TypeError | \Error | \Exception $e) {
                $this->serverError_ajax($e);
            }

            exit;
        }

        $id = (int)$this->getQueryParam('id');
        if ( $id <= 0 || $id > PHP_INT_MAX ) $this->redirect('/main/');
        $this->stockID = $id;
    }

    /**
     * @throws \Exception
     */
    public function action()
    {
        $id = $this->stockID;
        $modelView = new ModelView($id);

        if (!$modelView->checkID($id)) $this->redirect('/');

        $row = $modelView->row;
        $this->title .= $row['number_3d'] ." ". $row['model_type'];

        $coll_id = $modelView->getCollections();

        $button3D = '';
        if ( $stl_file = $modelView->getStl() )
        {
            $modelView->removeOldStl();
            $button3D = $stl_file;
            // ПРИМЕР!!
            //$path = _webDIR_HTTP_ . 'js_lib/';
            //$this->includeJSFile( 'three.min.js', ['path'=> $path] );
            $this->includePHPFile('3DViewPanels.php');
        }
        $ai_file = $modelView->getAi();
        $rhino_file = $modelView->get3dm();

        $matsCovers = $modelView->getModelMaterials();
        $complectes = $modelView->getComplectes();
        //debug($complectes,'$complectes',1);
        $images = $modelView->getImages();

        $mainImg = $modelView->choseMainImage($images);

        // Чтобы вызывать этот медод из Вида, (видимо несколько раз в цикле) и не передавать туда всю модель
        // создадим такой костыль
        $setPrevImg = function( $image ) use (&$modelView)
        {
            return $modelView->origin_preview_ImgSelect($image);
        };

        $usedInModels =$modelView->usedInModels();
        $descriptions = $modelView->getDescriptions();
        $labels = $modelView->getLabels();
        $gemsTR = $modelView->getGems();
        $dopVCTr = $modelView->getDopVC();
        $repairs = $modelView->getRepairs();
        $statuses = $modelView->getStatusesHistory();

        $currentStatus = $modelView->getStatus($row);
        if ( empty($currentStatus)  )
        {
            $s = new Statuses($id);
            $currentStatus = $s->findLastStatus();
            $s->updateStockStatus( $currentStatus );
        }

        $stat_name = $currentStatus['stat_name']??'';
        $stat_date = $currentStatus['stat_date']??'';
        $stat_class = $currentStatus['class']??'';
        $stat_title = $currentStatus['title']??'';
        $stat_glyphi = 'glyphicon glyphicon-' . ($currentStatus['glyphi']??'');

        $isStatusPresentTechJew = in_array_recursive(101,$statuses);
        $isStatusPresentDesign = in_array_recursive(89,$statuses);

        // смотрим отрисовывать ли нам кнопку едит
        $editBtn = $modelView->editBtnShow();

        $this->includeJSFile('show_pos_scrpt.js', ['defer','timestamp'] );
        $this->includeJSFile('imageViewer.js', ['timestamp'] );

        $imgEncode = json_encode($images,JSON_UNESCAPED_UNICODE);
        $imgJs = <<<JS
        window.addEventListener('load',function() {
          new ImageViewer($imgEncode).init();
        }, false);
JS;
        $this->includeJS($imgJs);

        $this->includePHPFile('imageWrapper.php');
        $this->includePHPFile('progressModal.php', path: _globDIR_);

        $appForSketch =  User::permission('paymentManager') && (int)$currentStatus['id'] === 35;
        $appFor3DTech =  User::permission('MA_techJew') && (int)$currentStatus['id'] === 1;
        if ( $appForSketch || $appFor3DTech )
        {
            $this->includePHPFile('approve_modal.php');
            $this->includeJSFile('approveBtns.js',['defer','timestamp']);
        }

        $compacted = compact([
            'id','row','coll_id','button3D','complectes','images','mainImg','setPrevImg', 'labels','gemsTR','dopVCTr','stat_name',
            'stat_date','stat_class','stat_title','stat_glyphi','statuses','ai_file','stl_file','editBtn', 'isStatusPresentDesign',
            'repairs', 'matsCovers','rhino_file','usedInModels','descriptions','currentStatus','isStatusPresentTechJew',
        ]);

        return $this->render('modelView', $compacted);
    }

    /**
     * @param string $string
     * @param int $length
     * @param bool $isLongStr
     * @return bool|string
     */
    public function cutLongNames(string $string, int $length = 20, bool $isLongStr = false )
    {
        if ( empty($string) ) return '';
        if ( $isLongStr && $length && $string ) return mb_strlen($string) > $length ? true : false;
        return mb_strlen($string) < $length ? $string : mb_substr($string,0,$length-3) . "...";
    }


    protected function actionExtractStlFiles()
    {
        $request = $this->request;
        $zip_name = $request->post('zip_name');
        $zip_id = (int)$request->post('zip_id');
        $zip_num3d = $request->post('zip_num3d');

        $resp_arr['done'] = false;
        if ( empty($zip_name) || empty($zip_id) || empty($zip_num3d) )
        {
            $resp_arr['errMessage'] = 'ExtractZip: Incoming data not valid.';
            echo json_encode($resp_arr);
            exit;
        }

        $path = $zip_num3d .'/'.$zip_id.'/stl/';
        $pathHTTP = _stockDIR_HTTP_.$path;
        $pathAbsolute = _stockDIR_.$path;
        $filePath = $pathAbsolute.$zip_name;
        if ( !file_exists($filePath) )
        {
            $resp_arr['errMessage'] = 'ExtractZip: Zip archive '.$filePath.' not found.';
            echo json_encode($resp_arr);
            exit;
        }

        $zip = new \ZipArchive();
        $res = $zip->open($filePath);
        if ( $res )
        {
            $zip->extractTo($pathAbsolute);

            $names = [];
            for ($i = 0; $i < $zip->numFiles; $i++) $names[$i] = $zip->getNameIndex($i);

            $resp_arr['names'] = $names;
            $resp_arr['zip_path'] = $pathHTTP;
            $resp_arr['done'] = true;

        } else {
            $resp_arr['errMessage'] = 'ExtractZip: Can\'t open zip archive.';
        }

        echo json_encode($resp_arr);
        exit;
    }

    protected function actionDellStlFiles()
    {
        $request = $this->request;
        $dell_names = $request->post('dell_name');
        if ( is_array($dell_names) && !empty($dell_names) )
        {
            $arr = [];
            foreach ( $dell_names as $name )
            {
                $stockPath = explode('/', $name);
                unset($stockPath[0],$stockPath[1],$stockPath[2],$stockPath[3]);
                $absPath = _stockDIR_ . implode('/',$stockPath);
                $fileName = basename($name);
                if ( file_exists($absPath) )
                {
                    unlink($absPath);
                    $arr['files'][] = $fileName;
                }
            }
            echo json_encode($arr);
        }
        exit;
    }

    /**
     * @param string $approve
     * @param int $id
     * @throws \Exception
     */
    protected function approves(string $approve, int $id )
    {
        $handler = new HandlerPrices($id);
        if (!$handler->checkID($id))
            exit(json_encode(['error' => AppCodes::getMessage(AppCodes::MODEL_DOES_NOT_EXIST)]));

        $pn = new PushNotice();

        //Дизайн утверждён (Худ. совет.)
        if ($approve === 'approveSketch') {

            if (!User::permission('paymentManager'))
                exit(json_encode(['error' => AppCodes::getMessage(AppCodes::NO_PERMISSION)]));
            if ( !$handler->isStatusPresent(89) && $handler->isStatusPresent(35) )
            {
                if ( $handler->addDesignPrices('designOK') !== -1 )
                {
                    $handler->updateStatus(89, User::getFIO());

                    $pn->addPushNotice($id, 2, null, null, null, null, 89, User::getFIO());
                    exit(json_encode(['success' => AppCodes::getMessage(AppCodes::PRICE_CREDITED)]));
                }
                exit(json_encode(['error' => AppCodes::getMessage(AppCodes::PRICE_NOT_CREDITED)]));
            }
        }

        //Подпись технолога (Занин)
        if ($approve === 'signByTech') {
            if (!User::permission('MA_techJew'))
                exit(json_encode(['error' => AppCodes::getMessage(AppCodes::NO_PERMISSION)]));

            if (!$handler->isStatusPresent(101) && $handler->isStatusPresent(89) )
            {
                if ( $handler->addTechPrices('SignedTechJew') !== -1 )
                {
                    $handler->updateStatus(101, User::getFIO());

                    $pn->addPushNotice($id, 2, null, null, null, null, 101, User::getFIO());
                    exit(json_encode(['success' => AppCodes::getMessage(AppCodes::PRICE_CREDITED)]));
                }
                exit(json_encode(['error' => AppCodes::getMessage(AppCodes::PRICE_NOT_CREDITED)]));
            }
        }

        exit(json_encode(['success' => AppCodes::getMessage(AppCodes::NOTHING_DONE)]));
    }

}