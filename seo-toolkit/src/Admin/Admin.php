<?php
/**
 * @package Toolkit\Admin
 */

namespace Toolkit\Admin;

use Toolkit\Loader;
use Toolkit\Admin\Request;
use Toolkit\Admin\Settings\About;
use Toolkit\Admin\Settings\General;
use Toolkit\Admin\Settings\Organization;
use Toolkit\Admin\Settings\SocialMedia;
use Toolkit\Admin\Settings\Sitemaps;
use Toolkit\Admin\Settings\Editor;

/**
 * Admin class
 *
 * @since 1.0.0
 */
class Admin
{
    /**
     * @since 1.0.0
     * @var string
     */
    private $plugin_file = null;

    /**
     * @since 1.0.0
     * @var string
     */
    private $plugin_slug = null;

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Admin
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct( Loader $loader )
    {
        $this->plugin_file = $loader->getFile();
        $this->plugin_slug = $loader->getSlug();

        /* Setting for super admins and administrators */
        if ( current_user_can( 'manage_options' ) ) {
            add_action( 'admin_menu', [ $this, 'menu'   ] );
        }

        /* Setting for users with other roles and capabilities */
        if ( current_user_can( 'publish_posts' ) ) {
            add_action( 'admin_menu', [ $this, 'posts' ] );
        }

        if ( current_user_can( 'manage_categories' ) ) {
            Taxonomies::newInstance();
        }

        /* Register the admin scripts */
        add_action( 'admin_menu', [ $this, 'register' ] );

        /* Plugins administration functionalities. */
        if ( current_user_can( 'activate_plugins' ) ) {
            add_action( 'admin_init', [ $this, 'action' ] );
        }

        /* Notices */
        add_action( 'admin_notices', [ $this, 'notices' ] );

        /* Actions */
        Request::newInstance();
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Admin
     */
    public static function newInstance( Loader $loader )
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Admin( $loader );
        }

