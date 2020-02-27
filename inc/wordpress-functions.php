<?php

/*
 * Kod źródłowy chroniony prawem autorskim.
 * Autor Daniel Bośnjak
 * Pliki ładowane przez WPCMS udostępnione są na licencji OpenSource v3.0
 */

/**
 * Bez warunkowo wyświetl błąd 404 - funkcja musi być wywołana przed get_header
 * 
 * @global WP_Query $wp_query
 * @param string $cust_message
 * @param string $cust_title
 */

 
function is_crawler() {
    $ar = array('arachnoidea', 'googlebot', 'msn', 'gulper', 'zyborg', 'cyveillance');
    foreach ($ar as $crawl)
        if (strstr(strtolower($_SERVER['HTTP_USER_AGENT']), $crawl) !== false) {
            return true;
            break;
        }
    return false;
}

function is_ajax() {
    return wp_doing_ajax() or (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
}

function go404($cust_message = false, $cust_title = false) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404');
    exit();
}

function get_post_terms_assoc($post_id, $taxonomy, $field = 'name') {
    $args['fileds'] = 'all';
    $terms = wp_get_post_terms($post_id, $taxonomy, $args);
    foreach ($terms as $t) {
        $ret[$t->term_id] = $t->$field;
    }

    return $ret;
}

/**
 * 
 * @param int $post_id
 * @return int ID autora
 */
function get_post_author($post_id, $object = false) {
    //cache
    $listing = get_post((int) $post_id);
    if (!$listing)
        return false;

    $author_id = $listing->post_author;

    if ($object) {
        return get_userdata($author_id);
    }

    return $author_id;
}

function get_taxonomy_tree($args = array(), $taxonomy, $simple = 0) {
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

function list_hooked_functions($tag = false) {
    global $wp_filter;
    if ($tag) {
        $hook[$tag] = $wp_filter[$tag];
        if (!is_array($hook[$tag])) {
            trigger_error("Nothing found for '$tag' hook", E_USER_WARNING);
            return;
        }
    } else {
        $hook = $wp_filter;
        ksort($hook);
    }
    echo '<pre>';
    foreach ($hook as $tag => $priority) {
        echo "<br />&gt;&gt;&gt;&gt;&gt;\t<strong>$tag</strong><br />";
        ksort($priority);
        foreach ($priority as $priority => $function) {
            echo $priority;
            foreach ($function as $name => $properties)
                echo "\t$name<br />";
        }
    }
    echo '</pre>';
    return;
}

/*
 * Get top parent
 */

function get_root_category($category_id = '') {
    //cache
    $cid = wp_cache_get('catid_top_' . $category_id, 'efait_');
    if ($cid === false) {
        if (is_category($category_id)) {
            $parent_cats = get_category_parents($category_id);
            $split_arr = explode('/', $parent_cats);
            $cid = get_cat_id($split_arr[0]);
            wp_cache_set('catid_top_' . $category_id, $cid, 'efait_' );
        }
    }
    return $cid;
}

/**
 * 
 * @param int $catid 
 * @param string $taxonomy
 * @return int ID of root category
 */
function get_root_term($catid, $taxonomy = 'category', $type = 'ID') {
//cache
    if (is_object($catid)) {
        if ($catid->parent == 0)
            $term = $catid;
        else {
            $parent = $catid->parent;
            $catid = $catid->term_id;
        }
    } else {
        $parent = $catid;
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
 * Get data about top term of selected taxonomy for post.
 * @param $post_id int
 * @param $taxonomy string = valid taxonomy
 * 
 * @return mixed
 */
function get_post_root_term($post_id, $taxonomy = 'category', $type = 'ID') {
    // global $post;
    if (is_object($post_id)) {
        $post_id = $post_id->ID;
    }
    $single = false;
    $term = wp_cache_get('catid_top_' . $taxonomy . '_' . $post_id, 'efait_');

    if ($term == false) {
        $terms = wp_get_post_terms($post_id, $taxonomy);
       
        foreach ($terms as $aterm) {
            if ($aterm->parent == 0 && $aterm->term_id != 1) {
                $term[] = $aterm;
                if (in_array($type, ['ID', 'single'])) {
                    $single = true;
                    break;
                }
            }
        }

        if (!$term) {
            $term = get_root_term($terms[0], $taxonomy, $type);
        }
        wp_cache_set('catid_top_' . $taxonomy . '_' . $post_id, $term, 'efait_');
    }

    if ($single)
        return $type == 'ID' ? $term[0]->term_id : $term[0];
    else
        return $term;
}

/**
 * Link do strony autora 
 * 
 * @param int $author_id 
 * @param string $class Klasy CSS do wstawienia w linku
 * @return type
 */
function get_the_author_name_linked($author_id, $class = '') {
    return '<a href="' . get_author_posts_url($author_id) . '" class="' . $class . '" >' . get_the_author_meta('display_name', $author_id) . '</a>';
}


/**
 * Sprawdza czy jesteśmy na stronie logowania domyślnej dla wordpressa.
 * @return bool
 */
function is_login() {
    return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
}

/**
 *  Liczenie wskazanych postów od opcjonalnie wskazanej daty
 * 
 * @param int $userid
 * @param string $post_type 
 * @param time $date
 * 
 * @return int post count
 */
function count_user_posts_by_type($userid, $post_type = 'post', $date = false) {
    global $wpdb;

    $where = get_posts_by_author_sql($post_type, true, $userid);
    if ($date)
        $where .= " AND  DATE(post_date) >= '$date'";
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts $where");

    return apply_filters('get_usernumposts', $count, $userid);
}

/**
 * pobranie listy ID kategorii gdzie autor ma produkty. funkcja krytycznie pobiera zasoby. Możliwe ogromne zapytania.
 * 
 * @param in $author_id
 * @return string[] ids comma separeted
 */
function user_post_categories($author_id, $post_type = 'product', $tax = 'product_cat') {
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
 * Funkcja pobiera post po tytule
 * 
 * @global std $wpdb
 * @param string $page_title
 * @param string $post_type
 * @param constans $output OBJECT, ARRAY, ID
 * @return mixed bool,object[] 
 */
function get_post_by_title($page_title, $post_type = 'coupon', $output = OBJECT, $author = '') {
    global $wpdb;
    $add = '';
    if ($author)
        $add = 'AND post_author = ' . (int) $author;
    $post = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='$post_type' $add", $page_title));
    if ($output == 'ID')
        return $post;
    if ($post)
        return get_post($post, $output);

    return false;
}

/**
 * funkcja pobiera link do wskazanego Sluga. Pobiera poprzez oszczędne zapytanie
 * @global std $wpdb
 * @param string $page_slug
 * @param constans $output
 * @param string $post_type
 * @return string
 */
function get_page_link_by_slug($page_slug, $output = OBJECT, $post_type = 'page') {
    global $wpdb;
    $page = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s AND post_status = 'publish'", $page_slug, $post_type));
    if ($page)
        return get_permalink($page);
    return '';
}



function top_term_link($class = '', $taxonomy = 'category') {
    global $post;
    $term = get_post_root_term($post->ID, $taxonomy, 'name');
    if ($term instanceof WP_Term)
        printf('<a href="%s" title="%s" $class="%s">%s</a>', get_term_link($term), $term->description, $class, $term->name);
}



