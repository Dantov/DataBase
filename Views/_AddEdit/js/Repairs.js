"use strict";

function Repairs()
{
    this.repairPricesModal = document.querySelector('#repairPricesModal');
    this.repairsBlock = document.querySelector('#repairsBlock');

    if (!this.repairsBlock)
        return;

    /**
     * Здесть ДОМ Ел табл прайсов в текущем ремонте
     * tbody
     */
    this.currentPricesTable = null;

    this.repairTypes = {
        repairs3d: {
            which: 0,
            name: 'repairs[3d]',
            topText: 'Ремонт 3Д модели №',
            modalTitleText: 'Выбрать оценку ремонта 3Д модели',
            panelColor: '#5fd7f5',
            panelIcon: 'fa-draw-polygon',
            repairsCount: 0,
            protoPriceName: 'gs_proto3DRow',
        },
        repairsJew: {
            which: 1,
            name: 'repairs[jew]',
            topText: 'Ремонт Мастер модели №',
            modalTitleText: 'Выбрать оценку ремонта Мастер Модели',
            panelColor: '#c1b467',
            panelIcon: 'fa-screwdriver',
            repairsCount: 0,
            protoPriceName: 'gs_protoMMRow',
        },
        repairsProd: {
            which: 2,
            name: 'repairs[prod]',
            topText: 'Ремонт модели на производстве №',
            modalTitleText: 'Выбрать оценку ремонта на производстве',
            panelColor: '#c2b497',
            panelIcon: 'fa-hammer',
            repairsCount: 0,
            protoPriceName: 'gs_protoMMRow',
        },
    };

    this.init();
}

Repairs.prototype.init = function()
{
    let that = this;

    // Накинем обработчики на добавление ремонтов
    $.each(this.repairsBlock.querySelectorAll('.addRepair'), function(i, button) {
        button.addEventListener('click', that.addRepair.bind(event, button, that) ,false);
    });

    // Накинем обработчики на удаление ремонтов
    $.each(this.repairsBlock.querySelectorAll('.removeRepair'), function(i, button) {
        button.addEventListener('click', that.removeRepair.bind(event, button, that) ,false);

        let repType = button.getAttribute('data-repType');
        if ( that.repairTypes[repType] )
            that.repairTypes[repType].repairsCount++;
    });
    
    // Накинем обработчики на удаление Оценок
    $.each(this.repairsBlock.querySelectorAll('.repDellGrade'), function(i, button) {
        button.addEventListener('click', that.removeGrade.bind(event, button, that) ,false);
    });

    // Накинем обработчики на panel-heading ремонтов. Свернуть / развернуть текущие ремонты
    $.each(this.repairsBlock.querySelectorAll('.panel-heading'), function(i, panelHeading) {
        that.collapseRepair(panelHeading);
    });


    this.repairPricesModal.querySelector('.selectRepairPrice').addEventListener('change', function () {
        that.addRepairPrice(this);
    },false);
    this.repairPricesModal.querySelector('.selectMMRepairPrice').addEventListener('change', function () {
        that.addRepairPrice(this);
    },false);

    $('#repairPricesModal').on('show.bs.modal', function (e) {
        debug(e);
        that.currentPricesTable = e.relatedTarget.parentElement.parentElement.nextElementSibling.children[1];
        debug(that.currentPricesTable);

        let currRep = that.currentPricesTable.getAttribute('class');

        that.repairPricesModal.querySelector('.titleText').innerHTML = that.repairTypes[currRep].modalTitleText;

        that.repairPricesModal.querySelector('.selectRepairPrice').classList.remove('hidden');
        that.repairPricesModal.querySelector('.selectMMRepairPrice').classList.add('hidden');

        if ( that.repairTypes[currRep].which > 0 )
        {
            that.repairPricesModal.querySelector('.selectRepairPrice').classList.add('hidden');
            that.repairPricesModal.querySelector('.selectMMRepairPrice').classList.remove('hidden');
        }
    });
    $('#repairPricesModal').on('hidden.bs.modal', function (e) {
        debug(e);
        that.currentPricesTable = null;
    });

    debug('Repairs init ok!');
};


