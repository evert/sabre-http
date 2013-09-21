<?php

namespace Sabre\HTTP;

class ClientTest extends \PHPUnit_Framework_TestCase {

    protected $client;

    function testSendGet() {

        $client = new ClientMock();

        $client->on('curl', function($settings, &$result) {

            $this->assertEquals([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HEADER => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_POSTREDIR => 0,
                CURLOPT_HTTPHEADER => ['X-Foo: bar'],
                CURLOPT_URL => 'http://example.org/',
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => null,
                CURLOPT_PUT => false,
                CURLOPT_ENCODING => 'identity',
            ], $settings);

            $returnHeaders = [
                "HTTP/1.1 200 OK",
                "X-Zim: Gir",
            ];

            $returnHeaders = implode("\r\n", $returnHeaders) . "\r\n\r\n";

            $returnBody = "hi!";

            $result = [
                $returnHeaders . $returnBody,
                [
                    'header_size' => strlen($returnHeaders),
                    'http_code' => 200,
                ],
                0,
                '',
            ];


        });

        $client->addCurlSetting(CURLOPT_POSTREDIR, 0);

        $request = new Request('GET','http://example.org/', ['X-Foo' => 'bar']);
        $response = $client->send($request);

        $this->assertEquals(
            '200 OK',
            $response->getStatus()
        );

        $this->assertEquals(
            'Gir',
            $response->getHeader('X-Zim')
        );

        $this->assertEquals(
            'hi!',
            $response->getBody(Message::BODY_STRING)
        );

    }

    function testSendHead() {

        $client = new ClientMock();

        $client->on('curl', function($settings, &$result) {

            $this->assertEquals([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HEADER => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_POSTREDIR => 3,
                CURLOPT_NOBODY => true,
                CURLOPT_CUSTOMREQUEST => 'HEAD',
                CURLOPT_HTTPHEADER => ['X-Foo: bar'],
                CURLOPT_URL => 'http://example.org/',
                CURLOPT_POSTFIELDS => null,
                CURLOPT_PUT => false,
                CURLOPT_ENCODING => 'identity',
            ], $settings);

            $returnHeaders = [
                "HTTP/1.1 200 OK",
                "X-Zim: Gir",
            ];

            $returnHeaders = implode("\r\n", $returnHeaders) . "\r\n\r\n";

            $returnBody = "hi!";

            $result = [
                $returnHeaders . $returnBody,
                [
                    'header_size' => strlen($returnHeaders),
                    'http_code' => 200,
                ],
                0,
                '',
            ];


        });
        $request = new Request('HEAD','http://example.org/', ['X-Foo' => 'bar']);
        $response = $client->send($request);

        $this->assertEquals(
            '200 OK',
            $response->getStatus()
        );

        $this->assertEquals(
            'Gir',
            $response->getHeader('X-Zim')
        );

        $this->assertEquals(
            'hi!',
            $response->getBody(Message::BODY_STRING)
        );

    }

    function testSendPUTStream() {

        $client = new ClientMock();

        $h = null;

        $client->on('curl', function($settings, &$result) use (&$h) {

            $this->assertEquals([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HEADER => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_POSTREDIR => 3,
                CURLOPT_PUT => true,
                CURLOPT_INFILE => $h,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_HTTPHEADER => ['X-Foo: bar'],
                CURLOPT_URL => 'http://example.org/',
                CURLOPT_ENCODING => 'identity',
            ], $settings);

            $returnHeaders = [
                "HTTP/1.1 200 OK",
                "X-Zim: Gir",
            ];

            $returnHeaders = implode("\r\n", $returnHeaders) . "\r\n\r\n";

            $returnBody = "hi!";

            $result = [
                $returnHeaders . $returnBody,
                [
                    'header_size' => strlen($returnHeaders),
                    'http_code' => 200,
                ],
                0,
                '',
            ];


        });

        $h = fopen('php://memory', 'r+');
        fwrite($h, 'booh');

        $request = new Request('PUT','http://example.org/', ['X-Foo' => 'bar'], $h);
        $response = $client->send($request);

        $this->assertEquals(
            '200 OK',
            $response->getStatus()
        );

        $this->assertEquals(
            'Gir',
            $response->getHeader('X-Zim')
        );

        $this->assertEquals(
            'hi!',
            $response->getBody(Message::BODY_STRING)
        );

    }

