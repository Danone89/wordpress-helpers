<?php
/**
 * Allow pods to load form view from active template. You need to put file in pods/form.php
 */

add_filter('pods_view_alt_view',function($view,$org_view, $data, $expires, $cache_mode){

    $template_file = locate_template('pods/form');
    if($org_view == PODS_DIR.'ui/front/form.php' && $template_file ){
        
        $view = PodsView::view($template_file,$data,$expires,$cache_mode);

    }

    return $view;


},10,5);
