/**
 * FlexSlider initializer.
 */

jQuery( window ).load( function() {
	var windowWidth     = jQuery( window ).width();
	var aggregatorWidth = jQuery( '.flexslider' ).width();
 
	function refreshFlexSlider() {
		jQuery( '.flexslider' ).resize();
	}

	jQuery( '.flexslider' ).flexslider( {
		start:          refreshFlexSlider,
		selector:       '.storefront-aggregator__items > .storefront-aggregator__item',
		animation:      'slide',
		slideshow:      false,
		animationSpeed: 500,
		itemWidth:      windowWidth >= 768 ? aggregatorWidth / 4 : aggregatorWidth,
		itemMargin:     windowWidth >= 768 ? 15 : 0,
		maxItems:       windowWidth >= 768 ? 4 : 1,
		minItems:       windowWidth >= 768 ? 4 : 1,
		move:           1,
	} );
} );
