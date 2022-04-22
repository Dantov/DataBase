<?php
namespace Views\_Main\Controllers;
use Views\_Globals\Models\SelectionsModel;
use Views\_Globals\Models\User;
use Views\_Main\Models\{PDFExports,HufDB_PDF,Search};
use Views\vendor\core\Sessions;

ini_set('max_execution_time',600); //10min // макс. время выполнения скрипта в секундах
ini_set('memory_limit','256M'); // -1 = может использовать всю память, устанавливается в байтах

$session = new Sessions();
$assist = $session->getKey('assist');
$user = $session->getKey('user');
$sMode = $session->getKey('selectionMode');
$collectPDF = null;

try {
    if ( $session->hasKey('searchFor') && !$sMode['showModels'] ) //|| $session->getKey('re_search')
    {
        $search = new Search();
        $foundRows = $search->search( $session->getKey('searchFor') );
        $collectPDF = new PDFExports( $assist, $user, $foundRows, $session->getKey('searchFor'), $assist['collectionName'] ); // Non searchFor because i don't wanna to name file from search input

    } elseif ( $sMode['showModels'] ) {
        $collectPDF = new PDFExports( $assist, $user,  (new SelectionsModel($session))->getSelectedModels() );
    } else {
        $collectPDF = new PDFExports( $assist, $user, [], '', $assist['collectionName'] );
    }
} catch (\Exception | \Error $e) {
    exit( json_encode(['error'=>$e->getMessage(), 'code'=>$e->getCode()]) );
}

if ( $collectPDF && empty($collectPDF->getRow()) ) $collectPDF->getModelsFormStock();
$complects = $collectPDF->countComplects();
$collectPDF->closeDB();

/**
 * @var $complectsCount - общее кол-во комплектов
 */
$complectsCount = count($complects); // общее кол-во комплектов

$collectPDF->setProgress($_POST['userName'], $_POST['tabID']);

$headerNameString = $collectPDF->headerNameString();
$pdfFileName = $collectPDF->translit($headerNameString) . '_' . date('d-m-Y') . '.pdf';


/**
 *  create new PDF document
 */
$pdf = new HufDB_PDF('L', 'mm', 'A4', true, 'UTF-8', false, $headerNameString);

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
//$pdf->setHeaderFont(Array(PDF_FONT_NAME_collectPDF, '', PDF_FONT_SIZE_collectPDF));
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
if (@file_exists(_vendorDIR_ .'/TCPDF/rus.php')) {
    $l = [];
    require_once( _vendorDIR_ .'/TCPDF/rus.php');
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
	$style2 = array('width' => 0.4, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0));
	$style_Vert = array('width' => 0.4, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 0, 0));
	
	$pdf->AddPage();
	//$pdf->Rect(10, 10, 140, 170, 'D', array(), array());



	//------------исходные данные----------------//
	$complectCounter = 0;
	$pageIter = 1;         // счетчик отрисованных страниц
	$pageRowsIter = 0;     // счетчик отрисованных строк на всей странице
	//$modelsIter = 0;       // счетчик отрисованных моделей в комплекте
	//$model_Img_Iter = 0;   // счетчик отрисованных картинок для всех моделей в строке

    $complIterShow = 0;

	$max_RowsPerPage = 8;
	$max_RowsPer_Half_Page = 4;
	
	//$max_modelsPerRow = 4;
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

    $det_name = null;

/**
 * верхний (главный) цикл - отрисовывает строки и страницы
 */
	for ( $i = 0; $i < $complectsCount; $i++ ) // for ( $i = 0; $i <= $complectsCount; $i++ ) - old
	{
        if(connection_status() != CONNECTION_NORMAL) exit;

		if (!$complects[$i]['modeller3D']) continue;
		
		if ( $pageRowsIter == $max_RowsPer_Half_Page ) { // печатаем на след полустранице
			$collectPDF -> nextPage_HalfPage( $Xcell, $Ycell, $X_line_bott, $Y_line_bott, $X_Img, $Y_Img, $Info_Cells_X, false, true );
			$pdf->Line(150.5,10,150.5,180, $style_Vert);
		}
		
		if ( $pageRowsIter == $max_RowsPerPage ) { // печатаем на след. странице
		
			$collectPDF -> nextPage_HalfPage( $Xcell, $Ycell, $X_line_bott, $Y_line_bott, $X_Img, $Y_Img, $Info_Cells_X, true );
		
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
			// $imgDopVC['images'] массив картинок
			$modWeight = $imgDopVC['model_weight'];
			$modStatus = $imgDopVC['status'];
			
			if ( $imgDopVC['model_type'] == 'Серьги' ) {
				foreach( $imgDopVC['dop_VC'] as $k => $v ){
					$det_name = $k;
				}
				unset($k,$v);
				$det = $imgDopVC['dop_VC']['Швенза'];
			}
			
			if ( $imgDopVC['model_type'] == 'Пусеты' ) {
				foreach( $imgDopVC['dop_VC'] as $k => $v ){
					$det_name = $k;
				}
				unset($k,$v);
				$det = $imgDopVC['dop_VC']['Закрутка'];
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
					$collectPDF->nextPage_HalfPage( $Xcell, $Ycell, $X_line_bott, $Y_line_bott, $X_Img, $Y_Img, $Info_Cells_X, true );
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

        $collectPDF->progressCount( ceil( ( $complectCounter * 100 ) / $complectsCount ) );

	} // END верхний (главный) цикл
	
	
	//$pdf->Output($pdfFileName, 'D');
	$pdf_string = $pdf->Output('pdfname.pdf', 'S');
	file_put_contents( _rootDIR_ . 'Pdfs/'.$pdfFileName, $pdf_string);

	//=============Last counter point ==============//
    $collectPDF->progressCount(100);
	//=============Last counter point ==============//
echo json_encode($pdfFileName);