<?php
namespace models;

//ini_set('max_execution_time',600); //10min // макс. время выполнения скрипта в секундах
//ini_set('memory_limit','256M'); // -1 = может использовать всю память, устанавливается в байтах

class PDFExports extends Kits 
{

    protected HufDB_PDF $pdf;
    public string $searchFor = '';
    public string $collectionName = '';
    public string $searchQuery = '';
    /**
     * массив с прогрессом создания пдф, для сокет сервера
     * @var array
     */
    public $progressResponse = [];

    /**
     * @var string
     * Имя пользов. который начал процесс создания ПДФ
     */
    public $userName = '';

    /**
     * @var string
     * Идентиф. вкладки с которой начат процесс создания ПДФ
     */
    public $tabID = '';

    /**
     * @var int
     */
    public $percent = 0;

    /**
     * 
     */
    public $socketClientResource;

    /**
     * PDFExports constructor.
     * @param string $searchQuery
     * @param string $searchFor
     * @param string $collectionName
     * @throws \Exception
     */
    function __construct( string $searchQuery='')
	{
		parent::__construct( $searchQuery ); //Kits Constructor
        $this->toPdf = true;

        $this->progressResponse = [
            'progressBarPercent' => 0,
            'user' => [
                'name'=> '',
                'tabID' => '',
            ],
            'message' => 'progressBarPDF' // флаг о том что идёт создание пдф
        ];

        $this->connectToDB();
	}

    public function setProgress($userName=false, $tabID=false)
    {
        $this->userName = is_string($userName)?$userName:'';
        $this->tabID = is_string($tabID)?$tabID:'';

        if ( empty($this->userName) || empty($this->tabID) ) return;

        $this->progressResponse['user'] = [
                'name'=> $this->userName,
                'tabID' => $this->tabID,
        ];


        // выключает сообщения об ошибках
        set_error_handler(function(){return true;});
        $this->socketClientResource = @stream_socket_client($this->localSocket, $errNo, $errorMessage);
        restore_error_handler();
    }

    public function progressCount($newPercent)
    {
        if ( !$this->socketClientResource )
            return;

        $this->progressResponse['progressBarPercent'] = $newPercent;

        fwrite($this->socketClientResource, json_encode($this->progressResponse));
    }

    protected function prepareCollExp()
    {
        $this->start = 0;
        $this->perPage = $this->totalModelsCount();
        $this->getModelsFormStock();

        
        $this->setProgress( $this->request->post('userName'), $this->request->post('tabID') );

        /**
        *  create new PDF document
        */
        $pdf = $this->pdf = new HufDB_PDF('L', 'mm', 'A4', true, 'UTF-8', false, headerName: $this->headerName());

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('');
        $pdf->SetTitle('');
        $pdf->SetSubject('');
        $pdf->SetKeywords('');

        // set default header data
        //$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
        $pdf->setFooterData(array(0,64,0), array(0,64,128));

        // set header and footer fonts
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(10, 10, 5, 10); // это отступы до самого контента минуя хидер и футер
        $pdf->SetHeaderMargin(7); // отступ от верхнего края до хидера
        $pdf->SetFooterMargin(5); // отступ от нижнего края до футера

        // set auto page breaks
        $pdf->SetAutoPageBreak(false, 10);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        $langFile = _vendorDIR_ .'tecnickcom/tcpdf/examples/lang/rus.php';
        if (@file_exists($langFile)) 
        {
            $l = [];
            require_once($langFile);
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.

        $pdf->SetFont('dejavusans', '', 10, '', true);
        // set text shadow effect
        $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

        $pdf->setJPEGQuality(75);
        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->SetFillColor(239, 238, 210);

        $pdf->AddPage();
        //$pdf->Rect(10, 10, 140, 170, 'D', array(), array());
    }

    public function collectionExport()
    {
        $this->prepareCollExp();

        $pdf = $this->pdf;

        //------------исходные данные----------------//
        $style2 = array('width' => 0.4, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0));
        $style_Vert = array('width' => 0.4, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0));

