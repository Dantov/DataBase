"use strict";
function Main() {

    this.searchValue = '';
    this.collectionName = '';

    this.queryParams = {};

    this.parseQueryString();
    this.lightUpSomeButtons();
    this.modalStatusesInit();

    this.modelsHref();
    debug('Main Init');
}

/**
 * возьмем id из строки запроса, если он там есть
 */
Main.prototype.parseQueryString = function()
{
    let url = window.location.href;
    if ( ~url.indexOf('?') )
    {
        let params = url.split('?')[1].split('&');
        for (let i=0; i<params.length; i++)
        {
            let paramVal = params[i].split('=');
            this.queryParams[ paramVal[0] ] = paramVal[1];
        }

        // вольмем и имя стр.
        let path = url.split('?')[0].split('/');
        this.queryParams.pageName= path[path.length-1] ? path[path.length-1] : path[path.length-2];
    }
};

Main.prototype.getQueryParam = function(param)
{
    let params = this.queryParams;
    for ( let key in params )
    {
        if ( key === param ) return params[key];
    }
};

Main.prototype.searchIn = function(num) {
    if ( num === 1 || num === 2 ) {
        $.ajax({
            url: "/globals/serchIn",
            type: 'POST',
            data: {
               searchInNum: num
            },
            success:function(data) {
                debug(data,'data');
                let searchInBtn = document.getElementById('searchInBtn').firstElementChild;
                searchInBtn.innerHTML = data;
            },
            error:function(error) {
                debug(error,"searchIn Error");
            }
        });
    }
};

Main.prototype.collectionSelect = function(self) {
	
	let collection_block = document.getElementById('collection_block');
	if ( collection_block.getAttribute('class')=='visible' )
	{
		collection_block.style.top = 20 + 'px';
		collection_block.classList.remove('visible');
		window.removeEventListener('click', hideCollBlock );
		return;
	} else {
		collection_block.classList.add('visible');
	}

	let a = this.getCoords(self);
	
	collection_block.style.top = (a.top - 15) + 'px';
		
	setTimeout(function(){
		window.addEventListener('click', hideCollBlock );
	},50);
		
	function hideCollBlock(event){
		if ( !event.target.hasAttribute('coll_block') ) {
			collection_block.style.top = 20 + 'px';
			collection_block.classList.remove('visible');
			window.removeEventListener('click', hideCollBlock );
		}
	}
};

Main.prototype.getCoords = function(elem) {

  let box = elem.getBoundingClientRect();

  let body = document.body;
  let docEl = document.documentElement;
  let scrollTop = window.pageYOffset || docEl.scrollTop || body.scrollTop;
  let scrollLeft = window.pageXOffset || docEl.scrollLeft || body.scrollLeft;
  let clientTop = docEl.clientTop || body.clientTop || 0;
  let clientLeft = docEl.clientLeft || body.clientLeft || 0;
  let top = box.top + scrollTop - clientTop;
  let left = box.left + scrollLeft - clientLeft;

  return {
    top: top,
    left: left
  };
  
};



/**
 * Ставит новую дату в рабочих центрах
 * @param self
 */
Main.prototype.changeStatusDate = function(self)
{
    let id = self.getAttribute('data-id');
    let oldDate = self.getAttribute('value');
    let newDate = self.value;

    let data = {
        changeStatusDate: 1,
        id: id,
        newDate: newDate,
        oldDate: oldDate
    };

    $.ajax({
        url: _ROOT_ + "/main/changeStatusDate",
        type: "POST",
        data: data,
        dataType:"json",
        success:function(data)
        {
            debug(data.ok);
        },
        error: function ()
        {
            alert('Ошибка!');
        }
    });

    debug(data);
    //debug(_ROOT_);
};
Main.prototype.lightUpSomeButtons = function()
{
    // подсветим кнопки Send PDF Send Xlsx
    let expiredButon = document.getElementById('expiredButon');
    let sendXLS = document.getElementById('sendXLS');
    let sendPDF = document.getElementById('sendPDF');
    function farFasToggle(elem) {
        elem.classList.toggle('far');
        elem.classList.toggle('fas');
    }
    if ( expiredButon )
    {
        expiredButon.addEventListener('mouseover',function () {
            farFasToggle(this.children[0]);
        });
        expiredButon.addEventListener('mouseout',function () {
            farFasToggle(this.children[0]);
        });
    }
    if ( sendXLS )
    {
        sendXLS.addEventListener('mouseover',function () {
            farFasToggle(this.children[0]);
        });
        sendXLS.addEventListener('mouseout',function () {
            farFasToggle(this.children[0]);
        });
    }
    if ( sendPDF )
    {
        sendPDF.addEventListener('mouseover',function () {
            farFasToggle(this.children[0]);
        });
        sendPDF.addEventListener('mouseout',function () {
            farFasToggle(this.children[0]);
        });
    }
};

