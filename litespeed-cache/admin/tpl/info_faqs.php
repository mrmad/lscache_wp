<?php
if (!defined('WPINC')) die;
?>
<h3 class="litespeed-title">
	<?=__('LiteSpeed Cache FAQs', 'litespeed-cache')?>
	<a href="javascript:;" class="litespeed-expend" data-litespeed-expend-all="faqs">+</a>
</h3>

<h4 class="litespeed-question litespeed-down"><?=__('Is the LiteSpeed Cache Plugin for WordPress free?', 'litespeed-cache')?></h4>
<div class="litespeed-answer">
	<p>
		<?=__('Yes, the plugin itself will remain free and open source.', 'litespeed-cache')?>
		<?=__('That said, a LiteSpeed server is required (see question 2)', 'litespeed-cache')?>
	</p>
</div>

<h4 class="litespeed-question litespeed-down"><?=__('What server software is required for this plugin?', 'litespeed-cache')?></h4>
<div class="litespeed-answer">
	<p><?=__('A LiteSpeed server is required in order to use this plugin.', 'litespeed-cache')?></p>
	<ol>
		<li>LiteSpeed Web Server Enterprise with LSCache Module (v5.0.10+)</li>
		<li>OpenLiteSpeed (v1.4.17+)</li>
		<li>LiteSpeed WebADC (v2.0+)</li>
	</ol>
	<p><?=__('Any single server or cluster including a LiteSpeed server will work.', 'litespeed-cache')?>
</div>

<h4 class="litespeed-question litespeed-down"><?=__('Does this plugin work in a clustered environment?', 'litespeed-cache')?></h4>
<div class="litespeed-answer">
	<p>
		<?=__('The cache entries are stored at the litespeed server level.', 'litespeed-cache')?>
		<?=__('The simplest solution is to use LiteSpeed WebADC, as the cache entries will be cached at that level.', 'litespeed-cache')?>
	</p>
	<p>
		<?=__('If using another load balancer, the cache entries will only be stored at the backend nodes, not at the load balancer.', 'litespeed-cache')?>
		<?=__('The purges will also not be synchronized across the nodes, so this is not recommended.', 'litespeed-cache')?>
	</p>
	<p>
		<?=sprintf(__('If a customized solution is required, please contact %s at %s', 'litespeed-cache'),
		'LiteSpeed Technologies', 'info@litespeedtech.com')?>
	</p>
	<p><?=__('NOTICE: The rewrite rules created by this plugin must be copied to the WebADC', 'litespeed-cache')?></p>
</div>

<h4 class="litespeed-question litespeed-down"><?=__('Where are the cache entries stored?', 'litespeed-cache')?></h4>
<div class="litespeed-answer">
	<p><?=__('The actual cached pages are stored and managed by LiteSpeed Servers. Nothing is stored on the PHP side.', 'litespeed-cache')?></p>
</div>

<h4 class="litespeed-question litespeed-down"><?=__('Is WooCommerce supported?', 'litespeed-cache')?></h4>
<div class="litespeed-answer">
	<p>
		<?=__('In short, yes.', 'litespeed-cache')?>
		<?=__('However, for some woocommerce themes, the cart may not be updated correctly.', 'litespeed-cache')?>
	</p>
	<p><b><?=__('To test the cart:', 'litespeed-cache')?></b></p>
	<ul>
		<li><?=__('On a non-logged-in browser, visit and cache a page, then visit and cache a product page.', 'litespeed-cache')?></li>
		<li><?=__('The first page should be accessible from the product page (e.g. the shop).', 'litespeed-cache')?></li>
		<li><?=__('Once both pages are confirmed cached, add the product to the cart.', 'litespeed-cache')?></li>
		<li><?=__('After adding to the cart, visit the first page.', 'litespeed-cache')?></li>
		<li><?=__('The page should still be cached, and the cart should be up to date.', 'litespeed-cache')?></li>
		<li><?=__('If that is not the case, please add woocommerce_items_in_cart to the do not cache cookie list.', 'litespeed-cache')?></li>
	</ul>
	<p>
		<?=__('Some themes like Storefront and Shop Isle are built such that the cart works without the rule.', 'litespeed-cache')?>
		<?=__('However, other themes like the E-Commerce theme, do not, so please verify the theme used.', 'litespeed-cache')?>
	</p>
</div>

<h4 class="litespeed-question litespeed-down"><?=__('Are my images optimized?', 'litespeed-cache')?></h4>
<div class="litespeed-answer">
	<p>
		<?=__('The cache plugin does not do anything with the images themselves.', 'litespeed-cache')?>
		<?=sprintf(__('We recommend you trying an image optimization plugin like %s to optimize your images.', 'litespeed-cache'),
		'<a href="https://shortpixel.com/h/af/CXNO4OI28044" rel="friend noopener noreferer" target="_blank">ShortPixel</a>')?>
		<?=__("It can reduce your site's images up to 90%.", 'litespeed-cache')?>
	</p>
</div>

<h4 class="litespeed-question litespeed-down"><?=__('How do I get WP-PostViews to display an updating view count?', 'litespeed-cache')?></h4>
<div class="litespeed-answer">
	<ol>
		<li><?=sprintf(__('Use %1$s to replace %2$s', 'litespeed-cache'),
					'<code>&lt;div id="postviews_lscwp"&gt;&lt;/div&gt;</code>',
					'<code>&lt;?php if(function_exists(\'the_views\')) { the_views(); } ?&gt;</code>')?>
			<ul>
				<li><?=__('NOTE: The id can be changed, but the div id and the ajax function must match.', 'litespeed-cache')?></li>
			</ul>
		</li>
		<li><?=sprintf(__('Replace the ajax query in %1$s with %2$s', 'litespeed-cache'),
					'<code>wp-content/plugins/wp-postviews/postviews-cache.js</code>',
					'<textarea id="wpwrap" rows="11" readonly>jQuery.ajax({
		type:"GET",
		url:viewsCacheL10n.admin_ajax_url,
		data:"postviews_id="+viewsCacheL10n.post_id+"&amp;action=postviews",
		cache:!1,
		success:function(data) {
			if(data) {
				jQuery(\'#postviews_lscwp\').html(data+\' views\');
			}
		}
	});</textarea>')?>
		</li>
		<li>
			<?=__('Purge the cache to use the updated pages.', 'litespeed-cache')?>
		</li>
	</ol>
</div>

