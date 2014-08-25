<?php

/*
 * Plugin Name: Jetpack Github 
 * Description: 
 * Plugin Author: enej
 */

class Jetpack_Github {
	
	const nspace = 'jetpack-github';
	const path   = 'wp-content/plugins/jetpack';
	const repo   = 'https://api.github.com/repos/Automattic/jetpack';
	const repo_web = 'https://github.com/Automattic/jetpack/';

	static function init(){
		add_action( 'wp_before_admin_bar_render', array( 'Jetpack_Github', 'current_branch_in_adminbar' ) );
		add_action( 'admin_menu',  array( 'Jetpack_Github', 'register_submenu_page' ), 1000 );
		add_action( 'load-jetpack_page_jetpack_github',  array( 'Jetpack_Github', 'switch_branch' ) );
	}

	static function register_submenu_page(){
		$plugin_page = add_submenu_page( 'jetpack', __( 'Jetpack Github', 'jetpack' ), __( 'Github', 'jetpack' ), 'jetpack_manage_modules', 'jetpack_github', array( 'Jetpack_Github', 'page' ) );
		
	}

	static function switch_branch(){
		
		if( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], 'switch_branch' ) )
			return;
		self::git_checkout( $_POST['branch']);
	}

	static function page(){
		
		?>
		<div class="wrap">
			<style type="text/css">
			.postbox h3{
				padding: 10px;
				cursor: initial!important;
			}
			.postbox-shell{
				padding:0  15px 15px;
			}
			</style>
			<h2>Github <span class="add-new-h2" ><?php echo self::get_current_branch(); ?></span></h2>
			<div class="postbox ">
				<h3>Do</h3>
				<div class="postbox-shell">
					<p>Currently on <strong><?php echo self::get_current_branch(); ?></strong>.</p>
					<form method="POST">
					<?php wp_nonce_field( 'switch_branch', '_nonce' ); ?>
					<input type="submit" value="Swithch to " class="primary-button button" />
					<select name="branch">
						<option>...</option>
						 <optgroup label="Branches">
						 	<?php $branches = self::get_remote_branches(); 
						 	foreach( $branches as $branch ) {  ?>
						 		<option value="<?php echo esc_attr( $branch->name); ?>"><?php echo  ucwords( $branch->name ); ?></option>
						 	<?php } ?>
						 </optgroup>
						 <optgroup label="Tags">
						 	<?php $tags = self::get_remote_tags(); 
						 	foreach( $tags as $tag ) {  ?>
						 		<option value="<?php echo esc_attr( $tag->name); ?>"><?php echo  ucwords( $tag->name ); ?></option>
						 	<?php } ?>
						 </optgroup>
					</select>
					</form>
					<p>$ git status</p>
					<pre><?php echo  self::git_status();?></pre>
				</div>

			</div>
			<div class="postbox ">
				<h3>Branches</h3>
				<div class="postbox-shell">
					<?php 
					foreach( $branches as $branch ) { 
						?>
						<a href="<?php echo esc_url( self::repo_web ."tree/". $branch->name ); ?>" ><?php echo ucwords( $branch->name ); ?></a> | 
						<a href="<?php echo esc_url( self::repo_web ."commit/". $branch->commit->sha ); ?>" >Last Commit</a><br />
					<?php } ?>
				</div>

			</div>
			<div class="postbox ">
				<h3>Tags</h3>
				<div class="postbox-shell">
					<?php 
					foreach( $tags as $tag ) { 
						?>
						<a href="<?php echo esc_url( self::repo_web ."tree/". $tag->name ); ?>" ><?php echo ucwords( $tag->name ); ?></a><br />
						
					<?php } ?>
				</div>

			</div>
		</div>
		<?php
		
	}

	static function current_branch_in_adminbar() {
		global $wp_admin_bar;
		$wp_admin_bar->add_menu(
			array(  'parent' => '',
					'id' => 'jetpack-current-branch',
					'title' => __( 'Current on ', self::nspace ) . self::get_current_branch() ,
					'meta' => array( 'title' => __( 'Current Branch', self::nspace ) . self::get_current_branch() ),
					'href' => admin_url( 'wp-admin/admin.php?page=jetpack_github' )
				)
			);
	}

	static function get_remote_branches() {
		return self::get_api( 'branches' );
	}

	static function get_remote_tags() {
		return self::get_api( 'tags' );
	}

	static function get_contributors() {
		return self::get_api( 'contributors' );
		
	}

	static function get_api( $item = 'branches' ){
		$json = get_transient( 'jetpack-github-' . $item );
		if( ! $json ) {
			
			$url = self::repo . '/'.$item;
			$response = wp_remote_get( $url );
			$json = json_decode( wp_remote_retrieve_body( $response ) );
			set_transient( 'jetpack-github-' . $item, $json, 60*60*15 );
		}
		return $json;
	}
	static function pull_changes( $tag_or_branch = 'master' ) {
		return shell_exec( 'cd '. ABSPATH . self::path . ' && git pull origin '.$tag_or_branch );	
	}
	static function git_checkout( $tag_or_branch = 'master') {
		self::pull_changes( $tag_or_branch );
		return shell_exec( 'cd '. ABSPATH . self::path . ' && git checkout '.$tag_or_branch );	
	}

	static function get_current_branch() {
		
		$branch = shell_exec('cd '. ABSPATH . self::path . ' && git rev-parse --abbrev-ref HEAD' );
		if( substr($branch, 0, 1) == '(') {
			return "tag: " . substr( substr( $branch, 14 ) , -1 );
		}
		return $branch;

	}

	static function git_status() {
		
		return shell_exec('cd '. ABSPATH . self::path . ' && git status' );
	}

	static function install(){

		if( is_null( shell_exec('cd '. ABSPATH . self::path . ' && git status' ) ) ) {
			echo "You don't have Jetpack Installed via GIT or your Server Doesn't have git \n";
			echo "To install jetpack via git run $: cd ". ABSPATH . self::path . " && git clone https://github.com/Automattic/jetpack.git \n";
		} else{
			shell_exec('cd '. ABSPATH . self::path . ' && git config core.filemode false' );
		};	
	}
}

Jetpack_Github::init();

register_activation_hook( __FILE__, array( 'Jetpack_Github', 'install' ) );

