<?php

namespace WHMCS\Module\Registrar\Gandiv5;

class ApiClient
{
    private $endPoint;
    private $apiKey;


    public function getDomainInfo($domain)
    {
        $url = "{$this->endPoint}/domain/domains/{$domain}";
        $response = $this->sendRequest($url, "GET");
        logModuleCall('Gandi V5', 'Domain info', $domain, $response);
        return json_decode($response);
    }
    
    public function registerDomain($domain, $contacts, $nameservers, $period)
    {
        foreach ($nameservers as $k => $v) {
            if (!$v) {
                unset($nameservers[$k]);
            }
        }
        $url = "{$this->endPoint}/domain/domains";
        $owner = [
            "city" => $contacts["owner"]["city"],
            "given" => $contacts["owner"]["firstname"],
            "family" => $contacts["owner"]["lastname"],
            "zip" => $contacts["owner"]["postcode"],
            "country" => $contacts["owner"]["countrycode"],
            "streetaddr" => $contacts["owner"]["address"],
            "phone" => $contacts["owner"]["phonenumberformatted"],
            "state" => $contacts["owner"]["state"],
            "type" => 0, // 0=person, 1=company, 2=association, 3=public body, 4=reseller
            "email" => $contacts["owner"]["email"]
        ];
        $params = [
            "fqdn" => $domain,
            "duration" => $period,
            "owner" => $owner,
            "nameservers" => $nameservers
        ];
        $response = $this->sendRequest($url, "POST", $params);
        logModuleCall('Gandi V5', 'Domain register', $params, $response);
        return json_decode($response);
    }

    public function transferDomain($domain, $contacts, $nameservers, $period, $authCode)
    {
        foreach ($nameservers as $k => $v) {
            if (!$v) {
                unset($nameservers[$k]);
            }
        }
        $url = "{$this->endPoint}/domain/domains";
        $owner = [
            "city" => $contacts["owner"]["city"],
            "given" => $contacts["owner"]["firstname"],
            "family" => $contacts["owner"]["lastname"],
            "zip" => $contacts["owner"]["postcode"],
            "country" => $contacts["owner"]["countrycode"],
            "streetaddr" => $contacts["owner"]["address"],
            "phone" => $contacts["owner"]["phonenumberformatted"],
            "state" => $contacts["owner"]["state"],
            "type" => 0, // 0=person, 1=company, 2=association, 3=public body, 4=reseller
            "email" => $contacts["owner"]["email"]
        ];
        $params = [
            "fqdn" => $domain,
            "duration" => $period,
            "owner" => $owner,
            "nameservers" => $nameservers
        ];
        $response = $this->sendRequest($url, "POST", $params);
        logModuleCall('Gandi V5', 'Domain transfer', $params, $response);
        return json_decode($response);
    }


