<?php
/**
 * @package Toolkit\Sitemaps
 */

namespace Toolkit\Sitemaps;

/**
 * Images class
 *
 * @since 1.0.0
 */
class Images
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Images
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'seo_toolkit_sitemap_post', [ $this, 'images'], 10, 2 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Images
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Images;
        }

        return self::$instance;
    }

    /**
     * Adds the images to the sitemap.
     *
     * @since 1.0.0
     */
    public function images( $data, $post_id )
    {
        $noimageindex = get_post_meta( $post_id, '_seo_toolkit_robots_noimageindex', true );

        if ( ! empty( $noimageindex ) ) {
            return $data;
        }

        if ( ! empty( $images = $this->getImages( $post_id ) ) ) {
            $data += [ 'images' => $images ];
        }

        return $data;
    }

    /**
     * Recover the image data.
     *
     * @since 1.0.0
     */
    private function getImages( $post_id )
    {
        $images = [];

        if ( $image_ids = (new \Toolkit\Post( $post_id ))->getImages() ) {

            foreach( $image_ids as $id ) {
                array_push( $images, [ 'loc' => wp_get_attachment_url( $id ) ] );
            }
        }

        return $images;
    }
}
