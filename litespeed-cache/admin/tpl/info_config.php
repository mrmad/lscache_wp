<?php
if (!defined('WPINC')) die;
?>
<h3 class="litespeed-title"><?=__('LiteSpeed Cache Configurations', 'litespeed-cache')?></h3>


<h4><?=__('Instructions for LiteSpeed Web Server Enterprise', 'litespeed-cache')?></h4>
<p>
	<?=__('Make sure that the server license has the LSCache module enabled.', 'litespeed-cache')?>
	<?=sprintf(__('A <a %s>2-CPU trial license with LSCache module</a> is available for free for %d days.', 'litespeed-cache'),
			'href="https://www.litespeedtech.com/products/litespeed-web-server/download/get-a-trial-license" rel="noopener noreferrer" target="_blank"', 15)?>
</p>
<p>
	<?=__('The server must be configured to have caching enabled.', 'litespeed-cache')?>
	<?=sprintf(__('If you are the server admin, <a %s>click here.</a>', 'litespeed-cache'),
			'href="https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:cache:common_installation#web_server_configuration" rel="noopener noreferrer" target="_blank"')?>
	<?=__('Otherwise request that the server admin configure the cache root for the server.', 'litespeed-cache')?>
</p>
<p>
	<?=__('In the .htaccess file for the WordPress installation, add the following:', 'litespeed-cache')?>
<textarea id="wpwrap" rows="3" readonly>&lt;IfModule LiteSpeed&gt;
   CacheLookup public on
&lt;/IfModule&gt;</textarea>
</p>


<h4><?=__('Instructions for OpenLiteSpeed', 'litespeed-cache')?></h4>
<p><?=__('This integration utilizes OLS\'s cache module.', 'litespeed-cache')?></p>
<p>
	<?=sprintf(__('If it is a fresh OLS installation, the easiest way to integrate is to use <a >ols1clk.</a>', 'litespeed-cache'),
			'href="http://open.litespeedtech.com/mediawiki/index.php/Help:1-Click_Install" rel="noopener noreferrer" target="_blank"')?>
	<?=sprintf(__('If using an existing WordPress installation, use the %s parameter.', 'litespeed-cache'), '--wordpresspath')?>
	<?=sprintf(__('Else if OLS and WordPress are already installed, please follow the instructions <a %s>here.</a>', 'litespeed-cache'),
			'href="http://open.litespeedtech.com/mediawiki/index.php/Help:How_To_Set_Up_LSCache_For_WordPress" rel="noopener noreferrer" target="_blank"')?>
</p>


<h3><?=__('How to test the plugin', 'litespeed-cache')?></h3>
<p><?=__('The LiteSpeed Cache Plugin utilizes LiteSpeed specific response headers.', 'litespeed-cache')?></p>
<p>
	<?=sprintf(__('Visiting a page for the first time should result in a %s or %s response header for the page.', 'litespeed-cache'),
			'<br><code>X-LiteSpeed-Cache-Control:miss</code><br>',
			'<br><code>X-LiteSpeed-Cache-Control:no-cache</code><br>')?>
</p>
<p>
	<?=sprintf(__('Subsequent requests should have the %s response header until the page is updated, expired, or purged.', 'litespeed-cache'), '<code>X-LiteSpeed-Cache-Control:hit</code><br>')?>
</p>
<p>
	<?=sprintf(__('Please visit <a %s>this page</a> for more information.', 'litespeed-cache'),
		'href="https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:cache:lscwp:installation#testing" rel="noopener noreferrer" target="_blank"')?>
</p>








