// This file is part of mh.gallery
// Copyright (C) 2023-2026 maxhaesslein
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

	setTimeout( function(){
		document.body.classList.add('transition');
	}, 50 );
};
window.addEventListener( 'load', init );


function isTouchDevice(){
	return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
}


const Lightmode = {

	init: function(){

		const toggle = document.getElementById('lightmode-toggle');

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


const Ajax = {

	currentImageIndex: false,
	currentImageIndexMax: false,
	images: false,
	request: false,

	init: function(){

		if( ! document.body.classList.contains('template-image') ) return;

		if( Ajax.currentImageIndex === false ) {
			Ajax.currentImageIndex = parseInt(GALLERY_IMAGE_INDEX, 10);
			Ajax.images = GALLERY_IMAGES;
			Ajax.currentImageIndexMax = Ajax.images.length;
		}

		const next = document.getElementById('navigate-next');
		if( next ) {
			next.addEventListener('click', function(e){
				if( Ajax.navigate(this) ) e.preventDefault();
			});
		}

		const prev = document.getElementById('navigate-prev');
		if( prev ) {
			prev.addEventListener('click', function(e){
				if( Ajax.navigate(this) ) e.preventDefault();
			});
		}

		window.addEventListener( "popstate", Ajax.urlNavigation );
		
	},

	urlNavigation: function( e ) {

		const state = e.state;

		if( ! state ) return;

		const url = state.url;

		if( ! url ) return;

		window.location.href = url;

	},

	navigate: function( el ) {

		if( Ajax.request ) {
			Ajax.request.abort();
			Ajax.request = false;
		}

		if( el.id == 'navigate-next' ) {
			Ajax.currentImageIndex++;
		} else if( el.id == 'navigate-prev' ) {
			Ajax.currentImageIndex--;
		} else {
			return false;
		}

		HideCursor.showCursor();

		if( Ajax.currentImageIndex < 0 ) {
			Ajax.currentImageIndex = 0;
		} else if( Ajax.currentImageIndex >= Ajax.currentImageIndexMax ) {
			Ajax.currentImageIndex = Ajax.currentImageIndexMax-1;
		}

		const loadingImage = Ajax.images[Ajax.currentImageIndex];
		if( ! loadingImage ) {
			console.warn('could not find image!', Ajax.currentImageIndex); // DEBUG
			return;
		}

		Ajax.updateTitle( GALLERY.texts.loading );

		const previewImageSrc = loadingImage.preview_src;
		const container = document.getElementById('fullscreen-target').querySelector('.image-container');
		container.innerHTML = '';

		container.style.background = 'var(--thumbnail-background-color)';
		container.classList.add('loading');

		container.style.aspectRatio = parseInt(loadingImage.width, 10)+'/'+parseInt(loadingImage.height, 10);

		const preloadImg = document.createElement('img');
		preloadImg.src = previewImageSrc;
		preloadImg.width = loadingImage.width;
		preloadImg.height = loadingImage.height;
		preloadImg.loading = 'eager';
		container.appendChild(preloadImg);

		document.getElementById('fullscreen-target').querySelector('.meta-bottom .info li:first-child').innerText = loadingImage.number;


		const imagePath = loadingImage.path;
		history.pushState( {url: loadingImage.url}, false, loadingImage.url );

		Ajax.request = new XMLHttpRequest();
		Ajax.request.open( 'GET', GALLERY.apiUrl+imagePath+'/' );

		Ajax.request.onreadystatechange = function(){

			if( Ajax.request.readyState !== XMLHttpRequest.DONE ) return;
		
			const url = el.href;

			if( Ajax.request.status === 200 ) {

				let response = Ajax.request.response;

				if( ! response ) {
					console.warn( 'AJAX request failed - no response', Ajax.request ); // DEBUG
					window.location.href = url; // request the complete page
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

			} else if( Ajax.request.status === 0 ) {
				// request was aborted
				return true;
			} else {
				// something went wrong …
				console.warn( 'AJAX request failed - wrong status code', Ajax.request.status, Ajax.request ); // DEBUG
				window.location.href = url; // request the complete page
			}

		}
		Ajax.request.send();

		return true;
	},

	updateTitle: function( newTitle ) {
		document.title = newTitle;
	}

};


const HideCursor = {

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


const KeyboardNavigation = {

	eventHandlersAdded: false,

	init: function(){
		if( ! document.body.classList.contains('template-image') ) return;

		if( KeyboardNavigation.eventHandlersAdded ) return;

		document.addEventListener( 'keydown', KeyboardNavigation.navigate );

		KeyboardNavigation.eventHandlersAdded = true;

	},

	navigate: function(e){

		let target = false;

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


const FullscreenButton = {

	init: function(){
		const button = document.getElementById('action-fullscreen');

		if( ! button ) return;

		button.addEventListener( 'click', FullscreenButton.toggle );

	},

	toggle: function( e ){

		e.preventDefault();

		const target = document.getElementById('fullscreen-target');

		if( ! target ) return;

		if( ! document.fullscreenElement ) {
			document.documentElement.requestFullscreen( target );
		} else if ( document.exitFullscreen ) {
			document.exitFullscreen();
		}
	}

};


let InformationButtonState = false;
const InformationButton = {

	target: false,

	init: function(){

		const button = document.getElementById('action-information');

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


const DownloadButton = {

	init: function(){

		if( ! isTouchDevice() ) return;

		const button = document.getElementById('download-overlay');

		if( ! button ) return;

		button.addEventListener( 'click', DownloadButton.toggle );

	},

	toggle: function( e ) {

		if( ! e.target.classList.contains('button-download') ) return;

		const button = document.getElementById('download-overlay');

		if( ! button ) return;

		e.preventDefault();

		button.classList.toggle('open');

	}

};


})();