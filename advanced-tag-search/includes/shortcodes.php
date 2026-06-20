<?php
/**
 * ショートコード定義
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [advanced_search] ショートコード
 * 
 * 使用例:
 * [advanced_search]
 * [advanced_search placeholder="検索してみる" show_quick_links="true"]
 */
function ats_search_shortcode($atts) {
    $atts = shortcode_atts(array(
        'placeholder' => '',
        'show_quick_links' => 'true',
    ), $atts, 'advanced_search');
    
    return ATS_Search_Widget::render_complete_widget($atts);
}

/**
 * [search_quick_links] ショートコード
 *
 * ブログのカテゴリーから自動生成されたクイックリンクを表示します。
 *
 * 使用例:
 * [search_quick_links]
 */
function ats_quick_links_shortcode($atts) {
    return ATS_Search_Widget::render_quick_links($atts);
}
