<div class="row">
    <p class="lead text-info text-center">Опции</p>
    <div class="col-xs-12 stats_table">

        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#tab1" role="tab" data-toggle="tab">Общие</a></li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active in fade" id="tab1">
                <center><h4></h4></center>
                <p><label for="width_control" title="Для широкоформатных мониторов" style="cursor:pointer;">Во всю ширину экрана</label> &nbsp;<input id="width_control" type="checkbox" <?=$widthCheck?> ></p>
                <p><label for="PN_control" style="cursor:pointer;">Показывать уведомления</label> &nbsp;<input id="PN_control" type="checkbox" <?=$PushNoticeCheck?> ></p>
                <p>Цвета фона:
                    <div class="row">
                        <?php for( $i = 0; $i < count($bgsImg); $i++ ): ?>
                            <div class="col-xs-6 col-sm-4 col-md-2 status123">
                                <input id="bg_img<?=$i;?>" <?=$bgsImg[$i]['checked'];?> data-class="<?=$bgsImg[$i]['body'];?>" name="bg_img" type="radio" class="bg_img">
                                <label for="bg_img<?=$i;?>" style="cursor:pointer;">
                                    <div class="<?=$bgsImg[$i]['prev'];?> img-responsive" style="width:165px; height:92px;"></div>
                                </label>
                            </div>
                        <?php endfor; ?>
                    </div>
                </p>
            </div> <!-- end of panel 1 -->
        </div><!-- end of Tab content -->
    </div>

    <a class="btn btn-default" type="button" href="<?=$_SESSION['prevPage'];?>"><span class="glyphicon glyphicon-triangle-left"></span> Назад</a>
</div><!--row-->