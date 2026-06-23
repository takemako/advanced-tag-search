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
    $all_wp_tags = $tag_manager->get_all_wp_tags();
    $all_wp_categories = $tag_manager->get_all_wp_categories();
    // クイックリンクに表示するカテゴリーの選択（未設定の場合はnull＝投稿のある全カテゴリー表示）
    $quick_link_categories = isset($settings['quick_link_categories']) && is_array($settings['quick_link_categories'])
        ? $settings['quick_link_categories']
        : null;
    // クイックリンクの候補（投稿のあるカテゴリーのみ）
    $quick_link_candidates = array_values(array_filter($all_wp_categories, function ($wp_category) {
        return !empty($wp_category['count']);
    }));

    // モーダル内ブロックの表示順
    $section_labels = array(
        'keyword'  => __('キーワードで検索', 'advanced-tag-search'),
        'tags'     => __('タグから絞り込む（タグカテゴリー）', 'advanced-tag-search'),
    );
    $default_order = array('keyword', 'tags');
    $section_order = isset($settings['section_order']) && is_array($settings['section_order'])
        ? array_values(array_intersect($settings['section_order'], $default_order))
        : $default_order;
    foreach ($default_order as $section_key) {
        if (!in_array($section_key, $section_order, true)) {
            $section_order[] = $section_key;
        }
    }

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


            <h2><?php _e('表示順設定', 'advanced-tag-search'); ?></h2>
            <p><?php _e('検索モーダル内のブロック（キーワード／タグ／カテゴリー）の表示順を変更できます。▲▼ボタンで並び替えてください。', 'advanced-tag-search'); ?></p>

            <ul id="ats-section-order" class="ats-section-order">
                <?php foreach ($section_order as $section_key): ?>
                    <li class="ats-section-order-item">
                        <span class="ats-section-order-handle dashicons dashicons-menu"></span>
                        <span class="ats-section-order-label"><?php echo esc_html($section_labels[$section_key]); ?></span>
                        <span class="ats-section-order-controls">
                            <button type="button" class="button ats-order-up" aria-label="<?php esc_attr_e('上へ', 'advanced-tag-search'); ?>">▲</button>
                            <button type="button" class="button ats-order-down" aria-label="<?php esc_attr_e('下へ', 'advanced-tag-search'); ?>">▼</button>
                        </span>
                        <input type="hidden" name="ats_section_order[]" value="<?php echo esc_attr($section_key); ?>">
                    </li>
                <?php endforeach; ?>
            </ul>


            <h2><?php _e('タグカテゴリー設定', 'advanced-tag-search'); ?></h2>
            <p><?php _e('各カテゴリーに表示するタグを、WordPressに登録済みのタグから選択してください。「カテゴリーを追加」ボタンで項目を増やせます。', 'advanced-tag-search'); ?></p>

            <?php if (empty($all_wp_tags)): ?>
                <p class="description">
                    <?php
                    printf(
                        wp_kses(
                            __('WordPressにタグが登録されていません。先に<a href="%s">タグを作成</a>してください。', 'advanced-tag-search'),
                            array('a' => array('href' => array()))
                        ),
                        esc_url(admin_url('edit-tags.php?taxonomy=post_tag'))
                    );
                    ?>
                </p>
            <?php else: ?>
                <div id="ats-categories-container">
                    <?php
                    $cat_index = 0;
                    foreach ($tag_categories as $category_data):
                        echo ats_render_category_block($cat_index, $category_data['title'], $category_data['tags'], $all_wp_tags);
                        $cat_index++;
                    endforeach;
                    ?>
                </div>
                <p>
                    <button type="button" class="button" id="ats-add-category">
                        <?php _e('＋ カテゴリーを追加', 'advanced-tag-search'); ?>
                    </button>
                </p>
                <script>
                    var atsCategoryTemplate = <?php echo wp_json_encode(ats_render_category_block('__INDEX__', '', array(), $all_wp_tags)); ?>;
                    var atsCategoryIndex = <?php echo intval($cat_index); ?>;
                </script>
            <?php endif; ?>

            <h2><?php _e('クイックリンク設定', 'advanced-tag-search'); ?></h2>
            <p><?php _e('検索窓の下に表示するクイックリンクを、ブログのカテゴリーから選択してください。リンク先は各カテゴリーのアーカイブページになります。', 'advanced-tag-search'); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label>
                            <?php _e('表示するクイックリンク', 'advanced-tag-search'); ?>
                        </label>
                    </th>
                    <td>
                        <?php if (empty($quick_link_candidates)): ?>
                            <p class="description">
                                <?php
                                printf(
                                    wp_kses(
                                        __('投稿のあるカテゴリーがありません。<a href="%s">カテゴリーを作成</a>し、投稿に割り当ててください。', 'advanced-tag-search'),
                                        array('a' => array('href' => array()))
                                    ),
                                    esc_url(admin_url('edit-tags.php?taxonomy=category'))
                                );
                                ?>
                            </p>
                        <?php else: ?>
                            <p class="ats-checkbox-toolbar">
                                <button type="button" class="button button-small ats-select-all"><?php _e('全選択', 'advanced-tag-search'); ?></button>
                                <button type="button" class="button button-small ats-deselect-all"><?php _e('全解除', 'advanced-tag-search'); ?></button>
                            </p>
                            <div class="ats-tag-checkbox-list">
                                <?php foreach ($quick_link_candidates as $wp_category): ?>
                                    <label class="ats-tag-checkbox">
                                        <input type="checkbox"
                                               name="ats_quick_link_categories[]"
                                               value="<?php echo esc_attr($wp_category['slug']); ?>"
                                               <?php checked(null === $quick_link_categories || in_array($wp_category['slug'], $quick_link_categories, true)); ?>>
                                        <?php echo esc_html($wp_category['name']); ?>
                                        <span class="ats-tag-count">(<?php echo intval($wp_category['count']); ?>)</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="description">
                                <?php _e('クイックリンクとして表示するカテゴリーにチェックを入れてください。すべて未選択の場合はクイックリンクは表示されません。', 'advanced-tag-search'); ?>
                            </p>
                        <?php endif; ?>
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

            <h2><?php _e('動作環境', 'advanced-tag-search'); ?></h2>
            <p><?php _e('このプラグインの必須・推奨バージョンと、現在の環境を表示します。', 'advanced-tag-search'); ?></p>

            <?php
            global $wp_version;
            $env_rows = array(
                array(
                    'label'       => 'PHP',
                    'current'     => PHP_VERSION,
                    'min'         => ATS_MIN_PHP,
                    'recommended' => ATS_RECOMMENDED_PHP,
                ),
                array(
                    'label'       => 'WordPress',
                    'current'     => $wp_version,
                    'min'         => ATS_MIN_WP,
                    'recommended' => ATS_RECOMMENDED_WP,
                ),
            );
            ?>
            <table class="form-table">
                <?php foreach ($env_rows as $row): ?>
                    <?php
                    if (version_compare($row['current'], $row['min'], '<')) {
                        $status = '✕ ' . __('必須バージョンを下回っています', 'advanced-tag-search');
                        $color  = '#d63638';
                    } elseif (version_compare($row['current'], $row['recommended'], '<')) {
                        $status = '△ ' . __('動作可能（推奨バージョン未満）', 'advanced-tag-search');
                        $color  = '#dba617';
                    } else {
                        $status = '✓ ' . __('推奨環境を満たしています', 'advanced-tag-search');
                        $color  = '#00a32a';
                    }
                    ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($row['label']); ?></th>
                        <td>
                            <?php
                            printf(
                                /* translators: 1: 現在, 2: 必須, 3: 推奨 */
                                esc_html__('現在: %1$s ／ 必須: %2$s 以上 ／ 推奨: %3$s 以上', 'advanced-tag-search'),
                                esc_html($row['current']),
                                esc_html($row['min']),
                                esc_html($row['recommended'])
                            );
                            ?>
                            <p class="description" style="color: <?php echo esc_attr($color); ?>; font-weight: 500;">
                                <?php echo esc_html($status); ?>
                            </p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php submit_button(__('設定を保存', 'advanced-tag-search'), 'primary', 'ats_save_settings'); ?>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // 全選択（ツールバー直後のチェックボックス一覧を対象）
        $(document).on('click', '.ats-select-all', function() {
            $(this).closest('.ats-checkbox-toolbar').next('.ats-tag-checkbox-list')
                .find('input[type="checkbox"]').prop('checked', true);
        });

        // 全解除
        $(document).on('click', '.ats-deselect-all', function() {
            $(this).closest('.ats-checkbox-toolbar').next('.ats-tag-checkbox-list')
                .find('input[type="checkbox"]').prop('checked', false);
        });

        // タグカテゴリーを追加
        $('#ats-add-category').on('click', function() {
            if (typeof atsCategoryTemplate === 'undefined') {
                return;
            }
            var html = atsCategoryTemplate.replace(/__INDEX__/g, atsCategoryIndex);
            $('#ats-categories-container').append(html);
            atsCategoryIndex++;
        });

        // タグカテゴリーを削除
        $(document).on('click', '.ats-remove-category', function() {
            $(this).closest('.ats-category-block').remove();
        });

        // 表示順: 上へ
        $(document).on('click', '.ats-order-up', function() {
            var $item = $(this).closest('.ats-section-order-item');
            var $prev = $item.prev('.ats-section-order-item');
            if ($prev.length) {
                $prev.before($item);
            }
        });

        // 表示順: 下へ
        $(document).on('click', '.ats-order-down', function() {
            var $item = $(this).closest('.ats-section-order-item');
            var $next = $item.next('.ats-section-order-item');
            if ($next.length) {
                $next.after($item);
            }
        });
    });
    </script>
    <?php
}

