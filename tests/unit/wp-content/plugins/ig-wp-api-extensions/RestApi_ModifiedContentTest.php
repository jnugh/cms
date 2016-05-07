<?php

class RestApi_ModifiedContentTest extends PHPUnit_Framework_TestCase {
    protected $httpRequests;
    protected static $languages = array('de','en','fr','ar','fa');

    //http://vmkrcmar21.informatik.tu-muenchen.de/wordpress_test/augsburg/de/wp-json/extensions/v0/modified_content/pages?since=2015-01-25T09%3A27%3A49%2B0000
    protected function setUp(){
        $host = 'http://localhost/wordpress/';
        $instance = 'augsburg/';
        $representation = 'wp-json/';
        $route = 'extensions/v0/modified_content/posts_and_pages/';

        foreach ($this->languages as &$lang) {
            $url = $host.$instance.$lang.'/'.$representation.$route;
            $req = new HttpRequest($url, HttpRequest::METH_GET);
            $req->addQueryData(array('since' => '2000-01-01T00:00:00Z'));
            $this->httpRequests[] = $req;
        }
    }

    public function test_modified_content_response_code_all_languages() {
        foreach ($this->httpRequests as &$req) {
            $req->send();
            $this->assertEquals(200, $req->getResponseCode());
        }
        $body = $req->getResponseBody();
        //TODO
    }
}