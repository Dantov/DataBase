<?php
namespace Views\_Nomenclature\Models;
use Views\_Globals\Models\General;

class GradingSystemModel extends General
{

    const WRONG_ID = 33;
    const SQL_ERROR = 44;
    const SUCCESS = 1;

    /**
     * GradingSystemModel constructor.
     * @throws \Exception
     */
    public function __construct()
	{
		parent::__construct();
	}

    /**
     * @return array
     * @throws \Exception
     */
    public function getData()
    {

        $gs = $this->findAsArray("select * from grading_system");

        //debug($gs,'',1);

        $result = [];
        foreach ( $gs as $row ) $result[$row['work_name']][] = $row;

        //debug($result,'',1);
        return $result;
	}

    /**
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getGSRowByID( int $id ) : array
    {
        return $this->findOne("select * from grading_system WHERE id='$id'");
    }

    /**
     * @param string $description
     * @param float $percent
     * @param string $examples
     * @param int $editGS_ID
     * @param float $basePoints
     * @return array
     * @throws \Exception
     */
    public function editGSPos( string $description, float $percent, string $examples, int $editGS_ID, float $basePoints = 0.0 ) : array
    {
        if ( !$this->findOne("select 1 from grading_system WHERE id='$editGS_ID' ") )
            return ['error'=>self::WRONG_ID];

        if ( $editGS_ID === 1 && $basePoints > 0 )
        {
            $sqlBP = "UPDATE grading_system SET points='$basePoints' WHERE id='1'";
            //$this->baseSql($sqlBP);
            $this->update(table: "grading_system", row: ['points'=>$basePoints], where: ['id','=',1]);

            $allRows = $this->findAsArray("SELECT id,percent,points FROM grading_system WHERE id<>1 ");
            foreach ( $allRows as &$gsRow )
                $gsRow['points'] = round(($basePoints * $gsRow['percent']) / 100, 2);

            if ( $this->insertUpdateRows($allRows, 'grading_system') !== -1 )
                return ['success'=>self::SUCCESS,'error'=>0];
        } else {
            $basePoints = (float)$this->findOne("select points from grading_system WHERE id='1' ")['points'];
            $newPoints =  round(($basePoints * $percent) / 100, 2);

            //$sql = "UPDATE grading_system SET description='$description', examples='$examples', percent='$percent', points='$newPoints' WHERE id='$editGS_ID'";
            //if ( $this->baseSql($sql) )
            $updRow = [
                'description'=>$description, 
                'examples'=>$examples, 
                'percent'=>$percent, 
                'points'=>$newPoints,
            ];

            try {
                $this->update(table: "grading_system", row: $updRow, where: ['id','=',$editGS_ID]);
            } catch ( \Exception | \Error $ex ) {
                return ['error'=>self::SQL_ERROR, 'sql'=> $ex->getMessage() ];
            } finally {
                if ( $this->affectedRows )
                    return ['success'=>self::SUCCESS,'error'=>0];
            }
            
            return ['error'=>4, 'sql'=>"some error occured" ];
        }

        return ['success'=>1, 'error'=>0];
    }

}