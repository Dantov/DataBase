"use strict";
$(function () {
	let tooltips = $('[data-toggle="tooltip"]');
    tooltips.tooltip();

    // Скопирует оригинальную строку в буфер
    tooltips.each(function (i,elem) {
        elem.addEventListener('click',function () {
        	let inner = this.innerHTML;
        	this.innerHTML = this.getAttribute('data-original-title');
        	copyInnerHTMLToClipboard(this);
        	this.innerHTML = inner;
        },false);
    });
});


//--------- Увеличит картинку на высоту правого блока ----------//
if ( _IS_DESKTOP_ )
{
    let descr = document.getElementById('descr');

    let descrH = descr.offsetHeight;
    let images_blockH = images_block.offsetHeight;

    if ( descrH >= images_blockH ) {
        descr.classList.add('bordersMiddleLeft');
        images_block.classList.remove('bordersMiddleRight');
    }
    if ( descrH <= images_blockH ) {
        descr.classList.remove('bordersMiddleLeft');
        images_block.classList.add('bordersMiddleRight');
    }

    window.addEventListener('load', function () {
        let dPHeight = document.querySelector('.descriptionPanel').offsetHeight;
        document.querySelector('.mainImage').style.height = dPHeight + 'px';

        document.querySelectorAll('.imageSmall').forEach( image => {
            image.style.height = image.offsetWidth + 'px';
        } );

    });
}


//--------- отображаем превью при наведении ----------//
addPrevImg( document.getElementById('complects') );
addPrevImg( document.querySelector('.table_vc_links') );

function addPrevImg(domEl) 
{
    if ( !domEl ) return;
	let complects = domEl.querySelectorAll('a');
	
	for ( let i = 0; i < complects.length; i++ ) {
	
		complects[i].addEventListener('mouseover',function(event){
			
			let mouseX = event.pageX;
			let mouseY = event.pageY;
			
			let hover = event.target;
			let imageBoxPrev = document.getElementById('imageBoxPrev');
				imageBoxPrev.style.top = 0 + 'px';
				imageBoxPrev.style.left = 0 + 'px';
			
			let src = hover.getAttribute('imgtoshow');

			imageBoxPrev.style.left = mouseX - 215 + 'px';

            imageBoxPrev.setAttribute('src',src);
            imageBoxPrev.onload = function(){
                imageBoxPrev.classList.remove('hidden');

                if ( domEl.classList.contains('table_vc_links') ){
                    imageBoxPrev.style.top = mouseY - 15 - imageBoxPrev.height + 'px';
				} else {
                    imageBoxPrev.style.top = mouseY + 15 + 'px';
				}

			};

		},false);
		
		complects[i].addEventListener('mouseout',function(event) {
			
			let imageBoxPrev = document.getElementById('imageBoxPrev');
			imageBoxPrev.classList.add('hidden');
            imageBoxPrev.removeAttribute('src');
			
		},false);

	}
}

// сделали textarea по высоте экрана
let tab5 = document.querySelector('body');
let textAreas = tab5.querySelectorAll('textarea');
if ( textAreas.length )
{
	let pre = document.createElement('pre');
	let appPre;
	for( let i = 0; i < textAreas.length; i++ ) {

		pre.innerHTML = textAreas[i].value;
		appPre = tab5.appendChild(pre);
		appPre.classList.add('br-0');
		let preHeight = appPre.offsetHeight;
		textAreas[i].style.height = preHeight + "px";
	}
	appPre.remove();	
}

function registerScript( targetElem, scriptPath )
{
    if ( !scriptPath || typeof scriptPath !== "string" )
    {
        debug("Script path error");
        return;
    }

    if ( !targetElem )
        debug("Target element can not be empty");
    let tElem;

    if ( typeof targetElem === "string" )
        tElem = document.querySelector(targetElem);

    if ( targetElem instanceof HTMLElement )
        tElem = targetElem;

    if ( !tElem )
    {
        debug("Target element error");
        return;
    }

    let scriptElem = document.createElement('script');
        scriptElem.setAttribute('src',scriptPath);

    tElem.appendChild(scriptElem);

    return scriptElem;
}

let butt3D = document.getElementById('butt3D');
if ( butt3D )
{
    butt3D.onclick = function() {
        let formData = new FormData( document.getElementById('extractForm') );
        $.ajax({
            url: "/model-view/extract-zip",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
			//dataType: "JSON", // не работает с new FormData
            success:function(resp) {
                resp = JSON.parse(resp);
                if ( resp.done === false )
                {
                    debug(resp.errMessage);
                	return;
                }

                let body = document.getElementById("body");
                let formToDell = document.getElementById("dellStlForm");

                let names = resp.names;

                for ( let i = 0; i < names.length; i++ )
                {
                    let input = document.createElement('input');
						input.setAttribute('type', 'hidden');
						input.setAttribute('name', 'dell_name[]');
						input.setAttribute('value', resp.zip_path + names[i]); //'../../Stock/'+resp['zip_path'] + names[i]
                    formToDell.appendChild(input);
                }

                //let three = document.createElement('script');
                //three.setAttribute('src','/web/js_lib/three.min.js');
                //body.appendChild(three);
                $( registerScript(body, '/web/js_lib/three.min.js') ).on('load',function()
                {
                    // let scripts = {
                    //     script2: '/web/js_lib/OrbitControls.js',
                    //     //script3: '/web/js_lib/TrackballControls.js',
                    //     script4: '/web/js_lib/TransformControls.js',
                    //     script5: '/web/js_lib/STLLoader.js',
                    //     //script6: '/Views/_ModelView/js/view3D.js?ver='+new Date().getMilliseconds(),
                    // };
                    registerScript(body, '/web/js_lib/OrbitControls.js');
                    registerScript(body, '/web/js_lib/TransformControls.js');

                    $( registerScript(body, '/web/js_lib/STLLoader.js') ).on('load',function()
                    {
                        registerScript(body, '/Views/_ModelView/js/view3D.js?ver='+new Date().getMilliseconds());
                    });

                });
            },
            error:function (err) {
				debug('Error sending ajax');
				debug(err);
            }
        })
    };
}

/** Сохранение картинок */
