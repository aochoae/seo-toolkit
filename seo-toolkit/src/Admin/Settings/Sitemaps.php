<?php
/**
 * @package Toolkit\Admin\Settings
 */

namespace Toolkit\Admin\Settings;

/**
 * Sitemaps class
 *
 * @since 1.0.0
 */
class Sitemaps extends AbstractPage
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_action( 'admin_init', [ $this, 'rewriteRules' ] );

        add_action( 'admin_init', [ $this, 'settings' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Sitemaps
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Sitemaps();
        }

        return self::$instance;
    }

    /**
     * Recreates rewrite rules.
     *
     * @since 1.0.0
     */
    public function rewriteRules()
    {
        flush_rewrite_rules( false );
    }

    /**
     * Register the plugin settings.
     *
     * @since 1.0.0
     */
    public function settings()
    {
        $options = [
            'seo_toolkit_sitemaps_enabled' => [
                'type'              => 'boolean',
                'show_in_rest'      => false,
                'default'           => true
            ],
            'seo_toolkit_sitemaps_limit' => [
                'type'              => 'number',
                'show_in_rest'      => false,
                'default'           => 1000
            ],
            'seo_toolkit_sitemaps_images_enable' => [
                'type'              => 'boolean',
                'show_in_rest'      => false,
                'default'           => true
            ]
        ];

        foreach( $options as $option => $args ) {
            register_setting( 'seo_toolkit_sitemaps', $option, $args );
        }
    }

    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    public function render()
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $columns = get_current_screen()->get_columns(); ?>

        <div class="wrap">

            <?php settings_errors(); ?>

            <h1 class="wp-heading-inline"><?php esc_html_e( 'XML Sitemaps', 'seo-toolkit' ); ?></h1>

            <form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">

                <?php settings_fields( 'seo_toolkit_sitemaps' ); ?>

                <?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
                <?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

                <div id="poststuff">

                    <div id="post-body" class="metabox-holder columns-<?= $columns ?>">

                        <div id="postbox-container-1" class="postbox-container">
                            <?php do_meta_boxes( get_current_screen(), 'side', null ); ?>
                        </div>

                        <div id="postbox-container-2" class="postbox-container">
                            <?php do_meta_boxes( get_current_screen(), 'normal', null ); ?>
                            <?php do_meta_boxes( get_current_screen(), 'advanced', null ); ?>
                        </div>

                    </div>

                </div>

            </form>

        </div>

        <?php
    }

    /**
     * Adds a meta box to Sitemaps screen.
     *
     * @since 1.0.0
     */
    public function metaboxes()
    {
        $screen = get_current_screen();

        /* Sitemaps */
        add_meta_box( 'sitemaps', esc_html__( 'Sitemaps', 'seo-toolkit' ), [ $this, 'sitemaps' ], $screen, 'normal', 'high' );

        /* Actions */
        add_meta_box( 'actions', esc_html__( 'Actions', 'seo-toolkit' ), [ $this, 'actions' ], $screen, 'side', 'high' );

        /* View details */
        add_meta_box( 'information', esc_html__( 'Information', 'seo-toolkit' ), [ $this, 'information' ], $screen, 'side', 'low' );
    }

    /**
     * Displays the 'Sitemaps' meta box.
     *
     * @since 1.0.0
     */
    public function sitemaps()
    {
        $data = [
            'sitemaps' => [
                'label' => __( 'General', 'seo-toolkit' ),
                'panel' => 'seo-toolbox-general'
            ]
        ];
        ?>

        <div id="submitpost" class="submitbox">

            <div id="minor-publishing">

                <div id="seo-toolkit-sitemaps" class="seo-toolkit-tabs seo-toolkit-tabs-container">

                    <ul class="tabs tabs-nav">
                    <?php foreach( $data as $key => $tab ) :  ?>
                        <li><a href="#<?php echo esc_attr( $tab[ 'panel' ] ); ?>"><?php echo esc_html( $tab[ 'label' ] ); ?></a></li>
                    <?php endforeach; ?>
                    </ul>

                    <div id="seo-toolbox-general" class="seo-toolkit-panel">

                        <?php $sitemaps_enabled = (bool) get_option( 'seo_toolkit_sitemaps_enabled', true ); ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_sitemaps_enabled">
                                <?php esc_html_e( 'Enable', 'seo-toolkit' ); ?>
                            </label>
                            <label class="switch">
                                <input name="seo_toolkit_sitemaps_enabled" id="seo_toolkit_sitemaps_enabled"
                                    class="toggle" value="true" <?php checked( $sitemaps_enabled, true ); ?> type="checkbox">
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <?php $limit = get_option( 'seo_toolkit_sitemaps_limit', 1000 ); ?>
                        <div class="form-field">
                            <label for="seo_toolkit_sitemaps_limit" class="title"><?php esc_html_e( 'Maximum URLs per sitemap', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_sitemaps_limit" id="seo_toolkit_sitemaps_limit"
                                value="<?php echo esc_attr( $limit ); ?>" type="number" min="1" max="5000">
                        </div>

                        <?php $images = (bool) get_option( 'seo_toolkit_sitemaps_images_enable', true ); ?>
                        <div class="form-field">
                            <label for="seo_toolkit_sitemaps_images_enable" class="title"><?php esc_html_e( 'Images publishing', 'seo-toolkit' ); ?></label>
                            <label for="seo_toolkit_sitemaps_images_enable">
                                <input name="seo_toolkit_sitemaps_images_enable" id="seo_toolkit_sitemaps_images_enable"
                                    value="true" <?php checked( $images, true ); ?> type="checkbox">
                                <?php esc_html_e( 'Your images can be found in Image Search results.', 'seo-toolkit' ); ?>
                            </label>
                        </div>

                    </div>

                </div>

            </div>

            <div id="major-publishing-actions">

                <div id="publishing-action">
                    <?php submit_button( null, 'primary', 'submit', false ); ?>
                </div>

                <div class="clear"></div>

            </div>

        </div>

        <?php
    }

    /**
     * Displays the 'Actions' meta box.
     *
     * @since 1.0.0
     */
    public function actions()
    {
        $sitemap = get_home_url( get_current_blog_id(), 'sitemap.xml' ); ?>

        <div id="submitpost" class="submitbox">

            <div id="major-publishing-actions">

                <div id="delete-action">
                    <a href="<?php echo esc_url( $sitemap ); ?>" target="_blank"><?php esc_html_e( 'View XML Sitemap', 'seo-toolkit' ); ?></a>
                </div>

                <div id="publishing-action">
                    <a href="#" class="seo-toolkit-ping button"><?php esc_html_e( 'Send', 'seo-toolkit' ); ?></a>
                </div>

                <div class="clear"></div>

            </div>

        </div>

        <?php
    }

    /**
     * Enqueues the admin scripts.
     *
     * @since 1.0.0
     */
    public function scripts()
    {
        $l10n = [
            'nonce' => wp_create_nonce( 'seo-toolkit-sitemaps-ping' )
        ];
        wp_localize_script( 'seo-toolkit-sitemaps', 'sitemaps_ping', $l10n );

        wp_enqueue_script( 'seo-toolkit-sitemaps' );
    }
}
