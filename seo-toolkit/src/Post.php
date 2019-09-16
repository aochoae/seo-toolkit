<?php
/**
 * @package Toolkit
 */

namespace Toolkit;

/**
 * Post class
 *
 * @since 1.0.0
 */
class Post
{
    /**
     * Post ID
     */
    private $post_id = 0;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct( $post_id )
    {
        $this->post_id = $post_id;
    }

    /**
     * Retrieves the document title.
     *
     * @since 1.0.0
     */
    public function getTitle()
    {
        $post_id = $this->post_id;

        $title = get_post_meta( $post_id, '_seo_toolkit_title', true );

        if ( empty( $title ) ) {
            $title = get_post_field( 'post_title', $post_id );
        }

        return wp_strip_all_tags( $title );
    }

    /**
     * Retrieves the document description.
     *
     * @since 1.0.0
     */
    public function getDescription()
    {
        $description = get_post_field( 'post_content', $this->post_id );

        $description = strip_shortcodes( $description );
        $description = wp_strip_all_tags( $description );
        $description = preg_replace('/\s+/', ' ', $description );
        $description = substr( $description, 0, 150 );

        return trim( $description );
    }

    /**
     * Retrieves the IDs of the images if they exist.
     *
     * @since 1.0.0
     */
    public function getImages()
    {
        $ids = [];

        if ( $image_id = $this->getImage() ) {
            $ids[] = $image_id;
        }

        if ( $gallery_ids = $this->getGallery() ) {
            $ids = array_merge( $ids, $gallery_ids );
        }

        return array_values( array_unique( $ids ) );
    }

    /**
     * Retrieves the ID of the featured image if exists.
     *
     * @since 1.0.0
     */
    public function getImage()
    {
        $post_id = $this->post_id;

        if ( has_post_thumbnail( $post_id ) ) {
            return get_post_thumbnail_id( $post_id );
        }

        return 0;
    }

    /**
     * Retrieves the IDs of the gallery if exists.
     *
     * @since 1.0.0
     */
    public function getGallery()
    {
        $post_id = $this->post_id;

        $post = get_post( $post_id );

        $ids = [];

        if ( function_exists( 'has_block' ) && has_block( 'gallery', $post->post_content ) ) {

            $blocks = parse_blocks( $post->post_content );

            foreach( $blocks as $block ) {
                if ( 'core/gallery' == $block['blockName'] ) {
                    if ( isset( $block['attrs']['ids'] ) ) {
                        $ids = array_merge( $ids, $block['attrs']['ids'] );
                    }
                }
            }
        }

        if ( $galleries = get_post_galleries( $post_id, false ) ) {

            foreach( $galleries as $gallery ) {

                if ( isset( $gallery[ 'ids' ] ) ) {

                    $gallery_ids = array_map( 'trim', explode( ',', $gallery[ 'ids' ] ) );

                    $ids = array_merge( $ids, $gallery_ids );
                }
            }
        }

        return array_values( array_unique( $ids ) );
    }

    /**
     * Retrieve the author id.
     *
     * @since 1.0.0
     */
    public function getAuthor()
    {
        return get_post_field( 'post_author', $this->post_id );
    }

    /**
     * Retrieve the publication date.
     *
     * @since 1.0.0
     */
    public function getDatePublished()
    {
        return get_post_field( 'post_date_gmt', $this->post_id );
    }

    /**
     * Retrieve the modification date.
     *
     * @since 1.0.0
     */
    public function getDateModified()
    {
        return get_post_field( 'post_modified_gmt', $this->post_id );
    }
}
