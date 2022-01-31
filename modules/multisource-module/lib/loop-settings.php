<div class="fl-custom-query fl-loop-data-source" data-source="endpoint">
    <div id="fl-builder-settings-section-general" class="fl-builder-settings-section">
        <h3 class="fl-builder-settings-title">
            <span class="fl-builder-settings-title-text-wrap"><?php _e('Endpoint', 'fl-builder'); ?></span>
        </h3>
        <table class="fl-form-table">
            <?php
            // Meta Key
            \FLBuilder::render_settings_field('url_endpoint', array(
                'type'  => 'text',
                'label' => __( 'URL', 'fl-builder' ),
            ), $settings);
            
						\FLBuilder::render_settings_field('url_endpoint2', array(
                'type'  => 'text',
                'label' => __( 'URL 2 (if used by this module)', 'fl-builder' ),
            ), $settings);
            
            ?>
        </table>
    </div>
</div>