<?php
namespace controllers;

use models\Statistic;

class StatisticController extends GeneralController
{

    /**
     * @throws \Exception
     */
    public function action()
    {
        $stat = new Statistic();
        $usersOnline = $stat->getUsersOnline();

        $compact = compact([
            'usersOnline',
        ]);

        $this->render('statistic', $compact);
    }

}