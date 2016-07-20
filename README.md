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

Pickles2 は、[PxFW-1.x](https://github.com/tomk79/PxFW-1.x) の後継です。
主な改善点は次の通りです。

- `composer` からインストールできるようになりました。
- 機能の追加、拡張が手軽にできるようになりました。
- コマンドラインからの実行が改善され、外部のツールやスクリプトとの連携が容易になりました。
- その他、よりシンプルに利用できるよう、多くの機能が改善されました。



## インストール手順 - Install


Pickles Framework 2.x はラッパーである [Pickles 2](https://github.com/pickles2/pickles2) からの利用をおすすめします。

```
$ cd {$documentRoot}
$ composer create-project pickles2/pickles2 ./
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
- PHP 5.4 以上
  - mbstring が有効に設定されていること
  - PDO SQLiteドライバー (PDO_SQLITE) が有効に設定されていること
  - safe_mode が無効に設定されていること


## 更新履歴 - Change log

### Pickles Framework 2.0.22 (2016年??月??日)

- コンフィグ項目 `$conf->paths_enable_sitemap` を追加。
- `$conf->paths_proc_type` に、新しい処理方法 pass を追加。デフォルトを pass に変更。
- `$conf->paths_proc_type` が direct のときに、二重拡張子の処理を適用できるようになった。

### Pickles Framework 2.0.21 (2016年7月14日)

- `$conf->paths_proc_type` の設定を、前方マッチから完全マッチに変更。 ディレクトリの指定等は、ワイルドカード `*` を使って表現する方針で統一。
- `PX=publish` に、パブリッシュ対象外のパスをコンフィグオプションで設定できる機能を追加。 (コマンドラインオプションで除外する方法は従来から存在していた)
- 公開キャッシュ と `_sys/ram/*` のディレクトリが存在しない場合に、作成を試みるように変更。
- `path_publish_dir` と `contents_manifesto` を設定しない場合 Notice が起こらないように変更。
- sitemaps ディレクトリが存在しない場合に Notice が起こらないように変更。
- サイトマップが最小構成の場合に、Noticeレベルのエラーが発生する不具合を修正。
- サイトマップ解析時に、Libre Office, Open Office 形式の一時ファイルを無視するように変更。
- その他の細かい不具合修正。

### Pickles Framework 2.0.20 (2016年4月7日)

- サイトマップCSVの定義列がアスタリスク始まりではない(または空欄)の列がある場合、定義行が存在しないとみなしてしまう問題を修正。

### Pickles Framework 2.0.19 (2016年3月15日)

- コンフィグ項目 $conf->copyright を追加。

### Pickles Framework 2.0.18 (2016年2月22日)

- パブリッシュオプション paths_ignore に指定したパスが、パブリッシュディレクトリから削除されてしまう不具合を修正。

### Pickles Framework 2.0.17 (2016年2月18日)

- 範囲指定したパブリッシュのディレクトリスキャンにかかるパフォーマンスを改善。
- ?PX=publish のオプション paths_ignore を追加。

### Pickles Framework 2.0.16 (2016年1月2日)

- パブリッシュ実行中に、パブリッシュ先ディレクトリに都度コピーする機能が無効になる場合がある不具合を修正。
- その他、軽微な不具合の修正。

### Pickles Framework 2.0.15 (2015年11月9日)

- Actor機能追加。
- pickles.php と px.php を分離。テストを書きやすくするための配慮により。
- パブリッシュ時、サイトマップ上でプロトコル名、またはドメイン名から始まるリンク先の場合はスキップするように変更。

### Pickles Framework 2.0.14 (2015年10月23日)

- Markdownプロセッサーが、head と foot を処理しないように変更。
- .ignore を含むパスへのリクエストを、.htaccess で除外するように変更。

### Pickles Framework 2.0.13 (2015年9月4日)

- サイトマップキャッシュに SQLite を導入。ページ数の多いサイトの処理が高速化。
- デフォルトで bowl "foot" の定義を新たに追加。
- サイトマップに、サイト外のURLを組み込めるようになった。

### Pickles Framework 2.0.12 (2015年8月3日)

- $conf->path_files を追加。
- $conf->default_timezone を追加。
- $conf->path_phpini を追加。
- コマンドラインオプション --command-php, -c を追加。
- その他、不具合の修正など。

### Pickles Framework 2.0.11 (2015年7月2日)

- パブリッシュに時間が掛かり過ぎるときに、タイムアウトが発生して途中終了することがある不具合を修正。
- パブリッシュログに ファイルサイズ と ファイル個々にの処理にかかった時間(microtime) を記載するようになった。
- その他、軽微な不具合の修正など。


## 開発者向け情報 - for Developer


### テスト - Test

```
$ cd {$documentRoot}
$ php vendor/phpunit/phpunit/phpunit
```


### ドキュメント出力 - phpDocumentor

```
$ composer run-script documentation
```


## ライセンス - License

Copyright (c)2001-2016 Tomoya Koyanagi, and Pickles 2 Project<br />
MIT License https://opensource.org/licenses/mit-license.php


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
