<?php
/**
 * Plugin Name: Xenice SEO
 * Plugin URI: https://www.xenice.com/xenice-seo
 * Description: Add SEO description and keywords fields for posts, pages, categories, custom post types, and custom taxonomies. Includes homepage SEO and auto description generation.
 * Version: 1.0.1
 * Author: Xenice
 * Author URI: https://www.xenice.com/
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: xenice-seo
 * Domain Path: /languages
 */


if (!defined('ABSPATH')) exit;

class Xenice_SEO {

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_seo_meta_box']);
        add_action('save_post', [$this, 'save_seo_meta']);
        add_action('edit_term', [$this, 'save_term_meta']);
        add_action('create_term', [$this, 'save_term_meta']);
        add_action('category_add_form_fields', [$this, 'add_term_meta_fields']);
        add_action('category_edit_form_fields', [$this, 'edit_term_meta_fields']);
        add_action('edit_tag_form_fields', [$this, 'edit_term_meta_fields']);
        add_action('tag_add_form_fields', [$this, 'add_term_meta_fields']);
        add_action('wp_head', [$this, 'output_seo_meta'], 1);
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /** ------------------------
     *  Meta Box for Posts
     * ------------------------ */
    public function add_seo_meta_box() {
        $types = get_post_types(['public' => true], 'names');
        foreach ($types as $type) {
            add_meta_box('xenice_seo_meta', esc_html__('SEO Settings', 'xenice-seo'), [$this, 'seo_meta_box_html'], $type, 'normal', 'default');
        }
    }

    public function seo_meta_box_html($post) {
        wp_nonce_field('xenice_seo_save_meta', 'xenice_seo_nonce');
        $description = get_post_meta($post->ID, '_xenice_seo_description', true);
        $keywords = get_post_meta($post->ID, '_xenice_seo_keywords', true);
        ?>
        <p>
            <label for="xenice_seo_description"><strong><?php esc_html_e('SEO Description', 'xenice-seo'); ?></strong></label><br>
            <textarea name="xenice_seo_description" id="xenice_seo_description" style="width:100%;" rows="3"><?php echo esc_textarea($description); ?></textarea>
        </p>
        <p>
            <label for="xenice_seo_keywords"><strong><?php esc_html_e('SEO Keywords (comma separated)', 'xenice-seo'); ?></strong></label><br>
            <input type="text" name="xenice_seo_keywords" id="xenice_seo_keywords" value="<?php echo esc_attr($keywords); ?>" style="width:100%;">
        </p>
        <?php
    }

