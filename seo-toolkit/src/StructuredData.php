<?php
/**
 * @package Toolkit
 */

namespace Toolkit;

/**
 * StructuredData class
 *
 * @since 1.0.0
 */
class StructuredData
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var StructuredData
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        $profiles = [
            'profile',
            'searchbox',
            'article'
        ];

        foreach( $profiles as $profile ) {
            call_user_func( [ $this, $profile ] );
        }

        add_action( 'seo_toolkit_head', [ $this, 'schema' ], 15, 1 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return StructuredData
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new StructuredData();
        }

        return self::$instance;
    }

    /**
     * Implements the enabled structured data.
     *
     * @since 1.0.0
     */
    public function schema( $context )
    {
        $schema = apply_filters( 'seo_toolkit_schema', [], $context );

        if ( empty( $schema ) ) {
            return;
        }

        $schema = array_filter( $schema );

        $graph = [
            '@context' => 'https://schema.org',
            '@graph'   => [ $schema ]
        ];

        $output = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? JSON_PRETTY_PRINT : 0;

        printf( '<script type="application/ld+json">%s</script>' . PHP_EOL, json_encode( $graph, $output ) );
    }

    /**
     * @since 1.0.0
     */
    public function profile()
    {
        $website = get_option( 'seo_toolkit_website', [] );

        $profile = isset( $website['profile'] ) ? $website['profile'] : '';

        switch( $profile ) {
        case 'person':
            new \Toolkit\StructuredData\Person;
            break;
        case 'organization':
            new \Toolkit\StructuredData\Organization;
            break;
	    }
    }

    /**
     * @since 1.0.0
     */
    public function searchbox()
    {
        new \Toolkit\StructuredData\SearchBox;
    }

    /**
     * @since 1.0.0
     */
    public function article()
    {
        $website = get_option( 'seo_toolkit_website', [] );

        $profile = isset( $website['profile'] ) ? $website['profile'] : '';

        if ( in_array( $profile, [ 'person', 'organization' ] ) ) {
            new \Toolkit\StructuredData\Article;
        }
    }
}
