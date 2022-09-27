"use strict";

function Options(){}

Options.prototype.widthControl = function(opt) {

    //let that = this;
    $.ajax({
        url: "/options/widthControl",
        type: 'POST',
        data: {
            widthControl: opt
        },
        dataType:"json",
        success:function(data) {
            // if ( data.done === 1 ) console.log('показать во всю ширину');
            // if ( data.done === 2 ) console.log('Показать по центру');
            document.location.reload(true);
        }
    });
};

Options.prototype.noticeControl = function(opt) {

    //let that = this;
    debug(opt,'opt');
    $.ajax({
        url: "/options/noticeControl",
        type: 'POST',
        data: {
            noticeActivate: opt
        },
        dataType:"json",
        success:function(data) {
            if ( data.done === 1 ) console.log('Уведомления активированы');
            if ( data.done === 2 ) console.log('Уведомления отключены');
        }
    });
};

Options.prototype.changeBgImg = function() {
    console.log(this);
    if ( !this.checked ) {
        console.log('Ухожу');
        return;
    }

    //let src = this.previousElementSibling.getAttribute('src');
    let src = this.getAttribute('data-class');
    console.log(src);
    $.ajax({
        url: "/options/changeBgImg",
        type: 'POST',
        data: {
            srcBgImg: src
        },
        dataType:"json",
        success:function(data) {
            if ( data.done ) {
                //alert('Новый фон установлен!');
                document.querySelector('body').setAttribute('class',src);
                //document.location.reload(true);
            } else {
                alert('что-то пошло не так!');
            }
        }
    });
};


let options = new Options();

let width_control = document.getElementById('width_control');
if ( width_control.checked ) {
    width_control.addEventListener('click',function(){
        options.widthControl(2);
    }, false);
} else {
    width_control.addEventListener('click',function(){
        options.widthControl(1);
    }, false);
}

let PN_control = document.getElementById('PN_control');
if ( PN_control.checked ) {
    PN_control.addEventListener('click',function(){
        options.noticeControl(2);
    }, false);
} else {
    PN_control.addEventListener('click',function(){
        options.noticeControl(1);
    }, false);
}

let bgImages = document.querySelectorAll('.bg_img');
for( let i = 0; i < bgImages.length; i++ ) {
    bgImages[i].addEventListener('change', options.changeBgImg, false);
}


