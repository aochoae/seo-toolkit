<?php
/**
 * @package Toolkit\SocialMedia
 */

namespace Toolkit\SocialMedia;

use Toolkit\Post;

/**
 * Facebook class
 *
 * @since 1.0.0
 */
class Facebook
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Facebook
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'seo_toolkit_open_graph', [ $this, 'website' ], 10, 2 );

        add_filter( 'seo_toolkit_open_graph', [ $this, 'article' ], 10, 1 );

        add_filter( 'seo_toolkit_open_graph', [ $this, 'facebook' ], 10, 1 );

        add_filter( 'seo_toolkit_metadata_property', [ $this, 'metatags' ], 4, 2 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Facebook
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Facebook();
        }

        return self::$instance;
    }

    /**
     * Open Graph Markup
     *
     * @link https://developers.facebook.com/docs/sharing/webmasters
     *
     * @since 1.0.0
     */
    public function metatags( $metatags, $context )
    {
        $opengraph = apply_filters( 'seo_toolkit_open_graph', [], $context );

        if ( empty( $opengraph ) ) {
            return $metatags;
        }

        return $metatags + $opengraph;
    }

    /**
     * Retrieves the Open Graph meta tags for the front page.
     *
     * @since 1.0.0
     */
    public function website( $metatags, $context )
    {
        if ( 'frontpage' !== $context ) {
            return $metatags;
        }

        $blog_id = get_current_blog_id();

        $key = hash( 'sha384', serialize( [ 'facebook-website', SEO_TOOLKIT_FILE, $blog_id ] ) );

        if ( false === ( $opengraph = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $facebook = get_option( 'seo_toolkit_facebook' );

            /* Title */
            $title = isset( $facebook[ 'title' ] ) ? $facebook[ 'title' ] : '';

            if ( empty( $title ) ) {
                $title = get_bloginfo( 'name' );
            }

            /* Description */
            $description = isset( $facebook[ 'description' ] ) ? $facebook[ 'description' ] : '';

            $description = preg_replace('/\s+/', ' ', $description );

            if ( empty( $description ) ) {
                $description = get_bloginfo( 'description' );
            }

            /* Basic tags */
            $opengraph = [
                'og:type'        => 'website',
                'og:site_name'   => get_bloginfo( 'name' ),
                'og:url'         => get_home_url( $blog_id, '/' ),
                'og:title'       => $title,
                'og:description' => $description
            ];

            /* Image */
            if ( isset( $facebook[ 'image' ] ) && ! empty( $facebook[ 'image' ] ) ) {

                $image_id = attachment_url_to_postid( $facebook[ 'image' ] );

                $opengraph['og:images'] = $this->getImage( $image_id );
            }

            wp_cache_set( $key, $opengraph, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $opengraph;
    }

    /**
     * Retrieves the Open Graph meta tags for the posts.
     *
     * @since 1.0.0
     */
    public function article( $metatags )
    {
        if ( ! is_singular() ) {
            return $metatags;
        }

        $post_id = get_queried_object_id();

        $key = hash( 'sha384', serialize( [ 'facebook-article', SEO_TOOLKIT_FILE, $post_id ] ) );

        if ( false === ( $opengraph = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $post = new Post( $post_id );

            /* Title */
            $title = get_post_meta( $post_id, '_seo_toolkit_facebook_title', true );

            if ( empty( $title ) ) {
                $title = $post->getTitle();
            }

            /* Description */
            $description = get_post_meta( $post_id, '_seo_toolkit_facebook_description', true );

            $description = preg_replace('/\s+/', ' ', $description );

            if ( empty( $description ) ) {
                $description = $post->getDescription();
            }

            /* Basic tags */
            $opengraph = [
                'og:type'        => 'article',
                'og:site_name'   => get_bloginfo( 'name' ),
                'og:url'         => get_permalink( $post_id ),
                'og:title'       => $title,
                'og:description' => $description
            ];

            /* Image */
            $_images = '';

            if( $image = (int) get_post_field( '_seo_toolkit_facebook_image', $post_id ) ) {

                $image_id = attachment_url_to_postid( $image );

                $cards['og:images'] = $this->getImage( $image_id );
            }

            elseif ( $image = $post->getImage() ) {
                $opengraph['og:images'] = $this->getImage( $image );
            }

            /* Gallery */
            elseif ( $gallery = $post->getGallery() ) {
                $opengraph['og:images'] = iterator_to_array( $this->getImages( $gallery ) );
            }

            if ( !empty( $_images ) ) {
                $opengraph = $_images;
            }

            wp_cache_set( $key, $opengraph, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $opengraph;
    }

    /**
     * Retrieves the Facebook Admin ID and Facebook App ID meta tags.
     *
     * @since 1.0.0
     */
    public function facebook( $metatags )
    {
        if ( ! is_front_page() || ! is_singular() ) {
            return $metatags;
        }

        $facebook = get_option( 'seo_toolkit_facebook' );

        $admins = isset( $facebook['admins'] ) ? $facebook['admins'] : '';

        if ( ! empty( $admins ) ) {
            $metatags['fb:admins'] = $admins;
        }

        $app_id = isset( $facebook['app_id'] ) ? $facebook['app_id'] : '';

        if ( ! empty( $app_id ) ) {
            $metatags['fb:app_id'] = $app_id;
        }

        return $metatags;
    }

    /**
     * Retrieves the metadata for multiple images.
     *
     * @since 1.0.0
     */
    private function getImages( $images_ids )
    {
        foreach ( $images_ids as $image_id ) {
            yield "$image_id" => $this->getImageProperty( $image_id );
        }
    }

    /**
     * Retrieves the metadata for an image.
     *
     * @since 1.0.0
     */
    private function getImage( $image_id )
    {
        return $this->getImageProperty( $image_id );
    }

    /**
     * Creates the metadata for an image.
     *
     * @since 1.0.0
     */
    private function getImageProperty( $image_id )
    {
        $metadata = wp_get_attachment_metadata( $image_id );

        $metatags = [
            'og:image'            => wp_get_attachment_url( $image_id ),
            'og:image:secure_url' => wp_get_attachment_url( $image_id ),
            'og:image:type'       => get_post_mime_type( $image_id ),
            'og:image:width'      => $metadata[ 'width' ],
            'og:image:height'     => $metadata[ 'height' ]
        ];

        if ( wp_http_supports( array( 'ssl' ), $metatags[ 'og:image:secure_url' ] ) ) {
            unset( $metatags[ 'og:image:secure_url' ] );
        }

        return $metatags;
    }
}