        return self::$instance;
    }

    /**
     * Prints admin screen notices.
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function notices()
    {
        $permalink_structure = get_option( 'permalink_structure' );

        if ( !empty( $permalink_structure ) ) {
            return;
        }

        $settings_url = sprintf( '<a href="%s">%s</a>',
            esc_url( admin_url( 'options-permalink.php' ) ),
            esc_html__( 'Permalink Settings', 'seo-toolkit' )
        );

        $message = esc_html__( 'Choose an SEO-friendly permalink structure.', 'seo-toolkit' ); ?>

        <div class="notice notice-warning">
            <p><?php printf( '%s %s', $message, $settings_url ); ?></p>
        </div>

        <?php
    }

    /**
     * Adds Settings link to plugins area.
     *
     * @since 1.0.0
     */
    public function action()
    {
        add_filter( 'plugin_action_links', function( $actions, $plugin_file, $plugin_data, $context ) {

            if ( $this->plugin_file !== $plugin_file ) {
                return $actions;
            }

            $new_actions = [];

            $settings = add_query_arg( [
                'page' => sanitize_key( sprintf( '%s-settings', $this->plugin_slug ) )
            ], network_admin_url( 'admin.php' ) );

            $new_actions[ 'settings' ] = sprintf( '<a href="%s">%s</a>', esc_url( $settings ), esc_html__( 'Settings', 'seo-toolkit' ) );

            return array_merge( $actions, $new_actions );
        }, 10, 4 );
    }

    /**
     * Adds the admin menus.
     *
     * @since 1.0.0
     */
    public function menu()
    {
        global $submenu;

        /* General Settings page */
        $settings_page = General::newInstance();

        $settings_slug = sanitize_key( sprintf( '%s-settings', $this->plugin_slug ) );

        $settings = add_menu_page(
            esc_html__( 'General Settings', 'seo-toolkit' ),
            esc_html__( 'SEO Toolkit', 'seo-toolkit' ),
            'manage_options',
            $settings_slug,
            [ $settings_page, 'render' ],
            'dashicons-admin-site',
            '99.074074'
        );

        add_action( "load-$settings", [ $settings_page, 'metaboxes' ] );
        add_action( "load-$settings", [ $settings_page, 'enqueue' ] );
        add_action( "load-$settings", [ $settings_page, 'scripts' ] );
        add_action( "load-$settings", [ $settings_page, 'screen' ] );
        add_action( "admin_footer-$settings", [ $settings_page, 'footer' ] );

        /* Social Media page */
        $social_page = SocialMedia::newInstance();

        $social_slug = sanitize_key( sprintf( '%s-social-media', $this->plugin_slug ) );

        $social = add_submenu_page(
            $settings_slug,
            esc_html__( 'Social Media Settings', 'seo-toolkit' ),
            esc_html__( 'Social Media', 'seo-toolkit' ),
            'manage_options',
            $social_slug,
            [ $social_page, 'render' ]
        );

        add_action( "load-$social", [ $social_page, 'metaboxes' ] );
        add_action( "load-$social", [ $social_page, 'enqueue' ] );
        add_action( "load-$social", [ $social_page, 'scripts' ] );
        add_action( "load-$social", [ $social_page, 'screen' ] );
        add_action( "admin_footer-$social", [ $social_page, 'footer' ] );

        /* Sitemaps page */
        $sitemaps_page = Sitemaps::newInstance();

        $sitemaps_slug = sanitize_key( sprintf( '%s-sitemaps', $this->plugin_slug ) );

        $sitemaps = add_submenu_page(
            $settings_slug,
            esc_html__( 'XML Sitemaps Settings', 'seo-toolkit' ),
            esc_html__( 'XML Sitemaps', 'seo-toolkit' ),
            'manage_options',
            $sitemaps_slug,
            [ $sitemaps_page, 'render' ]
        );

        add_action( "load-$sitemaps", [ $sitemaps_page, 'metaboxes' ] );
        add_action( "load-$sitemaps", [ $sitemaps_page, 'enqueue' ] );
        add_action( "load-$sitemaps", [ $sitemaps_page, 'scripts' ] );
        add_action( "load-$sitemaps", [ $sitemaps_page, 'screen' ] );
        add_action( "admin_footer-$sitemaps", [ $sitemaps_page, 'footer' ] );

        /* Editor page */
        $editor_page = Editor::newInstance();

        $editor_slug = sanitize_key( sprintf( '%s-editor', $this->plugin_slug ) );

        $editor = add_submenu_page(
            $settings_slug,
            esc_html__( 'Editor', 'seo-toolkit' ),
            esc_html__( 'Editor', 'seo-toolkit' ),
            'manage_options',
            $editor_slug,
            [ $editor_page, 'render' ]
        );

        add_action( "load-$editor", [ $editor_page, 'metaboxes' ] );
        add_action( "load-$editor", [ $editor_page, 'enqueue' ] );
        add_action( "load-$editor", [ $editor_page, 'screen' ] );
        add_action( "admin_footer-$editor", [ $editor_page, 'footer' ] );

        /* Changes the string of the submenu */
        $submenu[ $settings_slug ][0][0] = esc_html_x( 'General', 'settings screen', 'seo-toolkit' );
    }

    /**
     * Load the post.
     *
     * @since 1.0.0
     */
    public function posts()
    {
        /* Posts */
        $posts = \Toolkit\Admin\Posts::newInstance();

        add_action( "admin_head-post.php",     [ $posts, 'enqueue' ] );
        add_action( "admin_head-post-new.php", [ $posts, 'enqueue' ] );

        /* Social Media */
        $profiles = [
            'facebook',
            'twitter'
        ];

        foreach( $profiles as $profile ) {

            $enabled = (bool) get_option( "seo_toolkit_{$profile}_enabled", true );

            if ( $enabled ) {
                call_user_func( [ $this, $profile ] );
            }
        }
    }

    /**
     * @since 1.0.0
     */
    public function facebook()
    {
        \Toolkit\Admin\Posts\Facebook::newInstance();
    }

    /**
     * @since 1.0.0
     */
    public function twitter()
    {
        \Toolkit\Admin\Posts\Twitter::newInstance();
    }

    /**
     * Register the admin stylesheets and scripts.
     *
     * @since 1.0.0
     */
    public function register()
    {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_register_style( 'seo-toolkit-style',
            plugins_url( "static/css/posts$suffix.css", SEO_TOOLKIT_FILE ),
            [],
            null,
            'all'
        );

        wp_register_script( 'seo-toolkit-script',
            plugins_url( "static/js/posts$suffix.js", SEO_TOOLKIT_FILE ),
            [ 'jquery-ui-tabs' ],
            null
        );

        wp_register_script( 'seo-toolkit-upload',
            plugins_url( "static/js/upload$suffix.js", SEO_TOOLKIT_FILE ),
            [ 'jquery' ],
            null,
            true
        );

        wp_register_script( 'seo-toolkit-sitemaps',
            plugins_url( "static/js/sitemaps$suffix.js", SEO_TOOLKIT_FILE ),
            [ 'jquery' ],
            null,
            true
        );
    }
}