    private function generatePassword($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);
        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }
        return $result;
    }

    public function __construct($apiKey, $testMode=true)
    {
        $this->endPoint = $testMode?"https://api.gandi.net/v5":"https://api.gandi.net/v5";
        $this->apiKey = $apiKey;
    }

    /*
    *
    * Check if a domain is available to register
    *
    * @param string $domain
    * @return string
    *
    */
    public function getDomainAvailability(string $domain)
    {
        $url = "{$this->endPoint}/domain/check?name={$domain}";
        $response = json_decode($this->sendRequest($url, "GET"));
        logModuleCall('Gandi V5', 'Domain availability', $domain, $response);
        return $response->products[0]->status;
    }
 
    /*
    *
    * Return the list of domains available in the reseller account
    *
    * @return array
    *
    */
    public function getDomainList()
    {
        $url = "{$this->endPoint}/domain/domains";
        $response = $this->sendRequest($url, "GET");
        logModuleCall('Gandi V5', 'Domain list', $domain, $response);
        return json_decode($response);
    }

    /*
    *
    * Return the list of  domain contacts
    *
    * @param string $domain
    * @return array
    *
    */
    public function getDomainContacts(string $domain)
    {
        $url = "{$this->endPoint}/domain/domains/{$domain}/contacts";
        $response = $this->sendRequest($url, "GET");
        logModuleCall('Gandi V5', 'Domain availability', $domain, $response);
        return json_decode($response);
    }


    /*
    *
    * Return the domain nameservers
    *
    * @param string $domain
    * @return array
    *
    */
    public function getDomainNameservers(string $domain)
    {
        $url = "{$this->endPoint}/domain/domains/{$domain}/nameservers";
        $response = $this->sendRequest($url, "GET");
        logModuleCall('Gandi V5', 'Domain availability', $domain, $response);
        return json_decode($response);
    }

    /*
    *
    * Renew a domain
    *
    * @param string $domain
    * @param int $period
    * @return array
    *
    */
    public function renewDomain(string $domain, $period = 1)
    {
        $url = "{$this->endPoint}/domain/domains/{$domain}/renew";
        $params = [
            'duration' => $period
        ];
        $response = $this->sendRequest($url, "POST", $params);
        logModuleCall('Gandi V5', 'Domain renew', $domain, $response);
        return json_decode($response);
    }


    /*
    *
    * Update domain nameservers
    *
    * @param string $domain
    * @param array $nameservers
    * @return array
    *
    */
    public function updateDomainNameservers(string $domain, array $nameservers)
    {
        $url = "{$this->endPoint}/domain/domains/{$domain}/nameservers";
        $params = [
            'nameservers' => $nameservers
        ];
        $response = $this->sendRequest($url, "PUT", $params);
        logModuleCall('Gandi V5', 'Domain update nameservers', $domain, $response);
        return json_decode($response);
    }


    /*
    *
    * Update domain contacts
    *
    * @param string $domain
    * @param array $contacts
    * @return array
    *
    */
    public function updateDomainContacts(string $domain, array $contacts)
    {
        $url = "{$this->endPoint}/domain/domains/{$domain}/contacts";
        $owner = (object) $contacts['Owner'];
        $admin = (object) $contacts['Admin'];
        $admin->type = 0;
        $tech = (object) $contacts['Technical'];
        $tech->type = 0;
        $billing = (object) $contacts['Billing'];
        $billing->type = 0;
        $params = [
            'owner' => $owner,
            'admin' => $admin,
            'bill' => $billing,
            'tech' => $tech
        ];
        $response = $this->sendRequest($url, "PATCH", $params);
        logModuleCall('Gandi V5', 'Domain update contacts', [$domain,$params], $response);
        return json_decode($response);
    }


    /*
     *
     * Register nameserver
     *
     * @param string $domain
     * @param string $name
     * @param string $ip
     * @return array
     *
     */
    public function registerNameserver(string $domain, string $name, string $ip)
    {
        $url = "{$this->endPoint}/domain/domains/{$domain}/hosts";
        $params = [
            'name' => $name,
            'ips' => [$ip]
        ];
        $response = $this->sendRequest($url, "POST", $params);
        logModuleCall('Gandi V5', 'Register nameserver', $domain, $response);
        return json_decode($response);
    }

    /*
    *
    * Delete nameserver
    *
    * @param string $domain
    * @param string name
    * @return array
    *
    */
    public function deleteNameserver(string $domain, string $name)
    {
        $url = "{$this->endPoint}/domain/domains/{$domain}/hosts/{$name}";
        $response = $this->sendRequest($url, "DELETE", []);
        logModuleCall('Gandi V5', 'Delete nameserver', $domain, $response);
        return json_decode($response);
    }

    /*
    *
    * Update nameserver
    *
    * @param string $domain
    * @param string $name
    * @param string $ip
    * @return array
    *
    */
    public function updateNameserver(string $domain, string $name, string $ip)
    {
        $url = "{$this->endPoint}/domain/domains/{$domain}/hosts/{$name}";
        $params = [
            'ips' => [$ip]
        ];
        $response = $this->sendRequest($url, "PUT", $params);
        logModuleCall('Gandi V5', 'Update nameserver', $domain, $response);
        return json_decode($response);
    }




    public function sendRequest($url, $method="GET", $post=[], $timeout=30)
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
     * Return the LiveDNS info.
     *
     * @param string $domain
     * @return array
     *
     */
    public function getLiveDnsInfo(string $domain)
    {
        $url = "{$this->endPoint}/domain/domains/{$domain}/livedns";
        $response = $this->sendRequest($url, "GET");
        logModuleCall('Gandi V5', 'LiveDNS info', $domain, $response);
        return json_decode($response);
    }

    /*
    *
    * Enable LiveDNS.
    *
    * @param string $domain
    * @return array
    *
    */
    public function enableLiveDNS(string $domain)
    {
        $url = "{$this->endPoint}/domain/domains/{$domain}/livedns";
        $response = $this->sendRequest($url, "POST");
        logModuleCall('Gandi V5', 'Enable LiveDNS', $domain, $response);
        return json_decode($response);
    }
}
