"use strict";

function ProgressModal()
{
    if ( !document.getElementById('modalProgress') ) return;

    this.doc = {
        doc: 'PDF',
        fileName:'',
        url:'',
        headerColor:'#1d82a6',
        icon: 'far fa-file-pdf',
        type:'',
        data:{
            userName: userName,
            tabID: tabName,
        },
        method:'GET',
        xhr: '',
        switch:'',
    };

    this.openPDFListen = false; // сообщим о том что накинули обработчик

    this.pictID = null; // ID картинки которую хотим распечатать
    
    this.init();
    
}

/**
 * Иницаализация модального окна для прогресс бара
 * запускается при создании объекта Main
 * Collection to PDF
 */
ProgressModal.prototype.setProgressModal = function(docSwitch)
{
    let doc = this.doc;
    doc.switch = docSwitch;

	if ( docSwitch === 'xls' || docSwitch === 'getXlsxFwc' || docSwitch === 'getXlsxExpired' )
	{
		doc.doc = 'Excel';
		doc.icon = 'far fa-file-excel';
		doc.url = '/main/excel-export';
		doc.headerColor = '#00a623';
		doc.method = 'GET';
		doc.data.excel = 1;
	}
    switch ( docSwitch )
    {
        case 'pdf':
            doc.doc = 'PDF';
            doc.url = '/main/collection-pdf';
            doc.data.collectionPDF = 1;
            doc.method = 'POST';
            break;
		case 'xls':
            doc.data.getXlsx = 1;
            doc.subtitle = 'Таблица Рабочих Участков';
            break;
		case 'getXlsxFwc':
			doc.data.getXlsxFwc = 1;
			doc.subtitle = 'Таблица "Конечный центр нахождения модели"';
			break;
		case 'getXlsxExpired':
			doc.data.getXlsxExpired = 1;
			doc.subtitle = 'Таблица Просроченнх';
			break;
        case 'passport':
            doc.doc = 'PDF';
            doc.method = 'POST';
            doc.url = '/model-view/?document-pdf=1';
            doc.text = 'Пасспорт';
            doc.data.id = main.getQueryParam('id');
            doc.data.document = 'passport';
            break;
        case 'runner':
            doc.doc = 'PDF';
            doc.method = 'POST';
            doc.url = '/model-view/?document-pdf=1';
            doc.text = 'Бегунок';
            doc.data.id = main.getQueryParam('id');
            doc.data.document = 'runner';
            break;
        case 'both':
            doc.doc = 'PDF';
            doc.method = 'POST';
            doc.url = '/model-view/?document-pdf=1';
            doc.text = 'Пасспорт + Бегунок';
            doc.data.id = main.getQueryParam('id');
            doc.data.document = 'both';
            break;
        case 'picture':
            doc.doc = 'PDF';
            doc.method = 'POST';
            doc.url = '/model-view/?document-pdf=1';
            doc.text = 'Картинка';
            doc.data.id = main.getQueryParam('id');
            doc.data.pictID = this.pictID;
            doc.data.document = 'picture';
            break;
    }

    debug(doc);

    let modal = $('#modalProgress');
    modal.iziModal('setTitle', 'Идёт подготовка к созданию <b>'+ doc.doc +'</b> документа.');
	if ( doc.subtitle ) modal.iziModal('setSubtitle', doc.subtitle);
    modal.iziModal('setHeaderColor', doc.headerColor);
    modal.iziModal('setIcon', doc.icon);
};

