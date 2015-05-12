<?php

/**
 * This allows the creation of bp-docs programatically. We're ahead of the plugin, here, so this may be able to be replaced later.
 *
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    CC_BuddyPress_Docs_Maps_Reports_Extension
 * @subpackage CC_BuddyPress_Docs_Maps_Reports_Extension/includes
 */

class CC_MRAD_BP_Doc_Save {
    var $post_type_name;
    var $associated_item_tax_name;

    var $doc_id;
    var $doc_slug;

    var $is_new_doc;

    /**
     * PHP 5 constructor
     *
     * @package BuddyPress Docs
     * @since 1.0-beta
     */
    function __construct( $args = array() ) {
        global $bp;

        $this->post_type_name       = $bp->bp_docs->post_type_name;
        $this->associated_item_tax_name = $bp->bp_docs->associated_item_tax_name;

    }


    /**
     * Saves a doc.
     *
     * This method handles saving for both new and existing docs. It detects the
     * difference by looking for the presence of $this->doc_slug
     *
     * @package BuddyPress Docs
     * @since 1.0-beta
     *
     * @param array $passed_args {
     *        @type int    $doc_id ID of the doc, if it already exists.
     *        @type string $title Doc title.
     *        @type string $content Doc content.
     *        @type string $permalink Optional. Permalink will be calculated if
     *                     if not specified.
     *        @type int    $author_id ID of the user submitting the changes.
     *        @type int    $group_id ID of the associated group, if any.
     *                     Special cases: Passing "null" leaves current group
     *                     associations intact. Passing 0 will unset existing
     *                     group associations.
     *        @type bool   $is_auto Is this an autodraft?
     *        @type array  $taxonomies Taxonomy terms to apply to the doc.
     *                     Use the form: array( $tax_name => (array) $terms ).
     *        @type array  $settings Doc access settings. Of the form:
     *                     array( 'read' => 'group-members',
     *                            'edit' => 'admins-mods',
     *                            'read_comments' => 'group-members',
     *                            'post_comments' => 'group-members',
     *                            'view_history' => 'creator' )
     *        @type int   $parent_id The ID of the parent doc, if applicable.
     *        }
     * @return int 0 on error, post_id on success
     */
    function save( $passed_args = false ) {
        $bp = buddypress();
        $retval = 0;

        // Sensible defaults
        $defaults = array(
            'doc_id'        => 0,
            'title'         => '',
            'content'       => '',
            'permalink'     => '',
            'author_id'     => bp_loggedin_user_id(),
            'group_id'      => null,
            'is_auto'       => 0,
            'taxonomies'    => array(),
            'settings'      => array(),
            'parent_id'     => 0,
            );

        $args = wp_parse_args( $passed_args, $defaults );

        $towrite = PHP_EOL . 'parsed $args, in save() ' . print_r( $args, TRUE );
        $fp = fopen('xml-rpc-request-args.txt', 'a');
        fwrite($fp, $towrite);
        fclose($fp);

        // bbPress plays naughty with revision saving
        add_action( 'pre_post_update', 'wp_save_post_revision' );

        // Check group associations
        // @todo Move into group integration piece
        if ( bp_is_active( 'groups' ) ) {
            // Check whether the user can associate the doc with the group.
            // $args['group_id'] could be null (untouched) or 0, which unsets existing association
            if ( ! empty( $args['group_id'] ) && ! $this->user_can_associate_with_group( $args['group_id'], $args['author_id'] ) ) {
                return $retval;
            }
        }

        if ( empty( $args['title'] ) ) {
            // The title field is required
            return $retval;
        } else {
            // Use the passed permalink if it exists, otherwise create one
            if ( ! empty( $args['permalink'] ) ) {
                $args['permalink'] = sanitize_title( $args['permalink'] );
            } else {
                $args['permalink'] = sanitize_title( $args['title'] );
            }

            $r = array(
                'post_type'    => bp_docs_get_post_type_name(),
                'post_title'   => $args['title'],
                'post_name'    => $args['permalink'],
                'post_content' => sanitize_post_field( 'post_content', $args['content'], 0, 'db' ),
                'post_status'  => 'publish',
                'post_parent'  => $args['parent_id']
            );

            $towrite = PHP_EOL . 'parsed $r, in save(): ' . print_r( $r, TRUE );
            $fp = fopen('xml-rpc-request-args.txt', 'a');
            fwrite($fp, $towrite);
            fclose($fp);

            if ( empty( $args['doc_id'] ) ) {
                $this->is_new_doc = true;

                // We only save the author for new docs.
                $r['post_author'] = $args['author_id'];

                // If there's a 'doc_id' value use
                // the autodraft as a starting point.
                if ( 0 != $args['doc_id'] ) {
                    $post_id = (int) $args['doc_id'];
                    $r['ID'] = $post_id;
                    wp_update_post( $r );
                } else {
                    $post_id = wp_insert_post( $r );
                }

                if ( ! $post_id ) {
                    $retval = 0;
                } else {
                    $this->doc_id = $post_id;

                    $the_doc = get_post( $this->doc_id );
                    $this->doc_slug = $the_doc->post_name;

                    // A normal, successful save
                    $retval = $post_id;
                }
            } else {
                $this->is_new_doc = false;

                $this->doc_id = $args['doc_id'];
                $r['ID']      = $this->doc_id;

                // Make sure the post_name is unique, wp_unique_post_slug requires a post_id
                $r['post_name'] = wp_unique_post_slug( $r['post_name'], $this->doc_id, $r['post_status'], $this->post_type_name, $r['post_parent'] );

                $this->doc_slug = $r['post_name'];

                if ( ! wp_update_post( $r ) ) {
                    $retval = false;
                } else {
                    // Remove the edit lock
                    delete_post_meta( $this->doc_id, '_edit_lock' );
                    delete_post_meta( $this->doc_id, '_bp_docs_last_pinged' );
                    $retval = $this->doc_id;
                }

                $post_id = $this->doc_id;
            }
        }

        // If the Doc was successfully created, run some more stuff
        if ( ! empty( $post_id ) ) {

            // Add to a group, if necessary
            if ( ! is_null( $args['group_id'] ) ) {
                bp_docs_set_associated_group_id( $post_id, $args['group_id'] );
            }

            // Save the last editor id. We'll use this to create an activity item
            update_post_meta( $this->doc_id, 'bp_docs_last_editor', $args['author_id'] );

            // Update taxonomies if necessary
            $towrite = PHP_EOL . 'taxonomy looping: ' . print_r( $args['taxonomies'], TRUE );

            if ( ! empty( $args['taxonomies'] ) ) {
                foreach ( $args['taxonomies'] as $tax_name => $terms ) {
                    // Make sure that the terms are an array
                    if ( ! is_array( $terms ) ) {
                        $terms = explode( ',', $terms );
                    }
                    $towrite .= PHP_EOL . 'taxonomy name: ' . print_r( $tax_name, TRUE ) . ' | taxonomy term: ' . print_r( $terms, TRUE );
                    $tax_result = wp_set_object_terms( $post_id, $terms, $tax_name );
                    $towrite .= PHP_EOL . 'result: ' . print_r( $tax_result, TRUE );
                }
            }
            $fp = fopen('xml-rpc-request-args.txt', 'a');
            fwrite($fp, $towrite);
            fclose($fp);

            // Save settings.
            $this->save_doc_access_settings( $this->doc_id, $args['author_id'], $args['settings'] );

            // Increment the revision count
            $revision_count = get_post_meta( $this->doc_id, 'bp_docs_revision_count', true );
            update_post_meta( $this->doc_id, 'bp_docs_revision_count', intval( $revision_count ) + 1 );
        }

        // Provide a custom hook for plugins and optional components.
        // WP's default save_post isn't enough, because we need something that fires
        // only when we save from the front end (for things like taxonomies, which
        // the WP admin handles automatically)
        do_action( 'bp_docs_doc_saved', $this );

        do_action( 'bp_docs_after_save', $this->doc_id );

        return $retval;
    }

