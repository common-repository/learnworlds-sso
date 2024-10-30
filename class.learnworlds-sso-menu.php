<?php

class Learnworlds_SSO_Menu{

    const SSO_LINK_CLASS_NAME = 'learnworlds-sso';

    public function __construct(){

        add_action( 'admin_head-nav-menus.php', array( $this, 'add_nav_menu_meta_boxes' ) );
        add_filter( 'nav_menu_link_attributes', array( $this, 'modify_sso_menu_items' ), 10, 3 );
    }

    public function add_nav_menu_meta_boxes(){
        add_meta_box( 'learnworlds_sso_endpoints_nav_link', __( 'Learnworlds endpoints', 'learnworlds' ), array( $this, 'nav_menu_links' ), 'nav-menus', 'side', 'low' );
    }

    function modify_sso_menu_items( $atts, $item, $args )
    {
        if ((is_array($item->classes) && in_array(self::SSO_LINK_CLASS_NAME, $item->classes)) || (is_string($item->classes) && strpos($item->classes, self::SSO_LINK_CLASS_NAME) !== false)) {
            $baseUrl = rest_url(Learnworlds_SSO_Route::ROUTE_NAMESPACE . Learnworlds_SSO_Route::ROUTE_URL);
            $atts['href'] = $baseUrl . '?redirectUrl=' . urlencode($atts['href']);
        }

        return $atts;
    }

    /**
	 * Output menu links.
	 */
	public function nav_menu_links() {

        $endpoints = [
            'SSO link' => rest_url(Learnworlds_SSO_Route::ROUTE_NAMESPACE . Learnworlds_SSO_Route::ROUTE_URL)
        ];

		?>
		<div id="posttype-learnworlds-endpoints" class="posttypediv">
			<div id="tabs-panel-learnworlds-endpoints" class="tabs-panel tabs-panel-active">
				<ul id="learnworlds-endpoints-checklist" class="categorychecklist form-no-clear">
					<?php
					$i = -1;
					foreach ( $endpoints as $key => $value ) :
						?>
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-object-id]" value="<?php echo esc_attr( $i ); ?>" /> <?php echo esc_html( $key ); ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-type]" value="custom" />
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-title]" value="<?php echo esc_html( $key ); ?>" />
                            <input type="hidden" class="menu-item-url" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-url]" value="" />
                            <input type="hidden" class="menu-item-classes" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-classes]" value="<?php echo self::SSO_LINK_CLASS_NAME; ?>" />
						</li>
						<?php
						$i--;
					endforeach;
					?>
				</ul>
			</div>
			<p class="button-controls">
				<span class="list-controls">
					<a href="<?php echo esc_url( admin_url( 'nav-menus.php?page-tab=all&selectall=1#posttype-learnworlds-endpoints' ) ); ?>" class="select-all"><?php esc_html_e( 'Select all', 'learnworlds' ); ?></a>
				</span>
				<span class="add-to-menu">
					<button type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to menu', 'learnworlds' ); ?>" name="add-post-type-menu-item" id="submit-posttype-learnworlds-endpoints"><?php esc_html_e( 'Add to menu', 'learnworlds' ); ?></button>
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

}