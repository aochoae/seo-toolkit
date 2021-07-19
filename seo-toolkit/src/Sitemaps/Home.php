<?php
/**
 * @package Toolkit\Sitemaps
 */

namespace Toolkit\Sitemaps;

/**
 * Home class
 *
 * @since 1.0.0
 */
class Home extends AbstractSitemap
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    public function getSitemap()
    {
        $key = hash( 'sha384', serialize( [ "sitemap-home", SEO_TOOLKIT_FILE, 'document' ] ) );

        if ( false === ( $document = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            $document = $this->getDocument();

            wp_cache_set( $key, $document, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return $document;
    }

    /**
     * Creates a new XML sitemap.
     *
     * @since 1.0.0
     */
    private function getDocument()
    {
        $document = new \DOMDocument( '1.0', 'UTF-8' );
        $document->formatOutput = false;

        $xsl = plugins_url( 'static/css/sitemap.xsl', SEO_TOOLKIT_FILE );
        $xslt = $document->createProcessingInstruction( 'xml-stylesheet', "type=\"text/xsl\" href=\"{$xsl}\"" );
        $document->appendChild( $xslt );

        $element = $document->createElementNS( 'https://www.sitemaps.org/schemas/sitemap/0.9', 'urlset' );
        $document->appendChild( $element );

        $home = $this->getHome();

        $urlset = $document->getElementsByTagName( 'urlset' )->item(0);

        $url = $document->createElement( 'url' );
        $url->appendChild( $document->createElement( 'loc', $home['loc'] ) );
        $url->appendChild( $document->createElement( 'lastmod', $home['lastmod'] ) );
        $url->appendChild( $document->createElement( 'changefreq', 'daily' ) );

        $urlset->appendChild( $url );

        return $document->saveXML();
    }

    /**
     * Retrieve the home url data.
     *
     * @since 1.0.0
     */
    private function getHome()
    {
        $args = [
            'numberposts'      => 1,
            'orderby'          => 'post_date',
            'order'            => 'DESC',
            'meta_key'         => '',
            'meta_value'       => '',
            'post_type'        => 'post',
            'post_status'      => 'publish',
            'suppress_filters' => true
        ];
        $post = wp_get_recent_posts( $args, OBJECT );

        $lastmod = isset( $post[0] ) ? $post[0]->post_date_gmt : current_time( 'mysql', true );

        return [
            'loc'     => get_home_url( get_current_blog_id(), '/' ),
            'lastmod' => date( DATE_W3C, strtotime( $lastmod ) )
        ];
    }
}
