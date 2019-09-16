<?php
/**
 * @package Toolkit\Admin\Posts
 */

namespace Toolkit\Admin\Posts;

/**
 * Twitter class
 *
 * @since 1.0.0
 */
class Twitter
{
    /**
     * Singleton instance.
     *
     * @since 1.0.0
     * @var Twitter
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
     * @return Twitter
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Twitter;
        }

        return self::$instance;
    }

    /**
     * Adds Twitter tab.
     *
     * @since 1.0.0
     */
    public function panel( $tabs, $post_type, $post_id )
    {
        $twitter = [
            'twitter' => [
                'label' => __( 'Twitter', 'seo-toolkit' ),
                'panel' => 'toolbox-twitter'
            ]
        ];

        return $tabs + $twitter;
    }

    /**
     * Displays the Twitter panel.
     *
     * @since 1.0.0
     */
    public function content( $post_type, $post_id )
    {
        wp_nonce_field( "seo-toolkit-twitter-{$post_id}-save", "seo-toolkit-twitter-{$post_id}-nonce" ); ?>

        <div id="toolbox-twitter" class="seo-toolkit-panel">

            <?php $twitter_card = get_post_meta( $post_id, '_seo_toolkit_twitter_card', true ); ?>
            <div class="form-field">
                <label class="title" for="_seo_toolkit_twitter_card"><?php esc_html_e( 'Twitter Cards', 'seo-toolkit' ); ?></label>
                <select id="_seo_toolkit_twitter_card" name="_seo_toolkit_twitter_card">
                    <option <?php selected( $twitter_card, 'default' ); ?> value="default">
                        <?php esc_html_e( 'Default', 'seo-toolkit' ); ?>
                    </option>
                    <option <?php selected( $twitter_card, 'summary' ); ?> value="summary">
                        <?php esc_html_e( 'Summary Card', 'seo-toolkit' ); ?>
                    </option>
                    <option <?php selected( $twitter_card, 'summary_large_image' ); ?> value="summary_large_image">
                        <?php esc_html_e( 'Summary Card with Large Image', 'seo-toolkit' ); ?>
                    </option>
                </select>
            </div>

            <?php $title = get_post_meta( $post_id, '_seo_toolkit_twitter_title', true ); ?>
            <div class="form-field">
                <label class="title" for="_seo_toolkit_twitter_title"><?php esc_html_e( 'Twitter Title', 'seo-toolkit' ); ?></label>
                <input name="_seo_toolkit_twitter_title" id="_seo_toolkit_twitter_title" value="<?php echo $title; ?>" type="text">
            </div>

            <?php $description = get_post_meta( $post_id, '_seo_toolkit_twitter_description', true ); ?>
            <div class="form-field">
                <label class="title" for="_seo_toolkit_twitter_description"><?php esc_html_e( 'Twitter Description', 'seo-toolkit' ); ?></label>
                <textarea id="_seo_toolkit_twitter_description" name="_seo_toolkit_twitter_description" rows="5" cols="80"><?php echo $description; ?></textarea>
            </div>

            <?php $image = get_post_meta( $post_id, '_seo_toolkit_twitter_image', true ); ?>
            <div class="form-field">
                <label class="title" for="_seo_toolkit_twitter_image"><?php esc_html_e( 'Twitter Image', 'seo-toolkit' ); ?></label>
                <input name="_seo_toolkit_twitter_image" id="_seo_toolkit_twitter_image" value="<?php echo $image; ?>" type="text">
                <input id="seo_toolkit_twitter_button" type="button" class="button" value="<?php esc_html_e( 'Upload Image', 'seo-toolkit' ); ?>" />
                <p class="description">
                <?php esc_html_e( 'URL of image to use in the card. Images must be less than 5MB in size. JPG, PNG, WEBP and GIF formats are supported.', 'seo-toolkit' ); ?></p>
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
        $nonce = filter_input( INPUT_POST, "seo-toolkit-twitter-{$post_id}-nonce", FILTER_SANITIZE_STRING );

        if ( empty( $nonce ) ) {
            return;
        }

        /* Verify that the nonce is valid */
        if ( ! wp_verify_nonce( $nonce, "seo-toolkit-twitter-{$post_id}-save" ) ) {
            return;
        }

        /* Don't update if running an autosave */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        /* Don't update if the post is a revision or an autosave */
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        $metadata = [
            '_seo_toolkit_twitter_card'        => FILTER_SANITIZE_STRING,
            '_seo_toolkit_twitter_title'       => FILTER_SANITIZE_STRING,
            '_seo_toolkit_twitter_description' => FILTER_SANITIZE_STRING,
            '_seo_toolkit_twitter_image'       => FILTER_VALIDATE_URL
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
