"use strict";
//-------- Statuses buttons ---------//
let statusesChevrons = document.querySelectorAll('.statusesChevron');
if ( statusesChevrons )
{
    $.each(statusesChevrons, function(i, button)
    {
        button.addEventListener('click', function () {

            if ( +button.getAttribute('data-status') === 0 )
            {
                button.setAttribute('data-status','1')
            } else {
                button.setAttribute('data-status','0');
            }

            button.classList.toggle('btn-info');
            button.classList.toggle('btn-primary');
            button.children[0].classList.toggle('glyphicon-menu-down');
            button.children[0].classList.toggle('glyphicon-menu-left');
            let statArea = this.parentElement.parentElement.children[1];
            statArea.classList.toggle('statusesPanelBodyHidden');
            statArea.classList.toggle('statusesPanelBodyVisible');
        }, false);
    });
}

let workingCenters = document.getElementById('workingCenters');
if ( workingCenters )
{
    let panelNeedle;
    let statusesInputs = workingCenters.querySelectorAll('input');
    $.each(statusesInputs, function(i, input)
    {
        if ( input.hasAttribute('checked') )
        {
            panelNeedle = input.parentElement.parentElement.parentElement.parentElement;
            panelNeedle.classList.remove('panel-info');
            panelNeedle.classList.add('panel-warning');

            panelNeedle.querySelector('button').click();
        }
    });

    let openAll = document.querySelector('#openAll');
    let closeAll = document.querySelector('#closeAll');
    openAll.addEventListener('click', function () {

        $.each(statusesChevrons, function(i, button)
        {
            if ( +button.getAttribute('data-status') === 1 ) return;
            button.click();
        });

        this.classList.add('hidden');
        closeAll.classList.remove('hidden');
    }, false);
    closeAll.addEventListener('click', function () {
        $.each(statusesChevrons, function(i, button)
        {
            if ( +button.getAttribute('data-status') === 0 ) return;
            button.click();
        });

        this.classList.add('hidden');
        openAll.classList.remove('hidden');
    }, false);
}
//-------- END Statuses buttons ---------//
