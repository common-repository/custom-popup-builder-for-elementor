<?php
$crate_action = add_query_arg(
	array(
		'action' => 'custom_popup_builder_create_from_library_preset',
	),
	esc_url( admin_url( 'admin.php' ) )
);
?>
<div class="custom-popup-builder-library-page__inner">
	<Card :dis-hover="true">
		<Spin size="large" v-if="spinnerShow"></Spin>
		<Alert type="error" show-icon v-if="presetsLoadedError">
			<?php esc_html_e( 'Server Error', 'custom-popup-builder' ); ?>
			<span slot="desc">Request failed with status code 404, something wrong with custom-popup-builder-api</span>
		</Alert>
		<form
			id="custom-popup-builder-library-page-form"
			class="custom-popup-builder-library-page__form"
			ref="customPopupLibraryForm"
			method="POST"
			action="<?php echo $crate_action; ?>"
			v-if="presetsLoaded"
		>
			<input type="hidden" name="preset" :value="preset">
			<h2 class="custom-popup-builder-library-page__title"><?php esc_html_e( 'Custom Popup Presets Library', 'custom-popup-builder' ); ?></h2>
			<preset-filters
				v-if="categoriesLoaded"
				:categories='categoryData'
				:current-category="activeCategories"
			>
			</preset-filters>
			<preset-list
				:presets='presetList'
			>
			</preset-list>
			<Alert type="info" show-icon v-if="presetsLength==0">
				<?php esc_html_e( 'No matches found', 'custom-popup-builder' ); ?>
			</Alert>
			<div class="custom-popup-builder-library-page__pagination" v-if="isShowPagination">
				<Page
					:total="presetsLength"
					:page-size="perPage"
					@on-change="changePage"
				/>
			</div>
		</form>
	</Card>
</div>
