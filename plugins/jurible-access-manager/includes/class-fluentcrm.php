<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JAM_FluentCRM {

    /**
     * Tag & list a contact on purchase.
     *
     * @param int    $user_id WordPress user ID.
     * @param object $rule    Access rule object.
     */
    public static function on_purchase( $user_id, $rule ) {
        if ( ! function_exists( 'FluentCrmApi' ) ) {
            return;
        }

        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            return;
        }

        $contact = FluentCrmApi( 'contacts' )->createOrUpdate( [
            'email'  => $user->user_email,
            'status' => 'subscribed',
        ] );

        if ( ! $contact || is_wp_error( $contact ) ) {
            return;
        }

        $tag_ids  = JAM_Access_Rules::get_crm_tag_ids( $rule );
        $list_ids = JAM_Access_Rules::get_crm_list_ids( $rule );

        if ( ! empty( $tag_ids ) ) {
            $contact->attachTags( $tag_ids );
        }

        if ( ! empty( $list_ids ) ) {
            $contact->attachLists( $list_ids );
        }
    }

    /**
     * Untag & unlist a contact on revocation.
     *
     * @param int    $user_id WordPress user ID.
     * @param object $rule    Access rule object.
     */
    public static function on_revoke( $user_id, $rule ) {
        if ( ! function_exists( 'FluentCrmApi' ) ) {
            return;
        }

        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            return;
        }

        $contact = FluentCrmApi( 'contacts' )->getContactByUserRef( $user_id );
        if ( ! $contact ) {
            $contact = FluentCrmApi( 'contacts' )->getContact( $user->user_email );
        }

        if ( ! $contact ) {
            return;
        }

        $tag_ids  = JAM_Access_Rules::get_crm_tag_ids( $rule );
        $list_ids = JAM_Access_Rules::get_crm_list_ids( $rule );

        if ( ! empty( $tag_ids ) ) {
            $contact->detachTags( $tag_ids );
        }

        if ( ! empty( $list_ids ) ) {
            $contact->detachLists( $list_ids );
        }
    }
}
