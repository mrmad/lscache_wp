<?php
if (!defined('WPINC')) die;

$known_compat = array(
	'bbPress',
	'WooCommerce',
	'Contact Form 7',
	'Google XML Sitemaps',
	'Yoast SEO',
	'Wordfence Security',
	'NextGen Gallery',
	'Aelia CurrencySwitcher',
	'Autoptimize',
	'Better WP Minify',
	'WP Touch',
	'Theme My Login',
	'wpForo',
);

$known_uncompat = array();

?>
<h3 class="litespeed-title"><?=__('LiteSpeed Cache Plugin Compatibility', 'litespeed-cache')?></h3>

<p><a href="https://wordpress.org/support/topic/known-supported-plugins?replies=1" rel="noopener noreferrer" target="_blank"><?=__('Link Here', 'litespeed-cache')?></a></p>
<p>
	<?=__('Please add a comment listing the plugins that you are using and how they are functioning on the support thread.', 'litespeed-cache')?>
	<?=__('With your help, we can provide the best WordPress caching solution.', 'litespeed-cache')?>
</p>

<h4><?=__('This is a list of plugins that are confirmed to be compatible with LiteSpeed Cache Plugin:', 'litespeed-cache')?></h4>
<ul>
<?php
	foreach ($known_compat as $plugin_name) {
		echo '<li>' . $plugin_name . '</li>';
	}
?>
</ul>

<h4><?=__('This is a list of known UNSUPPORTED plugins:', 'litespeed-cache')?></h4>
<ul>
<?php
if($known_uncompat) {
	foreach ($known_uncompat as $plugin_name) {
		echo '<li>' . $plugin_name . '</li>';
	}
}else{
	echo "<li>NILL</li>";
}
?>
</ul>


