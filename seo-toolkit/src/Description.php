<?php
/**
 * @package Toolkit
 */

namespace Toolkit;

/**
 * Description class
 *
 * @since 1.0.0
 */
class Description
{
    /**
     * Settings
     *
     * @since 1.0.0
     */
    private $settings;

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Description
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        $this->settings = get_option( 'seo_toolkit_description' );

        add_filter( 'seo_toolkit_description', [ $this, 'frontpage' ], 10, 2 );

        add_filter( 'seo_toolkit_description', [ $this, 'blog' ], 10, 2 );

        add_filter( 'seo_toolkit_description', [ $this, 'singular' ], 10, 2 );

        add_filter( 'seo_toolkit_description', [ $this, 'taxonomies' ], 10, 2 );

        add_filter( 'seo_toolkit_description', [ $this, 'paginated' ], 10, 2 );

        add_filter( 'seo_toolkit_metadata', [ $this, 'metatags' ], 2, 2 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Description
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Description;
        }

        return self::$instance;
    }

    /**
     * Filters the descriptions meta tags.
     *
     * @since 1.0.0
     */
    public function metatags( $metatags, $context )
    {
        $description = apply_filters( 'seo_toolkit_description', $metatags, $context );

        if ( empty( $description ) ) {
            return $metatags;
        }

        $description = stripslashes( preg_replace( '|\s+|', ' ', $description ) );

        return $metatags + [ 'description' => wp_strip_all_tags( $description ) ];
    }

    /**
     * Filters the description for the front page.
     *
     * @since 1.0.0
     */
    public function frontpage( $description, $context )
    {
        if ( 'frontpage' !== $context ) {
            return $description;
        }

        $blog_id = get_current_blog_id();

        $key = hash( 'md5', serialize( [ 'description-frontpage', SEO_TOOLKIT_FILE, $blog_id ] ) );

        if ( false === ( $description = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $description = '';

            if ( 0 !== ( $page_id = get_option( 'page_on_front' ) ) ) {
                $description = get_post_meta( $page_id, '_seo_toolkit_description', true );
            }

            if ( empty( $description ) ) {

                $option = isset( $this->settings[ 'frontpage' ] ) ? $this->settings[ 'frontpage' ]: '%default%';

                switch ( $option ) {
                    case '%default%':
                        $description = get_option( 'seo_toolkit_description_default' );
                        break;
                    case '%tagline%':
                        $description = get_bloginfo( 'description', 'display' );
                        break;
                    default:
                        $description = '';
                }

                $description = $description ?: get_bloginfo( 'description', 'display' );
            }

            $description = apply_filters( 'seo_toolkit_description_frontpage', $description, $option, $blog_id );

            wp_cache_set( $key, $description, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $description;
    }

    /**
     * Filters the description for the blog page.
     *
     * @since 1.0.0
     */
    public function blog( $description, $context )
    {
        if ( 'blog' !== $context ) {
            return $description;
        }

        $page_id = (int) get_option( 'page_for_posts' );

        $key = hash( 'md5', serialize( [ 'description-blog', SEO_TOOLKIT_FILE, $page_id ] ) );

        if ( false === ( $description = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $description = get_post_meta( $page_id, '_seo_toolkit_description', true );

            $option = '';

            if ( empty( $description ) ) {

                $option = isset( $this->settings[ 'blog' ] ) ? $this->settings[ 'blog' ]: '%default%';

                switch ( $option ) {
                    case '%excerpt%':
                        $description = get_post_field( 'post_excerpt', $post_id );
                        break;
                    case '%tagline%':
                        $description = get_bloginfo( 'description', 'display' );
                        break;
                    default:
                        $description = '';
                }
            }

            $description = apply_filters( 'seo_toolkit_description_blog', $description, $option, $page_id );

            wp_cache_set( $key, $description, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $description;
    }

    /**
     * Filters the description for a single post of any post type (post,
     * attachment, page, custom post types).
     *
     * @since 1.0.0
     */
    public function singular( $description, $context )
    {
        if ( ! is_singular() ) {
            return $description;
        }

        $post_id = get_queried_object_id();

        $key = hash( 'md5', serialize( [ "description-$context", SEO_TOOLKIT_FILE, $post_id ] ) );

        if ( false === ( $description = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $description = get_post_meta( $post_id, '_seo_toolkit_description', true );

            $option = '';

            if ( empty( $description ) ) {

                $option = isset( $this->settings[ $context ] ) ? $this->settings[ $context ]: '%excerpt%';

                switch ( $option ) {
                    case '%excerpt%':
                        $description = get_post_field( 'post_excerpt', $post_id );
                        break;
                    default:
                        $description = '';
                }
            }

            $description = apply_filters( "seo_toolkit_description_$context", $description, $option, $post_id );

            if ( empty( $description ) ) {
                $description = (new Post( $post_id ))->getDescription();
            }

            wp_cache_set( $key, $description, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $description;
    }

    /**
     * Filters the description for categories, tags and taxonomies.
     *
     * @since 1.0.0
     */
    public function taxonomies( $description, $context )
    {
        if ( ! ( is_category() || is_tag() || is_tax() ) ) {
            return $description;
        }

        $term_id = (int) get_queried_object_id();

        $key = hash( 'md5', serialize( [ "description-$context", SEO_TOOLKIT_FILE, $term_id ] ) );

        if ( false === ( $description = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $option = isset( $this->settings[ $context ] ) ? $this->settings[ $context ] : '%none%';

            switch ( $option ) {
                case '%description%':
                    $description = get_term_meta( $term_id, '_seo_toolkit_description', true );

                    if ( empty( $description ) ) {
                        $description = term_description( $term_id );
                    }
                    break;
                case '%biography%':
                    $description = get_the_author_meta( 'description', $term_id );
                    break;
                default:
                    $description = '';
            }

            $description = apply_filters( "seo_toolkit_description_$context", $description, $option, $term_id );

            wp_cache_set( $key, $description, 'seo_toolkit', WEEK_IN_SECONDS );
        }

        return $description;
    }

    /**
     * Filters the description (empty) for paginated pages.
     *
     * @since 1.0.0
     */
    public function paginated( $description, $context )
    {
        if ( is_paged() ) {
            return '';
        }

        return $description;
    }
}
