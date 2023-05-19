<?php

namespace App\Helper;

use App\Models\Node;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;

class Helper
{
    // Creates a new HTTP client and sends a request
    public static function httpClient(String $httpRequestMethod, String $local, Node $nodeMaster, Array $bodyContent = null)
    {
        $clientHTTP = new Client(['verify' => false]);

        $URL = 'https://' . $nodeMaster->ip_address . ':' . $nodeMaster->port . '/api/' . $local;

        $headerOptions = [
            'Authorization' => 'Bearer ' . $nodeMaster->token,
        ];

        try {
            if ($bodyContent) {
                $response = $clientHTTP->request($httpRequestMethod, $URL, [
                    'headers' => $headerOptions,
                    'json' => $bodyContent,
                    'timeout' => 3
                ]);
            } else {
                $response = $clientHTTP->request($httpRequestMethod, $URL, [
                    'headers' => $headerOptions,
                    'timeout' => 3
                ]);
            }

            return $response;
        } catch (ConnectException $e) {
            throw new \Exception("Request timeout. Please verify the router connection.", 504); // Gateway Timeout
        } catch (RequestException $e) {
            throw new \Exception("Request malformed. Please verify your request.", 400); // Bad Request
        } catch (ServerException $e) {
            throw new \Exception("Server error.", 500); // Server Error
        }
    }
}
