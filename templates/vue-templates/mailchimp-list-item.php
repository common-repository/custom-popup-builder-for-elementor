<Card class="custom-popup-builder-settings-page__list">
	<p><b><?php esc_html_e( 'Name: ', 'custom-popup-builder' ); ?></b>{{ list.name }}</p>
	<p><b><?php esc_html_e( 'List ID: ', 'custom-popup-builder' ); ?></b>{{ list.id }}</p>
	<p><b><?php esc_html_e( 'Date Created: ', 'custom-popup-builder' ); ?></b>{{ list.dateCreated }}</p>
	<p><b><?php esc_html_e( 'Member Count: ', 'custom-popup-builder' ); ?></b>{{ list.memberCount }}</p>
	<p>
		<b><?php esc_html_e( 'DoubleOptin: ', 'custom-popup-builder' ); ?></b>
		<Icon size="18" type="ios-checkmark-circle-outline" v-if="list.doubleOptin == true"/>
		<Icon size="18" type="ios-close-circle-outline" v-if="list.doubleOptin == false"/>
	</p>
	<p class="merge-fields" v-if="isMergeFields">
		<b><?php esc_html_e( 'Merge Fields: ', 'custom-popup-builder' ); ?></b>
		<span v-for="(name, key) in list.mergeFields" :key="key">{{ key }} ({{ name }})</span>
	</p>
	<Button
		:loading="mergeFieldsStatusLoading"
		size="small"
		@click="getMergeFields( list.id, $event )"
	>
		<?php esc_html_e( 'Get Merge Fields', 'custom-popup-builder' ); ?>
	</Button>
</Card>
