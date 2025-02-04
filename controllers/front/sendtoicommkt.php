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

require_once dirname(dirname(__DIR__)) . '/apihelper.php';

class IcommktconnectorSendToIcommktModuleFrontController extends ModuleFrontController
{

    public $php_self;

    public function __construct()
    {
        $this->context = Context::getContext();
        parent::__construct();
    }

    public function init()
    {
        parent::init();

        $action = Tools::getValue('action');
        $secure_token = Tools::getValue('secure_token');
        $secure_token_back = Configuration::get('ICOMMKT_SECURE_TOKEN');

        if (empty($secure_token) || ($secure_token != $secure_token_back)) {
            echo 'El token no coincide';
            exit();
        }

        switch ($action) {
            case 'sendtoicommktuser':
                $this->sendUsersIcommkt();
                break;
            default:
                break;
        }
    }

    public function sendUsersIcommkt()
    {
        if (!Module::isInstalled('icommktconnector')) {
            echo 'Error: módulo no instalado.';
            exit();
        } else {
            if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') == true) {
                $sql  = 'SELECT id, email, newsletter_date_add FROM ' . _DB_PREFIX_ . 'emailsubscription e
                    WHERE is_send_icommkt IS NULL OR is_send_icommkt = 0 '.
                    Shop::addSqlRestriction(false, 'e');
                $table = 'emailsubscription';
            } else {
                $sql  = 'SELECT id, email, newsletter_date_add FROM ' . _DB_PREFIX_ . 'newsletter n
                    WHERE is_send_icommkt IS NULL OR is_send_icommkt = 0 '.
                    Shop::addSqlRestriction(false, 'n');
                $table = 'newsletter';
            }
            
            $result = Db::getInstance()->executeS($sql);

            $apiHelper = ApiHelper::getInstance();

            foreach ($result as $user) {
                if ($user['email']) {
                    $response = $apiHelper->sendContactToIcommkt($user['email'], $user['newsletter_date_add']);
                    $response = json_decode($response, true);
                    if ($response['SaveContactJsonResult']['StatusCode'] == ApiHelper::STATUS_CODE_SUCCESS) {
                        Db::getInstance()->update(
                            $table,
                            array(
                            'is_send_icommkt'=> 1,
                            'date_send_icommkt' => date('Y-m-d H:i:s')),
                            'id='.$user['id']
                        );
                    }
                }
            }
        }
    }
}
