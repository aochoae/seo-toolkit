<?php
/**
 * @package Toolkit\Sitemaps
 */

namespace Toolkit\Sitemaps;

/**
 * Author class
 *
 * @since 1.0.0
 */
class Author extends AbstractSitemap
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
        $key = hash( 'sha384', serialize( [ 'sitemap-author', SEO_TOOLKIT_FILE, 'document' ] ) );

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
    public function getDocument()
    {
        $document = new \DOMDocument( '1.0', 'UTF-8' );
        $document->formatOutput = false;

        $xsl = plugins_url( 'static/css/sitemap.xsl', SEO_TOOLKIT_FILE );
        $xslt = $document->createProcessingInstruction( 'xml-stylesheet', "type=\"text/xsl\" href=\"{$xsl}\"" );
        $document->appendChild( $xslt );

        $root = $document->createElementNS( 'https://www.sitemaps.org/schemas/sitemap/0.9', 'urlset' );
        $document->appendChild( $root );

        $output = '';

        $posts = $this->getAuthors();

        $urlset = $document->getElementsByTagName( 'urlset' )->item(0);

        foreach( $posts as $post ) {
            $url = $document->createElement( 'url' );
            $url->appendChild( $document->createElement( 'loc', $post['loc'] ) );
            $url->appendChild( $document->createElement( 'changefreq', 'monthly' ) );
            $urlset->appendChild( $url );
        }

        $output = $document->saveXML();

        return $output;
    }

    /**
     * Retrieves the URLs of the authors.
     *
     * @since 1.0.0
     */
    public function getAuthors()
    {
        $args = [
            'fields'              => [ 'ID' ],
            'has_published_posts' => true
        ];
        $authors = get_users( $args );

        foreach( $authors as $author ) {
            yield [
                'loc'=> get_author_posts_url( $author->ID )
            ];
        }
    }
}
