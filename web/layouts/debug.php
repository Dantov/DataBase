<a class="btn btn-primary absolute" style="top:51px;" role="button" data-toggle="collapse" href="#collapseDebug" aria-expanded="false" aria-controls="collapseDebug" title="overral DB query's">Debug (sql): <?=soffit\db\Database::$overallQuerys?></a>
<div class="collapse absolute" style="top:90px; z-index: 9000;" id="collapseDebug">
  <div class="well">
    <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#getQueryParams" aria-expanded="false" aria-controls="getQueryParams">getQueryParams</button>
    <button class="btn btn-success" type="button" data-toggle="collapse" data-target="#_SESSION" aria-expanded="false" aria-controls="_SESSION">SESSION</button>
    <button class="btn btn-warning" type="button" data-toggle="collapse" data-target="#_COOKIE" aria-expanded="false" aria-controls="_COOKIE">COOKIE</button>
    <button class="btn btn-danger" type="button" data-toggle="collapse" data-target="#Config" aria-expanded="false" aria-controls="Config">Config</button>
    <button class="btn btn-default" type="button" data-toggle="collapse" data-target="#_SERVER" aria-expanded="false" aria-controls="_SERVER">SERVER</button>
    <?php
        //debug(_rootDIR_,'_rootDIR_');
        //debug(_coreDIR_,'_coreDIR_');
        //debug(_stockDIR_,'_stockDIR_');
        //debug($_SESSION,'$_SESSION');
        //debug($_COOKIE,'$_COOKIE');
        //debug( soffit\Config::get(), "Config");
        //debug($_SERVER,'$_SERVER');
    ?>
  </div>
  <div class="row">
    <div class="col-xs-3">
      <div class="collapse" id="getQueryParams"><?php debug($this->getQueryParams()); ?></div>
    </div>
    <div class="col-xs-4">
      <div class="collapse" id="_SESSION"><?php debug($_SESSION); ?></div>
    </div>
    <div class="col-xs-4">
      <div class="collapse" id="_COOKIE"><?php debug($_COOKIE); ?></div>    
    </div>
    <div class="col-xs-4">
      <div class="collapse" id="Config"><?php debug(soffit\Config::get()); ?></div>
    </div>
    <div class="col-xs-4">
      <div class="collapse" id="_SERVER"><?php debug($_SERVER); ?></div>
    </div>  
  </div>
</div>