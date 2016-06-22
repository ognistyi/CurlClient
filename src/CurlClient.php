<?php

namespace Ognistyi\CurlClient;


use Ognistyi\CurlClient\Base\Configurable;
use Ognistyi\CurlClient\Exceptions\JsonConvertException;

class CurlClient
{
    private $_url;
    private $_query;
    private $_headers;
    private $_post;
    private $_rawContent;

    public function setHeaders(array $headers)
    {
        $this->_headers = $headers;
        
        return $this;
    }

    public function setQuery(array $query)
    {
        $this->_query = $query;
        
        return $this;
    }

    public function setPost($post)
    {
        $this->_post = $post;

        return $this;
    }

    /**
     * @param mixed $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->_url = $url;

        return $this;
    }
    
    public function send()
    {
        $curl = curl_init();

        $url = $this->_url;

        if (!empty($this->_query)) {
            $url .= http_build_query($this->_query);
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url ,
            //CURLOPT_USERPWD => env('API_USER') . ':' . env('API_PASSWORD'),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        if (!empty($this->_headers)) {
            $headers = [];
            
            foreach ($this->_headers as $key => $val) {
                $headers[] = "$key: $val";
            }

            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($this->_post)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_post);
        }

        $responseRaw = curl_exec($curl);

        if ($curlErrorNumber = curl_errno($curl)) {
            $curlErrorMessage = curl_strerror($curlErrorNumber);

            throw new ConnectException("#$curlErrorNumber: $curlErrorMessage");
        }

        curl_close($curl);

        $this->_rawContent = $responseRaw;

        return $this;
    }

    /**
     * @return mixed
     */
    public function asRawContent()
    {
        return $this->_rawContent;
    }

    public function asArray($options = null)
    {
        $content = json_decode($this->asRawContent(), true, 512, $options);

        if ($jsonErrNo = json_last_error()) {
            $jsonMessage = json_last_error_msg();
            throw new JsonConvertException("#$jsonErrNo: $jsonMessage");
        }

        return $content;
    }

    public function asObject($object = null, $options = null)
    {
        if (empty($object)) {
            $object = new \stdClass();
        }
        
        return Configurable::configure($object, $this->asArray($options));
    }
}