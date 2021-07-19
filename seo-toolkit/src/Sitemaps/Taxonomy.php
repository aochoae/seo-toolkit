<?php
/**
 * @package Toolkit\Sitemaps
 */

namespace Toolkit\Sitemaps;

/**
 * Taxonomy class
 *
 * @since 1.0.0
 */
class Taxonomy extends AbstractSitemap
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct( $taxonomy )
    {
        $this->taxonomy = esc_sql( $taxonomy );
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    public function getSitemap()
    {
        $key = hash( 'sha384', serialize( [ "sitemap-{$this->taxonomy}", SEO_TOOLKIT_FILE, 'document' ] ) );

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

        $root = $document->createElementNS( 'http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset' );
        $document->appendChild( $root );

        $taxonomies = $this->getTaxonomies();

        $urlset = $document->getElementsByTagName( 'urlset' )->item(0);

        foreach( $taxonomies as $taxonomy ) {
            $url = $document->createElement( 'url' );
            $url->appendChild( $document->createElement( 'loc', $taxonomy['loc'] ) );
            $url->appendChild( $document->createElement( 'changefreq', 'monthly' ) );
            $urlset->appendChild( $url );
        }

        return $document->saveXML();
    }

    /**
     * Retrieves the public taxonomies.
     *
     * @since 1.0.0
     */
    private function getTaxonomies()
    {
        $terms = get_terms( [
            'exclude'    => $this->getExcludeTaxonomies(),
            'taxonomy'   => $this->taxonomy,
            'hide_empty' => true
        ] );

        foreach( $terms as $term ) {
            yield [
                'loc'=> get_term_link( $term->term_id )
            ];
        }
    }

    /**
     * Retrieve the taxonomy ids that should not be included in the sitemap.
     *
     * @since 1.0.0
     */
    private function getExcludeTaxonomies()
    {
        $key = hash( 'sha384', serialize( [ "sitemap-{$this->taxonomy}", SEO_TOOLKIT_FILE, 'database' ] ) );

        if ( false === ( $ids = wp_cache_get( $key, 'seo_toolkit' ) ) ) {

            global $wpdb;

            $sql = "SELECT
                        `{$wpdb->prefix}terms`.`term_id`
                    FROM
                        `{$wpdb->prefix}terms`,
                        `{$wpdb->prefix}termmeta`,
                        `{$wpdb->prefix}term_taxonomy`
                    WHERE
                        `{$wpdb->prefix}terms`.`term_id` = `{$wpdb->prefix}termmeta`.`term_id`
                    AND
                        `{$wpdb->prefix}termmeta`.`meta_key` LIKE '_seo_toolkit_robots'
                    AND
                        `{$wpdb->prefix}termmeta`.`meta_value` LIKE '%noindex%'
                    AND
                        `{$wpdb->prefix}terms`.`term_id` = `{$wpdb->prefix}term_taxonomy`.`term_id`
                    AND
                        `{$wpdb->prefix}term_taxonomy`.`taxonomy` LIKE '{$this->taxonomy}';";

            $ids = $wpdb->get_results( $sql, OBJECT_K );

            $ids = array_keys( $ids );

            wp_cache_set( $key, $ids, 'seo_toolkit', DAY_IN_SECONDS );
        }

        return apply_filters( "seo_toolkit_sitemap_{$this->taxonomy}_exclude", $ids );
    }
}
