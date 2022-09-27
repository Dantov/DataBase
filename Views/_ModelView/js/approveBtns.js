"use strict";

function Approves()
{
    this.modal = document.querySelector('#approveModal');

    this.approveSketchBtn = document.querySelector('#approveSketchBtn');
    this.approve3DTechBtn = document.querySelector('#approve3DTechBtn');

    this.modalTitle = this.modal.querySelector('.modal-title');
    this.modalBody = this.modal.querySelector('.modal-body');
    this.approveSubmit = this.modal.querySelector('.approveSubmit');

    this.operationStatus = false;

    this.respText = '';

    this.url = '/model-view/?approve=1';
    this.apprData = {
        id: main.getQueryParam('id'),
        approve: '',
    };
    this.init();
}

Approves.prototype.init = function()
{
    let that = this;

    $('#approveModal').on('show.bs.modal', function (e) {
        //debug(e.relatedTarget);
        that.showingModal(e.relatedTarget);
    });
    $('#approveModal').on('hide.bs.modal', function () {
        that.hideModal();
    });
    $('#approveResultModal').on('hide.bs.modal', function () {
        this.querySelector(".modal-title").innerHTML = "";
        this.querySelector(".modal-body").innerHTML = "";
    });

    this.approveSubmit.addEventListener('click',function () {
        that.signModel(this);
    },false);

    $("#alertResponse").iziModal({
        timeout: 5000,
        zindex: 1100,
        timeoutProgressbar: true,
        pauseOnHover: true,
        restoreDefaultContent: false,
    });

    $(document).on('closing', '#alertResponse', function (e) {
        if ( that.operationStatus === false )
        {
            //pm.approveSubmit.classList.remove('disabled');
        }
    });
    $(document).on('closed', '#alertResponse', function (e) {
        if ( that.operationStatus === true )
            document.location.reload(true);
    });

    debug('Approves init ok!');
};

Approves.prototype.hideModal = function()
{
    this.modalTitle.innerHTML = "";
    this.modalBody.innerHTML = "";
    this.approveSubmit.innerHTML = "";
    this.apprData.approve = "";
    this.modal.querySelector('.approveSubmit').classList.remove('disabled');
};

Approves.prototype.showingModal = function(btn)
{
    let idBtn = btn.id;
    let txt = "";
    this.modalTitle.parentElement.classList.add('bg-info');
    switch ( idBtn )
    {
        case "approveSketchBtn":

            txt = '<i class="fas fa-magic"></i>';
            txt += " Худ. Совет - <b><i>" + wsUserData.fio + "</i></b>";

            this.modalTitle.innerHTML = txt;
            this.modalBody.innerHTML = "Утвердить этот эскиз в работу?";
            this.approveSubmit.innerHTML = '<i class="fas fa-magic"></i>' + " Утвердить";
            this.apprData.approve = 'approveSketch';

            this.respText = "<b>Эскиз утвержден! </b>";
            break;
        case "approve3DTechBtn":
            txt = '<span class="glyphicon glyphicon-education" aria-hidden="true"></span>';
            txt += " Технолог - <b><i>" + wsUserData.fio + "</i></b>";

            this.modalTitle.innerHTML = txt;
            this.modalBody.innerHTML = "<b>Подписать эту 3Д модель?</b>";
            this.approveSubmit.innerHTML = '<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>' + " Подписать";
            this.apprData.approve = 'signByTech';

            this.respText = "<b>Модель подписана! </b>";
            break;
    }
};

Approves.prototype.signModel = function(btn)
{
    let that = this;
    $.ajax({
        type: 'POST',
        url: that.url,
        data: that.apprData,
        dataType:"json",
        cache: false,
        beforeSend: function() {
            btn.classList.add('disabled');
        },
        success:function(response) {
            debug(response,'response');
            $('#approveModal').modal('hide');
            let apprModRes = document.getElementById('approveResultModal');

            if ( response.success )
            {
                that.resultModalCall( apprModRes, 'success', that.respText + response.success.message, response.success.code);
            } else if ( response.error )
            {
                that.resultModalCall( apprModRes, 'error', response.error.message, response.error.code);
            } else {
                that.resultModalCall( apprModRes );
            }
        },
        error:function (error) {
            $('#approveModal').modal('hide');
            that.resultModalCall( document.getElementById('approveResultModal'), 'serverError', error.responseText, error.status );
        }
    });
};

Approves.prototype.resultModalCall = function( modalObj, callType, message, code )
{
    // if ( !modalObj ) return;

    // let resModalTitle = modalObj.querySelector('#approveResultLabel');
    // let resModalBody = modalObj.querySelector('.modal-body');

    // let i = document.createElement('i');
    // let span = document.createElement('span');

    switch (callType)
    {
        case "success":
        {
            this.operationStatus = true;
            $('#alertResponse').iziModal('setHeaderColor', '#d09d16');
            $('#alertResponse').iziModal('setIcon', 'far fa-check-circle');
            $('#alertResponse').iziModal('setTitle', message);
            $('#alertResponse').iziModal('setSubtitle', 'Операция прошла успешно!');
            // resModalTitle.parentElement.classList.add('bg-success');
            // resModalTitle.classList.add('text-bold');
            // i.setAttribute('class','far fa-check-square');
            // span.innerHTML = " Операция завершена успешно! ";

        } break;
        case "error":
        {
            $('#alertResponse').iziModal('setHeaderColor', 'rgb(189, 91, 91)');
            $('#alertResponse').iziModal('setIcon', 'fas fa-exclamation-triangle');
            $('#alertResponse').iziModal('setTitle', 'Операция завершена с ошибкой! ' + message );
            $('#alertResponse').iziModal('setSubtitle', "Код " + code);
        
            // resModalTitle.parentElement.classList.add('bg-warning');
            // resModalTitle.classList.add('text-bold');
            // i.setAttribute('class','fas fa-exclamation-triangle');
            // span.innerHTML = " Операция завершена с ошибкой! " + code;
        } break;
        case "serverError":
        {
            $('#alertResponse').iziModal('setHeaderColor', 'rgb(189, 91, 91)');
            $('#alertResponse').iziModal('setIcon', 'fas fa-bug');
            $('#alertResponse').iziModal('setTitle', 'Ошибка на сервере! ' + message );
            $('#alertResponse').iziModal('setSubtitle', "Код " + code);

            // resModalTitle.parentElement.classList.add('bg-danger');
            // resModalTitle.classList.add('text-bold');
            // i.setAttribute('class','fas fa-bug');
            // span.innerHTML = " Ошибка! " + code;
        } break;
        default:
        {
            $('#alertResponse').iziModal('setIcon', 'fas fa-exclamation-circle');
            $('#alertResponse').iziModal('setTitle', '' + message);
            $('#alertResponse').iziModal('setSubtitle', code);
            // resModalTitle.parentElement.classList.add('bg-info');
            // i.setAttribute('class','fas fa-exclamation-circle');
            // span.innerHTML = " " + code;
        } break;
    }

    //resModalTitle.appendChild(i);
    //resModalTitle.appendChild(span);


    // if ( message )
    //     resModalBody.innerHTML = message;
    //$(modalObj).modal('show');
    $("#alertResponse").iziModal("open");
};

let approves = new Approves();