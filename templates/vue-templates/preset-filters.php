<div class="custom-popup-builder-library-page__filters">
	<div class="custom-popup-builder-library-page__filters-category">
		<span><b><?php esc_html_e( 'Categories: ', 'custom-popup-builder' ); ?></b></span>
		<ul>
			<li
				v-for="category in categories"
			>
				<i-switch size="small" @on-change="filterByCategory( $event, category.id )"/>
				<span>{{ category.label }}</span>
			</li>
		</ul>
	</div>
	<div class="custom-popup-builder-library-page__filters-misc">
		<span><b><?php esc_html_e( 'Filter By : ', 'custom-popup-builder' ); ?></b></span>
		<Select size="small" @on-change="filterBy" :value="filterByValue" style="width:100px">
			<Option value="date">Date</Option>
			<Option value="name">Name</Option>
			<Option value="popular">Popular</Option>
		</Select>
	</div>
</div>
