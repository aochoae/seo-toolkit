<?php
/**
 * @package Toolkit\StructuredData
 */

namespace Toolkit\StructuredData;

/**
 * Organization class
 *
 * @since 1.0.0
 */
class Organization extends AbstractStructuredData
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_filter( 'seo_toolkit_schema', [ $this, 'json' ], 5, 2 );
    }

    /**
     * Organization
     *
     * @since 1.0.0
     */
    public function json( $schema, $context )
    {
        /* Settings */
        $organization = get_option( 'seo_toolkit_organization', [] );

        $schema[] = $this->organization( $organization );

        return $schema;
    }

    /**
     * An organization such as a school, NGO, corporation, club, etc.
     *
     * @link https://schema.org/Organization
     *
     * @since 1.0.0
     */
    private function organization( $organization )
    {
        $name = isset( $organization['name'] ) ? $organization['name'] : '';

        if ( empty( $name ) ) {
            $name = get_bloginfo( 'name', 'display' );
        }

        $schema = [
            '@type' => 'Organization',
            '@id'   => $this->getId(),
            'name'  => $name,
            'url'   => esc_url( get_home_url( get_current_blog_id(), '/' ) )
        ];

        /* Logo */
        if ( $logo = $this->logo( $organization ) ) {
            $schema += [ 'logo' => esc_url( $logo ) ];
        }

        return $schema;
    }

    /**
     * URL of a logo that is representative of the organization.
     *
     * @link https://developers.google.com/search/docs/data-types/logo
     * @link https://schema.org/logo
     *
     * @since 1.0.0
     */
    private function logo( $organization )
    {
        return isset( $organization['logo'] ) ? $organization['logo'] : '';
    }
}