Repairs.prototype.addRepair = function(button, self, event)
{
    event.preventDefault();
    event.stopPropagation();

    let btnType = button.getAttribute('data-repair');
    let newRepairWindow = self.repairsBlock.parentElement.querySelector('#protoRepair').cloneNode(true);
    let today = new Date();
    let timeMilisec = +today;
    let repairData = self.repairTypes[btnType];
    let targetRepBlock = self.repairsBlock.querySelector('#' + btnType);

    /** Достаанем из базы имена мастеров **/
    $.ajax({
        type: 'GET',
        url: '/add-edit/masterLI/',
        data: {
            masterLI: repairData.which,
        },
        dataType:"json",
        success:function(response) {
            debug(response,'response');
            if ( response.error )
            {
                AR.setDefaultMessage( 'error', 'subtitle', "Ошибка. Имена мастеров не найдены." );
                AR.error( response.error.message, response.error.code, response.error );
                return;
            }
            let masterUL = newRepairWindow.querySelector('.toWhomList');
            masterUL.innerHTML = response.li;
        },
        error:function (error) {
            AR.serverError( error.status, error.responseText );
            debug(error);
        }
    });

    /**
     * SET ATTR
     */
    newRepairWindow.id = '';
    newRepairWindow.classList.remove('hidden');
    newRepairWindow.classList.add(btnType);
    newRepairWindow.setAttribute('id','allRepairs_' + timeMilisec);
    newRepairWindow.querySelector('.panel-heading').style.backgroundColor = repairData.panelColor;
    newRepairWindow.querySelector('.panel-heading').children[0].classList.add(repairData.panelIcon); // i
    newRepairWindow.querySelector('.repairs_name').innerHTML = repairData.topText;
    newRepairWindow.querySelector('.repairs_number').innerHTML = ++repairData.repairsCount;
    newRepairWindow.querySelector('.repairs_date').innerHTML = formatDate(today);
    newRepairWindow.querySelector('.removeRepair').setAttribute('data-repType',btnType);
    newRepairWindow.querySelector('.panel-collapse').setAttribute('id','repairCollapse_' + timeMilisec);
    newRepairWindow.querySelector('.panel-collapse').setAttribute('aria-labelledby','allRepairs_' + timeMilisec);

    /**
     * NAMES
     */
    newRepairWindow.querySelector('.sender').setAttribute('name',repairData.name + '[sender][]');
    newRepairWindow.querySelector('.toWhom').setAttribute('name',repairData.name + '[toWhom][]');
    newRepairWindow.querySelector('.repairs_descr_need').setAttribute('name',repairData.name + '[descrNeed][]');
    newRepairWindow.querySelector('.repairs_id').setAttribute('name',repairData.name + '[id][]');
    newRepairWindow.querySelector('.repairs_num').setAttribute('name',repairData.name + '[rep_num][]');
    newRepairWindow.querySelector('.repairs_num').value = repairData.repairsCount;
    newRepairWindow.querySelector('.repairs_which').setAttribute('name',repairData.name + '[which][]');
    newRepairWindow.querySelector('.repairs_which').value = repairData.which;
    newRepairWindow.querySelector('.repairStatus').setAttribute('name',repairData.name + '[status][]');
    newRepairWindow.querySelector('.statusDate').setAttribute('name',repairData.name + '[status_date][]');
    newRepairWindow.querySelector('.date').setAttribute('name',repairData.name + '[date][]');
    newRepairWindow.querySelector('.posID').setAttribute('name',repairData.name + '[pos_id][]');

    /**
     * INSERT
     */
    let appendedRepair = targetRepBlock.appendChild(newRepairWindow);

    /**
     * LISTENERS
     */
    appendedRepair.querySelector('.removeRepair').addEventListener('click', function (event) {
        self.removeRepair(this, self, event);
    }, false);
    self.collapseRepair(appendedRepair.querySelector('.panel-heading'));
};

/**
 * Удаление ремонтов целиком
 * @param button
 * @param event
 * @param self
 */
Repairs.prototype.removeRepair = function(button, self, event)
{
    event.stopPropagation();
    event.preventDefault();

    let toDell = button.parentElement.parentElement;
    let repType = button.getAttribute('data-repType');
    if ( self.repairTypes[repType] )
        self.repairTypes[repType].repairsCount--;

    if ( toDell.classList.contains('new') )
    {
        toDell.remove();
    } else {
        toDell.classList.add('hidden');
        toDell.querySelector('.repairs_descr_done').innerHTML = '-1';
    }
};

/**
 * Свернуть / развернуть ремонт
 */
Repairs.prototype.collapseRepair = function(ph)
{
    // при клике на панель, раскрыли панель и подсветили её
    ph.addEventListener('click',function (e) {
        let click = e.target;
        if ( click.classList.contains('removeRepair') ) return;
        $(this.nextElementSibling).collapse('toggle');
        let panel = this.parentElement;

        //panel.classList.toggle('panel-default');
        panel.classList.toggle('panel-primary');
        panel.classList.toggle('panel-warning');
    });
    // Подсветили строку модели по mouse over
    ph.addEventListener('mouseover',function (e) {
        let click = e.target;
        if ( click.classList.contains('removeRepair') ) return;

        let panel = this.parentElement;
        if ( !panel.classList.contains('panel-primary') )
        {
            panel.classList.toggle('panel-default');
            panel.classList.toggle('panel-warning');
        }
    });

    // Убрали подсветку по mouse out
    ph.addEventListener('mouseout',function (e) {
        let click = e.target;
        if ( click.classList.contains('removeRepair') ) return;

        let panel = this.parentElement;
        if ( !panel.classList.contains('panel-primary') )
        {
            panel.classList.toggle('panel-default');
            panel.classList.toggle('panel-warning');
        }
    });
};


/**
 * REPAIR PRICES
 */
