<?php

namespace Views\_Globals\Widgets;

class Pagination
{

    /*
     * @var number
     * По сколько отображать на одной стр.
     */
    public $itemsPerPage = "";
    /*
     * @var number
     * общее ко-во элементов для отображения
     */
    public $total = 0;
    /*
     * @var number
     * необходимое ко-во страниц для отображения $count элементов
     */
    public $countPages = "";
    /*
     * @var number
     * номер текцщей стр.
     */
    public $currentPage = "";
    /*
     * @var number
     * кол-во отображаемых квадратиков пагинации
     */
    public $showedSquares = 10;
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


    public function __construct( int $totalCount, int $perPage, int $page )
    {
        $this->total = (int)$totalCount;
        $this->itemsPerPage = (int)$perPage;

        $this->countPages = $this->getCount();
        //debug($this->countPages,'countPages');
        //debug($this->total,'total');

        $this->currentPage = $this->getCurrentPage($page);
        //debug($this->currentPage,'currentPage');
    }

    public function getStart()
    {
        return ( $this->currentPage - 1 ) * $this->itemsPerPage;
    }

    public function getCount()
    {
        if ( $this->itemsPerPage < 1 || !is_int($this->itemsPerPage) ) $this->itemsPerPage = 1;
        return ceil( $this->total / $this->itemsPerPage ) ?: 1;
    }

    public function getCurrentPage( int $page ) : int
    {
        if ( $page < 1 ) $page = 1;
        if ( $page > $this->countPages ) $page = $this->countPages;
        return $page;
    }


}