<?php
/**
 * @package Toolkit\Sitemaps
 */

namespace Toolkit\Sitemaps;

/**
 * Post class
 *
 * @since 1.0.0
 */
class Post extends AbstractSitemap
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct( $post_type )
    {
        $this->post_type = esc_sql( $post_type );
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    public function getSitemap()
    {
        $key = hash( 'sha384', serialize( [ "sitemap-{$this->post_type}", SEO_TOOLKIT_FILE, 'document' ] ) );

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

        $root = $document->createElementNS(
            'http://www.sitemaps.org/schemas/sitemap/0.9',
            'urlset'
        );
        $document->appendChild( $root );

        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:image',
            'http://www.google.com/schemas/sitemap-image/1.1'
        );

        $posts = $this->getPosts();

        $urlset = $document->getElementsByTagName( 'urlset' )->item(0);

        foreach( $posts as $post ) {

            $url = $document->createElement( 'url' );

            $url->appendChild( $document->createElement( 'loc',        $post['loc']        ) );
            $url->appendChild( $document->createElement( 'lastmod',    $post['lastmod']    ) );
            $url->appendChild( $document->createElement( 'changefreq', $post['changefreq'] ) );

            if ( isset( $post['images'] ) && ! empty( $post['images'] ) ) {
                foreach( $post['images'] as $idx => $image ) {
                    $images = $url->appendChild( $document->createElement( 'image:image' ) );
                    $images->appendChild( $document->createElement( 'image:loc', $image['loc'] ) );
                }
            }

            $urlset->appendChild( $url );
        }

        return $document->saveXML();
    }

    /**
     * Retrieve the posts data.
     *
     * @since 1.0.0
     */
    private function getPosts()
    {
        $posts = $this->getPostIds();

        foreach( $posts as $post ) {

            $data = [
                'loc'        => get_permalink( $post->post_id ),
                'lastmod'    => $post->lastmod,
                'changefreq' => absint( current_time( 'timestamp', true ) - strtotime( $post->lastmod ) ) > 31556952 ? 'yearly' : 'daily'
            ];

            yield apply_filters( "seo_toolkit_sitemap_{$this->post_type}", $data, $post->post_id );
        }
    }

    /**
     * Retrieve the posts.
     *
     * @since 1.0.0
     */
    private function getPostIds()
    {
        $key = hash( 'sha384', serialize( [ "sitemap-{$this->post_type}", SEO_TOOLKIT_FILE, 'database' ] ) );

        if ( false === ( $ids = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            global $wpdb;

            $limit = esc_sql( get_option( 'seo_toolkit_sitemaps_limit', 1000 ) );

            $sql = "SELECT DISTINCT
                        `ID` as post_id,
                        DATE_FORMAT(`post_modified_gmt`, '%Y-%m-%dT%H:%i:%SZ') as lastmod
                    FROM
                        `{$wpdb->posts}`
                    WHERE
                        `post_type` LIKE '{$this->post_type}'
                    AND
                        `post_status` LIKE 'publish'
                    AND
                        `post_password` = ''
                    AND
                        `ID` NOT IN (
                            SELECT
                                `post_id`
                            FROM
                                `{$wpdb->postmeta}`
                            WHERE
                                `meta_key` LIKE '_seo_toolkit_robots'
                            AND
                                `meta_value` LIKE '%noindex%'
                        )
                    ORDER BY `post_modified_gmt` DESC LIMIT {$limit};";

            $ids = $wpdb->get_results( $sql, OBJECT_K );

            wp_cache_set( $key, $ids, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return apply_filters( "seo_toolkit_sitemap_{$this->post_type}_ids", $ids );
    }
}
