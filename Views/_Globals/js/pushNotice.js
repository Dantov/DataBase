"use strict";

function PushNotice()
{
    this.pushNoticeBadge = document.querySelector('.pushNoticeBadge');

    // только что полученные уведомления от checkNotice - полные данные
    // массив объектов
    this.incomingNotices = [];

    //delete localStorage.showedNotice;
    /**
     * массив строк, здесь хранятся ID показанных уведомлений
     * @type {Array}
     */
    this.showedNotice = [];
    if ( localStorage.getItem('showedNotice') )
    {
        this.showedNotice = JSON.parse( localStorage.getItem('showedNotice') );
    }

    this.pushNoticeBadgeInc();
    this.noticesBadgeToggle();

    let that = this;
    iziToast.settings({
    	titleSize: 12,
		titleLineHeight: 14,
		messageSize: 12,
		messageLineHeight: 12,
		imageWidth: 75,
		position: 'topRight',
		timeout: 20000,
		maxWidth: 350,
        zindex: 998,
        target: '#pushNoticeWrapp',
        onClosing: function(instance, toast, closedBy) {
            if ( closedBy === 'timeout' )
            {
                // когда нажали на Спрятать что бы второй раз не вносить показанные в массив
                if ( that.showedNotice.includes(toast.id) ) return;

                that.showedNotice.push(toast.id);
                localStorage.setItem('showedNotice', JSON.stringify(that.showedNotice));
                that.pushNoticeBadgeInc();
                console.info('closedBy: ' + closedBy); // tells if it was closed by 'drag' or 'button'
            }

            if ( closedBy === 'button' || closedBy === 'drag' )
            {
                //debug(closedBy,'closedBy');
                that.closeNotice(toast.id);
            }

            //debug( JSON.parse( localStorage.getItem('showedNotice') ) );
        }
	});

    debug('PushNotice Init ok!');
}

PushNotice.prototype.pushNoticeBadgeInc = function() {
    if ( !this.pushNoticeBadge )
        return;

    this.pushNoticeBadge.innerHTML = this.showedNotice.length;
};

PushNotice.prototype.closeAllNotices = function() {
	
	

	let that = this;
    $.ajax({
        url: "/globals/pushNotice",
        type: 'POST',
        data: {
            PushNotice: 1,
            closeAllPN: 1,
            closeById: that.showedNotice,
        },
        dataType:"json",
        success:function(data) {
            if ( !data.done ) return console.log('Ошибка закрытия.' + data.done);

            that.incomingNotices = [];
            that.showedNotice = [];
            that.pushNoticeBadgeInc();
            localStorage.setItem('showedNotice', JSON.stringify(that.showedNotice));
        }
    });
	
};

/**
 * Закрытие по крестику для каждого тоста
 * @param id
 * @param url string
 */
PushNotice.prototype.closeNotice = function(id, url)
{
    //ajax запрос на поставку ип адреса в таблицу
    let that = this;
    $.ajax({
        url: "/globals/pushNotice",
        type: 'POST',
        data: {
            PushNotice: 1,
            closeNotice: id
        },
        dataType:"json",
        success:function(data) {
            let objects = that.incomingNotices;

            for ( let i = 0; i < objects.length; i++) {
                if (objects[i].not_id === id) {
                    that.incomingNotices.splice(i, 1);
                    break;
                }
            }
            that.showedNotice.forEach((item, i) => {
                if (item === id) {
                    that.showedNotice.splice(i, 1);
                    localStorage.setItem('showedNotice', JSON.stringify(that.showedNotice));
                    return false;
                }
            });
            that.pushNoticeBadgeInc();

            if ( url ) document.location.href = url;
            //debug(that.showedNotice,'closeNotice-showedNotice');
        }
    });
};

