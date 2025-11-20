/**
 * Advanced Tag Search - Modal Control
 */

(function($) {
    'use strict';
    
    // 選択されたタグを保持する配列
    let selectedTags = [];
    
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
        $(document).on('click', '.ats-tag', function() {
            toggleTag($(this));
        });
    }
    
    /**
     * 検索機能の初期化
     */
    function initSearch() {
        $(document).on('click', '#ats-search-submit', function() {
            performSearch();
        });
    }
    
    /**
     * モーダルを開く
     */
    function openModal() {
        $('#ats-modal-overlay').fadeIn(300);
        // bodyのスクロールを無効化（背景のスクロールを防ぐ）
        $('body').css({
            'overflow': 'hidden',
            'position': 'fixed',
            'width': '100%'
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
            'position': '',
            'width': ''
        });
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
     * 検索ボタンの状態を更新
     */
    function updateSearchButton() {
        const $submitButton = $('#ats-search-submit');
        
        if (selectedTags.length > 0) {
            $submitButton.prop('disabled', false);
            $submitButton.text('選択したタグで絞り込む (' + selectedTags.length + ')');
        } else {
            $submitButton.prop('disabled', false);
            $submitButton.text('選択したタグで絞り込む');
        }
    }
    
    /**
     * 検索を実行
     */
    function performSearch() {
        if (selectedTags.length === 0) {
            // タグが選択されていない場合は通常の検索ページへ
            window.location.href = atsAjax.searchUrl;
            return;
        }
        
        // タグ検索URLを構築
        // 複数タグの場合はカンマ区切りで連結
        const tagQuery = selectedTags.join(',');
        const searchUrl = atsAjax.searchUrl + '?tag=' + encodeURIComponent(tagQuery);
        
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
            alert('タグを選択してください');
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
                } else {
                    alert('検索に失敗しました');
                }
            },
            error: function() {
                alert('エラーが発生しました');
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
        $('.ats-tag').removeClass('selected');
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
        }
    };
    
})(jQuery);
