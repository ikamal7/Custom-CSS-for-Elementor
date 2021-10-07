<?php
/*
 * Plugin Name: Custom CSS for Elementor
 * Plugin URI: https://kamal.pw/
 * Description: This plugin helps you add custom CSS to any widget of Elementor.
 * Version: 1.0.0
 * Author: Kamal Hosen
 * Author URI: https://kamal.pw/
 * Text Domain: custom-css-el
 * Domain Path: /languages/
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Core\DynamicTags\Dynamic_CSS;
use Elementor\Plugin;

 final class Elementor_Custom_CSS{
      /**
     * Plugin Version
     *
     * @var string The plugin version.
     * @since 1.0.0
     */
    const VERSION = '1.0.0';

    /**
     * Minimum Elementor Version
     *
     * @var string Minimum Elementor version required to run the plugin.
     * @since 1.0.0
     */
    const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

    /**
     * Minimum PHP Version
     *
     * @var string Minimum PHP version required to run the plugin.
     * @since 1.0.0
     */
    const MINIMUM_PHP_VERSION = '7.0';

    /**
     * Instance
     *
     * @access private
     * @static
     * @var Elementor_Test_Extension The single instance of the class.
     * @since 1.0.0
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @access public
     * @static
     * @since 1.0.0
     *
     * @return Elementor_Test_Extension An instance of the class.
     */
    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;

    }

    /**
     * Constructor
     *
     * @access public
     * @since 1.0.0
     */
    public function __construct() {

        add_action( 'init', [$this, 'i18n'] );
        add_action( 'plugins_loaded', [$this, 'init'] );

    }

    /**
     * Load Textdomain
     *
     * Load plugin localization files.
     *
     * Fired by `init` action hook.
     *
     * @access public
     * @since 1.0.0
     */
    public function i18n() {

        load_plugin_textdomain( 'custom-css-el' );

    }

    /**
     * Initialize the plugin
     *
     * Load the plugin only after Elementor (and other plugins) are loaded.
     * Checks for basic plugin requirements, if one check fail don't continue,
     * if all check have passed load the files required to run the plugin.
     *
     * Fired by `plugins_loaded` action hook.
     *
     * @access public
     * @since 1.0.0
     */
    public function init() {

        // Check if Elementor installed and activated
        if ( !did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [$this, 'admin_notice_missing_main_plugin'] );
            return;
        }

        // Check for required Elementor version
        if ( !version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            add_action( 'admin_notices', [$this, 'admin_notice_minimum_elementor_version'] );
            return;
        }

        // Check for required PHP version
        if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [$this, 'admin_notice_minimum_php_version'] );
            return;
        }

       
        $this->add_actions();

    }

     /**
     * Admin notice
     *
     * Warning when the site doesn't have Elementor installed or activated.
     *
     * @access public
     * @since 1.0.0
     */
    public function admin_notice_missing_main_plugin() {

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'custom-css-el' ),
            '<strong>' . esc_html__( 'Custom CSS for Elementor', 'custom-css-el' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'custom-css-el' ) . '</strong>'
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required Elementor version.
     *
     * @access public
     * @since 1.0.0
     */
    public function admin_notice_minimum_elementor_version() {

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'custom-css-el' ),
            '<strong>' . esc_html__( 'Custom CSS for Elementor', 'custom-css-el' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'custom-css-el' ) . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required PHP version.
     *
     * @access public
     * @since 1.0.0
     */
    public function admin_notice_minimum_php_version() {

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'custom-css-el' ),
            '<strong>' . esc_html__( 'Custom CSS for Elementor', 'custom-css-el' ) . '</strong>',
            '<strong>' . esc_html__( 'PHP', 'custom-css-el' ) . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

    }

    protected function add_actions() {
		add_action( 'elementor/element/after_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/element/parse_css', [ $this, 'add_post_css' ], 10, 2 );
		add_action( 'elementor/css-file/post/parse', [ $this, 'add_page_settings_css' ] );

		add_filter( 'elementor_pro/editor/localize_settings', [ $this, 'localize_settings' ] );
	}

    /**
	 * @param $element    Controls_Stack
	 * @param $section_id string
	 */
	public function register_controls( Controls_Stack $element, $section_id ) {
		// Remove Custom CSS Banner (From free version)
		if ( 'section_custom_css_pro' !== $section_id ) {
			return;
		}

		$this->_custom_css_controls( $element );
	}

    /**
	 * @param Controls_Stack $controls_stack
	 */
	public function _custom_css_controls( $controls_stack ) {
		$old_section = Plugin::instance()->controls_manager->get_control_from_stack( $controls_stack->get_unique_name(), 'section_custom_css_pro' );

		Plugin::instance()->controls_manager->remove_control_from_stack( $controls_stack->get_unique_name(), [ 'section_custom_css_pro', '_custom_css_el' ] );

		$controls_stack->start_controls_section(
			'_custom_css_el',
			[
				'label' => __( 'Custom CSS EL', 'custom-css-el' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$controls_stack->add_control(
			'custom_css_title_el',
			[
				'raw' => __( 'Add your own custom CSS here', 'custom-css-el' ),
				'type' => Controls_Manager::RAW_HTML,
			]
		);

		$controls_stack->add_control(
			'custom_css_el',
			[
				'type' => Controls_Manager::CODE,
				'label' => __( 'Custom CSS', 'custom-css-el' ),
				'language' => 'css',
				'render_type' => 'ui',
				'show_label' => false,
				'separator' => 'none',
			]
		);

		$controls_stack->add_control(
			'custom_css_description_el',
			[
				'raw' => __( 'Use "selector" to target wrapper element. Examples:<br>selector {color: red;} // For main element<br>selector .child-element {margin: 10px;} // For child element<br>.my-class {text-align: center;} // Or use any custom selector', 'custom-css-el' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
			]
		);

		$controls_stack->end_controls_section();
	}

    /**
	 * @param $post_css Post
	 * @param $element  Element_Base
	 */
	public function add_post_css( $post_css, $element ) {
		if ( $post_css instanceof Dynamic_CSS ) {
			return;
		}

		$element_settings = $element->get_settings();

		if ( empty( $element_settings['custom_css_el'] ) ) {
			return;
		}

		$css = trim( $element_settings['custom_css_el'] );

		if ( empty( $css ) ) {
			return;
		}
		$css = str_replace( 'selector', $post_css->get_element_unique_selector( $element ), $css );

		// Add a css comment
		$css = sprintf( '/* Start custom CSS for %s, class: %s */', $element->get_name(), $element->get_unique_selector() ) . $css . '/* End custom CSS */';

		$post_css->get_stylesheet()->add_raw_css( $css );
	}

    /**
	 * @param $post_css Post
	 */
	public function add_page_settings_css( $post_css ) {

		$document = Plugin::instance()->documents->get( $post_css->get_post_id() );
		$custom_css = $document->get_settings( 'custom_css' );

		$custom_css = trim( $custom_css );

		if ( empty( $custom_css ) ) {
			return;
		}

		$custom_css = str_replace( 'selector', $document->get_css_wrapper_selector(), $custom_css );

		// Add a css comment
		$custom_css = '/* Start custom CSS */' . $custom_css . '/* End custom CSS */';

		$post_css->get_stylesheet()->add_raw_css( $custom_css );
	}

 }

 Elementor_Custom_CSS::instance();
