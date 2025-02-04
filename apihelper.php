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

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ApiHelper();
        }
        return self::$instance;
    }

    /**
     * Envía un contacto a Icommkt
     *
     * @param string $email
     * @param string $newsletter_date_add
     * @return string|false
     */
    public function sendContactToIcommkt($email, $newsletter_date_add)
    {
        $date = date_create($newsletter_date_add);
        $date = date_format($date, "d/m/Y");
        $url =  "https://api.icommarketing.com/Contacts/SaveContact.Json/";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = array(
            'ProfileKey' => Configuration::get('ICOMMKT_PROFILEKEY'),
            'Contact' => array (
                'Email'=> $email,
                'CustomFields'=> array(
                    array (
                        'Key' => 'newsletter_date_add',
                        'Value' => $date
                    )
                )
            ),
        );
        $payload = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Authorization:' . Configuration::get('ICOMMKT_APIKEY')
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}