    function save_doc_access_settings( $doc_id, $author_id, $settings ) {
        if ( empty( $author_id ) ) {
            $author_id = bp_loggedin_user_id();
        }

        // Two cases:
        // 1. User is saving a doc for which he can update the access settings
        if ( ! empty( $settings ) ) {

            update_post_meta( $doc_id, 'bp_docs_settings', $settings );

            // The 'read' setting must also be saved to a taxonomy, for
            // easier directory queries
            $read_setting = isset( $settings['read'] ) ? $settings['read'] : 'anyone';
            bp_docs_update_doc_access( $doc_id, $read_setting );

        // 2. User is saving a doc for which he can't manage the access settings
        // isset( $_POST['settings'] ) is false; the access settings section
        // isn't included on the edit form
        } else {
            // Do nothing.
            // Leave the access settings intact.
        }
    }

    function user_can_associate_with_group( $group_id = 0, $user_id = 0 ) {

        if ( empty( $group_id ) || empty( $user_id ) ) {
            return false;
        }

        if ( user_can( $user_id, 'bp_moderate' ) ) {
            return true;
        }

        $group_settings = bp_docs_get_group_settings( $group_id );

        switch ( $group_settings['can-create'] ) {
            case 'admin' :
                if ( groups_is_user_admin( $user_id, $group_id ) ) {
                    $retval = true;
                } else {
                    $retval = false;
                }

                break;
            case 'mod' :
                if ( groups_is_user_mod( $user_id, $group_id ) || groups_is_user_admin( $user_id, $group_id ) ) {
                    $retval = true;
                } else {
                    $retval = false;
                }

                break;
            case 'member' :
            default :
                if ( groups_is_user_member( $user_id, $group_id ) ) {
                    $retval = true;
                } else {
                    $retval = false;
                }
                break;
        }
        return $retval;
    }
} // End class