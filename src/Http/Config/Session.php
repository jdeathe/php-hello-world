<?php

// Required settings for multiple Memcached session store nodes
if (
    ini_get(
        'session.save_handler'
    ) == 'memcached' &&
    0 !== substr_count(
        ini_get(
            'session.save_path'
        ),
        ','
    )
) {
    ini_set(
        'memcached.sess_binary',
        'On'
    );
    ini_set(
        'memcached.sess_consistent_hash',
        'On'
    );
    ini_set(
        'memcached.sess_number_of_replicas',
        (string) substr_count(
            ini_get(
                'session.save_path'
            ),
            ','
        )
    );
    ini_set(
        'memcached.sess_remove_failed',
        '1'
    );
}
