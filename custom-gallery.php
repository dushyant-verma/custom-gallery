<?php
/**
 * Plugin Name: Custom Gallery with Tabs
 * Description: Adds a custom post type for a gallery with support for images, videos, and custom tabs.
 * Version: 1.0
 * Author: Dushyant Verma
 * Text Domain: custom-gallery
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Register Custom Post Type
function custom_gallery_post_type() {
    register_post_type('custom_gallery', array(
        'labels' => array(
            'name' => __('Gallery', 'custom-gallery'),
            'singular_name' => __('Gallery Item', 'custom-gallery'),
        ),
        'public' => true,
        'menu_icon' => 'dashicons-format-gallery',
        'supports' => array('title', 'thumbnail'),
        'taxonomies' => array('gallery_category'),
    ));

    register_taxonomy('gallery_category', 'custom_gallery', array(
        'labels' => array(
            'name' => __('Gallery Categories', 'custom-gallery'),
            'singular_name' => __('Gallery Category', 'custom-gallery'),
        ),
        'public' => true,
        'hierarchical' => true,
    ));
}
add_action('init', 'custom_gallery_post_type');

// Add Meta Boxes for Video URL
function custom_gallery_add_meta_boxes() {
    add_meta_box('gallery_video_url', 'Video URL', 'custom_gallery_video_url_callback', 'custom_gallery', 'normal', 'high');
}
add_action('add_meta_boxes', 'custom_gallery_add_meta_boxes');

function custom_gallery_video_url_callback($post) {
    $video_url = get_post_meta($post->ID, 'gallery_video_url', true);
    ?>
    <label for="gallery_video_url">Enter Video URL:</label>
    <input type="text" name="gallery_video_url" id="gallery_video_url" value="<?php echo esc_attr($video_url); ?>" style="width:100%;">
    <?php
}

// Save Meta Box Data
function custom_gallery_save_meta_boxes($post_id) {
    if (array_key_exists('gallery_video_url', $_POST)) {
        update_post_meta($post_id, 'gallery_video_url', sanitize_text_field($_POST['gallery_video_url']));
    }
}
add_action('save_post', 'custom_gallery_save_meta_boxes');

function custom_gallery_shortcode() {
    ob_start();
    $categories = get_terms('gallery_category');

    echo '<div class="gallery-tabs">';
    echo '<button class="tab-button active" data-category="all">All</button>';
    foreach ($categories as $category) {
        echo '<button class="tab-button" data-category="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</button>';
    }
    echo '</div>';

    $query = new WP_Query(array('post_type' => 'custom_gallery', 'posts_per_page' => -1));

    echo '<div class="gallery-content">';
    echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3" id="gallery-items">'; // Bootstrap grid with ID

    while ($query->have_posts()) {
        $query->the_post();
        $video_url = get_post_meta(get_the_ID(), 'gallery_video_url', true);
        $category_classes = join(' ', wp_get_post_terms(get_the_ID(), 'gallery_category', array('fields' => 'slugs')));

        echo '<div class="col gallery-item ' . esc_attr($category_classes) . '">';
        echo '<div class="gallery-img">';

        if ($video_url) {
            echo '<a href="' . esc_url($video_url) . '" data-fancybox="gallery">
                    <img src="' . esc_url(get_the_post_thumbnail_url()) . '" alt="' . esc_attr(get_the_title()) . '">
                  </a>';
        } else {
            echo '<a href="' . esc_url(get_the_post_thumbnail_url()) . '" data-fancybox="gallery">
                    <img src="' . esc_url(get_the_post_thumbnail_url()) . '" alt="' . esc_attr(get_the_title()) . '">
                  </a>';
        }

        echo '<div class="title-block">' . get_the_title() . '</div>';
        echo '</div>'; // Close .gallery-img
        echo '</div>'; // Close .col
    }

    echo '</div>'; // Close row
    echo '</div>'; // Close .gallery-content

    wp_reset_postdata();
    ?>
    
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const buttons = document.querySelectorAll(".tab-button");
        const items = document.querySelectorAll(".gallery-item");
        const galleryRow = document.getElementById("gallery-items");

        buttons.forEach(button => {
            button.addEventListener("click", function () {
                const category = this.getAttribute("data-category");

                // Remove active class from all buttons
                buttons.forEach(btn => btn.classList.remove("active"));
                this.classList.add("active");

                // Clear and rebuild row structure
                galleryRow.innerHTML = "";

                items.forEach(item => {
                    if (category === "all" || item.classList.contains(category)) {
                        galleryRow.appendChild(item);
                    }
                });
            });
        });
    });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('custom_gallery', 'custom_gallery_shortcode');



function enqueue_bootstrap_styles() {
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', array(), '5.3.0');
}
add_action('wp_enqueue_scripts', 'enqueue_bootstrap_styles');

// Enqueue Styles and Scripts
function custom_gallery_enqueue_scripts() {
    wp_enqueue_style('fancybox-css', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css');
    wp_enqueue_style('gallery-style', plugin_dir_url(__FILE__) . 'style.css');

    wp_enqueue_script('jquery');
    wp_enqueue_script('fancybox-js', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js', array('jquery'), null, true);
    wp_enqueue_script('gallery-script', plugin_dir_url(__FILE__) . 'script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'custom_gallery_enqueue_scripts');
