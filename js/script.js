/*jshint jquery:true */

$(document).ready(function($) {
	"use strict";


	/*jshint -W018 */
	/*-------------------------------------------------*/
	/* =  portfolio isotope
	/*-------------------------------------------------*/

	var winDow = $(window);
		// Needed variables
		var $container=$('.iso-call');
		var $filter=$('.filter');

		try{
			$container.imagesLoaded( function(){
				$container.trigger('resize');
				$container.isotope({
					filter:'*',
					layoutMode:'masonry',
					animationOptions:{
						duration:750,
						easing:'linear'
					}
				});
				setTimeout(Resize, 1500);
			});
		} catch(err) {
		}

		winDow.on('resize', function(){
			var selector = $filter.find('a.active').attr('data-filter');

			try {
				$container.isotope({ 
					filter	: selector,
					animationOptions: {
						duration: 750,
						easing	: 'linear',
						queue	: false,
					}
				});
			} catch(err) {
			}
			return false;
		});
		
		// Isotope Filter 
		$filter.find('a').on('click', function(){
			var selector = $(this).attr('data-filter');

			try {
				$container.isotope({ 
					filter	: selector,
					animationOptions: {
						duration: 750,
						easing	: 'linear',
						queue	: false,
					}
				});
			} catch(err) {

			}
			return false;
		});


	var filterItemA	= $('.filter li a');

		filterItemA.on('click', function(){
			var $this = $(this);
			if ( !$this.hasClass('active')) {
				filterItemA.removeClass('active');
				$this.addClass('active');
			}
		});

	/*-------------------------------------------------*/
	/* =  OWL carousell
	/*-------------------------------------------------*/
	try {
		var owlWrap = $('.owl-wrapper');

		if (owlWrap.length > 0) {

			if (jQuery().owlCarousel) {
				owlWrap.each(function(){

					var carousel= $(this).find('.owl-carousel'),
						dataNum = $(this).find('.owl-carousel').attr('data-num'),
						dataNum2,
						dataNum3;

					if ( dataNum == 1 ) {
						dataNum2 = 1;
						dataNum3 = 1;
					} else if ( dataNum == 2 ) {
						dataNum2 = 2;
						dataNum3 = dataNum - 1;
					} else {
						dataNum2 = dataNum - 1;
						dataNum3 = dataNum - 2;
					}

					carousel.owlCarousel({
						autoPlay: 10000,
						navigation : true,
						items : dataNum,
						itemsDesktop : [1199,dataNum2],
						itemsDesktopSmall : [979,dataNum3],
						itemsTablet : [768, dataNum3],
					});

				});
			}
		}

	} catch(err) {

	}

	$('#container').addClass('active');


	
	/*-------------------------------------------------*/
	/* =  count increment
	/*-------------------------------------------------*/
	try {
		$('.statistic-post').appear(function() {
			$('.timer').countTo({
				speed: 4000,
				refreshInterval: 60,
				formatter: function (value, options) {
					return value.toFixed(options.decimals);
				}
			});
		});
	} catch(err) {

	}

	/* ---------------------------------------------------------------------- */
	/*	Contact Form
	/* ---------------------------------------------------------------------- */

	var submitContact = $('#submit_contact'),
		message = $('#msg');

	submitContact.on('click', function(e){
		e.preventDefault();

		var $this = $(this);
		
		$.ajax({
			type: "POST",
			url: 'contact.php',
			dataType: 'json',
			cache: false,
			data: $('#contact-form').serialize(),
			success: function(data) {

				if(data.info !== 'error'){
					$this.parents('form').find('input[type=text],textarea,select').filter(':visible').val('');
					message.hide().removeClass('alert-success').removeClass('alert-danger').addClass('alert-success').html(data.msg).fadeIn('slow').delay(5000).fadeOut('slow');
				} else {
					message.hide().removeClass('alert-success').removeClass('alert-danger').addClass('alert-danger').html(data.msg).fadeIn('slow').delay(5000).fadeOut('slow');
				}
			}
		});
	});

	/*-------------------------------------------------*/
	/* =  scroll between sections
	/*-------------------------------------------------*/

	$('.navigate-section > li > a[href*=#], a.target-link[href*=#]').on('click', function(event) {
		event.preventDefault();
		var offset = 74;
		var childOffset = 0;
		if ($(this).is("[data-offset]")) {
		    childOffset = parseInt($(this).attr('data-offset'));
		}
		$('html, body').animate({
			scrollTop: $($(this).attr('href')).offset().top - offset + childOffset
		}, 500, 'linear');
	});

	/*-------------------------------------------------*/
	/* =  add active state in menu for active section
	/*-------------------------------------------------*/

	$('section').each(function() {
		$(this).waypoint( function( direction ) {
			if( direction === 'down' ) {
				var containerID = $(this).attr('id');
				/* update navigation */
				$('.navigate-section > li > a').removeClass('active');
				$('.navigate-section > li > a[href*=#'+containerID+']').addClass('active');
			}
		} , { offset: '70px' } );
		
		$(this).waypoint( function( direction ) {
			if( direction === 'up' ) {
				var containerID = $(this).attr('id');
				/* update navigation */
				$('.navigate-section > li > a').removeClass('active');
				$('.navigate-section > li > a[href*=#'+containerID+']').addClass('active');
			}
		} , { offset: function() { return -$(this).height() - 70; } });
	});

	/* ---------------------------------------------------------------------- */
	/*	Header animate after scroll
	/* ---------------------------------------------------------------------- */



	(function() {

		var docElem = document.documentElement,
			didScroll = false,
			changeHeaderOn = 130;
			document.querySelector( 'header, a.go-top' );
		function init() {
			window.addEventListener( 'scroll', function() {
				if( !didScroll ) {
					didScroll = true;
					setTimeout( scrollPage, 100 );
				}
			}, false );
		}
		
		function scrollPage() {
			var sy = scrollY();
			if ( sy >= changeHeaderOn ) {
				$( 'header' ).addClass('active');
				$( 'a.go-top' ).addClass('active');
			}
			else {
				$( 'header' ).removeClass('active');
				$( 'a.go-top' ).removeClass('active');
			}
			didScroll = false;
		}
		
		function scrollY() {
			return window.pageYOffset || docElem.scrollTop;
		}
		
		init();
		
	})();

});


function Resize() {
	$(window).trigger('resize');
}