/**
 * タグカテゴリー1件分の入力ブロックをHTMLで返す
 *
 * @param string|int $index         フィールドのインデックス（新規追加用に __INDEX__ を渡せる）
 * @param string     $title         カテゴリー名
 * @param array      $selected_tags 選択済みタグ名の配列
 * @param array      $all_wp_tags   WordPressの全タグ（get_all_wp_tags の戻り値）
 * @return string
 */
function ats_render_category_block($index, $title, $selected_tags, $all_wp_tags) {
    $selected_tags = is_array($selected_tags) ? $selected_tags : array();

    ob_start();
    ?>
    <div class="ats-category-block" data-index="<?php echo esc_attr($index); ?>">
        <div class="ats-category-block-header">
            <input type="text"
                   name="ats_category_title[<?php echo esc_attr($index); ?>]"
                   value="<?php echo esc_attr($title); ?>"
                   class="regular-text ats-category-title-input"
                   placeholder="<?php esc_attr_e('カテゴリー名（例: ジャンルから絞り込む）', 'advanced-tag-search'); ?>">
            <button type="button" class="button-link-delete ats-remove-category">
                <?php _e('このカテゴリーを削除', 'advanced-tag-search'); ?>
            </button>
        </div>
        <p class="ats-checkbox-toolbar">
            <button type="button" class="button button-small ats-select-all"><?php _e('全選択', 'advanced-tag-search'); ?></button>
            <button type="button" class="button button-small ats-deselect-all"><?php _e('全解除', 'advanced-tag-search'); ?></button>
        </p>
        <div class="ats-tag-checkbox-list">
            <?php foreach ($all_wp_tags as $wp_tag): ?>
                <label class="ats-tag-checkbox">
                    <input type="checkbox"
                           name="ats_category_tags[<?php echo esc_attr($index); ?>][]"
                           value="<?php echo esc_attr($wp_tag['name']); ?>"
                           <?php checked(in_array($wp_tag['name'], $selected_tags, true)); ?>>
                    <?php echo esc_html($wp_tag['name']); ?>
                    <span class="ats-tag-count">(<?php echo intval($wp_tag['count']); ?>)</span>
                </label>
            <?php endforeach; ?>
        </div>
        <p class="description">
            <?php _e('このカテゴリーに表示するタグにチェックを入れてください。', 'advanced-tag-search'); ?>
        </p>
    </div>
    <?php
    return ob_get_clean();
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
    );

    // クイックリンクに表示するカテゴリーの保存（チェックされたスラッグのみ。未チェックなら空配列）
    $quick_link_categories = array();
    if (isset($_POST['ats_quick_link_categories']) && is_array($_POST['ats_quick_link_categories'])) {
        $quick_link_categories = array_map('sanitize_title', $_POST['ats_quick_link_categories']);
    }
    $settings['quick_link_categories'] = $quick_link_categories;

    // モーダル内ブロックの表示順を保存（既知キーのみ・重複除去・欠けは末尾補完）
    $allowed_sections = array('keyword', 'tags', 'category');
    $section_order = array();
    if (isset($_POST['ats_section_order']) && is_array($_POST['ats_section_order'])) {
        foreach ($_POST['ats_section_order'] as $section_key) {
            $section_key = sanitize_key($section_key);
            if (in_array($section_key, $allowed_sections, true) && !in_array($section_key, $section_order, true)) {
                $section_order[] = $section_key;
            }
        }
    }
    foreach ($allowed_sections as $section_key) {
        if (!in_array($section_key, $section_order, true)) {
            $section_order[] = $section_key;
        }
    }
    $settings['section_order'] = $section_order;

    update_option('ats_settings', $settings);
    
    // タグカテゴリーの保存（チェックされたタグのみを保存。キーは連番で再採番）
    if (isset($_POST['ats_category_title']) && is_array($_POST['ats_category_title'])) {
        $tag_categories = array();
        $n = 0;

        foreach ($_POST['ats_category_title'] as $index => $title) {
            $title = sanitize_text_field($title);

            // チェックされたタグを取得（未チェックの場合はPOSTに含まれないため空配列）
            $selected_tags = array();
            if (isset($_POST['ats_category_tags'][$index]) && is_array($_POST['ats_category_tags'][$index])) {
                $selected_tags = array_map('sanitize_text_field', $_POST['ats_category_tags'][$index]);
            }

            // カテゴリー名・タグともに空のカテゴリーは保存しない
            if ('' === $title && empty($selected_tags)) {
                continue;
            }

            $tag_categories['cat_' . $n] = array(
                'title' => '' !== $title ? $title : __('絞り込み', 'advanced-tag-search'),
                'tags'  => $selected_tags,
            );
            $n++;
        }

        update_option('ats_tag_categories', $tag_categories);
    }
}
