<?php

namespace HandlerCore\components;
/**
 *
 */
class HttpService
{
    private $headers;
    private $url;
    private $mode;
    private $data;
    private $verbose;
    private $last_http_error_code;
    private $cert_path;

    const MODE_JSON = "json";
    const MODE_RAW = "RAW";

    function __construct($url) {
        $this->url = $url;

        $headers = array('Accept', ' */*');

        $this->mode = self::MODE_RAW;
        $this->verbose = false;
    }

    function enableVerbose(): void
    {
        $this->verbose = true;
    }

    function addHeader($name, $val): void
    {
        $this->headers[$name] = $val;
    }

    function addMultipleHeaders($headers_array): void
    {

        if(is_array($headers_array)){
            $this->headers = array_merge($this->headers, $headers_array);
        }
    }

    function setSendModeJSON(): void
    {
        $this->mode = self::MODE_JSON;
        $this->addHeader("Content-Type", "application/json");
    }

    private function buildHeaders(): array
    {
        $all = array();

        if(is_array($this->headers)){
            foreach ($this->headers as $key => $value) {
                $all[] = "$key: $value";
            }
        }
        return $all;
    }

    function setData($data): void
    {
        $this->data = $data;
    }

    function call(): bool|string
    {
        $curl = curl_init();


        if($this->headers){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->buildHeaders());
        }


        if($this->verbose){
            $verbose_file = fopen('php://output', 'w');

            curl_setopt($curl, CURLOPT_VERBOSE, true);
            curl_setopt($curl, CURLOPT_STDERR, $verbose_file);
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);

        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if($this->cert_path){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

            curl_setopt($curl, CURLOPT_CAINFO, $this->cert_path);
        }else{
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
        }


        if($this->verbose){
            echo "cert path: " . $this->cert_path;
        }


        $result = curl_exec($curl);


        $this->last_http_error_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($this->verbose){
            if(is_array($this->data)){
                echo "POST_dATA: ";
                print_r($this->data);
            }else{
                echo "POST_dATA: " . $this->data;
            }


            print_r(curl_getinfo($curl));


            $verboseLog = stream_get_contents($verbose_file);
            echo " LOG::" . $verboseLog;
        }

        if(!$result && $this->verbose){
            echo " ERR::" . curl_errno($curl);
        }


        curl_close($curl);

        return $result;
    }

    public function getLastHttpCode(){
        return $this->last_http_error_code;
    }

    public function setCertFile($abs_path): void
    {
        $this->cert_path = $abs_path;
    }
}
