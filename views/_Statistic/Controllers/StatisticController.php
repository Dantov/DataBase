<?php
namespace Views\_Statistic\Controllers;
use Views\_Statistic\Models\Statistic;
use Views\_Globals\Controllers\GeneralController;


class StatisticController extends GeneralController
{

    /**
     * @throws \Exception
     */
    public function action()
    {
        $stat = new Statistic();
        $usersOnline = $stat->getUsersOnline();
        //$userOS = $stat->getUserOSData();

        $compact = compact([
            'usersOnline','userOS',
        ]);

        $this->render('statistic', $compact);
    }

}