<?php
/**
 * @package Toolkit\Admin
 */

namespace Toolkit\Admin;

/**
 * Posts class
 *
 * @since 1.0.0
 */
class Posts
{
    /**
     * Singleton instance.
     *
     * @since 1.0.0
     * @var Posts
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_action( 'add_meta_boxes', [ $this, 'metaboxes' ] );

        add_action( 'save_post', [ $this, 'update' ], 10, 2 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Posts
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Posts();
        }

        return self::$instance;
    }

    /**
     * Register meta boxes.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function metaboxes()
    {
        $post_types = array_keys( get_post_types( [ 'public' => true ], 'objects' ) );

        add_meta_box( 'seo-toolkit', esc_html__( 'Search Engine Optimization', 'seo-toolkit' ), [ $this, 'metabox' ], $post_types, 'normal', 'default' );
    }

    /**
     * Render meta boxes.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function metabox( $object )
    {
        $post_id = $object->ID;

        wp_nonce_field( "seo-toolkit-{$post_id}-save", "seo-toolkit-{$post_id}-nonce" );

        $tabs = [
            'general' => [
                'label'    => __( 'General', 'seo-toolkit' ),
                'panel'    => 'toolbox-general',
                'priority' => 5
            ]
        ];

        $tabs = apply_filters( "seo_toolkit_posts_tabs", $tabs, $object->post_type, $post_id );

        uasort( $tabs, [ $this, 'sort' ] );

        $indexing = [
            'Default',
            'index, follow',
            'index, nofollow',
            'noindex, follow',
            'noindex, nofollow'
        ];
        ?>

        <div id="seo-toolkit-post" class="seo-toolkit-tabs seo-toolkit-tabs-container">

            <ul class="tabs tabs-nav">
                <?php foreach( $tabs as $key => $tab ) : ?>
                    <li><a href="#<?php echo esc_attr( $tab[ 'panel' ] ) ?>"><?php echo esc_html( $tab[ 'label' ] ); ?></a></li>
                <?php endforeach; ?>
            </ul>

            <div id="toolbox-general" class="seo-toolkit-panel">

                <?php $title = get_post_meta( $post_id, '_seo_toolkit_title', true ); ?>
                <div class="form-field clear">
                    <label class="title" for="_seo_toolkit_title"><?php esc_html_e( 'Document title', 'seo-toolkit' ); ?></label>
                    <input name="_seo_toolkit_title" id="_seo_toolkit_title" value="<?php echo $title; ?>" type="text">
                </div>

                <?php $description = get_post_meta( $post_id, '_seo_toolkit_description', true ); ?>
                <div class="form-field">
                    <label class="title" for="_seo_toolkit_description"><?php esc_html_e( 'Description', 'seo-toolkit' ); ?></label>
                    <textarea id="_seo_toolkit_description" name="_seo_toolkit_description" rows="5" cols="80"><?php echo esc_textarea( $description ); ?></textarea>
                </div>

                <?php $robots = get_post_meta( $post_id, '_seo_toolkit_robots', true ); ?>
                <div class="form-field">
                    <label class="title" for="_seo_toolkit_robots"><?php esc_html_e( 'Robots', 'seo-toolkit' ); ?></label>
                    <select id="_seo_toolkit_robots" name="_seo_toolkit_robots">
                    <?php foreach( $indexing as $key ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $robots, $key ); ?>><?php echo esc_html( $key ); ?></option>
                    <?php endforeach; ?>
                    </select>
                </div>

                <?php $noarchive = get_post_meta( $post_id, '_seo_toolkit_robots_noarchive', true ); ?>
                <div class="form-field">
                    <label class="selectit">
                        <input id="_seo_toolkit_robots_noarchive" name="_seo_toolkit_robots_noarchive" value="true" <?php checked( $noarchive, true ); ?> type="checkbox" />
                        <?php esc_html_e( 'Do not show a "Cached" link in search results.', 'seo-toolkit' ); ?>
                    </label>
                </div>

                <?php $nosnippet = get_post_meta( $post_id, '_seo_toolkit_robots_nosnippet', true ); ?>
                <div class="form-field">
                    <label class="selectit">
                        <input id="_seo_toolkit_robots_nosnippet" name="_seo_toolkit_robots_nosnippet" value="true" <?php checked( $nosnippet, true ); ?> type="checkbox" />
                        <?php esc_html_e( 'Do not show a snippet in the search results for this post.', 'seo-toolkit' ); ?>
                    </label>
                </div>

                <?php $noimage = get_post_meta( $post_id, '_seo_toolkit_robots_noimageindex', true ); ?>
                <div class="form-field">
                    <label class="selectit">
                        <input id="_seo_toolkit_robots_noimageindex" name="_seo_toolkit_robots_noimageindex" value="true" <?php checked( $noimage, true ); ?> type="checkbox" />
                        <?php esc_html_e( 'Do not index images on this post.', 'seo-toolkit' ); ?>
                    </label>
                </div>

            </div>

            <?php do_action( "seo_toolkit_posts_tabs_content", $object->post_type, $post_id ); ?>

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
        /* Verify that the nonce is valid */
        $nonce = filter_input( INPUT_POST, "seo-toolkit-{$post_id}-nonce", FILTER_SANITIZE_STRING );

        if ( ! wp_verify_nonce( $nonce, "seo-toolkit-{$post_id}-save" ) ) {
            return;
        }

        /* Don't update if the post is a revision or an autosave */
        if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) {
            return;
        }

        /* Don't update if running an autosave */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $metadata = [
            '_seo_toolkit_title'               => FILTER_SANITIZE_STRING,
            '_seo_toolkit_description'         => FILTER_SANITIZE_STRING,
            '_seo_toolkit_robots'              => FILTER_SANITIZE_STRING,
            '_seo_toolkit_robots_noarchive'    => FILTER_VALIDATE_BOOLEAN,
            '_seo_toolkit_robots_nosnippet'    => FILTER_VALIDATE_BOOLEAN,
            '_seo_toolkit_robots_noimageindex' => FILTER_VALIDATE_BOOLEAN
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

    /**
     * Callback to sort the tabs by priority.
     *
     * @since 1.0.0
     */
    public function sort( $a, $b )
    {
        if ( ! isset( $a['priority'] ) ) {
            $a['priority'] = 10;
        }

        if ( ! isset( $b['priority'] ) ) {
            $b['priority'] = 10;
        }

        if ( $a['priority'] === $b['priority'] ) {
            return 0;
        }

        return $a['priority'] < $b['priority'] ? -1 : 1;
    }

    /**
     * Loads the stylesheets and script required for Tabs functionality
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function enqueue()
    {
        wp_enqueue_media();

        wp_enqueue_style( 'seo-toolkit-style' );

        $l10n = [
            'facebook' => esc_html__( 'Facebook',     'seo-toolkit' ),
            'twitter'  => esc_html__( 'Twitter',      'seo-toolkit' ),
            'button'   => esc_html__( 'Choose Image', 'seo-toolkit' )
        ];
        wp_localize_script( 'seo-toolkit-script', 'socialmedia_upload', $l10n );

        wp_enqueue_script( 'seo-toolkit-script' );
    }
}
