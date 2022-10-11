"use strict";

function DeleteModal() {

	this.dellObj = {};

    this.init();
}

DeleteModal.prototype.init = function()
{
    debug('init delete modal');
    $('#modalDelete').iziModal({
        title: '',
        headerColor: '#ff3f36',
        icon: 'glyphicon glyphicon-trash',
        transitionIn: 'comingIn',
        transitionOut: 'comingOut',
        overlayClose: false,
        closeButton: true,
        afterRender: function () {
            document.getElementById('modalDeleteContent').classList.remove('hidden');
        }
    });

    let that = this;
    // начало открытия
    //$(document).on('opening', '#modalDelete', that.onModalOpen.bind(null, that) );
    $(document).on('opening', '#modalDelete', function () {
		that.onModalOpen(that);
    } );
    // Начало закрытия
    $(document).on('closing', '#modalDelete', that.onModalClosing.bind(null, that) );
    // исчезло
    $(document).on('closed', '#modalDelete', that.onModalClosed.bind(null, that) );

    //обработчики на кнопки
    let buttons = document.getElementById('modalDeleteContent').querySelectorAll('a');
    let dell = buttons[1];
    let ok = buttons[2];

    dell.addEventListener('click', that.modalDeleteButton.bind(event,that) );

};


DeleteModal.prototype.onModalOpen = function(that)
{
    console.log('Dell Modal is Open');

    let modal = $('#modalDelete');
    let status = document.getElementById('modalDeleteContent').querySelector('#modalDeleteStatus');
    let buttons = document.getElementById('modalDeleteContent').querySelectorAll('a');
    let back = buttons[0];
    let dell = buttons[1];
    let ok = buttons[2];

	let num3d = document.querySelector('#num3d').value;
	let vendor_code = document.querySelector('#vendor_code').value;
	let modelType = document.querySelector('#modelType').value;
	let modelText = '<b>'+ num3d + ' / ' + vendor_code + ' - ' + modelType +'</b>';
	
	let dellData = that.dellObj;
	let titleText, img;
	if ( dellData.fileName )
	{
		if ( dellData.fileType === 'image' )
		{
			titleText = 'Удалить картинку <b>' + dellData.fileName + '?</b>';
			img = document.createElement('img');
			img.src = _URL_ + '/Stock/' + num3d + '/' + dellData.id + '/images/' + dellData.fileName + '';
			img.height = 100;
		} else {
			img = document.createElement('p');
			img.innerHTML = dellData.fileName;
		}
		status.appendChild(img);
	}
	if ( dellData.fileType === 'stl' )
		titleText = 'Удалить STL файлы позиции '+ modelText +'?';
    if ( dellData.fileType === '3dm' )
        titleText = 'Удалить Rhino 3dm файлы позиции '+ modelText +'?';
    if ( dellData.fileType === 'ai' )
		titleText = 'Удалить AI файл позиции '+ modelText +'?'; 
	if ( dellData.dellPosition ) 
	{
		titleText = 'Удалить позицию '+ modelText +'?';
		modal.iziModal('setIcon', 'glyphicon glyphicon-floppy-remove');
	}

    modal.iziModal('setTitle', titleText);
    modal.iziModal('setSubtitle', 'Удаление происходит безвозвратно!');

    back.classList.remove('hidden');
    dell.classList.remove('hidden');

};
DeleteModal.prototype.onModalClosing = function(that, event)
{
    console.log('Modal is closing');

};
DeleteModal.prototype.onModalClosed = function(that, event)
{
    console.log('Modal is closed');

    let modal = $('#modalDelete');
    let buttons = document.getElementById('modalDeleteContent').querySelectorAll('a');
    let status = document.getElementById('modalDeleteContent').querySelector('#modalDeleteStatus');
    
	let back = buttons[0];
	let dell = buttons[1];
	let ok = buttons[2];

    status.innerHTML = '';
    back.classList.add('hidden');
    dell.classList.add('hidden');
    ok.classList.add('hidden');

    modal.iziModal('setTitle', '');
    modal.iziModal('setSubtitle', '');
    modal.iziModal('setHeaderColor', '#ff3f36');
    modal.iziModal('setIcon', 'glyphicon glyphicon-trash');
    
};

DeleteModal.prototype.modalDataInit = function(dellObj) 
{
	this.dellObj = dellObj;
};
DeleteModal.prototype.modalDeleteButton = function(that, event) {
	debug(that.dellObj,'dellObj');
	let dellData = that.dellObj;
	let imgElement;
	if ( dellData.element )
	{
		imgElement = dellData.element;
		delete dellData.element;
	}
		
	let modal = $('#modalDelete');
	let buttons = document.getElementById('modalDeleteContent').querySelectorAll('a');
	let status = document.getElementById('modalDeleteContent').querySelector('#modalDeleteStatus');

	let back = buttons[0];
	let dell = buttons[1];
	let ok = buttons[2];

	$.ajax({
		type: 'POST',
		url: '/model-edit/delete',
		data: dellData,
		dataType:"json",
		success:function(response) {
            debug(response,'response');

			let fileName = response.fileName;
			let text = response.text;

			if ( response.dell ) { // удалили модель целиком
				fileName = response.dell; // здесь строка с именем модели
				text = 'Модель ';
			}
			
			modal.iziModal('setTitle', text + fileName +' удалена!');
			modal.iziModal('setSubtitle', '');
			modal.iziModal('setHeaderColor', '#2aabd2');
			modal.iziModal('setIcon', 'glyphicon glyphicon-ok');
			
			if (imgElement) imgElement.remove();

			if ( response.dell ) {
				ok.onclick = function() {
            		redirect( _URL_ + '/main');
            	};
			} else {
				ok.onclick = function() {
            		document.location.reload(true);	
            	};
			}
            
			ok.classList.remove('hidden');
			back.classList.add('hidden');
			dell.classList.add('hidden');
		},
		error:function (error) {
            debug("Ошибка при удалении!");

			debug(error);
        }
	});
};

let dellModal = new DeleteModal();
