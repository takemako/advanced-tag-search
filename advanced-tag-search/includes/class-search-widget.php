<?php
/**
 * 検索ウィジェットクラス
 */

if (!defined('ABSPATH')) {
    exit;
}

class ATS_Search_Widget {
    
    /**
     * 検索ボックスのレンダリング
     */
    public static function render_search_box($atts = array()) {
        $settings = get_option('ats_settings', array());
        $placeholder = isset($atts['placeholder']) ? $atts['placeholder'] : 
                      (!empty($settings['placeholder']) ? $settings['placeholder'] : __('タグから探してみる', 'advanced-tag-search'));
        $search_title = isset($atts['title']) ? $atts['title'] : 
                       (!empty($settings['search_title']) ? $settings['search_title'] : '');
        
        ob_start();
        ?>
        <div class="ats-search-container">
            <?php if (!empty($search_title)): ?>
            <h2 class="ats-search-title"><?php echo esc_html($search_title); ?></h2>
            <?php endif; ?>
            <div class="ats-search-box">
                <input type="text" 
                       id="ats-search-input" 
                       class="ats-search-input" 
                       placeholder="<?php echo esc_attr($placeholder); ?>"
                       readonly>
                <button type="button" class="ats-search-button" id="ats-search-button">
                    <svg class="ats-search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * クイックリンクのレンダリング
     */
    public static function render_quick_links($atts = array()) {
        $settings = get_option('ats_settings', array());
        $links = isset($atts['links']) ? explode(',', $atts['links']) : 
                (isset($settings['quick_links']) ? $settings['quick_links'] : array());
        
        if (empty($links)) {
            // デフォルトのクイックリンク
            $links = array(
                array('text' => '川越のお店一覧', 'url' => '/category/shops/'),
                array('text' => '特集記事一覧', 'url' => '/category/features/'),
                array('text' => '川越氷川神社', 'url' => '/tag/hikawa-shrine/'),
                array('text' => '川越でスイーツ', 'url' => '/tag/sweets/'),
                array('text' => 'サイトマップ', 'url' => '/sitemap/'),
            );
        }
        
        ob_start();
        ?>
        <div class="ats-quick-links">
            <?php foreach ($links as $link): ?>
                <?php if (is_array($link)): ?>
                    <a href="<?php echo esc_url($link['url']); ?>" class="ats-quick-link">
                        <?php echo esc_html($link['text']); ?>
                    </a>
                <?php else: ?>
                    <span class="ats-quick-link"><?php echo esc_html($link); ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * モーダルウィンドウのレンダリング
     */
    public static function render_modal() {
        $settings = get_option('ats_settings', array());
        $modal_title = !empty($settings['modal_title']) ? $settings['modal_title'] : __('タグ検索', 'advanced-tag-search');
        
        $tag_manager = new ATS_Tag_Manager();
        $categories = $tag_manager->get_tag_categories();
        
        ob_start();
        ?>
        <div id="ats-modal-overlay" class="ats-modal-overlay" style="display: none;">
            <div class="ats-modal">
                <div class="ats-modal-header">
                    <h2 class="ats-modal-title"><?php echo esc_html($modal_title); ?></h2>
                    <button type="button" class="ats-modal-close" id="ats-modal-close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                
                <div class="ats-modal-body">
                    <?php foreach ($categories as $category_key => $category_data): ?>
                        <div class="ats-tag-category">
                            <h3 class="ats-category-title"><?php echo esc_html($category_data['title']); ?></h3>
                            <div class="ats-tag-list">
                                <?php foreach ($category_data['tags'] as $tag): ?>
                                    <button type="button" 
                                            class="ats-tag" 
                                            data-tag="<?php echo esc_attr($tag); ?>"
                                            data-category="<?php echo esc_attr($category_key); ?>">
                                        #<?php echo esc_html($tag); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="ats-modal-footer">
                    <button type="button" class="ats-search-submit" id="ats-search-submit">
                        <?php _e('選択したタグで絞り込む', 'advanced-tag-search'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 完全な検索ウィジェットのレンダリング
     */
    public static function render_complete_widget($atts = array()) {
        $show_quick_links = isset($atts['show_quick_links']) ? 
                           filter_var($atts['show_quick_links'], FILTER_VALIDATE_BOOLEAN) : true;
        
        $output = self::render_search_box($atts);
        
        if ($show_quick_links) {
            $output .= self::render_quick_links($atts);
        }
        
        $output .= self::render_modal();
        
        return $output;
    }
}
