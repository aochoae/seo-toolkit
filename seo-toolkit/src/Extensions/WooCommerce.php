<?php
/**
 * @package Toolkit
 */

namespace Toolkit\Extensions;

/**
 * WooCommerce class
 *
 * @since 1.0.0
 */
class WooCommerce
{
    /**
     * Singleton instance
     *
     * @since 1.0.0
     * @var WooCommerce
     */
    private static $instance;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        add_filter( 'seo_toolkit_contexts', [ $this, 'contexts' ], 15, 2 );

        add_filter( 'seo_toolkit_description_product', [ $this, 'product' ], 10, 3 );

        add_filter( 'seo_toolkit_description_product_archive', [ $this, 'shop' ], 10, 3 );

        add_filter( 'seo_toolkit_description_page', [ $this, 'description' ], 10, 3 );

        add_filter( 'seo_toolkit_robots_page', [ $this, 'robots' ], 10, 3 );

        add_filter( 'seo_toolkit_sitemap_page_ids', [ $this, 'sitemap' ], 20, 1 );

        add_filter( 'seo_toolkit_description_format', [ $this, 'options' ], 15, 2 );

        add_filter( 'seo_toolkit_description_strings', [ $this, 'descriptionString' ], 15, 1 );

        add_filter( 'seo_toolkit_sitemap_product', [ $this, 'images'], 10, 2 );
    }

    /**
     * The singleton method.
     *
     * @since 1.0.0
     *
     * @return WooCommerce
     */
    public static function newInstance()
    {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new WooCommerce();
        }

        return self::$instance;
    }

    /**
     * Adds the 'Shop page' string to product archive.
     *
     * @since 1.0.0
     */
    public function contexts( $contexts )
    {
        $contexts[ 'product_archive' ] = __( 'Shop page', 'seo-toolkit' );

        return $contexts;
    }

    /**
     * Retrieves the product description of the 'short description' field.
     *
     * @since 1.0.0
     */
    public function product( $description, $option, $post_id )
    {
        switch( $option ) {
            case '%short_description%':
                $description = get_post_field( 'post_excerpt', $post_id );
                break;
        }

        return $description;
    }

    /**
     * Retrieves the shop page description of the 'excerpt' field.
     *
     * @since 1.0.0
     */
    public function shop( $description, $option, $object_id )
    {
        if ( empty( $description ) && '%description%' == $option ) {

            $shop_id = wc_get_page_id( 'shop' );

            switch( $option ) {
                case '%description%':
                    $description = get_post_field( 'post_excerpt', $shop_id );
                    break;
            }
        }

        return $description;
    }

    /**
     * The account, cart and checkout pages do not require the 'description'
     * metadata.
     *
     * @since 1.0.0
     */
    public function description( $description, $option, $post_id )
    {
        if ( is_cart() || is_checkout() || is_account_page() ) {
            return '';
        }

        return $description;
    }

    /**
     * Add 'noindex, nofollow' to prevent search engines from
     * indexing the account, cart and checkout pages.
     *
     * @since 1.0.0
     */
    public function robots( $robots, $option, $post_id )
    {
        if ( is_cart() || is_checkout() || is_account_page() ) {
            return [ [ 'robots' => 'noindex, nofollow' ] ];
        }

        return $robots;
    }

    /**
     * Removes the Cart, Checkout and  My Account pages from sitemap.
     *
     * @since 1.0.0
     */
    public function sitemap( $ids )
    {
        $page_ids = [
            wc_get_page_id( 'cart' ),
            wc_get_page_id( 'checkout' ),
            wc_get_page_id( 'myaccount' )
        ];

        foreach( $page_ids as $id ) {
            unset( $ids[ $id ] );
        }

        return $ids;
    }

    /**
     * Adds options to generate the descriptions of the WooCommerce pages.
     *
     * @since 1.0.0
     */
    public function options( $format, $context )
    {
        switch( $context ) {
            case 'product':
                $format[ 'product' ] = [
                    '%short_description%',
                    '%none%'
                ];
                break;
            case 'product_tag':
            case 'product_cat':
            case 'product_archive':
                $format[ $context ] = [
                    '%description%',
                    '%none%'
                ];
                break;
        }

        return $format;
    }

    /**
     * Sets the 'Short product description' string to the 'short_description'
     * option.
     *
     * @since 1.0.0
     */
    public function descriptionString( $options )
    {
        $options[ '%short_description%' ] = __( 'Short product description', 'seo-toolkit' );

        return $options;
    }

    /**
     * Adds the images to the sitemap.
     *
     * @since 1.0.0
     */
    public function images( $data, $product_id )
    {
        $noimageindex = get_post_meta( $product_id, '_seo_toolkit_robots_noimageindex', true );

        if ( ! empty( $noimageindex ) ) {
            return $data;
        }

        $images = [];

        if ( $ids = $this->getImages( $product_id ) ) {

            foreach( $ids as $id ) {
                array_push( $images, [ 'loc' => wp_get_attachment_url( $id ) ] );
            }
        }

        if ( ! empty( $images ) ) {
            $data += [ 'images' => $images ];
        }

        return $data;
    }

    /**
     * Recover the image data.
     *
     * @since 1.0.0
     */
    private function getImages( $product_id )
    {
        $ids = [];

        $product = new \WC_product( $product_id );

        if ( $id = $product->get_image_id() ) {
            $ids[] = $id;
        }

        if ( $gallery_ids = $product->get_gallery_image_ids() ) {
            $ids = array_merge( $ids, $gallery_ids );
        }

        return array_values( array_unique( $ids ) );
    }
}
