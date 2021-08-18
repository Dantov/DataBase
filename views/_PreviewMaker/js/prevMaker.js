"use strict";


let start = document.getElementById('start');

let progressBar = document.querySelector('.progress-bar');
let textBox = document.getElementById('textBox');

start.addEventListener('click', function () {


    $.ajax({
        url: "/preview-maker/start",
        type: "POST",
        data: {
            start: 1,
            tabID: tabName,
        },
        dataType:"json",
        beforeSend:function() {
            start.innerHTML = "Processing...";
            start.classList.add('active','disabled');
        },
        success:function(data) {
            if ( data.finish ) {
                console.log(data.message);



                start.classList.remove('active','disabled');
                start.innerHTML = "Start operation";
            }

        }
    });


}, false);


function previewsMakerProgressData( percent, message )
{
    progressBar.style.width = percent + "%";
    progressBar.innerHTML = percent + "%";
    textBox.textContent += message + '\n';
}
