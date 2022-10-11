<?php
/**
 * Date: 03.12.2020
 * Time: 17:47
 */
namespace models;

class SaveModelProgressCounter extends ProgressCounter
{

    /**
     * @var int
     * текущее ко-во прогресса
     */
    public $currentProgresses = 0;

    /**
     * @var int
     * общее кол-во процессов
     */
    public $overallProcesses = 0;

    public function __construct( string $userName, string $tabID, int $overallProcesses = 0 )
    {
        $this->overallProcesses = $overallProcesses;
        parent::__construct();

        $this->setProgress($userName, $tabID);
    }

    public function __toString()
    {
        return "CurrPr: " . $this->currentProgresses . "OverPr: " . $this->overallProcesses;
    }

    public function count( int $percent=0 )
    {
        if ( $this->currentProgresses >= $this->overallProcesses )
        {
            $this->progressCount(100);
            return;
        }

        $newPercent = ceil( ( ++$this->currentProgresses * 100 ) / $this->overallProcesses );
        if ( $percent ) $newPercent = $percent;
        $this->progressCount($newPercent);
    }
}