<?php
namespace Views\_Main\Models;

use Views\vendor\core\Sessions;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style;

class ToExcel extends Main
{

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

    public $collectionName;

    // нужен чтобы запросить в контроллере, при создании имени файла
    public $foundRows = [];

    function __construct()
    {
        // уходим если нет моделей для вывода
        if ( isset($_SESSION['nothing']) )
        {
            debug($_SESSION['nothing']);
            return;
        }

        $session = new Sessions();
        $assist = $session->getKey('assist');
        $searchFor = $session->getKey('searchFor');

        if ( $searchFor ) // || $session->getKey('re_search')
        {
            $search = new Search();
            $this->foundRows = $search->search( $searchFor );

            $this->collectionName = (int)$assist['searchIn'] === 1 ? 'Поиск по: '.$searchFor : $assist['collectionName'].'" Поиск по: '.$searchFor.':';

            parent::__construct($assist, false, $this->foundRows);
        } else {

            parent::__construct($assist, false, $this->foundRows);

            $this->getModelsFormStock();
            $this->collectionName = $assist['collectionName'];
        }


        $this->progressResponse = [
            'progressBarPercent' => 0,
            'user' => [
                'name'=> '',
                'tabID' => '',
            ],
            'message' => 'progressBarPDF' // флаг о том что идёт создание пдф
        ];
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
        if ( !$this->socketClientResource ) return;
        if ( !is_resource($this->socketClientResource) ) return;

        $this->progressResponse['progressBarPercent'] = $newPercent;

        fwrite($this->socketClientResource, json_encode($this->progressResponse));
    }

