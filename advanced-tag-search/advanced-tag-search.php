<?php
/**
 * Plugin Name: Advanced Tag Search
 * Plugin URI: https://example.com/advanced-tag-search
 * Description: 高度な検索機能を提供するプラグイン。タグやカテゴリーでの絞り込み検索が可能です。
 * Version: 1.6.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Makoto Takei
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: advanced-tag-search
 * Domain Path: /languages
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

// プラグインの定数定義
define('ATS_VERSION', '1.6.0');
define('ATS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ATS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ATS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// 動作要件（最低バージョン）
define('ATS_MIN_PHP', '7.4');
define('ATS_MIN_WP', '6.0');
// 推奨バージョン
define('ATS_RECOMMENDED_PHP', '8.2');
define('ATS_RECOMMENDED_WP', '6.8');

/**
 * メインクラス
 */
class Advanced_Tag_Search {
    
    /**
     * シングルトンインスタンス
     */
    private static $instance = null;
    
    /**
     * シングルトンインスタンスを取得
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * コンストラクタ
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * 初期化処理
     */
    private function init() {
        // 依存ファイルの読み込み
        $this->load_dependencies();
        
        // フックの登録
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_ajax_ats_get_tags', array($this, 'ajax_get_tags'));
        add_action('wp_ajax_nopriv_ats_get_tags', array($this, 'ajax_get_tags'));
        add_action('wp_ajax_ats_get_post_count', array($this, 'ajax_get_post_count'));
        add_action('wp_ajax_nopriv_ats_get_post_count', array($this, 'ajax_get_post_count'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // タグ検索のクエリフィルター
        add_action('pre_get_posts', array($this, 'modify_tag_query'));

        // プラグイン有効化時の処理
        register_activation_hook(__FILE__, array($this, 'activate'));
    }
    
    /**
     * 依存ファイルの読み込み
     */
    private function load_dependencies() {
        require_once ATS_PLUGIN_DIR . 'includes/class-tag-manager.php';
        require_once ATS_PLUGIN_DIR . 'includes/class-search-widget.php';
        require_once ATS_PLUGIN_DIR . 'includes/shortcodes.php';
        
        if (is_admin()) {
            require_once ATS_PLUGIN_DIR . 'admin/settings.php';
        }
    }
    
    /**
     * フロントエンド用スクリプト・スタイルの読み込み
     */
    public function enqueue_scripts() {
        // CSS
        wp_enqueue_style(
            'ats-style',
            ATS_PLUGIN_URL . 'assets/css/style.css',
            array(),
            ATS_VERSION
        );

        // カスタム色を適用
        $this->add_custom_colors();

        // JavaScript
        wp_enqueue_script(
            'ats-modal',
            ATS_PLUGIN_URL . 'assets/js/modal.js',
            array('jquery'),
            ATS_VERSION,
            true
        );

        // Ajax用のデータを渡す
        wp_localize_script('ats-modal', 'atsAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ats-nonce'),
            'searchUrl' => home_url('/'),
        ));
    }

    /**
     * カスタム色をCSSとして出力
     */
    private function add_custom_colors() {
        $settings = get_option('ats_settings', array());
        $icon_color = sanitize_hex_color($settings['search_icon_color'] ?? '#666666');
        $button_color = sanitize_hex_color($settings['button_color'] ?? '#2196F3');

        // デフォルト値にフォールバック
        $icon_color = $icon_color ?: '#666666';
        $button_color = $button_color ?: '#2196F3';

        $custom_css = sprintf(
            '/* 虹眼鏡アイコンの色 */
.ats-search-icon {
    stroke: %1$s !important;
}

/* 検索ボタンの色 */
.ats-search-button {
    background: %2$s !important;
    border-color: %2$s !important;
}
.ats-search-button:hover {
    background: %2$s !important;
    opacity: 0.9;
}

/* モーダル内の絞り込みボタンの色 */
.ats-search-submit {
    background: %2$s !important;
    border-color: %2$s !important;
}
.ats-search-submit:hover {
    background: %2$s !important;
    opacity: 0.9;
}',
            esc_attr($icon_color),
            esc_attr($button_color)
        );

        wp_add_inline_style('ats-style', $custom_css);
    }
    
