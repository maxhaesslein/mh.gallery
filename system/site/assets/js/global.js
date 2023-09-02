(function(){
	
var hideCursor = {

	delay: 2000,
	timer: false,

	init: function(){

		hideCursor.startTimeout();

		document.addEventListener( 'mousemove', hideCursor.showCursor );
	},

	startTimeout: function(){
		hideCursor.timer = setTimeout(function(){
			document.body.classList.add('cursor-hidden');
		}, hideCursor.delay);
	},

	showCursor: function(){
		clearTimeout(hideCursor.timer);
		document.body.classList.remove('cursor-hidden');
		hideCursor.startTimeout();
	}

};

function init() {
	hideCursor.init();
};


window.addEventListener( 'load', init );

})();
