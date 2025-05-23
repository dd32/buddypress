<?php
/**
 * @group members
 * @group routing
 * @group root_profiles
 */
class BP_Tests_Routing_Members_Root_Profiles extends BP_UnitTestCase {
	protected $old_current_user = 0;
	protected $u;
	protected $permalink_structure = '';

	public function set_up() {
		parent::set_up();

		add_filter( 'bp_core_enable_root_profiles', '__return_true' );

		$this->old_current_user = get_current_user_id();
		$uid = self::factory()->user->create( array(
			'user_login' => 'boone',
			'user_nicename' => 'boone',
		) );
		$this->u = new WP_User( $uid );
		wp_set_current_user( $uid );
		$this->permalink_structure = get_option( 'permalink_structure', '' );
	}

	public function tear_down() {
		wp_set_current_user( $this->old_current_user );
		$this->set_permalink_structure( $this->permalink_structure );
		remove_filter( 'bp_core_enable_root_profiles', '__return_true' );

		parent::tear_down();
	}

	public function test_members_directory() {
		$this->set_permalink_structure( '/%postname%/' );
		$this->go_to( home_url( bp_get_members_root_slug() ) );

		$pages        = bp_core_get_directory_pages();
		$component_id = bp_current_component();

		$this->assertEquals( bp_get_members_root_slug(), $pages->{$component_id}->slug );
	}

	public function test_member_permalink() {
		$this->set_permalink_structure( '/%postname%/' );
		$domain = home_url( $this->u->user_nicename );
		$this->go_to( $domain );

		$this->assertTrue( bp_is_user() );
		$this->assertTrue( bp_is_my_profile() );
		$this->assertEquals( $this->u->ID, bp_displayed_user_id() );
	}

	/**
	 * @ticket BP6475
	 */
	public function test_member_permalink_when_members_page_is_nested_under_wp_page() {
		$this->markTestSkipped();

		/**
		 * This is no more supported in BuddyPress.
		 */

		$this->set_permalink_structure( '/%postname%/' );
		$p = self::factory()->post->create( array(
			'post_type' => 'post',
			'post_name' => 'foo',
		) );

		$members_page_id = bp_core_get_directory_page_id( 'members' );
		wp_update_post( array(
			'ID'          => $members_page_id,
			'post_parent' => $p,
		) );

		$url = bp_members_get_user_url( $this->u->ID );
		$this->go_to( $url );

		$this->assertTrue( bp_is_user() );
		$this->assertTrue( bp_is_my_profile() );
		$this->assertEquals( $this->u->ID, bp_displayed_user_id() );
	}

	public function test_member_activity_page() {
		$this->set_permalink_structure( '/%postname%/' );
		$url = home_url( $this->u->user_nicename ) . '/' . bp_get_activity_slug();
		$this->go_to( $url );

		$this->assertTrue( bp_is_user() );
		$this->assertTrue( bp_is_my_profile() );
		$this->assertEquals( $this->u->ID, bp_displayed_user_id() );

		$this->assertTrue( bp_is_activity_component() );
	}
}
