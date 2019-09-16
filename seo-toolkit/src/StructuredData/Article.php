<?php
/**
 * @package Toolkit\StructuredData
 */

namespace Toolkit\StructuredData;

/**
 * Article class
 *
 * @since 1.0.0
 */
class Article extends AbstractStructuredData
{
    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_filter( 'seo_toolkit_schema', [ $this, 'json' ], 10, 2 );
    }

    /**
     * Article
     *
     * @link https://developers.google.com/search/docs/data-types/article?hl=es-419#non-amp
     *
     * @since 1.0.0
     */
    public function json( $schema, $context )
    {
        if ( ! in_array( $context, [ 'post', 'page' ] ) ) {
            return $schema;
        }

        $post_id = get_queried_object_id();

        $post = new \Toolkit\Post( $post_id );

        $article = [
            '@type'            => 'Article',
            'headline'         => $post->getTitle(),
            'mainEntityOfPage' => get_permalink( $post_id ),
            'datePublished'    => $post->getDatePublished(),
            'dateModified'     => $post->getDateModified(),
            'author'           => $this->getAuthor( $post->getAuthor() ),
            'image'            => $this->getImages( $post->getImages(), $post->getAuthor() ),
            'publisher'        => [ '@id' => $this->getId() ]
        ];

        $schema[] = $article;

        return $schema;
    }

    /**
     * Retrieves the article author.
     *
     * @since 1.0.0
     */
    private function getAuthor( $user_id )
    {
        $schema = [
            '@type' => [ 'Person' ],
            'name'  => get_the_author_meta( 'display_name', $user_id ),
            'url'   => esc_url( get_author_posts_url( $user_id ) ),
            'image' => esc_url( get_avatar_url( $user_id, [ 'size' => 256 ] ) )
        ];

        return $schema;
    }

    /**
     * Retrieves the article images.
     *
     * @since 1.0.0
     */
    private function getImages( $image_ids, $user_id )
    {
        $images = [];

        foreach( $image_ids as $image_id ) {
            array_push( $images, wp_get_attachment_url( $image_id ) );
        }

        if ( empty( $images ) ) {
            array_push( $images, get_avatar_url( $user_id, [ 'size' => 256 ] ) );
        }

        return $images;
    }
}
