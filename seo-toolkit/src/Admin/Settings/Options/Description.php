<?php
/**
 * @package Toolkit\Admin\Options
 */

namespace Toolkit\Admin\Settings\Options;

/**
 * Description class
 *
 * @since 1.0.0
 */
class Description
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Description
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Description
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Description;
        }

        return self::$instance;
    }

    /**
     * Retrieve the options for description meta tag.
     *
     * @since 1.0.0
     */
    public function getFormat( $context )
    {
        $format = [];

        $format['frontpage'] = [
            '%default%',
            '%tagline%',
            '%none%'
        ];

        $format['blog'] = [
            '%tagline%',
            '%excerpt%',
            '%none%'
        ];

        $format['post'] = [
            '%excerpt%',
            '%none%'
        ];

        $format['page'] = [
            '%excerpt%',
            '%none%'
        ];

        $format['attachment'] = [
            '%excerpt%',
            '%none%'
        ];

        $format['category'] = [
            '%description%',
            '%none%'
        ];

        $format['post_tag'] = [
            '%description%',
            '%none%'
        ];

        $format['author'] = [
            '%biography%',
            '%none%'
        ];

        $format = apply_filters( 'seo_toolkit_description_format', $format, $context );

        $option = isset( $format[ $context ] ) ? $format[ $context ] : [ '%none%' ];

        return $this->getOptions( $option );
    }

    /**
     * Retrieves an option.
     *
     * @since 1.0.0
     */
    private function getOptions( $keys )
    {
        $return = [];

        foreach( $keys as $key ) {

            $options = explode( " ", $key );

            $auxiliar = [];

            foreach( $options as $option ) {
                $auxiliar[] = $this->getString( $option );
            }

            $return[ $key ] = join( ' ', $auxiliar );
        }

        return $return;
    }

    /**
     * Sets a string to display instead of option.
     *
     * @since 1.0.0
     */
    private function getString( $key )
    {
        $options = [
            '%default%'     => __( 'Default',           'seo-toolkit' ),
            '%site-title%'  => __( 'Site title',        'seo-toolkit' ),
            '%tagline%'     => __( 'Tagline',           'seo-toolkit' ),
            '%separator%'   => __( 'Separator',         'seo-toolkit' ),
            '%title%'       => __( 'Title',             'seo-toolkit' ),
            '%excerpt%'     => __( 'Excerpt',           'seo-toolkit' ),
            '%description%' => __( 'Description',       'seo-toolkit' ),
            '%biography%'   => __( 'Biographical info', 'seo-toolkit' ),
            '%search%'      => __( 'Search',            'seo-toolkit' ),
            '%error%'       => __( 'Error 404',         'seo-toolkit' ),
            '%none%'        => __( 'None',              'seo-toolkit' )
        ];

        $options = apply_filters( 'seo_toolkit_description_strings', $options );

        return $options[ $key ];
    }
}
