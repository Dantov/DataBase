<?php
namespace models;

use libs\classes\AppCodes;

class Tiles extends Main 
{

    /**
     * Main constructor.
     * @param array $searchQuery
     * @throws \Exception
     */
    public function __construct( string $searchQuery='' )
    {
        parent::__construct(searchQuery: $searchQuery);

        if ( !isset($this->assist['page']) ) 
            $this->assist['page'] = 0;
    }
	
    
    /**
     * Calculates to output models by tiles
     * @return array
     * @throws \Exception
     */
    public function getModels()
    {
        $result = [
            'showByTiles' => '',
            'iter' => 0,
        ];
        if ( !count($this->row) ) return $result;

        $posIds = [];
        foreach ($this->row as $pos)
            $posIds[] = $pos['id'];  

        $this->registerTable(['images'=>'i','stl_files'=>'stlf']);
        $rowImages = $this->IMAGES
            ->select(['pos_id','img_name','main','onbody','sketch'])
            ->where(['pos_id','IN',$posIds])
            ->exe();
        $rowStls = $this->STL_FILES
            ->select(['pos_id','stl_name'])
            ->where(['pos_id','IN',$posIds])
            ->exe();

        foreach ( $rowStls as $key => $rowStl )
        {
            $rowStls[$rowStl['pos_id']] = $rowStl;
            unset($rowStls[$key]);
        }

        ob_start();
            foreach( $this->row as $pos )
            {
                if ( !isset($pos['id']) ) continue; 
                $this->drawModel( $pos, $rowImages, $rowStls);
                $result['iter']++; // счетчик отрисованных позиций
            }
            $result['showByTiles'] = ob_get_contents();
        ob_end_clean();

        return $result;
    }


}