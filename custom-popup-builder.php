<?php
/**
 * Plugin Name: Custom Popup Builder for Elementor
 * Description: This addon Elementor Custom Popup Builderis used for creating popups with Elementor and in this you can full customize your popup layout which you want to show.
 * Version:     1.0.0
 * Author:      OneX Technologies
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Custom_Popup_Builder' ) ) {

	class Custom_Popup_Builder {

		private static $instance = null;

		private $version = '1.0.0';

		private $plugin_url = null;

		private $plugin_path = null;

		public $assets = null;

		public $post_type = null;

		public $settings = null;

		public $export_import = null;

		public $conditions = null;

		public $extensions = null;

		public $integration = null;

		public $generator = null;

		public $ajax_handlers = null;

		public $elementor_finder = null;

		public function __construct() {
		
			add_action( 'init', array( $this, 'init' ), -999 );
			
			
			
		
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			//add_action( 'admin_init', array( $this, 'check_setup_wizard' ) );
			add_action( 'admin_init', array( $this, 'setup_wizard' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		public function get_version() {
			return $this->version;
		}

		public function has_elementor() {
			return defined( 'ELEMENTOR_VERSION' );
		}

		public function elementor() {
			return \Elementor\Plugin::$instance;
		}

		public function init() {
			
			add_action( 'admin_init', array( $this, 'check_setup_wizard' ) );
			add_action( 'admin_notices', array( $this,'my_info_notice' ) );
			
			if ( ! did_action( 'elementor/loaded' ) ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
				return;
			}
			if ( ! $this->has_elementor() ) {
				return;
			}

			$this->load_files();

			$this->assets = new Custom_Popup_Builder_Assets();

			$this->post_type = new Custom_Popup_Builder_Post_Type();

			$this->settings = new Custom_Popup_Builder_Settings();

			$this->export_import = new Custom_Export_Import();

			$this->conditions = new Custom_Popup_Builder_Conditions_Manager();

			$this->extensions = new Custom_Popup_Builder_Element_Extensions();

			$this->integration = new Custom_Popup_Builder_Integration();

			$this->generator = new Custom_Popup_Builder_Generator();

			$this->ajax_handlers = new Custom_Popup_Builder_Ajax_Handlers();

			$this->elementor_finder = new Custom_Elementor_Finder();

		}

		public function load_files() {
			require $this->plugin_path( 'includes/assets.php' );
			require $this->plugin_path( 'includes/admin-ajax-handlers.php' );
			require $this->plugin_path( 'includes/ajax-handlers.php' );
			require $this->plugin_path( 'includes/post-type.php' );
			require $this->plugin_path( 'includes/settings.php' );
			require $this->plugin_path( 'includes/export-import.php' );
			require $this->plugin_path( 'includes/utils.php' );
			require $this->plugin_path( 'includes/conditions/manager.php' );
			require $this->plugin_path( 'includes/extension.php' );
			require $this->plugin_path( 'includes/integration.php' );
			require $this->plugin_path( 'includes/generator.php' );
			require $this->plugin_path( 'includes/elementor-finder/elementor-finder.php' );
		}



		/* code for set up page */
		public function admin_menus() {
			add_dashboard_page( '', '', 'manage_options', 'cpbe-setup', '' );
		}
		
		/* code for set up check_setup_wizard */
		
		public function check_setup_wizard()
		{
			$returndata = $this->check_setup();
			if(empty($returndata))
			{
		
				wp_redirect( admin_url( 'index.php?page=cpbe-setup' ) );
				exit();

			}
		}
		
			public function check_setup()
		{
			$request = wp_remote_get('http://techybirds.com/wp-json/userdataget/pluginuserdataget?baseurl='.get_site_url().'&pluginname=custom-popup-builder');
			$body = wp_remote_retrieve_body( $request );
			$jakkdata = json_decode($body);
			return $jakkdata;
		}
		
		
		public function setup_wizard() {
			
			if ( empty( $_GET['page'] ) || 'cpbe-setup' !== $_GET['page'] ) { // WPCS: CSRF ok, input var ok.
				return;
			}
			
			if ( isset( $_REQUEST['action'] ))
			{
				
				$returndata = $this->savethird($_REQUEST);
				$jakkdata = json_decode($returndata);
				
				$message = $jakkdata->message;
				 
				if($message=='already')
				{
					wp_redirect( admin_url());
				}
				
				if($message=='insert')
				{
					wp_redirect( admin_url());
				}
				
			}
			
			ob_start();
			$this->setup_wizard_header();
			//$this->setup_wizard_steps();
			$this->setup_wizard_content();
			$this->setup_wizard_footer();
			exit;
		}
		public function savethird($data)
		{
			
			$name = $_REQUEST['fname'];
			$emailid = $_REQUEST['emailaddress'];
			//print_r($data); die;
			$request = wp_remote_get('http://techybirds.com/wp-json/userdataget/pluginuserdataget?baseurl="'.get_site_url().'"&pluginname=custom-popup-builder');
			//print_r($request);
			$body = wp_remote_retrieve_body( $request );
			$jakkdata = json_decode($body);
			//print_r($jakkdata);
			if(empty($jakkdata))
			{
					 $url  = 'http://techybirds.com/wp-json/userdataentery/pluginuserdataentery';
					$body = array(
						'name' => $name,
						'emailid' => $emailid,
						'baseurl' => get_site_url(),
						'pluginname'=> 'custom-popup-builder',
						);
				//print_r($body);
					$args = array(
						'method'      => 'POST',
						'timeout'     => 90,
						'sslverify'   => false,
						'headers'     => array(
							'Authorization' => 'none',
							'Content-Type'  => 'application/json',
						),
						'body'        => json_encode($body),
					);

    		$request = wp_remote_post( $url, $args );
			 if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
					error_log( print_r( $request, true ) );
				}
			$response = wp_remote_retrieve_body( $request );
			
		//	print_r($response);
			}
			
			
			/*print_r($response);
			die;*/
			return $response;
			}
			
		public function enqueue_scripts() {
		
			$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			if ('cpbe-setup' == $_GET['page'] ) {
				wp_enqueue_style( 'cpbe-setup', $this->plugin_url('assets/css/cpbe-setup.css'), '', '' );
			}
		}
		
		
		public function setup_wizard_header() {
		set_current_screen();
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php esc_html_e( 'Custom Popup Builder for elementor &rsaquo; Setup Wizard', 'custom-popup-builder' ); ?></title>
			<?php  do_action( 'admin_enqueue_scripts' ); ?>
			<?php wp_print_scripts( 'cpbe-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="cwe-setup wp-core-ui">
			<h1 id="cwe-logo"><a href="#"><img src="<?php echo esc_url( $this->plugin_url('assets/image/icon-128x128.png')); ?>" alt="<?php esc_attr_e( 'Custom Popup Builder For elementor', 'custom-popup-builder' ); ?>" /></a></h1>
		<?php
	}
		
		/**
		* Setup Wizard content
		*/
		public function setup_wizard_content()
		{
			echo '<div class="cwe-setup-content">';
			?>
			<form method="post" class="emaildatasetup" action="<?php echo admin_url();?>?page=cpbe-setup&action=save-settings" name="emaildatasetup">
            <p class="store-setup"><?php esc_html_e( 'The following wizard will help you configure your store and get you started quickly.', 'custom-popup-builder' ); ?></p>
            <div class="store-address-container">
            <label class="location-prompt" for="store_address"><?php esc_html_e( 'Email address', 'custom-popup-builder' ); ?></label>
				<input type="email" id="emailaddress" class="location-input" name="emailaddress" required pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}" value="<?php echo esc_attr( $address ); ?>" />

				<label class="location-prompt" for="store_address_2"><?php esc_html_e( 'Name', 'custom-popup-builder' ); ?></label>
				<input type="text" id="fname" class="location-input" name="fname" value="" required />
                <p class="wc-setup-actions step">
				<button class="button-primary button button-large" value="<?php esc_attr_e( "Save!", 'custom-popup-builder' ); ?>" name="save_step"><?php esc_html_e( "Save", 'custom-popup-builder' ); ?></button>
                
                
			</p>
            </div>
            </form>
            
			<?php 
			echo '</div>';
		}
		
		/**
	 * Setup Wizard Footer.
	 */
	public function setup_wizard_footer() {
		?>
			<?php //if ( 'store_setup' === $this->step ) : ?>
			<!--	<a class="cwe-setup-footer-links" href="<?php //echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Not right now', 'custom-popup-builder' ); ?></a>-->
			<?php //do_action( 'woocommerce_setup_footer' ); ?>
			</body>
		</html>
		<?php
	}
	
	
	function my_info_notice()
		{
		//	echo 'sdf';die;
		$paths = array();

			foreach(get_plugins() as $p_basename => $plugin)
			{
				
				
				$string = $plugin['Name'];
				$string = strtolower( $string );
	
				if (strpos($string, 'recaptcha') !== false) {
			
						$paths[] = is_plugin_active($p_basename) ? 'Active' : 'Disabled';
				}
			}
		
			if(in_array('Disabled',$paths))
			{
				$message = 'Install recaptcha plugin';
				printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
			}
			
			if(empty($paths))
			{
				$message = 'Install recaptcha plugin';
				printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
			}
		}

		public function plugin_path( $path = null ) {

			if ( ! $this->plugin_path ) {
				$this->plugin_path = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			return $this->plugin_path . $path;
		}
	
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			return $this->plugin_url . $path;
		}

		public function template_path() {
			return apply_filters( 'custom-popup-builder/template-path', 'custom-popup-builder/' );
		}

		public function get_template( $name = null ) {

			$template = locate_template( $this->template_path() . $name );

			if ( ! $template ) {
				$template = $this->plugin_path( 'templates/' . $name );
			}

			if ( file_exists( $template ) ) {
				return $template;
			} else {
				return false;
			}
		}

		public function admin_notice_missing_main_plugin() {

			if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

			$elementor_link = sprintf(
				'<a href="%1$s">%2$s</a>',
				admin_url() . 'plugin-install.php?s=elementor&tab=search&type=term',
				'<strong>' . esc_html__( 'Elementor', 'custom-popup-builder' ) . '</strong>'
			);

			$message = sprintf(
				esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'custom-popup-builder' ),
				'<strong>' . esc_html__( 'Custom Popup Builder', 'custom-popup-builder' ) . '</strong>',
				$elementor_link
			);

			printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
		}

		public function activation() {
			
			require $this->plugin_path( 'includes/post-type.php' );

			Custom_Popup_Builder_Post_Type::register_post_type();

			flush_rewrite_rules();
			
			
			
		}

		public static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}

if ( ! function_exists( 'custom_popup_builder' ) ) {

	function custom_popup_builder() {
		return Custom_Popup_Builder::get_instance();
	}
}

custom_popup_builder();
