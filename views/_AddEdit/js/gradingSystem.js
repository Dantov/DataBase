"use strict";

function GradingSystem()
{
    this.modal = document.querySelector('#grade3DModal');

    this.table_3Dmodeller = document.querySelector('#pricesData .modeller3D');
    this.table_modellerJewPrices = document.querySelector('#pricesData .modellerJewPrices');

    this.init();
    this.deleteGS3DRow();
    this.deleteJewPriceRow();
    this.payJewPriceBtn();
}

GradingSystem.prototype.init = function()
{
    let that = this;

    if ( this.modal )
    {
        let select3DGrade = this.modal.querySelector('.add3DGrade');
        select3DGrade.addEventListener('change', function() {
            that.selectGrade3DChange(this);
        });
    }
    if ( this.table_modellerJewPrices )
    {
        let addButton = document.querySelector('#pricesData .addModellerJewPrice');
        if (addButton)
        {
            addButton.addEventListener('click', function() {
                that.addModellerJewPrice();
                //this.classList.add('hidden'); // не будем скрывать кнопку + (добавляем несколько стоимостей для доработки)
            });
        }
    }

    $('#grade3DModal').on('show.bs.modal', function (e) {
        //debug(e);
    });
    $('#grade3DModal').on('hidden.bs.modal', function (e) {
        //let button = e.relatedTarget;
    });

    debug('GradingSystem3D init ok!');
};

/**
 * Добавление строки стоимости в Доработка модели
 */
GradingSystem.prototype.addModellerJewPrice = function()
{
	if ( !this.table_modellerJewPrices ) return;
    let row_number = this.table_modellerJewPrices.querySelectorAll("tr").length;

    let input_name = document.createElement('input');
        input_name.setAttribute('name', 'modellerJewPrice[cost_name][]');
        input_name.setAttribute('class', 'form-control');
        input_name.setAttribute('value', "Доработка модели");
        input_name.setAttribute('type', 'string');

    let input_value = document.createElement('input');
        input_value.setAttribute('name', 'modellerJewPrice[value][]');
        input_value.setAttribute('class', 'form-control');
        input_value.setAttribute('value', '0');
        input_value.setAttribute('type', 'number');
        input_value.value = '0';

    let input_id = document.createElement('input');
        input_id.setAttribute('name', 'modellerJewPrice[id][]');
        input_id.setAttribute('class', 'hidden');
        input_id.setAttribute('value', "");
        input_id.setAttribute('hidden', '');

    let newRow = document.querySelector('.gs_protoModJewRow').cloneNode(true);
        newRow.removeAttribute('class');
        newRow.children[0].innerHTML = row_number;
        newRow.children[1].appendChild(input_name);
        newRow.children[2].appendChild(input_value);
        newRow.children[2].appendChild(input_id);

    let last = this.table_modellerJewPrices.querySelector('.t-total');

    this.table_modellerJewPrices.insertBefore(newRow,last);

};
/**
 * Создать инпут на удаление
 */
GradingSystem.prototype.deleteJewPriceRow = function()
{
    let that = this;
    if ( this.table_modellerJewPrices )
    {
        let dellButtons = this.table_modellerJewPrices.querySelectorAll('.maJewPriceDell');
        $.each(dellButtons, function(i, button) {

            button.addEventListener('click', function() {
                let id = this.parentElement.previousElementSibling.previousElementSibling.children[1].value;
                let priceValue = this.parentElement.previousElementSibling.previousElementSibling.children[0].value;

                if ( !id ) return;
                let input = document.createElement('input');
                    input.setAttribute('hidden', '');
                    input.setAttribute('value', id);
                    input.setAttribute('name', 'modellerJewPrice[toDell][]');
                    input.classList.add('hidden');

                let tTotal = that.table_modellerJewPrices.querySelector('.t-total');
                    tTotal.firstElementChild.appendChild(input);
                    // изменение общего прайса
                    tTotal.children[2].innerHTML -= priceValue;

            }, false);
        });
    }
};
/**
 * Накидывает обработчик оплаты на кнопку payJewPriceBtn
 */
GradingSystem.prototype.payJewPriceBtn = function()
{
	if ( !this.table_modellerJewPrices ) return;
    let payJewButtons = this.table_modellerJewPrices.querySelectorAll('.payJewPriceBtn');
    if ( !payJewButtons.length ) return;

    $.each(payJewButtons, function(i, button) {
        button.addEventListener('click', function () {
            let priceID = this.parentElement.parentElement.querySelector('.jewPriceID').value;
            let priceName = this.parentElement.parentElement.querySelector('.jewPriceName').value;
            let priceValue = this.parentElement.parentElement.querySelector('.jewPriceValue').value;

            if ( confirm("Зачислить и отметить оплату этой стоимости: " + priceName + " - " + priceValue +  " ?") )
            {
                $.ajax({
                    url: "/add-edit/countCurrentJewPrice",
                    type: "POST",
                    data: {
                        countCurrentJewPrice: 1,
                        priceID: priceID,
                    },
                    //dataType:"json",
                    success:function(result) {

                        //debug(result);
                        result = JSON.parse(result);

                        debug(result);
                        if ( result.debug )
                            debugModal( result.debug );

                        if ( result.success )
                        {
                            alert(result.success.message);
                            reload();
                        }
                        if ( result.error )
                        {
                            let err = result.error;
                            alert("Code: " + err.code + '\n' + err.message);
                        }

                    },
                    error:function(error) {
                        debug(error,"count price error");
                    }
                });
            }
        }, false);
    });
};



