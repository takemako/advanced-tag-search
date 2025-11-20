<?php
/**
 * 管理画面設定
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 設定ページのレンダリング
 */
function ats_render_settings_page() {
    // 権限チェック
    if (!current_user_can('manage_options')) {
        wp_die(__('このページにアクセスする権限がありません。', 'advanced-tag-search'));
    }
    
    // 設定の保存処理
    if (isset($_POST['ats_save_settings']) && check_admin_referer('ats_settings_nonce')) {
        ats_save_settings();
        echo '<div class="notice notice-success is-dismissible"><p>' . 
             __('設定を保存しました。', 'advanced-tag-search') . '</p></div>';
    }
    
    // 現在の設定を取得
    $settings = get_option('ats_settings', array());
    $tag_manager = new ATS_Tag_Manager();
    $tag_categories = $tag_manager->get_tag_categories();
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('ats_settings_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ats_search_title">
                            <?php _e('検索窓のタイトル', 'advanced-tag-search'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="ats_search_title" 
                               name="ats_search_title" 
                               value="<?php echo esc_attr($settings['search_title'] ?? ''); ?>" 
                               class="regular-text">
                        <p class="description">
                            <?php _e('検索窓の上に表示されるタイトルを設定します（例: 川越の気になる！を探す）', 'advanced-tag-search'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ats_placeholder">
                            <?php _e('検索窓のプレースホルダー', 'advanced-tag-search'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="ats_placeholder" 
                               name="ats_placeholder" 
                               value="<?php echo esc_attr($settings['placeholder'] ?? 'タグから探してみる'); ?>" 
                               class="regular-text">
                        <p class="description">
                            <?php _e('検索窓に表示されるプレースホルダーテキストを設定します。', 'advanced-tag-search'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ats_modal_title">
                            <?php _e('モーダルのタイトル', 'advanced-tag-search'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="ats_modal_title" 
                               name="ats_modal_title" 
                               value="<?php echo esc_attr($settings['modal_title'] ?? 'タグ検索'); ?>" 
                               class="regular-text">
                        <p class="description">
                            <?php _e('モーダルウィンドウのタイトルを設定します。', 'advanced-tag-search'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <h2><?php _e('色設定', 'advanced-tag-search'); ?></h2>
            <p><?php _e('検索ボタンとアイコンの色をカスタマイズできます。', 'advanced-tag-search'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ats_search_icon_color">
                            <?php _e('虹眼鏡アイコンの色', 'advanced-tag-search'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="ats_search_icon_color" 
                               name="ats_search_icon_color" 
                               value="<?php echo esc_attr($settings['search_icon_color'] ?? '#666666'); ?>" 
                               class="ats-color-picker">
                        <p class="description">
                            <?php _e('検索窓の虹眼鏡アイコンの色を設定します。（デフォルト: #666666）', 'advanced-tag-search'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ats_button_color">
                            <?php _e('検索ボタンの色', 'advanced-tag-search'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="ats_button_color" 
                               name="ats_button_color" 
                               value="<?php echo esc_attr($settings['button_color'] ?? '#2196F3'); ?>" 
                               class="ats-color-picker">
                        <p class="description">
                            <?php _e('モーダル内の「絞り込む」ボタンの色を設定します。（デフォルト: #2196F3）', 'advanced-tag-search'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            
            <h2><?php _e('タグカテゴリー設定', 'advanced-tag-search'); ?></h2>
            <p><?php _e('各カテゴリーのタグをカンマ区切りで入力してください。', 'advanced-tag-search'); ?></p>
            
            <table class="form-table">
                <?php foreach ($tag_categories as $category_key => $category_data): ?>
                <tr>
                    <th scope="row">
                        <label for="ats_category_title_<?php echo esc_attr($category_key); ?>">
                            <?php _e('カテゴリー名', 'advanced-tag-search'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="ats_category_title_<?php echo esc_attr($category_key); ?>"
                               name="ats_category_title[<?php echo esc_attr($category_key); ?>]" 
                               value="<?php echo esc_attr($category_data['title']); ?>"
                               class="regular-text"
                               placeholder="<?php echo esc_attr($category_data['title']); ?>">
                        <p class="description">
                            <?php _e('モーダルに表示されるカテゴリー名を設定します（例: ジャンルから絞り込む）', 'advanced-tag-search'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ats_category_<?php echo esc_attr($category_key); ?>">
                            <?php _e('タグ一覧', 'advanced-tag-search'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea id="ats_category_<?php echo esc_attr($category_key); ?>" 
                                  name="ats_category_tags[<?php echo esc_attr($category_key); ?>]" 
                                  rows="3" 
                                  class="large-text"><?php echo esc_textarea(implode(', ', $category_data['tags'])); ?></textarea>
                        <p class="description">
                            <?php _e('タグをカンマ区切りで入力してください（例: タグ1, タグ2, タグ3）', 'advanced-tag-search'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;"></td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <h2><?php _e('クイックリンク設定', 'advanced-tag-search'); ?></h2>
            <p><?php _e('検索窓の下に表示するクイックリンクを設定します。', 'advanced-tag-search'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ats_quick_links">
                            <?php _e('クイックリンク', 'advanced-tag-search'); ?>
                        </label>
                    </th>
                    <td>
                        <div id="ats-quick-links-container">
                            <?php
                            $quick_links = isset($settings['quick_links']) ? $settings['quick_links'] : array();
                            if (empty($quick_links)) {
                                $quick_links = array(
                                    array('text' => '川越のお店一覧', 'url' => '/category/shops/'),
                                    array('text' => '特集記事一覧', 'url' => '/category/features/'),
                                );
                            }
                            
                            foreach ($quick_links as $index => $link):
                            ?>
                            <div class="ats-quick-link-item" style="margin-bottom: 10px;">
                                <input type="text" 
                                       name="ats_quick_link_text[]" 
                                       value="<?php echo esc_attr($link['text']); ?>" 
                                       placeholder="リンクテキスト" 
                                       style="width: 200px; margin-right: 10px;">
                                <input type="text" 
                                       name="ats_quick_link_url[]" 
                                       value="<?php echo esc_attr($link['url']); ?>" 
                                       placeholder="URL" 
                                       style="width: 300px; margin-right: 10px;">
                                <button type="button" class="button ats-remove-link">削除</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="ats-add-link" class="button">リンクを追加</button>
                        <p class="description">
                            <?php _e('クイックリンクのテキストとURLを設定します。', 'advanced-tag-search'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <h2><?php _e('ショートコード', 'advanced-tag-search'); ?></h2>
            <p><?php _e('以下のショートコードをページやウィジェットに貼り付けて使用してください。', 'advanced-tag-search'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('基本的な使用', 'advanced-tag-search'); ?></th>
                    <td>
                        <code>[advanced_search]</code>
                        <p class="description">
                            <?php _e('検索窓、クイックリンク、モーダルを表示します。', 'advanced-tag-search'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('カスタマイズ', 'advanced-tag-search'); ?></th>
                    <td>
                        <code>[advanced_search placeholder="検索してみる" show_quick_links="false"]</code>
                        <p class="description">
                            <?php _e('プレースホルダーやクイックリンクの表示をカスタマイズできます。', 'advanced-tag-search'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('クイックリンクのみ', 'advanced-tag-search'); ?></th>
                    <td>
                        <code>[search_quick_links]</code>
                        <p class="description">
                            <?php _e('クイックリンクのみを表示します。', 'advanced-tag-search'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('設定を保存', 'advanced-tag-search'), 'primary', 'ats_save_settings'); ?>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // リンク追加
        $('#ats-add-link').on('click', function() {
            const html = '<div class="ats-quick-link-item" style="margin-bottom: 10px;">' +
                '<input type="text" name="ats_quick_link_text[]" placeholder="リンクテキスト" style="width: 200px; margin-right: 10px;">' +
                '<input type="text" name="ats_quick_link_url[]" placeholder="URL" style="width: 300px; margin-right: 10px;">' +
                '<button type="button" class="button ats-remove-link">削除</button>' +
                '</div>';
            $('#ats-quick-links-container').append(html);
        });
        
        // リンク削除
        $(document).on('click', '.ats-remove-link', function() {
            $(this).closest('.ats-quick-link-item').remove();
        });
    });
    </script>
    <?php
}

/**
 * 設定の保存
 */
function ats_save_settings() {
    // 基本設定
    $settings = array(
        'search_title' => sanitize_text_field($_POST['ats_search_title'] ?? ''),
        'placeholder' => sanitize_text_field($_POST['ats_placeholder'] ?? ''),
        'modal_title' => sanitize_text_field($_POST['ats_modal_title'] ?? ''),
        'search_icon_color' => sanitize_hex_color($_POST['ats_search_icon_color'] ?? '#666666'),
        'button_color' => sanitize_hex_color($_POST['ats_button_color'] ?? '#2196F3'),
        'quick_links' => array(),
    );
    
    // クイックリンクの保存
    if (isset($_POST['ats_quick_link_text']) && isset($_POST['ats_quick_link_url'])) {
        $texts = $_POST['ats_quick_link_text'];
        $urls = $_POST['ats_quick_link_url'];
        
        for ($i = 0; $i < count($texts); $i++) {
            if (!empty($texts[$i]) && !empty($urls[$i])) {
                $settings['quick_links'][] = array(
                    'text' => sanitize_text_field($texts[$i]),
                    'url' => esc_url_raw($urls[$i]),
                );
            }
        }
    }
    
    update_option('ats_settings', $settings);
    
    // タグカテゴリーの保存
    if (isset($_POST['ats_category_tags']) && isset($_POST['ats_category_title'])) {
        $tag_categories = array();
        
        foreach ($_POST['ats_category_tags'] as $category_key => $tags_string) {
            $tags = array_map('trim', explode(',', $tags_string));
            $tags = array_filter($tags); // 空要素を削除
            
            $tag_categories[sanitize_key($category_key)] = array(
                'title' => sanitize_text_field($_POST['ats_category_title'][$category_key]),
                'tags' => array_map('sanitize_text_field', $tags),
            );
        }
        
        update_option('ats_tag_categories', $tag_categories);
    }
}
