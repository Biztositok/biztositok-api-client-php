<?php

namespace Biztositok\Api;

/**
 * @author moltam
 */
class Response implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @param array $response The decoded response from the API.
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Returns the decoded response from the API.
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns whether the API request was successful.
     *
     * @return bool
     */
    public function isSuccess()
    {
        return isset($this->response['success']) && $this->response['success'] == 1;
    }

    /**
     * Returns the status message.
     *
     * @return string
     */
    public function getMessage()
    {
        return isset($this->response['message']) ? $this->response['message'] : '';
    }

    /**
     * Returns the errors.
     *
     * @return array An array containing the errors. Each element contains the following elements: field, error_message.
     *
     * If there are no errors, then an empty array is returned.
     */
    public function getErrors()
    {
        return isset($this->response['errors']) ? $this->response['errors'] : [];
    }

    /**
     * Returns the error messages.
     *
     * @return array If there are no errors, then an empty array is returned.
     */
    public function getErrorMessages()
    {
        if (isset($this->response['errors'])) {
            $messages = [];

            foreach ($this->response['errors'] as $row) {
                $messages[] = $row['error_message'];
            }

            return $messages;
        } else {
            return [];
        }
    }

    /**
     * Returns the errors as strings, containing the field and the message.
     *
     * @return array If there are no errors, then an empty array is returned.
     */
    public function getErrorsCombined()
    {
        if (isset($this->response['errors'])) {
            $messages = [];

            foreach ($this->response['errors'] as $row) {
                $messages[] = "[" . $row['field'] . "]: " . $row['error_message'];
            }

            return $messages;
        } else {
            return [];
        }
    }

    /**
     * Returns a value from the response.
     *
     * @param string $key The key of the element. Keys separated with a dot can be passed to access a sub-level element
     * (eg.: user.address.zip to access ['user']['address']['zip']).
     * @param mixed $default [optional]
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return self::getValue($this->response, $key, $default);
    }

    public function offsetExists($offset)
    {
        return isset($this->response[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->response[$offset];
    }

    public function offsetSet($offset, $value)
    {
        // readonly
    }

    public function offsetUnset($offset)
    {
        // readonly
    }

    /**
     * Returns a value from the array.
     *
     * @param array $data The array for lookup.
     * @param string $key The key of the element. Keys separated with a dot can be passed to access a sub-level element
     * (eg.: user.address.zip to access ['user']['address']['zip']).
     * @param mixed $default [optional]
     *
     * @return mixed
     */
    private static function getValue($data, $key, $default = null)
    {
        if (isset($data[$key])) {
            return $data[$key];
        }

        if (strpos($key, '.') !== false) {
            $path = explode('.', $key);
            $curr_key = $path[0];

            if (isset($data[$curr_key])) {
                if (is_array($data[$curr_key])) {
                    return self::getValue($data[$curr_key], implode('.', array_slice($path, 1)), $default);
                } else {
                    return $default;
                }
            } else {
                return $default;
            }
        } else {
            return isset($data[$key]) ? $data[$key] : $default;
        }
    }
}
