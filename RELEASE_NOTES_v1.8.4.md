# Advanced Tag Search v1.8.4 - リリースノート

## 📋 概要

v1.8.4 では、検索モーダルから**カテゴリー絞り込みを削除**し、タグ検索に一本化しました。表示やカウントが不安定だったカテゴリー絞り込みを取り除き、動作を安定させています。

- **対象バージョン:** 1.8.4
- **必須環境:** WordPress 6.0 以上 / PHP 7.4 以上
- **推奨環境:** WordPress 6.8 / PHP 8.2

---

## 🔄 変更内容

### カテゴリー絞り込みの削除

- 検索モーダルから「**カテゴリーから絞り込む**」セクションを削除しました。
- 管理画面の「**カテゴリー絞り込み設定**」を削除しました。
- 「表示順設定」からカテゴリー項目を削除しました（キーワード／タグの2ブロックになります）。

### 影響しないもの

- **クイックリンク**（検索窓の下に表示されるカテゴリーへのリンク）は、これまでどおり利用できます。
- タグ検索・キーワード検索・記事数表示・記事のないタグの選択不可表示などはそのまま動作します。

---

## ⚠️ テーマ側の警告について

AFFINGER テーマで以下のような警告が表示される場合があります。

```
Warning: Undefined array key "title" in .../themes/affinger/st-title.php
Warning: Undefined array key "meta_keywords" in .../themes/affinger/st-taxonomy.php
Warning: Undefined array key "meta_description" in .../themes/affinger/st-taxonomy.php
```

これらは **AFFINGER テーマ側の PHP 警告**で、当プラグインが出力しているものではありません（当プラグインはこれらのテーマファイルを変更しません）。本番サイトで表示させないためには、`wp-config.php` で次のように設定してください。

```php
define('WP_DEBUG', false);
// もしくはデバッグログのみ取得し、画面には出さない:
// define('WP_DEBUG', true);
// define('WP_DEBUG_DISPLAY', false);
// define('WP_DEBUG_LOG', true);
```

---

## 📦 インストール / アップデート

1. `advanced-tag-search.zip` をダウンロード
2. WordPress 管理画面 →「プラグイン」→「新規追加」→「プラグインのアップロード」
3. ZIP を選択してアップロードし、「今すぐインストール」→「プラグインを有効化」
