<?php
namespace models;

use vendor\tecnickcom\tcpdf\TCPDF;

//require_once _vendorDIR_. 'tecnickcom/tcpdf/tcpdf.php';
class DocumentPDF
{

    /**
     * @var \TCPDF
     */
	public $pdf;
    /**
     * @var ModelView
     */
	public $modelView;
	public $modelViewData = [];
	public $document = '';

    /**
     * @var ProgressCounter
     */
	public $progress;

	public $materialMM;
	public $executive;

	public $complects_lenght;
	public $complectCounter;

    /**
     * DocumentPDF constructor.
     * @param $id
     * @param $userName
     * @param $tabID
     * @param $document
     * @throws \Exception
     */
    public function __construct($id, $userName, $tabID, $document )
	{
		$id = (int)$id;
		if ( $id <= 0 || $id > 999999 ) exit('wrong ID');
		if ( !isset($userName) || !isset($tabID) ) exit( 'no user data');

        if ( trueIsset($document) ) $this->document =$document;
		$this->init($id, $userName, $tabID);
	}

    /**
     * @param $id
     * @param $userName
     * @param $tabID
     * @throws \Exception
     */
    public function init($id, $userName, $tabID)
	{
		$this->progress = new ProgressCounter();
	    $this->progress->setProgress($userName, $tabID);
	    $this->complects_lenght = 10;
	    $this->complectCounter = 0;

	    $modelView = new ModelView($id);
	    if ( $this->document === 'picture' )
        {
            $this->modelViewData['images'] = $modelView->getImages();
        } else {
            $this->modelViewData['coll_id'] = $modelView->getCollections();
            $this->modelViewData['matsCovers'] = $modelView->getModelMaterials();
            $this->modelViewData['images'] = $modelView->getImages();
            $this->modelViewData['gems'] = $modelView->getGems();
            $this->modelViewData['dopVC'] = $modelView->getDopVC();
            $this->modelViewData['repairs'] = $modelView->getRepairs();
            $this->modelViewData['complected'] = $modelView->getComplectes();
        }
        $this->modelViewData['row'] = $modelView->row;
        $this->modelViewData['date'] = date_create( $this->modelViewData['row']['date'] )->Format('d.m.Y');
        $this->modelView = $modelView;

		// create new PDF document
		$pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
	
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		
		// set document information
		$pdf->SetCreator('');
		$pdf->SetAuthor('');
		$pdf->SetTitle('');
		$pdf->SetSubject('');
		$pdf->SetKeywords('');

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		// set margins
		$pdf->SetMargins(10, 3, 5, 5); // это отступы до самого контента минуя хидер и футер
		// set auto page breaks
		$pdf->SetAutoPageBreak(false, 10);
		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		// set some language-dependent strings (optional)
		if (@file_exists(_vendorDIR_.'TCPDF/rus.php'))
		{
			require_once(_vendorDIR_.'TCPDF/rus.php');
			$pdf->setLanguageArray($l??[]);
		}
		// set default font subsetting mode
		$pdf->setFontSubsetting(true);
		$pdf->setJPEGQuality(75);
		$pdf->SetFillColor(239, 238, 210);

		$this->pdf = $pdf;

		//============= counter point ==============//
	    $overallProgress = ceil(( ++$this->complectCounter * 100 ) / $this->complects_lenght);
	    $this->progress->progressCount( $overallProgress );
	}

	public function printPassportRunnerHeader($row, $date, $complected=[])
	{
		$pdf = $this->pdf;
		$complectedTD = '';
		
		if ( is_array($complected) && !empty($complected) ) 
		{
			foreach( $complected as $modelCompl )
			{
				$complectedTD .= $modelCompl['model_type'] . ',';
			}
			$complectedTD = trim($complectedTD,",");
			
			$complectedTD = 'В комплекте: <b>'.$complectedTD.'</b>';
		}

		$header='
			<style>
				td {
					/*border: 1px solid grey;*/
				}
			</style>
			<table cellpadding="2" style="" width="100%">
				<tbody>
					<tr>
						<td width="50%" style="text-align:left;">Фабричный Артикул: <b>'.$row['vendor_code'].'</b></td>
						<td width="50%" style="text-align:right;">Дата Добавления: <b>'.$date.'</b></td>
					</tr>
					<tr>
						<td style="text-align:left;">Номер 3Д: <b>'.$row['number_3d'].'-'.$row['model_type'].'</b></td>
						<td style="text-align:right;">'.$complectedTD.'</td>
					</tr>
				</tbody>
			</table>
		';
		$pdf->SetFont('dejavusans', '', 9, '', true);
		$pdf->setCellPaddings(0, 0, 0, 0);
		$pdf->writeHTMLCell(195, '', '', '', $header, 0, 1, 0, true, 'C', true);
		$pdf->setCellPaddings(0, 1, 0, 0);

		//============= counter point ==============//
	    $overallProgress = ceil(( ++$this->complectCounter * 100 ) / $this->complects_lenght);
	    $this->progress->progressCount( $overallProgress );
	}

