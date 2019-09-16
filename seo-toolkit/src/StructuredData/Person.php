<?php
/**
 * @package Toolkit\StructuredData
 */

namespace Toolkit\StructuredData;

/**
 * Person class
 *
 * @since 1.0.0
 */
class Person extends AbstractStructuredData
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
     * Person
     *
     * @since 1.0.0
     */
    public function json( $schema, $context )
    {
        /* Settings */
        $person = get_option( 'seo_toolkit_person', [] );

        $schema[] = $this->person( $person );

        return $schema;
    }

    /**
     * An person.
     *
     * @link https://schema.org/Person
     *
     * @since 1.0.0
     */
    private function person( $person )
    {
        $username = isset( $person['username'] ) ? $person['username'] : '';

        if ( empty( $username ) ) {
            return [];
        }

        $user = get_user_by( 'login', $username );

        if ( false === $user ) {
            return [];
        }

        $name = get_the_author_meta( 'display_name', $user->ID );

        $image = isset( $person['avatar'] ) ? $person['avatar'] : '';

        if ( empty( $image ) ) {
            $image = get_avatar_url( $user->ID, [ 'size' => 256 ] );
        }

        $schema = [
            '@type' => [ 'Person', 'Organization' ],
            '@id'   => $this->getId(),
            'name'  => $name,
            'url'   => esc_url( get_home_url( get_current_blog_id(), '/' ) ),
            'image' => [
                '@type' => 'ImageObject',
                '@id'   => esc_url( get_home_url( get_current_blog_id(), '/#image' ) ),
                'url'   => esc_url( $image )
            ],
            'logo' => [
                '@id' => esc_url( get_home_url( get_current_blog_id(), '/#image' ) )
            ]
        ];

        return $schema;
    }
}
