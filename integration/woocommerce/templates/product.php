<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Product template.
 *
 * @version 0.1
 * @author  Clément Cazaud <opportus@gmail.com>
 */

$title    = apply_filters( 'the_title', $item->post_title );
$content  = get_the_excerpt( $item->ID );
$url      = get_the_permalink( $item->ID );
$image    = wp_get_attachment_image_src( get_post_thumbnail_id( $item->ID ), 'thumbnail' );
$rating   = intval( wc_get_product( $item->ID )->get_average_rating() ); ?>

<li class="storefront-aggregator__item">
	<a class="storefront-aggregator__item__link" href="<?php echo esc_url( $url ); ?>">

		<?php if ( $image[0] ): ?>
			<img class="storefront-aggregator__item__image" src="<?php echo esc_url( $image[0] ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
		<?php endif; ?>

		<h4 class="storefront-aggregator__item__title"><?php echo  esc_html( $title ); ?></h4>
	
		<?php if ( $rating && 'no' !== get_option( 'woocommerce_enable_review_rating' ) ): ?>
			<p class="storefront-aggregator__item__rating  star-rating" title="<?php echo  esc_attr( sprintf( __( 'Rated %s out of 5', 'woocommerce' ), $rating ) ); ?>">
				<span style="width:<?php echo ( $rating / 5 ) * 100; ?>%">
					<strong class="rating"><?php echo esc_html( $rating ); ?></strong> <?php  _e( 'out of 5', 'woocommerce' ); ?>
				</span>
			</p>
		<?php endif; ?>

		<p class="storefront-aggregator__item__content"><?php echo esc_html( $content ); ?></p>

	</a>
</li>
