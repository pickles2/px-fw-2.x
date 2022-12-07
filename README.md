# Pickles Framework 2

<table>
  <thead>
    <tr>
      <th></th>
      <th>Linux</th>
      <th>Windows</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th>master</th>
      <td align="center">
        <a href="https://travis-ci.org/pickles2/px-fw-2.x"><img src="https://secure.travis-ci.org/pickles2/px-fw-2.x.svg?branch=master"></a>
      </td>
      <td align="center">
        <a href="https://ci.appveyor.com/project/tomk79/px-fw-2-x"><img src="https://ci.appveyor.com/api/projects/status/bq8v3bgfrhbvr6rv/branch/master?svg=true"></a>
      </td>
    </tr>
    <tr>
      <th>develop</th>
      <td align="center">
        <a href="https://travis-ci.org/pickles2/px-fw-2.x"><img src="https://secure.travis-ci.org/pickles2/px-fw-2.x.svg?branch=develop"></a>
      </td>
      <td align="center">
        <a href="https://ci.appveyor.com/project/tomk79/px-fw-2-x"><img src="https://ci.appveyor.com/api/projects/status/bq8v3bgfrhbvr6rv/branch/develop?svg=true"></a>
      </td>
    </tr>
  </tbody>
</table>


Pickles Framework(PxFW) は、静的で大きなウェブサイトを効率よく構築できる オープンソースのHTML生成ツールです。<br />
データベース不要、PHPが動くサーバーに手軽に導入でき、プロトタイプ制作を中心に進めるような柔軟な制作スタイルを実現します。

