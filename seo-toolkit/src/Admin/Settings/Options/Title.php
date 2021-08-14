<?php
/**
 * @package Toolkit\Admin\Options
 */

namespace Toolkit\Admin\Settings\Options;

/**
 * Title class
 *
 * @since 1.0.0
 */
class Title
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var Title
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
     * @return Title
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Title;
        }

        return self::$instance;
    }

    /**
     * Retrieve the options for title tag.
     *
     * @since 1.0.0
     */
    public function getFormat( $area )
    {
        $format = [];

        $format['frontpage'] = [
            '%site-title% %separator% %tagline%',
            '%site-title%'
        ];

        $format['blog'] = [
            '%title% %separator% %site-title%',
            '%title% %separator% %tagline%',
            '%title%'
        ];

        $format['author'] = [
            '%author% %separator% %site-title%',
            '%author%'
        ];

        $format['search'] = [
            '%search% %separator% %site-title%',
            '%search%'
        ];

        $format['error'] = [
            '%error% %separator% %site-title%',
            '%error%'
        ];

        $format['default'] = [
            '%title% %separator% %site-title%',
            '%title%'
        ];

        $format = apply_filters( 'seo_toolkit_title_formats', $format );

        $_area = isset( $format[ $area ] ) ? $format[ $area ] : $format[ 'default' ];

        return $this->getOptions( $_area );
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

            $auxiliary = [];

            foreach( $options as $option ) {
                $auxiliary[] = $this->getString( $option );
            }

            $return[ $key ] = join( ' ', $auxiliary );
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
            '%default%'    => esc_html__( 'Default',    'seo-toolkit' ),
            '%site-title%' => esc_html__( 'Site title', 'seo-toolkit' ),
            '%tagline%'    => esc_html__( 'Tagline',    'seo-toolkit' ),
            '%separator%'  => esc_html__( 'Separator',  'seo-toolkit' ),
            '%title%'      => esc_html__( 'Title',      'seo-toolkit' ),
            '%author%'     => esc_html__( 'Author',     'seo-toolkit' ),
            '%search%'     => esc_html__( 'Search',     'seo-toolkit' ),
            '%error%'      => esc_html__( 'Error 404',  'seo-toolkit' )
        ];

        $options = apply_filters( 'seo_toolkit_title_strings', $options );

        return $options[ $key ];
    }
}
