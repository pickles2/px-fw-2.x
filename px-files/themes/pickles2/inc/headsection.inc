<?php /*
すべての layout について共通の、headセクションの記述
*/ ?>

		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="keywords" content="<?= htmlspecialchars($px->site()->get_current_page_info('keywords')); ?>" />
		<meta name="description" content="<?= htmlspecialchars($px->site()->get_current_page_info('description')); ?>" />
		<meta name="copyright" content="&copy;<?= htmlspecialchars($px->conf()->copyright); ?>">

<?= $px->get_contents_manifesto(); ?>

		<!-- Bootstrap -->
		<link rel="stylesheet" href="<?= htmlspecialchars( $px->path_plugin_files('/styles/bootstrap/css/bootstrap.min.css') ); ?>">
		<link rel="stylesheet" href="<?= htmlspecialchars( $px->path_plugin_files('/styles/bootstrap/css/bootstrap-theme.min.css') ); ?>">
		<script src="<?= htmlspecialchars( $px->path_plugin_files('/styles/bootstrap/js/bootstrap.min.js') ); ?>"></script>

		<!-- Theme -->
		<link rel="stylesheet" href="<?= htmlspecialchars( $px->path_plugin_files('/styles/theme.css') ); ?>">
