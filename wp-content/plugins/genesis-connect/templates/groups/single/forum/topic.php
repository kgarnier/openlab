<?php do_action( 'bp_before_group_forum_topic' ) ?>
<?php if ( bp_has_forum_topic_posts() ) : ?>

	<form action="<?php bp_forum_topic_action() ?>" method="post" id="forum-topic-form" class="standard-form">

		<div class="pagination no-ajax">

			<div id="post-count" class="pag-count">
				<?php bp_the_topic_pagination_count() ?>
			</div>

			<div class="pagination-links" id="topic-pag">
				<?php bp_the_topic_pagination() ?>
			</div>

		</div>

		<div id="topic-meta">
			<h3><?php bp_the_topic_title() ?> (<?php bp_the_topic_total_post_count() ?>)</h3>
			<a class="button" href="<?php bp_forum_permalink() ?>/">&larr; <?php _e( 'Group Forum', 'buddypress' ) ?></a> &nbsp; <a class="button" href="<?php bp_forum_directory_permalink() ?>/"><?php _e( 'Group Forum Directory', 'buddypress') ?></a>

			<?php if ( bp_group_is_admin() || bp_group_is_mod() || bp_get_the_topic_is_mine() ) : ?>
				<div class="admin-links"><?php bp_the_topic_admin_links() ?></div>
			<?php endif; ?>
			<?php do_action( 'bp_group_forum_topic_meta' ); ?>
		</div>
		<?php do_action( 'bp_before_group_forum_topic_posts' ) ?>
		<ul id="topic-post-list" class="item-list">
			<?php while ( bp_forum_topic_posts() ) : bp_the_forum_topic_post(); ?>

				<li id="post-<?php bp_the_topic_post_id() ?>">
					<div class="poster-meta">
						<?php bp_the_topic_post_poster_avatar( 'width=40&height=40') ?>
						<?php echo sprintf( __( '%s said %s ago:', 'buddypress' ), bp_the_topic_post_poster_name( false ), bp_the_topic_post_time_since( false ) ) ?>
					</div>

					<div class="post-content">
						<?php bp_the_topic_post_content() ?>
					</div>

					<div class="admin-links">
						<?php if ( bp_group_is_admin() || bp_group_is_mod() || bp_get_the_topic_post_is_mine() ) : ?>
							<?php bp_the_topic_post_admin_links() ?>
						<?php endif; ?>

						<?php do_action( 'bp_group_forum_post_meta' ); ?>

						<a href="#post-<?php bp_the_topic_post_id() ?>" title="<?php _e( 'Permanent link to this post', 'buddypress' ) ?>">#</a>
					</div>
				</li>

			<?php endwhile; ?>
		</ul>

		<?php do_action( 'bp_after_group_forum_topic_posts' ) ?>

		<div class="pagination no-ajax">

			<div id="post-count" class="pag-count">
				<?php bp_the_topic_pagination_count() ?>
			</div>

			<div class="pagination-links" id="topic-pag">
				<?php bp_the_topic_pagination() ?>
			</div>

		</div>
		
		<?php if ( ( is_user_logged_in() && 'public' == bp_get_group_status() ) || bp_group_is_member() ) : ?>

			<?php if ( bp_get_the_topic_is_topic_open() ) : ?>

				<div id="post-topic-reply">
					<a name="post-reply"></a>

					<?php if ( bp_groups_auto_join() && !bp_group_is_member() ) : ?>
						<p><?php _e( 'You will auto join this group when you reply to this topic.', 'buddypress' ) ?></p>
					<?php endif; ?>

					<?php do_action( 'groups_forum_new_reply_before' ) ?>

					<p><strong><?php _e( 'Add a reply:', 'buddypress' ) ?></strong></p>

					<textarea name="reply_text" id="reply_text"></textarea>

					<div class="submit">
						<input type="submit" name="submit_reply" id="submit" value="<?php _e( 'Post Reply', 'buddypress' ) ?>" />
					</div>

					<?php do_action( 'groups_forum_new_reply_after' ) ?>

					<?php wp_nonce_field( 'bp_forums_new_reply' ) ?>
				</div>

			<?php else : ?>

				<div id="message" class="info">
					<p><?php _e( 'This topic is closed, replies are no longer accepted.', 'buddypress' ) ?></p>
				</div>

			<?php endif; ?>

		<?php endif; ?>

	</form>
<?php else: ?>
	<div id="message" class="info">
		<p><?php _e( 'There are no posts for this topic.', 'buddypress' ) ?></p>
	</div>
<?php endif;?>
<?php do_action( 'bp_after_group_forum_topic' ) ?>