	public function makeLabelsStr($row)
	{
	    $pictsDir = _webDIR_HTTP_ . 'picts/'; 

		$labelImg = '';
		$arr_labels = explode(";",$row['labels']);
		$this->executive = false;
		$this->materialMM = 'Бронза';
		foreach( $arr_labels as &$value ) {
			if ( $value == "Срочное!" ) $labelImg .= '<img height="20" src="'.$pictsDir.'label_hot.png"/>&nbsp;';
			if ( $value == "Бриллианты" ) $labelImg .= '<img height="20" src="'.$pictsDir.'label_Brill.png"/>&nbsp;';
			if ( $value == "Литьё с камнями" ) $labelImg .= '<img height="20" src="'.$pictsDir.'label_Swst.png"/>&nbsp;';
	        if ( $value == "Размеры в воске" ) $labelImg .= '<img height="20" src="'.$pictsDir.'label_waxSize.png"/>&nbsp;';
	        if ( $value == "Прямое литьё из Воска" )
	        {
	            $labelImg .= '<img height="20" src="'.$pictsDir.'label_FrontSmelt.png"/>&nbsp;';
	            $this->materialMM = '';
	        }
	        if ( $value == "Прямое литьё из Полимера" )
	        {
	            $labelImg .= '<img height="20" src="'.$pictsDir.'label_FrontSmeltPoly.png"/>&nbsp;';
	            $this->materialMM = '';
	        }

			if ( $value == "Эксклюзив" )
			{
				$this->executive = true;
				$labelImg .= '<img height="20" src="'.$pictsDir.'label_exec.png"/>&nbsp;';
			}
	        if ( $value == "Эксперимент" ) $labelImg .= '<img height="20" src="'.$pictsDir.'label_exper.png"/>&nbsp;';
	        if ( $value == "Ремонт" ) $labelImg .= '<img height="20" src="'.$pictsDir.'label_repair.png"/>&nbsp;';
		}
		$labelImgDIV = '';
		if ( $labelImg ) {
			$labelImgDIV .= '<span>';
			$labelImgDIV .= $labelImg;
			$labelImgDIV .= '</span>';
		}

		//============= counter point ==============//
	    $overallProgress = ceil(( ++$this->complectCounter * 100 ) / $this->complects_lenght);
	    $this->progress->progressCount( $overallProgress );

		return $labelImgDIV;
	}

