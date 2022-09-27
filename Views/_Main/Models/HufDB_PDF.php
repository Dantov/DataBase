<?php
namespace Views\_Main\Models;

/**
 * Created by PhpStorm.
 * User: Dant
 * Date: 12.01.2020
 * Time: 16:24
 */
require_once _vendorDIR_ . "tecnickcom/tcpdf/tcpdf.php";
class HufDB_PDF extends \TCPDF
{
    public $headerName;

    public function __construct(string $orientation = 'P', string $unit = 'mm', string $format = 'A4', bool $unicode = true, string $encoding = 'UTF-8', bool $diskcache = false, bool $pdfa = false, $headerName = '')
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

        if ( !empty($headerName) ) $this->headerName = $headerName;
    }

    //Page header
    public function Header()
    {
        $date = date('d.m.Y');
        if ( empty($_SESSION['foundRow']) )
        {
            $coll_name = 'Коллекция: '.$_SESSION['assist']['collectionName'].'_'.$date;
        } elseif ( !empty($_SESSION['searchFor']) ) {
            $coll_name = 'Найдено: '. $_SESSION['searchFor'] . ' - ' .$date;
        } else {
            $coll_name = 'Выделенное - '.$date;
        }

        // Set font
        $this->SetFont('dejavusans', '', 12);
        $this->SetTextColor( 167,167,167 ); // серый
        $this->setTextShadow(array('enabled'=>false, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
        // Title
        $this->Cell(0, 10, $coll_name, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
    }

    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-12);
        // Set font
        $this->SetFont('dejavusans', 'I', 9);
        // Page number
        $this->Cell(308, 10, ''.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}