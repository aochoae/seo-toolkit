<?php
/**
 * @package Toolkit\Admin\Settings
 */

namespace Toolkit\Admin\Settings;

/**
 * AbstractPage class
 *
 * @since 1.0.0
 */
abstract class AbstractPage
{
    /**
     * Displays the plugin settings page.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public abstract function render();

    /**
     * Displays the plugin information.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function information()
    {
        $plugin = get_plugin_data( SEO_TOOLKIT_FILE ); ?>

        <div class="plugin-information">

            <table class="form-table" aria-label="<?php esc_html_e( 'Plugin Information', 'seo-toolkit' ); ?>">
                <tr>
                <th scope="row"><?php esc_html_e( 'Name:', 'seo-toolkit' ); ?></th>
                    <td><?php echo esc_html( $plugin[ 'Name' ] ); ?></td>
                </tr>
                <tr>
                <th scope="row"><?php esc_html_e( 'Version:', 'seo-toolkit' ); ?></th>
                    <td><?php echo esc_html( $plugin[ 'Version' ] ); ?></td>
                </tr>
                <tr>
                <th scope="row"><?php esc_html_e( 'License:', 'seo-toolkit' ); ?></th>
                    <td><?php echo '<a href="https://spdx.org/licenses/GPL-2.0-or-later.html">GPL-2.0-or-later</a>'; ?></td>
                </tr>
            </table>

        </div>

        <?php
    }

    /**
     * Registers and configures the admin screen options.
     *
     * @since 1.0.0
     */
    public function screen()
    {
        add_screen_option( 'layout_columns', [
            'max'     => 2,
            'default' => 2
        ] );
    }

    /**
     * Enqueues the admin stylesheets and scripts for Tabs functionality.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue()
    {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_style( 'seo-toolkit-admin-style',
            plugins_url( "static/css/settings$suffix.css", SEO_TOOLKIT_FILE ),
            [],
            null,
            'all'
        );

        wp_enqueue_script( 'seo-toolkit-admin-script',
            plugins_url( "static/js/settings$suffix.js", SEO_TOOLKIT_FILE ),
            [ 'jquery-ui-tabs', 'postbox' ],
            null,
            true
        );
    }

    /**
     * Add a jQuery function to make collapsible metaboxes.
     *
     * @since 1.0.0
     */
    public function footer()
    {
        ?>
        <script>
            jQuery(document).ready(function() {
                postboxes.add_postbox_toggles(pagenow);
            });
        </script>
        <?php
    }
}