    /**
     * @throws \Exception
     */
    public function printPassport()
	{
		$modelView = $this->modelView;
		$row = $this->modelViewData['row'];
	    $coll_id = $this->modelViewData['coll_id'];
	    $matsCovers = $this->modelViewData['matsCovers'];
	    $complected = $this->modelViewData['complected'];
	    $images = $this->modelViewData['images'];
	    $gems = $this->modelViewData['gems'];
	    $dopVC = $this->modelViewData['dopVC'];
	    $repairs = $this->modelViewData['repairs'];
		$date = $this->modelViewData['date'];
		$labelImgDIV = $this->makeLabelsStr($row);
		$notes = $modelView->getDescriptions();

		$pdf = $this->pdf;
		$progress = $this->progress;

		$pdf->AddPage();
		$this->printPassportRunnerHeader($row, $date, $complected);

		// $rowspans = 5;
		$rowspans = 4;

		$gems_tr = '';
		if ( !empty($gems) ) {
			$gCountTot = count($gems);
			$rowspans += $gCountTot;
			foreach ( $gems as $row_gems )
			{
				$diam = '';
				if ( is_numeric($row_gems['gem_size']) ) $diam = 'Ø';
				$gems_tr .= '
				<tr>
					<td><b>'.$diam.$row_gems['gem_size'].' - '.$row_gems['gem_value'].'</b></td>
					<td><b>'.$row_gems['gem_name'].' '.$row_gems['gem_cut'].'</b></td>
					<td><b>'.$row_gems['gem_color'].'</b></td>
				</tr>
				';
			}
		}


	    $size_range = trim($row['size_range']);
		$size_rangeTR = '';
		if ( !empty($size_range) ) {
			$size_rangeTR = '
				<tr >
					<td style="text-align:left;">Размерный ряд:</td>
					<td style="text-align:left;"><b>'.$size_range.'</b></td>
					<td ></td>
				</tr>
			';
			$rowspans++;
		}


		// -- создание картинок -- //
		$sketchimg = '';
		$mainimg = '';
		$onbodyimg = '';
		//$imgPath = _stockDIR_ . $row['number_3d'].'/'.$id.'/images/';
        //debugAjax($images,"images");
		foreach ( $images as $image ) //$img_str
		{
	        //$imgPath = _stockDIR_ . explode('Stock/', $img_str['img_name'])[1];
	        $imgPath = _stockDIR_ . explode('Stock/', $image['imgPath'])[1];
            //debugAjax($imgPath,"imgPath");

            if ( $image['sketch'] == 1 ) $sketchimg = $imgPath;
			if ( $image['main'] == 1 )   $mainimg =   $imgPath;
			if ( $image['onbody'] == 1 ) $onbodyimg = $imgPath;
		}
        //debugAjax("end","end", END_AB);
		$realImgHeight = 100; // если нет эскиза - высота блока = 100пикс
		if ( !empty($sketchimg) ) {
			$befImgY = $pdf->GetY();
			$pdf->Image($sketchimg, 11, 21, 57, 50, '', '', '', true, 150, '', false, false, 0, 'CM', false, false);
			$afterImgY = $pdf->getImageRBY();
			$realImgHeight = ($afterImgY - $befImgY)*3.2;
		}


		//============= counter point ==============//
	    $overallProgress = ceil(( ++$this->complectCounter * 100 ) / $this->complects_lenght);
	    $progress->progressCount( $overallProgress );


	    $matsCoversStr = '';
	    foreach ( $matsCovers as $material )
	    {
	        $part = $material['part'] ? $material['part'] . ': ' : '';
	        $handling = $material['handling'] ? ' - ' . $material['handling'] : '' ;

	        $area = $material['area'] ? $material['area'] . ': ' : '';
	        $covColor = $material['covColor'] ? ' - ' . $material['covColor']  : '' ;
	        $matsCoversStr .= '
	        <tr>
	            <td colspan="2"><i>'.$part.'</i><b>'.$material['type'].' '.$material['probe'].' '.$material['metalColor'].'</b>'.$handling.'</td>
	            <td><i>'.$area.'</i><b>'.$material['covering'].'</b>'.$covColor.'</td>
	        </tr>
	        ';
	        $rowspans++;
	    }

	    $pictsDir = _webDIR_HTTP_ . 'picts/'; 
		//--table 1--//
		$table1 = '
			<style>
				td {
					border: 1px solid grey;
				}
				table {
					border: 2px solid #333;
				}
			</style>
			<table cellpadding="2" width="100%">
				<tbody>
					<tr>
						<td width="30%" style="text-align:left;">Эскиз</td>
						<td width="70%" colspan="3" style="text-align:center;">Коллекции <b>&laquo;'.$row['collections'].'&raquo;</b></td>
					</tr>
					<tr>
						<td rowspan="'.$rowspans.'" style="text-align:center;"><img height="'.$realImgHeight.'" src="'.$pictsDir.'10x10.png"></td>
						<td colspan="2" width="35%" style="text-align:center;">Вставки</td>
						<td width="35%" style="text-align:center;">Цвет</td>
					</tr>
					'.$gems_tr.'
					<tr><td colspan="3" style="text-align:center;">Материалы</td></tr>
					'.$matsCoversStr.'
					<tr>
						<td>Вид модели</td>
						<td ><b>'.$row['model_type'].'</b></td>
						<td>Вес: <b>'.$row['model_weight'].' гр.</b></td>
					</tr>
					'.$size_rangeTR.'
					<tr>
						<td colspan="3">'.$labelImgDIV.'</td>
					</tr>
				</tbody>
			</table>
		';
		$pdf->SetFont('dejavusans', '', 8, '', true);
		$pdf->writeHTMLCell(195, '', '', '', $table1, 0, 1, 0, true, 'L', true);
		//--END table 1 --//

		//============= counter point ==============//
	    $overallProgress = ceil(( ++$this->complectCounter * 100 ) / $this->complects_lenght);
	    $progress->progressCount( $overallProgress );

		//--table 2--//
		$table2 = '
			<style>
				td {
					border: 1px solid grey;
				}
				table {
					border: 2px solid #333;
				}
			</style>
			<table cellpadding="3">
				<thead>
					<tr>
						<td>Автор</td>
						<td>3D Модельер</td>
						<td>3D Контроль</td>
						<td>Технолог</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><b>'.$row['author'].'</b><br/>&nbsp; </td>
						<td><b>'.$row['modeller3D'].'</b><br/>&nbsp; </td>
						<td><b>Быков В.А.</b><br/>&nbsp; </td>
						<td><b>Занин В.</b><br/>&nbsp; </td>
					</tr>

				</tbody>
			</table>
		';
		
		$pdf->setCellPaddings(0, 2, 0, 0);
		$pdf->SetFont('dejavusans', '', 9, '', true);
		$pdf->writeHTMLCell(195, '', '', '', $table2, 0, 1, 0, true, 'L', true);

		//============= counter point ==============//
	    $overallProgress = ceil(( ++$this->complectCounter * 100 ) / $this->complects_lenght);
	    $progress->progressCount( $overallProgress );

		$lastTableY = $pdf->GetY();
		
		// -- создание картинок -- //
		$imagesTD = '';
		$imgcolspan = 0;
		$widthMainImg = 96;
		if ( !empty($onbodyimg) ) {
			$widthMainImg = 48;
			$befImgY = $pdf->GetY();
			$pdf->Image($onbodyimg, 58.5, $lastTableY+8, 48, 48, '', '', 'C', true, 150, '', false, false, 0, 'CM', false, false);
			$afterImgY = $pdf->getImageRBY();
			$realImgHeight = ($afterImgY - $befImgY)*3.5;
			
			$imagesTD .='<td style="text-align:center;"><img height="'.$realImgHeight.'" src="'.$pictsDir.'10x10.png"></td>';
			$imgcolspan++;
		}
		if ( !empty($mainimg) ) {
			$befImgY = $pdf->GetY();
			$pdf->Image($mainimg, 11, $lastTableY+8, $widthMainImg, 48, '', '', '', true, 150, '', false, false, 0, 'CM', false, false);
			//$pdf->Rect(11, $lastTableY+8, $widthMainImg, 48, 'F', array(), array(128,255,128));
			$afterImgY = $pdf->getImageRBY();
			$realImgHeight = ($afterImgY - $befImgY)*3;
			
			$imagesTD .='<td style="text-align:center;" ><img height="'.$realImgHeight.'" src="'.$pictsDir.'10x10.png"></td>';
			$imgcolspan++;
		}
		// ---- //
		
		$image_table='
				<table cellpadding="2" style="border: 2px solid #333;">
					<tbody>
						<tr>
							<td colspan="'.$imgcolspan.'" style="text-align:center;border-bottom: 1px solid grey;">Рендеры 3D модели:</td>
						</tr>
						<tr>
							'.$imagesTD.'
						</tr>
					</tbody>
				</table>
		';
		$row_dop_vc_str = '';
		if ( !empty($dopVC) )
		{
			foreach ( $dopVC as $row_dop_vc )
			{
			$row_dop_vc_str .= '
				<tr style="">
					<td width="35%" style="background-color: AQUA;border-right: 1px solid grey;border-bottom: 1px solid grey;">'.$row_dop_vc['vc_names'].'</td>
					<td width="65%" style="text-align:left;border-bottom: 1px solid grey;" ><b>'.$row_dop_vc['vc_link'].'</b> '.$row_dop_vc['vc_descript'].'</td>
				</tr>
			';
			}
		}
		$dopVC_table='
			<table cellpadding="2" style="border: 2px solid #333;">
				<tbody>
					<tr>
						<td colspan="2" style="text-align:center; border-bottom: 1px solid grey;">Ссылки на доп. артикулы:</td>
					</tr>
					'.$row_dop_vc_str.'
				</tbody>
			</table>
		';
		
		$tableWrapp='
			<table width="100%" cellpadding="1">
				<tbody>
					<tr>
						<td width="50%" style="text-align:center;">'.$image_table.'</td>
						<td width="50%" style="text-align:center;">'.$dopVC_table.'</td>
					</tr>
				</tbody>
			</table>
		';
		
		$pdf->writeHTMLCell(195, '', '', '', $tableWrapp, 0, 1, 0, true, 'L', true);
		//--END table 2--//
		
		//============= counter point ==============//
	    $overallProgress = ceil(( ++$this->complectCounter * 100 ) / $this->complects_lenght);
	    $progress->progressCount( $overallProgress );

		//--END table 3--//
		$descr = trim($row['description']);
		$descrScol = '';
		if (!empty($descr)) $descrScol = 'style="background-color: lime;"';
		$descr_table = '
			<style>
				table {
					border: 2px solid #333;
				}
			</style>
			<table cellspacing="1" cellpadding="2" width="100%">
				<tbody>
					<tr><td colspan="2" style="text-align:left;"><span '.$descrScol.'>Примечания:</span></td></tr>
					<tr><td colspan="2">'.$descr.'</td></tr>
				</tbody>
			</table>
		';

		if ( !empty( $notes ) )
        {
            $descr_table .= '<table cellspacing="1" cellpadding="2" width="100%"><tbody>';
            foreach ( $notes as $note )
            {
                $descr_table .= '<tr><td colspan="2" style="text-align:left;"><span '.$descrScol.'>Описание № '.$note['num'].': </span></td></tr>';
				$descr_table .= '<tr><td colspan="2">'.$note['text'].'</td></tr>';
            }
            $descr_table .= '</tbody></table>';
        }

		$pdf->writeHTMLCell(195, '', '', '', $descr_table, 0, 1, 0, true, 'L', true);


		$repairsTR = '';
		if ( $repairs )
		{
			foreach ( $repairs as $repRow )
	        {
	            $repName = '3D';
	            if ( $repRow['which'] == 1 ) $repName = 'Моднльера-доработчика';
				$repairsTR .= '
					<tr >
						<td colspan="2" style="text-align:left;">
							<span style="background-color: BISQUE;">Ремонт '. $repName. ' №'.$repRow['rep_num'].' от - '.date_create( $repRow['date'] )->Format('d.m.Y').': </span>
							<span>'.$repRow['repair_descr'].'</span>
						</td>
					</tr>	
				';	
			}

			$rep_table='
				<style>
					table {
						border: 2px solid #333;
					}
				</style>
				<table cellspacing="1" cellpadding="2" width="100%">
					<tbody>
							'.$repairsTR.'
					</tbody>
				</table>
			';
			$pdf->writeHTMLCell(195, '', '', '', $rep_table, 0, 1, 0, true, 'L', true);
		}
		
		$table2='
			<style>
				td {
					border: 1px solid grey;
				}
				table {
					border: 2px solid #333;
				}
			</style>
			<table cellspacing="1" cellpadding="4" width="100%">
				<tr>
					<td width="25%">Материал мастер модели</td>
					<td width="25%">Модельер</td>
					<td width="25%">Уровень сложности</td>
					<td width="25%">Дата</td>
				</tr>
				<tr>
					<td><b>'.$this->materialMM.'</b></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</table>
		';
		if ( !$this->executive ) $pdf->writeHTMLCell(195, '', '', '', $table2, 0, 1, 0, true, 'L', true);

		//============= counter point ==============//
	    $overallProgress = ceil(( ++$this->complectCounter * 100 ) / $this->complects_lenght);
	    $progress->progressCount( $overallProgress );

		$table3='
			<style>
				td {
					border: 1px solid grey;
				}
				table {
					border: 2px solid #333;
				}
			</style>
			<table cellspacing="1" cellpadding="4" width="100%">
				<tr>
					<td width="15%">Участок</td>
					<td width="15%">Дата</td>
					<td width="15%">Подпись</td>
					<td width="55%">Примечания</td>
				</tr>
				<tr>
					<td>ОС<br></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td>М. участок<br></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td>Литейка<br></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td>Бригады<br></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</table>
		';
		$pdf->writeHTMLCell(195, '', '', '', $table3, 0, 1, 0, true, 'L', true);
	}

