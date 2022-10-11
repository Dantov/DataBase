"use strict";

function PaymentManager()
{
    this.modal = document.querySelector("#paymentModal");
    this.modalBody = this.modal.querySelector(".modal-body");
    this.payButton = this.modal.querySelector(".payButton");

    this.priceIDs = [];
    this.excludePricesList = []; // исключенные из оплаты прайсы

    this.panelsToRemove = [];

    this.operationStatus = false;
    this.init();

    $(function () {
      $('[data-toggle="tooltip"]').tooltip();
    });
}

PaymentManager.prototype.init = function(button) 
{
    let pm = this;
    $('#paymentModal').on('show.bs.modal', function (e) {
        //debug(e);
        let button = e.relatedTarget;
        pm.getPricesAllData(button);
    });

    $('#paymentModal').on('hidden.bs.modal', function (e) {
        let f = pm.modalBody.querySelector('.columnFirst');
        let s = pm.modalBody.querySelector('.columnSecond');
        f.innerHTML = "";
        s.innerHTML = "";
        f.classList.remove('col-md-12', 'col-md-6');
        s.classList.remove('col-md-12', 'col-md-6');
        pm.modal.children[0].classList.remove("modal-lg");
        pm.priceIDs = [];
        pm.excludePricesList = [];

        pm.panelsToRemove = [];
    });

    this.payButton.addEventListener('click', function () {
        pm.payPrices(this);
    },false);

    this.addCollapsesEvent();
    this.addCollapsesAll();
    this.collapsesAllPM();

    $("#alertResponse").iziModal({
        timeout: 5000,
        zindex: 1100,
        timeoutProgressbar: true,
        pauseOnHover: true,
        restoreDefaultContent: true,
    });

    $(document).on('closing', '#alertResponse', function (e) {
        if ( pm.operationStatus === false )
        {
            pm.payButton.classList.remove('disabled');
        }
    });
    $(document).on('closed', '#alertResponse', function (e) {
        if ( pm.operationStatus !== true ) return;
        /*
        $.each(pm.panelsToRemove, function (i, panel) {
            panel.remove();
        });
		pm.payButton.classList.remove('disabled');
		pm.payButton.previousElementSibling.classList.remove('hidden');*/
        document.location.reload(true);
    });

    debug('PaymentManager init ok!');
};

/**
 * Накинем обработчик на панель, что бы открывалась не только лишь по клику на ссылке collapse
 */
PaymentManager.prototype.addCollapsesEvent = function()
{
    let allModels = document.getElementById('allModels');
    let panelHeadings = allModels.querySelectorAll('.panel-heading');

    $.each(panelHeadings, function (i, ph) {
        // при клике на панель, раскрыли панель и подсветили её
        ph.addEventListener('click',function (e) {
            let click = e.target;
            if ( click.classList.contains('modelHref') ) return;
            $(this.nextElementSibling).collapse('toggle');
            let panel = this.parentElement;

            //panel.classList.toggle('panel-default');
            panel.classList.toggle('panel-info');
            panel.classList.toggle('panel-primary');
        });
        // Подсветили строку модели по mouse over
        ph.addEventListener('mouseover',function (e) {
            let click = e.target;
            if ( click.classList.contains('modelHref') ) return;

            let panel = this.parentElement;
            if ( !panel.classList.contains('panel-primary') )
            {
                panel.classList.toggle('panel-default');
                panel.classList.toggle('panel-info');
            }
        });
        // Убрали подсветку по mouse out
        ph.addEventListener('mouseout',function (e) {
            let click = e.target;
            if ( click.classList.contains('modelHref') ) return;

            let panel = this.parentElement;
            if ( !panel.classList.contains('panel-primary') )
            {
                panel.classList.toggle('panel-default');
                panel.classList.toggle('panel-info');
            }
        });
    });
};

/**
 * Обработчик на кнопку "Раскрыть все"
 */
