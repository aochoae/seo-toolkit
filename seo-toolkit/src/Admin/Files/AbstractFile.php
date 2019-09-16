<?php
/**
 * @package Toolkit\Admin\Files
 */

namespace Toolkit\Admin\Files;

/**
 * AbstractFile class
 *
 * @since 1.0.0
 */
abstract class AbstractFile
{
    /**
     * Retrieves the file absolute path.
     *
     * @since 1.0.0
     */
    abstract public function getFile();

    /**
     * Retrieves the WP_Filesystem object.
     *
     * @since 1.0.0
     */
    public function getFilesystem()
    {
        $credentials = request_filesystem_credentials( '' );

        if ( ! WP_Filesystem( $credentials ) ) {
            return false;
        }

        global $wp_filesystem;

        return $wp_filesystem;
    }

    /**
     * Retrieves the file contents.
     *
     * @since 1.0.0
     */
    public function getContents()
    {
        $filesystem = $this->getFilesystem();

        return $filesystem->get_contents( $this->getFile() );
    }

    /**
     * Retrieves the default contents if the file does not exists.
     *
     * @since 1.0.0
     */
    public function getDefaultContents()
    {
        return '';
    }

    /**
     * Write data to the file.
     *
     * @since 1.0.0
     */
    public function write( $content )
    {
        $filesystem = $this->getFilesystem();

        $filesystem->put_contents( $this->getFile(), $content, FS_CHMOD_FILE );

        return $content;
    }

    /**
     * Verifies whether the file exists.
     *
     * @since 1.0.0
     */
    public function exists()
    {
        return @file_exists( $this->getFile() );
    }
}
