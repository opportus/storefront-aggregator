<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Post template.
 *
 * @version 0.2
 * @author  Clément Cazaud <opportus@gmail.com>
 */

$title    = apply_filters( 'the_title', $item->post_title );
$date_iso = apply_filters( 'the_date', $item->post_date );
$date     = get_the_date( '', $item->ID );
$url      = get_the_permalink( $item->ID );
$author   = get_the_author_meta( 'nickname',  apply_filters( 'the_author', $item->post_author ) );
$image    = wp_get_attachment_image_src( get_post_thumbnail_id( $item->ID ), 'thumbnail' );
$content  = wp_html_excerpt( $item->post_content, 150, '&#8230;' ); ?>

<li class="storefront-aggregator__item">
	<a class="storefront-aggregator__item__link" href="<?php echo esc_url( $url ); ?>">
		
		<?php if ( isset( $image[0] ) ): ?>
			<img class="storefront-aggregator__item__image" src="<?php echo esc_url( $image[0] ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
		<?php endif; ?>

		<h4 class="storefront-aggregator__item__title"><?php echo  esc_html( $title ); ?></h4>
		<p class="storefront-aggregator__item__date"><time datetime="<?php echo esc_attr( $date_iso ); ?>"><?php echo esc_html( $date ); ?></time></p>
		<p class="storefront-aggregator__item__author"><?php echo esc_html( $author ); ?></p>
		<p class="storefront-aggregator__item__content"><?php echo ( $content ); ?></p>

	</a>
</li>
