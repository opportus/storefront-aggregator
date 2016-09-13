<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Aggregator template.
 *
 * @version 0.2
 * @author  Clément Cazaud <opportus@gmail.com>
 */

$title   = apply_filters( 'the_title',   $aggregator->post_title );
$content = apply_filters( 'the_content', $aggregator->post_content );

do_action( 'storefront_aggregator_before' ); ?>

<section id="storefront-aggregator-<?php echo esc_attr( strval( $aggregator->ID ) ) ?>" class="storefront-aggregator" aria-label="<?php echo esc_attr__( 'Aggregator', 'storefront-aggregator' ); ?>">
	<div class="col-full">

		<?php do_action( 'storefront_aggregator_top' ); ?>

		<?php if ( $title || $content ): ?>
			<header class="storefront-aggregator__header">

				<?php if ( $title ): ?>
					<h3 class="storefront-aggregator__title alpha"><?php echo esc_html( $title ); ?></h3>
				<?php endif; ?>

				<?php if ( $content ): ?>
					<div class="storefront-aggregator__content"><?php echo $content; ?></div>
				<?php endif; ?>

				<?php do_action( 'storefront_aggregator_header' ); ?>

			</header>
		<?php endif; ?>

		<div class="flexslider">
			<ul class="storefront-aggregator__items">

				<?php foreach ( $aggregator->items as $item_count => $item ) {

					/**
					 * Hooked `storefront_aggregator_items_template()` - 10
					 */
					do_action( 'storefront_aggregator_items_template', $item, $aggregator, $item_count );
				} ?>

			</ul>
		</div>

		<?php do_action( 'storefront_aggregator_bottom' ); ?>
 
	</div>
</section>

<?php do_action( 'storefront_aggregator_after' ); ?>
