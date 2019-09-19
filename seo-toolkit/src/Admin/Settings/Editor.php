<?php
/**
 * @package Toolkit\Admin\Settings
 */

namespace Toolkit\Admin\Settings;

/**
 * Editor class
 *
 * @since 1.0.0
 */
class Editor extends AbstractPage
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
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Editor
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Editor;
        }

        return self::$instance;
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

            <h1 class="wp-heading-inline"><?php esc_html_e( 'Editor', 'seo-toolkit' ); ?></h1>

            <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">

                <input type="hidden" name="action" value="robotstxt">
                <?php wp_nonce_field( 'seo-toolbox-edit', '_nonce' ); ?>

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
     * Adds a meta box to Editor screen.
     *
     * @since 1.0.0
     */
    public function metaboxes()
    {
        $screen = get_current_screen();

        /* Edit robots.txt file */
        add_meta_box( 'robotstxt', esc_html__( 'Edit robots.txt file', 'seo-toolkit' ), [ $this, 'robotstxt' ], $screen, 'normal', 'high' );

        /* View details */
        add_meta_box( 'information', esc_html__( 'Information', 'seo-toolkit' ), [ $this, 'information' ], $screen, 'side', 'low' );
    }

    /**
     * Displays the 'Edit robots.txt file' meta box.
     *
     * @since 1.0.0
     */
    public function robotstxt()
    {
        $file = new \Toolkit\Admin\Files\Robots;

        $content = $file->exists() ? $file->getContents() : $file->getDefaultContents();

        if ( ! $file->exists() ) {

            $message = __( 'The robots.txt file does not exist. The file will be created when you save the changes.', 'seo-toolkit' );

            printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', esc_html( $message ) );
        }

        if ( true == filter_input( INPUT_GET, 'saved', FILTER_VALIDATE_BOOLEAN ) ) {

            $message = __( 'The robots.txt file has been saved.', 'seo-toolkit' );

            printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $message ) );
        }
        ?>
        <div id="submitpost" class="submitbox">

            <div id="minor-publishing">

                <textarea name="robotstxt" rows="10" cols="50"><?php echo esc_textarea( $content ); ?></textarea>

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
}
