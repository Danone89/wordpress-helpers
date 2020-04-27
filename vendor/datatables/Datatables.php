<?php

namespace pix\datatables;

/**
 * Register datatable shortcode
 * parameters 
 *  *columns data:label
 *  *ajax <url>
 *  *serverSide <yes|no>
 *  *id <string>
 *  printable
 *  *
 */
class Datatables
{
    const V = 1.424;
    private static $oInstance = null;

    /**
     * Bootstrap
     */
    static function init($url)
    {
        if (!self::$oInstance) {
            self::$oInstance = new Datatables($url);
        }
    }

    static function getInstance()
    {
        if (empty(self::$oInstance)) {
            throw new \Exception('Klasa nie zainicjalizowana.');
        }
        return self::$oInstance;
    }


    function __construct($url)
    {
        $this->assets_dir = $url;
        add_filter('rest_post_collection_params', function ($params) {
            if (isset($params['per_page'])) {
                $params['per_page']['maximum'] = 250;
            }
            $params['search']['type'] = 'array';

            return $params;
        }, 100, 1);

        add_filter('datatable_params', function ($args) {
            $args['nonce'] = wp_create_nonce('wp_rest');
            return $args;
        });
        add_action('wp_enqueue_scripts', function () {
            global $post;
            if (true or is_object($post)) {
                if (true or has_shortcode($post->post_content, 'datatable')) {
                    $this->loadAssets();
                }
            }
        });
        add_shortcode('datatable', array($this, 'generateTable'));
    }
    function generateTable($args = [], $content = '')
    {
        $params = shortcode_atts(array(
            'args' => '',
            'class' => '',
            'model' => '',
            'columns' => '', //comma
            'serverside' => false,
            'ajax' => '',
            'datatype' => '',
            'printable' => false,
            'rowgroup' => '',
            'nosearch' => false,
            'nopaging' => false,
            'id' => 'list',
        ), $args, 'datatable');

        if ($params['columns']) {
            $columns = explode(';', $params['columns']);
            if (is_array($columns)) {
                $columns_arr = [];

                foreach ($columns as $c) {

                    $row = explode(':', $c);
                    if (count($row) == 2)
                        $columns_arr[$row[0]] = $row[1];
                    else
                        $columns_arr[] = $row[0];
                }
                $params['columns'] = $columns_arr;
            }
        }
        $Director = new GenericTable($params['id'], $params);
        if ($params['class']) {
            $Director->buildTableFromModel($params['class']);
        } else {
            $Director->buildTable();
        }
        try {
            $Table = $Director->getTable();
        } catch (\Exception $e) {
            return $e->getMessage();
        }





        return $Table->tableHTML();
    }


    function loadAssets()
    {

        $v = self::V;

        wp_enqueue_script('markjs', $this->assets_dir . 'assets/jquery.mark.min.js', ['datatables'], $v, true);
        wp_enqueue_script('datatables-mark', $this->assets_dir . 'assets/datatables.mark.min.js', ['datatables', 'markjs'], $v, true);
        wp_enqueue_script('datatables-init', $this->assets_dir . 'assets/datatables-init.js', ['jquery', 'datatables'], $v, true);
        wp_add_inline_script('datatables-init', '
        var DataTablesPL = {
        "processing": "Przetwarzanie...",
        "search": "Szukaj:",
        "lengthMenu": "Pokaż _MENU_ pozycji",
        "info": "Pozycje od _START_ do _END_ z _TOTAL_ łącznie",
        "infoEmpty": "Pozycji 0 z 0 dostępnych",
        "infoFiltered": "(filtrowanie spośród _MAX_ dostępnych pozycji)",
        "infoPostFix": "",
        "loadingRecords": "Wczytywanie...",
        "zeroRecords": "Nie znaleziono pasujących pozycji",
        "emptyTable": "Brak danych",
        "colvis": "Kolumny ",
        "paginate": {
            "first": "Pierwsza",
            "previous": "Poprzednia",
            "next": "Następna",
            "last": "Ostatnia"
        },
        "aria": {
            "sortAscending": ": aktywuj, by posortować kolumnę rosnąco",
            "sortDescending": ": aktywuj, by posortować kolumnę malejąco"
        }
    };');
    }
}
