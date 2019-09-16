<?php
/**
 * @package Toolkit
 */

namespace Toolkit;

/**
 * Metadata class
 *
 * @since 1.0.0
 */
class Metadata
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Metadata
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_action( 'seo_toolkit_head', [ $this, 'name'     ], 2, 1 );
        add_action( 'seo_toolkit_head', [ $this, 'property' ], 2, 1 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Metadata
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Metadata;
        }

        return self::$instance;
    }

    /**
     * Implements the meta tags for the HTML document.
     *
     * @since 1.0.0
     */
    public function name( $context )
    {
        $metatags = apply_filters( 'seo_toolkit_metadata', [], $context );

        $metatags = array_filter( $metatags, function( $v, $k ) {
            return ! empty( $v );
        }, ARRAY_FILTER_USE_BOTH );

        if ( empty( $metatags ) ) {
            return;
        }

        $print = function( $metatags ) use ( &$print ) {

            foreach( $metatags as $property => $content ) {

                if ( is_array( $content ) ) {
                    $print( $content );
                } else {
                    printf( '<meta name="%s" content="%s" />' . PHP_EOL, esc_attr( $property ), esc_attr( $content ) );
                }
            }
        };

        $print( $metatags );
    }

    /**
     * Implements the meta tags for the HTML document.
     *
     * @since 1.0.0
     */
    public function property( $context )
    {
        $metatags = apply_filters( 'seo_toolkit_metadata_property', [], $context );

        $metatags = array_filter( $metatags, function( $v, $k ) {
            return ! empty( $v );
        }, ARRAY_FILTER_USE_BOTH );

        if ( empty( $metatags ) ) {
            return;
        }

        $print = function( $metatags ) use ( &$print ) {

            foreach( $metatags as $property => $content ) {

                if ( is_array( $content ) ) {
                    $print( $content );
                } else {
                    printf( '<meta property="%s" content="%s" />' . PHP_EOL, esc_attr( $property ), esc_attr( $content ) );
                }
            }
        };

        $print( $metatags );
    }
}