ProgressModal.prototype.onModalOpen = function(that, event)
{
    console.log('Modal is Open');

    let modalButtonsBlock = document.getElementById('modalProgress').querySelector('.modalButtonsBlock');
    let cancel = modalButtonsBlock.querySelector('.modalProgressCancel');
    let download = modalButtonsBlock.querySelector('.modalProgressDownload');
    let open = modalButtonsBlock.querySelector('.modalProgressOpen');
    let ok = modalButtonsBlock.querySelector('.modalProgressOK');
    let modal = $('#modalProgress');

    let docStr = that.searchValue ? 'Найдено ' + that.searchValue : that.collectionName;
    let doc = that.doc;

    // debug(that.collectionName,'that.collectionName');
    // debug(that.searchValue,'that.searchValue');


    that.xhr = $.ajax({
        url: doc.url,
        method: doc.method,
        cache: false,
        dataType:'json',
        data: doc.data,
        beforeSend: function() {
            cancel.classList.remove('hidden');
            docStr = doc.text ? doc.text : docStr;
            modal.iziModal('setTitle', 'Идёт создание <b>'+ doc.doc +'</b> документа: <b>' + docStr + '</b>');

            if ( doc.switch === 'xls' || doc.switch === 'getXlsxFwc' || doc.switch === 'getXlsxExpired' )
            {
                // вторым запросом добудем имя файла
                $.ajax({
                    type:'GET',
                    url: '/main/wc-file-name',
                    data: {
                        excel:1,
                        getFileName:1
                    },
                    cache: false,
                    dataType:'json',
                    success:function(data) {
                        doc.fileName = data.fileName;
                        debug(data.fileName);
                    }
                });
            }
        },
        success:function(fileName)
        {
            //if ( doc.switch === 'passport' || doc.switch === 'runner' || doc.switch === 'picture' ) doc.fileName = docStr = fileName;
            //if ( fileName.length < 12 ) doc.fileName = docStr = fileName;
            //modal.iziModal('setTitle', 'Создание <b>'+doc.doc+'</b> документа: <b>' + docStr + '</b> завершено!');

            if ( fileName.debug )
            {
                debug(fileName);
                if ( typeof debugModal === 'function' )
                {
                    debugModal( fileName.debug );
                    //edit.classList.remove('hidden');
                    return;
                }
            }
            if ( fileName.error )
            {
                AR.setDefaultMessage( 'error', 'subtitle', "Ошибка при сохранении." );
                AR.error( fileName.error.message, fileName.error.code, fileName.error );
                //edit.classList.remove('hidden');
                return;
            }

            if ( doc.switch === 'xls' || doc.switch === 'getXlsxFwc' || doc.switch === 'getXlsxExpired' )
            {
                modal.iziModal('setTitle', 'Создание <b>'+doc.doc+'</b> документа: <b>' + docStr + '</b> завершено!');

                let int = setInterval(function () {
                    if ( doc.fileName )
                    {
                        modal.iziModal('setSubtitle', "Заберите файл <b><i>'" + doc.fileName + ".xlsx'</i></b> в загрузках вашего браузера.");
                        ok.classList.remove('hidden');
                        cancel.classList.add('hidden');
                        clearInterval(int);

                        let $a = $("<a>");
                        $a.attr("href",fileName);
                        $("body").append($a);
                        $a.attr("download", doc.fileName + ".xlsx");
                        $a[0].click();
                        $a.remove();
                    }
                },500);

            } else {
                doc.fileName = docStr = fileName;
                modal.iziModal('setTitle', 'Создание <b>'+doc.doc+'</b> документа: <b>' + docStr + '</b> завершено!');

                debug(fileName);
                doc.fileName = fileName;

                cancel.classList.add('hidden');
                download.classList.remove('hidden');
                open.classList.remove('hidden');

                if ( !that.openPDFListen ) {
                    open.addEventListener('click',function () {
                        that.openPDF(doc.fileName);
                        that.openPDFListen = true;
                    });
                }

                download.setAttribute('href', _ROOT_ + 'Pdfs/' + fileName );
            }

            //that.ProgressBar(100);
        },
        error: function (error) {
            AR.serverError( error.status, error.responseText );

            //edit.classList.remove('hidden');
        }
    })/*.done(function(data)
    {
		if ( doc.switch === 'xls' || doc.switch === 'getXlsxFwc' || doc.switch === 'getXlsxExpired' )
        {
            let int = setInterval(function () {
                if ( doc.fileName )
                {
                    let $a = $("<a>");
                    $a.attr("href",data);
                    $("body").append($a);
                    $a.attr("download", doc.fileName + ".xlsx");
                    $a[0].click();
                    $a.remove();

                    clearInterval(int);
                }
            },500);
        }
    })*/;
};
ProgressModal.prototype.onModalClosing = function(main, event)
{
    console.log('Modal is closing');
    debug(main.xhr);
    if ( main.xhr.readyState !== 4 ) {
        main.xhr.abort();
        console.log('abort');
        return;
    }
    let doc = main.doc;
    debug(doc);
    if ( !doc.fileName ) return;
    // if ( doc.switch === 'pdf' || doc.switch === 'passport' || doc.switch === 'runner' )
    // {
        $.ajax({
            url: "/globals/delete",
            type: "POST",
            data: {
                isPDF: 1,
                pdfName: doc.fileName,
            },
            dataType:"json",
            success:function(data) {
                if ( data.success ) console.log('delete complete');
            }
        });
    //}
};
ProgressModal.prototype.onModalClosed = function(main, event)
{
    console.log('Modal is closed');

    let modalButtonsBlock = document.getElementById('modalProgress').querySelector('.modalButtonsBlock');
    modalButtonsBlock.querySelector('.modalProgressCancel').classList.add('hidden');
    modalButtonsBlock.querySelector('.modalProgressDownload').classList.add('hidden');
    modalButtonsBlock.querySelector('.modalProgressOpen').classList.add('hidden');
    modalButtonsBlock.querySelector('.modalProgressOK').classList.add('hidden');

    let modal = $('#modalProgress');

    modal.iziModal('setTitle', '');
    modal.iziModal('setSubtitle', '');

    main.ProgressBar(-1);
};

