<?php

namespace Includes\MailChimp\API;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * MailChimp API
 *
 * Includes MailChimp API methods.
 *
 * @since 6.5.0
 */
class QuoteupMailChimpAPI
{
    /**
     * The reference to *Singleton* instance of this class.
     *
     * @var Singleton 
     */
    private static $instance;
   
    /**
     * Contains data center value.
     *
     * @var string
     */
    protected $dataCenter;

    /**
     * API Key.
     *
     * @var string
     */
    protected $apiKey = null;

    /**
     * Version for API.
     *
     * @var string
     */
    public $version;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance($apiKey)
    {
        if (null === static::$instance) {
            static::$instance = new static($apiKey);
        }
        
        return static::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct($apiKey)
    {
        $this->version     = '3.0';
        $this->dataCenter = 'us2';
        $this->setApiKey($apiKey);
    }

    /**
     * Set API key and data center.
     *
     * @since 6.5.0
     *
     * @param  string  $key  Entire API Key.
     *
     * @return  void
     */
    public function setApiKey($key)
    {
        $parts = str_getcsv($key, '-');

        if (2 == count($parts)) {
            $this->dataCenter = $parts[1];
        }
        $this->apiKey = $parts[0];
    }

    /**
     * Get the user details from the MailChimp service.
     *
     * @since 6.5.0
     *
     * @param  string  $listId  List Id.
     * @param  string  $email  Email address of the user.
     *
     * @return array|int|null  Return array containing the response data. If
     *                         there is any error, int value is returned. If
     *                         there is no response and no error, null is returned.
     */
    public function member($listId, $email)
    {
        $hash = md5(strtolower(trim($email)));
        return $this->get("lists/$listId/members/$hash", array());
    }

    /**
     * Create or update the user in the subscription list.
     *
     * @since 6.5.0
     *
     * @param  string  $listId  List Id.
     * @param  string  $email  Email address of the user.
     * @param  bool|string  $subscribed  The subscription status to be set 
     *                                   for the user.
     * @param  array  $mergeFields  An individual merge var and value for a member.
     * @param  array  $listInterests  ID of the interest in question.
     *
     * @return array|int|null  Return array containing the response data. If
     *                         there is any error, int value is returned. If
     *                         there is no response and no error, null is returned.
     */
    public function updateOrCreate($listId, $email, $subscribed = true, $mergeFields = array(), $listInterests = array(), $language = null)
    {
        $hash = md5(strtolower(trim($email)));

        if ($subscribed === true) {
            $status = 'subscribed';
            $statusIfNew = 'subscribed';
        } elseif ($subscribed === false) {
            $status = 'unsubscribed';
            $statusIfNew = 'pending';
        } elseif ($subscribed === null) {
            $status = 'cleaned';
            $statusIfNew = 'subscribed';
        } else {
            $status = $subscribed;
            $statusIfNew = 'pending';
        }

        $data = array(
            'email_address' => $email,
            'status' => $status,
            'status_if_new' => $statusIfNew,
            'merge_fields' => $mergeFields,
            'interests' => $listInterests,
            'language' => $language
        );

        if (empty($data['merge_fields'])) {
            unset($data['merge_fields']);
        }

        if (empty($data['interests'])) {
            unset($data['interests']);
        }
        
        if (empty($data['language'])) {
            unset($data['language']);
        }
        
        return $this->put("lists/$listId/members/$hash", $data);
    }

    /**
     * Get the data from the MailChimp service.
     *
     * @since 6.5.0
     *
     * @param  string  $url  URL for API to retrieve data.
     * @param  array  $params  Parameters to be appeneded to the URL.
     *
     * @return  array|int|null  Return array containing the response data. If
     *                          there is any error, int value is returned. If
     *                          there is no response and no error, null is returned.
     */
    protected function get($url, $params = null)
    {
        $curl = curl_init();

        $options = $this->applyCurlOptions('GET', $url, $params);

        curl_setopt_array($curl, $options);

        return $this->processCurlResponse($curl);
    }

    /**
     * Put (add/ update) the data in the MailChimp service.
     *
     * @since 6.5.0
     *
     * @param  string  $url  URL for API to push data.
     * @param  array  $body  Data to be passed along with API request.
     *
     * @return array|int|null  Return array containing the response data. If
     *                         there is any error, int value is returned. If
     *                         there is no response and no error, null is returned.
     */
    protected function put($url, $body)
    {
        $curl = curl_init();

        $json = json_encode($body);

        $options = $this->applyCurlOptions('PUT', $url, array(), array(
            'Expect:',
            'Content-Length: '.strlen($json),
        ));

        $options[CURLOPT_POSTFIELDS] = $json;

        curl_setopt_array($curl, $options);

        return $this->processCurlResponse($curl);
    }

    /**
     * Apply curl options.
     *
     * @since 6.5.0
     *
     * @param  string  $method  Method for the API request. Possible values
     *                          put, post, etc.
     * @param  string  $url  URL for API to push data.
     * @param  array  $params  Parameters to be appeneded to the URL.
     * @param  array  $headers  Header of the request
     *
     * @return  array  Return array containing the CURL options.
     */
    protected function applyCurlOptions($method, $url, $params = array(), $headers = array())
    {
        $env = mailchimp_environment_variables();

        $curlOptions = array(
            CURLOPT_USERPWD => "mailchimp:{$this->apiKey}",
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_URL => $this->url($url, $params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_HTTPHEADER => array_merge(array(
                'content-type: application/json',
                'accept: application/json',
                "user-agent: MailChimp for WooCommerce/{$env->version}; PHP/{$env->php_version}; WordPress/{$env->wp_version}; Woo/{$env->wc_version};",
            ), $headers)
        );

        // if we have a dedicated IP address, and have set a configuration for it, we'll use it here.
        if (defined('MAILCHIMP_USE_OUTBOUND_IP')) {
            $curlOptions[CURLOPT_INTERFACE] = MAILCHIMP_USE_OUTBOUND_IP;
        }

        // if we need to define a specific http version being used for curl requests, we can override this here.
        if (defined('MAILCHIMP_USE_HTTP_VERSION')) {
            $curlOptions[CURLOPT_HTTP_VERSION] = MAILCHIMP_USE_HTTP_VERSION;
        }

        return $curlOptions;
    }

    /**
     * Send API request and receive the response.
     *
     * @return 6.5.0
     *
     * @param  object  $curl  CurlHandle instance.
     *
     * @return  array|int|null  Return array containing the response data. If
     *                          there is any error, int value is returned. If
     *                          there is no response and no error, null is returned.
     */
    protected function processCurlResponse($curl)
    {
        $response = curl_exec($curl);

        $err = curl_error($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ($err) {
            return -1; // CURL Error
        }

        $data = json_decode($response, true);

        $httpCode = !empty($info) && isset($info['http_code']) ? $info['http_code'] : -1;
        $called_url = !empty($info) && isset($info['url']) ? $info['url'] : 'none';

        // let's block these from doing anything below because the API seems to be having trouble.
        if ($httpCode <= 99) {
            return -2; // API is failing
        }

        if ($httpCode >= 200 && $httpCode <= 400) {
            return $data;
        }

        return null;
    }

    /**
     * Return the URL for the API request.
     *
     * @since 6.5.0
     *
     * @param  string  $extra  Extra endpoints to be appended to the URL.
     * @param  null|array  $params  Parameters to be appeneded to the URL.
     *
     * @return  string  Return string containing the URL for the API request.
     */
    protected function url($extra = '', $params = null)
    {
        $url = "https://{$this->dataCenter}.api.mailchimp.com/{$this->version}/";

        if (!empty($extra)) {
            $url .= $extra;
        }

        if (!empty($params)) {
            $url .= '?'.(is_array($params) ? http_build_query($params) : $params);
        }

        return $url;
    }
}
