PxFW-2.x
=========


Pickles Framework(PxFW) は、静的で大きなウェブサイトを効率よく構築できる オープンソースのHTML生成ツールです。<br />
データベース不要、PHPが動くサーバーに手軽に導入でき、プロトタイプ制作を中心に進めるような柔軟な制作スタイルを実現します。

PxFW-2.x は、[PxFW-1.x](https://github.com/tomk79/PxFW-1.x) の後継です。
主だった改善点は次の通りがあります。

- `composer` からインストールできるようになりました。
- 機能の追加、拡張が手軽にできるようになりました。
- コマンドラインからの実行が改善され、外部のツールやスクリプトとの連携が容易になりました。
- その他、よりシンプルに利用できるよう、多くの機能が改善されました。



## インストール手順 - Install


Pickles Framework 2.x はラッパーである [Pickles 2](https://github.com/tomk79/pickles2) からの利用をおすすめします。

```
$ cd {$documentRoot}
$ composer create-project tomk79/pickles2 ./ dev-master
```

`.px_execute.php` の置かれたディレクトリがドキュメントルートになるよう、ウェブサーバーを設定してください。

Pickles Framework が書き込みを行うディレクトリがあります。次のコマンドは、書き込み権限を付与するためのものです。すでに権限がある場合は実行する必要はありません。

```
$ chmod -R 777 ./px-files/_sys
$ chmod -R 777 ./caches
```



## システム要件 - System Requirement

- Linux系サーバ または Windowsサーバ
- Apache1.3以降
  - mod_rewrite が利用可能であること
  - .htaccess が利用可能であること
- PHP5.3以上
  - mb_string が有効に設定されていること
  - safe_mode が無効に設定されていること


## 開発者向け情報 - for Developer

### テスト - Test

```
$ cd {$documentRoot}
$ ./vendor/phpunit/phpunit/phpunit picklesTest "./px-files/_sys/tests/picklesTest.php"
```

### ドキュメント出力 - phpDocumentor

$ php ./vendor/phpdocumentor/phpdocumentor/bin/phpdoc --title "Pickles Framework 2.x API Document" -d "./px-files/_sys/php/" -f "./vendor/tomk79/filesystem/php/filesystem.php","./vendor/tomk79/request/php/request.php" -t "./sample_pages/phpdoc/"




## ライセンス - License

MIT License


## 作者 - Author

- (C)Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>


