(function(){


function init() {
	Ajax.init();
	HideCursor.init();
	KeyboardNavigation.init();
	TouchNavigation.init();
	FullscreenButton.init();
	Preload.init();
};
window.addEventListener( 'load', init );


var Ajax = {

	init: function(){

		if( ! document.body.classList.contains('template-image') ) return;

		var next = document.getElementById('navigate-next');
		if( next ) {
			next.addEventListener('click', function(e){
				if( Ajax.navigate(this) ) e.preventDefault();
			});
		}

		var prev = document.getElementById('navigate-prev');
		if( prev ) {
			prev.addEventListener('click', function(e){
				if( Ajax.navigate(this) ) e.preventDefault();
			});
		}

		window.addEventListener( "popstate", Ajax.urlNavigation );
		
	},

	urlNavigation: function( e ) {

		var state = e.state;

		if( ! state ) return;

		var url = state.url;

		if( ! url ) return;

		window.location.href = url;

	},

	navigate: function( el ) {

		Preload.cancel();

		var imageSlug = false;
		if( el.id == 'navigate-next' ) {
			imageSlug = el.dataset.gallerySlug+'/'+el.dataset.nextImageSlug;
		} else if( el.id == 'navigate-prev' ) {
			imageSlug = el.dataset.gallerySlug+'/'+el.dataset.prevImageSlug;
		} else {
			return false;
		}

		requestUrl = GALLERY.apiUrl+imageSlug+'/';

		var request = new XMLHttpRequest();
		request.open( 'GET', requestUrl );

		request.onreadystatechange = function(){

			if( request.readyState !== XMLHttpRequest.DONE ) return;

			if( request.status === 200 ) {

				var response = request.response;

				var url = el.href;
				if( url ) {
					history.pushState( {url: url}, false, url );
				}

				if( ! response ) {
					// TODO: handle error case
					console.warn( 'AJAX request failed.', request ); // DEBUG
					return false;
				}

				response = JSON.parse(response);
				if( response.content ) {
					document.getElementById('fullscreen-target').innerHTML = response.content;
				}

				if( response.title ) {
					title = response.title;
					Ajax.updateTitle( title );
				}

				if( response.prev_image_url ) {
					document.getElementById('prev-image-preload').href = response.prev_image_url;
				}
				if( response.next_image_url ) {
					document.getElementById('next-image-preload').href = response.next_image_url;
				}

				init(); // re-init all event listeners

			} else {
				// something went wrong …
				// TODO: handle error case
				console.warn( 'AJAX request failed.', request ); // DEBUG
			}

		}
		request.send();

		return true;
	},

	updateTitle: function( newTitle ) {
		document.title = newTitle;
	}

};


var HideCursor = {

	delay: 2000,
	timer: false,

	init: function(){

		HideCursor.startTimeout();

		document.addEventListener( 'mousemove', HideCursor.showCursor );
	},

	startTimeout: function(){
		HideCursor.timer = setTimeout(function(){
			document.body.classList.add('cursor-hidden');
		}, HideCursor.delay);
	},

	showCursor: function(){
		clearTimeout(HideCursor.timer);
		document.body.classList.remove('cursor-hidden');
		HideCursor.startTimeout();
	}

};


var KeyboardNavigation = {

	init: function(){
		if( ! document.body.classList.contains('template-image') ) return;

		document.addEventListener( 'keydown', KeyboardNavigation.navigate );
	},

	navigate: function(e){

		var target = false;

		if( e.key == 'ArrowDown' || e.key == 'ArrowRight' ) {
			target = document.getElementById('navigate-next');
			e.preventDefault();
		} else if( e.key == 'ArrowUp' || e.key == 'ArrowLeft' ) {
			target = document.getElementById('navigate-prev');
			e.preventDefault();
		} else if( e.key == 'Escape' ) {
			target = document.getElementById('navigate-overview');
			e.preventDefault();
		}

		if( ! target ) return;
		
		target.click();

	}

};


var TouchNavigation = {

	threshold: 50, // minimum pixels to move
	posX: false,

	init: function(){
		if( ! document.body.classList.contains('template-image') ) return;

		document.addEventListener( 'touchstart', TouchNavigation.navigateStart, false );
		document.addEventListener( 'touchend', TouchNavigation.navigateEnd, false );
		document.addEventListener( 'touchcancel', TouchNavigation.navigateCancel, false );
		document.addEventListener( 'touchmove', TouchNavigation.navigateMove, false );
		document.addEventListener( 'touchmove', function(e){e.preventDefault();}, false ); // fix for Edge
	},

	navigateStart: function(e){

		var touches = TouchNavigation.getTouches(e);

		if( ! touches || touches.length != 1 ) {
			TouchNavigation.navigateCancel();
			return;
		}

		TouchNavigation.posX = touches[0].clientX;

	},

	navigateCancel: function(e){

		TouchNavigation.posX = false;

	},

	navigateMove: function(e){

		var touches = TouchNavigation.getTouches(e);

		if( ! touches ) return;

		if( touches.length == 1 ) return;

		// as soon as we detect multitouch, we abort the navigation, because then the user most likely wants to zoom in

		TouchNavigation.navigateCancel();

	},

	navigateEnd: function(e){

		if( TouchNavigation.posX === false ) return;

		var touches = TouchNavigation.getTouches(e);

		if( ! touches || touches.length != 1 ) {
			TouchNavigation.navigateCancel();
			return;
		}

		var newClientX = touches[0].clientX;

		var delta = newClientX - TouchNavigation.posX;

		var direction = Math.sign(delta);

		delta = Math.abs(delta);

		TouchNavigation.posX = false;

		if( delta < TouchNavigation.threshold ) return;

		var target = false;

		if( direction < 0 ) {
			// swipe to left, next image
			target = document.getElementById('navigate-next');
			e.preventDefault();
		} else {
			// swipe to right, prev image
			target = document.getElementById('navigate-prev');
			e.preventDefault();
		}

		if( ! target ) return;
		
		target.click();

	},

	getTouches: function(e){
		if( ! e.changedTouches ) return new Array(e);

		return e.changedTouches;
	}

};


var FullscreenButton = {

	init: function(){
		var button = document.getElementById('action-fullscreen');

		if( ! button ) return;

		button.addEventListener( 'click', FullscreenButton.toggle );

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

};


var Preload = {

	preloaded: [],
	timeout: false,
	request: false,

	init: function(){

		if( ! document.body.classList.contains('template-image') ) return;

		var next = document.getElementById('navigate-next');
		if( next ) {
			imageSlug = next.dataset.gallerySlug+'/'+next.dataset.nextImageSlug;

			Preload.timeout = setTimeout(function(){
				Preload.load(imageSlug);
			}, 500);
		}

	},

	cancel: function(){
		clearTimeout(Preload.timeout);
		Preload.request.abort();
		Preload.timeout = false;
	},

	load: function(imageSlug) {

		if( Preload.preloaded.includes(imageSlug) ) return;

		Preload.preloaded.push(imageSlug);

		requestUrl = GALLERY.apiUrl+imageSlug+'/?imageonly=true';

		Preload.request = new XMLHttpRequest();
		Preload.request.open( 'GET', requestUrl );

		Preload.request.onreadystatechange = function(){

			if( Preload.request.readyState !== XMLHttpRequest.DONE ) return;

			if( Preload.request.status === 200 ) {

				var response = Preload.request.response;

				if( ! response ) {
					// ignore error case
					return;
				}

				response = JSON.parse(response);

				var oldPreloaders = document.querySelectorAll('.image-preload');
				for( var oldPreloader of oldPreloaders ) {
					oldPreloader.remove();
				}

				var wrapper = document.createElement('div');
				wrapper.classList.add('image-preload');
				wrapper.innerHTML = response.content;

				var container = document.getElementById('image-wrapper').querySelector('.image-container');
				container.appendChild(wrapper);

			} else {
				// something went wrong …
				// ignore error case
				return;
			}

		}
		Preload.request.send();

	},

};


})();