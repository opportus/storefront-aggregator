<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Comment template.
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
$image    = wp_get_attachment_image_src( get_post_thumbnail_id( $item->comment_post_ID ), 'thumbnail' ); ?>

<li class="storefront-aggregator__item">
	<a class="storefront-aggregator__item__link" href="<?php echo esc_url( $url ); ?>">

		<?php if ( isset( $image[0] ) ): ?>
			<img class="storefront-aggregator__item__image" src="<?php echo esc_url( $image[0] ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
		<?php endif; ?>

		<h4 class="storefront-aggregator__item__title"><?php echo  esc_html( $title ); ?></h4>
		<p class="storefront-aggregator__item__date"><time datetime="<?php echo esc_attr( $date_iso ); ?>"><?php echo esc_html( $date ); ?></time></p>
		<p class="storefront-aggregator__item__author"><?php echo esc_html( $author ); ?></p>
		<cite class="storefront-aggregator__item__content"><?php echo esc_html( $content ); ?></cite>

	</a>
</li>
