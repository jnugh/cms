<?php
    include_once 'C:\xampp\htdocs\wordpress\vendor\autoload.php';


    class RestApi_ModifiedContentTest extends PHPUnit_Framework_TestCase {
        protected $httpRequests;
        protected $instances;
        protected $client;
        protected static $languages = array('de','en','fr','ar','fa');

        //http://vmkrcmar21.informatik.tu-muenchen.de/wordpress_test/augsburg/de/wp-json/extensions/v0/modified_content/pages?since=2015-01-25T09%3A27%3A49%2B0000
        public function setUp(){
            $this->instances = wp_get_sites();
            $host = 'http://localhost/wordpress/';
            $representation = 'wp-json/';
            $route = 'extensions/v0/modified_content/posts_and_pages/';
            $this->client = new GuzzleHttp\Client();
            foreach ($this->instances as $instance) {
                $requests = array();
                foreach ($this->languages as &$language) {
                    $blog_name = get_blog_details($instance)->blogname;
                    $url = $host.$blog_name.'/'.$language.'/'.$representation.$route.'?2000-01-01T00:00:00Z';
                    $req = $this->client->request(HttpRequest::METH_GET,$url);
                    $requests[] = $req;
                }
                $this->httpRequests[get_blog_details($instance)->blogname] = $requests;
            }
        }

        public function test_modified_content_response_code_all_languages_all_instances() {
            /*for ($i = 0; $i < count($this->httpRequests); $i++) {
                for ($l = 0; $l < count($myArrays[$i]); $l++) {
                    print $myArrays[$i][$l];
                    print "<br/>";
                };
            };

            $body = $req->getResponseBody();
            */
            print_r($this->httpRequests);

        }
    }

    $test = new RestApi_ModifiedContentTest;
    $test->setUp();
    $test.test_modified_content_response_code_all_languages_all_instances();