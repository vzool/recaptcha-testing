<?php
/**
 * URL PHP Library
 * https://www.cloudonex.com
 * (c) Abdelaziz Elrashed <aeemh.sdn@gmail.com>
 * (c) Razib M <razib@cloudonex.com>
 */
class URL {

    // public $options;
    public $handle; // cURL resource handle.

    // Populated after execution:
    public $body; // Response body.

    public $info; // Response info object.
    public $error; // Response error string.
    public $response_status_lines; // indexed array of raw HTTP response status lines.

    public $decoded_response; // Decoded response body.

    public $base_url;
    public $headers;
    public $user_agent;

    public function __construct($base_url = 'localhost', $headers = [], $user_agent = 'HttpClient 1.0'){
        $this->base_url = $base_url;
        $this->headers = $headers;
        $this->user_agent = $user_agent;
    }

    function __call($method, $args){

        $allowed = [
            'GET', 'POST', 'PUT', 'PATCH',
            'DELETE', 'HEAD', 'OPTIONS',
        ];

        if(preg_grep( "/{$method}/i" , $allowed)){
            return $this->execute($args[0], $method, $args[1] ?? '');
        }
        return false;
    }

    public function execute($path, $method='GET', $parameters=[]){
        $client = clone $this;
        $client->path = $path;
        $client->handle = curl_init();
        $curlopt = [
            CURLOPT_HEADER => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_USERAGENT => $this->user_agent,
            /*CURLOPT_HTTPHEADER => [
                'Authorization: Bearer '.$client->options['api_key']
            ]*/
        ];

        if($this->headers){
            $curlopt[CURLOPT_HTTPHEADER] = array_map(function($value, $key){
                return sprintf("{$key}: {$value}");
            }, $this->headers);
        }

        if(strtoupper($method) == 'POST'){
            $curlopt[CURLOPT_POST] = TRUE;
          //  $curlopt[CURLOPT_POSTFIELDS] = $parameters;

            curl_setopt($client->handle, CURLOPT_POSTFIELDS, http_build_query($parameters));
        }
        elseif(strtoupper($method) != 'GET'){
            $curlopt[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
            curl_setopt($client->handle, CURLOPT_POSTFIELDS, http_build_query($parameters));
        }

        if($client->base_url){
            if($client->path[0] != '/' && substr($client->base_url, -1) != '/')
                $client->path = '/' . $client->path;
            $client->path = $client->base_url . $client->path;
        }
        $curlopt[CURLOPT_URL] = $client->path;

        curl_setopt_array($client->handle, $curlopt);

        $client->parse_response(curl_exec($client->handle));
        $client->info = (object) curl_getinfo($client->handle);
        $client->error = curl_error($client->handle);

        curl_close($client->handle);
        return $client;
    }

    public function parse_response($response){
        $headers = [];
        $this->response_status_lines = [];
        $line = strtok($response, "\n");
        do {
            if(strlen(trim($line)) == 0){
                // Since we tokenize on \n, use the remaining \r to detect empty lines.
                if(count($headers) > 0) break; // Must be the newline after headers, move on to response body
            }
            elseif(strpos($line, 'HTTP') === 0){
                // One or more HTTP status lines
                $this->response_status_lines[] = trim($line);
            }else {
                // Has to be a header
                list($key, $value) = explode(':', $line, 2);
                $key = trim(strtolower(str_replace('-', '_', $key)));
                $value = trim($value);

                if(empty($headers[$key]))
                    $headers[$key] = $value;
                elseif(is_array($headers[$key]))
                    $headers[$key][] = $value;
                else
                    $headers[$key] = [$headers[$key], $value];
            }
        } while($line = strtok("\n"));

        $this->headers = (object) $headers;
        $this->body = strtok("");

        return $this;
    }

    public function toJson(){
        return $this->body;
    }

    public function get(){
        return json_decode($this->body);
    }

    public function toArray(){
        return json_decode($this->body);
    }
}