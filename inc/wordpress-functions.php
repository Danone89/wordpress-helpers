<?php

/*
 * Kod źródłowy chroniony prawem autorskim.
 * Licencja GNU Lesser General Public License v3
 * Autor Daniel Bośnjak
 */



function is_crawler()
{
    $ar = array('arachnoidea', 'googlebot', 'msn', 'gulper', 'zyborg', 'cyveillance');
    foreach ($ar as $crawl)
        if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), $crawl) !== false) {
            return true;
            break;
        }
    return false;
}
/**
 * Check if current request is made by AJAX.
 *
 * @return boolean
 */
if (!function_exists('is_ajax')) :
    function is_ajax()
    {
        return wp_doing_ajax() or (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
    }
endif;
/**
 * Display 404 page, you must call this in template_redirect or earlier. In other case it wont work and rise errors.
 * 
 * @global WP_Query $wp_query
 * @param string $cust_message
 * @param string $cust_title
 */
function go404($cust_message = false, $cust_title = false)
{
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    //get_template_part('404');
}

function get_post_terms_assoc($post_id, $taxonomy, $field = 'name')
{
    $args['fileds'] = 'all';
    $terms = wp_get_post_terms($post_id, $taxonomy, $args);
    foreach ($terms as $t) {
        $ret[$t->term_id] = $t->$field;
    }

    return $ret;
}

function get_taxonomy_tree($args = array(), $taxonomy, $simple = 0)
{
    $args = array_merge($args, array('hide_empty' => 0, 'title_li' => false, 'hierarchical' => 1));
    $cats = get_terms($taxonomy, $args);

    $roots = $childs = $ret = array();
    if (is_array($cats)) {
        while ($element = array_shift($cats)) {
            if ($element->parent == 0) {
                array_push($roots, $element);
            } else {
                $childs[$element->parent][] = $element;
            }
        }

        $ret = array('roots' => $roots, 'childs' => $childs);
    }


    if ($simple && empty($childs))
        $ret = $ret['roots'];
    return $ret;
}




/**
 * 
 * @param int $catid 
 * @param string $taxonomy
 * @return int ID of root category
 */
function get_root_term($term_id, $taxonomy = 'category', $type = 'ID')
{
    //cache
    if (is_object($term_id)) {
        if ($term_id->parent == 0)
            $term = $term_id;
        else {
            $parent = $term_id->parent;
            $catid = $term_id->term_id;
        }
    } else {
        $parent =  $catid = $term_id;
    }

    if (empty($term)) {
        $cacheId = 'catid_top_' . $taxonomy . '_' . $catid;
        $term = wp_cache_get($cacheId, 'efait_');
        if ($term === false) {
            while ($parent > 0) {
                $term = get_term($parent, $taxonomy); // get the object for the catid
                $catid = $term->term_id;
                $parent = $term->parent;

                // the while loop will continue whilst there is a $catid
                // when there is no longer a parent $catid will be NULL so we can assign our $catParent
            }
            wp_cache_set($cacheId, $term, 'efait_');
        }
    }
    return $type == 'ID' ? $term->term_id : $term;
}


/**
 * Link do strony autora 
 * 
 * @param int $author_id 
 * @param string $class Klasy CSS do wstawienia w linku
 * @return type
 */
function get_the_author_name_linked($author_id = 0, $class = '')
{
    global $post;
    $author_id = $author_id === 0 ? $post->post_author : 0;
    if ($author_id == 0) return '';
    return '<a href="' . get_author_posts_url($author_id) . '" class="' . $class . '" >' . get_the_author_meta('display_name', $author_id) . '</a>';
}


/**
 * Check if currently page is login page. Works with default wordpress login screen, woocommerce and others if using login_page hook.
 * @return bool
 */
function is_login()
{
    if (!class_exists('WC')) {
        $result = is_account_page() && !is_user_logged_in();
    } else {
        $result = in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
    }

    return apply_filters('login_page', $result);
}

/**
 * Count users posts by supplied type and in supplied date rande..
 * 
 * @param int $userid
 * @param mixed $post_type 
 * @param time $date
 * 
 * @return int post count
 */
function count_user_posts_by_type($author_id, $post_type = 'post', $date_from = false, $date_to = false)
{
    global $wpdb;

    $where = get_posts_by_author_sql($post_type, true, $author_id);
    if ($date_to)
        $where .= " AND  DATE(post_date) >= '$date_to'";
    if ($date_from)
        $where .= " AND  DATE(post_date) <= '$date_from'";

    $count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts $where");

    return apply_filters('get_usernumposts', $count, $author_id);
}

/**
 * Get user categories where he get published content. Resource hungry, should be queued - uses transients.
 * 
 * @param in $author_id
 * @return string[] ids comma separeted
 */
function user_post_categories($author_id, $post_type = 'product', $tax = 'product_cat')
{
    $cacheId = 'user_cats_' . $author_id;
    return;
    if (!$user_cats = wp_cache_get($cacheId)) {
        $query['post_author'] = $author_id;
        $query['numberposts'] = -1;
        $query['fields'] = 'ids';
        $query['post_type'] = $post_type;
        $user_cats = wp_get_object_terms(get_posts($query), $tax);
        wp_cache_add($cacheId, $user_cats);
    }

    return $user_cats;
}



/**
 * Get object link by slug
 * @global std $wpdb
 * @param string $page_slug
 * @param constans $output
 * @param string $post_type
 * @return string
 */
function get_object_link_by_slug($page_slug, $post_type = 'page', $output = OBJECT)
{
    global $wpdb;
    $page = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s AND post_status = 'publish'", $page_slug, $post_type));
    if ($page)
        return get_permalink($page);
    return '';
}


/**
 * Create tmp dir, and if need underneath folder structer
 *
 * @param string $dir no traling slash
 * @return string full path
 */
function wp_tmp_dir($dir = '')
{

    $uploads = wp_get_upload_dir();
    $path =   $uploads['basedir'] . '/tmp' . ($dir ? '/' . $dir : '');
    if (!is_dir($path))
        mkdir($path, 0775, true);

    return $path . '/';
}
function wp_date_localised($format, $timestamp = null)
{
    // This function behaves a bit like PHP's Date() function, but taking into account the Wordpress site's timezone
    // CAUTION: It will throw an exception when it receives invalid input - please catch it accordingly
    // From https://mediarealm.com.au/

    $tz_string = get_option('timezone_string');
    $tz_offset = get_option('gmt_offset', 0);

    if (!empty($tz_string)) {
        // If site timezone option string exists, use it
        $timezone = $tz_string;
    } elseif ($tz_offset == 0) {
        // get UTC offset, if it isn’t set then return UTC
        $timezone = 'UTC';
    } else {
        $timezone = $tz_offset;

        if (substr($tz_offset, 0, 1) != "-" && substr($tz_offset, 0, 1) != "+" && substr($tz_offset, 0, 1) != "U") {
            $timezone = "+" . $tz_offset;
        }
    }

    if ($timestamp === null) {
        $timestamp = time();
    }

    $datetime = new DateTime();
    $datetime->setTimestamp($timestamp);
    $datetime->setTimezone(new DateTimeZone($timezone));
    return $datetime->format($format);
}

/**
 * Strtotime with localised outbput by taking into account the Wordpress site's timezone. 
 * Throws InvalidDateString on wrong input
 * @param [type] $str
 * @return void
 */
function wp_strtotime($str)
{

    $tz_string = get_option('timezone_string');
    $tz_offset = get_option('gmt_offset', 0);

    if (!empty($tz_string)) {
        // If site timezone option string exists, use it
        $timezone = $tz_string;
    } elseif ($tz_offset == 0) {
        // get UTC offset, if it isn’t set then return UTC
        $timezone = 'UTC';
    } else {
        $timezone = $tz_offset;

        if (substr($tz_offset, 0, 1) != "-" && substr($tz_offset, 0, 1) != "+" && substr($tz_offset, 0, 1) != "U") {
            $timezone = "+" . $tz_offset;
        }
    }
    $datetime = new DateTime($str, new DateTimeZone($timezone));
    return $datetime->format('U');
}
/**
 * Undocumented function
 *
 * @param [int/object WP_User] $id_or_object
 * @return void
 */
function get_user_roles(&$id_or_object)
{
    global $wp_roles;
    if ($id_or_object instanceof \WP_User) {
        $user = $id_or_object;
    } else {
        $user = get_userdata($id);
    }
    foreach ($user->roles as $role)
        yield $wp_roles->roles[$role];
}
