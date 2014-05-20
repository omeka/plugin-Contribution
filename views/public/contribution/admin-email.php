A new contribution to <?php echo get_option('site_title'); ?> has been made.
	
Contribution URL for review:

    <?php
        set_theme_base_uri('admin');
        echo abs_item_uri($item);
        set_theme_base_uri();
    ?>