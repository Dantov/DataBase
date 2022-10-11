"use strict";

function CollectionsModal() {

	this.currentInput = {};
    this.init();
}

CollectionsModal.prototype.init = function()
{
    debug('init collections modal');
    let that = this;
    $('#collectionsModal').iziModal({
        headerColor: '#41a868',
        icon: 'fas fa-gem',
		width: '90%',
        transitionIn: 'comingIn',
        transitionOut: 'comingOut',
        overlayClose: true,
        closeButton: true,
        afterRender: function () {
            //document.getElementById('modalCollectionsContent').classList.remove('hidden');
            let modalContent = document.querySelector('#modalCollectionsContent');
                modalContent.classList.remove('hidden');
                modalContent.addEventListener('click', function (event) {
                    let click = event.target;
                    if ( !click.hasAttribute('elemToAdd') ) return;

                    that.currentInput.value = click.innerHTML;
                });
        },
    });

    // начало открытия
	/*
    $(document).on('opening', '#collectionsModal', that.addCollection.bind(null, that) );
    // Начало закрытия
    $(document).on('closing', '#collectionsModal', that.onModalClosing.bind(null, that) );
    // исчезло
    $(document).on('closed', '#collectionsModal', that.onModalClosed.bind(null, that) );
    */
};


let collectionsModal = new CollectionsModal();