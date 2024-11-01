<?php

// Plugin core data
global $sptotal__;

$sptotal__ = array(
    'activate_link' => '',
    'prolink'       => 'https://webfixlab.com/',
    'notice'        => array()
);

$sptotal__['plugin'] = array(
    'screen'        => array(
        'toplevel_page_sptotal-settings'
    ),
    'review_link'   => 'https://wordpress.org/support/plugin/single-product-total/reviews/?rate=5#new-post',
    'free_url'      => 'https://wordpress.org/plugins/single-product-total/',
    'request_quote' => 'https://webfixlab.com/request-quote/',
    'name'          => __( 'Single Product Total', 'single-pruduct-total' ),
    'woo_url'       => 'wcurl'
);

$sptotal__['fields'] = array(
    'sptotal_total_text'     => array(
        'type'        => 'text',
        'label'       => __( 'Label', 'single-pruduct-total' ),
        'placeholder' => __( 'Enter label', 'single-pruduct-total' )
    ),
    'spline_if_inline' => array(
        'type'    => 'checkbox',
        'label'   => __( 'Label display', 'single-pruduct-total' ),
        'default' => 'no',
        'desc'    => __( 'Block (label at the top)', 'single-pruduct-total' )
    ),
    'sptotal_total_position' => array(
        'type'    => 'select',
        'label'   => __( 'Position', 'single-pruduct-total' ),
        'options' => array(
            'before_cart_btn' => __( 'Before add to cart button', 'single-pruduct-total' ),
            'after_cart_btn'  => __( 'After add to cart button', 'single-pruduct-total' ),
            'before_price'    => __( 'Before price', 'single-pruduct-total' ),
            'after_price'     => __( 'After price', 'single-pruduct-total' ),
            'fixed_bottom_right' => __( 'Fixed bottom right', 'single-pruduct-total' ),
            'fixed_bottom_left'  => __( 'Fixed bottom left', 'single-pruduct-total' ),
            'fixed_top_right'    => __( 'Fixed top right', 'single-pruduct-total' ),
            'fixed_top_left'     => __( 'Fixed top left', 'single-pruduct-total' ),
        )
    ),
    'sptotal_text_align' => array(
        'type'    => 'select',
        'label'   => __( 'Text align', 'single-pruduct-total' ),
        'options' => array(
            'left'   => __( 'Left', 'single-pruduct-total' ),
            'center' => __( 'Center', 'single-pruduct-total' ),
            'right'  => __( 'Right', 'single-pruduct-total' )
        )
    ),
    'sptotal_label_color' => array(
        'type'  => 'color',
        'label' => __( 'Label color', 'single-pruduct-total' ),
        'class' => 'sptotal-colorpicker',
        'attr'  => array(
            'data-default-color' => ''
        )
    ),
    'sptotal_price_color' => array(
        'type'  => 'color',
        'label' => __( 'Price color', 'single-pruduct-total' ),
        'class' => 'sptotal-colorpicker',
        'attr'  => array(
            'data-default-color' => ''
        )
    ),
    'sptotal_background' => array(
        'type'  => 'color',
        'label' => __( 'Background', 'single-pruduct-total' ),
        'class' => 'sptotal-colorpicker',
        'attr'  => array(
            'data-default-color' => ''
        )
    )
);

// hook to modify global $sptotal__ data variable
do_action( 'sptotal_modify_core_data' );