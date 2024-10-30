<div style="border-bottom: 1px solid #eee; padding-bottom: 25px;">
    <h4><?php echo esc_attr__('Asset Preview Description', INPLAYER_TEXT_DOMAIN) ?></h4>
    <p><?php echo __('text_preview_asset_explain', INPLAYER_TEXT_DOMAIN); ?></p>

    <input type="text" id="asset-description" name="asset_description"
       value="<?php if($desc = $this->asset('metadata')) { echo $desc['preview_description']; } ?>" autocomplete="off"
       placeholder="<?php echo esc_attr__( 'Enter asset description', INPLAYER_TEXT_DOMAIN ); ?>">
</div>