Main.prototype.modalStatusesInit = function()
{
    let that = this;
    if ( document.getElementById('modalStatuses') )
    {
        $("#modalStatuses").iziModal({
            icon: 'fas fa-code-branch',
            width: "95%",
            afterRender: function() {
                document.getElementById('modalContent').classList.remove('hidden');
                let statusesChevrons = document.querySelectorAll('.statusesChevron');
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

                let currentSelectedStatus = document.getElementById('currentSelectedStatus').innerHTML;
                let modalStatuses = document.getElementById('modalStatuses');
                let statusesItems = modalStatuses.querySelectorAll('.wc-status-item');
                let panelNeedle;

                $.each(statusesItems, function(i, a)
                {
                    if ( a.children[1].innerHTML === currentSelectedStatus )
                    {
                        a.classList.add('active');
                        panelNeedle = a.parentElement.parentElement.parentElement;
                        panelNeedle.classList.remove('panel-info');
                        panelNeedle.classList.add('panel-primary');
                        panelNeedle.querySelector('button').click();
                        return;
                    }
                });

                let openAll = document.querySelector('#openAll');
                let closeAll = document.querySelector('#closeAll');
                let switchByStatusHistory = document.querySelector('.switchByStatusHistory');
                let inputDates = document.querySelector('.byStatHistory_dates').querySelectorAll('input');

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

                switchByStatusHistory.addEventListener('mouseup', function (event) {
                    // event.stopPropagation();
                    // event.preventDefault();
                    that.searchByStatusesHistory(this.querySelector('input'));
                });

                inputDates[0].addEventListener('change', function () {
                    that.changeDateByStatusesHistory(this);
                });
                inputDates[1].addEventListener('change', function () {
                    that.changeDateByStatusesHistory(this);
                });
            },
        });
    }
};



Main.prototype.searchByStatusesHistory = function(input)
{
    let check = !input.checked;
    let span = input.parentElement.nextElementSibling;
    let dates = span.nextElementSibling;
    let inputDates = dates.querySelectorAll('input');

    $.ajax({
        url: "/main/changeStatusByHistory",
        type: "POST",
        data: {
            statHistoryON: 1,
            byStatHistory: +check,
        },
        dataType:"json",
        success:function(data)
        {
            if ( data.ok )
            {
                span.style.fontWeight = 'bold';
                span.innerHTML = 'Поиск в истории включен!';

                dates.classList.remove('hidden');
            } else {
                span.style.fontWeight = 'normal';
                span.innerHTML = 'Поиск в истории выключен';

                inputDates[0].value = '';
                inputDates[1].value = '';
                dates.classList.add('hidden');
            }
        },
        error: function (e)
        {
            debug(e,'Ошибка!');
        }
    });
};
Main.prototype.changeDateByStatusesHistory = function(dateInput)
{
    let obj = {
        changeDates: 1,
    };
    obj[dateInput.name] = dateInput.value ? dateInput.value : '0000-00-00';

    $.ajax({
        url: "/main/changeStatusByHistory",
        type: "POST",
        data: obj,
        dataType:"json",
        success:function(data)
        {
            if ( data.ok ) {
                debug(data.ok);
            }
        },
        error: function (e)
        {
            debug(obj);
            debug(e,'Ошибка!');
        }
    });
};

Main.prototype.modelsHref = function()
{
    let modelsContent = document.getElementById('loadeding_cont');
    if ( !modelsContent ) return;

    //let protoLabels = document.querySelectorAll('.main-label-new');
    let prj = modelsContent.querySelectorAll('.prj-thumbnail');

    /*
    $.each(prj, function(i, model)
    {
        model.addEventListener('click', function (e) {

            if ( e.target.classList.contains('selectionCheck') )
                return;

            let href = this.querySelector('.model-href').getAttribute('href');
            //redirect(href);
            debug(href);
        }, true);
    });
    */
};

if ( main !== 'object' ) main = new Main();