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
     */
    public function get_default_categories() {
        return array(
            'genre' => array(
                'title' => __('ジャンルから絞り込む', 'advanced-tag-search'),
                'tags' => array(
                    '食べ歩き', 'レストラン', 'カフェ', 'スイーツ', '居酒屋', 'バー',
                    '宿泊施設', '温泉・銭湯', '展示', '体験', 'お土産',
                    '神社・寺院', '観光スポット', 'お祭り'
                )
            ),
            'area' => array(
                'title' => __('エリアから絞り込む', 'advanced-tag-search'),
                'tags' => array(
                    '蔵造りの街並み周辺', '時の鐘周辺', '菓子屋横丁周辺', '氷川神社周辺',
                    '川越市役所周辺', '昭和の街周辺', '大正浪漫夢通り周辺', '川越駅',
                    '本川越駅・川越市駅', 'クレアモール周辺', '喜多院周辺', '川越城周辺',
                    '南古谷駅方面', '南大塚駅方面', '新河岸駅方面', '西川越駅方面',
                    '霞ヶ関駅方面'
                )
            ),
            'keyword' => array(
                'title' => __('キーワードから絞り込む', 'advanced-tag-search'),
                'tags' => array(
                    '芋', '鰻', '団子', '蕎麦', '太麺焼きそば', 'カレー', 'うどん', '肉',
                    'ラーメン', 'コーヒー', 'ケーキ', 'アイス', 'かき氷', 'お茶', 'お酒'
                )
            ),
            'time' => array(
                'title' => __('時間帯から絞り込む', 'advanced-tag-search'),
                'tags' => array(
                    'モーニング', 'ランチ', 'ディナー'
                )
            ),
            'season' => array(
                'title' => __('季節から絞り込む', 'advanced-tag-search'),
                'tags' => array(
                    '春', '夏', '秋', '冬'
                )
            ),
            'other' => array(
                'title' => __('その他から絞り込む', 'advanced-tag-search'),
                'tags' => array(
                    'おすすめまとめ', '観光コース', '月がきれい', 'kokoradi'
                )
            )
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
