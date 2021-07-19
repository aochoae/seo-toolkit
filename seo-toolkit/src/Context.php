<?php
/**
 * @package Toolkit
 */

namespace Toolkit;

/**
 * Context class
 *
 * @since 1.0.0
 */
class Context
{
    /**
     * Construct.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
    }

    /**
     * Retrieve all public pages (contexts) in cache.
     *
     * @since 1.0.0
     */
    public function getContexts()
    {
        $key = hash( 'sha384', serialize( [ 'contexts', SEO_TOOLKIT_FILE ] ) );

        if ( false === ( $contexts = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $contexts = $this->doContexts();

            wp_cache_set( $key, $contexts, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $contexts;
    }

    /**
     * Retrieve all public pages (contexts).
     *
     * @since 1.0.0
     */
    private function doContexts()
    {
        $context = [];

        /* Front page */
        $context[ 'frontpage' ] = __( 'Home', 'seo-toolkit' );

        /* Home */
        $context[ 'blog' ] = __( 'Blog', 'seo-toolkit' );

        /* Public post types */
        $post_types = get_post_types( [ 'public' => true, '_builtin' => true ], 'objects' );

        foreach( $post_types as $post_type ) {
            $context[ $post_type->name ] = $post_type->labels->name;
        }

        /* Taxonomies */
        $taxonomies = get_taxonomies( [ 'public'   => true, '_builtin' => true ], 'objects' );

        foreach( $taxonomies as $taxonomy ) {
            $context[ $taxonomy->name ] = $taxonomy->labels->name;
        }

        /* Custom post types with taxonomies */
        $custom_post_types = get_post_types( [ 'public' => true, '_builtin' => false ], 'objects' );

        foreach( $custom_post_types as $post_type ) {

            $context[ $post_type->name ] = $post_type->labels->name;

            $taxonomies = get_taxonomies( [ 'object_type' => [ $post_type->name ], 'public' => true ], 'objects' );

            foreach( $taxonomies as $taxonomy ) {
                $context[ $taxonomy->name ] = $taxonomy->labels->name;
            }

            if ( true == $post_type->has_archive ) { /* translators: %s: post type label name */
                $context[ "{$post_type->name}_archive" ] = sprintf( __( '%s archive', 'seo-toolkit' ), $post_type->labels->name );
            }
        }

        /* Author archives */
        $context[ 'author' ] = __( 'Authors', 'seo-toolkit' );

        /* Date archives */
        $context[ 'date' ] = __( 'Date', 'seo-toolkit' );

        /* Search */
        $context[ 'search' ] = __( 'Search', 'seo-toolkit' );

        /* Page not found */
        $context[ 'error' ] = __( 'Error 404', 'seo-toolkit' );

        unset( $context[ 'post_format' ] );

        return apply_filters( 'seo_toolkit_contexts', $context );
    }

    /**
     * Retrieve the current page (context).
     *
     * @since 1.0.0
     */
    public function getContext()
    {
        $context = '';

        if ( is_front_page() ) {
            $context = 'frontpage';
        }

        elseif ( is_home() ) {
            $context = 'blog';
        }

        elseif ( is_singular() ) {
            $context = get_post_type( get_queried_object_id() );
        }

        elseif ( is_archive() ) {

            if ( is_category() || is_tag() || is_tax() ) {
                $object = get_queried_object();

                $context = $object->taxonomy;
            }

            elseif ( is_author() ) {
                $context = 'author';
            }

            elseif ( is_date() ) {
                $context = 'date';
            }

            elseif ( is_post_type_archive() ) {
                $context = get_query_var( 'post_type' ) . '_archive';
            }
        } elseif ( is_search() ) {
            $context = 'search';
        } elseif ( is_404() ) {
            $context = 'error';
        } else {
            $context = 'unknown';
        }

        return apply_filters( 'seo_toolkit_context', $context );
    }
}
