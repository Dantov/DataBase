"use strict";
//-------- Side buttons ---------//
$.each(document.querySelectorAll('.submitButton'), function (index, button) {
    button.addEventListener('click',function (event) {
        event.preventDefault();
        event.stopPropagation();

        submitForm();
    }, false);
});

let addEditSideButtons = {};
window.addEventListener('load',function(){
    addEditSideButtons = document.getElementById('AddEditSideButtons').querySelectorAll('button');
},false);

function pageUp()
{
    //debug('Текущая прокрутка сверху: ' + window.pageYOffset);
    window.scrollTo(0,0);
}

function pageDown()
{
    let scrollHeight = Math.max(
        document.body.scrollHeight, document.documentElement.scrollHeight,
        document.body.offsetHeight, document.documentElement.offsetHeight,
        document.body.clientHeight, document.documentElement.clientHeight
    );
    window.scrollTo(0,scrollHeight);
}

window.addEventListener('scroll',function () {
    let scrollHeight = Math.max(
        document.body.scrollHeight, document.documentElement.scrollHeight,
        document.body.offsetHeight, document.documentElement.offsetHeight,
        document.body.clientHeight, document.documentElement.clientHeight
    );
    let windowHeight = document.documentElement.clientHeight;

    addEditSideButtons = document.getElementById('AddEditSideButtons').querySelectorAll('button');

    //верхняя
    if ( window.pageYOffset === 0 ) addEditSideButtons[0].classList.add('hidden');
    if ( window.pageYOffset !== 0 ) addEditSideButtons[0].classList.remove('hidden');

    // нижняя
    if ( Math.round(window.pageYOffset + windowHeight) === scrollHeight ) addEditSideButtons[2].classList.add('hidden');
    if ( Math.round(window.pageYOffset + windowHeight) !== scrollHeight ) addEditSideButtons[2].classList.remove('hidden');

});
//-------- END Side buttons ---------//