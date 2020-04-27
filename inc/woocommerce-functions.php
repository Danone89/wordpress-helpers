<?php

namespace Wordpress_helpers;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if (!class_exists('WC')) return;

function woocommerce_product_price($ID = false)
{
    global $product;
    //get the sale price of the product whether it be simple, grouped or variable
    $sale_price = get_post_meta($ID, '_price', true);
    if ($sale_price)
        return number_format($sale_price, 2) . 'zł';
    //get the regular price of the product, but of a simple product
    $regular_price = get_post_meta($ID, '_regular_price', true);

    //oh, the product is variable to $sale_price is empty? Lets get a variation price

    if ($regular_price == "" && is_object($product)) {
        $available_variations = $product->get_available_variations();
        $variation_id = $available_variations[0]['variation_id'];
        $variable_product1 = new WC_Product_Variation($variation_id);
        $regular_price = $variable_product1->regular_price;
    }
    return number_format($regular_price, 2) . 'zł';
}

/**
 * 
 * @param int $term_id
 * @param string $size z listy zdefinowanych
 */
function the_term_image($term_id, $size = 'list')
{
    if (function_exists('get_woocommerce_term_meta')) {
        $thumbnail_id = get_woocommerce_term_meta($term_id, 'thumbnail_id', true);
        $image = wp_get_attachment_image_src($thumbnail_id, $size);
    }
    if (!$thumbnail_id) {
        if (!function_exists('z_taxonomy_image_url'))
            return;
        $image = get_taxonomy_image_url($term_id, $size);
    }
    if (is_array($image)) {
        $width = $image[1];
        $height = $image[2];
        $image = str_replace('http://', 'https://', $image[0]);
    }
    if ($image) {
        echo '<img src="' . $image . '" width="' . $width . '" height="' . $height . '"  alt="" />';
    }
}

