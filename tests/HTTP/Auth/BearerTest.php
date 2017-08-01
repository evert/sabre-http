<?php declare (strict_types=1);

namespace Sabre\HTTP\Auth;

use PHPUnit\Framework\TestCase;
use Sabre\HTTP\Request;
use Sabre\HTTP\Response;

class BearerTest extends TestCase {

    function testGetToken() {

        $request = new Request('GET', '/', [
            'Authorization' => 'Bearer 12345'
        ]);

        $bearer = new Bearer('Dagger', $request, new Response());

        $this->assertEquals(
            '12345',
            $bearer->getToken()
        );

    }

    function testGetCredentialsNoheader() {

        $request = new Request('GET', '/', []);
        $bearer = new Bearer('Dagger', $request, new Response());

        $this->assertNull($bearer->getToken());

    }

    function testGetCredentialsNotBearer() {

        $request = new Request('GET', '/', [
            'Authorization' => 'QBearer 12345'
        ]);
        $bearer = new Bearer('Dagger', $request, new Response());

        $this->assertNull($bearer->getToken());

    }

    function testRequireLogin() {

        $response = new Response();
        $request = new Request('GET', '/');
        $bearer = new Bearer('Dagger', $request, $response);

        $bearer->requireLogin();

        $this->assertEquals('Bearer realm="Dagger"', $response->getHeader('WWW-Authenticate'));
        $this->assertEquals(401, $response->getStatus());

    }

}
