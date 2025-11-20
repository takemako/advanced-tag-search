# WordPress検索プラグイン設計書

## プラグイン名
**Advanced Tag Search** (advanced-tag-search)

## ディレクトリ構造

```
advanced-tag-search/
├── advanced-tag-search.php          # メインプラグインファイル
├── readme.txt                        # WordPress公式用README
├── assets/
│   ├── css/
│   │   └── style.css                # スタイルシート
│   ├── js/
│   │   └── modal.js                 # モーダル制御JavaScript
│   └── images/
│       └── search-icon.svg          # 検索アイコン
├── includes/
│   ├── class-search-widget.php      # 検索ウィジェットクラス
│   ├── class-tag-manager.php        # タグ管理クラス
│   └── shortcodes.php               # ショートコード定義
└── admin/
    ├── settings.php                  # 管理画面設定
    └── css/
        └── admin-style.css          # 管理画面用CSS
```

## クラス設計

### 1. メインクラス: `Advanced_Tag_Search`
プラグインの初期化と全体管理を担当

**メソッド:**
- `__construct()` - コンストラクタ
- `init()` - 初期化処理
- `enqueue_scripts()` - スクリプト・スタイルの読み込み
- `register_shortcodes()` - ショートコードの登録

### 2. タグ管理クラス: `ATS_Tag_Manager`
タグとカテゴリーの管理を担当

**メソッド:**
- `get_tag_categories()` - タグカテゴリーの取得
- `get_tags_by_category($category)` - カテゴリー別タグ取得
- `save_tag_categories($data)` - タグカテゴリーの保存
- `get_all_tags()` - 全タグの取得
- `get_all_categories()` - 全カテゴリーの取得

### 3. 検索ウィジェットクラス: `ATS_Search_Widget`
検索窓の表示とクイックリンクを担当

**メソッド:**
- `render_search_box()` - 検索窓のHTML生成
- `render_quick_links()` - クイックリンクのHTML生成
- `render_modal()` - モーダルウィンドウのHTML生成

### 4. 管理画面クラス: `ATS_Admin`
WordPress管理画面での設定を担当

**メソッド:**
- `add_menu_page()` - 設定ページの追加
- `render_settings_page()` - 設定画面のレンダリング
- `save_settings()` - 設定の保存

## データベース設計

### オプションテーブル使用
WordPress標準の`wp_options`テーブルを使用

**保存するオプション:**
- `ats_tag_categories` - タグカテゴリーの設定(JSON形式)
- `ats_quick_links` - クイックリンクの設定(配列)
- `ats_search_placeholder` - 検索窓のプレースホルダー
- `ats_modal_title` - モーダルのタイトル

### データ構造例(JSON)
```json
{
  "tag_categories": {
    "genre": {
      "title": "ジャンルから絞り込む",
      "tags": ["食べ歩き", "レストラン", "カフェ"]
    },
    "area": {
      "title": "エリアから絞り込む",
      "tags": ["蔵造りの街並み周辺", "時の鐘周辺"]
    }
  }
}
```

## ショートコード設計

### `[advanced_search]`
検索窓とモーダルを表示

**パラメータ:**
- `placeholder` - プレースホルダーテキスト(デフォルト: "タグから探してみる")
- `show_quick_links` - クイックリンク表示(デフォルト: true)

**使用例:**
```
[advanced_search placeholder="検索してみる" show_quick_links="true"]
```

### `[search_quick_links]`
クイックリンクのみを表示

**パラメータ:**
- `links` - 表示するリンク(カンマ区切り)

**使用例:**
```
[search_quick_links links="カテゴリー1,カテゴリー2"]
```

## JavaScript設計

### modal.js
モーダルの開閉とタグ選択を制御

**主要関数:**
- `openModal()` - モーダルを開く
- `closeModal()` - モーダルを閉じる
- `toggleTag(tagElement)` - タグの選択/解除
- `performSearch()` - 選択されたタグで検索実行
- `loadTags()` - Ajaxでタグデータを読み込み

**イベントハンドラ:**
- 検索窓クリック → モーダル表示
- タグクリック → タグ選択状態切り替え
- 絞り込みボタンクリック → 検索実行
- 閉じるボタン/背景クリック → モーダル閉じる

## CSS設計

### スタイルの構成
1. **検索窓スタイル** (`.ats-search-box`)
2. **クイックリンクスタイル** (`.ats-quick-links`)
3. **モーダルオーバーレイ** (`.ats-modal-overlay`)
4. **モーダル本体** (`.ats-modal`)
5. **タグスタイル** (`.ats-tag`)
6. **レスポンシブ対応** (@media queries)

### カラー変数
```css
:root {
  --ats-primary-color: #A0616A;
  --ats-secondary-color: #E8B4B8;
  --ats-text-color: #333;
  --ats-bg-color: #FFF;
  --ats-overlay-color: rgba(0, 0, 0, 0.5);
}
```

## Ajax API設計

### エンドポイント
`wp-ajax` アクションを使用

**アクション:**
1. `ats_get_tags` - タグ一覧取得
2. `ats_search_posts` - タグ検索実行
3. `ats_save_settings` - 設定保存(管理者のみ)

### レスポンス形式
```json
{
  "success": true,
  "data": {
    "tags": [...],
    "posts": [...]
  }
}
```

## セキュリティ対策

1. **Nonce検証** - Ajax リクエストにnonceを使用
2. **エスケープ処理** - 出力時に`esc_html()`, `esc_url()`を使用
3. **権限チェック** - 管理機能は`current_user_can()`で制限
4. **SQLインジェクション対策** - `$wpdb->prepare()`を使用
5. **XSS対策** - ユーザー入力のサニタイズ

## 国際化対応

- テキストドメイン: `advanced-tag-search`
- 翻訳関数: `__()`, `_e()`, `esc_html__()`を使用
- POT ファイル生成対応
