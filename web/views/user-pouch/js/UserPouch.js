"use strict";

function UserPouch()
{

    this.init();
}

UserPouch.prototype.init = function()
{
    this.addCollapsesAll();
    this.addCollapsesEvent();
    $(function () {
      $('[data-toggle="tooltip"]').tooltip();
    });
    debug('UserPouch init ok!');
};


/**
 * Обработчик на кнопку "Раскрыть все"
 */
UserPouch.prototype.addCollapsesAll = function()
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
 * Накинем обработчик на панель, что бы открывалась не только лишь по клику на ссылке collapse
 */
UserPouch.prototype.addCollapsesEvent = function()
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

let up = new UserPouch();