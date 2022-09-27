<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="approveModalLabel">Утвердить эскиз в работу?</h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-triangle-left" aria-hidden="true"></span> Отмена</button>
                <button type="button" class="btn btn-primary pull-right approveSubmit"></button>
            </div>
        </div>
    </div>
</div>

<div id="alertResponse" aria-hidden="true" aria-labelledby="alertResponse" role="dialog" class="iziModal">
  <div id="alertResponseContent" style="padding: 10px" class="hidden">
  </div>
</div>

<div class="modal fade" id="approveResultModal" tabindex="-1" role="dialog" aria-labelledby="approveResultLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="approveResultLabel"></h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" onclick="document.location.reload(true);" class="btn btn-success centered" data-dismiss="modal"><span class="glyphicon glyphicon-ok-circle" aria-hidden="true"></span>OK</button>
            </div>
        </div>
    </div>
</div>