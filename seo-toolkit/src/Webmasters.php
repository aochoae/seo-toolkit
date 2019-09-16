<?php
/**
 * @package Toolkit
 */

namespace Toolkit;

/**
 * Webmasters class
 *
 * @since 1.0.0
 */
class Webmasters
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Webmasters
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'seo_toolkit_metadata', [ $this, 'webmasters' ], 10, 1 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Webmasters
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Webmasters;
        }

        return self::$instance;
    }

    /**
     * Filter meta tags to verify the owner's website in search engines.
     *
     * @since 1.0.0
     */
    public function webmasters( $metatags )
    {
        $settings = get_option( 'seo_toolkit_webmasters', [] );

        $Webmasters = [];

        foreach( $settings as $meta => $code ) {
            $Webmasters[ $meta ] = $code;
        }

        return $metatags + $Webmasters;
    }
}
