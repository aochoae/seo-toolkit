<?php
/**
 * Plugin Name: SEO Toolkit
 * Plugin URI: https://github.com/seo-toolkit
 * Description: SEO Toolkit is a smart plugin that assistances you to optimize your website for purposes of SEO with easy.
 * Author: SEO Toolkit
 * Author URI: https://www.seo-toolkit.page/
 * Version: 1.0.0
 * License: GNU General Public License v2 or later
 * License URI: https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain: seo-toolkit
 * Domain Path: /languages
 *
 * WC requires at least: 3.6
 * WC tested up to: 3.7
 *
 * @package   SeoToolkit
 * @author    Luis A. Ochoa
 * @copyright 2019 Luis A. Ochoa
 * @license   GPL-2.0-or-later
 */

/* Define SEO_TOOLKIT_FILE */
if ( ! defined( 'SEO_TOOLKIT_FILE' ) ) {
    define( 'SEO_TOOLKIT_FILE', __FILE__ );
}

/* PHP namespace autoloader */
require_once( dirname( SEO_TOOLKIT_FILE ) . '/vendor/autoload.php' );

\Toolkit\Loader::newInstance( plugin_basename( SEO_TOOLKIT_FILE ) );
