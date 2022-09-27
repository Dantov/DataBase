<a class="btn btn-primary absolute" style="top:51px;" role="button" data-toggle="collapse" href="#collapseDebug" aria-expanded="false" aria-controls="collapseDebug" title="overral DB query's">Debug (sql): <?=Views\vendor\core\db\Database::$overallQuerys?></a>
<div class="collapse absolute" style="top:90px; z-index: 9000;" id="collapseDebug">
  <div class="well">
    <?php
        //debug(_rootDIR_,'_rootDIR_');
        //debug(_coreDIR_,'_coreDIR_');
        //debug(_stockDIR_,'_stockDIR_');
        debug($this->getQueryParams(),'QueryParams');
        debug($_SESSION,'$_SESSION');
        debug($_COOKIE,'$_COOKIE');
        debug( Views\vendor\core\Config::get(), "Config");
    ?>
  </div>
  <?php //debug($_SERVER,'$_SERVER'); ?>
</div>