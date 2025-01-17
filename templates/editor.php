<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$popup_id = get_the_ID();

$uniq_popup_id = 'custom-popup-builder-' . $popup_id;

$meta_settings = get_post_meta( $popup_id, '_elementor_page_settings', true );

$popup_settings_main = wp_parse_args( $meta_settings, custom_popup_builder()->generator->popup_default_settings );

$close_button_html = '';

$use_close_button = isset( $popup_settings_main['use_close_button'] ) ? filter_var( $popup_settings_main['use_close_button'], FILTER_VALIDATE_BOOLEAN ) : true;

if ( isset( $popup_settings_main['close_button_icon'] ) ) {
	$close_button_icon = $popup_settings_main['close_button_icon'];
	$close_button_html = sprintf(
		'<div class="custom-popup-builder__close-button %s"><i class="%s"></i></div>',
		! $use_close_button ? 'hidden' : '',
		$close_button_icon
	);
}

$use_overlay = isset( $popup_settings_main['use_overlay'] ) ? filter_var( $popup_settings_main['use_overlay'], FILTER_VALIDATE_BOOLEAN ) : true;

$overlay_html = sprintf(
	'<div class="custom-popup-builder__overlay %s"></div>',
	! $use_overlay ? 'hidden' : ''
);

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<?php if ( ! current_theme_supports( 'title-tag' ) ) : ?>
		<title><?php echo wp_get_document_title(); ?></title>
		<?php endif; ?>
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
	<div class="custom-popup-builder-edit-area">
		<div id="<?php echo $uniq_popup_id; ?>" class="custom-popup-builder custom-popup-builder--edit-mode">
			<div class="custom-popup-builder__inner">
				<?php echo $overlay_html; ?>
				<div class="custom-popup-builder__container">
					<?php echo $close_button_html; ?>
					<div class="custom-popup-builder__container-inner">
					<div class="custom-popup-builder__container-overlay"></div><?php

					do_action( 'custom-popup-builder/blank-page/before-content' );

					while ( have_posts() ) :
						the_post();
						the_content();
					endwhile;

					do_action( 'custom-popup-builder/blank-page/after-content' );

					wp_footer();
					?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="secondary"></div>
	</body>
</html>
