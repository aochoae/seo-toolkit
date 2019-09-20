<?php
/**
 * @package Toolkit
 */

namespace Toolkit;

/**
 * Hook the WordPress plugin into the appropriate WordPress actions and filters.
 *
 * @since 1.0.0
 */
class Loader
{
    /**
     * Plugin version
     *
     * @since 1.0.0
     * @var string
     */
    const VERSION = '1.0.1';

    /**
     * The path to a plugin main file
     *
     * @since 1.0.0
     * @var string
     */
    private $plugin_file = '';

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Loader
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct( $plugin_file )
    {
        $this->plugin_file = $plugin_file;

        add_action( 'init', [ $this, 'loadTextdomain' ] );

        add_action( 'init', [ $this, 'extensions' ] );

        add_action( 'init', [ $this, 'features' ] );

        add_action( 'wp_head', [ $this, 'head' ], 1 );

        if ( is_admin() ) {
            add_action( 'init', [ $this, 'admin' ] );
        }

        add_action( 'plugins_loaded', [ $this, 'loaded' ] );

        register_activation_hook( SEO_TOOLKIT_FILE, 'flush_rewrite_rules' );

        register_deactivation_hook( SEO_TOOLKIT_FILE, 'flush_rewrite_rules' );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Loader
     */
    public static function newInstance( $plugin_file )
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Loader( $plugin_file );
        }

        return self::$instance;
    }

    /**
     * Perform the functionalities.
     *
     * @since 1.0.0
     */
    public function loaded()
    {
        $tools = [
            '\Toolkit\Title',
            '\Toolkit\Robots',
            '\Toolkit\Description',
            '\Toolkit\SocialMedia',
            '\Toolkit\Webmasters',
            '\Toolkit\StructuredData',
            '\Toolkit\Metadata'
        ];

        foreach( $tools as $tool ) {
            call_user_func( "$tool::newInstance" );
        }

        if ( get_option( 'seo_toolkit_sitemaps_enabled', true ) ) {
            \Toolkit\Sitemaps::newInstance();
        }
    }

    /**
     * Load translated strings for the current locale.
     *
     * @since 1.0.0
     */
    public function loadTextdomain()
    {
        load_plugin_textdomain( 'seo-toolkit' );
    }

    /**
     * Load extensions.
     *
     * @since 1.0.0
     */
    public function extensions()
    {
        if ( defined( 'WC_PLUGIN_FILE' ) ) {
            \Toolkit\Extensions\WooCommerce::newInstance();
        }

        if ( class_exists( 'bbPress' ) ) {
            \Toolkit\Extensions\bbPress::newInstance();
        }
    }

    /**
     * Add features for WordPress.
     *
     * @since 1.0.0
     */
    public function features()
    {
        /* Adds support for excerpts to pages */
        add_post_type_support( 'page', 'excerpt' );

        /* Adds contact methods */
        add_filter( 'user_contactmethods', function( $methods, $user ) {

            $new_methods = [
                'facebook'  => __( 'Facebook',  'seo-toolkit' ),
                'twitter'   => __( 'Twitter',   'seo-toolkit' ),
                'instagram' => __( 'Instagram', 'seo-toolkit' ),
                'youtube'   => __( 'YouTube',   'seo-toolkit' ),
                'linkedin'  => __( 'LinkedIn',  'seo-toolkit' ),
                'pinterest' => __( 'Pinterest', 'seo-toolkit' )
            ];

            return array_merge( $methods, $new_methods );
        }, 10, 2 );
    }

    /**
     * Add our custom head action to wp_head.
     *
     * @since 1.0.0
     */
    public function head()
    {
        add_action( 'seo_toolkit_head', function() {
            echo "<!-- SEO Toolkit - https://www.seo-toolkit.page/ -->" . PHP_EOL;
        }, 1 );

        add_action( 'seo_toolkit_head', function() {
            echo "<!-- SEO Toolkit -->" . PHP_EOL;
        }, PHP_INT_MAX );

        do_action( 'seo_toolkit_head', (new Context)->getContext() );
    }

    /**
     * Hook into actions and filters for administrative interface page.
     *
     * @since 1.0.0
     */
    public function admin()
    {
        \Toolkit\Admin\Admin::newInstance( $this );
    }

    /**
     * Retrieve the basename of the plugin.
     *
     * @since 1.0.0
     */
    public function getFile()
    {
        return $this->plugin_file;
    }

    /**
     * Retrieve the slug of the plugin.
     *
     * @since 1.0.0
     */
    public function getSlug()
    {
        return basename( $this->plugin_file, '.php' );
    }
}
