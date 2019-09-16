<?php
/**
 * @package Toolkit\Sitemaps
 */

namespace Toolkit\Sitemaps;

/**
 * AbstractSitemap class
 *
 * @since 1.0.0
 */
abstract class AbstractSitemap
{
    /**
     * Retrieves a sitemap.
     *
     * @since 1.0.0
     */
    abstract public function getSitemap();
}
