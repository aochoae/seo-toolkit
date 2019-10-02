<?php
/**
 * @package Toolkit\Admin
 */

namespace Toolkit\Admin;

/**
 * Taxonomies class
 *
 * @since 1.0.0
 */
class Taxonomies
{
    /**
     * Singleton instance.
     *
     * @since 1.0.0
     * @var Post
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        $taxonomies = get_taxonomies( [ 'public' => true ], 'names' );

        foreach( $taxonomies as $taxonomy ) {
            add_action( "{$taxonomy}_edit_form_fields", [ $this, 'editFields' ], 20, 2 );
        }

        add_action( 'edited_terms', [ $this, 'update' ], 10, 2 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return Taxonomies
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Taxonomies();
        }

        return self::$instance;
    }

    /**
     * Adds the 'Document title', 'Description' and 'Robots' fields.
     *
     * @since 1.0.0
     */
    public function editFields( $term, $taxonomy )
    {
        $term_id = $term->term_id;

        wp_nonce_field( "seo-toolkit-{$term_id}-save", "seo-toolkit-{$term_id}-nonce" );

        $title = get_term_meta( $term_id, '_seo_toolkit_title', true );

        $description = get_term_meta( $term_id, '_seo_toolkit_description', true );

        $indexing = [
            'Default',
            'index, follow',
            'index, nofollow',
            'noindex, follow',
            'noindex, nofollow'
        ];
        ?>

        <tr class="form-field">
            <th scope="row">
                <label><?php esc_html_e( 'Advanced', 'seo-toolkit' ); ?></label>
            </th>
            <td>
            <label><?php esc_html_e( 'The following fields are used for SEO purposes.', 'seo-toolkit' ); ?></label>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="_seo_toolkit_title"><?php esc_html_e( 'Document title', 'seo-toolkit' ); ?></label>
            </th>
            <td>
                <input name="_seo_toolkit_title" id="_seo_toolkit_title" type="text" value="<?php echo esc_html( $title ); ?>" size="40">
                <p class="description"><?php esc_html_e( 'This title is used to display in the search results.', 'seo-toolkit' ); ?></p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row">
                <label for="_seo_toolkit_description"><?php esc_html_e( 'Description', 'seo-toolkit' ); ?></label>
            </th>
            <td>
                <textarea id="_seo_toolkit_description" class="large-text"
                    name="_seo_toolkit_description" rows="5" cols="50"><?php echo esc_textarea( $description ); ?></textarea>
                <p class="description"><?php esc_html_e( 'This description is used to display in the search results.', 'seo-toolkit' ); ?></p>
            </td>
        </tr>

        <?php $robots = get_term_meta( $term_id, '_seo_toolkit_robots', true ); ?>

        <tr class="form-field">
            <th scope="row">
                <label for="_seo_toolkit_robots"><?php esc_html_e( 'Robots', 'seo-toolkit' ); ?></label>
            </th>
            <td>
                <select id="_seo_toolkit_robots" name="_seo_toolkit_robots">
                <?php foreach( $indexing as $key ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $robots, $key ); ?>>
                        <?php echo esc_html( $key ); ?>
                    </option>
                <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e( 'Control the crawling and indexing.', 'seo-toolkit' ); ?></p>
            </td>
        </tr>

        <?php
    }

    /**
     * Save new data.
     *
     * @since 1.0.0
     */
    public function update( $term_id, $taxonomy )
    {
        /* Verify that the nonce is valid */
        $nonce = filter_input( INPUT_POST, "seo-toolkit-{$term_id}-nonce", FILTER_SANITIZE_STRING );

        if ( ! wp_verify_nonce( $nonce, "seo-toolkit-{$term_id}-save" ) ) {
            return;
        }

        $metadata = [
            '_seo_toolkit_title'       => FILTER_SANITIZE_STRING,
            '_seo_toolkit_description' => FILTER_SANITIZE_STRING,
            '_seo_toolkit_robots'      => FILTER_SANITIZE_STRING
        ];

        $metadata = filter_input_array( INPUT_POST, $metadata );

        foreach ( $metadata as $meta => $new_value ) {

            $value = get_term_meta( $term_id, $meta, true );

            if ( ! empty( $new_value ) && $new_value != $value ) {
                update_term_meta( $term_id, $meta, $new_value );
            } elseif ( empty( $new_value ) && $value ) {
                delete_term_meta( $term_id, $meta );
            }
        }
    }
}
