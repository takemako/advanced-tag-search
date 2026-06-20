<?php
/**
 * タグ管理クラス
 */

if (!defined('ABSPATH')) {
    exit;
}

class ATS_Tag_Manager {
    
    /**
     * タグカテゴリーの取得
     */
    public function get_tag_categories() {
        $categories = get_option('ats_tag_categories', array());
        
        if (empty($categories)) {
            $categories = $this->get_default_categories();
        }
        
        return $categories;
    }
    
    /**
     * デフォルトのタグカテゴリーを取得
     *
     * 標準では3カテゴリー。タグは管理画面でWordPress登録済みタグから選択します。
     */
    public function get_default_categories() {
        return array(
            'cat_0' => array(
                'title' => __('ジャンルから絞り込む', 'advanced-tag-search'),
                'tags'  => array(),
            ),
            'cat_1' => array(
                'title' => __('エリアから絞り込む', 'advanced-tag-search'),
                'tags'  => array(),
            ),
            'cat_2' => array(
                'title' => __('キーワードから絞り込む', 'advanced-tag-search'),
                'tags'  => array(),
            ),
        );
    }
    
    /**
     * デフォルトカテゴリーを設定
     */
    public function set_default_categories() {
        $categories = $this->get_default_categories();
        update_option('ats_tag_categories', $categories);
    }
    
    /**
     * タグカテゴリーの保存
     */
    public function save_tag_categories($categories) {
        return update_option('ats_tag_categories', $categories);
    }
    
    /**
     * カテゴリー別タグの取得
     */
    public function get_tags_by_category($category_key) {
        $categories = $this->get_tag_categories();
        
        if (isset($categories[$category_key])) {
            return $categories[$category_key]['tags'];
        }
        
        return array();
    }
    
    /**
     * WordPressのタグ一覧を取得
     */
    public function get_all_wp_tags() {
        $tags = get_tags(array(
            'hide_empty' => false,
        ));
        
        $tag_list = array();
        foreach ($tags as $tag) {
            $tag_list[] = array(
                'id' => $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'count' => $tag->count,
            );
        }
        
        return $tag_list;
    }
    
    /**
     * WordPressのカテゴリー一覧を取得
     */
    public function get_all_wp_categories() {
        $categories = get_categories(array(
            'hide_empty' => false,
        ));
        
        $category_list = array();
        foreach ($categories as $category) {
            $category_list[] = array(
                'id' => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->count,
                'link' => get_category_link($category->term_id),
            );
        }

        return $category_list;
    }
    
    /**
     * タグ名からWordPressのタグオブジェクトを取得
     */
    public function get_tag_by_name($tag_name) {
        return get_term_by('name', $tag_name, 'post_tag');
    }
    
    /**
     * カテゴリー名からWordPressのカテゴリーオブジェクトを取得
     */
    public function get_category_by_name($category_name) {
        return get_term_by('name', $category_name, 'category');
    }
}
