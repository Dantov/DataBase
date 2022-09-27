"use strict";

function New3DPN()
{
    this.badge = document.getElementById('new3DPNBadge');
    this.init();
}

New3DPN.prototype.init = function()
{
    if ( !this.badge ) return;

    this.buttonsToggle();
    debug('New3DPN Init ok!')
};

New3DPN.prototype.buttonsToggle = function() {

    let that = this;
    // кнопка показать/скрыть все
    let show = this.badge.querySelector('.pn_show');
    let hide = this.badge.querySelector('.pn_hide');
    if ( !show || !hide ) return;

	hide.addEventListener('click',function(){
        let showedToasts = that.showingToasts();
        for ( let i = 0; i < showedToasts.length; i++ )
        {
            iziToast.hide({}, showedToasts[i]);
        }
    });
	
    show.addEventListener('click',function(){
		
		hide.click();
		
        $.ajax({
            url: "/globals/pushNotice",
            type: 'GET',
            data: {
                getNew3DNotices: 1,
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
                    AR.setDefaultMessage( 'error', 'subtitle', "Ошибка при получении уведомлений." );
                    AR.error( resp.error.message, resp.error.code, resp.error );
                    return;
                }

                $.each(resp, function (i, noticeData) {
                    that.addPN(noticeData);
                });

            },
            error: function (error) {
                AR.serverError( error.status, error.responseText );
            }
        });

    });

    

};

New3DPN.prototype.addPN = function(notice) {

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
        target: '#new3DNoticeWrapp',
        theme: 'light', // dark

        id: "new3DNotice_" + notice.id,
        title: '<u>В работу: </u>' + notice.number_3d +'/'+ notice.vendor_code + ' - ' + notice.model_type,
        message: "<i><u>" + notice.description + "</u></i>",
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
New3DPN.prototype.showingToasts = function() {
    return document.getElementById('new3DNoticeWrapp').querySelectorAll('.iziToast');
};

New3DPN.prototype.countComingNotice = function(notice) {
    this.addPN(notice);
    let digit = this.badge.querySelector('.da_Badge').innerHTML;
    this.badge.querySelector('.da_Badge').innerHTML = ++digit + '';
};

let new3DPN = function (){
    if ( wsUserData.fio === 'Гость' ) return;
    if ( !_PNSHOW_ ) return;

    if ( !new3DNotices )
    {
        new3DNotices = new New3DPN();
    }
}();
