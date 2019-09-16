<?php
/**
 * @package Toolkit\StructuredData
 */

namespace Toolkit\StructuredData;

/**
 * SearchBox class
 *
 * @since 1.0.0
 */
class SearchBox extends AbstractStructuredData
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_filter( 'seo_toolkit_schema', [ $this, 'json' ], 10, 2 );
    }

    /**
     * Sitelinks Searchbox
     *
     * @link https://developers.google.com/search/docs/data-types/sitelinks-searchbox
     *
     * @since 1.0.0
     */
    public function json( $schema, $context )
    {
        $url = get_home_url( get_current_blog_id(), '/' );

        $target = sprintf( '%s?s={search_term_string}', esc_url( $url ) );

        $searchbox = [
            '@type'    => 'WebSite',
            'url'      => esc_url( $url ),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => $target,
                'query-input' => 'required name=search_term_string'
            ]
        ];

        $schema[] = $searchbox;

        return $schema;
    }
}
