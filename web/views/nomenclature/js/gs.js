"use strict";

function GradingSystem()
{
    this.modal = document.querySelector('#gsEditModal');

    this.editGS_description = null;
    this.editGS_examples = null;
    this.editGS_basePercent = null;
    this.editGS_ID = null;

    this.init();
}

GradingSystem.prototype.init = function(button)
{
    let gs = this;

    // сделали textarea по высоте экрана
    let tab5 = document.querySelector('#tab5');
    let textAreas = tab5.querySelectorAll('textarea');

    let pre = document.createElement('pre');
    let appPre;
    for( let i = 0; i < textAreas.length; i++ ) {

        pre.innerHTML = textAreas[i].value;
        appPre = tab5.appendChild(pre);
        appPre.classList.add('br-0');
        let preHeight = appPre.offsetHeight;
        textAreas[i].style.height = preHeight + "px";
    }
    appPre.remove();

    //$('#gsEditModal').modal('show');
    $('#gsEditModal').on('show.bs.modal', function (e) {
        debug(e);

        gs.showGSEditModal(e);
    });

    $('#gsEditModal').on('hidden.bs.modal', function (e) {
        //let button = e.relatedTarget;
        gs.hideGSEditModal();
    });

    this.editGSButtonInit();

    debug('GradingSystem init ok!');
};

GradingSystem.prototype.showGSEditModal = function(e)
{

    let button = e.relatedTarget;
    //debug(button);
    let rowID = +button.getAttribute('data-id');
    let that = this;

    $.ajax({
        url: "/nomenclature/editGS",
        type: 'POST',
        data: {
            gsShow: 1,
            showGSID: rowID,
        },
        dataType:"json",
        success:function(data) {
            debug(data);

            that.editGS_ID = data.id;

            that.modal.querySelector('.panel-heading').innerHTML = data.work_name;

            that.modal.querySelector('.editGS_description').innerHTML = data.description;
            that.modal.querySelector('.editGS_description').value = data.description;

            that.modal.querySelector('.editGS_examples').setAttribute('value', data.examples);
            that.modal.querySelector('.editGS_examples').value = data.examples;

            that.modal.querySelector('.editGS_basePercent').setAttribute('value', data.percent);
            that.modal.querySelector('.editGS_basePercent').value = data.percent;

            if ( +data.id === 1 )
            {
                that.modal.querySelector('.editGS_Points').innerHTML = '';

                that.modal.querySelector('.editGS_PointsInput').setAttribute('value', data.points);
                that.modal.querySelector('.editGS_PointsInput').setAttribute('name', 'basePoints');
                that.modal.querySelector('.editGS_PointsInput').value = data.points;

                that.modal.querySelector('.editGS_PointsInput').classList.remove('hidden');
            } else {
                that.modal.querySelector('.editGS_Points').innerHTML = data.points;
            }

        },
        error: function (error) {
            AR.serverError(error.status, error.responseText);
        }
    });
};
GradingSystem.prototype.editGSButtonInit = function()
{
    let that = this;
    this.modal.querySelector('.editGS_edit').addEventListener('click', function() {

        let editGS_form = new FormData( document.getElementById('editGS_form') );
            editGS_form.append('editGS_ID', that.editGS_ID );
            editGS_form.append('gsEdit', '1' );

        debug(editGS_form,'editGS_form');
        $.ajax({
            url: "/nomenclature/editGS",
            type: 'POST',
            data: editGS_form,
            processData: false,
            contentType: false,
            success:function(data) {
                data = JSON.parse(data);
                debug(data);
                $('#gsEditModal').modal('hide');
                if ( data.error === 44 )
                {
                    AR.serverError(44, data.sql);
                } else if ( data.success ) {
                    AR.success("Оценка изменена.", 1);
                    AR.onClosed( function () {
                        reload(true);
                    });
                }
            },
            error: function (error) {
                AR.serverError(error.status, error.responseText);
            }
        });

    });
};
GradingSystem.prototype.hideGSEditModal = function()
{
    this.editGS_ID = 0;

    this.modal.querySelector('.editGS_PointsInput').setAttribute('value', '');
    this.modal.querySelector('.editGS_PointsInput').removeAttribute('name');
    this.modal.querySelector('.editGS_PointsInput').value = '';
    this.modal.querySelector('.editGS_PointsInput').classList.add('hidden');

};

let gs = new GradingSystem();