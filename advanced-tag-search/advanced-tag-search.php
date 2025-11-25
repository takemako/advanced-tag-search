<?php
/**
 * Plugin Name: Advanced Tag Search
 * Plugin URI: https://example.com/advanced-tag-search
 * Description: Kawagoe.funのような高度な検索機能を提供するプラグイン。タグやカテゴリーでの絞り込み検索が可能です。
 * Version: 1.5.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Your Name
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
define('ATS_VERSION', '1.5.0');
define('ATS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ATS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ATS_PLUGIN_BASENAME', plugin_basename(__FILE__));

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
     * タグ検索のクエリを修正（AND条件）
     */
    public function modify_tag_query($query) {
        // 管理画面やメインクエリ以外はスキップ
        if (is_admin() || !$query->is_main_query()) {
            return;
        }
        
        // tagパラメータが存在するか確認
        if (!isset($_GET['tag']) || empty($_GET['tag'])) {
            return;
        }
        
        $tags_string = sanitize_text_field($_GET['tag']);
        $tags = array_map('trim', explode(',', $tags_string));
        $tags = array_filter($tags); // 空要素を削除
        
        // タグがない場合は何もしない
        if (empty($tags)) {
            return;
        }
        
        // タグが1つだけの場合は通常のタグ検索
        if (count($tags) === 1) {
            // WordPressのデフォルト動作に任せる（何もしない）
            return;
        }
        
        // 複数タグのAND検索を設定
        $tag_ids = array();
        
        foreach ($tags as $tag_slug) {
            $tag_slug = sanitize_title($tag_slug);
            $tag = get_term_by('slug', $tag_slug, 'post_tag');
            
            if ($tag && !is_wp_error($tag)) {
                $tag_ids[] = $tag->term_id;
            }
        }
        
        // 有効なタグがある場合のAND検索
        if (!empty($tag_ids)) {
            $query->set('tag__and', $tag_ids);
            // tagパラメータをクリア（重複を防ぐ）
            $query->set('tag', '');
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
            'quick_links' => array(),
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
 * プラグインの初期化
 */
function ats_init() {
    return Advanced_Tag_Search::get_instance();
}

// プラグインを起動
ats_init();
