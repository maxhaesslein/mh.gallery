(function(){


function init() {
	Lightmode.init();
	Ajax.init();
	HideCursor.init();
	KeyboardNavigation.init();
	TouchNavigation.init();
	FullscreenButton.init();
};
window.addEventListener( 'load', init );


var Lightmode = {

	init: function(){

		var toggle = document.getElementById('lightmode-toggle');

		if( ! toggle ) return;

		// don't bother with showing the toggle, if we can't save the state.
		// we fall back to the default (light) view, or use lightmode if the
		// user has lightmode set and the browser reports this to us
		// (see snippets/header.php for the loading code)
		if( typeof(Storage) === "undefined" ) return;

		toggle.addEventListener( 'click', function(e){

			if( document.body.classList.contains( 'lightmode' ) ) {
				
				document.body.classList.add('soft-fade');
				setTimeout( function(){
					document.body.classList.remove('lightmode');
					setTimeout( function(){
						document.body.classList.remove('soft-fade');
					}, 200 );
				}, 40 );

				if( typeof(Storage) !== "undefined" ) {
					localStorage.setItem( 'lightmode_state', 'dark' );
				}
			} else {
				
				document.body.classList.add('soft-fade');
				setTimeout( function(){
					document.body.classList.add('lightmode');
					setTimeout( function(){
						document.body.classList.remove('soft-fade');
					}, 200 );
				}, 40 );

				if( typeof(Storage) !== "undefined" ) {
					localStorage.setItem( 'lightmode_state', 'light' );
				}
			}

			e.preventDefault();
		});

	}

};


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

		// TODO: maybe cancel TouchNavigation requests?
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
					TouchNavigation.updateImageOffset(0);
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

	eventHandlersAdded: false,
	delay: 2000,
	timer: false,

	init: function(){

		if( ! document.body.classList.contains('template-image') ) return;

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

	offsetThreshold: 1/4,
	threshold: 50, // minimum pixels to move
	eventHandlersAdded: false,
	posX: false,
	startX: false,
	offset: 0,
	animation: false,

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
		document.addEventListener( 'touchmove', function(e){e.preventDefault();}, false ); // fix for Edge; TODO: check, if this fix is still needed

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

			if( request.readyState !== XMLHttpRequest.DONE ) return;

			if( request.status === 200 ) {

				console.log('   finished', imageSlug, container);

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

	updateImageOffset: function( offset, animate = false, callback = false ){

		if( animate ) {
			// animate to new position

			TouchNavigation.animation = true;

			TouchNavigation.animateImageOffset( offset, 0, callback );

		} else {
			// instantly jump to new position

			TouchNavigation.animation = false;

			document.body.style.setProperty('--image-offset', offset+'px');
			TouchNavigation.offset = offset;

		}

	},

	animateImageOffset: function( targetOffset, timeStep, callback) {

		var maxSteps = 50; // this sets the length of the animation. the higher this number, the slower the animation.

		if( ! TouchNavigation.animation ) return;

		if( timeStep >= maxSteps ) {
			// this should never happen, but if it does this is our safeguard:
			TouchNavigation.offset = targetOffset;
			TouchNavigation.animation = false;
			return;
		}

		if( Math.round(TouchNavigation.offset) == Math.round(targetOffset) ) {
			TouchNavigation.offset = Math.round(targetOffset);
			TouchNavigation.animation = false;
			callback();
			return;
		}

		var distance = TouchNavigation.offset - targetOffset,
			direction = Math.sign(distance);

		distance = Math.abs(distance);

		var percent = timeStep/maxSteps;
		percent = 1 - (1 - percent/2) * (1 - percent/2); // easeOutQuad

		var offset = TouchNavigation.offset - (distance*(percent)*direction);

		document.body.style.setProperty('--image-offset', offset+'px');
		TouchNavigation.offset = offset;

		requestAnimationFrame(function(){
			TouchNavigation.animateImageOffset(targetOffset, (timeStep+1), callback);
		});

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

		TouchNavigation.updateImageOffset(0);

	},

	navigateCancel: function(e){

		TouchNavigation.posX = false;
		TouchNavigation.startX = false;

		TouchNavigation.updateImageOffset(0);

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

		TouchNavigation.updateImageOffset(TouchNavigation.offset + distance);

	},

	navigateEnd: function(e){

		if( TouchNavigation.startX === false ) return;

		var offsetThreshold = window.innerWidth*TouchNavigation.offsetThreshold; 

		var offset = TouchNavigation.offset;

		var direction = Math.sign(offset),
			offset = Math.abs(offset);

		if( offset > offsetThreshold ) {
			// scroll to prev/next image

			offset = window.innerWidth*direction;

			// make sure prev/next image exists
			if( direction > 0 ) {
				// prev
				if( ! document.getElementById('navigate-prev') ) offset = 0;
			} else {
				// next
				if( ! document.getElementById('navigate-next') ) offset = 0;
			}

		} else {
			// scroll back to current image

			offset = 0;

		}

		TouchNavigation.posX = false;
		TouchNavigation.startX = false;

		var callback = function(){

			var target = false;;

			if( TouchNavigation.offset > 10 ) {
				// prev image
				target = document.getElementById('navigate-prev');
			} else if( TouchNavigation.offset < -10 ) {
				// next image
				target = document.getElementById('navigate-next');
			} else {
				// stay at current image
				return;
			}

			if( target ) target.click();
		}

		TouchNavigation.updateImageOffset(offset, true, callback);

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