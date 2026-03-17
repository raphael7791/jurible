<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JAM_Sync {

    /**
     * Run synchronization (Phase 5 — stub).
     */
    public static function run() {
        return [
            'enrolled'         => 0,
            'already_enrolled' => 0,
            'errors'           => 0,
            'message'          => 'Synchronisation pas encore implémentée (Phase 5).',
        ];
    }
}
