<?php if (!defined('ABSPATH')) {
    exit;
} ?>

<div class="wrap">
	<h1><?php _e('ReplyBox', 'replybox'); ?> - <?php _e('Better WordPress Comments', 'replybox'); ?></h1>

	<?php settings_errors( 'replybox' ); ?>

	<form method="POST" action="<?php echo esc_html(admin_url('admin-post.php')); ?>">
		<input type="hidden" name="action" value="replybox_settings">
		<?php wp_nonce_field('replybox_settings') ?>
    	<table class="form-table">
    		<tbody>
    			<tr>
    				<th scope="row">
    					<label for="site_id"><?php _e('Site ID (Required)', 'replybox'); ?></label>
    				</th>
    				<td>
						<input type="text" name="site_id" id="site_id" value="<?php echo $this->get_option('site_id'); ?>" class="regular-text">
						<p class="description">
							<?php printf(__('Your site ID comes from %s.', 'replybox'), '<a href="https://getreplybox.com" target="_blank">ReplyBox</a>'); ?>
						</p>
    				</td>
    			</tr>
    			<tr>
    				<th scope="row">
    					<h2><?php _e('Optional Setup', 'replybox'); ?></h2>
    				</th>
    			</tr>
    			<tr>
    				<th scope="row">
    					<label for="secure_token"><?php _e('Secure Token', 'replybox'); ?></label>
    				</th>
    				<td>
						<input type="text" name="secure_token" id="secure_token" value="<?php echo $this->get_option('secure_token'); ?>" class="regular-text" readonly>
						<p class="description" style="max-width: 40em;">
							<?php printf(__('Enter your secure token under <strong>Site > Embed</strong> in %s. <br/>This will allow ReplyBox to sync comments with WordPress.', 'replybox'), '<a href="https://app.getreplybox.com" target="_blank">ReplyBox</a>'); ?>
						</p>
    				</td>
    			</tr>
                <tr>
                    <th scope="row">
                        <label><?php _e('WordPress Sign-In', 'replybox'); ?></label>
                    </th>
                    <td>
                        <p class="description" style="max-width: 40em;">
		                    <?php printf(__('Want to allow users to comment on posts or pages using their existing WordPress account? Check out our <a href="%s" target="_blank">WordPress Sign-In</a> doc.', 'replybox'), 'https://getreplybox.com/docs/wordpress-sign-in'); ?>
                        </p>
                    </td>
                </tr>
    			<tr>
    				<th scope="row">
    					<h2><?php _e('Embed Options:', 'replybox'); ?></h2>
    				</th>
    			</tr>
    			<tr>
    				<th scope="row">
    					<label for="param_order"><?php _e('Sort comments', 'replybox'); ?></label>
    				</th>
    				<td>
					    <select name="param_order" id="param_order" class="regular-text">
					        <option value="asc" <?php selected( $this->get_option('param_order'), 'asc' ); ?>><?php _e('Ascending (Oldest first)', 'replybox'); ?></option>
					        <option value="desc" <?php selected( $this->get_option('param_order'), 'desc' ); ?>><?php _e('Descending (Newest first)', 'replybox'); ?></option>
					    </select>
    				</td>
    			</tr>
    			<tr>
    				<th scope="row">
    					<label for="param_lang"><?php _e('Language', 'replybox'); ?></label>
    				</th>
    				<td>
					    <select name="param_lang" id="param_lang" class="regular-text">
					        <option value="en" <?php selected( $this->get_option('param_lang'), 'en' ); ?>><?php _e('English', 'replybox'); ?></option>
					        <option value="fr" <?php selected( $this->get_option('param_lang'), 'fr' ); ?>><?php _e('French', 'replybox'); ?></option>
					        <option value="de" <?php selected( $this->get_option('param_lang'), 'de' ); ?>><?php _e('German', 'replybox'); ?></option>
					        <option value="nl" <?php selected( $this->get_option('param_lang'), 'nl' ); ?>><?php _e('Dutch', 'replybox'); ?></option>
					        <option value="pt" <?php selected( $this->get_option('param_lang'), 'pt' ); ?>><?php _e('Portugese', 'replybox'); ?></option>
					        <option value="es" <?php selected( $this->get_option('param_lang'), 'es' ); ?>><?php _e('Spanish', 'replybox'); ?></option>
					        <option value="cz" <?php selected( $this->get_option('param_lang'), 'cz' ); ?>><?php _e('Czech', 'replybox'); ?></option>
					        <option value="pl" <?php selected( $this->get_option('param_lang'), 'pl' ); ?>><?php _e('Polish', 'replybox'); ?></option>
					    </select>
						<p class="description">
							<?php _e('Translate the embed language.', 'replybox'); ?>
						</p>
    				</td>
    			</tr>
    		</tbody>
    	</table>

    	<p class="submit">
    		<button type="submit" class="button button-primary">
    			<?php _e('Save Changes', 'replybox'); ?>
    		</button>
    	</p>
	</form>
</div>