PaymentManager.prototype.addCollapsesAll = function()
{
    let openAllModels = document.getElementById('openAllModels');

    /**
     * allPanelsState
     * Переключатель всех панелей
     * @type {boolean}
     */
    let allPanelsState = false; // все закрыто по умолчанию

    openAllModels.addEventListener('click', function () {
        let allModels = document.getElementById('allModels');
        let panelHeadings = allModels.querySelectorAll('.panel-heading');

        $.each(panelHeadings, function (i, ph) {

            // при клике на панель, раскрыли панель и подсветили её
            //this - panel-heading
            if ( !allPanelsState )
                if ( this.nextElementSibling.classList.contains('in') ) return;

            $(this.nextElementSibling).collapse('toggle');

            let panel = this.parentElement;
            panel.classList.toggle('panel-default');
            panel.classList.toggle('panel-primary');
        });

        this.classList.toggle('btn-default');
        this.classList.toggle('btn-danger');
        this.querySelector('.t').innerHTML = this.classList.contains('btn-danger') ? 'Свернуть все' : 'Раскрыть все';

        allPanelsState = !allPanelsState;

    },false);
};

/**
 * Обработчик на кнопку "Раскрыть все" в модале
 */
PaymentManager.prototype.collapsesAllPM = function()
{
    let that = this;
    let openAllModels = this.modal.querySelector('.openAllPanelsPM');

    /**
     * allPanelsState
     * Переключатель всех панелей
     * @type {boolean}
     */
    let allPanelsState = false; // все закрыто по умолчанию

    openAllModels.addEventListener('click', function () {
        let panelHeadings = that.modal.querySelector('.modal-body').querySelectorAll('.panel-heading');

        $.each(panelHeadings, function (i, ph) {

            // при клике на панель, раскрыли панель и подсветили её
            //this - panel-heading
            if ( !allPanelsState )
                if ( this.nextElementSibling.classList.contains('in') ) return;

            $(this.nextElementSibling).collapse('toggle');

            let panel = this.parentElement;
            panel.classList.toggle('panel-default');
            panel.classList.toggle('panel-primary');
        });

        this.classList.toggle('btn-default');
        this.classList.toggle('btn-danger');
        this.querySelector('.tp').innerHTML = this.classList.contains('btn-danger') ? 'Свернуть все' : 'Раскрыть все';

        allPanelsState = !allPanelsState;
    }, false);
};

/**
 * Исключает из оплаты один прайс в модели
 */
PaymentManager.prototype.excludePriceFromModel = function(priceID, row)
{
    this.excludePricesList.push(priceID);
    row.classList.add('hidden');

    let titleNum = this.modal.querySelector('.modal-title').querySelector('b');
    let titleOverallNum;

    let num = parseInt(row.children[0].querySelector('b').innerHTML);

    let pFooter = row.parentElement.parentElement.querySelector('.panel-footer');
    let ov = pFooter.innerHTML.split(' ');

    let numOverall = parseInt( ov[1] );

    numOverall -= num;
    ov[1] = numOverall + 'грн.';
    pFooter.innerHTML = ov.join(' ');

    if ( titleNum )
    {
        titleOverallNum = parseInt(titleNum.innerHTML);
        titleNum.innerHTML = (titleOverallNum - num) + '';
    }
};

/**
 * Исключает из оплаты все прайсы в модели
 */
PaymentManager.prototype.excludePricesFromModel = function( button, currentExcludedPrices, tMValue)
{
    let that = this;

    $.each(currentExcludedPrices, function (i, cPr) {
        that.excludePricesList.push(cPr);
    });
    let titleNum = this.modal.querySelector('.modal-title').querySelector('b');
    let titleOverallNum = parseInt(titleNum.innerHTML);

    titleNum.innerHTML = (titleOverallNum - tMValue) + '';

    button.parentElement.parentElement.classList.add('hidden');
};

/**
 * Идет за прайсами и моделями в базу. Вставляет их в ПМ
 */
