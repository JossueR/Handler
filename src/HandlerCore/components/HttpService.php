<?php

namespace HandlerCore\components;
/**
 * Clase que permite realizar llamadas a APIs y obtener respuestas.
 */
class HttpService
{

    /**
     * @var array $headers Cabeceras HTTP de la solicitud.
     */
    private $headers;

    /**
     * @var string $url URL a la que se realizará la solicitud.
     */
    private $url;

    /**
     * @var string $mode Modo de envío de datos (json o RAW).
     */
    private $mode;

    /**
     * @var mixed $data Datos a enviar en la solicitud.
     */
    private $data;

    /**
     * @var bool $verbose Indicador de modo detallado (verbose) de la solicitud.
     */
    private $verbose;

    /**
     * @var int|null Último código de error HTTP recibido.
     */
    private $last_http_error_code;

    /**
     * @var string|null Ruta absoluta al archivo de certificado.
     */
    private $cert_path;

    /**
     * Modo de envío JSON para la solicitud.
     */
    const MODE_JSON = "json";

    /**
     * Modo de envío RAW para la solicitud.
     */
    const MODE_RAW = "RAW";

    /**
     * Constructor de la clase HttpService.
     *
     * @param string $url URL a la que se realizarán las solicitudes.
     */
    function __construct($url) {
        $this->url = $url;

        $headers = array('Accept', ' */*');

        $this->mode = self::MODE_RAW;
        $this->verbose = false;
    }

    /**
     * Habilita el modo detallado (verbose) de la solicitud.
     *
     * @return void
     */
    function enableVerbose(): void
    {
        $this->verbose = true;
    }

    /**
     * Agrega una cabecera a la solicitud.
     *
     * @param string $name Nombre de la cabecera.
     * @param mixed $val Valor de la cabecera.
     * @return void
     */
    function addHeader($name, $val): void
    {
        $this->headers[$name] = $val;
    }

    /**
     * Agrega múltiples cabeceras a la solicitud.
     *
     * @param array $headers_array Arreglo de cabeceras.
     * @return void
     */
    function addMultipleHeaders(array $headers_array): void
    {

        if(is_array($headers_array)){
            $this->headers = array_merge($this->headers, $headers_array);
        }
    }

    /**
     * Establece el modo de envío JSON y agrega la cabecera Content-Type.
     *
     * @return void
     */
    function setSendModeJSON(): void
    {
        $this->mode = self::MODE_JSON;
        $this->addHeader("Content-Type", "application/json");
    }

    /**
     * Construye un arreglo de cabeceras para la solicitud HTTP.
     *
     * @return array Arreglo de cabeceras construido a partir de las cabeceras definidas.
     */
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

    /**
     * Establece los datos a enviar en la solicitud.
     *
     * @param mixed $data Datos a enviar en la solicitud.
     * @return void
     */
    function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * Realiza una llamada HTTP utilizando cURL y devuelve la respuesta recibida.
     *
     * @return bool|string La respuesta obtenida de la llamada HTTP o false en caso de error.
     */
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

    /**
     * Obtiene el último código de estado HTTP recibido en una llamada cURL.
     *
     * @return int|null El código de estado HTTP de la última llamada cURL.
     */
    public function getLastHttpCode(): ?int
    {
        return $this->last_http_error_code;
    }

    /**
     * Establece la ubicación del archivo de certificado SSL para la conexión cURL.
     *
     * @param string $abs_path Ruta absoluta al archivo de certificado SSL.
     * @return void
     */
    public function setCertFile(string $abs_path): void
    {
        $this->cert_path = $abs_path;
    }
}