PushNotice.prototype.addNotice = function(notice)
{
    let found = false;
    for (let object of this.incomingNotices) {
        if (object.not_id === notice.not_id) {
            found = true;
            break;
        }
    }
    if ( !found ) {
        this.incomingNotices.push(notice);
        //debug(this.incomingNotices,'incomingNotices');
    }
    
    

    let addStr, newNotice;
    let that = this;
    let url = _ROOT_ + "model-view/?id=" + notice.pos_id;

    if ( +notice['addEdit'] === 1 ) addStr = "Добавлена новая";
    if ( +notice['addEdit'] === 2 ) addStr = "Изменена";
    if ( +notice['addEdit'] === 3 ) addStr = "Удалена";

	let glyphi,title,img_src;
	
	// переходим на модель при клике и ставим IP если она не удалена
	if ( +notice['addEdit'] !== 3 ) {
		glyphi = notice.status.glyphi;
		title = notice.status.title;
		img_src = notice.img_src;
	} else { // модель удалена
		glyphi = 'remove-sign';
		title = 'Модель удалена!';
		img_src = _URL_ + '/web/picts/deleted.png';
	}
	
    iziToast.show({
		id: notice.not_id,
		title: notice.number_3d +'/'+ notice.vendor_code + ' - ' + notice.model_type,
		message: addStr + ' модель!',
		image: img_src,
		icon: 'glyphicon glyphicon-'+ glyphi,
		iconColor: '',
	});
	
	newNotice = document.getElementById(notice.not_id);
    newNotice.querySelector('.iziToast-icon').setAttribute('title', title);
    
	if ( +notice['addEdit'] !== 3 ) {
		newNotice.children[0].addEventListener('click',function() {
			that.closeNotice(notice.not_id, url);
		});
	}
    
};

PushNotice.prototype.checkNotice = function() {

    let that = this;

    $.ajax({
        url: "/globals/pushNotice",//"/Glob_Controllers/pushNoticeController.php",
        type: 'POST',
        data: {
            PushNotice: 1,
            checkNotice: 1,
        },
        dataType:"json",
        success:function(data) {
            if ( data.debug )
            {
                debugModal(data.debug);
            }
            console.log('incomingNotices = ', data);
            if ( typeof data !== 'object' ) return;
            that.incomingNotices = data;

            for ( let i = 0; i < data.length; i++ )
            {
                // если оно есть в массиве, значит уже показано - уходим.
                if ( that.showedNotice.includes(data[i].not_id) ) continue;
                that.addNotice(data[i]);
            }
            
            /// синхронизируем актуальные уведомления в data с адишниками в localStorage
            //  нужно что б нормально работало из под разные браузеров на одном IP
            let actual = [];
            //debug(that.showedNotice,'showedNotice');
            for ( let i = 0; i < that.showedNotice.length; i++ )
            {
                let id = that.showedNotice[i];
                for ( let j = 0; j < data.length; j++ )
                {
                    if ( id == data[j].not_id ) actual.push(id);
                }
            }
            
            //debug(actual,'actual');
            
            that.showedNotice = actual;
            localStorage.setItem('showedNotice', JSON.stringify(that.showedNotice));
            that.pushNoticeBadgeInc();
        }
    });
};
/**
 * showingToasts()
 * В данный момент отображаемые тосты
 * на которых еще не истек таймаут
 */
PushNotice.prototype.showingToasts = function() {
    return document.getElementById('pushNoticeWrapp').querySelectorAll('.iziToast');
};

/**
 * Обработчики на кнопки под баджем
 */
PushNotice.prototype.noticesBadgeToggle = function() {
	let that = this;
	let noticesBadge = document.getElementById('noticesBadge');
    if ( !noticesBadge ) return;

	function hideNotices()
    {
        let showedToasts = that.showingToasts();
        for ( let i = 0; i < showedToasts.length; i++ )
        {
            iziToast.hide({}, showedToasts[i]);
        }
    }

    // кнопка показать все
	noticesBadge.querySelector('.noticeShow').addEventListener('click',function(){

        hideNotices(); // сначало прячем все открытые

        // потом заного открываем все с новыми таймаутами
		//debug(that.incomingNotices,'показываем');
        for ( let i = 0; i < that.incomingNotices.length; i++ )
        {
            that.addNotice(that.incomingNotices[i]);
        }
	});

	// кнопка скрыть все
	noticesBadge.querySelector('.noticeHide').addEventListener('click', function(){

        let showedToasts = that.showingToasts();
        for ( let i = 0; i < showedToasts.length; i++ )
        {
        	//debug(showedToasts[i].id, 'showedToastsID');
            // если оно есть в массиве, не добавляем снова.
            if ( that.showedNotice.includes(showedToasts[i].id) ) continue;
            that.showedNotice.push(showedToasts[i].id);
            that.pushNoticeBadgeInc();
        }
        
        localStorage.setItem('showedNotice', JSON.stringify(that.showedNotice));
        
        hideNotices();
    });

	// кнопка убрать все
	noticesBadge.querySelector('.noticeCloseAll').addEventListener('click',function(){
		noticesBadge.querySelector('.noticeHide').click();
		
	    that.closeAllNotices();
        hideNotices();
	});
	
};

window.onload = function()
{
    if ( wsUserData.fio === 'Гость' ) return;
    if ( !_PNSHOW_ ) return;

    if ( !pushNotice )
    {
        pushNotice = new PushNotice();
    }
    pushNotice.checkNotice();
};
