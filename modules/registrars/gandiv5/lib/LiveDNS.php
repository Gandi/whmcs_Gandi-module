<?php

namespace WHMCS\Module\Registrar\Gandiv5;

class LiveDNS
{
    const ENDPOINT = 'https://api.gandi.net/v5/livedns/';
    private $apiKey;


    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /*
    *
    * Send request.
    *
    * @param string $url
    * @param string $method
    * @param string $url
    * @param array $post
    * @param integer $timeout
    * @return array
    *
    */

    private function sendRequest(string $url, $method="GET", array $post=[], $timeout=30)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
             CURLOPT_PORT => '0',
             CURLOPT_URL => $url,
             CURLOPT_RETURNTRANSFER => true,
             CURLOPT_ENCODING => "",
             CURLOPT_MAXREDIRS => 10,
             CURLOPT_TIMEOUT => $timeout,
             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
             CURLOPT_CUSTOMREQUEST => $method,
             CURLOPT_HTTPHEADER => array(
                 "authorization: Apikey {$this->apiKey}",
                 "content-type: application/json"
             ),
             CURLOPT_USERAGENT => 'WHMCS/1.3',
             CURLINFO_HEADER_OUT => true
         ));
        if ($method == "POST") {
            curl_setopt_array($curl, [ CURLOPT_CUSTOMREQUEST => "POST"]);
            curl_setopt_array($curl, [ CURLOPT_POSTFIELDS  => json_encode($post)]);
        }
        if ($method == "PUT") {
            curl_setopt_array($curl, [ CURLOPT_CUSTOMREQUEST => "PUT"]);
            curl_setopt_array($curl, [ CURLOPT_POSTFIELDS  => json_encode($post)]);
        }

        if ($method == "PATCH") {
            curl_setopt_array($curl, [ CURLOPT_CUSTOMREQUEST => "PATCH"]);
            curl_setopt_array($curl, [ CURLOPT_POSTFIELDS  => json_encode($post)]);
        }

        if ($method == "DELETE") {
            curl_setopt_array($curl, [ CURLOPT_CUSTOMREQUEST => "DELETE"]);
        }


        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return $response;
    }


    /*
    *
    * Return the LiveDNS record list.
    *
    * @param string $domain
    * @return array
    *
    */
    public function getLiveDnsRecords(string $domain)
    {
        $url = $this::ENDPOINT . "/domains/{$domain}/records";
        $response = $this->sendRequest($url, "GET");
        logModuleCall('Gandi V5', 'LiveDNS records', $domain, $response);
        return json_decode($response);
    }


    /*
    *
    * Delete a LiveDNS record.
    *
    * @param string $domain
    * @param object $record
    * @return array
    *
    */
    public function deleteRecord(string $domain, $record)
    {
        $url = $this::ENDPOINT . "/domains/{$domain}/records/{$record->rrset_name}/{$record->rrset_type}";
        $response = $this->sendRequest($url, "DELETE");
        logModuleCall('Gandi V5', 'LiveDNS delete record', [$domain,$record], $response);
        return json_decode($response);
    }

    /*
    *
    * Create a LiveDNS record.
    *
    * @param string $domain
    * @param array $record
    * @return array
    *
    */
    public function addRecord(string $domain, $record)
    {
        $url = $this::ENDPOINT . "/domains/{$domain}/records";
        $params = [
            'rrset_name' => $record['hostname'],
            'rrset_type' => $record['type'],
            'rrset_values' => [$record['address']]
        ];
        if ($record['type'] == 'MX') {
            $params['rrset_values'] = [ "{$record['priority']} {$record['address']}" ];
        } else {
            $params['rrset_values'] = [ $record['address'] ];
        }
        //var_dump($params);die();
        $response = $this->sendRequest($url, "POST", $params);
        logModuleCall('Gandi V5', 'LiveDNS add record', [$domain,$params], $response);
        return json_decode($response);
    }
}
