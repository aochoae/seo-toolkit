<?php
/**
 * @package Toolkit\Admin\Options
 */

namespace Toolkit\Admin\Settings\Options;

/**
 * Robots class
 *
 * @since 1.0.0
 */
class Robots
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Robots
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Robots
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Robots;
        }

        return self::$instance;
    }

    /**
     * Retrieve the options for robots meta tag.
     *
     * @since 1.0.0
     */
    public function getFormat( $context )
    {
        $format[ 'default' ] = [
            'index',
            'index, follow',
            'index, nofollow',
            'noindex',
            'noindex, follow',
            'noindex, nofollow',
            'nofollow'
        ];

        $format[ 'frontpage' ] = [
            'index',
            'index, follow',
            'index, nofollow'
        ];

        $format[ 'search' ] = [
            'noindex',
            'noindex, nofollow'
        ];

        $format[ 'error' ] = [
            'noindex',
            'noindex, nofollow'
        ];

        $format = apply_filters( 'seo_toolkit_robots_format', $format, $context );

        $values = isset( $format[ $context ] ) ? $format[ $context ] : $format[ 'default' ];

        return $values;
    }
}
