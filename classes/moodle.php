<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The mod_mindmaap mindmaap API.
 *
 * @package     mod_mindmaap
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class mindmaap {

    private $baseurl;
    private $customertoken;

    public function __construct(string $customertoken, $baseurl) {
        $this->customertoken = $customertoken;
        $this->baseurl = $baseurl;
    }

    /**
     * @param string $name
     * @param string $description
     * @param array $additional_data - should contain activityId data
     *
     * Example: $additionaldata = [766]; 766 - activity_id
     *
     * @return array
     */
    public function createpage(string $name, string $description, array $additionaldata = []) {
        $response = $this->request($this->baseurl . 'mindmap/create',
                ['name' => $name, 'description' => $description, 'additional_data' => $additionaldata]);

        //print_r($response);
        //exit;
        if (!$response || !$response['status']) {
            // Do additional logging if nothing created or error happend.
            return $response;
        }

        return $response['data'];
    }

    /**
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param array $additionaldata - should contains activity_id
     *
     * Example: $additionaldata = [766]; 766 - activity_id
     *
     * @return array
     */
    public function registeruser(string $email, string $firstname, string $lastname, array $additionaldata = []) {
        $response = $this->request(
                $this->baseurl . 'users/register', [
                        'email' => $email,
                        'first_name' => $firstname,
                        'last_name' => $lastname,
                        'additional_data' => $additionaldata
                ]
        );

        if (empty($response) || empty($response['status'])) {
            // Do additional logging if nothing created or error happend.
            return $response;
        }

        return $response['data'];
    }

    /**
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param array $additional_data - should contains activity_id
     *
     * Example: $additional_data = [766]; 766 - activity_id
     *
     * @return string
     */
    public function geturlforuser(string $email, string $firstname, string $lastname, array $additional_data = []) {
        $user = $this->registeruser($email, $firstname, $lastname, $additional_data);

        return $user['url'];
    }

    /**
     * @param string $urlparam
     *
     * @return string
     */
    public function getsessionurl(string $urlparam, string $mindmap) {
        return $this->baseurl . "users/set-session/?token={$this->getcustomertoken()}&url_param={$urlparam}&map={$mindmap}";
    }

    /**
     * @param string $url
     * @param array $body
     *
     * @return array
     */
    private function request(string $url, array $body): array {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body, '', '&')); // Post Fields.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
                "Authorization: Bearer {$this->getcustomertoken()}",
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            // Add logg error.
            echo 'Error:' . curl_error($ch);

            return [];
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $decoded = json_decode($response, true);

        return $decoded;
    }

    /**
     * @param $token
     */
    public function setcustomertoken($token) {
        $this->customertoken = $token;
    }

    /**
     * @return string
     */
    public function getcustomertoken() {
        return $this->customertoken;
    }
}
