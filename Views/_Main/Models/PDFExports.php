<?php
namespace Views\_Main\Models;

class PDFExports extends Main {

    protected $toPdf;
    public $searchFor = '';
    public $collectionName = '';
    public $foundRow = '';
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
     * @param $assist
     * @param $user
     * @param $foundRow
     * @param string $searchFor
     * @param string $collectionName
     * @throws \Exception
     */
	function __construct( $assist, $user, $foundRow = [], $searchFor='', $collectionName='' )
	{
		parent::__construct( $assist, $user, $foundRow);
		$this->toPdf = true;
		if ( !empty($searchFor) ) $this->searchFor = $searchFor;
		if ( !empty($collectionName) ) $this->collectionName = $collectionName;
		if ( !empty($foundRow) ) $this->foundRow = $foundRow;

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

    public function headerNameString()
    {
        if ( empty($this->foundRow) )
        {
            $trans_str = $this->collectionName;
        } else {
            //костыль. если ищем по дате
            $trans_str = str_ireplace("::", "", $this->searchFor);
        }
        if ( empty($trans_str) ) $trans_str = 'Выделенное_';

        return $trans_str;
    }

	protected function get_Images_FromPos($id)
    {
		$images_src = array();
		//$img_quer = mysqli_query($this->connection, " SELECT img_name, main, detail FROM images WHERE pos_id='$id' ");
        $img_quer = $this->findAsArray(" SELECT img_name, main, detail FROM images WHERE pos_id='$id' ");
		
		//while ( $row_images = mysqli_fetch_assoc($img_quer) ){
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