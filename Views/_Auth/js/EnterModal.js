"use strict";
/*
function EnterModal()
{
    this.modal = $("#modal-enter");
    this.modalElem = document.querySelector("#modal-enter");


    this.init();
}

EnterModal.prototype.init = function()
{
    let that = this;


    this.modal.iziModal({
        closeOnEscape: false,
        //closeButton: false,
        overlayClose: false,
        overlayColor: 'rgba(0, 0, 0, 0.6)',
    });

    $(document).on('open', '#modal-enter', function (e) {

    });

    $(document).on('closing', '#modal-enter', function (e) {

    });
    $(document).on('closed', '#modal-enter', function (e) {

    });

    // $(document).on('load', 'body', function () {
    //     that.modal.iziModal('open');
    // });

    /*$(document).on('click', '.trigger-custom', function (event) {
        event.preventDefault();
        $('#modal-custom').iziModal('open');
    });*/

    /* JS inside the modal */
/*
    this.modal.on('click', 'header a', function(event) {
        event.preventDefault();

        let index = $(this).index();
        $(this).addClass('active').siblings('a').removeClass('active');
        $(this).parents("div").find("section").eq(index).removeClass('hide').siblings('section').addClass('hide');

        if ( $(this).index() === 0 )
        {
            $("#modal-enter .iziModal-content .icon-close").css('background', '#ddd');
        } else {
            $("#modal-enter .iziModal-content .icon-close").attr('style', '');
        }
    });

    this.modal.on('click', '.submit', function(event) {
        event.preventDefault();

        let fx = "wobble",  //wobble shake
            $modal = $(this).closest('.iziModal');

        if ( !$modal.hasClass(fx) )
        {
            $modal.addClass(fx);
            setTimeout(function() {
                $modal.removeClass(fx);
            }, 1500);
        }
    });

    console.log('Enter Modal init ok!');
};

window.addEventListener('load', function () {
    let em = new EnterModal();
    em.modal.iziModal('open');
}, false);

*/
$(function(){

    /* Instantiating iziModal */
    $("#modal-custom").iziModal({
        closeOnEscape: false,
        closeButton: false,
        overlayClose: false,
        overlayColor: 'rgba(0, 0, 0, 0.6)'
    });

    /* JS inside the modal */

    $("#modal-custom").on('click', 'header a', function(event) {
        event.preventDefault();
        let index = $(this).index();
        $(this).addClass('active').siblings('a').removeClass('active');
        $(this).parents("div").find("section").eq(index).removeClass('hide').siblings('section').addClass('hide');

        if( $(this).index() === 0 ){
            $("#modal-custom .iziModal-content .icon-close").css('background', '#ddd');
        } else {
            $("#modal-custom .iziModal-content .icon-close").attr('style', '');
        }
    });

    $("#modal-custom").on('click', '.submit', function(event) {
        event.preventDefault();

        let fx = "wobble",  //wobble shake
            $modal = $(this).closest('.iziModal');

        if( !$modal.hasClass(fx) ){
            $modal.addClass(fx);
            setTimeout(function(){
                $modal.removeClass(fx);
            }, 1500);
        }
    });

});
window.addEventListener('load', function () {
    $("#modal-custom").iziModal('open');
}, false);