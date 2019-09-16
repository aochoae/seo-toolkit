<?php
/**
 * @package Toolkit
 */

namespace Toolkit\Extensions;

/**
 * bbPress class
 *
 * @since 1.0.0
 */
class bbPress
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var bbPress
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'seo_toolkit_contexts', [ $this, 'contexts' ], 10, 1 );

        add_action( 'do_meta_boxes', [ $this, 'metaboxes' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return bbPress
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new bbPress;
        }

        return self::$instance;
    }

    /**
     * Removes replies post type.
     *
     * @since 1.0.0
     */
    public function contexts( $contexts )
    {
        unset( $contexts[ bbp_get_reply_post_type() ] );

        return $contexts;
    }

    /**
     * Removes the meta box.
     *
     * @since 1.0.0
     */
    public function metaboxes()
    {
        remove_meta_box( 'seo-toolkit', bbp_get_reply_post_type(), 'normal' );
    }
}