    function testSendPUTString() {

        $client = new ClientMock();

        $client->on('curl', function($settings, &$result) {

            $this->assertEquals([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HEADER => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_POSTREDIR => 3,
                CURLOPT_POSTFIELDS => 'boo',
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_HTTPHEADER => ['X-Foo: bar'],
                CURLOPT_URL => 'http://example.org/',
                CURLOPT_ENCODING => 'identity',
            ], $settings);

            $returnHeaders = [
                "HTTP/1.1 200 OK",
                "X-Zim: Gir",
            ];

            $returnHeaders = implode("\r\n", $returnHeaders) . "\r\n\r\n";

            $returnBody = "hi!";

            $result = [
                $returnHeaders . $returnBody,
                [
                    'header_size' => strlen($returnHeaders),
                    'http_code' => 200,
                ],
                0,
                '',
            ];


        });

        $request = new Request('PUT','http://example.org/', ['X-Foo' => 'bar'], 'boo');
        $response = $client->send($request);

        $this->assertEquals(
            '200 OK',
            $response->getStatus()
        );

        $this->assertEquals(
            'Gir',
            $response->getHeader('X-Zim')
        );

        $this->assertEquals(
            'hi!',
            $response->getBody(Message::BODY_STRING)
        );

    }

    /**
     * @expectedException \Sabre\HTTP\ClientException
     */
    function testCurlError() {

        $client = new ClientMock();

        $client->on('curl', function($settings, &$result) {

            $result = [
                '',
                [
                    'header_size' => 0,
                    'http_code' => 200,
                ],
                1,
                'Error',
            ];


        });

        $request = new Request('PUT','http://example.org/', ['X-Foo' => 'bar'], 'boo');
        $client->send($request);

    }

    function testCurlErrorRetry() {

        $client = new ClientMock();

        $client->on('curl', function($settings, &$result) {

            $result = [
                '',
                [
                    'header_size' => 0,
                    'http_code' => 200,
                ],
                1,
                'Error',
            ];

        });

        $hits = 0;

        $client->on('exception', function(Request $request, ClientException $e, &$retry, $retryCount) use (&$hits) {

            $hits++;
            if ($retryCount < 1) {
                $retry = true;
            }

        });

        $caught = false;
        try {
            $request = new Request('PUT','http://example.org/', ['X-Foo' => 'bar'], 'boo');
            $client->send($request);
        } catch (ClientException $e) {
            $caught = true;
        }

        $this->assertTrue($caught);
        $this->assertEquals(2, $hits);

    }

    function testSendRetryAfterError() {

        $client = new ClientMock();

        $foo = 0;

        $client->on('curl', function($settings, &$result) use (&$foo) {

            $foo++;
            if ($foo === 1) {
                $returnCode = '400 Bad request';
            } else {
                $returnCode = '200 OK';
            }

            $returnHeaders = [
                "HTTP/1.1 " . $returnCode,
                "X-Zim: Gir",
            ];

            $returnHeaders = implode("\r\n", $returnHeaders) . "\r\n\r\n";

            $returnBody = "hi!";

            $result = [
                $returnHeaders . $returnBody,
                [
                    'header_size' => strlen($returnHeaders),
                    'http_code' => (int)$returnCode,
                ],
                0,
                '',
            ];


        });

        $request = new Request('PUT','http://example.org/', ['X-Foo' => 'bar'], 'boo');
        $response = $client->send($request);

        $this->assertEquals(
            '400 Bad request',
            $response->getStatus()
        );
        $this->assertEquals(1, $foo);

        // Doing this again, but retrying this time.
        $foo = 0;
        $client->on('error:400', function($request, $response, &$retry, $retryCount) {
            if ($retryCount === 0) $retry = true;
        });

        $response = $client->send($request);

        $this->assertEquals(
            '200 OK',
            $response->getStatus()
        );
        $this->assertEquals(2, $foo);

    }

    function testThrowExceptions() {

        $client = new ClientMock();

        $foo = 0;

        $client->on('curl', function($settings, &$result) use (&$foo) {

            $foo++;
            if ($foo === 1) {
                $returnCode = '400 Bad request';
            } else {
                $returnCode = '200 OK';
            }

            $returnHeaders = [
                "HTTP/1.1 " . $returnCode,
                "X-Zim: Gir",
            ];

            $returnHeaders = implode("\r\n", $returnHeaders) . "\r\n\r\n";

            $returnBody = "hi!";

            $result = [
                $returnHeaders . $returnBody,
                [
                    'header_size' => strlen($returnHeaders),
                    'http_code' => (int)$returnCode,
                ],
                0,
                '',
            ];


        });

        $client->setThrowExceptions(true);

        try {
            $request = new Request('PUT','http://example.org/', ['X-Foo' => 'bar'], 'boo');
            $response = $client->send($request);
            $this->fail('We expected an exception to be thrown, so this should be unreachable');
        } catch (ClientHttpException $e) {

            $this->assertEquals('400 Bad request', $e->getHttpStatus());
            $this->assertEquals('Gir', $e->getResponse()->getHeader('X-Zim'));

        }


    }

}

class ClientMock extends Client {
    public $curlSettings=[];
    
    public function setCurlSetting($optName,$value){
       $this->curlSettings[$optName] = $value;
    }
    
    public function setCurlSettings(array $arrayOfSettings){
        $this->curlSettings += $arrayOfSettings;
    }
    
    function curlRequest() {
        $this->emit('curl', [$this->curlSettings, &$result]);
        return $result;
    }

}