    public function getBordersArray($switch)
    {
        $borders = [];
        switch ( $switch )
        {
            case "vertical":
                //вертикальные границы ячейки
                $borders = array(
                    'borders' => array(
                        'left' => array(
                            'borderStyle' => Style\Border::BORDER_THICK,
                            'color' => array('rgb' => '000000'),
                        ),
                        'right' => array(
                            'borderStyle' => Style\Border::BORDER_THIN,
                            'color' => array('rgb' => '000000'),
                        ),
                    ),
                );
                break;
            case "verLast":
                $borders = array(
                    'borders' => array(
                        'right' => array(
                            'borderStyle' => Style\Border::BORDER_THICK,
                            'color' => array('rgb' => '000000'),
                        ),
                    ),
                );
                break;
            case "horPerRow":
                $borders = array(
                    'borders' => array(
                        'bottom' => array(
                            'borderStyle' => Style\Border::BORDER_THIN,
                            'color' => array('rgb' => '000000'),
                        ),
                    ),
                );
                break;
        }

        return $borders;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Exception
     */
    public function getXlsx()
    {

        $workingCenters = $this->getWorkingCentersSorted();
        foreach ( $workingCenters as &$workingCenter )
        {
            $perf_day = $workingCenter['perf_day']??'';
            $statuses_start = $workingCenter['statuses']['start']??[];
            $statuses_end = $workingCenter['statuses']['end']??[];

            $workingCenter['title'] = $workingCenter['descr'];
            $workingCenter['deadline'] = "Срок 1 день (". $perf_day . " Артикула/день)";
            $workingCenter['statuses']['start'] = $statuses_start['id']??'';
            $workingCenter['statuses']['end'] = $statuses_end['id']??'';
        }
        unset($workingCenter);
        //debug($workingCenters,'workingCenters',1);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();


        $startRow = 2;
        $sheet->setCellValue('A'.$startRow, 'Артикул');
        $sheet->setCellValue('B'.$startRow, 'Кол-во');
        $sheet->setCellValue('C'.$startRow, 'Р-Ряд');


        //$columnIndex1, $row1, $columnIndex2, $row2
        $wcCount = count( $workingCenters ); //23
        for ( $columnIndex1 = 4; $columnIndex1 < ($wcCount*2)+4; $columnIndex1++ )
        {
            $columnIndex1Plus = $columnIndex1 + 1;
            try {
                $sheet->mergeCellsByColumnAndRow($columnIndex1,$startRow,$columnIndex1Plus,$startRow);
                $sheet->mergeCellsByColumnAndRow($columnIndex1,$startRow+1,$columnIndex1Plus,$startRow+1);
                $sheet->mergeCellsByColumnAndRow($columnIndex1,$startRow+2,$columnIndex1Plus,$startRow+2);
            } catch (\Exception $e) {
                echo "/n Error in mergeCellsByColumnAndRow() " . $e->getMessage() . "/n" . "Line: ". $e->getLine();
            }
        }

        $columnIndex = 4;
        foreach ( $workingCenters as $workingCenter )
        {
            $sheet->setCellValueByColumnAndRow($columnIndex, $startRow, $workingCenter['name']);
            $sheet->setCellValueByColumnAndRow($columnIndex, $startRow+1, $workingCenter['title']);
            $sheet->setCellValueByColumnAndRow($columnIndex, $startRow+2, $workingCenter['deadline']);
            $columnIndex = $columnIndex+2;
        }

        // рисуем Постутило/сдано
        for ( $columnIndex = 4; $columnIndex < ($wcCount*2)+4; $columnIndex++ )
        {
            $sheet->setCellValueByColumnAndRow($columnIndex+0, $startRow+3, 'Поступило');
            $sheet->setCellValueByColumnAndRow($columnIndex+1, $startRow+3, 'Сдано');
            ++$columnIndex;
        }


        $sheet->getRowDimension($startRow)->setRowHeight(20);
        $sheet->getRowDimension($startRow+1)->setRowHeight(22);
        $sheet->getRowDimension($startRow+2)->setRowHeight(15);

        for ( $columnIndex = 4; $columnIndex < ($wcCount*2)+4; $columnIndex++ )
        {
            $sheet->getColumnDimensionByColumn($columnIndex)->setWidth(13);
        }

        $fontStyleTop = array(
            'name'      	=> 'Calibri',
            'size'     	    => 12,
            'bold'      	=> true,
            'italic'    	=> false,
            //'underline' 	=> Style\Font::UNDERLINE_DOUBLE,
            'strike'    	=> false,
            'superScript' 	=> false,
            'subScript' 	=> false,
            'color'     	=> array(
                'rgb' => 'E0FFFF'
            )
        );
        $fontStyleRow3 = [
            'size'  => 9,
            'color' => [
                'rgb' => '2F4F4F'
            ]
        ];

        // кол-во получившихся колонок
        $allAvailableColumns = ($wcCount*2)+3;
        $sheet->getStyleByColumnAndRow(1, $startRow, $allAvailableColumns, $startRow)->getFont()->applyFromArray($fontStyleTop);
        $sheet->getStyleByColumnAndRow(1, $startRow+1, $allAvailableColumns, $startRow+3)->getFont()->applyFromArray($fontStyleRow3);


        $borderStyleArray = array(
            'borders' => array(
                'outline' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
                'horizontal' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,//BORDER_THICK
                    'color' => array('rgb' => '000000'),
                ),
                'vertical' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
            ),
        );

        // Границы 2 - 4 строки
        $sheet->getStyleByColumnAndRow(1, $startRow+1, $allAvailableColumns, $startRow+3)->applyFromArray($borderStyleArray);

        // горизонтальное выравнивание 4х первых строк
        $sheet->getStyleByColumnAndRow(1, $startRow, $allAvailableColumns, $startRow+3)
            ->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(Style\Alignment::VERTICAL_CENTER);

        // фоновый цвет первой строки
        $sheet->getStyleByColumnAndRow(1, $startRow, $allAvailableColumns, $startRow)
            ->getFill()
            ->setFillType(Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('4682B4');

        // фоновый цвет второй строки
        $sheet->getStyleByColumnAndRow(1, $startRow+1, $allAvailableColumns, $startRow+1)
            ->getFill()
            ->setFillType(Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('7FFFD4');

        // фоновый цвет Пост Сдано строки
        $sheet->getStyleByColumnAndRow(4, $startRow+3, $allAvailableColumns, $startRow+3)
            ->getFill()
            ->setFillType(Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('2ae2f8');



        $sheet->setTitle('Отчёт рабочих центров');

        $ModelRow = $this->getRow();
        $countModelRow = count(is_array( $ModelRow ) ? $ModelRow : [] );

        // Запишем вверху дату и коллекцию
        $topText1 = 'Общее кол-во выведенных изделий:  '.$countModelRow;
        $topText2 = 'Коллекция:  "'. $this->collectionName.'"';
        $topText3 = 'Дата:  '. date('d.m.Y');
        $sheet->setCellValue('D1', $topText1);
        $sheet->setCellValue('H1', $topText2);
        $sheet->setCellValue('L1', $topText3);
        $sheet->getStyleByColumnAndRow(1, 1, $allAvailableColumns, 1)
            ->getFill()
            ->setFillType(Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('FFFACD');

        $rowS = $startRow+4; // с какой строки начинать
        $rowFILLIndex = $rowS;

        $bottomBorderPerCell = $this->getBordersArray('horPerRow');
        $verticalBordersPerCell = $this->getBordersArray('vertical');
        $verticalBorderLast = $this->getBordersArray('verLast');



        // Внешний цикл выводит строки
        for ( $i = 0; $i < $countModelRow; $i++ )
        {
            $xlsxRow = $this->drawXlsxRow( $ModelRow[$i] );
            $wCenters = $xlsxRow['wCenters'];
            $sizeRange = $xlsxRow['sizeRange'];
            $trFill = $xlsxRow['trFill'];

            // колонки первые 3
            $vendor_code = trim($ModelRow[$i]['vendor_code']);
            $sheet->setCellValueByColumnAndRow(1, $rowS, $vendor_code ?: $ModelRow[$i]['number_3d']);

            // Ссылка
            //$uri = 'http://192.168.0.245/Views/ModelView/index.php?id='.$ModelRow[$i]['id'];
            //$sheet->getCellByColumnAndRow(1, $rowS)->getHyperlink()->setUrl($uri);

            $sheet->getStyleByColumnAndRow(1, $rowS)
                ->getAlignment()
                ->setHorizontal(Style\Alignment::VERTICAL_CENTER)
                ->setVertical(Style\Alignment::VERTICAL_CENTER);

            // горизонтальная черта под первыми тремя столбцами
            $sheet->getStyleByColumnAndRow(1, $rowS,3, $rowS)->applyFromArray( $bottomBorderPerCell );

            $sheet->setCellValueByColumnAndRow(3, $rowS, $sizeRange);

            // в комменте полный список размеров
            if ( !empty($ModelRow[$i]['size_range']) )
                $sheet->getCommentByColumnAndRow(3, $rowS)->getText()->createTextRun($ModelRow[$i]['size_range']);

            //колонки остальные
            $columnIndex = 4; // начиная с 4й колонки
            $borderColumnIndex = $columnIndex;
            //$columnFILLIndex = $columnIndex;


            foreach ( is_array($wCenters)?$wCenters:[] as $wCenterL )
            {
                /* выделить рабочие участки цветами
                $sheet->getStyleByColumnAndRow( $columnFILLIndex, $rowFILLIndex )
                    ->getFill()
                    ->setFillType(Style\Fill::FILL_SOLID )
                    ->getStartColor()
                    ->setRGB('00FFFF');
                $columnFILLIndex = $columnFILLIndex+2;
                */

                $endDate = $wCenterL['end']['date']??'';
                $startDate = $wCenterL['start']['date']??'';

				if ( $startDate === -1 ) {
					$sheet->setCellValueByColumnAndRow($columnIndex, $rowS, "Просрочено");
					$sheet->getStyleByColumnAndRow($columnIndex, $rowS)
					->getFill()
					->setFillType(Style\Fill::FILL_SOLID)
					->getStartColor()
					->setRGB('2d2d2d');

                    $sheet->getStyleByColumnAndRow($columnIndex, $rowS)
                    ->getFont()
                    ->applyFromArray([
                        'color' => [
                            'rgb' => 'fff'
                        ]
                    ]);
				} else {
					$sheet->setCellValueByColumnAndRow($columnIndex, $rowS, $wCenterL['start']['date']??'');
				}
				
                $columnIndex++;

                if ( $endDate === -1 ) { //просрачено
                    $sheet->setCellValueByColumnAndRow($columnIndex, $rowS, "Просрочено");
                    $sheet->getStyleByColumnAndRow($columnIndex, $rowS)
                        ->getFill()
                        ->setFillType(Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('dc5d26');

                    $sheet->getStyleByColumnAndRow($columnIndex, $rowS)
                        ->getFont()
                        ->applyFromArray([
                            'color' => [
                                'rgb' => 'FFFFFF'
                            ]
                        ]);
                } else {
                    $sheet->setCellValueByColumnAndRow($columnIndex, $rowS, $wCenterL['end']['date']??'');
                }
					

                // горизонтальная черта под строкой
                $sheet->getStyleByColumnAndRow($borderColumnIndex, $rowS, $columnIndex, $rowS)->applyFromArray( $bottomBorderPerCell );

                // вертикальные рамки на каждый участок
                $sheet->getStyleByColumnAndRow($borderColumnIndex, $rowS)->applyFromArray( $verticalBordersPerCell );
                $borderColumnIndex = $borderColumnIndex+2;

                $columnIndex++;
            }

            // отложено/снято с произв.
            if ( $trFill ) $sheet->getStyleByColumnAndRow(1, $rowS, $allAvailableColumns, $rowS)
                ->getFill()
                ->setFillType(Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('f89891');

            $rowFILLIndex = $rowFILLIndex+2;
            $rowS++;

            $this->progressCount( ceil( ( $i * 100 ) / $countModelRow ) );
        }

        // рисуем последнюю верт четру
        $sheet->getStyleByColumnAndRow($allAvailableColumns, $startRow+4, $allAvailableColumns, $rowS-1)->applyFromArray( $verticalBorderLast );

        $sheet->getColumnDimensionByColumn(1)->setAutoSize(true);
        $sheet->getColumnDimensionByColumn(3)->setAutoSize(true);
        $sheet->getStyleByColumnAndRow(3, $startRow+1, 3, $rowS)
            ->getAlignment()
            ->setHorizontal(Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(Style\Alignment::VERTICAL_CENTER);


        //вертикальные границы ячейки
        $borderBottom = array(
            'borders' => array(
                'top' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
            ),
        );
        $sheet->getStyleByColumnAndRow(1, $rowS, $allAvailableColumns, $rowS)->applyFromArray($borderBottom);


        $this->output($spreadsheet);
    }





    /**
     * Final Working centers
     * Конечный центр нахождения
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
	public function getXlsxFwc()
	{
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$startRow = 1;
		// Титул
        $sheet->mergeCellsByColumnAndRow(1,$startRow,8,$startRow);
        $sheet->setCellValueByColumnAndRow(1, $startRow, 'Конечный Рабочий Центр Нахождения');
        // Шрифт 2й строки

        $fontStyleTop = array(
            'name'      	=> 'Calibri',
            'size'     	    => 12,
            'bold'      	=> true,
            'italic'    	=> false,
            //'underline' 	=> Style\Font::UNDERLINE_DOUBLE,
            'strike'    	=> false,
            'superScript' 	=> false,
            'subScript' 	=> false,
            'color'     	=> array(
                'rgb' => '460e15'
            )
        );
        $sheet->getStyleByColumnAndRow(1, $startRow, 8, $startRow)->getFont()->applyFromArray($fontStyleTop);

        // высоты строк
		$sheet->getRowDimension($startRow)->setRowHeight(25);

        $startRow++;
		$sheet->mergeCellsByColumnAndRow(1,$startRow,5,$startRow);
		$sheet->mergeCellsByColumnAndRow(6,$startRow,8,$startRow);
		

		$sheet->setCellValueByColumnAndRow(6, $startRow, 'Дата: '. $this->formatDate(time()));
		$sheet->getRowDimension($startRow)->setRowHeight(20);

        // Шрифт 1й строки

        $fontStyleTop = array(
            'name'      	=> 'Calibri',
            'size'     	    => 12,
            'bold'      	=> true,
            'italic'    	=> false,
            //'underline' 	=> Style\Font::UNDERLINE_DOUBLE,
            'strike'    	=> false,
            'superScript' 	=> false,
            'subScript' 	=> false,
            'color'     	=> array(
                'rgb' => 'E0FFFF'
            )
        );
        $sheet->getStyleByColumnAndRow(1, $startRow, 8, $startRow)->getFont()->applyFromArray($fontStyleTop);


        // фоновый цвет 2й строки
        $sheet->getStyleByColumnAndRow(1, $startRow, 8, $startRow)
            ->getFill()
            ->setFillType(Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('4682B4');
		
		$startRow++;
		$sheet->setCellValue('A'.$startRow, 'Артикул / №3Д');
		$sheet->setCellValue('B'.$startRow, 'Наименование');
		$sheet->setCellValue('C'.$startRow, 'Конечный рабочий центр нахождения');
		$sheet->setCellValue('D'.$startRow, 'Статус');
		$sheet->setCellValue('E'.$startRow, 'Кол-во арт. в коллекции шт.');
		$sheet->setCellValue('F'.$startRow, 'Кол-во готовых арт. шт.');
		$sheet->setCellValue('G'.$startRow, 'Остаток артикулов');
		$sheet->setCellValue('H'.$startRow, 'Дата');

		// высоты строк
		$sheet->getRowDimension($startRow)->setRowHeight(25);
        // ширины колоок
        for ( $columnIndex = 1; $columnIndex <= 8; $columnIndex++ ) {
            $sheet->getColumnDimensionByColumn($columnIndex)->setWidth(20);
        }

        // Шрифт 3й строки
        $fontStyleRow2 = [
            'size'  => 10,
            'color' => [
                'rgb' => '2F4F4F'
            ]
        ];
        $sheet->getStyleByColumnAndRow(1, $startRow, 8, $startRow)->getFont()->applyFromArray($fontStyleRow2);



        $borderStyleArray = array(
            'borders' => array(
                'outline' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
                'horizontal' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,//BORDER_THICK
                    'color' => array('rgb' => '000000'),
                ),
                'vertical' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
            ),
        );

        // Границы 3х строк
        $sheet->getStyleByColumnAndRow(1, 2, 8, 3)->applyFromArray($borderStyleArray);
        // горизонтальное выравнивание 3х первых строк
        $sheet->getStyleByColumnAndRow(1, 1, 8, 3)
            ->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(Style\Alignment::VERTICAL_CENTER);


        // фоновый цвет 3й строки
        $sheet->getStyleByColumnAndRow(1, 3, 8, 3)
            ->getFill()
            ->setFillType(Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('d3ffd3');



        $sheet->setTitle('Отчёт "Конечный рабочий центр"');

        $ModelRows = $this->getRow();
        $countModelRows = count(is_array( $ModelRows ) ? $ModelRows : [] );

        // Запишем вверху дату и коллекцию
        $sheet->setCellValueByColumnAndRow(1, 2, $this->collectionName . '  -  Изделий: '. $countModelRows);


        $bottomBorderPerCell = $this->getBordersArray('horPerRow');

        $startRow++; // начинаем с 3й строки
		for ( $i = 0; $i < $countModelRows; $i++ )
		{
			$thisModel = $this->drawTable2Row( $ModelRows[$i], true );
            $lastStatus = $thisModel['lastStatus'];
            $vendor_code = trim($thisModel['model']['vendor_code']);

            // заполняем колонки
            $sheet->setCellValueByColumnAndRow(1, $startRow, $vendor_code ?: $thisModel['model']['number_3d']);
            $sheet->setCellValueByColumnAndRow(2, $startRow, $thisModel['model']['model_type']);
            $sheet->setCellValueByColumnAndRow(3, $startRow, $thisModel['workingCenter']['name']);
            $sheet->setCellValueByColumnAndRow(4, $startRow, isset($lastStatus['status']['name_ru'])?$lastStatus['status']['name_ru']:"");
            $sheet->setCellValueByColumnAndRow(5, $startRow, $thisModel['sizeRange']);
            $sheet->setCellValueByColumnAndRow(6, $startRow, 0);
            $sheet->setCellValueByColumnAndRow(7, $startRow, $thisModel['vc_balance']);
            $sheet->setCellValueByColumnAndRow(8, $startRow, $this->formatDate($lastStatus['date']));

            // высоты строк
            $sheet->getRowDimension($startRow)->setRowHeight(20);

            // выноска
            if ( isset($lastStatus['status']['title']) )
            {
                $title = $lastStatus['status']['title']." - ".$lastStatus['name']?:"";
                $sheet->getCommentByColumnAndRow(4, $startRow)->getText()->createTextRun($title);

            }

            // выравнивание
            $sheet->getStyleByColumnAndRow(1, $startRow, 8, $startRow)
                ->getAlignment()
                ->setHorizontal(Style\Alignment::VERTICAL_CENTER)
                ->setVertical(Style\Alignment::VERTICAL_CENTER);

            // границы
            $sheet->getStyleByColumnAndRow(1, $startRow,8, $startRow)->applyFromArray( $bottomBorderPerCell );
            $sheet->getStyleByColumnAndRow(8, $startRow)->applyFromArray( array(
                    'borders' => array(
                        'right' => array(
                            'borderStyle' => Style\Border::BORDER_THIN,
                            'color' => array('rgb' => '000000'),
                        ),
                    ),
                )
            );

            //заполнение цветом через строку если есть остаток от деления
            if ( $i % 2 )
            {
                $sheet->getStyleByColumnAndRow(1, $startRow, 8, $startRow)
                    ->getFill()
                    ->setFillType(Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('f1edff');
            }
            $lastStatusID = $lastStatus['status']['id']??0;
            if ( (int)$lastStatusID === 11 || (int)$lastStatusID === 88)
            {
                $sheet->getStyleByColumnAndRow(1, $startRow, 8, $startRow)
                    ->getFill()
                    ->setFillType(Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('f7a8b2');
            }

            $startRow++;
            $this->progressCount( ceil( ( $i * 100 ) / $countModelRows ) );
		}

        $sheet->mergeCellsByColumnAndRow(1,$startRow,8,$startRow);

        // Границы
        $sheet->getStyleByColumnAndRow(1, $startRow,8,$startRow)->applyFromArray(array(
                'borders' => array(
                    'top' => array(
                        'borderStyle' => Style\Border::BORDER_THICK,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            )
        );

        // горизонтальное выравнивание
        $sheet->getStyleByColumnAndRow(1, $startRow,8,$startRow)
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(Style\Alignment::VERTICAL_CENTER);
        $sheet->setCellValueByColumnAndRow(1, $startRow, 'Общее кол-во выведенных изделий: '.$countModelRows);


        $this->output($spreadsheet);
	}


    /**
     * Таблица просроченных
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Exception
     */
	public function getXlsxExpired()
	{
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();


        //========= Шапка таблицы ========//
        $sheet->setTitle('Отчёт Таблица Просроченных');

        $startRow = 1;
        // Титул

        $sheet->mergeCellsByColumnAndRow(1,$startRow,5,$startRow);

        $sheet->setCellValueByColumnAndRow(1, $startRow, 'Таблица Просроченных');
        // Шрифт 2й строки

        $fontStyleTop = array(
            'name'      	=> 'Calibri',
            'size'     	    => 12,
            'bold'      	=> true,
            'italic'    	=> false,
            //'underline' 	=> Style\Font::UNDERLINE_DOUBLE,
            'strike'    	=> false,
            'superScript' 	=> false,
            'subScript' 	=> false,
            'color'     	=> array(
                'rgb' => '460e15'
            )
        );
        $sheet->getStyleByColumnAndRow(1, $startRow, 5, $startRow)->getFont()->applyFromArray($fontStyleTop);

        // высоты строк
        $sheet->getRowDimension($startRow)->setRowHeight(25);

        $startRow++;

        $sheet->mergeCellsByColumnAndRow(1,$startRow,3,$startRow);
        $sheet->mergeCellsByColumnAndRow(4,$startRow,5,$startRow);

        $sheet->setCellValueByColumnAndRow(1, $startRow, $this->collectionName);
        $sheet->setCellValueByColumnAndRow(4, $startRow, 'Дата: '. $this->formatDate(time()));
        $sheet->getRowDimension($startRow)->setRowHeight(20);

        // Шрифт 1й строки
        $fontStyleTop = array(
            'name'      	=> 'Calibri',
            'size'     	    => 12,
            'bold'      	=> true,
            'italic'    	=> false,
            //'underline' 	=> Style\Font::UNDERLINE_DOUBLE,
            'strike'    	=> false,
            'superScript' 	=> false,
            'subScript' 	=> false,
            'color'     	=> array(
                'rgb' => 'E0FFFF'
            )
        );
        $sheet->getStyleByColumnAndRow(1, $startRow, 5, $startRow)->getFont()->applyFromArray($fontStyleTop);


        // фоновый цвет 2й строки
        $sheet->getStyleByColumnAndRow(1, $startRow, 5, $startRow)
            ->getFill()
            ->setFillType(Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('4682B4');

        $startRow++;
        $sheet->setCellValue('A'.$startRow, 'Участок: ');
        $sheet->setCellValue('B'.$startRow, 'Описание участка');
        $sheet->setCellValue('C'.$startRow, 'Все Изделия');
        $sheet->setCellValue('D'.$startRow, 'Просроченные');
        $sheet->setCellValue('E'.$startRow, 'Ответственный');

        // высоты строк
        $sheet->getRowDimension($startRow)->setRowHeight(25);
        // ширины колоок
        $sheet->getColumnDimensionByColumn(1)->setWidth(30);
        $sheet->getColumnDimensionByColumn(2)->setWidth(55);
        $sheet->getColumnDimensionByColumn(3)->setWidth(15);
        $sheet->getColumnDimensionByColumn(4)->setWidth(15);
        $sheet->getColumnDimensionByColumn(5)->setWidth(20);

        // Шрифт 3й строки
        $fontStyleRow2 = [
            'size'  => 12,
            'color' => [
                'rgb' => '2F4F4F'
            ]
        ];
        $sheet->getStyleByColumnAndRow(1, $startRow, 5, $startRow)->getFont()->applyFromArray($fontStyleRow2);



        $borderStyleArray = array(
            'borders' => array(
                'outline' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
                'horizontal' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,//BORDER_THICK
                    'color' => array('rgb' => '000000'),
                ),
                'vertical' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
            ),
        );

        // Границы 3х строк
        $sheet->getStyleByColumnAndRow(1, 2, 5, 3)->applyFromArray($borderStyleArray);
        // горизонтальное выравнивание 3х первых строк
        $sheet->getStyleByColumnAndRow(1, 1, 5, 3)
            ->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(Style\Alignment::VERTICAL_CENTER);

        // фоновый цвет 3й строки
        $sheet->getStyleByColumnAndRow(1, 3, 5, 3)
            ->getFill()
            ->setFillType(Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('d3ffd3');
        //========= Конец Шапки  ========//


        $workingCentersExpired = $this->getWorkingCentersExpired(true);
        $countAll = $workingCentersExpired['countAll'];
        $countAllExpired = $workingCentersExpired['countAllExpired'];
        $workingCenters = $workingCentersExpired['workingCenters'];
        $users = $this->getUsers();

        $count = count($workingCenters);
        $i = 0;
        $startRow++;

        foreach ( $workingCenters as $workingCenter )
        {
            $wcUser = [];
            foreach ( $users as $user )
            {
                if ( $user['id'] == $workingCenter['user_id'] )
                {
                    $wcUser['fio'] = $user['fio'];
                    $wcUser['fullFio'] = $user['fullFio'];
                }
            }
            $sheet->getRowDimension($startRow)->setRowHeight(25);
            $sheet->getStyleByColumnAndRow(1, $startRow, 5, $startRow)
                ->getAlignment()
                ->setWrapText(true)
                ->setHorizontal(Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyleByColumnAndRow(1, $startRow)
                ->getAlignment()
                ->setWrapText(true)
                ->setHorizontal(Style\Alignment::HORIZONTAL_RIGHT)
                ->setVertical(Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyleByColumnAndRow(2, $startRow)
                ->getAlignment()
                ->setWrapText(true)
                ->setHorizontal(Style\Alignment::HORIZONTAL_LEFT)
                ->setVertical(Style\Alignment::VERTICAL_CENTER);
            //заполнение цветом через строку если есть остаток от деления
            if ( $i % 2 )
            {
                $sheet->getStyleByColumnAndRow(1, $startRow, 5, $startRow)
                    ->getFill()
                    ->setFillType(Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('6cffe9');
            }

            $sheet->setCellValueByColumnAndRow(1, $startRow, ($workingCenter['name']??'') ? $workingCenter['name'] . ':':'');
            $sheet->setCellValueByColumnAndRow(2, $startRow, $workingCenter['descr']??'');
            $sheet->setCellValueByColumnAndRow(3, $startRow, $workingCenter['countAll']??'');
            $sheet->setCellValueByColumnAndRow(4, $startRow, $workingCenter['expired']??'');
            $sheet->setCellValueByColumnAndRow(5, $startRow, $wcUser['fio']??'');


            $this->progressCount( floor( ( $i * 100 ) / $count ) );
            $i++;
            $startRow++;
        }

        //========= Подвал ========//
        $sheet->setCellValue('A'.$startRow, 'Всего / ');
        $sheet->setCellValue('B'.$startRow, 'Просроченных');
        $sheet->setCellValue('C'.$startRow, $countAll);
        $sheet->setCellValue('D'.$startRow, $countAllExpired);

        // Границы
        $sheet->getStyleByColumnAndRow(1, $startRow,5,$startRow)->applyFromArray(array(
                'borders' => array(
                    'top' => array(
                        'borderStyle' => Style\Border::BORDER_THICK,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            )
        );

        // выравнивание
        $sheet->getStyleByColumnAndRow(1, $startRow)
            ->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Style\Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyleByColumnAndRow(2, $startRow)
            ->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Style\Alignment::HORIZONTAL_LEFT)
            ->setVertical(Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyleByColumnAndRow(3, $startRow,5,$startRow)
            ->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(Style\Alignment::VERTICAL_CENTER);


        $this->output($spreadsheet);
	}


    /**
     * @param $spreadsheet
     */
    protected function output($spreadsheet)
    {
        $writer = new Xlsx($spreadsheet);
        ob_start();
        try {
            $writer->save('php://output');
        } catch (\Exception $e) {
            exit (json_encode(['error' => ['message'=>$e->getMessage(), 'file'=>$e->getFile(), 'line' => $e->getLine(), 'code'=>$e->getCode()], 'message'=>'Error in save() XLSX']));
        }

        $xlsData = ob_get_contents();
        ob_end_clean();

        $this->progressCount(100);
        exit( json_encode('data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,' . base64_encode($xlsData)) );

    }


}