"use strict";

let container = document.querySelector('.content');

function addRow(self)
{
	let tbody = self.parentElement.parentElement.parentElement;
	let trPlus = self.parentElement.parentElement;
	let len = tbody.querySelectorAll('.collsRow').length;
	let tab = tbody.getAttribute('data-tab');
	
	let newRow = document.querySelector('.protoRow').cloneNode(true);
		newRow.classList.remove('protoRow');
		newRow.classList.add('collsRow');
		newRow.children[1].children[0].setAttribute('data-tab',tab);
		newRow.children[0].innerHTML = ++len;
		
	let elem = tbody.insertBefore(newRow, trPlus);
		elem.children[1].children[0].addEventListener('change', changeInpt, false);
}

applyEvents();
function applyEvents() {
	let inputs = container.querySelectorAll('.collsRow input');
	let a = container.querySelectorAll('.collsRow a');
	
	for( let i = 0; i < inputs.length; i++ ) {
		inputs[i].addEventListener('change', changeInpt, false);
		a[i].addEventListener('click', dellRow, false);
	}
}

function changeInpt() {
	
	let tab = this.getAttribute('data-tab');
	let id = this.getAttribute('data-id');
	let val = this.value;
	let that = this;
	
	console.log('tab=',tab,' value=',val,' id=',id);
	
	let obj = {
		tab : tab,
		id : id,
		val : val
	};
	
	$.ajax({
		url: "/nomenclature/change",
		type: 'POST',
		data: obj,
		dataType:"json",
		success:function(data) {
			/*
			debug(data);

			if ( data.debug )
				debugModal(data.debug);
			*/
			let target = that.parentElement.previousElementSibling;
			if (data.status === 1) {	
				let span = document.createElement('span');
					span.setAttribute('class','glyphicon glyphicon-ok pull-right');
					span.setAttribute('title','Сохранено.');
				let elem = target.appendChild(span);
				setTimeout(function(){
					elem.remove();
				}, 2000);
				if ( data.add === 1 ) {
					let span = document.createElement('span');
						span.setAttribute('class','glyphicon glyphicon-trash');
						span.setAttribute('aria-hidden','true');
					let a = document.createElement('a');
						a.setAttribute('class','btn btn-sm btn-default');
						a.setAttribute('type','button');
						a.setAttribute('role','button');
						a.appendChild(span);
						a.addEventListener('click', dellRow, false);
					that.setAttribute('data-id',data.id);
					target = that.parentElement.nextElementSibling.nextElementSibling;
					that.parentElement.nextElementSibling.innerHTML = data.date;
					target.appendChild(a);
				}
			} else if (data.status === -1){
				console.log('data.coll=',data.coll);
				let objNames = {
					collections : 'Коллекция - "'+val+'" уже есть!',
					gems_names : 'Сырьё - "'+val+'" уже есть!',
					gems_cut : 'Огранка - "'+val+'" уже есть!',
					gems_color : 'Цвет - "'+val+'" уже есть!',
					gems_sizes : 'Размер - "'+val+'" уже есть!',
					author : 'Автор - "'+val+'" уже есть!',
					modeller3d : 'Модельер - "'+val+'" уже есть!',
					model_type : 'Тип модели - "'+val+'" уже есть!',
					vc_names : 'Доп. Артикул - "'+val+'" уже есть!'
				};
				let strAlert = '';
				for (let key in objNames) {
					if ( tab === key ) {
						strAlert = objNames[key];
						break;
					}
				}
				alert(strAlert);
			} else if (data.status === 0){
				alert('ошибка при внесении данных. Попробуйте снова.' );
			}
		}
	});
}

function dellRow()
{
	//let table = this.parentElement.parentElement.getAtribute('data-tab');
	let input = this.parentElement.previousElementSibling.previousElementSibling.children[0];
	let tab = input.getAttribute('data-tab');
	let id = input.getAttribute('data-id');
	let val = input.value;
	let that = this;
	let str = '';

	console.log('tab=',tab,' val=',val,' id=',id);
	let objNames = {
		collections : 'Коллекция "' + val + '" удалена!;Удалить коллекцию - "' + val + '" ? Все модели принадлежащие к ней будут помечены тирэ.',
		gems_names : 'Сырьё "' +val+ '" удален!;Удалить сырьё - "' + val + '" ?',
		gems_cut : 'Огранка "' +val+ '" удалена!;Удалить огранку - "' + val + '" ?',
		gems_color : 'Цвет "' +val+ '" удален!;Удалить цвет - "' + val + '" ?',
		gems_sizes : 'Размер "' +val+ '" удален!;Удалить размер - "' + val + '" ?',
		author : 'Автор "' +val+ '" удален!;Удалить автора - "' + val + '" ?',
		modeller3d : 'Модельер "' +val+ '" удален!;Удалить модельера - "' + val + '" ?',
		model_type : 'Тип модели "' +val+ '" удален!;Удалить тип модели - "' + val + '" ?',
		vc_names : 'Доп. Артикул "' +val+ '" удален!;Удалить доп. Артикул - "' + val + '" ?'
	};

	let strAlert = '';
	for (let key in objNames) {
		if ( tab === key ) {
			strAlert = objNames[key];
			break;
		}
	}

	let mass = strAlert.split(';');
	let conf_str = mass[1];
	strAlert = mass[0];

	let objRequest = {
		tab : tab,
		id : id,
		val : val,
		dell : 1
	};
	
	let conf = confirm("Удалить запись " + val + "?");
	if ( conf ) {
		$.ajax({
			url: "/nomenclature/dellrow", //путь к скрипту, который обрабатывает задачу
			type: 'POST',
			data: objRequest,
			dataType:"json",
			success:function(data) {  //функция обратного вызова, выполняется в случае успешной отработки скрипта

				let target = that.parentElement.parentElement;
				if (data.dell === 1) {

					if ( data.count ) str = data.count + ' моделей остались без коллекции';
					target.remove();

					alert( "Запись " + val + " удалена!" );

				}
			}
		})
	}

}