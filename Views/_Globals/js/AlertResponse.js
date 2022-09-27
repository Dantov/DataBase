"use strict";

function AlertResponse( modalID, initObj )
{
	this.modalID = '';
	if ( modalID ) 
		this.modalID = modalID;

	this.modal = $( this.modalID );
	this.initObj = {};

	this.defaultMessages = {
		success: {
			headerColor: "#d09d16",
			icon: "far fa-check-circle",
			title: "",
			subtitle: "Операция прошла успешно!",
			width: "700px",
		},
		error: {
			headerColor: "#ff260d",
			icon: "fas fa-exclamation-triangle",
			title: "",
			subtitle: "Операция завершена с ошибкой!",
			width: "700px",
		},
		serverError: {
			headerColor: "#FF5733",
			icon: "fas fa-bug'",
			title: "Ошибка на сервере! Попробуйте позже.",
			subtitle: "Код: ",
			width: "100%",
		},
        warning: {
            headerColor: "#FF7F50",
            icon: "fas fa-exclamation-circle",
            title: "",
            subtitle: "",
            width: "700px",
        },
        notice: {
            headerColor: "#FF69B4",
            icon: "fas fa-exclamation",
            title: "",
            subtitle: "",
            width: "700px",
        },
	};
	this.callbacks = {
		opening: function(){},
		opened:  function(){},
		closing: function(){},
		closed: function(){},
		fullscreen: function(){},
	};

	this.callback = null;

	this.init( initObj );
}

/** INITIALIZATION **/
AlertResponse.prototype.init = function( initObj )
{
	if ( initObj ) 
	{
		this.initObj = initObj;
	} else {
		this.initObj = {
	        timeout: 5000,
	        zindex: 1100,
	        width: '700px',
	        timeoutProgressbar: true,
	        pauseOnHover: true,
	        restoreDefaultContent: true,
	    };
	}
	this.modal.iziModal( this.initObj );
	let that = this;
	$(document).on('opening', this.modalID, function (e, modal) {
    	that.callback = that.callbacks.opening(); 
    });
    $(document).on('opened', this.modalID, function (e, modal) {
    	that.callback = that.callbacks.opened(); 
    });
    $(document).on('closing', this.modalID, function (e, modal) {
    	that.callback = that.callbacks.closing(); 
    });
    $(document).on('closed', this.modalID, function (e, modal) {
    	that.callback = that.callbacks.closed();
    });
    
    debug("AR init ok!");
};

AlertResponse.prototype.onClosing = function( callback )
{
	if ( typeof callback === 'function' )
		this.callbacks.closing = callback;
};
AlertResponse.prototype.onClosed = function( callback )
{
	if ( typeof callback === 'function' )
		this.callbacks.closed = callback;
};

AlertResponse.prototype.setDefaultMessage = function( callType, method, value )
{
	if ( !callType && !method ) return;

	if ( this.defaultMessages[callType] )
	{
		let calltype = this.defaultMessages[callType];
		if ( calltype[method] )
			calltype[method] = value;
	}
};


/** CALLS **/
AlertResponse.prototype.success = function( message, code )
{
	let dM = this.defaultMessages.success;

	this.modal.iziModal('setWidth', dM.width);
	this.modal.iziModal('setHeaderColor', dM.headerColor);
    this.modal.iziModal('setIcon', dM.icon);
    this.modal.iziModal('setTitle', message);
    this.modal.iziModal('setSubtitle', dM.subtitle + " Код: " + code);

    this.modal.iziModal("open");
};
AlertResponse.prototype.error = function( message, code, errorObject )
{
	let dM = this.defaultMessages.error;

	this.modal.iziModal('setWidth', dM.width);
	this.modal.iziModal('setHeaderColor', dM.headerColor);
    this.modal.iziModal('setIcon', dM.icon);
    this.modal.iziModal('setTitle', message);
    this.modal.iziModal('setSubtitle', dM.subtitle + " Код: " + code);
    if ( errorObject )
    {
        let text = '';
        if ( errorObject.file )
        {
        	this.modal.iziModal('setWidth', "90%");
        	this.modal.iziModal('pauseProgress');
        	text = '<div class="p1">';
            text += '<p class="textSizeMiddle bg-warning p1">File: <b>'+ errorObject.file +' __ on line: '+ errorObject.line +'</b></p>';
        }

        if ( errorObject.previous )
            text += '<p>Previous: <b>'+ errorObject.previous +'</b></p>';
        
        if ( errorObject.trace )
        {
            text += '<p class="bg-info p1"> <b>Trace:</b> <br>';
            $.each(errorObject.trace, function (i, tarceObj) {
                text += '<p class="mb-1 brb-2-secondary">File: <b>'+ tarceObj.file +' __ on line: '+ tarceObj.line +'</b><br>';
                text += 'Class: <b>'+ tarceObj.class +' '+ tarceObj.type +'<i>'+  tarceObj.function + '()</i></b></p>';
            });
            text += '</p>';
        }
        if ( text )
        {
        	text += '</div>';
        	this.modal.iziModal('setContent', text);
        }
    }

    this.modal.iziModal("open");
};
AlertResponse.prototype.serverError = function( code, message )
{
	let dM = this.defaultMessages.serverError;
	this.modal.iziModal('setHeaderColor', dM.headerColor);
    this.modal.iziModal('setIcon', dM.icon);
    this.modal.iziModal('setTitle', dM.title);
    this.modal.iziModal('setSubtitle', dM.subtitle + code);
    this.modal.iziModal('setWidth', dM.width);
    this.modal.iziModal('pauseProgress');
    if ( message ) // Убрать все теги <script> в этой мессаге
    	this.modal.iziModal('setContent', '<div>'+ message +'</div>');

    this.modal.iziModal("open");
};
AlertResponse.prototype.warning = function( message, code )
{
    let dM = this.defaultMessages.warning;

    this.modal.iziModal('setWidth', dM.width);
    this.modal.iziModal('setHeaderColor', dM.headerColor);
    this.modal.iziModal('setIcon', dM.icon);
    this.modal.iziModal('setTitle', message);
    this.modal.iziModal('setSubtitle', dM.subtitle + " Код: " + code);

    this.modal.iziModal("open");
};
AlertResponse.prototype.notice = function( message, code )
{
    let dM = this.defaultMessages.notice;

    this.modal.iziModal('setWidth', dM.width);
    this.modal.iziModal('setHeaderColor', dM.headerColor);
    this.modal.iziModal('setIcon', dM.icon);
    this.modal.iziModal('setTitle', message);
    this.modal.iziModal('setSubtitle', dM.subtitle + " Код: " + code);

    this.modal.iziModal("open");
};

AlertResponse.prototype.call = function( callType, message, code, errorObject )
{
    switch (callType)
    {
        case "success":
        {
            this.success(message, code);
        } break;
        case "error":
        {
        	this.error(message, code, errorObject);
        } break;
        case "serverError":
        {
           this.serverError( message, code );
        } break;
        case "debug":
        {
        	if ( typeof debugModal === 'function' )
                return debugModal(data.debug);
        } break;
        default:
        {
        } break;
    }
};

let AR = new AlertResponse( "#alertResponseModal" );