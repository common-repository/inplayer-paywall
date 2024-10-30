<b><?php esc_attr_e('Click on a Asset title to insert the generated Shortcode in your post/page', INPLAYER_TEXT_DOMAIN); ?></b>

<table class="wp-list-table widefat fixed striped inplayer-asset-title-list">
    <thead>
        <tr>
            <th scope="col"><?php esc_attr_e('Asset Title', INPLAYER_TEXT_DOMAIN); ?></th>
            <th scope="col"><?php esc_attr_e('Asset ID', INPLAYER_TEXT_DOMAIN); ?></th>
            <th scope="col"><?php esc_attr_e('Asset Type', INPLAYER_TEXT_DOMAIN); ?></th>
            <th scope="col"><?php esc_attr_e('Asset Shortcode', INPLAYER_TEXT_DOMAIN); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach($response['body']['collection'] as $asset):
            if($asset['is_active']):
        ?>
        <tr>
            <td><?php echo $asset['title'] ?></td>
            <td><?php echo $asset['id'] ?></td>
            <td><?php echo strtoupper($asset['item_type']['name']); ?></td>
            <td><a href="#asset" class="return_shortcode" data-id="<?php echo $asset['id'] ?>">Insert shortcode</a></td>
        </tr>
    <?php endif; endforeach; ?>
    </tbody>
</table>