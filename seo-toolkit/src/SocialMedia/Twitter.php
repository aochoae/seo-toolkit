<?php
/**
 * @package Toolkit\SocialMedia
 */

namespace Toolkit\SocialMedia;

use Toolkit\Post;

/**
 * Twitter class
 *
 * @since 1.0.0
 */
class Twitter
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Twitter
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'seo_toolkit_twitter_cards', [ $this, 'website' ], 10, 2 );

        add_filter( 'seo_toolkit_twitter_cards', [ $this, 'article' ], 10, 2 );

        add_filter( 'seo_toolkit_metadata', [ $this, 'metatags' ], 6, 2 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Twitter
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Twitter();
        }

        return self::$instance;
    }

    /**
     * Tweets with Cards Markup
     *
     * @link https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/abouts-cards
     *
     * @since 1.0.0
     */
    public function metatags( $metadata, $context )
    {
        $twitter = apply_filters( 'seo_toolkit_twitter_cards', [], $context );

        if ( empty( $twitter ) ) {
            return $metadata;
        }

        return $metadata + $twitter;
    }

    /**
     * Retrieves the Twitter Cards meta tags for the front page.
     *
     * @since 1.0.0
     */
    public function website( $metatags, $context )
    {
        if ( 'frontpage' !== $context ) {
            return $metatags;
        }

        $blog_id = get_current_blog_id();

        $key = hash( 'md5', serialize( [ 'twitter-website', SEO_TOOLKIT_FILE, $blog_id ] ) );

        if ( false === ( $cards = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $twitter = get_option( 'seo_toolkit_twitter' );

            /* Twitter Cards */
            $card = isset( $twitter[ 'card' ] ) ? $twitter[ 'card' ]: 'summary';

            /* Profile */
            if ( $profile = isset( $twitter[ 'profile' ] ) ? $twitter[ 'profile' ] : '' ) {
                $profile = sprintf( '@%s', trim( $profile, '@' ) );
            }

            /* Title */
            $title = isset( $twitter[ 'title' ] ) ? $twitter[ 'title' ] : '';

            if ( empty( $title ) ) {
                $title = get_bloginfo( 'name' );
            }

            /* Description */
            $description = isset( $twitter[ 'description' ] ) ? $twitter[ 'description' ] : '';

            $description = preg_replace('/\s+/', ' ', $description );

            if ( empty( $description ) ) {
                $description = get_bloginfo( 'description' );
            }

            /* Basic tags */
            $cards = [
                'twitter:card'        => $card,
                'twitter:site'        => $profile,
                'twitter:title'       => $title,
                'twitter:description' => $description
            ];

            /* Image */
            if ( isset( $twitter[ 'image' ] ) && ! empty( $twitter[ 'image' ] ) ) {

                $image_id = attachment_url_to_postid( $twitter[ 'image' ] );

                $cards['twitter:images'] = $this->getImage( $image_id );
            }

            wp_cache_set( $key, $cards, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $cards;
    }

    /**
     * Retrieves the Twitter Cards meta tags for the posts.
     *
     * @since 1.0.0
     */
    public function article( $metatags, $context )
    {
        if ( ! is_singular() ) {
            return $metatags;
        }

        $post_id = get_queried_object_id();

        $key = hash( 'md5', serialize( [ 'twitter-article', SEO_TOOLKIT_FILE, $post_id ] ) );

        if ( false === ( $cards = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $post = new Post( $post_id );

            /* Twitter Cards */
            $card = get_post_meta( $post_id, '_seo_toolkit_twitter_card', true );

            if ( empty( $card ) || 'default' == $card ) {

                $twitter = get_option( 'seo_toolkit_twitter' );

                $card = isset( $twitter[ 'card' ] ) ? $twitter[ 'card' ] : 'summary';
            }

            /* Title */
            $title = get_post_meta( $post_id, '_seo_toolkit_twitter_title', true );

            if ( empty( $title ) ) {
                $title = $post->getTitle();
            }

            /* Description */
            $description = get_post_meta( $post_id, '_seo_toolkit_twitter_description', true );

            $description = preg_replace('/\s+/', ' ', $description );

            if ( empty( $description ) ) {
                $description = $post->getDescription();
            }

            /* Basic tags */
            $cards = [
                'twitter:card'        => $card,
                'twitter:title'       => $title,
                'twitter:description' => $description
            ];

            /* Author */
            $author_id = (int) get_post_field( 'post_author', $post_id );

            if ( ! empty( $author = $this->getUsername( $author_id ) ) ) {
                $cards[ 'twitter:creator' ] = $author;
            }

            /* Image */
            if( $image = get_post_field( '_seo_toolkit_twitter_image', $post_id ) ) {

                $image_id = attachment_url_to_postid( $image );

                $cards['twitter:images'] = $this->getImage( $image_id );
            }

            /* Gallery */
            elseif ( $gallery = $post->getGallery() ) {
                $cards['twitter:images'] = iterator_to_array( $this->getImages( $gallery ) );
            }

            /* Image */
            elseif ( $image = $post->getImage() ) {
                $cards['twitter:images'] = $this->getImage( $image );
            }

            wp_cache_set( $key, $cards, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $cards;
    }

    /**
     * Retrieves the metadata for the first image obtained.
     *
     * @since 1.0.0
     */
    private function getImages( $images_ids )
    {
        foreach ( $images_ids as $image_id ) {
            yield "$image_id" => $this->getImageProperty( $image_id );
            break; /* Just one image */
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
        $metatags['twitter:image'] = wp_get_attachment_url( $image_id );

        if ( ! empty( $caption = wp_get_attachment_caption( $image_id ) ) ) {
            $metatags['twitter:image:alt'] = $caption;
        }

        return $metatags;
    }

    /**
     * Creates the metadata for an image.
     *
     * @since 1.0.0
     */
    private function getUsername( $author_id )
    {
        $twitter = get_the_author_meta( 'twitter', $author_id );

        if ( false !== ( $twitter = filter_var( $twitter, FILTER_VALIDATE_URL ) ) ) {

            preg_match( '|^http(?:s)://twitter.com/([A-Za-z0-9_]+)$|i', $twitter, $matches );

            $twitter = isset( $matches[1] ) ? $matches[1] : '';
        }

        return $twitter;
    }
}
