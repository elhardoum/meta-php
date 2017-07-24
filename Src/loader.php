<?php

use MetaPHP\Meta;

/**
  * Config
  */

defined ( 'DB_HOST' ) || define ( 'DB_HOST', 'localhost' );
defined ( 'DB_NAME' ) || define ( 'DB_NAME', '' );
defined ( 'DB_USER' ) || define ( 'DB_USER', '' );
defined ( 'DB_PASS' ) || define ( 'DB_PASS', '' );

require_once __DIR__ . '/App/Database.php';
require_once __DIR__ . '/App/Meta.php';

function metaphp($fresh=null) { return Meta::instance($fresh); }

function metaphp_options() {
    return metaphp()->group('options');
}

function metaphp_usermeta() {
    return metaphp()->group('users');
}

function metaphp_postmeta() {
    return metaphp()->group('posts');
}

function get_option($name, $default=null) {
    if ( !trim($name) )
        return;

    // $cached = metaphp_cached_options();

    // if ( isset($cached[$name]) )
    //     return $cached[$name];

    $value = metaphp_options()->get($name, null, $default);

    // $cached[$name] = $value;
    // metaphp_cached_options_set($cached);

    return $value;
}

function get_user_meta($user_id, $name, $default=null) {
    if ( !trim($name) )
        return;

    // $cached = cached_user_meta($user_id);

    // if ( isset($cached[$name]) )
    //     return $cached[$name];

    $value = metaphp_usermeta()->get($name, $user_id, $default);

    // $cached[$name] = $value;
    // cached_user_meta_set($user_id, $cached);

    return $value;
}

function get_post_meta($post_id, $name, $default=null) {
    if ( !trim($name) )
        return;

    // $cached = cached_post_meta($post_id);

    // if ( isset($cached[$name]) )
    //     return $cached[$name];

    $value = metaphp_postmeta()->get($name, $post_id, $default);

    // $cached[$name] = $value;
    // cached_post_meta_set($post_id, $cached);

    return $value;
}

function update_option($name, $value) {
    $updated = metaphp_options()->update($name, $value);

    // if ( $updated ) {
    //     $cached = metaphp_cached_options();
    //     $cached[$name] = $value;
    //     metaphp_cached_options_set($cached);
    // }

    return $updated;
}

function update_user_meta($user_id, $name, $value) {
    $updated = metaphp_usermeta()->update($name, $value, $user_id);

    // if ( $updated ) {
    //     $cached = cached_user_meta($user_id);
    //     $cached[$name] = $value;
    //     cached_user_meta_set($user_id, $cached);
    // }

    return $updated;
}

function update_post_meta($post_id, $name, $value) {
    $updated = metaphp_postmeta()->update($name, $value, $post_id);

    // if ( $updated ) {
    //     $cached = cached_post_meta($post_id);
    //     $cached[$name] = $value;
    //     cached_post_meta_set($post_id, $cached);
    // }

    return $updated;
}

function delete_option($name) {
    $deleted = metaphp_options()->delete($name);

    // if ( $deleted ) {
    //     $cached = metaphp_cached_options();
    //     if ( isset($cached[$name]) ) {
    //         unset($cached[$name]);
    //         metaphp_cached_options_set($cached);
    //     }
    // }

    return $deleted;
}

function delete_user_meta($user_id, $name) {
    $deleted = metaphp_usermeta()->delete($name, $user_id);

    // if ( $deleted ) {
    //     $cached = cached_user_meta($user_id);
    //     if ( isset($cached[$name]) ) {
    //         unset($cached[$name]);
    //         cached_user_meta_set($user_id, $cached);
    //     }
    // }

    return $deleted;
}

function delete_post_meta($post_id, $name) {
    $deleted = metaphp_postmeta()->delete($name, $post_id);

    // if ( $deleted ) {
    //     $cached = cached_post_meta($post_id);
    //     if ( isset($cached[$name]) ) {
    //         unset($cached[$name]);
    //         cached_post_meta_set($post_id, $cached);
    //     }
    // }

    return $deleted;
}

function delete_all_options() {
    if ( metaphp_options()->deleteAll(null) ) {
        // Cache::delete('options');
        // metaphp_cached_options_delete();
        return true;
    }
}

function delete_all_user_meta($user_id) {
    if ( metaphp_usermeta()->deleteAll(null, $user_id) ) {
        // Cache::delete("user_meta_{$user_id}");
        // cached_user_meta_delete($user_id);
        return true;
    }
}

function delete_all_post_meta($post_id) {
    if ( metaphp_postmeta()->deleteAll(null, $post_id) ) {
        // Cache::delete("post_meta_{$post_id}");
        // cached_post_meta_delete($post_id);
        return true;
    }
}

/** some helpers from WordPress core **/

if ( !function_exists('maybe_serialize') ) :

function maybe_serialize( $data ) {
    if ( is_array( $data ) || is_object( $data ) )
        return serialize( $data );
 
    if ( is_serialized( $data, false ) )
        return serialize( $data );
 
    return $data;
}

endif;

if ( !function_exists('maybe_unserialize') ) :

function maybe_unserialize( $original ) {
    if ( is_serialized( $original ) )
        return @unserialize( $original );
    return $original;
}

endif;

if ( !function_exists('is_serialized') ) :

function is_serialized( $data, $strict = true ) {
    // if it isn't a string, it isn't serialized.
    if ( ! is_string( $data ) ) {
        return false;
    }
    $data = trim( $data );
    if ( 'N;' == $data ) {
        return true;
    }
    if ( strlen( $data ) < 4 ) {
        return false;
    }
    if ( ':' !== $data[1] ) {
        return false;
    }
    if ( $strict ) {
        $lastc = substr( $data, -1 );
        if ( ';' !== $lastc && '}' !== $lastc ) {
            return false;
        }
    } else {
        $semicolon = strpos( $data, ';' );
        $brace     = strpos( $data, '}' );
        // Either ; or } must exist.
        if ( false === $semicolon && false === $brace )
            return false;
        // But neither must be in the first X characters.
        if ( false !== $semicolon && $semicolon < 3 )
            return false;
        if ( false !== $brace && $brace < 4 )
            return false;
    }
    $token = $data[0];
    switch ( $token ) {
        case 's' :
            if ( $strict ) {
                if ( '"' !== substr( $data, -2, 1 ) ) {
                    return false;
                }
            } elseif ( false === strpos( $data, '"' ) ) {
                return false;
            }
            // or else fall through
        case 'a' :
        case 'O' :
            return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
        case 'b' :
        case 'i' :
        case 'd' :
            $end = $strict ? '$' : '';
            return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
    }
    return false;
}

endif;