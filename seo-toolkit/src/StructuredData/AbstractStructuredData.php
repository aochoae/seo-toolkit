<?php
/**
 * @package Toolkit
 */

namespace Toolkit\StructuredData;

/**
 * AbstractStructuredData class
 *
 * @since 1.0.0
 */
abstract class AbstractStructuredData
{
    /**
     * Filters the JSON for Linking Data.
     *
     * @since 1.0.0
     */
    public abstract function json( $schema, $context );

    /**
     * Generate an ID according to the website profile.
     *
     * @link https://www.w3.org/TR/json-ld/
     * @link https://www.w3.org/TR/json-ld/#node-identifiers
     *
     * @since 1.0.0
     */
    public function getId()
    {
        $website = get_option( 'seo_toolkit_website', '' );

        $profile = isset( $website['profile'] ) ? $website['profile'] : '';

        $id = '';

        switch( $profile ) {
            case 'organization':
                $id = get_home_url( get_current_blog_id(), '/#organization' );
                break;
            case 'person':
                $id = get_home_url( get_current_blog_id(), '/#person' );
                break;
        }

        return esc_url_raw( $id );
    }
}