Repairs.prototype.addRepairPrice = function(select)
{
    let that = this;
    let repairType = this.currentPricesTable.getAttribute('class');
    let repairData = this.repairTypes[repairType];


    let gsID = +select.value;

    // проверим если есть такая оценка
    let hasID = false;
    $.each(this.currentPricesTable.querySelectorAll('tr'), function(i, tr){
        if ( +tr.getAttribute('data-gradeID') === gsID ) {
            $('#repairPricesModal').modal('hide');
            return hasID = true;
        }
    });
    if ( hasID ) return;

    let option = select.options[select.options.selectedIndex];
    let workName = option.getAttribute('data-workName');
    let price = option.getAttribute('data-points') * 100;
    let gradeType = option.getAttribute('data-gradeType');
    let description = option.getAttribute('title');

    $('#repairPricesModal').modal('hide');

    // ID названия оценки из таблицы Grading_system
    let inputID = document.createElement('input');
        inputID.setAttribute('hidden', '');
        inputID.setAttribute('value', gsID);
        inputID.setAttribute('name', repairData.name + '[prices][gs_id][]');
        inputID.classList.add('hidden');
        inputID.value = gsID;

    // ID оценки из таблицы model_prices (для новых пустое)
    // grade_type оценки из таблицы grading_system (  )
    let inputIDmp = document.createElement('input');
        inputIDmp.setAttribute('hidden', '');
        inputIDmp.setAttribute('value', gradeType);
        inputIDmp.setAttribute('name', repairData.name + '[prices][is3d_grade][]');
        inputIDmp.classList.add('hidden');
        inputIDmp.value = gradeType;

    // ID оценки из таблицы model_prices (для новых пустое)

    let repairID = this.currentPricesTable.parentElement.parentElement.querySelector('.repairs_id').value;
    let inputRepID = document.createElement('input');
        inputRepID.setAttribute('hidden', '');
        inputRepID.setAttribute('value', repairID);
        inputRepID.setAttribute('name', repairData.name + '[prices][repair_id][]');
        inputRepID.classList.add('hidden');
        inputRepID.value = repairID;

    // Сама стоимость
    let inputPoints = document.createElement('input');
    if ( +price.toFixed() !== 0 )
    {
        inputPoints.setAttribute('hidden', '');
        inputPoints.classList.add('hidden');
    }
    inputPoints.setAttribute('value', +price.toFixed());
    inputPoints.value = +price.toFixed();
    inputPoints.setAttribute('name', repairData.name + '[prices][value][]');
    inputPoints.classList.add('form-control');

    // Для Тултипа
    let div = document.createElement('div');
        div.classList.add('cursorPointer', 'lightUpGSRow');
        div.setAttribute('data-toggle','tooltip');
        div.setAttribute('data-placement','bottom');
        div.setAttribute('title',description);
        div.innerHTML = workName;

    let newRow = document.querySelector('.' + repairData.protoPriceName).cloneNode(true);
        newRow.setAttribute('data-gradeID',gsID);
        newRow.removeAttribute('class');
    if ( +price.toFixed() !== 0 )
        newRow.children[2].innerHTML = +price.toFixed();

    let totalRow = this.currentPricesTable.querySelector('.t-total');
    let insertedRow = this.currentPricesTable.insertBefore(newRow, totalRow);
        insertedRow.children[1].appendChild(div);
        insertedRow.children[2].appendChild(inputPoints);
        insertedRow.children[3].appendChild(inputIDmp);
        insertedRow.children[3].appendChild(inputID);
        insertedRow.children[3].appendChild(inputRepID);

    let dellButton = insertedRow.children[4].querySelector('.ma3DgsDell');
        dellButton.addEventListener('click',function () {
            that.removeGrade(this,that);
        },false);

    let priceValue = +price.toFixed();
    let overallValue = +totalRow.children[2].innerHTML;

    totalRow.children[2].innerHTML = (overallValue + priceValue) + '';

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
};

/**
 * Удаление прайсов в ремонтпх
 * @param self
 * @param button
 * @param event
 */
Repairs.prototype.removeGrade = function(button, self, event)
{
    let tBody = button.parentElement.parentElement.parentElement;
    let tr = button.parentElement.parentElement;
    let tTotal = tBody.querySelector('.t-total');
    let priceValue = +tr.children[2].firstElementChild.value;
    let overallValue = +tTotal.children[2].innerHTML;
    let id = +button.parentElement.previousElementSibling.children[0].value;

    let num = overallValue - priceValue;
    tTotal.children[2].innerHTML = num < 0 ? 0 : num + '';

    tr.remove();

    if ( !id ) return;
    let name = self.repairTypes[tBody.getAttribute('class')].name;
    let input = document.createElement('input');
        input.setAttribute('hidden', '');
        input.setAttribute('value', id + '');
        input.setAttribute('name', name + '[prices][toDell][]');
        input.classList.add('hidden');

    tTotal.firstElementChild.appendChild(input);
};

let repairs = new Repairs();