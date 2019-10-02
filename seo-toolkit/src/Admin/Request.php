<?php
/**
 * @package Toolkit\Admin
 */

namespace Toolkit\Admin;

/**
 * Request class
 *
 * @since 1.0.0
 */
class Request
{
    /**
     * Singleton instance.
     *
     * @since 1.0.0
     * @var Post
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_action( 'admin_post_robotstxt', [ $this, 'robotstxt' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Post
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Request();
        }

        return self::$instance;
    }

    /**
     * Edit the .htaccess and robots.txt files.
     *
     * @since 1.0.0
     */
    public function robotstxt()
    {
        /* Verify that a nonce is correct */
        $nonce = filter_input( INPUT_POST, '_nonce', FILTER_SANITIZE_STRING );

        if ( ! wp_verify_nonce( $nonce, 'seo-toolbox-edit' ) ) {

            $args = [
                'back_link' => true,
                'exit'      => true,
                'response' 	=> 403
            ];
            wp_die( __( 'Security error has occurred.', 'seo-toolkit' ), __( 'Error', 'seo-toolkit' ), $args );
        }

        /* URL to redirect */
        $redirect = admin_url( 'admin.php?page=seo-toolkit-editor' );

        /* robots.txt */
        $contents = filter_input( INPUT_POST, 'robotstxt', FILTER_SANITIZE_STRING );

        (new \Toolkit\Admin\Files\Robots)->write( $contents );

        $redirect = add_query_arg( 'saved', 'true', $redirect );

        /* Redirect */
        wp_safe_redirect( esc_url_raw( $redirect ) );
        exit;
    }
}
