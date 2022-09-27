"use strict";

function onImgLoad(self) {
	let prevImg = self.previousElementSibling;
		prevImg.classList.toggle('hidden');
	self.classList.toggle('hidden');
}
/*
function trytoload(){
	
	var timerId = setInterval(function() {
		var loaded_cont = document.getElementById('loaded_cont');
		if ( loaded_cont ) {
			var loadingCircle = document.getElementById('loadingCircle');
				loadingCircle.classList.add('hidden');
			var loadeding_cont = document.getElementById('loadeding_cont');
				loadeding_cont.classList.remove('hidden');
			//console.log('gotcha');
			clearInterval(timerId);
			return;
		}
	}, 250);
	
};
trytoload();
*/
