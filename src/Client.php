<?php

namespace Biztositok\Api;

/**
 * Client to communicate with the site API.
 *
 * @author moltam
 */
class Client
{
    /**
     * Default options for curl.
     *
     * @var array
     */
    protected $curlOptions = [
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'biztositok-php-1.0',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 2,
    ];

    /**
     * @var string
     */
    protected $apiEndpoint;

    /**
     * The username of the Site.
     *
     * @var string
     */
    protected $username;

    /**
     * The password of the Site.
     *
     * @var string
     */
    protected $password;

    /**
     * Initialize an API client.
     *
     * @param array $config The config
     */
    public function __construct($config)
    {
        $this->checkRequirements();

        if (isset($config['api_endpoint'])) {
            $this->setApiEndpoint($config['api_endpoint']);
        }

        if (isset($config['username'])) {
            $this->setUsername($config['username']);
        }

        if (isset($config['password'])) {
            $this->setPassword($config['password']);
        }
    }

    /**
     * Sets the api endpoint url.
     *
     * @param string $endpoint An url, eg.: http:://example.com
     */
    public function setApiEndpoint($endpoint)
    {
        $this->apiEndpoint = $endpoint;
    }

    /**
     * Sets the Site username.
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Sets the Site password.
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Sets the curl options. You can override the default options.
     *
     * @param array $options Array of key-value pairs.
     */
    public function setCurlOptions($options)
    {
        foreach ($options as $key => $val) {
            $this->curlOptions[$key] = $val;
        }
    }

    /**
     * Returns the API endpoint.
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->apiEndpoint;
    }

    /**
     * Returns the Site username.
     *
     * @return string The username.
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the Site password.
     *
     * @return string The password.
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Invokes an API function.
     *
     * @param string $path The path of the invoked function.
     * @param array $params [optional]
     * <p>The query/post adata.</p>
     *
     * @return Response The response object. It can be accessed as an array.
     *
     * @throws ApiException
     */
    public function api($path, $params = [])
    {
        $ch = $this->initCurl($path, $params);

        $response = curl_exec($ch);

        if ($response === false) {
            $e = new ApiException(sprintf('cURL error: %s', curl_error($ch)), curl_errno($ch));
            curl_close($ch);

            throw $e;
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($decoded === null) {
            throw new ApiException('Invalid API response (json decode error)');
        }

        return new Response($decoded);
    }

    /**
     * @throws ApiException
     */
    protected function checkRequirements()
    {
        if (!function_exists('curl_init')) {
            throw new ApiException('Biztositok API Client needs the CURL PHP extension.');
        }
        if (!function_exists('json_encode')) {
            throw new ApiException('Biztositok API Client needs the JSON PHP extension.');
        }
    }

    /**
     * Creates a new resource to communicate with the API.
     *
     * @param string $path Path of the invoked function.
     * @param array $params The post data.
     *
     * @return resource The cURL handle.
     *
     * @throws ApiException
     */
    protected function initCurl($path, $params)
    {
        $ch = curl_init();

        if (!is_resource($ch)) {
            throw new ApiException("cURL error: can't create resource.", 1);
        }

        $opts = $this->curlOptions;
        $opts[CURLOPT_URL] = $this->getURL($path);
        $opts[CURLOPT_POSTFIELDS] = http_build_query($this->prepareRequestParams($params), null, '&');

        // Disable the 'Expect: 100-continue' behaviour. This causes CURL to wait
        // for 2 seconds if the server does not support this header.
        if (isset($opts[CURLOPT_HTTPHEADER])) {
            $existing_headers = $opts[CURLOPT_HTTPHEADER];
            $existing_headers[] = 'Expect:';
            $opts[CURLOPT_HTTPHEADER] = $existing_headers;
        } else {
            $opts[CURLOPT_HTTPHEADER] = ['Expect:'];
        }

        curl_setopt_array($ch, $opts);

        return $ch;
    }

    /**
     * Prepares the parameters for the API request.
     *
     * @param array $params The query/post data.
     *
     * @return array The prepared parameters.
     */
    protected function prepareRequestParams($params)
    {
        $params['auth'] = [
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
        ];

        // json_encode all params values that are not strings
        foreach ($params as $key => $value) {
            if (!is_string($value)) {
                $params[$key] = json_encode($value);
            }
        }

        return $params;
    }

    /**
     * Builds the API endpoint URL.
     *
     * @param string $path The path of the API function.
     * @param array $params The query/post data.
     *
     * @return string The API url, extended with the parameters.
     */
    protected function getURL($path, $params = [])
    {
        $url = $this->apiEndpoint . (substr($this->apiEndpoint, -1) === '/' ? '' : '/') . 'api/run/';

        if (!empty($path)) {
            if (substr($path, 0, 1) === '/') {
                $path = substr($path, 1);
            }

            $url .= $path;
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params, null, '&');
        }

        return $url;
    }
}
