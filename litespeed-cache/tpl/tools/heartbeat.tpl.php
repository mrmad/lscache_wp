<?php
namespace LiteSpeed ;
defined( 'WPINC' ) || exit ;

$this->form_action() ;
?>

<h3 class="litespeed-title-short">
	<?php echo __( 'Heartbeat Control', 'litespeed-cache' ) ; ?>
	<?php $this->learn_more( 'https://www.litespeedtech.com/support/wiki/doku.php/litespeed_wiki:cache:lscwp:configuration:heartbeat', false, 'litespeed-learn-more' ) ; ?>
</h3>

<div class="litespeed-callout notice notice-warning inline">
	<h4><?php echo __( 'NOTICE:', 'litespeed-cache' ); ?></h4>
	<p><?php echo __( 'Disable WordPress interval heartbeat to reduce server load.', 'litespeed-cache' ) ; ?>
	<span class="litespeed-warning">
		🚨
		<?php echo __( 'Disabling this may cause WordPress tasks triggered by AJAX to stop working.', 'litespeed-cache' ) ; ?>
</span></p>
</div>


<table class="wp-list-table striped litespeed-table"><tbody>

	<tr>
		<th>
			<?php $id = Base::O_MISC_HEARTBEAT_FRONT ; ?>
			<?php $this->title( $id ) ; ?>
		</th>
		<td>
			<?php $this->build_switch( $id ) ; ?>
			<div class="litespeed-desc">

				<?php echo __( 'Turn ON to use heartbeat on frontend.', 'litespeed-cache' ) ; ?>
			</div>
		</td>
	</tr>

	<tr>
		<th>
			<?php $id = Base::O_MISC_HEARTBEAT_FRONT_TTL ; ?>
			<?php $this->title( $id ) ; ?>
		</th>
		<td>
			<?php $this->build_input( $id, 'litespeed-input-short') ; ?> <?php $this->readable_seconds() ; ?>
			<div class="litespeed-desc">
				<?php echo sprintf( __( 'Specify the %s heartbeat interval in seconds.', 'litespeed-cache' ), 'frontend' ) ; ?>
				<?php echo sprintf( __( 'WordPress valid interval is %s seconds.', 'litespeed-cache' ), '<code>15</code> - <code>120</code>' ) ; ?>
				<?php $this->recommended( $id ) ; ?>
				<?php $this->_validate_ttl( $id, 15, 120 ) ; ?>
			</div>
		</td>
	</tr>

	<tr>
		<th>
			<?php $id = Base::O_MISC_HEARTBEAT_BACK ; ?>
			<?php $this->title( $id ) ; ?>
		</th>
		<td>
			<?php $this->build_switch( $id ) ; ?>
			<div class="litespeed-desc">
				<?php echo __( 'Turn ON to use heartbeat on backend.', 'litespeed-cache' ) ; ?>
			</div>
		</td>
	</tr>

	<tr>
		<th>
			<?php $id = Base::O_MISC_HEARTBEAT_BACK_TTL ; ?>
			<?php $this->title( $id ) ; ?>
		</th>
		<td>
			<?php $this->build_input( $id, 'litespeed-input-short') ; ?> <?php $this->readable_seconds() ; ?>
			<div class="litespeed-desc">
				<?php echo sprintf( __( 'Specify the %s heartbeat interval in seconds.', 'litespeed-cache' ), 'backend' ) ; ?>
				<?php echo sprintf( __( 'WordPress valid interval is %s seconds', 'litespeed-cache' ), '<code>15</code> ~ <code>120</code>' ) ; ?>
				<?php $this->recommended( $id ) ; ?>
				<?php $this->_validate_ttl( $id, 15, 120 ) ; ?>
			</div>
		</td>
	</tr>

	<tr>
		<th>
			<?php $id = Base::O_MISC_HEARTBEAT_EDITOR ; ?>
			<?php $this->title( $id ) ; ?>
		</th>
		<td>
			<?php $this->build_switch( $id ) ; ?>
			<div class="litespeed-desc">
				<?php echo __( 'Turn ON to use heartbeat in backend editor.', 'litespeed-cache' ) ; ?>
			</div>
		</td>
	</tr>

	<tr>
		<th>
			<?php $id = Base::O_MISC_HEARTBEAT_EDITOR_TTL ; ?>
			<?php $this->title( $id ) ; ?>
		</th>
		<td>
			<?php $this->build_input( $id, 'litespeed-input-short') ; ?> <?php $this->readable_seconds() ; ?>
			<div class="litespeed-desc">
				<?php echo sprintf( __( 'Specify the %s heartbeat interval in seconds.', 'litespeed-cache' ), 'backend editor' ) ; ?>
				<?php echo sprintf( __( 'WordPress valid interval is %s seconds', 'litespeed-cache' ), '<code>15</code> ~ <code>120</code>' ) ; ?>
				<?php $this->recommended( $id ) ; ?>
				<?php $this->_validate_ttl( $id, 15, 120 ) ; ?>
			</div>
		</td>
	</tr>

</tbody></table>

<?php $this->form_end() ; ?>
