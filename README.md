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
$ composer create-project pickles2/pickles2 ./ dev-master
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

MIT License


## 作者 - Author

- (C)Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
