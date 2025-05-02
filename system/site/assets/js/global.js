// This file is part of mh.gallery
// Copyright (C) 2023-2025 maxhaesslein
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// See the file LICENSE.md for more details.

(function(){


function init() {
	Lightmode.init();
	Ajax.init();
	HideCursor.init();
	KeyboardNavigation.init();
	FullscreenButton.init();
	InformationButton.init();
	DownloadButton.init();
	Preload.init();

	setTimeout( function(){
		document.body.classList.add('transition');
	}, 50 );
};
window.addEventListener( 'load', init );


function isTouchDevice(){
	return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
}


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

		Preload.cancel();

		var imageSlug = false;
		if( el.id == 'navigate-next' ) {
			imageSlug = el.dataset.gallerySlug+'/'+el.dataset.nextImageSlug;
		} else if( el.id == 'navigate-prev' ) {
			imageSlug = el.dataset.gallerySlug+'/'+el.dataset.prevImageSlug;
		} else {
			return false;
		}

		document.getElementById('fullscreen-target').querySelector('img').style.opacity = 0;

		Ajax.updateTitle( 'loading …' );

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

	eventHandlersAdded: false,
	delay: 2000,
	timer: false,

	init: function(){

		if( ! document.body.classList.contains('template-image') ) return;

		if( isTouchDevice() ) return;

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

			if( InformationButtonState ) {
				InformationButton.close();
				return;
			}

			target = document.getElementById('navigate-overview');
			e.preventDefault();
		}

		if( ! target ) return;
		
		target.click();

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


var InformationButtonState = false;
var InformationButton = {

	target: false,

	init: function(){

		var button = document.getElementById('action-information');

		if( ! button ) return;

		InformationButton.target = document.getElementById('image-information');

		if( ! InformationButton.target ) return;

		button.addEventListener( 'click', InformationButton.toggle );

		if( InformationButtonState ) {
			InformationButton.open();
		}

	},

	toggle: function( e ) {

		e.preventDefault();

		if( InformationButton.target.open ) {
			InformationButton.close();
		} else {
			InformationButton.open();
		}

	},

	open: function() {

		InformationButtonState = true;

		InformationButton.target.show();

		InformationButton.target.querySelector('#image-information-close').addEventListener( 'click', function(e){
			InformationButton.close(this.parentNode);
		}, false );

	},

	close: function() {

		InformationButtonState = false;

		InformationButton.target.close();

	}

};


var DownloadButton = {

	init: function(){

		if( ! isTouchDevice() ) return;

		var button = document.getElementById('download-overlay');

		if( ! button ) return;

		button.addEventListener( 'click', DownloadButton.toggle );

	},

	toggle: function( e ) {

		if( ! e.target.classList.contains('button-download') ) return;

		var button = document.getElementById('download-overlay');

		if( ! button ) return;

		e.preventDefault();

		button.classList.toggle('open');

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