    /**
     * 管理画面用スクリプト・スタイルの読み込み
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_advanced-tag-search' !== $hook) {
            return;
        }
        
        // WordPressのカラーピッカーを読み込む
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        wp_enqueue_style(
            'ats-admin-style',
            ATS_PLUGIN_URL . 'admin/css/admin-style.css',
            array('wp-color-picker'),
            ATS_VERSION
        );
        
        // カラーピッカーの初期化
        wp_add_inline_script('wp-color-picker', '
            jQuery(document).ready(function($) {
                $(".ats-color-picker").wpColorPicker();
            });
        ');
    }
    
    /**
     * ショートコードの登録
     */
    public function register_shortcodes() {
        add_shortcode('advanced_search', 'ats_search_shortcode');
        add_shortcode('search_quick_links', 'ats_quick_links_shortcode');
    }
    
    /**
     * Ajax: タグ一覧取得
     */
    public function ajax_get_tags() {
        check_ajax_referer('ats-nonce', 'nonce');

        $tag_manager = new ATS_Tag_Manager();
        $tags = $tag_manager->get_tag_categories();

        wp_send_json_success($tags);
    }

    /**
     * Ajax: 選択した条件に一致する記事数を取得
     *
     * 選択中のタグ・カテゴリー（AND）とキーワードで一致する公開記事数を返します。
     */
    public function ajax_get_post_count() {
        check_ajax_referer('ats-nonce', 'nonce');

        $tags = isset($_POST['tags']) ? (array) wp_unslash($_POST['tags']) : array();
        $cats = isset($_POST['categories']) ? (array) wp_unslash($_POST['categories']) : array();
        $keyword = isset($_POST['keyword']) ? sanitize_text_field(wp_unslash($_POST['keyword'])) : '';

        // スラッグを文字列としてサニタイズ（スラッグ自体は変換しない）
        $tags = array_filter(array_map('sanitize_text_field', $tags));
        $cats = array_filter(array_map('sanitize_text_field', $cats));

        $args = array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => 1,
            'fields'              => 'ids',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => false,
        );

        // タグ・カテゴリーはAND条件（実際の検索と同じ挙動）
        $tax_query = array('relation' => 'AND');

        if (!empty($tags)) {
            $tax_query[] = array(
                'taxonomy' => 'post_tag',
                'field'    => 'slug',
                'terms'    => $tags,
                'operator' => 'AND',
            );
        }

        if (!empty($cats)) {
            $tax_query[] = array(
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => $cats,
                'operator' => 'AND',
            );
        }

        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        if ('' !== $keyword) {
            $args['s'] = $keyword;
        }

        $query = new WP_Query($args);