GradingSystem.prototype.selectGrade3DChange = function(select)
{

    let that = this;
    let gsID = +select.value;

    // проверим если есть такая оценка
    let hasID = false;
    $.each(this.table_3Dmodeller.querySelectorAll('tr'), function(i, tr){
        let trID = +tr.getAttribute('data-gradeID');
        if ( trID === gsID ) {
            hasID = true;
            $('#grade3DModal').modal('hide');
            return;
        }
    });
    if ( hasID ) return;

    let option = select.options[select.options.selectedIndex];
    let workName = option.getAttribute('data-workName');
    let price = option.getAttribute('data-points') * 100;
    let description = option.getAttribute('title');

    $('#grade3DModal').modal('hide');

    // ID оценки из таблицы Grading_system
    let inputID = document.createElement('input');
    inputID.setAttribute('hidden', '');
    inputID.setAttribute('value', gsID);
    inputID.setAttribute('name', 'ma3Dgs[gs3Dids][]');
    inputID.classList.add('hidden');
    inputID.value = gsID;

    // ID оценки из таблицы model_prices
    let inputIDmp = document.createElement('input');
    inputIDmp.setAttribute('hidden', '');
    inputIDmp.setAttribute('value', '');
    inputIDmp.setAttribute('name', 'ma3Dgs[mp3DIds][]');
    inputIDmp.classList.add('hidden');
    inputIDmp.value = '';

    // Сама оценка
    let inputPoints = document.createElement('input');
    if ( +price.toFixed() !== 0 )
    {
        inputPoints.setAttribute('hidden', '');
        inputPoints.classList.add('hidden');
    }
    inputPoints.setAttribute('value', +price.toFixed());
    inputPoints.value = +price.toFixed();
    inputPoints.setAttribute('name', 'ma3Dgs[gs3Dpoints][]');
    inputPoints.classList.add('form-control');

    // Для Тултипа
    let div = document.createElement('div');
    div.classList.add('cursorPointer', 'lightUpGSRow');
    div.setAttribute('data-toggle','tooltip');
    div.setAttribute('data-placement','bottom');
    div.setAttribute('title',description);
    div.innerHTML = workName;

    let newRow = document.querySelector('.gs_proto3DRow').cloneNode(true);
    newRow.setAttribute('data-gradeID',gsID);
    newRow.removeAttribute('class');
    if ( +price.toFixed() !== 0 )
        newRow.children[2].innerHTML = +price.toFixed();

    let totalRow = that.table_3Dmodeller.querySelector('.t-total');
    let insertedRow = that.table_3Dmodeller.insertBefore(newRow, totalRow);
    insertedRow.children[1].appendChild(div);
    insertedRow.children[2].appendChild(inputPoints);
    insertedRow.children[3].appendChild(inputIDmp);
    insertedRow.children[3].appendChild(inputID);

    let dellButton = insertedRow.children[4].querySelector('.ma3DgsDell');
        dellButton.addEventListener('click', function() {

            let totalRow = that.table_3Dmodeller.querySelector('.t-total');
            let overallValue = +totalRow.children[2].innerHTML;
            let priceValue = insertedRow.children[2].firstElementChild.value;

            insertedRow.remove();

            totalRow.children[2].innerHTML = (overallValue - priceValue) + '';
    },false);
    //this.setEventListener(dellButton);

    let priceValue = +price.toFixed();
    let overallValue = +totalRow.children[2].innerHTML;

    totalRow.children[2].innerHTML = (overallValue + priceValue) + '';

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

};

GradingSystem.prototype.deleteGS3DRow = function()
{
    let that = this;
    if ( this.table_3Dmodeller )
    {
        let dellButtons = this.table_3Dmodeller.querySelectorAll('.ma3DgsDell');
        $.each(dellButtons, function(i, button) {
            that.setEventListener(button);
        });
    }
};

GradingSystem.prototype.setEventListener = function( button )
{
    let that = this;
    button.addEventListener('click', function(event) {
        let id = button.parentElement.previousElementSibling.children[0].value;

        let priceValue = +button.parentElement.previousElementSibling.previousElementSibling.innerHTML;

        if ( !id ) return;
        let input = document.createElement('input');
        input.setAttribute('hidden', '');
        input.setAttribute('value', id);
        input.setAttribute('name', 'ma3Dgs[toDell][]');
        input.classList.add('hidden');

        let tTotal = that.table_3Dmodeller.querySelector('.t-total');
            tTotal.children[0].appendChild(input);

        let overallValue = +tTotal.children[2].innerHTML;
        tTotal.children[2].innerHTML = (overallValue - priceValue) + '';

    }, false);
};



let gs = new GradingSystem();
