<?php
/**
 * @package Toolkit\Sitemaps
 */

namespace Toolkit\Sitemaps;

/**
 * Ping class
 *
 * @since 1.0.0
 */
class Ping
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Ping
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_action( 'wp_ajax_sitemaps-ping', [ $this, 'ping' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Ping
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Ping();
        }

        return self::$instance;
    }

    /**
     * Submits the XML Sitemap to Bing and Google.
     *
     * @since 1.0.0
     */
    public function ping()
    {
        check_ajax_referer( 'seo-toolkit-sitemaps-ping', 'security' );

        $sitemap = filter_var( $_POST[ 'sitemap' ], FILTER_VALIDATE_URL );

        $services = [
            'bing'   => 'https://www.bing.com/ping?sitemap=',
            'google' => 'https://www.google.com/ping?sitemap='
        ];

        $notification = [];

        foreach( $services as $service => $service_url ) {

            $response = wp_remote_get( $service_url . urlencode( $sitemap ) );

            $notification[ $service ] = wp_remote_retrieve_body( $response );
        }

        wp_send_json_success( [ 'sitemap' => $sitemap, $notification ] );
    }
}