    public function save_seo_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!isset($_POST['xenice_seo_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['xenice_seo_nonce'])), 'xenice_seo_save_meta')) return;

        if (isset($_POST['xenice_seo_description'])) {
            update_post_meta($post_id, '_xenice_seo_description', sanitize_textarea_field(wp_unslash($_POST['xenice_seo_description'])));
        }
        if (isset($_POST['xenice_seo_keywords'])) {
            update_post_meta($post_id, '_xenice_seo_keywords', sanitize_text_field(wp_unslash($_POST['xenice_seo_keywords'])));
        }
    }

    /** ------------------------
     *  Taxonomy Fields
     * ------------------------ */
    public function add_term_meta_fields() {
        wp_nonce_field('xenice_seo_save_term', 'xenice_seo_term_nonce'); ?>
        <div class="form-field">
            <label for="xenice_seo_description"><?php esc_html_e('SEO Description', 'xenice-seo'); ?></label>
            <textarea name="xenice_seo_description" rows="3" style="width:100%;"></textarea>
        </div>
        <div class="form-field">
            <label for="xenice_seo_keywords"><?php esc_html_e('SEO Keywords (comma separated)', 'xenice-seo'); ?></label>
            <input type="text" name="xenice_seo_keywords" style="width:100%;">
        </div>
    <?php }

    public function edit_term_meta_fields($term) {
        wp_nonce_field('xenice_seo_save_term', 'xenice_seo_term_nonce');
        $description = get_term_meta($term->term_id, '_xenice_seo_description', true);
        $keywords = get_term_meta($term->term_id, '_xenice_seo_keywords', true);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="xenice_seo_description"><?php esc_html_e('SEO Description', 'xenice-seo'); ?></label></th>
            <td><textarea name="xenice_seo_description" rows="3" style="width:100%;"><?php echo esc_textarea($description); ?></textarea></td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label for="xenice_seo_keywords"><?php esc_html_e('SEO Keywords (comma separated)', 'xenice-seo'); ?></label></th>
            <td><input type="text" name="xenice_seo_keywords" value="<?php echo esc_attr($keywords); ?>" style="width:100%;"></td>
        </tr>
        <?php
    }

    public function save_term_meta($term_id) {
        if (!isset($_POST['xenice_seo_term_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['xenice_seo_term_nonce'])), 'xenice_seo_save_term')) return;

        if (isset($_POST['xenice_seo_description'])) {
            update_term_meta($term_id, '_xenice_seo_description', sanitize_textarea_field(wp_unslash($_POST['xenice_seo_description'])));
        }
        if (isset($_POST['xenice_seo_keywords'])) {
            update_term_meta($term_id, '_xenice_seo_keywords', sanitize_text_field(wp_unslash($_POST['xenice_seo_keywords'])));
        }
    }

    /** ------------------------
     *  Frontend Output
     * ------------------------ */
    public function output_seo_meta() {
        global $post;

        $description = '';
        $keywords = '';

        if (is_front_page() || is_home()) {
            $description = get_option('xenice_seo_home_description', '');
            $keywords = get_option('xenice_seo_home_keywords', '');
        } elseif (is_singular()) {
            $description = get_post_meta($post->ID, '_xenice_seo_description', true);
            $keywords = get_post_meta($post->ID, '_xenice_seo_keywords', true);

            if (!$description && get_option('xenice_seo_auto_description', 1)) {
                $content = wp_strip_all_tags($post->post_content);
                $description = mb_substr($content, 0, 150);
            }
        } elseif (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            $description = get_term_meta($term->term_id, '_xenice_seo_description', true);
            $keywords = get_term_meta($term->term_id, '_xenice_seo_keywords', true);
        }

        if ($description) echo '<meta name="description" content="' . esc_attr(trim($description)) . '">' . "\n";
        if ($keywords) echo '<meta name="keywords" content="' . esc_attr(trim($keywords)) . '">' . "\n";
    }

    /** ------------------------
     *  Settings Page
     * ------------------------ */
    public function add_settings_page() {
        add_options_page('Xenice SEO Settings', 'Xenice SEO', 'manage_options', 'xenice-seo', [$this, 'settings_page_html']);
    }

    public function register_settings() {
        register_setting('xenice_seo_options', 'xenice_seo_auto_description', [
            'type' => 'boolean',
            'sanitize_callback' => 'absint',
            'default' => 1,
        ]);
        register_setting('xenice_seo_options', 'xenice_seo_home_description', [
            'sanitize_callback' => 'sanitize_textarea_field',
            'default' => '',
        ]);
        register_setting('xenice_seo_options', 'xenice_seo_home_keywords', [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
    }

    public function settings_page_html() { ?>
        <div class="wrap">
            <h1><?php esc_html_e('Xenice SEO Settings', 'xenice-seo'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('xenice_seo_options');
                ?>
                <h2><?php esc_html_e('Automatic Description', 'xenice-seo'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Auto-generate description', 'xenice-seo'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="xenice_seo_auto_description" value="1" <?php checked(1, get_option('xenice_seo_auto_description', 1)); ?>>
                                <?php esc_html_e('Automatically generate description from first 150 characters if empty.', 'xenice-seo'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e('Homepage SEO', 'xenice-seo'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Homepage Description', 'xenice-seo'); ?></th>
                        <td>
                            <textarea name="xenice_seo_home_description" rows="3" style="width:100%;"><?php echo esc_textarea(get_option('xenice_seo_home_description', '')); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Homepage Keywords', 'xenice-seo'); ?></th>
                        <td>
                            <input type="text" name="xenice_seo_home_keywords" value="<?php echo esc_attr(get_option('xenice_seo_home_keywords', '')); ?>" style="width:100%;">
                        </td>
                    </tr>
                </table>

                <?php submit_button(esc_html__('Save Settings', 'xenice-seo')); ?>
            </form>
        </div>
    <?php }
}

new Xenice_SEO();

