"use strict";

function RepairsPN()
{
    this.repBadge = document.getElementById('repPNBadge');
    this.init();
}

RepairsPN.prototype.init = function()
{
    if ( !this.repBadge ) return;

    this.buttonsToggle();
    debug('RepairsPN Init ok!')
};

RepairsPN.prototype.buttonsToggle = function() {

    let that = this;
    // кнопка показать/скрыть все
    let showRepairs = this.repBadge.querySelector('.pn_rep_show');
    let hideRepairs = this.repBadge.querySelector('.pn_rep_hide');
    if ( !showRepairs || !hideRepairs ) return;

	hideRepairs.addEventListener('click',function(){
        let showedToasts = that.showingToasts();
        for ( let i = 0; i < showedToasts.length; i++ )
        {
            iziToast.hide({}, showedToasts[i]);
        }
    });
	
    showRepairs.addEventListener('click',function(){
		
		hideRepairs.click();
		
        $.ajax({
            url: "/globals/pushNotice",
            type: 'GET',
            data: {
                getRepairNotices: 1,
            },
            dataType:"json",
            success:function(resp) {

                if ( resp.debug )
                {
                    debug(resp);
                    if ( typeof debugModal === 'function' )
                    {
                        debugModal( resp.debug );
                        return;
                    }
                }
                if ( resp.error )
                {
                    AR.setDefaultMessage( 'error', 'subtitle', "Ошибка при сохранении." );
                    AR.error( resp.error.message, resp.error.code, resp.error );
                    return;
                }

                $.each(resp, function (i, noticeData) {
                    that.addRepairPN(noticeData);
                });

            },
            error: function (error) {
                AR.serverError( error.status, error.responseText );
            }
        });

    });

};

RepairsPN.prototype.addRepairPN = function(notice) {

    let url = _ROOT_ + "model-view/?id=" + notice.id;

    iziToast.show({
        titleSize: 12,
        titleLineHeight: 14,
        messageSize: 12,
        messageLineHeight: 12,
        imageWidth: 75,
        position: 'topRight',
        timeout: 20000,
        maxWidth: 350,
        zindex: 998,
        target: '#RepairsPNWrapp',
        theme: 'dark', // light

        id: "repairNotice_" + notice.id,
        title: "<u>Ремонт: </u>" + notice.number_3d +'/'+ notice.vendor_code + ' - ' + notice.model_type,
        message: " от <i><u>" + notice.sender + "</u></i>: " + notice.descrNeed,
        image: notice.img_name,
        icon: 'glyphicon glyphicon-'+ notice.glyphi,
        iconColor: '',
        onClosing: function(instance, toast, closedBy) {},
        onOpened: function(instance, toast){
            toast.querySelector('.iziToast-icon').setAttribute('title', notice.title);
            toast.children[0].addEventListener('click',function() {
                document.location.href = url;
            });
        },
    });

};
RepairsPN.prototype.showingToasts = function() {
    return document.getElementById('RepairsPNWrapp').querySelectorAll('.iziToast');
};

RepairsPN.prototype.countComingNotice = function(notice) {
    this.addRepairPN(notice);
    let digit = this.repBadge.querySelector('.da_Badge').innerHTML;
    this.repBadge.querySelector('.da_Badge').innerHTML = ++digit + '';
};

let rpn = function (){
    if ( wsUserData.fio === 'Гость' ) return;
    if ( !_PNSHOW_ ) return;

    if ( !repairNotices )
    {
        repairNotices = new RepairsPN();
    }
}();
