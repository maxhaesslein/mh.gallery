(function(){


function init() {
	Ajax.init();
	HideCursor.init();
	KeyboardNavigation.init();
	TouchNavigation.init();
	FullscreenButton.init();
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

		// TOOD: maybe cancel TouchNavigation requests?
		//Preload.cancel();

		var imageSlug = false;
		if( el.id == 'navigate-next' ) {
			imageSlug = el.dataset.gallerySlug+'/'+el.dataset.nextImageSlug;
		} else if( el.id == 'navigate-prev' ) {
			imageSlug = el.dataset.gallerySlug+'/'+el.dataset.prevImageSlug;
		} else {
			return false;
		}

		requestUrl = GALLERY.apiUrl+imageSlug+'/';

		// TODO: maybe keep the request around and cancel it, if we trigger navigate() again before it finished?

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

	eventHandlersAdded: false,
	delay: 2000,
	timer: false,

	init: function(){

		HideCursor.startTimeout();

		if( HideCursor.eventHandlersAdded ) return;

		document.addEventListener( 'mousemove', HideCursor.showCursor );
		HideCursor.eventHandlersAdded = true;

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

	eventHandlersAdded: false,

	init: function(){
		if( ! document.body.classList.contains('template-image') ) return;

		if( KeyboardNavigation.eventHandlersAdded ) return;

		document.addEventListener( 'keydown', KeyboardNavigation.navigate );

		KeyboardNavigation.eventHandlersAdded = true;

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
	eventHandlersAdded: false,
	posX: false,
	startX: false,
	offset: 0,

	init: function(){
		if( ! document.body.classList.contains('template-image') ) return;

		TouchNavigation.addEventListeners();

		TouchNavigation.loadAdjacentImages();

	},

	addEventListeners: function(){

		if( TouchNavigation.eventHandlersAdded ) return;

		document.addEventListener( 'touchstart', TouchNavigation.navigateStart, false );
		document.addEventListener( 'touchend', TouchNavigation.navigateEnd, false );
		document.addEventListener( 'touchcancel', TouchNavigation.navigateCancel, false );
		document.addEventListener( 'touchmove', TouchNavigation.navigateMove, false );
		document.addEventListener( 'touchmove', function(e){e.preventDefault();}, false ); // fix for Edge

		TouchNavigation.eventHandlersAdded = true;

	},

	loadAdjacentImages: function(){

		var imageWrapper = document.getElementById('image-wrapper');

		if( ! imageWrapper ) return;

		var navigateNext = document.getElementById('navigate-next'),
			navigatePrev = document.getElementById('navigate-prev');

		if( navigateNext ) {
			var nextImageContainer = document.createElement('div');
			nextImageContainer.classList.add('image-canvas', 'image-canvas-next');
			imageWrapper.appendChild(nextImageContainer);

			var nextImageSlug = navigateNext.dataset.gallerySlug+'/'+navigateNext.dataset.nextImageSlug;
			TouchNavigation.requestImage( nextImageSlug, nextImageContainer );
		}

		if( navigatePrev ) {
			var prevImageContainer = document.createElement('div');
			prevImageContainer.classList.add('image-canvas', 'image-canvas-prev');
			imageWrapper.insertBefore(prevImageContainer, imageWrapper.firstChild);

			var prevImageSlug = navigatePrev.dataset.gallerySlug+'/'+navigatePrev.dataset.prevImageSlug;
			TouchNavigation.requestImage( prevImageSlug, prevImageContainer );
		}

	},

	requestImage: function( imageSlug, container ) {

		// TODO: we probably want to be able to abort this request

		console.log('request', imageSlug, container );

		requestUrl = GALLERY.apiUrl+imageSlug+'/?imageonly=true';

		var request = new XMLHttpRequest();
		request.open( 'GET', requestUrl );

		request.onreadystatechange = function(){

			console.log('   finished', imageSlug, container);

			if( request.readyState !== XMLHttpRequest.DONE ) return;

			if( request.status === 200 ) {

				var response = request.response;

				if( response ) {

					response = JSON.parse(response);

					if( response.content ) {

						if( container ) {
							container.innerHTML = response.content;
						} else {
							// TODO: handle error case
							console.warn('container does no longer exist', imageSlug, container)
						}

					} else {
						// TODO: handle error case
						console.warn('response.content is empty', response);
					}

				} else {
					// TODO: handle error case
					console.warn('no response', request)
				}

			} else {
				// TODO: handle error case
				console.warn( 'AJAX request failed.', request ); // DEBUG
			}

		}

		request.send();

	},

	updateImageOffset: function(){

		var offset = TouchNavigation.offset;

		var direction = Math.sign(offset);

		percent = Math.abs(offset) / (window.innerWidth);

		percent = 1 - (1 - percent/2) * (1 - percent/2); // easeOutQuad

		offset = window.innerWidth*4/5 * percent * direction;

		document.documentElement.style.setProperty('--image-offset', offset+'px');

	},

	navigateStart: function(e){

		var touches = TouchNavigation.getTouches(e);
		if( ! touches || touches.length != 1 ) {
			// as soon as we detect multitouch, we abort the navigation, because then the user most likely wants to zoom in
			TouchNavigation.navigateCancel();
			return;
		}

		TouchNavigation.posX = touches[0].clientX;
		TouchNavigation.startX = touches[0].clientX;

		TouchNavigation.offset = 0;
		TouchNavigation.updateImageOffset();

	},

	navigateCancel: function(e){

		TouchNavigation.posX = false;
		TouchNavigation.startX = false;

		TouchNavigation.offset = 0;
		TouchNavigation.updateImageOffset();

	},

	navigateMove: function(e){

		var touches = TouchNavigation.getTouches(e);
		if( ! touches || touches.length != 1 ) {
			// as soon as we detect multitouch, we abort the navigation, because then the user most likely wants to zoom in
			TouchNavigation.navigateCancel();
			return;
		}

		var prevX = TouchNavigation.posX;

		TouchNavigation.posX = touches[0].clientX;

		var distance = TouchNavigation.posX - prevX; // negative: touch moves from right to left; positive: touch moves from left to right

		TouchNavigation.offset += distance;
		TouchNavigation.updateImageOffset();

	},

	navigateEnd: function(e){

		if( TouchNavigation.startX === false ) return;

		var touches = TouchNavigation.getTouches(e);
		if( ! touches || touches.length != 1 ) {
			// as soon as we detect multitouch, we abort the navigation, because then the user most likely wants to zoom in
			TouchNavigation.navigateCancel();
			return;
		}

		var currentX = touches[0].clientX;

		var delta = currentX - TouchNavigation.startX;

		var direction = Math.sign(delta);

		delta = Math.abs(delta);

		TouchNavigation.navigateCancel(); // this resets important variables

		if( delta < TouchNavigation.threshold ) {
			return;
		}

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


})();