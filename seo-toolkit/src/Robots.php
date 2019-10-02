<?php
/**
 * @package Toolkit
 */

namespace Toolkit;

/**
 * Robots class
 *
 * @since 1.0.0
 */
class Robots
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
        $this->settings = get_option( 'seo_toolkit_robots' );

        /* Robots meta tag */
        add_filter( 'seo_toolkit_robots', [ $this, 'singular' ], 10, 2 );

        add_filter( 'seo_toolkit_robots', [ $this, 'taxonomies' ], 10, 2 );

        add_filter( 'seo_toolkit_robots', [ $this, 'robots' ], 10, 2 );

        add_filter( 'seo_toolkit_robots', [ $this, 'paginated' ], PHP_INT_MAX, 2 );

        add_filter( 'seo_toolkit_metadata', [ $this, 'metatags' ], 4, 2 );

        /* Canonical */
        add_action( 'seo_toolkit_head', [ $this, 'canonical' ], 4 );

        /* Remove WordPress' canonical links */
        remove_action( 'wp_head', 'rel_canonical' );

        /* Protect RSS Feeds */
        if ( (bool) get_option( 'seo_toolkit_robots_feed_noindex', false ) ) {

            $actions = [
                'rss_head',
                'rss2_head',
                'atom_head',
                'rdf_header',
                'comments_atom_head',
                'commentsrss2_head'
            ];

            foreach($actions as $action) {
                add_action( $action, [ $this, 'feeds' ] );
            }
        }
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
            self::$instance = new Robots();
        }

        return self::$instance;
    }

    /**
     * Filters the robots meta tags.
     *
     * @since 1.0.0
     */
    public function metatags( $metadata, $context )
    {
        $robots = apply_filters( 'seo_toolkit_robots', [], $context );

        if ( (bool) get_option( 'seo_toolkit_robots_donot_implement_index', false ) ) {

            foreach( $robots as $idx => $value ) {
                if ( in_array( $value['robots'], [ 'index', 'index, follow' ] ) ) {
                    unset( $robots[ $idx ] );
                }
            }

            $robots = array_values( array_filter( $robots ) );
        }

        if ( empty( $robots ) ) {
            return $metadata;
        }

        return $metadata + [ 'robots' => $robots ];
    }

    /**
     * Filters the robots meta tags for a single post of any post type (post,
     * attachment, page, custom post types).
     *
     * @since 1.0.0
     */
    public function singular( $robots, $context )
    {
        if ( ! is_singular() ) {
            return $robots;
        }

        $post_id = get_the_ID();

        $key = hash( 'md5', serialize( [ "robots-$context", SEO_TOOLKIT_FILE, $post_id ] ) );

        if ( false === ( $robots = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $option = get_post_meta( $post_id, '_seo_toolkit_robots', true ) ?: 'default';

            $robots = [];

            if ( ! in_array( $option, [ 'default', 'Default' ] ) ) {
                $robots[] = [ 'robots' => $option ];
            } else {

                $default = isset( $this->settings[ $context ] ) ? $this->settings[ $context ] : 'index';

                $robots[] = [ 'robots' => $default ];
            }

            if ( ! empty( get_post_meta( $post_id, '_seo_toolkit_robots_noarchive', true ) ) ) {
                $robots[] = [ 'robots' => 'noarchive' ];
            }

            if ( ! empty( get_post_meta( $post_id, '_seo_toolkit_robots_nosnippet', true ) ) ) {
                $robots[] = [ 'robots' => 'nosnippet' ];
            }

            if ( ! empty( get_post_meta( $post_id, '_seo_toolkit_robots_noimageindex', true ) ) ) {
                $robots[] = [ 'robots' => 'noimageindex' ];
            }

            $robots = apply_filters( "seo_toolkit_robots_$context", $robots, $option, $post_id );

            wp_cache_set( $key, $robots, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $robots;
    }

    /**
     * Filters the robots meta tags for categories, tags and taxonomies.
     *
     * @since 1.0.0
     */
    public function taxonomies( $robots, $context )
    {
        if ( ! ( is_category() || is_tag() || is_tax() ) ) {
            return $robots;
        }

        $term_id = (int) get_queried_object_id();

        $key = hash( 'md5', serialize( [ "robots-$context", SEO_TOOLKIT_FILE, $term_id ] ) );

        if ( false === ( $robots = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $robots = [];

            $value = get_term_meta( $term_id, '_seo_toolkit_robots', true );

            if ( empty( $value ) || 'Default' == $value ) {
                $value = isset( $this->settings[ $context ] ) ? $this->settings[ $context ] : 'index, follow';
            }

            $robots[] = [ 'robots' => $value ];

            $robots = apply_filters( "seo_toolkit_robots_$context", $robots, $value, $term_id );

            wp_cache_set( $key, $robots, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $robots;
    }

    /**
     * Filters the robots meta tags for archive pages.
     *
     * @since 1.0.0
     */
    public function archive( $robots, $context )
    {
        if ( ! is_archive() || ( is_category() || is_tag() || is_tax() ) ) {
            return $robots;
        }

        $object_id = (int) get_queried_object_id();

        $key = hash( 'md5', serialize( [ "robots-$context", SEO_TOOLKIT_FILE, $object_id ] ) );

        if ( false === ( $robots = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $robots = [];

            $option = isset( $this->settings[ $context ] ) ? $this->settings[ $context ]: 'index';

            $robots[] = [ 'robots' => $option ];

            $robots = apply_filters( "seo_toolkit_robots_$context", $robots, $option, $object_id );

            wp_cache_set( $key, $robots, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $robots;
    }

    /**
     * Filters the robots meta tags for website pages.
     *
     * @since 1.0.0
     */
    public function robots( $robots, $context )
    {
        if ( is_singular() ) {
            return $robots;
        }

        $object_id = (int) get_queried_object_id();

        $key = hash( 'md5', serialize( [ "robots-$context", SEO_TOOLKIT_FILE, $object_id ] ) );

        if ( false === ( $robots = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $robots = [];

            $option = isset( $this->settings[ $context ] ) ? $this->settings[ $context ]: 'index';

            $robots[] = [ 'robots' => $option ];

            $robots = apply_filters( "seo_toolkit_robots_$context", $robots, $option, $object_id );

            wp_cache_set( $key, $robots, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $robots;
    }

    /**
     * Filters the robots meta tags for paginated pages.
     *
     * @since 1.0.0
     */
    public function paginated( $robots, $context )
    {
        if ( is_paged() ) {

            $option = get_option( 'seo_toolkit_robots_paginated_pages' );

            $paginated = [];

            $paginated[] = [ 'robots' => $option ];

            return $paginated;
        }

        return $robots;
    }

    /**
     * Output the canonical URL.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function canonical()
    {
        if ( ! empty( $url = $this->getCanonical() ) ) {
            printf( '<link rel="canonical" href="%s" />' . PHP_EOL, esc_url( $url ) );
        }
    }

    /**
     * Returns the canonical URL.
     *
     * @since 1.0.0
     *
     * @return string
     */
    private function getCanonical()
    {
        if ( is_404() ) {
            return '';
        }

        global $page, $paged;

        $canonical = '';

        $object_id = get_queried_object_id();

        if ( is_front_page() ) {
            $canonical = get_home_url( get_current_blog_id(), '/' );
        }

        elseif ( is_singular() ) {
            $canonical = get_permalink( $object_id );
        }

        elseif ( is_archive() ) {

            if ( is_category() || is_tag() || is_tax() ) {
                $object = get_queried_object();

                $canonical = get_term_link( $object, $object->taxonomy );
            }

            elseif ( is_author() ) {
                $canonical = get_author_posts_url( $object_id );
            }

            elseif ( is_date() ) {

                if ( is_day() ) {
                    $canonical = get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
                } elseif ( is_month() ) {
                    $canonical = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
                } elseif ( is_year() ) {
                    $canonical = get_year_link( get_query_var( 'year' ) );
                }
            }

            elseif ( is_post_type_archive() ) {
                $canonical = get_post_type_archive_link( get_query_var( 'post_type' ) );
            }

        } elseif ( is_search() ) {
            $canonical = get_search_link();
        } else {
            $canonical = '';
        }

        if ( ! empty( $canonical ) && ( $paged >= 2 || $page >= 2 ) ) {
            $canonical .= sprintf( 'page/%s/', max( $paged, $page ) );
        }

        if ( ! empty( $canonical ) ) {
            return apply_filters( 'seo_toolkit_canonical', $canonical );
        }
    }

    /**
     * Protect RSS Feeds.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function feeds()
    {
        echo '<xhtml:meta xmlns:xhtml="http://www.w3.org/1999/xhtml" name="robots" content="noindex" />' . PHP_EOL;
    }
}
