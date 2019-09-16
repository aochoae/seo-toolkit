<?php
/**
 * @package Toolkit\Admin\Files
 */

namespace Toolkit\Admin\Files;

/**
 * Robots class
 *
 * @since 1.0.0
 */
class Robots extends AbstractFile
{
    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    public function getFile()
    {
        return trailingslashit( $this->getFilesystem()->abspath() ) . 'robots.txt';
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    public function getDefaultContents()
    {
        $public = get_option( 'blog_public' );

        $output = "User-agent: *\n";

        if ( '0' == $public ) {
            $output .= "Disallow: /\n";
        } else {
            $site_url = parse_url( site_url() );

            $path = ! empty( $site_url['path'] ) ? $site_url['path'] : '';

            $output .= "Disallow: $path/wp-admin/\n";
            $output .= "Allow: $path/wp-admin/admin-ajax.php\n";
        }

        return $output;
    }
}
