<?php
//working scripts

// Apply term "docs" to a non-map, non-report doc
function mrad_apply_doc_type( $doc_id ) {
	if ( bp_docs_get_post_type_name() != get_post_type( $doc_id ) ) {
		return PHP_EOL . "Failed. {$doc_id} is not a doc.";
	}

	$terms = wp_get_object_terms( $doc_id, 'bp_docs_type' );

	if ( empty( $terms ) ) {
		wp_set_object_terms( $doc_id, 'doc', 'bp_docs_type' );
		return PHP_EOL . "Updated doc {$doc_id}";
	} else {
		return PHP_EOL . "Did not update doc {$doc_id}" . PHP_EOL . 'Terms: ' . print_r( $terms, true);

	}

}