Pickles 2 は、[PxFW-1.x](https://github.com/tomk79/PxFW-1.x) の後継です。
主な改善点は次の通りです。

- `composer` からインストールできるようになりました。
- 機能の追加、拡張が手軽にできるようになりました。
- コマンドラインからの実行が改善され、外部のツールやスクリプトとの連携が容易になりました。
- その他、よりシンプルに利用できるよう、多くの機能が改善されました。



## インストール手順 - Install

Pickles Framework 2.x はラッパーである [Get start "Pickles 2" !](https://github.com/pickles2/preset-get-start-pickles2) からの利用をおすすめします。

```
$ cd {$documentRoot}
$ composer create-project pickles2/preset-get-start-pickles2 ./
```

`.px_execute.php` の置かれたディレクトリがドキュメントルートになるよう、ウェブサーバーを設定してください。

Pickles Framework が書き込みを行うディレクトリがあります。次のコマンドは、書き込み権限を付与するためのものです。すでに権限がある場合は実行する必要はありません。

```
$ chmod -R 777 ./px-files/_sys
$ chmod -R 777 ./caches
```



## システム要件 - System Requirement

- Linux系サーバ または Windowsサーバ
- Apache
  - mod_rewrite が利用可能であること
  - .htaccess が利用可能であること
- PHP 7.3 以上
  - [mbstring](https://www.php.net/manual/ja/book.mbstring.php) PHP Extension
  - [JSON](https://www.php.net/manual/ja/book.json.php) PHP Extension
  - [PDO](https://www.php.net/manual/ja/book.pdo.php) PHP Extension
  - [PDO SQLite (PDO_SQLITE)](https://www.php.net/manual/ja/ref.pdo-sqlite.php) PHP Extension

プラグインなど他のパッケージとの構成によって、いくつかの要件が追加される場合があります。
依存パッケージのシステム要件も確認してください。



## 更新履歴 - Change log

### Pickles Framework v2.1.12 (リリース日未定)

- 初期化時に `session.cookie_secure = 1` をセットするようになった。

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

### Pickles Framework v2.0.49 (2020年12月19日)

- 内部コードの細かい改善。

### Pickles Framework v2.0.48 (2020年10月17日)

- 内部コードの細かい改善。

### Pickles Framework v2.0.47 (2020年6月21日)

- 外部依存パッケージのバージョンを更新。

### Pickles Framework v2.0.46 (2020年6月7日)

- `$px->mk_link()` に `target` オプションを追加。

### Pickles Framework v2.0.45 (2020年4月4日)

- 外部依存パッケージのバージョンを更新。
- パブリッシュ時に、開始時刻と終了時刻を `timelog.txt` に記録するようになった。

### Pickles Framework v2.0.44 (2020年1月2日)

- PHP 7.4 に対応した。
- `$_SERVER['REMOTE_ADDR']` がない場合に、 `null` で初期化するようになった。
- その他、軽微な内部コードの修正。

### Pickles Framework v2.0.43 (2019年12月13日)

- `$site->get_path_param()` の引数を省略できない不具合を修正。
- コマンドラインオプション `--method`、 `--body`、 `--body-file` を追加。

### Pickles Framework v2.0.42 (2019年9月4日)

- パブリッシュが2重に起動することがある問題を修正。
- サイトマップキャッシュに関する内部処理の修正。

### Pickles Framework v2.0.41 (2019年6月8日)

- パブリッシュのパラメータ `path_region`、`paths_region`、`paths_ignore` で、各行の先頭にスラッシュを補完するようになった。

### Pickles Framework v2.0.40 (2019年4月19日)

- `$site->get_page_originated_csv()` を追加。
- `PX=api.get.page_originated_csv` を追加。
- `PX=api.get.page_info` が、パラメータ `path` を省略した場合に、カレントページを探すようになった。
- `encodingconverter` に `ext` オプションを追加。対象の拡張子を制限できるようになった。
- `$site->get_pdo()` を追加。

### Pickles Framework v2.0.39 (2018年10月19日)

- Windows + PHP7 の環境で、CSV ファイルを正しく読み込めない問題に対応した。

### Pickles Framework v2.0.38 (2018年8月30日)

- サイトマップの `path` に、 Dataスキーマを扱えるようになった。
- 特殊なURL(`javascript:`、`data:` など)をパブリッシュしようとしてエラーになる不具合を修正。
- その他の細かい不具合を修正。

### Pickles Framework v2.0.37 (2018年2月28日)

- PHP 7.2 で SCSS のプレビューが失敗する問題を修正。
- 依存ライブラリ `michelf/php-markdown`, `leafo/scssphp` のバージョンを更新。

### Pickles Framework v2.0.36 (2018年2月15日)

- PHP 7.2 で、Warning が発生する問題を修正。

### Pickles Framework v2.0.35 (2018年1月24日)

- `$px->canonical()` を追加。
- `PX=api.get.canonical` を追加。
- `$px->mk_link()` に、 `canonical` オプションを追加。
- `$conf->path_files` に、コールバックを設定できるようになった。
- サイトマップ項目のうち、`id`, `path`, `content`, `logical_path`, `list_flg`, `layout`, `orderby`, `category_top_flg`, `role`, `proc_type` の前後の空白文字列を自動的に削除して扱うようになった。

### Pickles Framework v2.0.34 (2017年11月2日)

- [bugfix] .px_execute.php の絶対パス中にスペースが含まれている場合にパブリッシュが正常に処理されない不具合を修正。

### Pickles Framework v2.0.33 (2017年9月21日)

- `$site()->get_path_param()` で存在しないキーを要求した場合にエラーが起きる問題を修正した。
- `$px->internal_sub_request()` が、サブリクエストが発行した標準エラー出力を `$px->error()` に転送するようになった。
- 新しい設定項目 `$conf->scheme` と、対応するゲッターメソッド `$px->get_scheme()` を追加。
- その他、軽微な不具合の修正。

### Pickles Framework v2.0.32 (2017年5月30日)

- `$_SERVER['HTTP_USER_AGENT']` が存在しない場合、空白文字列で初期化するようになった。
- パブリッシュ時に、一時パブリッシュディレクトリに存在せずパブリッシュ先ディレクトリに存在する除外設定されたディレクトリが削除されてしまうことがある不具合を修正。

### Pickles Framework v2.0.31 (2017年4月20日)

- `$px->internal_sub_request()` を追加。
- `$px->bowl()->get()` を追加。
- `$px->bowl()->send()` を `$px->bowl()->put()` に改名。(古いメソッド名の実装は残されているが非推奨)
- `$px->bowl()->pull()` を `$px->bowl->get_clean()` に改名。(古いメソッド名の実装は残されているが非推奨)
- `PX=publish` の `paths_ignore` オプションで、ワイルドカード `*` が使えるようになった。
- プラグインの引数が空白の `()` で記述された場合に、空白文字列ではなく `null` として扱われるように修正。
- プラグインの引数内に改行コードを受け付けられない不具合を修正。

### Pickles Framework v2.0.30 (2017年3月8日)

- `$px->href()` が、 hash を query として置き換えてしまうことがある不具合を修正。
- `path` に hash や query を持つページを正常にパブリッシュできない不具合を修正。

### Pickles Framework v2.0.29 (2017年2月6日)

- サイトマップ項目に `proc_type` を追加。 `$conf->paths_proc_type` と同様の効果だが、サイトマップ上で設定できるようになった。
- クラス `site`, `bowl`, `pxcmd` のAPIを外部から呼び出せるようにした。
- パブリッシュコマンドの最後に、検出したアラートログを表示するようにした。
- パブリッシュコマンドの最後に、パブリッシュ処理にかかった時間を表示するようにした。
- `$px->get_path_homedir()` を `$px->get_realpath_homedir()` に改名。(古いメソッド名の実装は残されているが非推奨)
- `$px->get_path_docroot()` を `$px->get_realpath_docroot()` に改名。(古いメソッド名の実装は残されているが非推奨)
- メソッド名の改名に合わせて、 `PX=api.*` もそれぞれ改名。(古い名前のAPIの実装は残されているが非推奨)
- パブリッシュのパフォーマンスを改善。
- デフォルトの Content-type を proc_type の値を参照して決定するように変更した。
- `$px->header()`, `$px->header_list()` を追加。
- JSONでの出力時(コマンドラインオプション `-o json` 付加時)、 `header` に HTTPヘッダー情報が出力されるようになった。

### Pickles Framework v2.0.28 (2016年12月8日)

- Windowsサーバーで、サイトマップキャッシュが排他ロックされて更新に失敗することがある問題を修正。
- パブリッシュプラグインを `before_sitemap` に設定しても動作するように変更。サイトマップ生成のパフォーマンスが改善する。
- `$site` が既にセットされている場合に、再生成せずそのまま利用するようになった。実質、 `before_sitemap` に設定したプラグインから `$site` の挙動を変更することが可能になった。

### Pickles Framework v2.0.27 (2016年11月21日)

- サイトマップキャッシュ生成中の2件目以降のリクエストに関するパフォーマンスを改善。待ち時間がなくなった。
- サイトマップキャッシュ生成開始から60分経過しても進捗した形跡がなければ、再生成するようになった。
- サイトマップキャッシュ生成中の2件目以降のリクエストで、古いキャッシュが利用できない場合、仮のトップページがセットされるようになった。
- サイトマップキャッシュ生成中の2件目以降のリクエストで、古いキャッシュが利用できる場合、それを利用するようになった。
- PXコマンド `PX=publish.version`, `PX=clearcache.version` を追加。
- パブリッシュ時、エラーを含むページも、削除されずに出力されるようになった。

### Pickles Framework v2.0.26 (2016年10月17日)

- サイトマップキャッシュ生成中の2件目以降のリクエストに関するパフォーマンスが改善した。
- 依存ライブラリ michelf/php-markdown, leafo/scssphp のバージョンを更新。
- `PX=api` がサイトマップを利用できない場合に、サイトマップ操作のAPIが `false` を返すようになった。

### Pickles Framework v2.0.25 (2016年9月28日)

- サイトマップに載っていないファイルを単体でパブリッシュできない不具合を修正。
- パブリッシュ範囲をファイル単体で指定した場合の、2重拡張子によるファイル名の揺れを吸収するようになった。

### Pickles Framework v2.0.24 (2016年9月22日)

- proc_type が `pass` 、 `ignore` の場合に、 `$conf->funcs->before_sitemap`, `$conf->funcs->before_content` に設定されたPXコマンドが実行されるようになった。
- パブリッシュ範囲に具体的なファイル名を指定した場合のパフォーマンスが向上した。

### Pickles Framework v2.0.23 (2016年8月24日)

- パブリッシュのオプション `keep_cache` を追加。キャッシュの消去と再生成のプロセスをスキップできるようになった。
- パブリッシュのオプション `paths_region` を追加。パブリッシュ対象範囲を複数指定できるようになった。
- コマンドラインからの起動時にも、 `$_SERVER['DOCUMENT_ROOT']` を使用できるようになった。
- サイトマップに含まれる外部URLが `/index.html` で終わっている場合に、ページとして正しく処理できない不具合を修正。

### Pickles Framework v2.0.22 (2016年7月27日)

- コンフィグ項目 `$conf->paths_enable_sitemap` を追加。
- `$conf->paths_proc_type` に、新しい処理方法 pass を追加。デフォルトを pass に変更。
- `$conf->paths_proc_type` が direct のときに、二重拡張子の処理を適用できるようになった。
- 他プロセスがサイトマップキャッシュを生成中にアクセスした場合にサイトマップキャッシュ生成をスキップするアプリケーションロック機能を追加。
- Windows で Apache 上で実行する場合に、 `$px->get_path_controot()` 等のパスがずれてしまう不具合を修正。

### Pickles Framework v2.0.21 (2016年7月14日)

- `$conf->paths_proc_type` の設定を、前方マッチから完全マッチに変更。 ディレクトリの指定等は、ワイルドカード `*` を使って表現する方針で統一。
- `PX=publish` に、パブリッシュ対象外のパスをコンフィグオプションで設定できる機能を追加。 (コマンドラインオプションで除外する方法は従来から存在していた)
- 公開キャッシュ と `_sys/ram/*` のディレクトリが存在しない場合に、作成を試みるように変更。
- `path_publish_dir` と `contents_manifesto` を設定しない場合 Notice が起こらないように変更。
- sitemaps ディレクトリが存在しない場合に Notice が起こらないように変更。
- サイトマップが最小構成の場合に、Noticeレベルのエラーが発生する不具合を修正。
- サイトマップ解析時に、Libre Office, Open Office 形式の一時ファイルを無視するように変更。
- その他の細かい不具合修正。

### Pickles Framework v2.0.20 (2016年4月7日)

- サイトマップCSVの定義列がアスタリスク始まりではない(または空欄)の列がある場合、定義行が存在しないとみなしてしまう問題を修正。

### Pickles Framework v2.0.19 (2016年3月15日)

- コンフィグ項目 $conf->copyright を追加。

### Pickles Framework v2.0.18 (2016年2月22日)

- パブリッシュオプション paths_ignore に指定したパスが、パブリッシュディレクトリから削除されてしまう不具合を修正。

### Pickles Framework v2.0.17 (2016年2月18日)

- 範囲指定したパブリッシュのディレクトリスキャンにかかるパフォーマンスを改善。
- ?PX=publish のオプション paths_ignore を追加。

### Pickles Framework v2.0.16 (2016年1月2日)

- パブリッシュ実行中に、パブリッシュ先ディレクトリに都度コピーする機能が無効になる場合がある不具合を修正。
- その他、軽微な不具合の修正。

### Pickles Framework v2.0.15 (2015年11月9日)

- Actor機能追加。
- pickles.php と px.php を分離。テストを書きやすくするための配慮により。
- パブリッシュ時、サイトマップ上でプロトコル名、またはドメイン名から始まるリンク先の場合はスキップするように変更。

### Pickles Framework v2.0.14 (2015年10月23日)

- Markdownプロセッサーが、head と foot を処理しないように変更。
- .ignore を含むパスへのリクエストを、.htaccess で除外するように変更。

### Pickles Framework v2.0.13 (2015年9月4日)

- サイトマップキャッシュに SQLite を導入。ページ数の多いサイトの処理が高速化。
- デフォルトで bowl "foot" の定義を新たに追加。
- サイトマップに、サイト外のURLを組み込めるようになった。

### Pickles Framework v2.0.12 (2015年8月3日)

- $conf->path_files を追加。
- $conf->default_timezone を追加。
- $conf->path_phpini を追加。
- コマンドラインオプション --command-php, -c を追加。
- その他、不具合の修正など。

### Pickles Framework v2.0.11 (2015年7月2日)

- パブリッシュに時間が掛かり過ぎるときに、タイムアウトが発生して途中終了することがある不具合を修正。
- パブリッシュログに ファイルサイズ と ファイル個々にの処理にかかった時間(microtime) を記載するようになった。
- その他、軽微な不具合の修正など。


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

Copyright (c)2001-2022 Tomoya Koyanagi, and Pickles Project<br />
MIT License https://opensource.org/licenses/mit-license.php


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>
