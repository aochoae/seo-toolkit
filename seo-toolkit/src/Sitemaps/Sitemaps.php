<?php
/**
 * @package Toolkit\Sitemaps
 */

namespace Toolkit\Sitemaps;

/**
 * Sitemaps class
 *
 * @since 1.0.0
 */
class Sitemaps extends AbstractSitemap
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct( array $sitemaps )
    {
        if ( $key = array_search( 'index', $sitemaps ) ) {
            unset( $sitemaps[ $key ] );
        }

        $this->sitemaps = $sitemaps;
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    public function getSitemap()
    {
        $key = hash( 'sha384', serialize( [ 'sitemap-index', SEO_TOOLKIT_FILE, 'document' ] ) );

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

        $root = $document->createElementNS( 'http://www.sitemaps.org/schemas/sitemap/0.9', 'sitemapindex' );
        $document->appendChild( $root );

        $home_url = get_home_url( get_current_blog_id() );

        foreach( $this->sitemaps as $sitemap ) {
            $sitemapindex = $document->getElementsByTagName( 'sitemapindex' )->item(0);
            $element = $document->createElement( 'sitemap' );
            $element->appendChild( $document->createElement( 'loc', "$home_url/sitemap-$sitemap.xml" ) );
            $sitemapindex->appendChild( $element );
        }

        return $document->saveXML();
    }
}
