(function(){


function init() {
	Ajax.init();
	HideCursor.init();
	KeyboardNavigation.init();
	Preload.init();
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
	startX: false,
	offset: 0,

	init: function(){
		if( ! document.body.classList.contains('template-image') ) return;

		document.addEventListener( 'touchstart', TouchNavigation.navigateStart, false );
		document.addEventListener( 'touchend', TouchNavigation.navigateEnd, false );
		document.addEventListener( 'touchcancel', TouchNavigation.navigateCancel, false );
		document.addEventListener( 'touchmove', TouchNavigation.navigateMove, false );
		document.addEventListener( 'touchmove', function(e){e.preventDefault();}, false ); // fix for Edge
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


var Preload = {

	preloaded: [],
	timeouts: [],
	requests: [],

	init: function(){

		if( ! document.body.classList.contains('template-image') ) return;

		var next = document.getElementById('navigate-next');
		if( next ) {
			Preload.timeouts.push(setTimeout(function(){
				var imageSlug = next.dataset.gallerySlug+'/'+next.dataset.nextImageSlug
				Preload.load(imageSlug);
			}, 50));
		}

		var prev = document.getElementById('navigate-prev');
		if( prev ) {
			Preload.timeouts.push(setTimeout(function(){
				imageSlug = prev.dataset.gallerySlug+'/'+prev.dataset.prevImageSlug;
				Preload.load(imageSlug);
			}, 50));
		}

	},

	cancel: function(){

		for( var timeout of Preload.timeouts ) {
			clearTimeout(timeout);
		}
		Preload.timeouts = [];
		
		for( var request of Preload.requests ) {
			request.abort();
		}
		Preload.requests = [];

	},

	load: function(imageSlug) {

		if( Preload.preloaded.includes(imageSlug) ) return;

		Preload.preloaded.push(imageSlug);

		requestUrl = GALLERY.apiUrl+imageSlug+'/?imageonly=true';

		var request = new XMLHttpRequest();
		request.open( 'GET', requestUrl );

		request.onreadystatechange = function(){

			if( request.readyState !== XMLHttpRequest.DONE ) return;

			if( request.status !== 200 ) return;

			var response = request.response;

			if( ! response ) return;

			response = JSON.parse(response);

			var wrapper = document.createElement('div');
			wrapper.classList.add('preload-wrapper');
			wrapper.innerHTML = response.content;
			document.body.querySelector('main').appendChild(wrapper);

		}
		request.send();

		Preload.requests.push(request);

	},

};


})();