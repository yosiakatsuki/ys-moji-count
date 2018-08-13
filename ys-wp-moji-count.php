<?php
/**
 * Plugin Name:     Ys WP Moji Count
 * Plugin URI:      https://github.com/yosiakatsuki/ys-wp-moji-count
 * Description:     投稿一覧ページに文字数カウント列を追加するだけのプラグイン
 * Author:          yosiakatsuki
 * Author URI:      https://yosiakatsuki.net/
 * Text Domain:     ys-wp-moji-count
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Ys WP Moji Count
 */

/*
Copyright (c) 2018 Yoshiaki Ogata (https://yosiakatsuki.net/)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


define( 'YWMC_META_KEY', '_ywmc_moji_cnt' );

/**
 * カラムを追加
 *
 * @param array $columns columns.
 *
 * @return array
 */
function ywmc_manage_posts_columns( $columns ) {
	$columns = array_merge( $columns, array( 'ywmc_moji_count' => '文字数' ) );

	return $columns;
}

add_filter( 'manage_posts_columns', 'ywmc_manage_posts_columns' );

/**
 * カラムの中身を追加
 *
 * @param string $column  column name.
 * @param int    $post_id post id.
 */
function ywmc_manage_posts_custom_column( $column, $post_id ) {
	global $post;
	if ( 'ywmc_moji_count' == $column ) {
		$meta_value = get_post_meta( $post_id, YWMC_META_KEY, true );
		if ( ! empty( $meta_value ) && is_numeric( $meta_value ) ) {
			$moji_cnt = (int) $meta_value;
		} else {
			$moji_cnt = ywmc_moji_count( $post->post_content );
			update_post_meta( $post_id, YWMC_META_KEY, $moji_cnt );
		}
		echo number_format( $moji_cnt );
	}
}

add_action( 'manage_posts_custom_column', 'ywmc_manage_posts_custom_column', 10, 2 );


/**
 * 記事更新時に文字数をカウントしておく
 *
 * @param  string  $new_status new status.
 * @param  string  $old_status old status.
 * @param  WP_Post $post       post object.
 */
function ywmc_transition_post_status( $new_status, $old_status, $post ) {
	//カスタムフィールド更新タイミング
	$status = array(
		'publish',
		'pending',
		'draft',
		'future',
		'private',
	);
	if ( in_array( $new_status, $status ) ) {
		$moji_cnt = ywmc_moji_count( $post->post_content );
		update_post_meta( $post->ID, YWMC_META_KEY, $moji_cnt );
	}
}

add_action( 'transition_post_status', 'ywmc_transition_post_status', 10, 3 );


/**
 * 投稿本文の文字数をカウント
 *
 * @param string $post_content post content.
 *
 * @return int
 */
function ywmc_moji_count( $post_content ) {
	return mb_strlen( str_replace( array( "\r\n", "\r", "\n" ), '', strip_tags( apply_filters( 'the_content', $post_content ) ) ) );
}