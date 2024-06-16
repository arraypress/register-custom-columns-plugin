<?php

declare( strict_types=1 );

namespace ArrayPress\RegisterCustomColumnsPlugin;

use ArrayPress\RegisterCustomColumns\Utils\ColumnHelper;
use function ArrayPress\RegisterCustomColumns\register_post_columns;
use function ArrayPress\RegisterCustomColumns\register_edd_columns;

/**
 * Register custom columns for posts.
 *
 * @var array $custom_post_columns Configuration array for custom post columns.
 */
$custom_post_columns = [
	'thumbnail' => [
		'label'               => '', // Left blank on purpose,
		'display_callback'    => function ( $value, $post_id, $column ) {
			return ColumnHelper::post_thumbnail( $post_id, [ 64, 64 ] );
		},
		'position'            => 'before:title',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
		'width'               => '64px'
	],
];
register_post_columns( 'download', $custom_post_columns, 'edd_download_columns' );

/**
 * Register custom columns for EDD discounts.
 *
 * @var array $custom_discount_columns Configuration array for custom discount columns.
 */
$custom_discount_columns = [
	'savings'  => [
		'label'               => __( 'Savings', 'text-domain' ),
		'display_callback'    => function ( $value, $discount_id, $column ) {
			$discount = edd_get_discount( $discount_id );
			$stats    = new \EDD\Stats();

			return $stats->get_discount_savings( [
				'output'        => 'formatted',
				'discount_code' => $discount->code ?? '',
			] );
		},
		'position'            => 'after:use_count',
		'permission_callback' => function () {
			return current_user_can( 'view_shop_reports' );
		}
	],
	'earnings' => [
		'label'               => __( 'Earnings', 'text-domain' ),
		'display_callback'    => function ( $value, $discount_id, $column ) {
			$earnings_total = get_discount_earnings( $discount_id );

			return edd_currency_filter( edd_format_amount( $earnings_total ) );
		},
		'position'            => 'after:use_count',
		'permission_callback' => function () {
			return current_user_can( 'view_shop_reports' );
		}
	],
];
register_edd_columns( 'discounts', $custom_discount_columns );

/**
 * Register custom columns for EDD customers.
 *
 * @var array $custom_customer_columns Configuration array for custom customer columns.
 */
$custom_customer_columns = [
	'avg_order_value' => [
		'label'               => __( 'Average Order Value', 'text-domain' ),
		'display_callback'    => function ( $value, $customer_id, $column ) {
			$earnings_total = get_customer_average_order_value( $customer_id );

			return edd_currency_filter( edd_format_amount( $earnings_total ) );
		},
		'position'            => 'after:spent',
		'permission_callback' => function () {
			return current_user_can( 'view_shop_reports' );
		}
	],
];
register_edd_columns( 'customers', $custom_customer_columns );

/**
 * Register custom columns for EDD orders.
 *
 * @var array $custom_order_columns Configuration array for custom order columns.
 */
$custom_order_columns = [
	'purchased' => [
		'label'               => __( 'Purchased', 'text-domain' ),
		'display_callback'    => function ( $value, $customer_id, $column ) {
			return get_order_items( $customer_id );
		},
		'position'            => 'after:customer',
		'permission_callback' => function () {
			return current_user_can( 'view_shop_reports' );
		}
	],
];
register_edd_columns( 'orders', $custom_order_columns );

/**
 * Retrieve a flat array with unique concatenated product IDs and price IDs (if not null),
 * sorted by highest price. If there are multiple items, return the main item with a count of others.
 *
 * @param int $order_id The order ID.
 *
 * @return string|null The main item name with count of others or null if no items found.
 */
