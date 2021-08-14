<?php
/**
 * @package Toolkit\Admin\Settings
 */

namespace Toolkit\Admin\Settings;

use Toolkit\Admin\Settings\Options\Description;
use Toolkit\Admin\Settings\Options\Title;
use Toolkit\Admin\Settings\Options\Robots;
use Toolkit\Context;

/**
 * General class
 *
 * @since 1.0.0
 */
class General extends AbstractPage
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var General
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        $this->context = (new Context)->getContexts();

        add_action( 'admin_init', [ $this, 'settings' ] );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return General
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new General();
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
            'seo_toolkit_title' => [
                'type'         => 'string',
                'show_in_rest' => false,
                'default'      => []
            ],
            'seo_toolkit_title_separator' => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'show_in_rest'      => false,
                'default'           => '–'
            ],
            'seo_toolkit_description' => [
                'type'         => 'string',
                'show_in_rest' => false,
                'default'      => []
            ],
            'seo_toolkit_description_default' => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
                'show_in_rest'      => false,
                'default'           => ''
            ],
            'seo_toolkit_robots' => [
                'type'         => 'string',
                'show_in_rest' => false,
                'default'      => []
            ],
            'seo_toolkit_robots_paginated_pages' => [
                'type'         => 'string',
                'show_in_rest' => false,
                'default'      => ''
            ],
            'seo_toolkit_robots_donot_implement_index' => [
                'type'              => 'boolean',
                'sanitize_callback' => 'boolval',
                'show_in_rest'      => false,
                'default'           => false
            ],
            'seo_toolkit_robots_feed_noindex' => [
                'type'              => 'boolean',
                'sanitize_callback' => 'boolval',
                'show_in_rest'      => false,
                'default'           => true
            ],
            'seo_toolkit_website' => [
                'type'         => 'string',
                'show_in_rest' => false,
                'default'      => ''
            ],
            'seo_toolkit_organization' => [
                'type'              => 'string',
                'sanitize_callback' => function( $input )
                {
                    if ( ! is_array( $input ) ) {
                        return $input;
                    }

                    $args = [
                        'name' => FILTER_SANITIZE_STRING,
                        'logo' => FILTER_VALIDATE_URL
                    ];

                    return filter_var_array( $input, $args );
                },
                'show_in_rest'      => false,
                'default'           => []
            ],
            'seo_toolkit_person' => [
                'type'              => 'string',
                'sanitize_callback' => function( $input ) {
                    if ( ! is_array( $input ) ) {
                        return $input;
                    }

                    $args = [
                        'username' => FILTER_SANITIZE_STRING,
                        'avatar'   => FILTER_VALIDATE_URL
                    ];

                    return filter_var_array( $input, $args );
                },
                'show_in_rest'      => false,
                'default'           => []
            ],
            'seo_toolkit_webmasters' => [
                'type'              => 'string',
                'sanitize_callback' => [ $this, 'sanitize' ],
                'show_in_rest'      => false,
                'default'           => []
            ]
        ];
        foreach( $options as $option => $args ) {
            register_setting( 'seo_toolkit_general', $option, $args );
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
            'google-site-verification' => FILTER_SANITIZE_STRING,
            'msvalidate.01'            => FILTER_SANITIZE_STRING,
            'yandex-verification'      => FILTER_SANITIZE_STRING,
            'p:domain_verify'          => FILTER_SANITIZE_STRING,
            'baidu-site-verification'  => FILTER_SANITIZE_STRING
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

            <h1 class="wp-heading-inline"><?php esc_html_e( 'General Settings', 'seo-toolkit' ); ?></h1>

            <form action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" method="post">

                <?php settings_fields( 'seo_toolkit_general' ); ?>

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
     * Adds a meta box to General Settings screen.
     *
     * @since 1.0.0
     */
    public function metaboxes()
    {
        $screen = get_current_screen();

        /* Title and meta tags */
        add_meta_box( 'metatags', esc_html__( 'Title and Meta tags', 'seo-toolkit' ), [ $this, 'metatags' ], $screen, 'normal', 'high' );

        /* Website Profile */
        add_meta_box( 'website', esc_html__( 'Website Profile', 'seo-toolkit' ), [ $this, 'website' ], $screen, 'normal', 'high' );

        /* Site verification */
        add_meta_box( 'webmasters', esc_html__( 'Webmasters', 'seo-toolkit' ), [ $this, 'webmasters' ], $screen, 'normal', 'high' );

        /* View details */
        add_meta_box( 'information', esc_html__( 'Information', 'seo-toolkit' ), [ $this, 'information' ], $screen, 'side', 'high' );
    }

    /**
     * Displays the 'Title and Meta tags' meta box.
     *
     * @since 1.0.0
     */
    public function metatags()
    {
        $data = [
            'title' => [
                'label' => __( 'Document title', 'seo-toolkit' ),
                'panel' => 'seo-toolkit-title'
            ],
            'description' => [
                'label' => __( 'Description', 'seo-toolkit' ),
                'panel' => 'seo-toolkit-description'
            ],
            'robots' => [
                'label' => __( 'Crawling and Indexing', 'seo-toolkit' ),
                'panel' => 'seo-toolkit-robots'
            ]
        ];
        ?>

        <div id="submitpost" class="submitbox">

            <div id="minor-publishing">

                <div id="seo-toolkit-metatags" class="seo-toolkit-tabs seo-toolkit-tabs-container">

                    <ul class="tabs tabs-nav">
                    <?php foreach( $data as $tab ) :  ?>
                        <li><a href="#<?php echo esc_attr( $tab[ 'panel' ] ); ?>"><?php echo esc_html( $tab[ 'label' ] ); ?></a></li>
                    <?php endforeach; ?>
                    </ul>

                    <div id="seo-toolkit-title" class="seo-toolkit-panel">

                        <?php $title = get_option( 'seo_toolkit_title', [] ); ?>

                        <?php foreach( $this->context as $context => $string ) : ?>

                        <?php $title_format = isset( $title[$context] ) ? $title[$context]: ''; ?>

                        <div class="form-field">
                            <label for="seo_toolkit_title_<?php echo esc_attr( $context ); ?>" class="title"><?php echo esc_html( $string ); ?></label>
                            <select id="seo_toolkit_title_<?php echo esc_attr( $context ); ?>" name="seo_toolkit_title[<?php echo esc_attr( $context ); ?>]">
                            <?php foreach( Title::newInstance()->getFormat( $context ) as $format => $format_name ) : ?>
                                <option value="<?php echo esc_attr( $format ); ?>" <?php selected( $title_format, $format ); ?>>
                                    <?php echo esc_html( $format_name ); ?>
                                </option>
                            <?php endforeach; ?>
                            </select>
                        </div>

                        <?php endforeach; ?>

                        <?php $separator = get_option( 'seo_toolkit_title_separator' ); ?>
                        <div class="form-field">
                            <label for="seo_toolkit_title_separator" class="title"><?php esc_html_e( 'Separator', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_title_separator" id="seo_toolkit_title_separator"
                                value="<?php echo esc_attr( $separator ); ?>" type="text">
                            <p class="description">
                            <?php _e( 'Special signs can be entered as HTML. Example: <code>&amp;raquo;</code> becomes <code>»</code>.', 'seo-toolkit' ); ?>
                            </p>
                        </div>

                    </div>

                    <div id="seo-toolkit-description" class="seo-toolkit-panel">

                        <?php $description = get_option( 'seo_toolkit_description', [] ); ?>

                        <?php foreach( $this->context as $context => $string ) : ?>

                        <?php $description_format = isset( $description[$context] ) ? $description[$context]: ''; ?>

                        <div class="form-field">
                            <label for="seo_toolkit_description_<?php echo esc_attr( $context ); ?>" class="title"><?php echo $string; ?></label>
                            <select id="seo_toolkit_description_<?php echo esc_attr( $context ); ?>" name="seo_toolkit_description[<?php echo esc_attr( $context ); ?>]">
                            <?php foreach( Description::newInstance()->getFormat( $context ) as $format => $format_name ) : ?>
                                <option value="<?php echo esc_attr( $format ); ?>" <?php selected( $description_format, $format ); ?>>
                                    <?php echo esc_html( $format_name ); ?>
                                </option>
                            <?php endforeach; ?>
                            </select>
                        </div>

                        <?php endforeach; ?>

                        <?php $default = get_option( 'seo_toolkit_description_default', '' ); ?>
                        <div class="form-field">
                            <label for="seo_toolkit_description_default" class="title"><?php esc_html_e( 'Default', 'seo-toolkit' ); ?></label>
                            <textarea id="seo_toolkit_description_default"
                                name="seo_toolkit_description_default" rows="5" cols="80"><?php echo esc_textarea( $default ); ?></textarea>
                        </div>

                    </div>

                    <div id="seo-toolkit-robots" class="seo-toolkit-panel">

                        <?php $robots = get_option( 'seo_toolkit_robots' ); ?>

                        <?php foreach( $this->context as $context => $string ) : ?>

                        <?php $robots_format = isset( $robots[$context] ) ? $robots[$context]: ''; ?>
                        <div class="form-field">
                            <label for="seo_toolkit_robots_<?php echo esc_attr( $context ); ?>" class="title"><?php echo esc_html( $string ); ?></label>
                            <select id="seo_toolkit_robots_<?php echo esc_attr( $context ); ?>" name="seo_toolkit_robots[<?php echo esc_attr( $context ); ?>]">
                            <?php foreach( Robots::newInstance()->getFormat( $context ) as $format ) : ?>
                                <option value="<?php echo esc_attr( $format ); ?>" <?php selected( $robots_format, $format ); ?>>
                                    <?php echo esc_html( $format ); ?>
                                </option>
                            <?php endforeach; ?>
                            </select>
                        </div>

                        <?php endforeach; ?>

                        <?php $paginated = get_option( 'seo_toolkit_robots_paginated_pages' ); ?>
                        <div class="form-field">
                            <label for="seo_toolkit_robots_paginated_pages" class="title"><?php esc_html_e( 'Paginated pages', 'seo-toolkit' ); ?></label>
                            <select id="seo_toolkit_robots_paginated_pages" name="seo_toolkit_robots_paginated_pages">
                            <?php foreach( Robots::newInstance()->getFormat( 'default' ) as $format ) : ?>
                                <option value="<?php echo esc_attr( $format ); ?>" <?php selected( $paginated, $format ); ?>>
                                    <?php echo esc_html( $format ); ?>
                                </option>
                            <?php endforeach; ?>
                            </select>
                        </div>

                        <?php $implementation = (bool) get_option( 'seo_toolkit_robots_donot_implement_index', false ); ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_robots_donot_implement_index"><?php esc_html_e( 'Implementation', 'seo-toolkit' ); ?></label>
                            <label for="seo_toolkit_robots_donot_implement_index">
                                <input name="seo_toolkit_robots_donot_implement_index" id="seo_toolkit_robots_donot_implement_index" value="true" <?php checked( $implementation, true ); ?> type="checkbox" />
                                <?php _e( 'Do not implement the values <code>index</code> and <code>index, follow</code>.', 'seo-toolkit' ); ?>
                            </label>
                        </div>

                        <?php $feed_noindex = (bool) get_option( 'seo_toolkit_robots_feed_noindex', true ); ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_robots_feed_noindex"><?php esc_html_e( 'Protect RSS Feeds', 'seo-toolkit' ); ?></label>
                            <label for="seo_toolkit_robots_feed_noindex">
                                <input name="seo_toolkit_robots_feed_noindex" id="seo_toolkit_robots_feed_noindex" value="true" <?php checked( $feed_noindex, true ); ?> type="checkbox" />
                                <?php esc_html_e( 'Prevent indexing of RSS Feeds by search engines.', 'seo-toolkit' ); ?>
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
     * Displays the 'Website Profile' meta box.
     *
     * @since 1.0.0
     */
    public function website()
    {
        $data = [
            'title' => [
                'label' => __( 'Profile', 'seo-toolkit' ),
                'panel' => 'seo-toolkit-profile'
            ]
        ];

        $profiles = [
            'none'         => __( 'Select the website profile', 'seo-toolkit' ),
            'person'       => __( 'Person', 'seo-toolkit' ),
            'organization' => __( 'Organization', 'seo-toolkit' )
        ];

        $website = get_option( 'seo_toolkit_website' );

        $users = get_users( [ 'orderby' => 'registered' ] ); ?>

        <div id="submitpost" class="submitbox">

            <div id="minor-publishing">

                <div id="seo-toolkit-profile" class="seo-toolkit-tabs seo-toolkit-tabs-container">

                    <ul class="tabs tabs-nav">
                    <?php foreach( $data as $tab ) :  ?>
                        <li><a href="#<?php echo esc_attr( $tab[ 'panel' ] ); ?>"><?php echo esc_html( $tab[ 'label' ] ); ?></a></li>
                    <?php endforeach; ?>
                    </ul>

                    <div id="seo-toolkit-data" class="seo-toolkit-panel">

                        <?php $website_profile = isset( $website['profile'] ) ? $website['profile'] : ''; ?>
                        <div class="form-field">
                            <label for="seo_toolkit_website_profile" class="title"><?php esc_html_e( 'Website', 'seo-toolkit' ); ?></label>
                            <select id="seo_toolkit_website_profile" name="seo_toolkit_website[profile]">
                            <?php foreach( $profiles as $option => $profile ) : ?>
                                <option value="<?php echo esc_attr( $option ); ?>" <?php selected( $website_profile, $option ); ?>>
                                    <?php echo esc_html( $profile ); ?>
                                </option>
                            <?php endforeach; ?>
                            </select>
                        </div>

                        <?php $person = get_option( 'seo_toolkit_person', [] ); ?>

                        <?php $username = isset( $person['username'] ) ? $person['username'] : ''; ?>
                        <div id="person-name" class="form-field">
                            <label for="seo_toolkit_person_username" class="title"><?php esc_html_e( 'Name', 'seo-toolkit' ); ?></label>
                            <select id="seo_toolkit_person_username" name="seo_toolkit_person[username]">
                            <?php foreach( $users as $user ) : ?>
                                <?php error_log( $user->user_login ); ?>
                                <option value="<?php echo esc_attr( $user->user_login ); ?>" <?php selected( $username, $user->user_login ); ?>>
                                    <?php echo esc_html( "{$user->display_name} ({$user->user_login})" ); ?>
                                </option>
                            <?php endforeach; ?>
                            </select>
                        </div>

                        <?php $avatar = isset( $person['avatar'] ) ? $person['avatar'] : ''; ?>
                        <div id="person-picture" class="form-field">
                            <label class="title" for="seo_toolkit_person_avatar"><?php esc_html_e( 'Picture', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_person[avatar]" id="seo_toolkit_person_avatar"
                                value="<?php echo esc_attr( $avatar ); ?>" type="text">
                            <input id="seo_toolkit_person_picture_button" type="button" class="button" value="<?php esc_html_e( 'Upload Image', 'seo-toolkit' ); ?>" />
                        </div>

                        <?php $organization = get_option( 'seo_toolkit_organization', [] ); ?>

                        <?php $name = isset( $organization['name'] ) ? $organization['name'] : ''; ?>
                        <div id="organization-name" class="form-field">
                            <label class="title" for="seo_toolkit_organization_name"><?php esc_html_e( 'Organization Name', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_organization[name]" id="seo_toolkit_organization_name"
                                value="<?php echo esc_html( $name ); ?>" type="text">
                        </div>

                        <?php $logo = isset( $organization['logo'] ) ? $organization['logo'] : ''; ?>
                        <div id="organization-logo" class="form-field">
                            <label class="title" for="seo_toolkit_organization_logo"><?php esc_html_e( 'Organization Logo', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_organization[logo]" id="seo_toolkit_organization_logo"
                                value="<?php echo esc_attr( $logo ); ?>" type="text">
                            <input id="seo_toolkit_organization_logo_button" type="button" class="button" value="<?php esc_html_e( 'Upload Image', 'seo-toolkit' ); ?>" />
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
     * Displays the 'Webmasters' meta box.
     *
     * @since 1.0.0
     */
    public function webmasters()
    {
        $data = [
            'search-engines' => [
                'label' => 'Search Engines',
                'panel' => 'seo-toolkit-search-engines'
            ]
        ];

        $settings = get_option( 'seo_toolkit_webmasters', [] ); ?>

        <div id="submitpost" class="submitbox">

            <div id="minor-publishing">

                <div id="seo-toolkit-verification" class="seo-toolkit-tabs seo-toolkit-tabs-container">

                    <ul class="tabs tabs-nav">
                    <?php foreach( $data as $tab ) :  ?>
                        <li><a href="#<?php echo esc_attr( $tab[ 'panel' ] ); ?>"><?php echo esc_html( $tab[ 'label' ] ); ?></a></li>
                    <?php endforeach; ?>
                    </ul>

                    <div id="seo-toolkit-search-engines" class="seo-toolkit-panel">

                        <?php $google = isset( $settings['google-site-verification'] ) ? $settings['google-site-verification']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_webmasters_google"><?php esc_html_e( 'Google', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_webmasters[google-site-verification]" id="seo_toolkit_webmasters_google"
                                value="<?php echo esc_attr( $google ); ?>" type="text">
                            <p class="description"><?php echo wp_kses( __( 'Get your code in <a href="https://search.google.com/search-console/welcome" target="_blank">Google Search Console</a>.', 'seo-toolkit' ), [ 'a' => [ 'href' => [], 'target' => [] ] ] ); ?></p>
                        </div>

                        <?php $bing = isset( $settings['msvalidate.01'] ) ? $settings['msvalidate.01']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_webmasters_bing"><?php esc_html_e( 'Bing', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_webmasters[msvalidate.01]" id="seo_toolkit_webmasters_bing"
                                value="<?php echo esc_attr( $bing ); ?>" type="text">
                            <p class="description"><?php echo wp_kses( __( 'Get your code in <a href="https://www.bing.com/toolbox/webmaster" target="_blank">Bing Webmaster Tools</a>.', 'seo-toolkit' ), [ 'a' => [ 'href' => [], 'target' => [] ] ] ); ?></p>
                        </div>

                        <?php $yandex = isset( $settings['yandex-verification'] ) ? $settings['yandex-verification']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_webmasters_yandex"><?php esc_html_e( 'Yandex', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_webmasters[yandex-verification]" id="seo_toolkit_webmasters_yandex"
                                value="<?php echo esc_attr( $yandex ); ?>" type="text">
                            <p class="description"><?php echo wp_kses( __( 'Get your code in <a href="https://webmaster.yandex.com/welcome/" target="_blank">Yandex.Webmaster</a>.', 'seo-toolkit' ), [ 'a' => [ 'href' => [], 'target' => [] ] ] ); ?></p>
                        </div>

                        <?php $pinterest = isset( $settings['p:domain_verify'] ) ? $settings['p:domain_verify']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_webmasters_pinterest"><?php esc_html_e( 'Pinterest', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_webmasters[p:domain_verify]" id="seo_toolkit_webmasters_pinterest"
                                value="<?php echo esc_attr( $pinterest ); ?>" type="text">
                            <p class="description"><?php echo wp_kses( __( 'Get your code in <a href="https://help.pinterest.com/en/business/article/claim-your-website" target="_blank">Business Profile</a>.', 'seo-toolkit' ), [ 'a' => [ 'href' => [], 'target' => [] ] ] ); ?></p>
                        </div>

                        <?php $baidu = isset( $settings['baidu-site-verification'] ) ? $settings['baidu-site-verification']: ''; ?>
                        <div class="form-field">
                            <label class="title" for="seo_toolkit_webmasters_baidu"><?php esc_html_e( 'Baidu', 'seo-toolkit' ); ?></label>
                            <input name="seo_toolkit_webmasters[baidu-site-verification]" id="seo_toolkit_webmasters_baidu"
                                value="<?php echo esc_attr( $baidu ); ?>" type="text">
                            <p class="description"><?php echo wp_kses( __( 'Get your code in <a href="https://ziyuan.baidu.com/login/index?u=/site/siteadd" target="_blank">Baidu Webmaster Tools</a>.', 'seo-toolkit' ), [ 'a' => [ 'href' => [], 'target' => [] ] ] ); ?></p>
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
            'organization' => esc_html_x( 'Organization Logo', 'General settings', 'seo-toolkit' ),
            'person'       => esc_html_x( 'Picture',           'General settings', 'seo-toolkit' ),
            'button'       => esc_html_x( 'Choose Image',      'General settings', 'seo-toolkit' )
        ];
        wp_localize_script( 'seo-toolkit-upload', 'socialmedia_upload', $l10n );

        wp_enqueue_script( 'seo-toolkit-upload' );
    }
}
