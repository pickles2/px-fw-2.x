PxFW-2.x
=========


Pickles Framework(PxFW) は、静的で大きなウェブサイトを効率よく構築できる オープンソースのHTML生成ツールです。<br />
データベース不要、PHP5が動くサーバーに手軽に導入でき、プロトタイプ制作を中心に進めるような柔軟な制作スタイルを実現します。

PxFW-2.x は、[PxFW-1.x](https://github.com/tomk79/PxFW-1.x) の後継です。
主だった変更点は次の通りがあります。

- `composer` からインストールできるようになりました。
- 機能の追加、拡張が手軽にできるようになりました。



## インストール手順 - Install


Pickles Framework 2.x はラッパーである [Pickles 2](https://github.com/tomk79/pickles2) からの利用をおすすめします。

```
$ cd {$documentRoot}
$ composer create-project tomk79/pickles2 ./ dev-master
$ chmod -R 777 ./.pickles/_sys
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


## テスト - Test

```
$ cd {$documentRoot}
$ ./vendor/phpunit/phpunit/phpunit picklesTest "./.pickles/_sys/tests/picklesTest.php"
```



