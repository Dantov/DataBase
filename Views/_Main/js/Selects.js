"use strict";
function Selects() {

    this.sgUL = document.querySelector('#selectedGroup').children[1];
    this.lc = document.querySelector('#loadeding_cont');
    this.selectedElements = []; //здесь хранятся выделенные модели
    this.selectionMode = false;

    //
    this.listeners = false;

    let that = this;

    this.sgUL.querySelector('.selectsCheckAll').addEventListener('click', function(event){
        event.preventDefault();
        that.selectAllBoxes();
    },false);
    this.sgUL.querySelector('.selectsUncheckAll').addEventListener('click', function(event){
        event.preventDefault();
        that.unselectAllBoxes();
    },false);
    /*
    this.sgUL.querySelector('.selectsShowModels').addEventListener('click', function(event){
        event.preventDefault();
        that.showSelectedModels();
    },false);
    */
    if ( this.sgUL.querySelector('.editStatusesSelectedModels') )
    {
        this.sgUL.querySelector('.editStatusesSelectedModels').addEventListener('click', function(event){
            event.preventDefault();
            if ( that.selectedElements.length )
            {
                redirect('/edit-statuses/');
            }

        },false);
    }


    this.checkSelectionMode();
}

Selects.prototype.checkSelectionMode = function() {
    this.selectionMode = document.querySelector('#selectMode').classList.contains('btnDefActive');

    let that = this;
    if ( !this.listeners )
    {
        this.lc.addEventListener('click', function(event) {
            let click = event.target;
            if ( !click.hasAttribute('checkBoxId') ) return;

            that.selectBox(click);
        }, false);
        this.listeners = true;
    }

    if ( this.selectionMode ) this.checkSelectedModels();
};

Selects.prototype.checkSelectedModels = function() {

    let that = this;
    $.ajax({
        url: "/main/selectionCheck",
        type: "POST",
        data: {
            selections: 1,
            checkSelectedModels: 1
        },
        dataType: "json",
        success: function (data) {
            $.each(data, function (key, elem) {
                that.selectedElements.push(elem);
            });
            // for (let key in data) {
            //     that.selectedElements.push(data[key]);
            // }
            console.log(that.selectedElements);
        }
    })
};

Selects.prototype.toggleSelectionMode = function(self) {
    let activateClass = 1;  // 1 - мы включаем режим выделения. 2 - выключаем

    if ( this.selectionMode ) {
        if ( this.selectedElements.length ) {
            let conf = confirm('Есть выделенные элементы! Если отключить, все выделение сбросится. Продолжить?');
            if ( !conf ) return;
        }
        activateClass = 2;
    }

    let that = this;
    $.ajax({
        url: "/main/toggleSelectionMode",
        type: "POST",
        data: {
            selections: 1,
            toggle: activateClass
        },
        dataType: "json",
        success: function (data) {
            if ( data === 'on' )
            {
                that.selectionMode = true;
                document.querySelector('#selectedGroup').classList.remove('hidden');
                debug('Selection mode ON');
            }
            if ( data === 'off' )
            {
                that.selectionMode = false;
                that.selectedElements = [];
                let li_arr = that.sgUL.querySelectorAll('li');
                for ( let i = 2; i < li_arr.length; i++ ) {
                    li_arr[i].remove();
                }
                document.querySelector('#selectedGroup').classList.add('hidden');
                debug('Selection mode OFF');
            }
            self.classList.toggle('btnDefActive');
            that.toggleBoxes();
            //debug(that.selectedElements,'selectedElements');
        },
        error:function (e) {
            debug(e,'Error in selection Toggle');
        }
    });
};

Selects.prototype.toggleBoxes = function() {

    let selectBoxes = this.lc.querySelectorAll('.selectionCheck');
    let i;

    if ( this.selectionMode ) {
        for ( i = 0; i < selectBoxes.length; i++ ) {
            selectBoxes[i].classList.remove('hidden');
        }
    } else {
        for ( i = 0; i < selectBoxes.length; i++ ) {
            selectBoxes[i].children[0].children[0].className = "glyphicon glyphicon-unchecked";
            selectBoxes[i].children[1].checked = false;
            selectBoxes[i].classList.add('hidden');
        }
    }
};
/**
*  Выбрать все модели на странице
*/
Selects.prototype.selectAllBoxes = function(click) {
    let checkIdBoxes = this.lc.querySelectorAll('input');

    $.each(checkIdBoxes, function(i, checkbox) {
        if ( checkbox.checked ) return;
        checkbox.click();
    });

};
/**
*  Убрать выделения со всех моделей
*/
Selects.prototype.unselectAllBoxes = function(click) {
    let checkIdBoxes = this.lc.querySelectorAll('input');

    $.each(checkIdBoxes, function(i, checkbox){
        if ( !checkbox.checked ) return;
        checkbox.click();
    });

};
Selects.prototype.selectBox = function(click) {

    //console.log(click);
    
    let checked = 1;
    if ( !click.checked ) checked = 2;

    let that = this;
    $.ajax({
        url: "/main/selectBox",
        type: "POST",
        data: {
            selections: 1,
            checkBox: checked,
            modelId: click.getAttribute('modelId'),
            modelName: click.getAttribute('modelName'),
            modelType: click.getAttribute('modelType')
        },
        dataType:"json",
        success:function(obj) {
			
            let models = that.selectedElements;
            if ( obj.checkBox === 1 ) { // добавляем
                models.push(obj);
                // добавляем в меню вверху
                let newLI = document.createElement('li');
                    newLI.setAttribute('data-id',obj.id);
                    newLI.innerHTML = '<a href="../_ModelView/?id=' + obj.id + '" >' + obj.name + '</a>';
                that.sgUL.appendChild(newLI);
				
				let span = click.previousElementSibling.children[0];
				span.classList.remove('glyphicon-unchecked');
				span.classList.add('glyphicon-check');
            }

            if ( obj.checkBox === 2 ) { // удаляем
                let li_arr = that.sgUL.querySelectorAll('li[data-id]');
                // console.log(li_arr);
                // console.log('obj.id=' + obj.id);
                // debug(models,'selectedElements');
                
                let dellIndex = null;
                $.each(models, function(i) {
                    //debug(i);
                    if ( +obj.id === +li_arr[i].getAttribute('data-id') )
                        li_arr[i].remove();

                    if ( +obj.id === +models[i].id )
                        dellIndex = i;
                });
                models.splice(dellIndex, 1);

				let span = click.previousElementSibling.children[0];
				span.classList.remove('glyphicon-check');
				span.classList.add('glyphicon-unchecked');
            }
            console.log(that.selectedElements);
        }
    })
};

Selects.prototype.showSelectedModels = function() {
    if ( this.selectedElements.length )
    {
        $.ajax({
            url: "/main/selected-models-show",
            type: "POST",
            data: {
                selections: 1,
                selectedModels: 'show',
            },
            dataType: "json",
            success: function (data) {
                if ( data === 'ok' )
                {
                   redirect('/main/selected-models'); //document.location.href = '/main/selected-models';
                }
            },
            error:function (e) {
                debug(e.responseText,'Error in showSelectedModels');
            }
        });
    }
};

let selects = new Selects();