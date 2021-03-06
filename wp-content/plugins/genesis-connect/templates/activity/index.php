<?php
get_header();
genesis_before_content_sidebar_wrap();
gconnect_before_content();
?>
			<?php if ( !is_user_logged_in() ) : ?>
				<h3><?php _e( 'Site Activity', 'buddypress' ) ?></h3>
			<?php endif; ?>
			<?php do_action( 'bp_before_directory_activity_content' ) ?>
			<?php if ( is_user_logged_in() ) : ?>
				<?php gconnect_locate_template( array( 'activity/post-form.php'), true ) ?>
			<?php endif; ?>
			<?php do_action( 'template_notices' ) ?>
			<div class="item-list-tabs activity-type-tabs">
				<ul>
					<?php do_action( 'bp_before_activity_type_tab_all' ) ?>
					<li class="selected" id="activity-all"><a href="<?php echo bp_loggedin_user_domain() . BP_ACTIVITY_SLUG . '/' ?>" title="<?php _e( 'The public activity for everyone on this site.', 'buddypress' ) ?>"><?php printf( __( 'All Members (%s)', 'buddypress' ), bp_get_total_site_member_count() ) ?></a></li>
					<?php if ( is_user_logged_in() ) : ?>
						<?php do_action( 'bp_before_activity_type_tab_friends' ) ?>
						<?php if( bp_is_active( 'friends' ) && bp_get_total_friend_count( bp_loggedin_user_id() ) ) : ?>
							<li id="activity-friends"><a href="<?php echo bp_loggedin_user_domain() . BP_ACTIVITY_SLUG . '/' . BP_FRIENDS_SLUG . '/' ?>" title="<?php _e( 'The activity of my friends only.', 'buddypress' ) ?>"><?php printf( __( 'My Friends (%s)', 'buddypress' ), bp_get_total_friend_count( bp_loggedin_user_id() ) ) ?></a></li>
						<?php endif; ?>
						<?php do_action( 'bp_before_activity_type_tab_groups' ) ?>
						<?php if ( bp_is_active( 'groups' ) && bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) : ?>
							<li id="activity-groups"><a href="<?php echo bp_loggedin_user_domain() . BP_ACTIVITY_SLUG . '/' . BP_GROUPS_SLUG . '/' ?>" title="<?php _e( 'The activity of groups I am a member of.', 'buddypress' ) ?>"><?php printf( __( 'My Groups (%s)', 'buddypress' ), bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) ?></a></li>
						<?php endif; ?>
						<?php do_action( 'bp_before_activity_type_tab_favorites' ) ?>
						<?php if ( bp_get_total_favorite_count_for_user( bp_loggedin_user_id() ) ) : ?>
							<li id="activity-favorites"><a href="<?php echo bp_loggedin_user_domain() . BP_ACTIVITY_SLUG . '/favorites/' ?>" title="<?php _e( "The activity I've marked as a favorite.", 'buddypress' ) ?>"><?php printf( __( 'My Favorites (<span>%s</span>)', 'buddypress' ), bp_get_total_favorite_count_for_user( bp_loggedin_user_id() ) ) ?></a></li>
						<?php endif; ?>
						<?php do_action( 'bp_before_activity_type_tab_mentions' ) ?>
						<li id="activity-mentions"><a href="<?php echo bp_loggedin_user_domain() . BP_ACTIVITY_SLUG . '/mentions/' ?>" title="<?php _e( 'Activity that I have been mentioned in.', 'buddypress' ) ?>"><?php printf( __( '@%s Mentions', 'buddypress' ), bp_get_loggedin_user_username() ) ?><?php if ( bp_get_total_mention_count_for_user( bp_loggedin_user_id() ) ) : ?> <strong><?php printf( __( '(%s new)', 'buddypress' ), bp_get_total_mention_count_for_user( bp_loggedin_user_id() ) ) ?></strong><?php endif; ?></a></li>
					<?php endif; ?>
					<?php do_action( 'bp_activity_type_tabs' ) ?>
				</ul>
				<div class="clear"></div>
			</div><!-- .item-list-tabs -->
			<div class="item-list-tabs no-ajax" id="bpsubnav">
				<ul>
					<li class="feed"><a href="<?php bp_sitewide_activity_feed_link() ?>" title="RSS Feed"><?php _e( 'RSS', 'buddypress' ) ?></a></li>
					<?php do_action( 'bp_activity_syndication_options' ) ?>
					<li id="activity-filter-select" class="last">
						<select>
							<option value="-1"><?php _e( 'No Filter', 'buddypress' ) ?></option>
							<option value="activity_update"><?php _e( 'Show Updates', 'buddypress' ) ?></option>
							<option value="new_blog_post"><?php _e( 'Show Blog Posts', 'buddypress' ) ?></option>
							<option value="new_blog_comment"><?php _e( 'Show Blog Comments', 'buddypress' ) ?></option>
							<option value="new_forum_topic"><?php _e( 'Show New Forum Topics', 'buddypress' ) ?></option>
							<option value="new_forum_post"><?php _e( 'Show Forum Replies', 'buddypress' ) ?></option>
							<option value="created_group"><?php _e( 'Show New Groups', 'buddypress' ) ?></option>
							<option value="joined_group"><?php _e( 'Show New Group Memberships', 'buddypress' ) ?></option>
							<option value="friendship_accepted,friendship_created"><?php _e( 'Show Friendship Connections', 'buddypress' ) ?></option>
							<option value="new_member"><?php _e( 'Show New Members', 'buddypress' ) ?></option>
							<?php do_action( 'bp_activity_filter_options' ) ?>
						</select>
					</li>
				</ul>
				<div class="clear"></div>
			</div><!-- .item-list-tabs -->
			<div class="activity">
				<?php gconnect_locate_template( array( 'activity/activity-loop.php' ), true ); ?>
			</div><!-- .activity -->
			<form action="" name="activity-widget-form" id="activity-widget-form" method="post">
				<?php wp_nonce_field( 'activity_filter', '_wpnonce_activity_filter' ) ?>
				<input type="hidden" id="aw-querystring" name="aw-querystring" value="" />
				<input type="hidden" id="aw-oldestpage" name="aw-oldestpage" value="1" />
			</form>
			<?php do_action( 'bp_after_directory_activity_content' ) ?>
<?php
gconnect_after_content();
genesis_after_content_sidebar_wrap();
get_footer();
?>