    /**
     * @throws \Exception
     */
    public function printRunner()
	{
		$modelView = $this->modelView;
		$row = $this->modelViewData['row'];
	    $coll_id = $this->modelViewData['coll_id'];
	    $matsCovers = $this->modelViewData['matsCovers'];
	    $complected = $this->modelViewData['complected'];
	    $images = $this->modelViewData['images'];
	    $gems = $this->modelViewData['gems'];
	    $dopVC = $this->modelViewData['dopVC'];
	    $repairs = $this->modelViewData['repairs'];
		$date = $this->modelViewData['date'];
		$notes = $modelView->getDescriptions();
		//$labelImgDIV = $this->makeLabelsStr($row);

		$progress = $this->progress;
		$pdf = $this->pdf;

		$pdf->AddPage();

		$this->printPassportRunnerHeader($row, $date, $complected);

		// ---- //
	    $mainimg = '';
	    $schemeImg = '';
	    $sketchImg = '';
	    foreach ( $images as $img_str )
	    {
	        $imgPath = _stockDIR_ . explode('Stock/',$img_str['imgPath'])[1];
	        if ( $img_str['scheme'] == 1 ) $schemeImg = $imgPath;
	        if ( $img_str['main'] == 1 )   $mainimg =   $imgPath;
	        if ( $img_str['sketch'] == 1 ) $sketchImg =   $imgPath;
	    }
	    if ( empty($mainimg) )
	        if ( $sketchImg ) $mainimg = $sketchImg;

	    $pictsDir = _webDIR_HTTP_ . 'picts';
	    // ---- //
		$labelImgDIV = '';
		$arr_labels = explode(";",$row['labels']);
		foreach( $arr_labels as &$value )
		{
			if ( $value == "Срочное!" ) $labelImg[] = '<img height="20" src="'.$pictsDir.'/label_hot.png"/>&nbsp;';
			if ( $value == "Бриллианты" ) $labelImg[] = '<img height="20" src="'.$pictsDir.'/label_Brill.png"/>&nbsp;';
			if ( $value == "Литьё с камнями" ) $labelImg[] = '<img height="20" src="'.$pictsDir.'/label_Swst.png"/>&nbsp;';
			if ( $value == "Эксклюзив" ) $labelImg[] = '<img height="20" src="'.$pictsDir.'/label_exec.png"/>&nbsp;';
	        if ( $value == "Эксперимент" ) $labelImg[] = '<img height="20" src="'.$pictsDir.'/label_exper.png"/>&nbsp;';
	        if ( $value == "Ремонт" ) $labelImg[] = '<img height="20" src="'.$pictsDir.'/label_repair.png"/>&nbsp;';
	        if ( $value == "Размеры в воске" ) $labelImg[] = '<img height="20" src="'.$pictsDir.'/label_waxSize.png"/>&nbsp;';
	        if ( $value == "Прямое литьё из Воска" ) $labelImg[] = '<img height="20" src="'.$pictsDir.'/label_FrontSmelt.png"/>&nbsp;';
	        if ( $value == "Прямое литьё из Полимера" ) $labelImg[] = '<img height="20" src="'.$pictsDir.'/label_FrontSmeltPoly.png"/>&nbsp;';
		}
		
		if ( count($labelImg?:[]) )
		{
			$len_labelImg = count($labelImg?:[]);
			$labelImgDIV = '<div>';
			for ( $i=1; $i < $len_labelImg+1; $i++ ) {
				$labelImgDIV .= $labelImg[$i-1];
				if ( ( ($i % 2) == 0 ) && ( $len_labelImg != $i ) ) $labelImgDIV .= '<br>';
			}
			$labelImgDIV .= '</div>';
		}

		$style = array( 'width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(121,121,121) );
		$pdf->Line( 10, 13, 205.5, 13, $style);

		//============= counter point ==============//
		$overallProgress = ceil(( ++$this->complectCounter * 100 ) / $this->complects_lenght);
	    $progress->progressCount( $overallProgress );

		$rowspans = 3; //Увеличиваем на 1 если добавили строку <tr>

	    $matsCoversStr = '';
	    foreach ( $matsCovers as $material )
	    {
	        $part = $material['part'] ? $material['part'] . ': ' : 'Материал изделия: ';
	        $handling = $material['handling'] ? ' - ' . $material['handling'].', ' : '' ;

	        $area = $material['area'] ? $material['area'] . ': ' : '';
	        $covColor = $material['covColor'] ? ' - ' . $material['covColor']  : '' ;

	        $matsCoversStr .= '<tr>
                <td style="text-align:left;"><i style="background-color: #FFF8DC;"><u>'.$part.'</u></i></td>
                <td style="text-align:right;"><b>'.$material['type'].' '.$material['probe'].' '.$material['metalColor'].',</b>'.$handling.'<i> </i><b>'.$material['covering'].' '.$material['area'].'</b>'.$covColor.'</td>
            </tr>';
	        $rowspans++;
	    }
	    //debug($matsCoversStr,'$matsCoversStr',1);
		
		$size_rangeTR = '';
		$size_range = trim($row['size_range']);
		if ( !empty($size_range) ) {
			$size_rangeTR = '
				<tr >
					<td style="text-align:left;">Размерный ряд:</td>
					<td style="text-align:right;"><b>'.$size_range.'</b></td>
				</tr>
			';
			$rowspans++;
		}

		$txt = '';
		$br = '';
		if ( !empty($gems) ) {
			$gCountTot = count($gems);
			$gCount = 0;
			$txt = '<hr/>Вставки:<br>';
	        foreach ( $gems as $row_gems )
	        {
				$diam = '';
				$gCount++;
				if ( is_numeric($row_gems['gem_size']) ) $diam = 'Ø';
				if ( $gCount < $gCountTot ) $br = '<br>';
				$txt .= '<b>'.$diam.$row_gems['gem_size']." - ".$row_gems['gem_value']." ".$row_gems['gem_cut']." - ".$row_gems['gem_name']." - ".$row_gems['gem_color']."</b>".$br;
				$br = '';
			}
		}

		if ( !empty($dopVC) ) {
			$gCountTot = count($dopVC);
			$gCount = 0;
			$txt .= '<hr/>';
	        foreach ( $dopVC as $row_dop_vc )
	        {
				$gCount++;
				if ( $gCount < $gCountTot ) $br = '<br>';
				$txt .= '<span style="background-color: AQUA;">'.$row_dop_vc['vc_names'].': <b>'.$row_dop_vc['vc_link'].'</b> '.$row_dop_vc['vc_descript'].'</span>'.$br;
				$br = '';
			}
		}

		$descr = trim($row['description']);
		if ( !empty($descr) )
		{
			$txt .= '<hr/>';
			$txt .= '<span style="background-color: lime;">Примечания: </span><b>'.$row['description'].'</b>';
		}
        if (! empty($notes) )
        {
            foreach ( $notes as $note )
            {
                $txt .= '<hr/>';
                $txt .= '<span style="background-color: lime;">Описание № '.$note['num'].': </span><b>'.$note['text'].'</b>';
            }
        }

		if ( !empty($descr) || !empty($dopVC) || !empty($gems) ) {
			$gemsTR = '
				<tr >
					<td colspan="2" style="text-align:left;">'.$txt.'</td>
				</tr>
			';
			$rowspans++;
		}


		$repairsTR = '';
		if ( !empty($repairs) ) {
	        foreach ( $repairs as $repRow )
	        {
				$repairsTR .= '
					<tr >
						<td colspan="2" style="text-align:left;">
							<span style="background-color: BISQUE;">Ремонт №'.$repRow['rep_num'].' от - '.date_create( $repRow['date'] )->Format('d.m.Y').': </span>
							<span>'.$repRow['repair_descr'].'</span>
						</td>
					</tr>	
				';
				$rowspans++;		
			}
		}
		$pdfImgY = 15;
		if ( !empty($labelImgDIV) ) $pdfImgY += 3;

		$befImgY = $pdf->GetY();
		if ( $mainimg )
        {
            $pdf->Image($mainimg, 146, $pdfImgY, 61, 60, '', '', '', true, 150, '', false, false, 0, 'CM', false, false);
            $afterImgY = $pdf->getImageRBY();
            $realImgHeight = ($afterImgY - $befImgY)*3.4;
        }

	    //============= counter point ==============//
	    $overallProgress = ceil(( ++$this->complectCounter * 100 ) / $this->complects_lenght);
	    $progress->progressCount( $overallProgress );

		
		$top_txt = '
			<style>
				td {
					/*border: 1px solid grey;*/
				}
			</style>
			<table cellpadding="2" style="" width="100%">
				<tbody>
					<tr>
						<td width="18%" style="text-align:left;">Автор:</td>
						<td width="52%" style="text-align:right;"><b>'.$row['author'].'</b></td>
						<td width="30%" rowspan="'.$rowspans.'" style="text-align:center;">
							'.$labelImgDIV.'<img height="'.$realImgHeight.'" src="'.$pictsDir.'/10x10.png"/>
						</td>
					</tr>
					<tr>
						<td style="text-align:left;">Коллекция:</td>
						<td style="text-align:right;"><b>&laquo;'.$row['collections'].'&raquo;</b></td>
					</tr>
					<tr>
						<td style="text-align:left;">Вес изделия:</td>
						<td style="text-align:right;"><b>'.$row['model_weight'].' гр.</b></td>
					</tr>'.$matsCoversStr.$size_rangeTR.$gemsTR.$repairsTR.'
				</tbody>
			</table>
		';
		
		$pdf->writeHTMLCell(195, '', '', '', $top_txt, 0, 1, 0, true, 'L', true);
		$pdf->setCellPaddings(0, 2, 0, 0);

		//============= counter point ==============//
		$overallProgress = ceil(( ++$this->complectCounter * 100 ) / $this->complects_lenght);
	    $progress->progressCount( $overallProgress );
		
		//--table 1--//
		$table1 = "
			<style>
				td {
					border: 1px solid grey;
				}
			</style>
			<table cellpadding=\"3\" style=\"border: 2px solid grey;\">
						<thead>
							<tr>
								<td width=\"18%\">Участок</td>
								<td>Получил(дата, подпись)</td>
								<td>Сдал(дата, подпись)</td>
								<td width=\"32%\">Примечания(размеры, баллы допуски)</td>
							</tr>
						</thead>
						<tbody class=\"gem_rows_print\">
							<tr>
								<td width=\"18%\">3-Д моделирование</td>
								<td><b>{$row['modeller3D']}</b></td>
								<td>$date</td>
								<td width=\"32%\"></td>
							</tr>
							<tr>
								<td>Доработка модели</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td>Отд ПДО</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td>Восковка</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td>Отд ПДО</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td>Литейка</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td>Комплектация</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td>Монтировка</td>
								<td></td>
								<td></td>
								<td>размеры</td>
							</tr>

							<tr>
								<td>Закрепка</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<tr>
								<td>Кладовая</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
		";
		$pdf->SetFont('dejavusans', '', 8, '', true);
		$pdf->writeHTMLCell(195, '', '', '', $table1, 0, 1, 0, true, 'L', true);

		//============= counter point ==============//
		$overallProgress = ceil(( ++$this->complectCounter * 100 ) / $this->complects_lenght);
	    $progress->progressCount( $overallProgress );
		
		$tableMiddl = "
			<style>
				tr {
					border-bottom: 1px solid grey;
				}
			</style>
			<table class=\"tableMid\" cellpadding=\"3\">
				<tbody>
					<tr>
						<td style=\"border-bottom: 1px solid grey;\">
							<b>Причина ремонта:</b>
						</td>
					</tr>
					<tr>
						<td style=\"border-bottom: 1px solid grey;\">&nbsp;</td>
					</tr>
					<tr>
						<td style=\"border-bottom: 1px solid grey;\">&nbsp;</td>
					</tr>
					<tr>
						<td style=\"border-bottom: 1px solid grey;\"><b>закрыть доступ ДА, НЕТ (необходимое обвести кружком)</b></td>
					</tr>
					<tr>
						<td style=\"border-bottom: 1px solid grey;\"><b>ремонт утвердил технолог (дата,подпись)</b></td>
					</tr>
					<tr>
						<td style=\"border-bottom: 1px solid grey;\">после ремонта необходимо перерезать резинку ( накладку, шинку, белое, красное, все )</td>
					</tr>
					<tr>
						<td style=\"border-bottom: 1px solid grey;\">после ремонта необходимость сигнала (кол-во шт) </td>
					</tr>
				</tbody>
			</table>
		";
		
		$pdf->SetFont('dejavusans', '', 9, '', true);
		$pdf->writeHTMLCell(195, '', '', '', $tableMiddl, 0, 1, 0, true, 'L', true);

		$pdf->SetFont('dejavusans', '', 8, '', true);
		$pdf->writeHTMLCell(195, '', '', '', $table1, 0, 1, 0, true, 'L', true);
	}

    /**
     * @throws \Exception
     */
    public function printPassportRunner()
	{
		$this->printPassport();
		$this->printRunner();
	}

	public function exportToFile($append)
	{
		$row = $this->modelViewData['row'];
		$modTypeEn = $this->modelView->translit($row['model_type']);
		$pdfname = $row['number_3d'].'-'.$modTypeEn . '_' . $append . '.pdf';

		//$pdf->Output($path, 'F');
		
		$pdf_string = $this->pdf->Output('pdfname.pdf', 'S');
		if ( !file_exists( _webDIR_.'Pdfs/') ) 
			mkdir( _webDIR_.'Pdfs/', 0777, true);
		
		file_put_contents(_webDIR_.'Pdfs/'.$pdfname, $pdf_string);

	    //============= counter point ==============//
	    $this->progress->progressCount( 100 );

		return $pdfname;
	}

	public function printPicture($pictID)
    {
        $pdf = $this->pdf;
        $progress = $this->progress;
        $row = $this->modelViewData['row'];
        $date = $this->modelViewData['date'];
        $images = $this->modelViewData['images'];

        $tr = '';
        if ( $pictID > 0 )
        {
            $imgName = '';
            foreach ( $images as $image )
            {
                if ( (int)$image['id'] === $pictID )
                {
                    $imgName = $image['imgPath'];
                    break;
                }
            }
            $tr = '<tr><td><img src="'.$imgName.'"/></td></tr>';
        } else {
            $tds = [];
            foreach ( $images as $image ) $tds[] = '<td><img src="'.$image['imgPath'].'"/></td>';

            $div = 4;
            if ( count($tds) < 5 ) $div = 3;
            $tr = '<tr>';
            for ( $i = 1; $i <= count($tds); $i++ )
            {
                if ( $i % $div === 0 ) $tr .= '</tr><tr>';
                $tr .= $tds[$i-1];
            }
            $tr .= '</tr>';
        }

        $pdf->AddPage();
        $this->printPassportRunnerHeader($row, $date);
        $table = '<table><tbody>'.$tr.'</tbody></table>';
        $pdf->writeHTMLCell(195, '', '', '', $table, 0, 1, 0, true, 'L', true);
    }
}