        wp_send_json_success(array('count' => (int) $query->found_posts));
    }

    /**
     * 管理メニューの追加
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Advanced Tag Search', 'advanced-tag-search'),
            __('Tag Search', 'advanced-tag-search'),
            'manage_options',
            'advanced-tag-search',
            'ats_render_settings_page',
            'dashicons-search',
            30
        );
    }
    
    /**
     * タグ・カテゴリー検索のクエリを修正（AND条件）
     */
    public function modify_tag_query($query) {
        // 管理画面やメインクエリ以外はスキップ
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        $has_tag = isset($_GET['tag']) && !empty($_GET['tag']);
        $has_category = isset($_GET['ats_category']) && !empty($_GET['ats_category']);

        // タグもカテゴリーも指定がない場合は何もしない
        if (!$has_tag && !$has_category) {
            return;
        }

        // タグでの絞り込み
        if ($has_tag) {
            $tags_string = sanitize_text_field($_GET['tag']);
            $tags = array_filter(array_map('trim', explode(',', $tags_string)));

            // タグが複数ある場合はAND検索を設定（単一タグはWordPressのデフォルト動作に任せる）
            if (count($tags) > 1) {
                $tag_ids = array();

                foreach ($tags as $tag_slug) {
                    $tag = get_term_by('slug', sanitize_title($tag_slug), 'post_tag');

                    if ($tag && !is_wp_error($tag)) {
                        $tag_ids[] = $tag->term_id;
                    }
                }

                if (!empty($tag_ids)) {
                    $query->set('tag__and', $tag_ids);
                    // tagパラメータをクリア（重複を防ぐ）
                    $query->set('tag', '');
                }
            }
        }

        // カテゴリーでの絞り込み
        if ($has_category) {
            $cats_string = sanitize_text_field($_GET['ats_category']);
            $cat_slugs = array_filter(array_map('trim', explode(',', $cats_string)));

            $cat_ids = array();

            foreach ($cat_slugs as $cat_slug) {
                $category = get_term_by('slug', sanitize_title($cat_slug), 'category');

                if ($category && !is_wp_error($category)) {
                    $cat_ids[] = $category->term_id;
                }
            }

            // 有効なカテゴリーがある場合はAND検索を設定
            if (!empty($cat_ids)) {
                $query->set('category__and', $cat_ids);
            }
        }
    }

    /**
     * プラグイン有効化時の処理
     */
    public function activate() {
        // デフォルト設定の保存
        $default_settings = array(
            'placeholder' => __('タグから探してみる', 'advanced-tag-search'),
            'modal_title' => __('タグ検索', 'advanced-tag-search'),
        );
        
        if (!get_option('ats_settings')) {
            update_option('ats_settings', $default_settings);
        }
        
        // デフォルトのタグカテゴリーを設定
        if (!get_option('ats_tag_categories')) {
            $tag_manager = new ATS_Tag_Manager();
            $tag_manager->set_default_categories();
        }
    }
}

/**
 * 動作要件（PHP / WordPress バージョン）を満たすか確認
 *
 * @return bool 要件を満たす場合は true
 */
function ats_meets_requirements() {
    global $wp_version;

    $php_ok = version_compare(PHP_VERSION, ATS_MIN_PHP, '>=');
    $wp_ok  = version_compare($wp_version, ATS_MIN_WP, '>=');

    return $php_ok && $wp_ok;
}

/**
 * 要件未達時の管理画面通知
 */
function ats_requirements_notice() {
    global $wp_version;

    $message = sprintf(
        /* translators: 1: 必要PHP, 2: 必要WP, 3: 現在PHP, 4: 現在WP */
        __('「Advanced Tag Search」を利用するには PHP %1$s 以上 / WordPress %2$s 以上が必要です。（現在の環境: PHP %3$s / WordPress %4$s）', 'advanced-tag-search'),
        ATS_MIN_PHP,
        ATS_MIN_WP,
        PHP_VERSION,
        $wp_version
    );

    echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
}

/**
 * 有効化時のバージョンチェック（要件未達なら有効化を中止）
 */
function ats_activation_check() {
    if (ats_meets_requirements()) {
        return;
    }

    deactivate_plugins(plugin_basename(__FILE__));

    wp_die(
        esc_html(sprintf(
            /* translators: 1: 必要PHP, 2: 必要WP, 3: 現在PHP, 4: 現在WP */
            __('「Advanced Tag Search」を有効化できません。PHP %1$s 以上 / WordPress %2$s 以上が必要です。（現在の環境: PHP %3$s / WordPress %4$s）', 'advanced-tag-search'),
            ATS_MIN_PHP,
            ATS_MIN_WP,
            PHP_VERSION,
            $GLOBALS['wp_version']
        )),
        esc_html__('プラグイン有効化エラー', 'advanced-tag-search'),
        array('back_link' => true)
    );
}
register_activation_hook(__FILE__, 'ats_activation_check');

/**
 * プラグインの初期化
 */
function ats_init() {
    // 動作要件を満たさない場合は通知のみ表示して初期化しない
    if (!ats_meets_requirements()) {
        add_action('admin_notices', 'ats_requirements_notice');
        return;
    }

    return Advanced_Tag_Search::get_instance();
}

// プラグインを起動
ats_init();