PaymentManager.prototype.getPricesAllData = function(button)
{
    let that = this;

    $.ajax({
        url: "/payment-manager/?getPricesAll",
        type: 'POST',
        data: {
            priceIDs: button.getAttribute('data-priceID'),
            posID: button.getAttribute('data-posID'),
        },
        dataType:"json",
        success:function(models) {
            // ******* DEBUG ****** //
            if ( models.debug )
            {
                debugModal(models.debug);
                return;
            }
            // ******* ERROR ****** //
            if ( models.error )
            {
                that.operationStatus = false;
                //$('#paymentModal').modal('hide');
                let aR = $('#alertResponse');
                    aR.iziModal('setHeaderColor', 'rgb(189, 91, 91)');
                    aR.iziModal('setIcon', 'fas fa-exclamation-triangle');
                    aR.iziModal('setTitle', 'Ошибка! ' + models.error.message );
                    aR.iziModal('setSubtitle', "Код " + models.error.code);
                    aR.iziModal("open");
                return;
            }

            if ( button.getAttribute('data-prices') === 'all' )
            {
                that.panelsToRemove = document.querySelectorAll('.allModels .panel');
            } else if (button.getAttribute('data-prices') === 'allInModel') {
                let el = button;
                do {
                    if ( el.classList.contains('panel') ) 
                    {
                        that.panelsToRemove.push(el);
                        break;
                    }
                } while (el = el.parentElement);
            } else {
				let el = button;
                do {
                    if ( el.classList.contains('list-group-item') ) 
                    {
                        that.panelsToRemove.push(el);
                        break;
                    }
                } while (el = el.parentElement);
            }
            
            // ******* SUCCESS ****** //
            let toFirstColumn = Math.floor(models.length / 2);
            let colFirst = that.modalBody.querySelector('.columnFirst');
            let colSecond = that.modalBody.querySelector('.columnSecond');
            let totalValue = 0;

            $.each(models, function (i, model) {

                let newModelRow = document.querySelector('.PM_protoModel').cloneNode(true);

                newModelRow.classList.remove('hidden','PM_protoModel');
                let vc = model.vendorCode ? " / " + model.vendorCode : "";
                newModelRow.querySelector('.panel-heading').children[0].innerHTML = model.number_3d + vc + " - " + model.modelType;
                newModelRow.querySelector('.panel-body').children[0].src = model.imgName;

                let modelPanelID = "modelPanel_" + i;
                let collapsePanelID = "collapsePanelID_" + i;
                let ph = newModelRow.querySelector('.panel-heading');
                    ph.id = modelPanelID;
                    ph.children[0].setAttribute('href','#' + collapsePanelID);
                    ph.children[0].setAttribute('aria-controls', collapsePanelID);
                    ph.nextElementSibling.setAttribute('id', collapsePanelID);
                    ph.nextElementSibling.setAttribute('aria-labelledby', modelPanelID);
                    ph.addEventListener('click',function (e) {
                        $(this.nextElementSibling).collapse('toggle');
                    });

                let tMValue = 0;
                let ul = newModelRow.querySelector('.list-group');

                let thisModelExlPrices = [];
                $.each(model.prices, function (c, price) {
                    that.priceIDs.push(price.pID);
                    thisModelExlPrices.push(price.pID);

                    tMValue += +price.value;

                    let newLi = document.createElement('li');
                        newLi.classList.add('list-group-item');
                        newLi.classList.add('cursorArrow');

                    let p3D = '';
                    if ( +price.is3dGrade === 1 ) p3D = "<u>3D Моделирование: </u>";
                    let span = document.createElement('span');
                        span.setAttribute('data-toggle', 'tooltip');
                        span.setAttribute('data-placement', 'bottom');
                        span.setAttribute('title', price.gsDescr);
                        span.innerHTML = p3D + "<i>" + price.costName + ": </i><b>" + price.value + 'грн.</b>';
                    let spanFIO = document.createElement('span');
                    spanFIO.classList.add('pull-right', 'small');
                    spanFIO.innerHTML =  price.fio + " " + price.date;

                    let notPayedLabel = newModelRow.querySelector('.notPayed').cloneNode(true);
                    notPayedLabel.classList.remove('hidden');
                    notPayedLabel.classList.add('mr-1');

                    let accruedLabel = newModelRow.querySelector('.accrued').cloneNode(true);
                    accruedLabel.classList.remove('hidden');
                    accruedLabel.classList.add('mr-1');

                    let paySuccessLabel = newModelRow.querySelector('.paySuccess').cloneNode(true);
                    paySuccessLabel.classList.add('mr-1');

                    let exlPriceButton = newModelRow.querySelector('.exlPriceButton').cloneNode(true);
                        exlPriceButton.removeAttribute('hidden');

                    let div = document.createElement('div');
                        div.classList.add('pt-1');
                        div.appendChild(accruedLabel);
                        div.appendChild(notPayedLabel);
                        div.appendChild(paySuccessLabel);
                        div.appendChild(exlPriceButton);

                    newLi.appendChild(span);
                    newLi.appendChild(spanFIO);
                    newLi.appendChild(div);

                    ul.appendChild(newLi);

                    exlPriceButton.addEventListener('click', function (e) {
                        that.excludePriceFromModel(price.pID, newLi);
                    });

                });
                totalValue += tMValue;

                newModelRow.querySelector('.exlModelButton').addEventListener('click', function (e) {

                    that.excludePricesFromModel( this, Object.assign([],thisModelExlPrices), tMValue );
                });

                let footer = newModelRow.querySelector('.panel-footer');
                footer.innerHTML = "Всего: " + tMValue + "грн.";

                let paidType = button.getAttribute('data-prices');
                let topText = "";
                let md12 = "col-md-12";
                switch (paidType)
                {
                    case "allInModel":
                        topText = "Оплатить всё в модели";
                        $(newModelRow.querySelector('.collapse')).collapse('toggle');
                        break;
                    case     "single":
                        topText = "Оплатить <i>" + models[0].prices[0].costName + "</i>";
                        $(newModelRow.querySelector('.collapse')).collapse('toggle');
                        break;
                    case        "all":
                        topText = "Оплатить всё - <b>" + totalValue + "</b>грн.";
                        that.modal.children[0].classList.add("modal-lg");
                        md12 = "col-md-6";
                        break;
                }
                that.modal.querySelector('.modal-title').innerHTML = topText;
                if ( i <= toFirstColumn )
                {
                    colFirst.classList.add(md12);
                    colFirst.appendChild(newModelRow);
                } else {
                    colSecond.classList.add(md12);
                    colSecond.appendChild(newModelRow);
                }
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                });
            });
        },

        error:function(err) {
            that.operationStatus = false;
            //$('#paymentModal').modal('hide');
            let aR = $('#alertResponse');
                aR.iziModal('setHeaderColor', 'rgb(189, 91, 91)');
                aR.iziModal('setIcon', 'fas fa-bug');
                aR.iziModal('setTitle', 'Ошибка на сервере! Попробуйте позже.');
                aR.iziModal('setSubtitle', "Код: " + err.status);

                aR.iziModal("open");
            cursorRestore();
        }
    });

};

