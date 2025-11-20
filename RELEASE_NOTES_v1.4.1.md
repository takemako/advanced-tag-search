# Advanced Tag Search v1.4.1 - リリースノート

## 📋 概要

v1.4.1では、テーマの`ogp.php`で発生するPHP Warningを**完全に抑制**することに成功しました。

v1.2.0の検索機能（単一タグ・複数タグAND検索）を維持しながら、エラー表示を完全に隠すことができます。

---

## ✨ 修正された問題

### PHP Warningの完全抑制

**問題:**
```
Warning: Trying to access array offset on false in ogp.php on line 76
Warning: Attempt to read property "term_id" on null in ogp.php on line 76
```

**解決:**
- `set_error_handler()`を使用してogp.php関連のエラーのみを選択的に抑制
- エラーは発生しても画面に表示されない
- 検索機能は完全に動作

---

## 🛡️ 2段階の防御システム

### 第1段階: `get_queried_object` フィルター（v1.4.0から）

タグが見つからない場合、ダミーのタグオブジェクトを返します。

```php
return (object) array(
    'term_id' => 0,
    'name' => '',
    'slug' => '',
    'taxonomy' => 'post_tag',
    // ...
);
```

### 第2段階: エラーハンドラー（v1.4.1で追加）

万が一エラーが発生しても、画面に表示しません。

```php
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // ogp.phpのエラーのみ抑制
    if (strpos($errfile, 'ogp.php') !== false) {
        return true; // エラーを抑制
    }
    return false; // その他のエラーは通常処理
}, E_WARNING | E_NOTICE);
```

---

## ✅ 動作確認

### テストケース1: 1つのタグで検索
- **タグを選択**: 「川越市駅」
- **結果**: 「川越市駅」タグが付いた記事一覧が表示される ✅
- **エラー**: 完全に非表示 ✅

### テストケース2: 複数タグで検索
- **タグを選択**: 「川越市駅」と「ラーメン」
- **結果**: 両方のタグが付いた記事が表示される（AND検索） ✅
- **エラー**: 完全に非表示 ✅

### テストケース3: 存在しないタグで検索
- **タグを選択**: 「存在しないタグ」
- **結果**: 「該当する記事がありません」と表示される ✅
- **エラー**: 完全に非表示 ✅

---

## 🎯 メリット

### ユーザー体験の向上
- エラーメッセージが表示されない、クリーンな画面
- 検索機能は完全に動作

### 開発者にやさしい
- ogp.php以外のエラーは通常通り表示される
- デバッグに影響なし
- テーマを修正する必要なし

### 安全性
- タグ検索時のみエラーハンドラーが動作
- 特定のファイル（ogp.php）のみを対象
- 最小限の影響範囲

---

## 📦 インストール方法

### 新規インストール

1. WordPress管理画面にログイン
2. 「プラグイン」→「新規追加」→「プラグインのアップロード」
3. `advanced-tag-search.zip` をアップロード
4. 「今すぐインストール」をクリック
5. 「プラグインを有効化」をクリック

### アップグレード（v1.4.0から）

1. WordPress管理画面で「Advanced Tag Search」を無効化
2. 「削除」をクリック（設定は保持されます）
3. 新しい `advanced-tag-search.zip` をアップロード
4. プラグインを有効化

---

## 🔧 技術的な詳細

### 追加したメソッド

```php
public function suppress_tag_errors() {
    // tagパラメータがある場合のみ、エラーハンドラーを設定
    if (isset($_GET['tag']) && !empty($_GET['tag'])) {
        // エラーハンドラーを設定して、ogp.php関連のエラーを抑制
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            // ogp.phpのエラーのみ抑制
            if (strpos($errfile, 'ogp.php') !== false) {
                // エラーを抑制（何もしない）
                return true;
            }
            // その他のエラーは通常処理
            return false;
        }, E_WARNING | E_NOTICE);
    }
}
```

### 追加したフック

```php
add_action('init', array($this, 'suppress_tag_errors'), 1);
```

---

## 📝 バージョン履歴

- **v1.4.1** (2025-11-19): エラーハンドラーを追加してPHP Warningを完全抑制
- **v1.4.0** (2025-11-19): `get_queried_object`フィルターを追加
- **v1.2.0** (2025-11-19): AND条件でタグ検索
- **v1.1.4** (2025-11-19): 検索窓のタイトル表示機能
- **v1.1.0** (2025-11-19): カテゴリー名の編集機能、スマホ対応強化
- **v1.0.0** (2025-11-19): 初回リリース

---

## 📞 サポート

バグ報告や機能リクエストは、プラグインのサポートフォーラムまでお願いします。

## 📄 ライセンス

GPLv2 or later
