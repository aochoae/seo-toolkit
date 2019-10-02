<?php
/**
 * @package Toolkit
 */

namespace Toolkit;

/**
 * SocialMedia class
 *
 * @since 1.0.0
 */
class SocialMedia
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var SocialMedia
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
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
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return SocialMedia
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new SocialMedia();
        }

        return self::$instance;
    }

    /**
     * Implements the Facebook Open Graph.
     *
     * @since 1.0.0
     */
    public function facebook()
    {
        \Toolkit\SocialMedia\Facebook::newInstance();
    }

    /**
     * Implements the Twitter Cards.
     *
     * @since 1.0.0
     */
    public function twitter()
    {
        \Toolkit\SocialMedia\Twitter::newInstance();
    }
}
