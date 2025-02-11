<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Icommkt
 * @copyright Icommkt
 * @license   GPLv3
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ApiHelper {

    const STATUS_CODE_SUCCESS = 1;

    private static $instances = [];

    protected $apiKey;

    protected $profileKey;

    public static function getInstance($apiKey = null, $profileKey = null)
    {
        $instanceId = md5($apiKey . $profileKey);

        if (!isset(self::$instances[$instanceId])) {
            self::$instances[$instanceId] = new self($apiKey, $profileKey);
        }

        return self::$instances[$instanceId];
    }

    public function __construct($apiKey = null, $profileKey = null)
    {
        $this->apiKey = is_null($apiKey) ? Configuration::get('ICOMMKT_APIKEY') : $apiKey;
        $this->profileKey = is_null($profileKey) ? Configuration::get('ICOMMKT_PROFILEKEY') : $profileKey;
    }

    /**
     * Envía un contacto a Icommkt
     *
     * @param string $email
     * @param string $newsletter_date_add @deprecated version 1.2.4
     * @return string|false
     */
    public function sendContactToIcommkt($email, $newsletter_date_add = '')
    {
        return $this->sendContactRequest($email);
    }

    /**
     * Envía un contacto a Icommkt con campos personalizados
     *
     * @param string $email
     * @param array $customFields
     * @return string|false
     */
    public function sendContactToIcommktWithCustomFields($email, $customFields)
    {
        return $this->sendContactRequest($email, $customFields);
    }

    protected function sendContactRequest($email, $customFields = [])
    {
        $url =  "https://api.icommarketing.com/Contacts/SaveContact.Json/";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = array(
            'ProfileKey' => $this->profileKey,
            'Contact' => array (
                'Email'=> $email,
            ),
        );

        if (!empty($customFields) && is_array($customFields)) {
            $data['Contact']['CustomFields'] = $customFields;
        }

        $payload = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Authorization:' . $this->apiKey
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}