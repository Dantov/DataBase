<?php
/**
 * User: Admin
 * Date: 26.12.2021
 * Time: 20:05
 */
namespace models;

use soffit\{ActiveQuery,db\Table};

/**
 * Class StatusesOperations
 * Удалить / добавить / изменить - статусы в таблицах stock и statuses
 *
 */
class Statuses extends AddEdit
{
    protected Table $sts;

    public function __construct( int $id = 0 )
    {
        parent::__construct($id);
        //$aq = new ActiveQuery(['statuses']);
        $this->registerTable('statuses');
        //$this->aq->registerTable('statuses');
        //$this->STATUSES = $this->STATUSES;
    }


    /**
     * Проверка на единственный статус в табл. statuses
     * @throws \Exception
     */
    public function isSingle() : bool
    {
        $count = $this->STATUSES->count('c','pos_id')->where('pos_id','=',$this->id)->asOne('c')->exe();
        if ( $count > 1 )
            return false;

        return true;
    }

    /**
     * Выдаст array с данными последнего статуса модели, в табл. статусов
     * @param int $posID - id модели из stock
     * @return array
     * @throws \Exception
     */
    public function findLastStatus( int $posID = 0 ) : array
    {
        $modelID = $this->id;
        if ( $posID > 0 && $posID < PHP_INT_MAX )
            $modelID = $posID;

        $res = $this->STATUSES
            ->select(['*'])
            ->where('pos_id','=',$modelID)
            ->and('status','<>','')
            ->orderBy('id','DESC')
            ->asOne()
            ->exe();

        return $res;
    }

    /**
     * Проверит принадлежит ли последний проствленный статус, текущему пользователю
     * @param int $statusID
     * @param array $userData
     * @return boolean
     * @throws \Exception
     */
    public function checkStatusBelongUser( int $statusID = 0, array $userData = [] ) : bool
    {
        if ( !( $statusID > 0 && $statusID < PHP_INT_MAX) )
            $statusID = $this->findLastStatus()['status'];

        $permittedStatuses = $this->getPermittedStatuses( $userData );

        $belong = false;
        foreach ( $permittedStatuses as $permStatArr )
            if ( $statusID == $permStatArr['id'] )
                return true;

        return $belong;
    }
    /**
     * Удалить статус в таблице статусов
     * @param int $statusID
     * @return bool
     * @throws \Exception
     */
    public function deleteStatus( int $statusID ) : bool
    {
        return $this->deleteFromTable('statuses','id', $statusID);
    }
    /**
     * Поменяет данные статуса в табл. Stock на последний статус из табл. Statuses
     * @param array $updData - данные на которые нужно заменить в стоке
     * @param int $stockID
     * @return bool
     * @throws \Exception
     */
    public function updateStockStatus( array $updData, int $stockID = 0 ) : bool
    {
        if ( empty($updData) )
            throw new \Error("Status data for update is empty!", 333);

        if ( !( $stockID > 0 && $stockID < PHP_INT_MAX) )
            $stockID = $this->id;

        $statID = $updData['status'];
        $statDate = explode(' ',$updData['date'])[0];

        $this->STATUSES->update(table: "stock", row: ['status'=>$statID, 'status_date'=>$statDate], where: ['id','=',$stockID]);

        //if ( $this->baseSql($query) )
        if ( $this->STATUSES->affectedRows )
            return true;

        return false;
    }

}