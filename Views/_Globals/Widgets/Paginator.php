<?php
namespace Views\_Globals\Widgets;

use Views\vendor\core\Widget;


class Paginator extends Widget
{

    /*
     * @var object
     * объект паганации
     */
    public $pagination = [];
    /*
     * @var array
     */
    public $options = [];

    public $template = "";
    public $size ="";
    public $class ="";
    /*
     * @var number
     * кол-во отображаемых квадратиков пагинации
     */
    public $squaresPerPage = 10;
    /*
     * @var number
     * номера след 10(?) стр.
     */
    public $nextX_Squares = "";
    /*
     * @var number
     * номера пред. 10(?) стр.
     */
    public $prevX_Squares = "";

    public $startFromPage = 1;
    public $squarePages = "";
    public $backToPage = "";

    public function init()
    {
        if ( empty($this->pagination) || !is_object($this->pagination) ) return;
        if ( empty($this->options['template']) ) 
            throw new \Exception( __CLASS__ . " Template might be not empty ",2008); // file not found
        $this->template = $this->options['template'];

        $spp = (int)$this->options['squaresPerPage'];
        if ( !empty($spp) ) $this->squaresPerPage = $spp;

        $size = (string)$this->options['size'];
        if ( !empty($size) ) {
            switch ($size)
            {
                case "normal":
                    $this->size = "";
                    break;
                case "large":
                    $this->size = "pagination-lg";
                    break;
                case "small":
                    $this->size = "pagination-sm";
                    break;
                default:
                    $this->size = "";
                    break;
            }
        }

        $class = (string)$this->options['class'];
        if ( !empty($class) ) $this->class = $class;

        $this->startFromPage();
    }

    public function startFromPage()
    {
        $countPages = $this->pagination->countPages;
        $cP = [];
        for ( $c = 1; $c <= $countPages; $c++ )
        {
            $cP[$c] = $c;
        }

        $this->squarePages = ceil( $countPages / $this->squaresPerPage );
        $var = array_fill(0, $this->squarePages, []);
        //debug($var,'var',1);
        $k = 0;
        for ( $i = 0; $i < count($var)-1; $i++ )
        {
            //$var[$i] = [];
            for ( $j = 0; $j < $this->squaresPerPage; $j++ )
            {
                $k++;
                $var[$i][] = $cP[$k];
            }
        }

        /* найдем нашу страницу в каком нибудь из полученных массивах */
        $page = $this->pagination->currentPage;
        foreach ( $var as $k => $array )
        {
            if ( in_array($page,$array) )
            {
                $first = array_key_first($array);
                $this->startFromPage = $array[$first];
                break;
            }
        }

        if ($page > $this->squaresPerPage)
        {
            $this->backToPage = $this->startFromPage - 1;
        }
    }

    public function run()
    {
        
        $this->render( $this->template );
    }

}