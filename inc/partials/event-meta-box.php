<div class="postbox">
    <div class="postbox-header">
        <h2 class="hndle"><?php esc_html_e('Spektrix Details', 'untold'); ?></h2>
    </div>
    <div class="inside">
        <table class="widefat striped">
            <tbody>
                <tr>
                    <th scope="row">Spektrix ID</th>
                    <td>
                        <?php echo $spektrix_id; ?>
                    </td>
                </tr>
                <tr>
                    <th>Duration</th>
                    <td>
                        <?php echo esc_html(get_post_meta($post->ID, 'duration', true)); ?>
                    </td>
                </tr>
                <tr>
                    <th>Start Date</th>
                    <td>
                        <?php echo esc_html(get_post_meta($post->ID, 'start_date', true)); ?>
                    </td>
                </tr>
                <tr>
                    <th>End Date</th>
                    <td>
                        <?php echo esc_html(get_post_meta($post->ID, 'end_date', true)); ?>
                    </td>
                </tr>
                <tr>
                    <th>Next Instance Date</th>
                    <td>
                        <?php echo esc_html(get_post_meta($post->ID, 'next_instance_date', true)); ?>
                    </td>
                </tr>
                <tr>
                    <th>Trigger Warning Tag</th>
                    <td>
                        <?php echo esc_html(get_post_meta($post->ID, 'trigger_warning_tag', true)); ?>
                    </td>
                </tr>
                <tr>
                    <th>Online</th>
                    <td>
                        <?php echo esc_html(get_post_meta($post->ID, 'online', true) ? 'Yes' : 'No'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Family Friendly</th>
                    <td>
                        <?php echo esc_html(get_post_meta($post->ID, 'family_friendly', true) ? 'Yes' : 'No'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Free for Members</th>
                    <td>
                        <?php echo esc_html(get_post_meta($post->ID, 'free_for_members', true) ? 'Yes' : 'No'); ?>
                    </td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td>
                        <?php echo esc_html(get_post_meta($post->ID, 'location', true)); ?>
                    </td>
                </tr>
                <tr>
                    <th>Price Range</th>
                    <td>
                        <?php echo esc_html($prices_str ? $prices_str : 'Not available'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>