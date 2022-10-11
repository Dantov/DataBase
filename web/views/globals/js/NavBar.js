function NavBar()
{
	if ( !_PNSHOW_ )
        if ( document.getElementById('noticesBadge') )
            document.getElementById('noticesBadge').classList.add('hidden');
}
	
NavBar.prototype.getCoords = function(elem) {

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
NavBar.prototype.collectionSelect = function(self) {

    var collection_block = document.getElementById('collection_block');
    if ( collection_block.getAttribute('class') == 'visible' )
    {
        collection_block.style.top = 20 + 'px';
        collection_block.classList.remove('visible');
        window.removeEventListener('click', hideCollBlock );
        return;
    } else {
        collection_block.classList.add('visible');
    }

    var a = this.getCoords(self);

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

let navbar = new NavBar();