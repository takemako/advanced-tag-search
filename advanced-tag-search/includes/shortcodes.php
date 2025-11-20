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
 * 使用例:
 * [search_quick_links]
 * [search_quick_links links="リンク1,リンク2,リンク3"]
 */
function ats_quick_links_shortcode($atts) {
    $atts = shortcode_atts(array(
        'links' => '',
    ), $atts, 'search_quick_links');
    
    return ATS_Search_Widget::render_quick_links($atts);
}
