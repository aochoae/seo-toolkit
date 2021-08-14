<?php
/**
 * @package Toolkit\Admin\Posts
 */

namespace Toolkit\Admin\Posts;

/**
 * Facebook class
 *
 * @since 1.0.0
 */
class Facebook
{
    /**
     * Singleton instance.
     *
     * @since 1.0.0
     * @var Facebook
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'seo_toolkit_posts_tabs', [ $this, 'panel' ], 10, 3 );

        add_action( 'seo_toolkit_posts_tabs_content', [ $this, 'content' ], 10, 2 );

        add_action( 'save_post', [ $this, 'update' ], 10, 2 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Facebook
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Facebook();
        }

        return self::$instance;
    }

    /**
     * Adds Facebook tab.
     *
     * @since 1.0.0
     */
    public function panel( $tabs, $post_type, $post_id )
    {
        $facebook = [
            'facebook' => [
                'label' => __( 'Facebook', 'seo-toolkit' ),
                'panel' => 'toolbox-facebook'
            ]
        ];

        return $tabs + $facebook;
    }

    /**
     * Displays the Facebook panel.
     *
     * @since 1.0.0
     */
    public function content( $post_type, $post_id )
    {
        wp_nonce_field( "seo-toolkit-facebook-{$post_id}-save", "seo-toolkit-facebook-{$post_id}-nonce" ); ?>

        <div id="toolbox-facebook" class="seo-toolkit-panel">

            <?php $title = get_post_meta( $post_id, '_seo_toolkit_facebook_title', true ); ?>
            <div class="form-field">
                <label class="title" for="_seo_toolkit_facebook_title"><?php esc_html_e( 'Facebook Title', 'seo-toolkit' ); ?></label>
                <input name="_seo_toolkit_facebook_title" id="_seo_toolkit_facebook_title" value="<?php echo $title; ?>" type="text">
            </div>

            <?php $description = get_post_meta( $post_id, '_seo_toolkit_facebook_description', true ); ?>
            <div class="form-field">
                <label class="title" for="_seo_toolkit_facebook_description"><?php esc_html_e( 'Facebook Description', 'seo-toolkit' ); ?></label>
                <textarea id="_seo_toolkit_facebook_description" name="_seo_toolkit_facebook_description" rows="5" cols="80"><?php echo $description; ?></textarea>
            </div>

            <?php $image = get_post_meta( $post_id, '_seo_toolkit_facebook_image', true ); ?>
            <div class="form-field">
                <label class="title" for="_seo_toolkit_facebook_image"><?php esc_html_e( 'Facebook Image', 'seo-toolkit' ); ?></label>
                <input name="_seo_toolkit_facebook_image" id="_seo_toolkit_facebook_image" value="<?php echo $image; ?>" type="text">
                <input id="seo_toolkit_facebook_button" type="button" class="button" value="<?php esc_html_e( 'Upload Image', 'seo-toolkit' ); ?>" />
                <p class="description">
                <?php esc_html_e( 'Use images that are at least 1080 pixels in width for best display on high resolution devices. At the minimum, you should use images that are 600 pixels.', 'seo-toolkit' ); ?>
                </p>
            </div>

        </div>

        <?php
    }

    /**
     * Save meta box data.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function update( $post_id, $post )
    {
        /* Check if the nonce is set */
        $nonce = filter_input( INPUT_POST, "seo-toolkit-facebook-{$post_id}-nonce", FILTER_SANITIZE_STRING );

        /* Verify that the nonce is valid */
        if ( ! wp_verify_nonce( $nonce, "seo-toolkit-facebook-{$post_id}-save" ) ) {
            return;
        }

        /* Don't update if running an autosave */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        /* Don't update if the post is a revision or an autosave */
        if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) {
            return;
        }

        $metadata = [
            '_seo_toolkit_facebook_title'       => FILTER_SANITIZE_STRING,
            '_seo_toolkit_facebook_description' => FILTER_SANITIZE_STRING,
            '_seo_toolkit_facebook_image'       => FILTER_VALIDATE_URL
        ];

        $metadata = filter_input_array( INPUT_POST, $metadata );

        foreach ( $metadata as $meta => $new_value ) {

            $value = get_post_meta( $post_id, $meta, true );

            if ( ! empty( $new_value ) && $new_value != $value ) {
                update_post_meta( $post_id, $meta, $new_value );
            } elseif ( empty( $new_value ) && $value ) {
                delete_post_meta( $post_id, $meta );
            }
        }
    }
}
