<?php declare (strict_types=1);

namespace Sabre\HTTP;

use Sabre\Uri;

/**
 * The Request class represents a single HTTP request.
 *
 * You can either simply construct the object from scratch, or if you need
 * access to the current HTTP request, use Sapi::getRequest.
 *
 * @copyright Copyright (C) fruux GmbH (https://fruux.com/)
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Request extends Message implements RequestInterface {

    /**
     * HTTP Method
     *
     * @var string
     */
    protected $method;

    /**
     * Request Url
     *
     * @var string
     */
    protected $url;

    /**
     * Creates the request object
     *
     * @param resource|callable|string $body
     */
    function __construct(string $method = null, string $url = null, array $headers = null, $body = null) {

        if (!is_null($method))      $this->setMethod($method);
        if (!is_null($url))         $this->setUrl($url);
        if (!is_null($headers))     $this->setHeaders($headers);
        if (!is_null($body))        $this->setBody($body);

    }

    /**
     * Returns the current HTTP method
     */
    function getMethod() : string {

        return $this->method;

    }

    /**
     * Sets the HTTP method
     *
     * @return void
     */
    function setMethod(string $method) {

        $this->method = $method;

    }

    /**
     * Returns the request url.
     */
    function getUrl() : string {

        return $this->url;

    }

    /**
     * Sets the request url.
     *
     * @return void
     */
    function setUrl(string $url) {

        $this->url = $url;

    }

    /**
     * Returns the list of query parameters.
     *
     * This is equivalent to PHP's $_GET superglobal.
     */
    function getQueryParameters() : array {

        $url = $this->getUrl();
        if (($index = strpos($url, '?')) === false) {
            return [];
        } else {
            parse_str(substr($url, $index + 1), $queryParams);
            return $queryParams;
        }

    }

    /**
     * Sets the absolute url.
     *
     * @return void
     */
    function setAbsoluteUrl(string $url) {

        $this->absoluteUrl = $url;

    }

    /**
     * Returns the absolute url.
     */
    function getAbsoluteUrl() : string {

        return $this->absoluteUrl;

    }

    /**
     * Base url
     *
     * @var string
     */
    protected $baseUrl = '/';

    /**
     * Sets a base url.
     *
     * This url is used for relative path calculations.
     *
     * @return void
     */
    function setBaseUrl(string $url) {

        $this->baseUrl = $url;

    }

    /**
     * Returns the current base url.
     */
    function getBaseUrl() : string {

        return $this->baseUrl;

    }

    /**
     * Returns the relative path.
     *
     * This is being calculated using the base url. This path will not start
     * with a slash, so it will always return something like
     * 'example/path.html'.
     *
     * If the full path is equal to the base url, this method will return an
     * empty string.
     *
     * This method will also urldecode the path, and if the url was incoded as
     * ISO-8859-1, it will convert it to UTF-8.
     *
     * If the path is outside of the base url, a LogicException will be thrown.
     */
    function getPath() : string {

        // Removing duplicated slashes.
        $uri = str_replace('//', '/', $this->getUrl());

        $uri = Uri\normalize($uri);
        $baseUri = Uri\normalize($this->getBaseUrl());

        if (strpos($uri, $baseUri) === 0) {

            // We're not interested in the query part (everything after the ?).
            list($uri) = explode('?', $uri);
            return trim(decodePath(substr($uri, strlen($baseUri))), '/');

        }
        // A special case, if the baseUri was accessed without a trailing
        // slash, we'll accept it as well.
        elseif ($uri . '/' === $baseUri) {

            return '';

        }

        throw new \LogicException('Requested uri (' . $this->getUrl() . ') is out of base uri (' . $this->getBaseUrl() . ')');
    }

    /**
     * Equivalent of PHP's $_POST.
     *
     * @var array
     */
    protected $postData = [];

    /**
     * Sets the post data.
     *
     * This is equivalent to PHP's $_POST superglobal.
     *
     * This would not have been needed, if POST data was accessible as
     * php://input, but unfortunately we need to special case it.
     *
     * @return void
     */
    function setPostData(array $postData) {

        $this->postData = $postData;

    }

    /**
     * Returns the POST data.
     *
     * This is equivalent to PHP's $_POST superglobal.
     */
    function getPostData() : array {

        return $this->postData;

    }

    /**
     * An array containing the raw _SERVER array.
     *
     * @var array
     */
    protected $rawServerData;

    /**
     * Returns an item from the _SERVER array.
     *
     * If the value does not exist in the array, null is returned.
     *
     * @return string|null
     */
    function getRawServerValue(string $valueName) {

        if (isset($this->rawServerData[$valueName])) {
            return $this->rawServerData[$valueName];
        }

    }

    /**
     * Sets the _SERVER array.
     *
     * @return void
     */
    function setRawServerData(array $data) {

        $this->rawServerData = $data;

    }

    /**
     * Serializes the request object as a string.
     *
     * This is useful for debugging purposes.
     */
    function __toString() : string {

        $out = $this->getMethod() . ' ' . $this->getUrl() . ' HTTP/' . $this->getHTTPVersion() . "\r\n";

        foreach ($this->getHeaders() as $key => $value) {
            foreach ($value as $v) {
                if ($key === 'Authorization') {
                    list($v) = explode(' ', $v, 2);
                    $v .= ' REDACTED';
                }
                $out .= $key . ": " . $v . "\r\n";
            }
        }
        $out .= "\r\n";
        $out .= $this->getBodyAsString();

        return $out;

    }

}