PaymentManager.prototype.payPrices = function(payButton)
{
    payButton.classList.add('disabled');
    cursorSet('wait');
    
    let that = this;
    $.ajax({
        url: "/payment-manager/?payPrice",
        type: 'POST',
        data: {
            prices: that.priceIDs,
            excludePricesList: that.excludePricesList,
        },
        dataType:"json",
        success:function(data) {
            if ( data.success ) {
                that.operationStatus = true;

                let notPayed = that.modalBody.querySelectorAll('.notPayed');
                $.each(notPayed, function (i, label) {
                    label.classList.add('hidden');
                });

                setTimeout(function () {
                    let paySuccess = that.modalBody.querySelectorAll('.paySuccess');
                    $.each(paySuccess, function (i, label) {
                        label.classList.remove('hidden');
                    });
                    payButton.previousElementSibling.classList.add('hidden');
                }, 400);

                setTimeout(function () {
                    $('#paymentModal').modal('hide');
                    let aR = $('#alertResponse');
                        aR.iziModal('setHeaderColor', '#d09d16');
                        aR.iziModal('setTitle', 'Операция прошла успешно!');
                        aR.iziModal('setSubtitle', data.success.message);
                        aR.iziModal('setIcon', 'far fa-check-circle');

                        aR.iziModal("open");
                    cursorRestore();
                }, 800);

            } else if (data.error) {
                that.operationStatus = false;
                //$('#paymentModal').modal('hide');
                let aR = $('#alertResponse');
                aR.iziModal('setHeaderColor', 'rgb(189, 91, 91)');
                aR.iziModal('setIcon', 'fas fa-exclamation-triangle');
                aR.iziModal('setTitle', 'Операция завершена с ошибкой! ' + data.error.code);
                aR.iziModal('setSubtitle', data.error.message);

                aR.iziModal("open");
                cursorRestore();
            }
        },
        error:function(err) {
            that.operationStatus = false;
            //$('#paymentModal').modal('hide');
            let aR = $('#alertResponse');
            aR.iziModal('setHeaderColor', 'rgb(189, 91, 91)');
            aR.iziModal('setIcon', 'fas fa-bug');
            aR.iziModal('setTitle', 'Ошибка на сервере! Попробуйте позже.');
            aR.iziModal('setSubtitle', "Код: " + err.status);

            aR.iziModal("open");
            cursorRestore();
        }
    });

};

let pm = new PaymentManager();