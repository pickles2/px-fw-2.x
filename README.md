# Pickles Framework 2

Pickles Framework(PxFW) は、DB不要、オープンソースのPHP製静的CMS <a href="https://pickles2.com/" target="_blank">Pickles 2</a> のコア機能を提供します。<br />



## 更新履歴 - Change log

### Pickles Framework v2.1.20 (2024年3月20日)

- プラグインのIDが適切に発行されない不具合を修正。
- `$px->realpath_plugin_private_cache()` の返却値が正規化されない場合がある不具合を修正。

### Pickles Framework v2.1.19 (2023年11月13日)

- `autoindex` に、`class` オプションを追加。 CSSでのスタイル制御が容易になった。
- サイトマップキャッシュ生成に関するパフォーマンスを改善した。
- 新しい設定項目 `$conf->sitemap_cache_db_insert_unit_size` を追加した。
- その他、細かい不具合の修正、内部コードの修正など。

### Pickles Framework v2.1.18 (2023年9月24日)

- 内部で扱う時刻情報を ISO 8601 形式 に変更した。

### Pickles Framework v2.1.17 (2023年6月25日)

- 細かい不具合の修正。

### Pickles Framework v2.1.16 (2023年5月1日)

- `$conf->tagline` を追加した。
- `autoindex` の処理を改善。id属性を見出し要素自体に与えるようになった。

### Pickles Framework v2.1.15 (2023年4月22日)

- `$path_content` と `$proc_type` が、`before_content` プラグインの処理を受けて再計算されるようになった。
- `$site->set_page_info()` で、ページタイトルの更新が反映されない場合がある不具合を修正した。
- `$px` で、登録されていない外部からの動的なプロパティを参照しようとしたときに起きるエラーを修正した。
- `PX=publish.run` で、`path_region` を省略しても、 `paths_region` だけで対象範囲を絞れるようになった。
- その他、細かい不具合などの修正。

### Pickles Framework v2.1.14 (2023年2月11日)

- `$px` は、外部からの動的なプロパティ登録を受け付けるようになった。

### Pickles Framework v2.1.13 (2023年2月5日)

- テーマクラス `picklesFramework2\theme\theme` がリンクされなくなっていた不具合を修正。
- PHP設定の初期化処理を更新した。
- 新しい設定項目 `$conf->cookie_default_domain`, `$conf->cookie_default_path`, `$conf->cookie_default_expire` を追加した。
- 初期化時に `session.use_strict_mode = 1` をセットするようになった。
- その他、内部コードの細かい修正。

### Pickles Framework v2.1.12 (2022年12月28日)

- 初期化時に `session.cookie_secure = 1` をセットするようになった。
- サイトマップCSVの読み込み時に、UTF-8 を明示するようになった。
- `$px->h()` を追加した。
- その他、内部コードの細かい修正。

### Pickles Framework v2.1.11 (2022年11月3日)

- セッション関連の設定が正しく反映されない不具合を修正。
- 初期化時に `session.cookie_httponly = 1` をセットするようになった。

### Pickles Framework v2.1.10 (2022年9月25日)

- 内部コードの細かい修正。

### Pickles Framework v2.1.9 (2022年6月5日)

- 一部機能のパフォーマンス改善。
- HTTPレスポンスステータスメッセージを網羅した。

### Pickles Framework v2.1.8 (2022年5月22日)

- `$px->site()->get_sitemap_definition()` を追加。
- `PX=api.get.sitemap_definition` を追加。
- `$conf->custom_sitemap_definition` を追加。

### Pickles Framework v2.1.7 (2022年5月2日)

- ダイナミックパスに関する不具合を修正した。

### Pickles Framework v2.1.6 (2022年1月4日)

- PHP 8.1 に対応した。

### Pickles Framework v2.1.5, v2.0.55 (2021年8月21日)

- 同梱のプラグインが、より直接的な表現で設定できるようになった。

### Pickles Framework v2.1.4, v2.0.54 (2021年7月10日)

- 新しいコンフィグ項目 `$conf->default_lang`、 `$conf->accept_langs` を追加。
- `$px->lang()`、 `$px->set_lang()` を追加。
- 内部コードの細かい不具合の修正。

### Pickles Framework v2.1.3, v2.0.53 (2021年5月25日)

- `$px->get_version()` が示す値を、 v2.1系の番号に統一した。
- その他、内部コードの細かい改善。

### Pickles Framework v2.1.2, v2.0.52 (2021年4月23日)

- プラグイン `autoindex` で、複数の同名の見出しを含むコンテンツで、アンカー名が重複する場合がある問題を修正。
- v2.1系で、SCSSライブラリを更新。(ただし、互換性維持のため、v2.0系では従来のまま)
- 内部コードの細かい改善。

### Pickles Framework v2.1.1, v2.0.51 (2021年2月21日)

- APIが返すパスの、Windowsパスに関する環境依存を修正。

### Pickles Framework v2.1.0, v2.0.50 (2021年1月16日)

- Pickles Framework v2.1.x で、サポート環境を PHP 8 を含む PHP 7.3 以上に変更。 PHP 5.4 〜 7.2 へのサポートは、引き続き Pickles Framework v2.0.x で継続します。
- `$px->internal_sub_request()` に、新しいオプション `method`、 `body`、 `body_file` を追加。
- コマンドラインオプション `--method`、 `--body`、 `--body-file` の処理に関する不具合の修正。
- `--body-file` オプションは、 `px-files/_sys/ram/data/` 内を先に検索するようになった。


## 開発者向け情報 - for Developer

### テスト - Test

```
$ cd {$documentRoot}
$ php vendor/phpunit/phpunit/phpunit;
```


### ドキュメント出力 - phpDocumentor

```
$ wget https://phpdoc.org/phpDocumentor.phar;
$ composer run-script documentation;
```


## ライセンス - License

Copyright (c)2001-2024 Tomoya Koyanagi, and Pickles Project<br />
MIT License https://opensource.org/licenses/mit-license.php


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>