ProgressModal.prototype.init = function()
{
    $('#modalProgress').iziModal({
        title: '',
        transitionIn: 'comingIn',
        transitionOut: 'comingOut',
        overlayClose: false,
        afterRender: function () {
            document.getElementById('modalProgressContent').classList.remove('hidden');
        }
    });

    let that = this;
    // открылось
    $(document).on('opened', '#modalProgress', that.onModalOpen.bind(null, that) );
    // Начало закрытия
    $(document).on('closing', '#modalProgress', that.onModalClosing.bind(null, that) );
    // исчезло
    $(document).on('closed', '#modalProgress', that.onModalClosed.bind(null, that) );
};
ProgressModal.prototype.getPDF = function(doc)
{
    this.setProgressModal(doc);
    $('#modalProgress').iziModal('open');
};


ProgressModal.prototype.sendXLS = function(doc)
{
    let collectionName = document.getElementById('collectionName');
    let c_name = null;
    if (collectionName)
        c_name = collectionName.innerHTML;
    
    let topSearchInpt = document.querySelector('.topSearchInpt').getAttribute('value');

    this.searchValue = topSearchInpt;
    this.collectionName = c_name;

    if ( c_name === 'Все Коллекции' && topSearchInpt === '' ) { // уходим если выбраны все коллекции
        alert('Нужно выбрать какую нибудь коллекцию!');
        return;
    }

    this.setProgressModal(doc);
    $('#modalProgress').iziModal('open');
};

ProgressModal.prototype.sendPDF = function()
{
    let c_name = "";
    let collectionName = document.getElementById('collectionName');
    if (collectionName)
        c_name = collectionName.innerHTML;
    let topSearchInpt = document.querySelector('.topSearchInpt').getAttribute('value');

    this.searchValue = topSearchInpt;
    this.collectionName = c_name;

    debug(this.searchValue);
    debug(this.collectionName);

    if ( c_name === 'Все Коллекции' && topSearchInpt === '' ) { // уходим если выбраны все коллекции
        alert('Нужно выбрать какую нибудь коллекцию!');
        return;
    }

    this.setProgressModal('pdf');
    $('#modalProgress').iziModal('open');
};

ProgressModal.prototype.ProgressBar = function(percent)
{
    let progBar = document.querySelector('.progress-bar-success');//document.querySelector('.progressBarScript');

    if ( percent === -1 ) percent = 0;

    progBar.setAttribute('aria-valuenow', percent);
    progBar.style.width = percent + "%";
    progBar.innerHTML = percent + "%";
};
ProgressModal.prototype.openPDF = function(filename) {
    window.open( _ROOT_ + 'Pdfs/' + filename );
};



let progressModal = new ProgressModal();

function sendPDF() { // коллекции
    progressModal.sendPDF();
}
function sendXLS(drawBy)
{
	let doc = '';
	if ( +drawBy === 3 ) doc = 'xls';
	if ( +drawBy === 4 ) doc = 'getXlsxFwc';
	if ( +drawBy === 5 ) doc = 'getXlsxExpired';
		
    progressModal.sendXLS(doc);
}
function getPDF(doc) // пасспорт бегун картинка
{
    if ( doc === 'picture' )
    {
        let dopPictures = document.querySelector('.dopImages').querySelectorAll('.imageSmall');
        let pictID = 0;
        $.each(dopPictures, function (i, img) {
            if ( img.classList.contains('activeImage') )
            {
                progressModal.pictID = +img.getAttribute('data-id');
                return null;
            }
        });
    }
    if ( doc === 'pictureAll' )
    {
        progressModal.pictID = -1;
        doc = 'picture';
    }
    progressModal.getPDF(doc);
}