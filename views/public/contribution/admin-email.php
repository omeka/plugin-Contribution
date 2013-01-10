A new contribution to <?php echo get_option('site_title'); ?> has been made.
	
Contribution URL for review:

    <?php
        set_theme_base_url('admin');
        echo abs_item_url($item);
        set_theme_base_url();
    ?>
