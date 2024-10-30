<div class="custom-popup-builder-library-page__item">
	<div class="custom-popup-builder-library-page__item-inner">
		<Card>
			<div class="custom-popup-builder-library-page__item-content">
				<span class="custom-popup-builder-library-page__item-label">{{ title }}</span>
				<Button @click="openModal" shape="circle" icon="ios-add" type="primary" ghost><?php esc_html_e( 'Add', 'custom-popup-builder' ); ?></Button>
				<Modal
					v-model="modalShow"
					title="<?php esc_html_e( 'You really want to create a new Popup?', 'custom-popup-builder' ); ?>"
					@on-ok="createPopup"
					ok-text="Yes"
					cancel-text="No"
				>
					<p><?php esc_html_e( 'A new preset will be created. You\'ll be redirected to Editing page. Also the template will be added to the popups list on "All Popups" page.', 'custom-popup-builder' ); ?></p>
				</Modal>
			</div>
			<div class="custom-popup-builder-library-page__item-thumb">
				<img :src="thumbUrl" alt="">
			</div>
			<div class="custom-popup-builder-library-page__item-info">
				<div class="custom-popup-builder-library-page__item-info-item custom-popup-builder-library-page__item-category">
					<Poptip placement="top" trigger="hover">
						<Icon type="md-pricetag" />
						<div class="category-info" slot="content"><b><?php esc_html_e( 'Category: ', 'custom-popup-builder' ); ?></b>{{categoryName}}</div>
					</Poptip>
				</div>
				<div class="custom-popup-builder-library-page__item-info-item custom-popup-builder-library-page__item-install" v-if="install > 0">
					<Poptip placement="top" trigger="hover" width="220" word-wrap>
						<Badge :count="install" type="primary" overflow-count="999">
							<Icon type="md-contacts" />
						</Badge>
						<div class="install-info" slot="content">
							<b><?php esc_html_e( 'Users Choice.', 'custom-popup-builder' ); ?></b>
							<span v-if="install==1" style="{ display: block }"><?php esc_html_e( 'This preset has been added to collection by users {{install}} time', 'custom-popup-builder' ); ?></span>
							<span v-if="install!==1" style="{ display: block }"><?php esc_html_e( 'This preset has been added to collection by users {{install}} times', 'custom-popup-builder' ); ?></span>
						</div>
					</Poptip>
				</div>
				<div class="custom-popup-builder-library-page__item-info-item custom-popup-builder-library-page__item-required" v-if="requiredPlugins.length > 0">
					<Poptip placement="top" trigger="hover">
						<Icon type="logo-buffer" />
						<div class="custom-popup-builder-library-page__required" slot="content">
							<b><?php esc_html_e( 'Required Plugins: ', 'custom-popup-builder' ); ?></b>
							<span><?php esc_html_e( 'When creating content', 'custom-popup-builder' ); ?></span>
							<span><?php esc_html_e( 'third-party plug-ins are used', 'custom-popup-builder' ); ?></span>
							<div class="custom-popup-builder-library-page__required-list">
								<div v-for="plugin in requiredPlugins" class="custom-popup-builder-library-page__required-plugin">
									<a :href="plugin.link" target="_blank">
										<img :src="plugin.badge" alt="">
									</a>
								</div>
							</div>
						</div>
					</Poptip>
				</div>
				<div class="custom-popup-builder-library-page__item-info-item custom-popup-builder-library-page__item-excerpt" v-if="excerpt.length > 0">
					<Poptip placement="top" trigger="hover" width="200" :content="excerpt" word-wrap>
						<Icon type="md-information-circle" />
					</Poptip>
				</div>
				<div class="custom-popup-builder-library-page__item-info-item custom-popup-builder-library-page__item-permalink">
					<a :href="permalink" target="_blank">
						<Icon type="md-eye" />
					</a>
				</div>
			</div>
		</Card>
	</div>
</div>