        $complects = $this->countComplects();
        $complectsCount = count($complects); // общее кол-во комплектов

        $det_name = "";
        $complectCounter = 0;
        $pageIter = 1;         // счетчик отрисованных страниц
        $pageRowsIter = 0;     // счетчик отрисованных строк на всей странице
        $complIterShow = 0;
        $max_RowsPerPage = 8;
        $max_RowsPer_Half_Page = 4;
        $max_ImagesPerRow = 4;
        
        // проверять псле каждой отрисованной картинки не заходит ли она за max_ImagesPerRow
        $W_IMG = 34.5; // ширина картинки
        $H_IMG = 34.5; // высота картинки
        
        $Xcell = 11;
        $Info_Cells_X = array($Xcell,$Xcell+10,$Xcell+97); //начальные коорд. 3х ячеек по Х
        $Ycell = 10; //начальные коорд. ячеек по Y
        
        $X_line_bott = 10; //начальные коорд. нижней линии по Х
        $Y_line_bott = 10; //начальные коорд. нижней линии по Y
        
        $X_Img = 11; //начальные коорд. картинки по Х
        $Y_Img = 17; //начальные коорд. картинки по Y
        //------------конец исходные данные----------------//



        /**
         * верхний (главный) цикл - отрисовывает строки и страницы
         */
        for ( $i = 0; $i < $complectsCount; $i++ ) // for ( $i = 0; $i <= $complectsCount; $i++ ) - old
        {
            //if(connection_status() != CONNECTION_NORMAL) exit;

            if (!$complects[$i]['modeller3D']) continue;
            
            if ( $pageRowsIter == $max_RowsPer_Half_Page ) { // печатаем на след полустранице
                $this->nextPage_HalfPage( $Xcell, $Ycell, $X_line_bott, $Y_line_bott, $X_Img, $Y_Img, $Info_Cells_X, false, true );
                $pdf->Line(150.5,10,150.5,180, $style_Vert);
            }
            
            if ( $pageRowsIter == $max_RowsPerPage ) { // печатаем на след. странице
            
                $this -> nextPage_HalfPage( $Xcell, $Ycell, $X_line_bott, $Y_line_bott, $X_Img, $Y_Img, $Info_Cells_X, true );
            
                $pdf->AddPage();
                $pdf->Line(150.5,10,150.5,180, $style_Vert);
                $pageIter++;
                $pageRowsIter = 0;
            }

            $model_Img_Iter = 0; // сбрасываем кол-во отрисованных картинок
            
            $thisVC = !empty($complects[$i]['vendor_code']) ? " - Арт: {$complects[$i]['vendor_code']}" : "";
            $complIterShow++; // счетчик/номер слева вверху строки
            
            $pdf->MultiCell(10, '', $complIterShow.'.',                               0, 'L', 1, 0, $Info_Cells_X[0], $Ycell, true);
            $pdf->MultiCell(87, '', '№3D: '.$complects[$i]['number_3d'].''.$thisVC,    0, 'C', 1, 0, $Info_Cells_X[1], $Ycell, true);
            $pdf->MultiCell(42, '', $complects[$i]['modeller3D'], 0, 'R', 1, 0, $Info_Cells_X[2], $Ycell, true);
            
            //достаем модели из комплекта
            $modelTypes = $complects[$i]['model_type'];
            
            foreach( $modelTypes as $modelId => $imgDopVC )
            { 
                $det_name = "";
                // $imgDopVC['images'] массив картинок
                $modWeight = $imgDopVC['model_weight'];
                $modStatus = $imgDopVC['status'];
                
                if ( $imgDopVC['model_type'] == 'Серьги' ) {
                    foreach( $imgDopVC['dop_VC'] as $k => $v ){
                        $det_name = $k;
                    }
                    unset($k,$v);
                    $det = $imgDopVC['dop_VC']['Швенза']??"";
                }
                
                if ( $imgDopVC['model_type'] == 'Пусеты' ) {
                    foreach( $imgDopVC['dop_VC'] as $k => $v ){
                        $det_name = $k;
                    }
                    unset($k,$v);
                    $det = $imgDopVC['dop_VC']['Закрутка']??"";
                }
                
                // рисуем картинки для каждой модели
                for ( $im_c = 0; $im_c < count($imgDopVC['images']); $im_c++ ) {
                    
                    if ( $model_Img_Iter == $max_ImagesPerRow ) { // переходим на след строку когда картинок ==4 
                        $X_Img = ($pageRowsIter >= 4) ? 152 : 11;
                        $Y_Img += 42.5;
                        $Y_line_bott += 42.5;
                        $Ycell += 42.5;

                        $pageRowsIter++;
                        $model_Img_Iter = 0;
                    }
                    if ( $pageRowsIter == $max_RowsPer_Half_Page ) { // печатаем на след полустранице
                        $Xcell = 151;
                        $Info_Cells_X = array($Xcell,$Xcell+10,$Xcell+10+87); // координаты MultiCell по X
                        $Ycell = 10; // координаты MultiCell по Y
                        $Y_Img = 17;
                        $X_line_bott = 150.5;
                        $Y_line_bott = 10;
                    }
            
                    if ( $pageRowsIter == $max_RowsPerPage ) { // печатаем на след. странице
                        $this->nextPage_HalfPage( $Xcell, $Ycell, $X_line_bott, $Y_line_bott, $X_Img, $Y_Img, $Info_Cells_X, true );
                        $pdf->AddPage();
                        $pageIter++;
                        $pageRowsIter = 0;
                    }

                    $_src = $complects[$i]['number_3d'].'/'.$complects[$i]['model_type'][$modelId]['id'].'/images/'.$imgDopVC['images'][$im_c];
                    $img_src = file_exists(_stockDIR_.$_src) ? _stockDIR_HTTP_.$_src : _stockDIR_HTTP_."default.jpg";

                    $pdf->Image($img_src, $X_Img, $Y_Img, $W_IMG, $H_IMG, '', '', '', true, 150, '', false, false, 0, true, false, false);
                    $X_Img += $W_IMG;
                    
                    // меняем шрифт для вывода швенз
                    $pdf->SetFont('dejavusans', '', 8, '', true); 
                    // выводим швензу или закрутку если есть
                    if ( isset($det) && $im_c == 0 ) { 
                        $pdf->MultiCell(30, '', $det_name.': '.$det, 0, 'C', 0, 0, $X_Img-$W_IMG, $Y_Img+23, true);
                    }
                    //выведем статус
                    //$pdf->SetFillColor(255, 255, 233);
                    $pdf->MultiCell(35, '', $modStatus, 0, 'R', 0, 0, $X_Img-$W_IMG, $Y_Img+4, true);
                    
                    // меняем шрифт для ВЕС!!!
                    $pdf->SetFont('dejavusans', '', 9, '', true); 
                    // выведем вес
                    $pdf->MultiCell(30, '', 'Вес: '.$modWeight, 0, 'L', 0, 0, $X_Img-$W_IMG, $Y_Img, true);
                    
                    
                    $pdf->Line($X_Img,$Y_Img,$X_Img,$Y_Img+34.5, $style_Vert);
                    
                    $model_Img_Iter++; // посчитали картинку
                }
                
                if ( isset($det) ) unset($det,$det_name);
            }
            $pdf->SetFont('dejavusans', '', 10, '', true);// меняем шрифт обратно
            //нарисовали линию внизу комплекта
            $pdf->Line($X_line_bott,$Y_line_bott+42.5,$X_line_bott+140.5,$Y_line_bott+42.5, $style2);
            
            // добавили коорд. для след. комплекта
            $Y_line_bott += 42.5;
            $Ycell += 42.5;
            $Y_Img += 42.5;
            $X_Img = ($pageRowsIter >= 4) ? 152 : 11; // смотрит на какой части стр. рисовать след картинку

            $pageRowsIter++;

            // посчитали отрисованный комплект
            $complectCounter++;
            //$complectProgress = ceil( ( $complectCounter * 100 ) / $complectsCount );

            $this->progressCount( ceil( ( $complectCounter * 100 ) / $complectsCount ) );

        } // END верхний (главный) цикл
        
    }

    public function output(string $fileName ='') : void
    {
        $pdfFileName = $this->translit($this->headerName()) . '_' . date('d-m-Y') . '.pdf';
        //$pdf->Output($pdfFileName, 'D');
        $pdfBin = $this->pdf->Output('pdfname.pdf', 'S');
        file_put_contents( _webDIR_ . 'Pdfs/'.$pdfFileName, $pdfBin);

        //=============Last counter point==============//
        $this->progressCount(100);
        //=============Last counter point==============//
        //echo json_encode($pdfFileName);
        exit( json_encode($pdfFileName) );
    }

    public function headerName() : string
    {
        //$session = $this->session;
        $assist = $this->session->assist;
        $trans_str = '';
        if ( (int)$assist['collection_id'] > -1 )
        {
            $trans_str = $assist['collectionName']; //$assist['collectionName']
        } else {
            //костыль. если ищем по дате
            $trans_str = $this->session->getKey('searchFor');
            //$trans_str = str_ireplace("::", "", $this->session->getKey('searchFor') );
        }

        if ( empty($trans_str) ) 
            $trans_str = 'Выделенное_';
        /*
        debugAjax($session->getAll(),'$session');
        debugAjax($session->getKey('collection_id'),'$collection_id');
        debugAjax($this->collectionName,'$collectionName');
        debugAjax($trans_str,'$trans_str',END_AB);
        */

        return $trans_str;
    }

	protected function get_Images_FromPos($id)
    {
		$images_src = array();
        $img_quer = $this->findAsArray(" SELECT img_name, main, detail FROM images WHERE pos_id='$id' ");
		
        foreach ($img_quer as $row_images)
        {
			if ( $row_images['main'] == '1' || $row_images['detail'] == '1' ) {
				$images_src[] = $row_images['img_name'];
			}
		}
		
		return $images_src;
	}
	
	protected function get_DopVC_FromPos($id)
    {
		$dopVC_src = array();
		//$dopVC_quer = mysqli_query($this->connection, " SELECT vc_names, vc_3dnum FROM vc_links WHERE pos_id='$id' ");
        $dopVC_quer = $this->findAsArray(" SELECT vc_names, vc_3dnum FROM vc_links WHERE pos_id='$id' ");
		
		//while ( $dopVC_rows = mysqli_fetch_assoc($dopVC_quer) ){
        foreach ($dopVC_quer as $dopVC_rows)
        {
			if ( $dopVC_rows['vc_names'] == 'Швенза' || $dopVC_rows['vc_names'] == 'Закрутка' ) {
				$dopVC_src[$dopVC_rows['vc_names']] = $dopVC_rows['vc_3dnum'];
			}
			
		}
		
		return $dopVC_src;
	}
	
	public function nextPage_HalfPage( &$Xcell, &$Ycell, &$X_line_bott, &$Y_line_bott, &$X_Img, &$Y_Img, &$Info_Cells_X, $nextPage=false, $nextHalfPage=false ) {
		if ( $nextHalfPage === true ) {
			
			$Xcell = 151;
			$Info_Cells_X = array($Xcell,$Xcell+10,$Xcell+10+87); // координаты MultiCell по X
			$Ycell = 10; // координаты MultiCell по Y
			$X_Img = 152;
			$Y_Img = 17;
			$X_line_bott = 151;
			$Y_line_bott = 10;
			
		}
		
		if ( $nextPage === true ) {
			
			$Xcell = 11;
			$Info_Cells_X = array($Xcell,$Xcell+10,$Xcell+10+87);
			$Ycell = 10;
			$X_Img = 11;
			$Y_Img = 17;
			$X_line_bott = 10;
			$Y_line_bott = 10;

		}

	}

}