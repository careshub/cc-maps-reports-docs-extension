<?php

/**
 * General-purpose functions
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC_BuddyPress_Docs_Maps_Reports_Extension
 * @subpackage CC_BuddyPress_Docs_Maps_Reports_Extension/includes
 */

function mrad_report_base_url() {
    // We use the staging site for testing, use the real maps environment for production.
    $location = get_site_url();
    switch ( $location ) {
        case 'http://www.communitycommons.org':
            // Production value:
            $base_url = 'http://assessment.communitycommons.org/';
            break;
        default:
            // Testing value:
            $base_url = 'http://staging.maps.communitycommons.org/';
            break;
    }
    return apply_filters( 'mrad_report_base_url', $base_url );
}

function mrad_report_create_link_url() {
    return mrad_report_base_url() . 'CHNA/SelectArea.aspx?reporttype=libraryCHNA';
}

function mrad_report_open_link_url( $doc_id = 0 ) {
    if ( empty( $doc_id ) ) {
        if ( is_singular( bp_docs_get_post_type_name() ) && $q = get_queried_object() ) {
            $doc_id = isset( $q->ID ) ? $q->ID : 0;
        } else if ( get_the_ID() ) {
            $doc_id = get_the_ID();
        }
    }
    $report_id = get_post_meta( $doc_id, 'report_table_ID', true );

    return mrad_report_base_url() . 'CHNA/OpenReport.aspx?id=' . $report_id;
}

function mrad_map_base_url() {
    // We use the staging site for testing, use the real maps environment for production.
    $location = get_site_url();
    switch ( $location ) {
        case 'http://www.communitycommons.org':
            // Production value:
            $base_url = 'http://maps.communitycommons.org/';
            break;
        default:
            // Testing value:
            $base_url = 'http://staging.maps.communitycommons.org/';
            break;
    }
    return apply_filters( 'mrad_map_base_url', $base_url );
}

function mrad_map_create_link_url() {
    return mrad_map_base_url();
}

function mrad_map_open_link_url( $doc_id = 0 ) {
    if ( empty( $doc_id ) ) {
        if ( is_singular( bp_docs_get_post_type_name() ) && $q = get_queried_object() ) {
            $doc_id = isset( $q->ID ) ? $q->ID : 0;
        } else if ( get_the_ID() ) {
            $doc_id = get_the_ID();
        }
    }
    $map_id = get_post_meta( $doc_id, 'map_table_ID', true );

    return mrad_map_base_url() . 'viewer/?action=open_map&id=' . $map_id;
}