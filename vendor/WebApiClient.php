<?php
//helper class
namespace pix\helpers;

//@todo refactor to WpWebApi and user wp_remote_get
class WebApiClient
{
    protected static $url = '';

    function __construct()
    {

        self::$url = get_option('bss_webapi_url', 'https://webapi.bsskierniewice.pl/');
        $this->init();
    }
    function init()
    { }


    /**
     * Pobranie wyników zapytania
     *
     * @param [type] $query
     * @param string $endpoint
     * @return void
     */
    public function get_results($query, $endpoint = '')
    {
        $page = 0;
        $pages = 0;
        $query = urldecode($query);
        $single = false;
        do {
            $page++;
            $url = $this->getUrl($endpoint, $query, $page);
            $raw = file_get_contents($url, false);
            if (strpos($http_response_header[0], '200 OK')) {
                if ($pages == 0) {
                    foreach ($http_response_header as $header) {
                        if (strpos($header, 'X-Pagination-Page-Count:') === 0) {
                            $pages = intval(trim(str_replace('X-Pagination-Page-Count:', '', $header)));
                            break;
                        }
                    }
                }
                if ($pages === 0) {
                    yield json_decode($raw, true);
                } else {
                    foreach (json_decode($raw, true) as $d)
                        yield $d;
                }
            } else {
                throw new \Exception('Błąd pobierania danych');
            }
        } while ($pages > $page);
    }
    function addFilterArgs()
    { }
    protected function getUrl($endpoint, $query, $page = 1)
    {
        return sprintf('%s?%s&page=%s', self::$url . $endpoint, $query, $page);
    }
}
