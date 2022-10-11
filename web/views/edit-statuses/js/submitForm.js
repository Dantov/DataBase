"use strict";
function validateForm() {
	return true;
}
function submitForm() {
    if ( !validateForm() ) return null;

    let editform = document.getElementById('editform');

    let addedit = '/edit-statuses/formdata';
    let formData = new FormData(editform);
    formData.append('userName',userName);
	formData.append('tabID',tabName);
	
	let modal = $('#modalResult');
	let modalButtonsBlock = document.getElementById('modalResult').querySelector('.modalButtonsBlock');
	let status = document.querySelector('#modalResultStatus');
	let back = modalButtonsBlock.querySelector('.modalProgressBack');
	let edit = modalButtonsBlock.querySelector('.modalResultEdit');
	let show = modalButtonsBlock.querySelector('.modalResultShow');

	$('#modalResult').iziModal('open');
	let xhr;
	
	xhr = $.ajax({
		url: addedit,
		type: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		beforeSend: function() {
			debug(xhr);

			modal.iziModal('setTitle', 'Идёт отправление данных на сервер.');
			modal.iziModal('setHeaderColor', '#858172');
		},
		success:function(resp) {
			resp = JSON.parse(resp);
			debug(resp);
			if (resp.debug)
			{
                debugModal(resp.debug);
			}

			if ( resp.error )
            {
                AR.setDefaultMessage( 'error', 'subtitle', "Ошибка при сохранении." );
                AR.error( resp.error.message, resp.error.code, resp.error );
                
                edit.classList.remove('hidden');
                return;
            }

			if ( +resp.done === 1 ) {
				modal.iziModal('setIcon', 'glyphicon glyphicon-floppy-saved');
				modal.iziModal('setHeaderColor', '#edaa16');
				modal.iziModal('setTitle', 'Статусы внесены успешно!');
			} else {
				modal.iziModal('setIcon', 'glyphicon glyphicon-floppy-remove');
				modal.iziModal('setHeaderColor', '#7f6f13');
				modal.iziModal('setTitle', 'Ошибка при сохранении статусов!');
			}

			back.href = '/main/';
			
			back.classList.remove('hidden');
		},
		error: function(error) { // Данные не отправлены
			AR.serverError( error.status, error.responseText );
		
			//modal.iziModal('setTitle', 'Ошибка отправки! Попробуйте снова.');
			//modal.iziModal('setHeaderColor', '#95ffb1');

			edit.onclick = function() {
				document.location.reload(true);
			};
			edit.innerHTL = 'Назад';
			edit.classList.remove('hidden');

			debug(error);
		}
	});
}