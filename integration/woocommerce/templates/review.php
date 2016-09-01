<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Review template.
 *
 * @version 0.1
 * @author  Clément Cazaud <opportus@gmail.com>
 */

$title    = get_the_title( $item->comment_post_ID );
$url      = get_comment_link( $item->comment_ID );
$date     = get_comment_date( '', $item->comment_ID );
$date_iso = $item->comment_date;
$content  = wp_html_excerpt( $item->comment_content, 150, '&#8230;' );
$author   = apply_filters( 'comment_author', $item->comment_author );
$image    = wp_get_attachment_image_src( get_post_thumbnail_id( $item->comment_post_ID ), 'thumbnail' );
$rating   = intval( get_comment_meta( $item->comment_ID, 'rating', true ) ); ?>

<li class="storefront-aggregator__item">
	<a class="storefront-aggregator__item__link" href="<?php echo esc_url( $url ); ?>">

		<?php if ( $image[0] ): ?>
			<img class="storefront-aggregator__item__image" src="<?php echo esc_url( $image[0] ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
		<?php endif; ?>

		<h4 class="storefront-aggregator__item__title"><?php echo  esc_html( $title ); ?></h4>
		<p class="storefront-aggregator__item__date"><time datetime="<?php echo esc_attr( $date_iso ); ?>"><?php echo esc_html( $date ); ?></time></p>
		<p class="storefront-aggregator__item__author"><?php echo esc_html( $author ); ?></p>

		<?php if ( $rating && 'no' !== get_option( 'woocommerce_enable_review_rating' ) ): ?>
			<p class="storefront-aggregator__item__rating  star-rating" title="<?php echo  esc_attr( sprintf( __( 'Rated %s out of 5', 'woocommerce' ), $rating ) ); ?>">
				<span style="width:<?php echo ( $rating / 5 ) * 100; ?>%">
					<strong class="rating"><?php echo esc_html( $rating ); ?></strong> <?php  _e( 'out of 5', 'woocommerce' ); ?>
				</span>
			</p>
		<?php endif; ?>

		<cite class="storefront-aggregator__item__content"><?php echo esc_html( $content ); ?></cite>

	</a>
</li>
