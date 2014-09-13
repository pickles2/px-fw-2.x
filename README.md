Pickles 2
=========

## Install

```
$ cd {$documentRoot}
$ composer create-project tomk79/pickles2 ./ dev-master
```

## Usage

```
$ cd {$documentRoot}
$ vim _px_execute.php
<?php
chdir(__DIR__);
@require_once( './vendor/autoload.php' );
new pickles\pickles('./.pickles/');
```


