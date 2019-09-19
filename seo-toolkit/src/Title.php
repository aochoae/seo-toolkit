<?php
/**
 * @package Toolkit
 */

namespace Toolkit;

/**
 * Title class
 *
 * @since 1.0.0
 */
class Title
{
    /**
     * @since 1.0.0
     */
    const FORMAT_DEFAULT = "%title% %separator% %site-title%";

    /**
     * Settings
     *
     * @since 1.0.0
     */
    private $settings = [];

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Title
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        /* Settings */
        $default = [
            'frontpage' => '%site-title% %separator% %tagline%',
            'author'    => '%author% %separator% %site-title%'
        ];
        $this->settings = get_option( 'seo_toolkit_title', $default );

        /* Filters the parts of the document title */
        add_filter( 'document_title_parts', [ $this, 'title' ], 10, 1 );

        /* Filters the separator for the document title */
        add_filter( 'document_title_separator', [ $this, 'separator' ], 10, 1 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Title
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Title;
        }

        return self::$instance;
    }

    /**
     * Filters the parts of the document title.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function title( $title )
    {
        global $page, $paged;

        $context = (new Context)->getContext();

        $format = isset( $this->settings[ $context ] ) ? $this->settings[ $context ] : self::FORMAT_DEFAULT;

        $format = preg_split( '|\s+|', str_replace( '%separator%', '', $format ) );

        $title = [];

        foreach( $format as $key ) {

            switch( $key ) {
                case '%site-title%':
                    $_title = '';

                    if ( 0 !== ( $page_id = get_option( 'page_on_front' ) ) ) {
                        $_title = get_post_meta( $page_id, '_seo_toolkit_title', true );
                    }

                    if ( empty( $_title ) ) {
                        $_title = get_bloginfo( 'name', 'display' );
                    }
                    $title[] = $_title;
                    break;
                case '%tagline%':
                    $title[] = get_bloginfo( 'description', 'display' );
                    break;
                default:
                    $title[] = $this->getDocumentTitle();
            }
        }

        if ( $paged >= 2 || $page >= 2 ) { /* translators: %s: page number */
            $title[] = sprintf( __( 'Page %s', 'seo-toolkit' ), max( $paged, $page ) );
        }

        return apply_filters( 'seo_toolkit_title', $title, $context );
    }

    /**
     * Returns document title for the current page.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function getDocumentTitle()
    {
        $title = '';

        /* Home */
        if ( is_front_page() ) {

            if ( 0 !== ( $page_id = get_option( 'page_on_front' ) ) ) {
                $title = get_post_meta( $page_id, '_seo_toolkit_title', true );
            }

            if ( empty( $title ) ) {
                $title = get_bloginfo( 'name', 'display' );
            }
        }

        /* Blog and Singular */
        elseif ( is_home() || is_singular() ) {

            $post_id = (int) get_queried_object_id();

            $title = (new Post( $post_id ))->getTitle();
        }

        /* Post type archive title */
        elseif ( is_post_type_archive() ) {
            $title = post_type_archive_title( '', false );
        }

        /* Taxonomy archive */
        elseif ( is_category() || is_tag() || is_tax() ) {

            $term_id = (int) get_queried_object_id();

            $title = get_term_meta( $term_id, '_seo_toolkit_title', true );

            if ( empty( $title ) ) {
                $title = single_term_title( '', false );
            }
        }

        /* Author archive */
        elseif ( is_author() && $author = get_queried_object() ) {
            $title = $author->display_name;
        }

        /* Date archive */
        elseif ( is_year() ) {
            $title = get_the_date( 'Y' );
        }

        /* Date archive */
        elseif ( is_month() ) {
            $title = get_the_date( 'F Y' );
        }

        /* Date archive */
        elseif ( is_day() ) {
            $title = get_the_date();
        }

        /* Search results */
        elseif ( is_search() ) { /* translators: %s: search term */
            $title = sprintf( __( 'Search results for &#8220;%s&#8221;', 'seo-toolkit' ), get_search_query() );
        }

        /* Error 404 */
        elseif ( is_404() ) {
            $title = __( 'Page not found', 'seo-toolkit' );
        }

        return $title;
    }

    /**
     * Filters the separator for the document title.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function separator( $separator )
    {
        return get_option( 'seo_toolkit_title_separator', $separator );
    }
}
