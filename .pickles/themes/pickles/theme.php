<?php
/**
 * theme "default"
 */
namespace pickles\themes\pickles;

/**
 * theme "default" class
 */
class theme{

	public static function exec( $px ){
		ob_start(); ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title><?= htmlspecialchars($px->site()->get_page_info( $px->req()->get_request_file_path(), 'title')); ?> | Pickles 2</title>
	</head>
	<body>
		<h1><?= htmlspecialchars($px->site()->get_page_info( $px->req()->get_request_file_path(), 'title')); ?></h1>
		<div class="contents">
<?= $px->pull_content(); ?>

		</div>
	</body>
</html>
<?php
		$src = ob_get_clean();
		return $src;
	}

}