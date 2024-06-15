<?php
/**
 * Plugin Name:       Register Custom Columns Plugin
 * Plugin URI:        https://github.com/arraypress/register-custom-columns-plugin
 * Description:       A plugin demonstrating the usage of the WordPress Register Custom Columns Library with various
 * examples. Author:            ArrayPress Author URI:        https://arraypress.com License:           GNU General
 * Public License v2 or later License URI:       https://www.gnu.org/licenses/gpl-2.0.html Requires PHP:      7.4
 * Requires at least: 6.5.4 Version:           1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\RegisterCustomColumnsPlugin;

use function ArrayPress\RegisterCustomColumns\register_post_columns;
use function ArrayPress\RegisterCustomColumns\register_comment_columns;
use function ArrayPress\RegisterCustomColumns\register_taxonomy_columns;
use function ArrayPress\RegisterCustomColumns\register_media_columns;
use function ArrayPress\RegisterCustomColumns\register_user_columns;
use ArrayPress\RegisterCustomColumns\Utils\ColumnHelper;

require_once __DIR__ . '/vendor/autoload.php';

// Register Post Columns
$custom_post_columns = [
	'thumbnail'   => [
		'label'               => '', // Left blank on purpose,
		'display_callback'    => function ( $value, $post_id, $column ) {
			$thumbnail_id = get_post_thumbnail_id( $post_id );

			return ColumnHelper::image_thumbnail( $thumbnail_id, [ 64, 64 ] );
		},
		'position'            => 'before:title',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
		'width'               => '64px'
	],
	'review_date' => [
		'label'               => __( 'Review Date', 'text-domain' ),
		'meta_key'            => 'review_date',
		'position'            => 'before:date',
		'sortable'            => true,
		'inline_edit'         => true,
		'inline_attributes'   => [
			'type'  => 'date',
			'style' => 'min-width: 120px'
		],
		'display_callback'    => function ( $value, $post_id, $column ) {
			return ColumnHelper::format_date_with_color( $value );
		},
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		}
	],
];
register_post_columns( [ 'post', 'page' ], $custom_post_columns );

// Register Comment Columns
$custom_comment_columns = [
	'comment_word_count' => [
		'label'               => __( 'Word Count', 'text-domain' ),
		'display_callback'    => function ( $value, $comment_id, $column ) {
			$comment    = get_comment( $comment_id );
			$word_count = str_word_count( $comment->comment_content );

			return number_format_i18n( $word_count );
		},
		'position'            => 'after:author',
		'permission_callback' => function () {
			return current_user_can( 'moderate_comments' );
		}
	],
];
register_comment_columns( $custom_comment_columns );

// Register Taxonomy Columns
$custom_taxonomy_columns = [
	'color'            => [
		'label'               => __( 'Color', 'text-domain' ),
		'meta_key'            => 'color_meta',
		'position'            => 'after:slug',
		'inline_edit'         => true,
		'inline_attributes'   => [
			'type'  => 'color',
			'style' => 'min-width: 64px'
		],
		'display_callback'    => function ( $value, $term_id, $column ) {
			return ColumnHelper::color_circle( $value );
		},
		'permission_callback' => function () {
			return current_user_can( 'manage_categories' );
		}
	],
	'membership_level' => [
		'label'               => __( 'Membership Level', 'text-domain' ),
		'meta_key'            => 'membership_level_meta',
		'position'            => 'after:color',
		'sortable'            => true,
		'inline_edit'         => true,
		'inline_attributes'   => [
			'type'    => 'select',
			'options' => get_membership_levels()
		],
		'display_callback'    => function ( $value, $term_id, $column ) {
			$options = get_membership_levels();

			return esc_html( $options[ $value ] ?? __( 'N/A', 'text-domain' ) );
		},
		'permission_callback' => function () {
			return current_user_can( 'manage_categories' );
		}
	],
];
register_taxonomy_columns( [ 'category', 'post_tag' ], $custom_taxonomy_columns );

// Register Media Library Columns
$custom_media_columns = [
	'file_size'      => [
		'label'               => __( 'File Size', 'text-domain' ),
		'display_callback'    => function ( $value, $attachment_id, $column ) {
			return ColumnHelper::attachment_file_size( $attachment_id );
		},
		'position'            => 'after:author',
		'permission_callback' => function () {
			return current_user_can( 'upload_files' );
		}
	],
	'file_type'      => [
		'label'               => __( 'File Type', 'text-domain' ),
		'display_callback'    => function ( $value, $attachment_id, $column ) {
			$file_type = ColumnHelper::attachment_file_type( $attachment_id );

			return esc_html( $file_type );
		},
		'position'            => 'after:file_size',
		'permission_callback' => function () {
			return current_user_can( 'upload_files' );
		}
	],
	'file_extension' => [
		'label'               => __( 'File Extension', 'text-domain' ),
		'display_callback'    => function ( $value, $attachment_id, $column ) {
			$file_extension = ColumnHelper::attachment_file_extension( $attachment_id );

			return esc_html( $file_extension ?? __( 'N/A', 'text-domain' ) );
		},
		'position'            => 'after:file_type',
		'permission_callback' => function () {
			return current_user_can( 'upload_files' );
		}
	],
];
register_media_columns( $custom_media_columns );

// Register User Columns
$custom_user_columns = [
	'points' => [
		'label'               => __( 'Points', 'text-domain' ),
		'meta_key'            => 'points',
		'sortable'            => true,
		'numeric'             => true,
		'inline_edit'         => true,
		'inline_attributes'   => [
			'type'        => 'number',
			'placeholder' => __( 'Points', 'text-domain' ),
		],
		'display_callback'    => function ( $value, $user_id, $column ) {
			ColumnHelper::format_numeric( $value );
		},
		'permission_callback' => function () {
			return current_user_can( 'edit_users' );
		},
	],
];
register_user_columns( $custom_user_columns );

/**
 * Get the membership levels.
 *
 * @return array An array of membership levels.
 */
function get_membership_levels(): array {
	return [
		''         => __( 'None', 'text-domain' ),
		'bronze'   => __( 'Bronze', 'text-domain' ),
		'silver'   => __( 'Silver', 'text-domain' ),
		'gold'     => __( 'Gold', 'text-domain' ),
		'platinum' => __( 'Platinum', 'text-domain' ),
	];
}