function get_order_items( int $order_id ): string {
	if ( ! $order_id ) {
		return '&mdash;';
	}

	$order_items = \edd_get_order_items( [
		'order_id'      => $order_id,
		'orderby'       => 'total',
		'order'         => 'DESC',
		'no_found_rows' => true,
		'number'        => 200,
	] );

	if ( ! $order_items ) {
		return '&mdash;';
	}

	$main_item_name = esc_html( $order_items[0]->product_name );
	$item_count     = count( $order_items );

	// Build the tooltip content
	$tooltip_content = '<ul>';
	foreach ( $order_items as $item ) {
		$tooltip_content .= sprintf(
			'<li>%s - %s</li>',
			esc_html( $item->product_name ),
			esc_html( edd_currency_filter( edd_format_amount( $item->total ) ) )
		);
	}
	$tooltip_content .= '</ul>';

	// Create the tooltip HTML
	$tooltip = sprintf(
		'<span class="edd-help-tip dashicons dashicons-editor-help" title="%s"></span>',
		esc_attr( $tooltip_content )
	);

	if ( $item_count > 1 ) {
		return sprintf(
			'<span>%s + %d %s</span> %s',
			$main_item_name,
			$item_count - 1,
			__( 'more', 'text-domain' ),
			$tooltip
		);
	}

	return sprintf(
		'<span>%s</span> %s',
		$main_item_name,
		$tooltip
	);
}

/**
 * Get discount earnings for a specific discount ID.
 *
 * @param int $discount_id The discount ID to filter by.
 *
 * @return float The total discount earnings.
 */
function get_discount_earnings( int $discount_id ): float {
	global $wpdb;

	// Bail if no discount ID
	if ( empty( $discount_id ) ) {
		return 0.0;
	}

	// Check if cached data exists
	$cache_key    = "edd_discount_earnings_{$discount_id}";
	$cached_value = get_transient( $cache_key );

	if ( false !== $cached_value ) {
		return (float) $cached_value;
	}

	// Get complete order statuses
	$statuses     = edd_get_complete_order_statuses();
	$statuses     = array_map( 'sanitize_text_field', $statuses );
	$placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );

	// Prepare the query
	$query = $wpdb->prepare(
		"
                SELECT SUM(edd_o.total) as total_discount_earnings
                FROM {$wpdb->prefix}edd_order_adjustments edd_oa
                INNER JOIN {$wpdb->prefix}edd_orders edd_o
                ON edd_oa.object_id = edd_o.id
                WHERE edd_oa.type = 'discount'
                AND edd_oa.type_id = %d
                AND edd_oa.object_type = 'order'
                AND edd_o.type = 'sale'
                AND edd_o.status IN ({$placeholders})
                ",
		array_merge( [ $discount_id ], $statuses )
	);

	// Execute the query
	$result         = $wpdb->get_var( $query );
	$total_earnings = $result ? (float) $result : 0.0;

	// Cache the result for 1 hour
	set_transient( $cache_key, $total_earnings, 15 * MINUTE_IN_SECONDS );

	return $total_earnings;
}

/**
 * Get average order earnings for a specific customer ID.
 *
 * @param int $customer_id The customer ID to filter by.
 *
 * @return float The average order earnings.
 */
function get_customer_average_order_value( int $customer_id ): float {
	global $wpdb;

	// Bail if no customer ID
	if ( empty( $customer_id ) ) {
		return 0.0;
	}

	// Check if cached data exists
	$cache_key    = "edd_average_order_earnings_{$customer_id}";
	$cached_value = get_transient( $cache_key );

	if ( false !== $cached_value ) {
		return (float) $cached_value;
	}

	// Get complete order statuses
	$statuses     = edd_get_complete_order_statuses();
	$statuses     = array_map( 'sanitize_text_field', $statuses );
	$placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );

	// Prepare the query
	$query = $wpdb->prepare(
		"
                SELECT AVG(edd_o.total) as average_order_earnings
                FROM {$wpdb->prefix}edd_orders edd_o
                WHERE edd_o.customer_id = %d
                AND edd_o.type = 'sale'
                AND edd_o.status IN ({$placeholders})
                ",
		array_merge( [ $customer_id ], $statuses )
	);

	// Execute the query
	$result           = $wpdb->get_var( $query );
	$average_earnings = $result ? (float) $result : 0.0;

	// Cache the result for 15 minutes
	set_transient( $cache_key, $average_earnings, 15 * MINUTE_IN_SECONDS );

	return $average_earnings;
}