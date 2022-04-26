"use strict";
//debug(wsUserData);

let tabName; // идентификатор текущей вкладки
let userName = wsUserData.fio;

let ws;
let lenSS = sessionStorage.length;

if ( sessionStorage.length > 0 ) //просто обновили страницу
{
    // вдруг!! на новой вкладке пелогинился новый юзвер
    if ( tabName !== userName )
    {
        setTabName();
    } else {
        // имя вкладки уже есть
        tabName = sessionStorage.getItem('tabName');
    }

} else {
    //запишем вкладку в сессионное и локальное хранилище
    setTabName();
}
function setTabName()
{
    let timeMs = new Date().getTime();
    tabName = 'Tab_' + timeMs;

    sessionStorage.setItem('tabName', tabName);
}

//console.log("sessionStorage lenght = " + lenSS);
//console.log("tabName = " + tabName);
//console.log("UserName = " + userName);

function wsEventHandlers()
{
    ws.onopen = function(e) {
        console.log("[open] Соединение установлено");
		console.log('wsReadyState = ' + this.readyState);
    };
    ws.onmessage = function(evt)
    {
        try
        {
            let dataObj = JSON.parse(evt.data);
            //debug(dataObj);

            if ( dataObj.message === 'progressBarPDF' )
            {
                progressModal.ProgressBar(+dataObj.progressBarPercent);
                debug(+dataObj.progressBarPercent);
            }

            if ( typeof dataObj.newPushNotice === 'object' && _PNSHOW_ )
            {
                console.log(dataObj.newPushNotice);
                if ( !pushNotice ) pushNotice = new PushNotice();
                pushNotice.addNotice(dataObj.newPushNotice);
            }

            if ( typeof dataObj.newPushNoticeRepairs === 'object' && _PNSHOW_ )
            {
                console.log(dataObj.newPushNoticeRepairs);
                if ( !repairNotices ) repairNotices = new PushNotice();
                repairNotices.countComingNotice(dataObj.newPushNoticeRepairs);
            }

            if ( dataObj.prevMakerData === 1 )
            {
                if ( typeof (previewsMakerProgressData) === 'function' )
                    previewsMakerProgressData(+dataObj.progressBarPercent, dataObj.progressMessage);

                //debug(+dataObj.progressBarPercent);
            }
        }
        catch(e)
        {
            console.log(e);
            console.log('data = ', evt.data);
        }

    };
    ws.onclose = function(event) {
        if ( event.wasClean )
        {
            console.log('[close] Соединение закрыто чисто, код=${event.code} причина=${event.reason}');
        } else {
            // например, сервер убил процесс или сеть недоступна
            // обычно в этом случае event.code 1006
            console.log('[close] Соединение прервано');
        }
    };
}

function wsConnect()
{
    // регистрируемся на WebSocket сервере 127.0.0.1
    ws = new WebSocket("ws://"+_HOSTNAME_+":8000/?user=" + userName + "&tab=" + tabName + "&userID=" + wsUserData.id);
    wsEventHandlers();
}

wsConnect();
//console.log('wsReadyState = ' + ws.readyState);

setInterval(function() {
    if ( ws.readyState === 1 )
    {
        //console.log('Connected.');
        /*
        ws.send(
            JSON.stringify({
                'message':"Меня зовут Джон",
                'toUsers':['user1','user2'],
            })
        );
        */
    }

    if ( ws.readyState === 3 )
    {
        // console.log('Connection lost.');
        // console.log('Trying to reconnect...');
        // wsConnect();
    }

}, 5000);