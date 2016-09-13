<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Storefront Aggregator Customizer Section class.
 *
 * @version 0.2
 * @author  Clément Cazaud <opportus@gmail.com>
 */

final class Storefront_Aggregator_Customizer_Section extends WP_Customize_Section {
	public function render() {
		?>
		<li id="accordion-section-<?php esc_attr_e( $this->id ); ?>" class="accordion-section control-section control-section-<?php esc_attr_e( $this->type ); ?>">
			<h3 class="accordion-section-title" tabindex="0">
				<?php esc_html_e( $this->title ); ?>
				<span class="screen-reader-text"><?php _e( 'Press return or enter to open this section' ); ?></span>
				<?php if ( $this->description ) : ?>
					<br />
					<span class="description customize-section-description">
						<small><?php echo $this->description; ?></small>
					</span>
				<?php endif ?>
			</h3>
			<ul class="accordion-section-content">
				<li class="customize-section-description-container">
					<div class="customize-section-title">
						<button class="customize-section-back" tabindex="-1">
							<span class="screen-reader-text"><?php _e( 'Back' ); ?></span>
						</button>
						<h3>
							<span class="customize-action">
								<?php echo sprintf( __( 'Customizing &#9656; %s' ), esc_html( $this->manager->get_panel( $this->panel )->title ) ); ?>
							</span>
							<?php esc_html_e( $this->title ); ?>
						</h3>
					</div>
				</li>
			</ul>
		</li>
		<?php
	}
}
