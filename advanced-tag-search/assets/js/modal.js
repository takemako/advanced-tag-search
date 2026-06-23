/**
 * Advanced Tag Search - Modal Control
 */

(function($) {
    'use strict';
    
    // 選択されたタグを保持する配列
    let selectedTags = [];

    // 選択されたカテゴリー(スラッグ)を保持する配列
    let selectedCategories = [];

    // 記事数取得用（デバウンス・リクエスト管理）
    let countTimer = null;
    let countXhr = null;
    
    /**
     * 初期化処理
     */
    $(document).ready(function() {
        initModal();
        initTagSelection();
        initSearch();
    });
    
    /**
     * モーダルの初期化
     */
    function initModal() {
        // 検索窓クリックでモーダルを開く
        $(document).on('click', '#ats-search-input, .ats-search-button', function(e) {
            e.preventDefault();
            openModal();
        });
        
        // 閉じるボタンクリック
        $(document).on('click', '#ats-modal-close', function() {
            closeModal();
        });
        
        // オーバーレイクリックで閉じる
        $(document).on('click', '#ats-modal-overlay', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // ESCキーで閉じる
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#ats-modal-overlay').is(':visible')) {
                closeModal();
            }
        });
    }
    
    /**
     * タグ選択の初期化
     */
    function initTagSelection() {
        // タグの選択(カテゴリー絞り込みボタンと無効タグは除外)
        $(document).on('click', '.ats-tag:not(.ats-category-filter):not(.ats-tag-disabled)', function() {
            toggleTag($(this));
        });

        // カテゴリーの選択（無効カテゴリーは除外）
        $(document).on('click', '.ats-category-filter:not(.ats-tag-disabled)', function() {
            toggleCategory($(this));
        });
    }
    
    /**
     * 検索機能の初期化
     */
    function initSearch() {
        $(document).on('click', '#ats-search-submit', function() {
            performSearch();
        });

        // キーワード欄でEnterキーを押したら検索を実行
        $(document).on('keydown', '#ats-keyword-input', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });

        // キーワード入力中も一致する記事数を更新
        $(document).on('input', '#ats-keyword-input', function() {
            updateSearchButton();
        });
    }
    
    /**
     * モーダルを開く
     */
    function openModal() {
        // 現在のスクロール位置を保存
        const scrollY = window.pageYOffset;
        $('body').data('scroll-position', scrollY);

        $('#ats-modal-overlay').fadeIn(300);
        // bodyのスクロールを無効化（背景のスクロールを防ぐ）
        $('body').css({
            'overflow': 'hidden',
            'touch-action': 'none'
        });
    }

    /**
     * モーダルを閉じる
     */
    function closeModal() {
        $('#ats-modal-overlay').fadeOut(300);
        // bodyのスクロールを復元
        $('body').css({
            'overflow': '',
            'touch-action': ''
        });

        // スクロール位置を復元
        const scrollY = $('body').data('scroll-position') || 0;
        window.scrollTo(0, scrollY);
    }
    
    /**
     * タグの選択/解除を切り替え
     */
    function toggleTag($tagElement) {
        const tagName = $tagElement.data('tag');
        
        if ($tagElement.hasClass('selected')) {
            // 選択解除
            $tagElement.removeClass('selected');
            selectedTags = selectedTags.filter(tag => tag !== tagName);
        } else {
            // 選択
            $tagElement.addClass('selected');
            selectedTags.push(tagName);
        }
        
        // 検索ボタンの状態を更新
        updateSearchButton();
    }

    /**
     * カテゴリーの選択/解除を切り替え
     */
    function toggleCategory($categoryElement) {
        const categorySlug = $categoryElement.data('category-slug');

        if ($categoryElement.hasClass('selected')) {
            // 選択解除
            $categoryElement.removeClass('selected');
            selectedCategories = selectedCategories.filter(slug => slug !== categorySlug);
        } else {
            // 選択
            $categoryElement.addClass('selected');
            selectedCategories.push(categorySlug);
        }

        // 検索ボタンの状態を更新
        updateSearchButton();
    }

    /**
     * 検索ボタンの状態を更新
     *
     * 条件が選択されている場合は、一致する記事数を取得してボタンに表示します。
     */
    function updateSearchButton() {
        const $submitButton = $('#ats-search-submit');
        const keyword = ($('#ats-keyword-input').val() || '').trim();
        const hasCondition = selectedTags.length > 0 || selectedCategories.length > 0 || keyword !== '';

        $submitButton.prop('disabled', false);

        if (!hasCondition) {
            $submitButton.text('選択したタグで絞り込む');
            return;
        }

        // 一致する記事数を取得して表示
        fetchPostCount(keyword);
    }

    /**
     * 選択条件に一致する記事数を取得してボタンに反映（デバウンス）
     */
    function fetchPostCount(keyword) {
        const $submitButton = $('#ats-search-submit');

        // 取得中の表示
        $submitButton.text('件数を確認中...');

        clearTimeout(countTimer);
        countTimer = setTimeout(function() {
            if (countXhr) {
                countXhr.abort();
            }

            countXhr = $.ajax({
                url: atsAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ats_get_post_count',
                    nonce: atsAjax.nonce,
                    tags: selectedTags,
                    categories: selectedCategories,
                    keyword: keyword
                },
                success: function(response) {
                    if (response && response.success) {
                        $submitButton.text('選択した条件で絞り込む (' + response.data.count + '件)');
                    } else {
                        $submitButton.text('選択した条件で絞り込む');
                    }
                },
                error: function(jqXHR, textStatus) {
                    // 連続選択でabortした場合は次のリクエストに任せる
                    if (textStatus !== 'abort') {
                        $submitButton.text('選択した条件で絞り込む');
                    }
                }
            });
        }, 300);
    }


    /**
     * 検索を実行
     */
    function performSearch() {
        // キーワード（サイト内検索）を取得
        const keyword = ($('#ats-keyword-input').val() || '').trim();

        if (keyword === '' && selectedTags.length === 0 && selectedCategories.length === 0) {
            // キーワードもタグもカテゴリーも未指定の場合は通常の検索ページへ
            window.location.href = atsAjax.searchUrl;
            return;
        }

        // キーワード・タグ・カテゴリー検索URLを構築
        // 複数選択の場合はカンマ区切りで連結
        const params = [];

        if (keyword !== '') {
            // WordPress標準のサイト内検索パラメータ
            params.push('s=' + encodeURIComponent(keyword));
        }

        if (selectedTags.length > 0) {
            params.push('tag=' + encodeURIComponent(selectedTags.join(',')));
        }

        if (selectedCategories.length > 0) {
            params.push('ats_category=' + encodeURIComponent(selectedCategories.join(',')));
        }

        const searchUrl = atsAjax.searchUrl + '?' + params.join('&');

        // 検索ページへ遷移
        window.location.href = searchUrl;
    }
    
    /**
     * タグ検索用のクエリパラメータを処理
     * (WordPress側でカスタムクエリとして処理する必要があります)
     */
    function buildSearchQuery() {
        const params = new URLSearchParams();
        
        if (selectedTags.length > 0) {
            // タグをカンマ区切りで追加
            params.append('ats_tags', selectedTags.join(','));
        }
        
        return params.toString();
    }
    
    /**
     * Ajax検索(オプション機能)
     * ページ遷移せずに結果を表示したい場合に使用
     */
    function performAjaxSearch() {
        if (selectedTags.length === 0) {
            return;
        }

        $.ajax({
            url: atsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'ats_search_posts',
                nonce: atsAjax.nonce,
                tags: selectedTags
            },
            beforeSend: function() {
                $('#ats-search-submit').prop('disabled', true).text('検索中...');
            },
            success: function(response) {
                if (response.success) {
                    // 検索結果を表示
                    displaySearchResults(response.data);
                    closeModal();
                }
            },
            error: function() {
                console.error('ATS: 検索エラーが発生しました');
            },
            complete: function() {
                $('#ats-search-submit').prop('disabled', false);
                updateSearchButton();
            }
        });
    }
    
    /**
     * 検索結果を表示(オプション機能)
     */
    function displaySearchResults(results) {
        // 検索結果の表示処理
        // 実装はサイトの要件に応じてカスタマイズ
        console.log('Search results:', results);
    }
    
    /**
     * タグをクリアする
     */
    function clearSelectedTags() {
        selectedTags = [];
        selectedCategories = [];
        $('.ats-tag').removeClass('selected');
        $('#ats-keyword-input').val('');
        updateSearchButton();
    }
    
    /**
     * 公開API
     */
    window.ATS = {
        openModal: openModal,
        closeModal: closeModal,
        clearTags: clearSelectedTags,
        getSelectedTags: function() {
            return selectedTags.slice();
        },
        getSelectedCategories: function() {
            return selectedCategories.slice();
        }
    };
    
})(jQuery);
