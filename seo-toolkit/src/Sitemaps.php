<?php
/**
 * @package Toolkit
 */

namespace Toolkit;

/**
 * Sitemaps class
 *
 * @since 1.0.0
 */
class Sitemaps
{
    /**
     * XML Sitemap file
     *
     * @since 1.0.0
     */
    private $sitemap_xml = 'sitemap.xml';

    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Sitemaps
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'query_vars', [ $this, 'queryVars' ], 1, 1 );

        add_filter( 'rewrite_rules_array', [ $this, 'rewriteRules' ], 1, 1 );

        add_action( 'parse_query', [ $this, 'parseQuery' ], 1, 1 );

        add_filter( 'seo_toolkit_sitemaps', [ $this, 'exclude' ], 10, 1 );

        if ( 0 <> ( $frontpage = (int) get_option( 'page_on_front' ) ) ) {
            add_filter( 'seo_toolkit_sitemap_page_ids', function( $ids ) use ( $frontpage ) {
                unset( $ids[ $frontpage ] );
                return $ids;
            }, 99, 1 );
        }

        add_action( 'init', [ $this, 'complement' ] );

        add_action( 'init', [ $this, 'ping' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Sitemaps
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Sitemaps;
        }

        return self::$instance;
    }

    /**
     * Adds 'xml-sitemap' as query variable.
     *
     * @since 1.0.0
     */
    public function queryVars( $query_vars )
    {
        array_push( $query_vars, 'xml-sitemap' );

        return $query_vars;
    }

    /**
     * Adds rewrite rules for the XML sitemaps.
     *
     * @since 1.0.0
     */
    public function rewriteRules( $rules )
    {
        $sitemap = [
            'sitemap-([A-Za-z\-_]+).xml$' => 'index.php?xml-sitemap=$matches[1]',
            'sitemap.xml$'                => 'index.php?xml-sitemap=index'
        ];

        return $sitemap + $rules;
    }

    /**
     * Parse the 'xml-sitemap' query string.
     *
     * @since 1.0.0
     */
    public function parseQuery( $query )
    {
        if ( ! empty( $query->query_vars[ "xml-sitemap" ] ) ) {

            $sitemap = $query->query_vars[ "xml-sitemap" ];

            if ( ! empty( $xml = $this->getSitemap( $sitemap ) ) ) {

                $charset = get_bloginfo( 'charset' );

                header( "Content-type: text/xml; charset=" . $charset );
                header( "Content-Length: " . strlen( $xml ) );

                echo $xml;
                exit;
            }

            $query->set_404();
            status_header( 404 );
        }
    }

    /**
     * Retrieves the xml sitemap content.
     *
     * @since 1.0.0
     */
    public function getSitemap( $sitemap )
    {
        $sitemaps = $this->getSitemaps();

        if ( ! in_array( $sitemap, $sitemaps ) ) {
            return '';
        }

        $xml = '';

        switch( $sitemap ) {
            case 'index':
                $xml = (new \Toolkit\Sitemaps\Sitemaps( $sitemaps ))->getSitemap();
                break;
            case 'home':
                $xml = (new \Toolkit\Sitemaps\Home)->getSitemap();
                break;
            case 'post':
            case 'page':
                $xml = (new \Toolkit\Sitemaps\Post( $sitemap ))->getSitemap();
                break;
            case 'author':
                $xml = (new \Toolkit\Sitemaps\Author)->getSitemap();
                break;
            default:
                if ( post_type_exists( $sitemap ) ) {
                    $xml = (new \Toolkit\Sitemaps\Post( $sitemap ))->getSitemap();
                } elseif ( taxonomy_exists( $sitemap ) ) {
                    $xml = (new \Toolkit\Sitemaps\Taxonomy( $sitemap ))->getSitemap();
                } else {
                    $xml = '';
                }
        }

        return $xml;
    }

    /**
     * Filters the pages that will not be displayed in the sitemap index.
     *
     * @since 1.0.0
     */
    public function exclude( $ids )
    {
        $robots = get_option( 'seo_toolkit_robots', [] );

        $robots = array_filter( $robots, function( $v, $k ) {
            return in_array( $v, [ 'noindex', 'noindex, follow', 'noindex, nofollow' ] );
        }, ARRAY_FILTER_USE_BOTH );

        $pages = array_keys( $robots );

        foreach( $pages as $page ) {
            if ( false !== ( $idx = array_search( $page, $ids ) ) ) {
                unset( $ids[ $idx ] );
            }
        }

        return array_values( array_filter( $ids ) );
    }

    /**
     * Retrieve the list of XML sitemaps.
     *
     * @since 1.0.0
     */
    private function getSitemaps()
    {
        $sitemaps[] = 'home';

        $post_types = get_post_types( [ 'public' => true ] );

        foreach( $post_types as $post_type ) {

            if ( wp_count_posts( $post_type ) ) {
                $sitemaps[] = $post_type;
            }
        }

        if ( $key = array_search( 'attachment', $sitemaps ) ) {
            unset( $sitemaps[ $key ] );
        }

        foreach( $post_types as $post_type ) {

            $args = [
                'public'      => true,
                'object_type' => [ $post_type ]
            ];

            $taxonomies = get_taxonomies( $args );

            foreach( $taxonomies as $taxonomy ) {

                if ( 0 < wp_count_terms( $taxonomy, [ 'hide_empty' => true ] ) ) {
                    $sitemaps[] = $taxonomy;
                }
            }
        }

        if ( $key = array_search( 'post_format', $sitemaps ) ) {
            unset( $sitemaps[ $key ] );
        }

        array_push( $sitemaps, 'index', 'author' );

        $sitemaps = apply_filters( 'seo_toolkit_sitemaps', $sitemaps );

        return array_values( array_filter( $sitemaps ) );
    }

    /**
     * Adds the images to the sitemap.
     *
     * @since 1.0.0
     */
    public function complement()
    {
        if ( true == get_option( 'seo_toolkit_sitemaps_images_enable', true ) ) {
            \Toolkit\Sitemaps\Images::newInstance();
        }
    }

    /**
     * Submits the XML sitemap to search engines.
     *
     * @since 1.0.0
     */
    public function ping()
    {
        \Toolkit\Sitemaps\Ping::newInstance();
    }
}
