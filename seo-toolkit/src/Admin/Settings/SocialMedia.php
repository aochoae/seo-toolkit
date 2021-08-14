<?php
/**
 * @package Toolkit\Admin\Settings
 */

namespace Toolkit\Admin\Settings;

/**
 * SocialMedia class
 *
 * @since 1.0.0
 */
class SocialMedia extends AbstractPage
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
        add_action( 'admin_init', [ $this, 'settings' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return SocialMedia
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new SocialMedia();
        }

        return self::$instance;
    }

    /**
     * Register the plugin settings.
     *
     * @since 1.0.0
     */
    public function settings()
    {
        $options = [
            'seo_toolkit_facebook_enabled' => [
                'type'              => 'boolean',
                'show_in_rest'      => false,
                'default'           => true
            ],
            'seo_toolkit_facebook' => [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize' ],
                'show_in_rest'      => false,
                'default'           => []
            ],
            'seo_toolkit_twitter_enabled' => [
                'type'              => 'boolean',
                'show_in_rest'      => false,
                'default'           => true
            ],
            'seo_toolkit_twitter' => [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize' ],
                'show_in_rest'      => false,
                'default'           => []
            ]
        ];

        foreach( $options as $option => $args ) {
            register_setting( 'seo_toolkit_profile', $option, $args );
        }
    }

    /**
     * Callback function that sanitizes the inputs.
     *
     * @since 1.0.0
     */
    public function sanitize( $input )
    {
        if ( ! is_array( $input ) ) {
            return $input;
        }

        $args = [
            'admins'      => FILTER_SANITIZE_STRING,
            'app_id'      => FILTER_SANITIZE_STRING,
            'title'       => FILTER_SANITIZE_STRING,
            'description' => FILTER_SANITIZE_STRING,
            'image'       => FILTER_SANITIZE_URL,
            'profile'     => FILTER_SANITIZE_STRING,
        ];

        return filter_var_array( $input, $args );
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

            <h1 class="wp-heading-inline"><?php esc_html_e( 'Social Media', 'seo-toolkit' ); ?></h1>

            <form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">

                <?php settings_fields( 'seo_toolkit_profile' ); ?>

                <?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
                <?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

                <div id="poststuff">

                    <div id="post-body" class="metabox-holder columns-<?php echo $columns; ?>">

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
     * Adds a meta box to Social Media screen.
     *
     * @since 1.0.0
     */
    public function metaboxes()
    {
        $screen = get_current_screen();

        /* Profiles */
        add_meta_box( 'profiles', esc_html__( 'Social Profiles', 'seo-toolkit' ), [ $this, 'profiles' ], $screen, 'normal', 'high' );

        /* View details */
        add_meta_box( 'information', esc_html__( 'Information', 'seo-toolkit' ), [ $this, 'information' ], $screen, 'side', 'high' );
    }

    /**
     * Displays the 'Social Profiles' meta box.
     *
     * @since 1.0.0
     */
    public function profiles()
    {
        $data = [
            'facebook' => [
                'label' => __( 'Facebook Open Graph', 'seo-toolkit' ),
                'panel' => 'toolbox-facebook'
            ],
            'twitter' => [
                'label' => __( 'Twitter Cards', 'seo-toolkit' ),
                'panel' => 'toolbox-twitter'
            ]
        ];
        ?>

        <div id="submitpost" class="submitbox">

            <div id="minor-publishing">

                <div id="seo-toolkit-socialmedia" class="seo-toolkit-tabs seo-toolkit-tabs-container">

                    <ul class="tabs tabs-nav">
                    <?php foreach( $data as $tab ) :  ?>
                        <li><a href="#<?= esc_attr( $tab[ 'panel' ] ) ?>"><?= esc_html( $tab[ 'label' ] ) ?></a></li>
                    <?php endforeach; ?>
                    </ul>

                    <div id="toolbox-facebook" class="seo-toolkit-panel">

                        <?php $facebook_enabled = (bool) get_option( 'seo_toolkit_facebook_enabled', true ); ?>

                        <div class="form-field">
                            <label class="title" for="seo_toolkit_facebook_enabled"><?php esc_html_e( 'Enable', 'seo-toolkit' ); ?></label>
                            <label class="switch">
                                <input name="seo_toolkit_facebook_enabled" id="seo_toolkit_facebook_enabled"
                                    class="toggle" value="true" <?php checked( $facebook_enabled, true ); ?> type="checkbox">
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <?php $facebook = get_option( 'seo_toolkit_facebook' ); ?>

                        <?php $facebook_admins = isset( $facebook['admins'] ) ? $facebook['admins']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_facebook_admins"><?php esc_html_e( 'Facebook Admin ID', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_facebook[admins]" id="seo_toolkit_facebook_admins" value="<?php echo $facebook_admins; ?>" type="text">
                            <p class="description"><?php _e( 'You can enter multiple Facebook Admin IDs by separating them with a comma.', 'seo-toolkit' ); ?></p>
                        </div>

                        <?php $facebook_app = isset( $facebook['app_id'] ) ? $facebook['app_id']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_facebook_app_id"><?php esc_html_e( 'Facebook App ID', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_facebook[app_id]" id="seo_toolkit_facebook_app_id" value="<?php echo $facebook_app; ?>" type="text">
                            <p class="description"><?php _e( 'In order to use Facebook Insights you must add the app ID to your page. Find the app ID in your <a href="https://developers.facebook.com/apps/redirect/dashboard" target="_blank">App Dashboard</a>.', 'seo-toolkit' ); ?></p>
                        </div>

                        <?php $facebook_title = isset( $facebook['title'] ) ? $facebook['title']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_facebook_title"><?php esc_html_e( 'Title', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_facebook[title]" id="seo_toolkit_facebook_title" value="<?php echo $facebook_title; ?>" type="text">
                            <p class="description"><?php esc_html_e( 'A clear title without mentioning the domain itself.', 'seo-toolkit' ); ?></p>
                        </div>

                        <?php $facebook_description = isset( $facebook['description'] ) ? $facebook['description']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_facebook_description"><?php esc_html_e( 'Description', 'seo-toolkit' ); ?></label>
                            <textarea name="seo_toolkit_facebook[description]" id="seo_toolkit_facebook_description"><?php echo $facebook_description; ?></textarea>
                            <p class="description"><?php esc_html_e( 'A brief description of the content, usually between 2 and 4 sentences.', 'seo-toolkit' ); ?></p>
                        </div>

                        <?php $facebook_image = isset( $facebook['image'] ) ? $facebook['image']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_facebook_image"><?php esc_html_e( 'Image URL', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_facebook[image]" id="seo_toolkit_facebook_image" value="<?php echo $facebook_image; ?>" type="text">
                            <input id="seo_toolkit_facebook_button" type="button" class="button" value="<?php esc_html_e( 'Upload Image', 'seo-toolkit' ); ?>" />
                            <p class="description">
                            <?php esc_html_e( 'Use images that are at least 1080 pixels in width for best display on high resolution devices. At the minimum, you should use images that are 600 pixels.', 'seo-toolkit' ); ?>
                            </p>
                        </div>

                    </div>

                    <div id="toolbox-twitter" class="seo-toolkit-panel">

                        <?php $twitter_enabled = (bool) get_option( 'seo_toolkit_twitter_enabled', true ); ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_twitter_enabled"><?php esc_html_e( 'Enable', 'seo-toolkit' ); ?></label>
                            <label class="switch">
                                <input name="seo_toolkit_twitter_enabled" id="seo_toolkit_twitter_enabled"
                                    class="toggle" value="true" <?php checked( $twitter_enabled, true ); ?> type="checkbox">
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <?php $twitter = get_option( 'seo_toolkit_twitter' ); ?>

                        <?php $twitter_card = isset( $twitter['card'] ) ? $twitter['card']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_twitter_card"><?php esc_html_e( 'Twitter Cards', 'seo-toolkit' ); ?></label>
                            <select id="seo_toolkit_twitter_card" name="seo_toolkit_twitter[card]">
                                <option <?php selected( $twitter_card, 'summary' ); ?> value="summary">
                                    <?php esc_html_e( 'Summary Card', 'seo-toolkit' ); ?>
                                </option>
                                <option <?php selected( $twitter_card, 'summary_large_image' ); ?> value="summary_large_image">
                                    <?php esc_html_e( 'Summary Card with Large Image', 'seo-toolkit' ); ?>
                                </option>
                            </select>
                            <p class="description"><?php esc_html_e( 'With Twitter Cards, you can attach photos and videos to Tweets, helping to drive traffic to your website.', 'seo-toolkit' ); ?></p>
                        </div>

                        <?php $twitter_title = isset( $twitter['profile'] ) ? $twitter['profile']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_twitter_profile"><?php esc_html_e( 'Profile', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_twitter[profile]" id="seo_toolkit_twitter_profile"
                                value="<?php echo $twitter_title; ?>" type="text">
                            <p class="description"><?php esc_html_e( 'Twitter profile of website (Ex. username or @username).', 'seo-toolkit' ); ?></p>
                        </div>

                        <?php $twitter_title = isset( $twitter['title'] ) ? $twitter['title']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_twitter_title"><?php esc_html_e( 'Title', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_twitter[title]" id="seo_toolkit_twitter_title"
                                value="<?php echo $twitter_title; ?>" type="text">
                            <p class="description"><?php esc_html_e( 'Title of content (max 70 characters).', 'seo-toolkit' ); ?></p>
                        </div>

                        <?php $twitter_description = isset( $twitter['description'] ) ? $twitter['description']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_twitter_description"><?php esc_html_e( 'Description', 'seo-toolkit' ); ?></label>
                            <textarea name="seo_toolkit_twitter[description]" id="seo_toolkit_twitter_description"><?php echo $twitter_description; ?></textarea>
                            <p class="description"><?php esc_html_e( 'Description of content (maximum 200 characters).', 'seo-toolkit' ); ?></p>
                        </div>

                        <?php $twitter_image = isset( $twitter['image'] ) ? $twitter['image']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_twitter_image"><?php esc_html_e( 'Image URL', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_twitter[image]" id="seo_toolkit_twitter_image"
                                value="<?php echo $twitter_image; ?>" type="text">
                            <input id="seo_toolkit_twitter_button" type="button" class="button" value="<?php esc_html_e( 'Upload Image', 'seo-toolkit' ); ?>" />
                            <p class="description">
                            <?php esc_html_e( 'URL of image to use in the card. Images must be less than 5MB in size. JPG, PNG, WEBP and GIF formats are supported.', 'seo-toolkit' ); ?></p>
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
     * Enqueues the admin scripts.
     *
     * @since 1.0.0
     */
    public function scripts()
    {
        wp_enqueue_media();

        $l10n = [
            'profile'  => esc_html__( 'Logo',         'seo-toolkit' ),
            'facebook' => esc_html__( 'Facebook',     'seo-toolkit' ),
            'twitter'  => esc_html__( 'Twitter',      'seo-toolkit' ),
            'button'   => esc_html__( 'Choose Image', 'seo-toolkit' )
        ];
        wp_localize_script( 'seo-toolkit-upload', 'socialmedia_upload', $l10n );

        wp_enqueue_script( 'seo-toolkit-upload' );
    }
}
