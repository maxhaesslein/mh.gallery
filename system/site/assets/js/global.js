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


var keyboardNavigation = {

	init: function(){
		if( ! document.body.classList.contains('template-image') ) return;

		document.addEventListener( 'keydown', keyboardNavigation.navigate );
	},

	navigate: function(e){

		var target = false;

		if( e.key == 'ArrowDown' || e.key == 'ArrowRight' ) {
			target = document.getElementById('navigate-next');
			e.preventDefault();
		} else if( e.key == 'ArrowUp' || e.key == 'ArrowLeft' ) {
			target = document.getElementById('navigate-prev');
			e.preventDefault();
		}

		if( ! target || ! target.href ) return;
		
		window.location.href = target.href;

	}

};


var fullscreenButton = {

	init: function(){
		var button = document.getElementById('action-fullscreen');

		if( ! button ) return;

		button.addEventListener( 'click', fullscreenButton.toggle );

	},

	toggle: function( e ){

		e.preventDefault();

		var target = document.getElementById('fullscreen-target');

		if( ! target ) return;

		if( ! document.fullscreenElement ) {
			document.documentElement.requestFullscreen( target );
		} else if ( document.exitFullscreen ) {
			document.exitFullscreen();
		}
	}

}


function init() {
	hideCursor.init();
	keyboardNavigation.init();
	fullscreenButton.init();
};


window.addEventListener( 'load', init );

})();
