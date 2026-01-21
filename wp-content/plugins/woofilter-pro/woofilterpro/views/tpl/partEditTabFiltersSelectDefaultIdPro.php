<div class="row-settings-block wpfTypeSwitchable dataParentIgnore" data-type="dropdown radio list mul_dropdown buttons text multi" data-parent="f_list" data-no-values="custom_meta_field_check">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Select default id', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Selects the default filter value by id', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values col-xs-8 col-sm-9">
		<div class="settings-value">
			<?php
			HtmlWpf::text('f_select_default_id', array(
				'value' => ( isset($this->settings['f_select_default_id']) ? (int) $this->settings['f_select_default_id'] : '' ),
				'attrs' => 'class="woobewoo-flat-input"'
			));
			?>
		</div>
	</div>
</div>

