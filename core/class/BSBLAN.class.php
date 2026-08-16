<?php

// Last Modified : 2026/08/15 17:58:13

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class BSBLAN extends eqLogic
{

    public function encrypt()
    {
        $this->setConfiguration('user', utils::encrypt($this->getConfiguration('user')));
        $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
        $this->setConfiguration('passkey', utils::encrypt($this->getConfiguration('passkey')));
    }

    public function decrypt()
    {
        $this->setConfiguration('user', utils::decrypt($this->getConfiguration('user')));
        $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
        $this->setConfiguration('passkey', utils::decrypt($this->getConfiguration('passkey')));
    }

    public static function enable_cron($_enable)
    {
        $cron_BSBLAN = cron::byClassAndFunction('BSBLAN', 'update');
        $schedule = '* * * * *';
        if ($_enable == '1') {
            log::add('BSBLAN', 'debug', __('Activation du cron de BSBLAN', __FILE__));
            if (!is_object($cron_BSBLAN)) {
                $cron_BSBLAN = new cron();
                $cron_BSBLAN->setClass('BSBLAN');
                $cron_BSBLAN->setFunction('update');
                $cron_BSBLAN->setEnable(1);
                $cron_BSBLAN->setDeamon(0);
                $cron_BSBLAN->setSchedule($schedule);
                $cron_BSBLAN->setTimeout(1);
            } else {
                $cron_BSBLAN->setEnable(1);
            }
            $cron_BSBLAN->save();
        } else {
            log::add('BSBLAN', 'debug', __('Désactivation du cron de BSBLAN', __FILE__));
            if (is_object($cron_BSBLAN)) {
                $cron_BSBLAN->remove();
            }
        }
    }

    public function test_connexion()
    {
        log::add('BSBLAN', 'info', __FUNCTION__ . ' ' . $this->getName());

        $ip = $this->getConfiguration('ip');
        $url_api = 'http://' . $ip . '/';
        $passkey = $this->getConfiguration('passkey');
        if ($passkey != '') {
            $url_api = $url_api . $passkey . '/';
        }
        $url_api = $url_api . 'JI';
        log::add('BSBLAN', 'debug', __FUNCTION__ . '  url_api ' . $url_api);

        $user = $this->getConfiguration('user');
        $password = $this->getConfiguration('password');
        if ($user != "") {
            $userpassword = $user . ':' . $password;
        }

        $ch = curl_init();
        try {
            curl_setopt($ch, CURLOPT_URL, $url_api);

            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $timeout = $this->getConfiguration('timeout', '15');
            if (is_numeric($timeout)) {
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            }

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            if ($user != "") {
                curl_setopt($ch, CURLOPT_USERPWD, $userpassword);
            }
            $response = curl_exec($ch);

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($http_code == intval(200)) {
                $obj = json_decode($response, TRUE);
                log::add('BSBLAN', 'debug', 'curl_exec response : $http_code ' . $http_code . ' response ' . self::FormatArrayForLog($response));
                if (isset($obj['version'])) {
                    $return = __('Connexion OK : version de BSB-LAN', __FILE__) . ' ' . $obj['version'];
                    log::add('BSBLAN', 'debug', $return);
                    return 'OK ' . $return;
                } else {
                    $return = __('Connexion KO : pas un BSBLAN', __FILE__);
                    log::add('BSBLAN', 'debug', $return);
                    return 'KO ' . $return;
                }
            } else {
                if ($http_code == intval(0)) {
                    $return = __('Connexion KO : erreur http ', __FILE__) . ' ' . $http_code . ' ' . __('Pas de réponse de', __FILE__) . ' ' . $this->getConfiguration('ip');
                    log::add('BSBLAN', 'debug', $return);
                    return 'KO ' . $return;
                } else {
                    $return = __('Connexion KO : erreur http ' . $http_code . ' response --> ' . self::compactHtmlText($response), __FILE__);
                    log::add('BSBLAN', 'debug', $return);
                    return 'KO ' . $return;
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            curl_close($ch);
        }
    }


    public function https_file_get_contents($url, $json_data = '')
    {
        log::add('BSBLAN', 'info', __FUNCTION__ . '  url ' . $url . ' json ' . $json_data);

        $ip = $this->getConfiguration('ip');
        $url_api = 'http://' . $ip . '/';
        $passkey = $this->getConfiguration('passkey');
        if ($passkey != '') {
            $url_api = $url_api . $passkey . '/';
        }
        $url_api = $url_api . $url;
        log::add('BSBLAN', 'debug', __FUNCTION__ . '  url_api ' . $url_api);

        $user = $this->getConfiguration('user');
        $password = $this->getConfiguration('password');
        if ($user != "") {
            $userpassword = $user . ':' . $password;
        }

        $ch = curl_init();
        try {
            curl_setopt($ch, CURLOPT_URL, $url_api);

            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $timeout = $this->getConfiguration('timeout', '15');
            if (is_numeric($timeout)) {
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            }

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            if ($user != "") {
                curl_setopt($ch, CURLOPT_USERPWD, $userpassword);
            }
            if ($json_data != '') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, array("customer" => $json_data));
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    array(
                        'Content-Type:application/json',
                        'Content-Length: ' . strlen($json_data)
                    )
                );
            }
            $retry = $this->getConfiguration('retry', '3');
            if (is_numeric($retry) == false) {
                $retry = 3;
            } else {
                if ($retry <= 0) {
                    $retry = 1;
                }
            }
            $essai = 0;
            while ($essai < $retry) {
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($http_code == intval(200)) {
                    break;
                }
                log::add('BSBLAN', 'warning', 'curl_exec response : http_code ' . $http_code . ' Curl ' . __('erreur', __FILE__) . ': ' . curl_error($ch) . ' -> ' . __('nouvel essai', __FILE__));
                $essai = $essai + 1;
            }

            if ($http_code == intval(200)) {
                log::add('BSBLAN', 'debug', 'curl_exec response : http_code ' . $http_code . ' ' . __('reponse', __FILE__) . ' --> ' . self::compactHtmlText($response));
            } else {
                if ($http_code == intval(0)) {
                    $return = __('BSBLAN http erreur', __FILE__) . ' : ' . __('Pas de reponse de', __FILE__) . ' ' . $this->getConfiguration('ip') . ' Curl error: ' . curl_error($ch);
                    log::add('BSBLAN', 'error', $return);
                    return false;
                } else {
                    $return = __('BSBLAN http erreur', __FILE__) . ' : ' . $http_code . ' ' . __('reponse', __FILE__) . ' --> ' . self::compactHtmlText($response);
                    log::add('BSBLAN', 'debug', $return);
                    return false;
                }
            }
        } catch (\Throwable $th) {
            // throw $th;
            return false;
        } finally {
            curl_close($ch);
        }
        return $response;
    }

    function BSBLAN_api($_api, $json_data = '')
    {
        log::add('BSBLAN', 'debug', __FUNCTION__ . ' url ' . $_api . ' json ' . $json_data);

        $json = $this->https_file_get_contents($_api, $json_data);
        if ($json == false)
            return false;
        log::add('BSBLAN', 'debug', __FUNCTION__ . ' ' . __('Requete', __FILE__) . ' ' . $_api . ' json ' . compactJsonText($json));

        $obj = json_decode($json, TRUE);
        log::add('BSBLAN', 'debug', 'Data : ' . self::FormatArrayForLog($obj));

        return $obj;
    }

    public function create_command($id_commande, $info, $action, $refresh)
    {
        log::add('BSBLAN', 'info', __FUNCTION__ . ' ' . $this->getName() . ' Commande ' . $id_commande . ' Info ' . $info . ' Action ' . $action . ' Refresh ' . $refresh);

        //    $carte->getSessionId();
        if ($info != '') {
            $return = $this->create_info_command($id_commande);
        }
        if ($action != '') {
            $return = $this->create_action_command($id_commande);
        }
        if ($refresh != '') {
            $return = $this->create_refresh_command($id_commande);
        }
        return $return;
    }

    private function create_info_command($item_id)
    // crée la commande type info
    {
        if (is_object(cmd::byEqLogicIdAndLogicalId($this->id, $item_id))) {
            $return = $this->getName() . '  ' . __('commande info déjà créée', __FILE__) . ' ' . $item_id;
            log::add('BSBLAN', 'info', __FUNCTION__ . ' ' . $return);
            return 'KO ' . $return;
        }

        // lit la description du parametre
        $obj_detail = $this->BSBLAN_api('JC=' . $item_id);
        if ($obj_detail == false) {
            return 'KO ' . __('Erreur accès BSBLAN', __FILE__);
        }
        if (isset($obj_detail["$item_id"]['name'])) {

            $name = $item_id . ' ' . str_replace(array('&', '#', ']', '[', '%', "'"), ' ', $obj_detail["$item_id"]['name']);
            if ($name == '') {
                $name = $item_id;
            }
            log::add('BSBLAN', 'info', __FUNCTION__ . ' ' . $this->getName() . ' ' . __('création commande', __FILE__) . ' ' . $name);

            $cmd = new BSBLANCmd();

            // BD: pour éviter les problèmes de conversion par exemple quand le nom contient le caractere /
            $cmd->setName($name);
            $name = $cmd->getName();

            $cmd->setName(self::getUniqueCmdName($this->getId(), $name));

            /*
             http://<ip-address>/JQ
             Send: "Parameter"
             Receive: "Parameter", "Value", "Unit", 
             "DataType" (
                0 = plain value (number), 
                1 = ENUM (value (8/16 Bit) followed by space followed by text), 
                2 = bit value (bit value (decimal) followed by bitmask followed by text/chosen option), 
                3 = weekday, 
                4 = hour:minute, 
                5 = date and time, 
                6 = day and month, 
                7 = string, 
                8 = PPS time (day of week, hour:minute)), 
             "readonly" (0 = read/write, 1 = read only parameter), 
             "error" (0 - ok, 
               7 - parameter not supported, 
               1-255 - LPB/BSB bus errors, 
               256 - decoding error, 
               257 - unknown command, 
               258 - not found, 
               259 - no enum str, 
               260 - unknown type, 
               261 - query failed), 
               "isswitch" (1 = it VT_ONOFF or VT_YESNO data type (subtype of ENUM), 
               0 = all other cases)  
            */
            // crée la commande de type INFO
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId($item_id); // le logical id est égal à l'id du parametre
            $cmd->setConfiguration('infoId', $item_id);
            $cmd->setIsVisible(1);
            $cmd->setConfiguration('isPrincipale', '0');
            $cmd->setOrder(time());
            $cmd->setConfiguration('isCollected', '1');
            $cmd->setConfiguration('isswitch', $obj_detail["$item_id"]['isswitch']);
            $cmd->setConfiguration('readonly', $obj_detail["$item_id"]['readonly']);
            $cmd->setConfiguration('readwrite', $obj_detail["$item_id"]['readwrite']);
            $dataType = $obj_detail["$item_id"]['dataType'];
            $cmd->setConfiguration('internal_type', $dataType);
            $cmd->setConfiguration('dataTypename', $obj_detail["$item_id"]['dataTypename']);
            $cmd->setTemplate('dashboard', 'core::line');
            $cmd->setTemplate('mobile', 'core::line');
            $cmd->setUnite($obj_detail["$item_id"]['unit']);

            $cmd->setType('info');
            $cmd->setDisplay('generic_type', 'GENERIC_INFO');

            switch (true) {
                case $obj_detail["$item_id"]['possibleValues'] != '' and !empty($obj_detail["$item_id"]['possibleValues']):
                    $cmd->setSubType('string');
                    /*
                    $cmd->setConfiguration('internal_type', 'Enumeration');
                    foreach ($obj_detail["$item_id"]['possibleValues'] as $item_enum) {
                        $cmd->setConfiguration('internal_label_' . $item_enum['enumValue'], $item_enum['desc']);
                    }
                    */
                    break;
                case $dataType == '0' || $dataType == '1':
                    $cmd->setSubType('numeric');
                    break;
                case $dataType == '2':
                    $cmd->setSubType('binary');
                    break;
                default:
                    $cmd->setSubType('string');
                    break;
            }

            $cmd->save();
            $return = __('Commande info créée', __FILE__) . ' ' . $item_id;
            log::add('BSBLAN', 'debug', __FUNCTION__ . ' ' . $return);
            return 'OK ' . $return;
        }
    }

    private function create_refresh_command($item_id)
    // crée la commande type refresh
    {

        if (is_object(cmd::byEqLogicIdAndLogicalId($this->id, 'R_' . $item_id))) {
            $return = $this->getName() . '  ' . __('commande refresh déjà créée', __FILE__) . ' ' . $item_id;
            log::add('BSBLAN', 'info', __FUNCTION__ . ' ' . $return);
            return 'KO ' . $return;
        }

        // lit la description du parametre
        $obj_detail = $this->BSBLAN_api('JC=' . $item_id);
        if ($obj_detail == false) {
            return 'KO ' . __('Erreur accès BSBLAN', __FILE__);
        }
        if (isset($obj_detail["$item_id"]['name'])) {

            $name = $item_id . ' ' . str_replace(array('&', '#', ']', '[', '%', "'"), ' ', $obj_detail["$item_id"]['name']);
            if ($name == '') {
                $name = $item_id;
            }

            $name = $name . ' Refresh';

            log::add('BSBLAN', 'info', __FUNCTION__ . ' ' . $this->getName() . '  création commande ' . $name);

            $cmd = new BSBLANCmd();

            // BD: pour éviter les problèmes de conversion par exemple quand le nom contient le caractere /
            $cmd->setName($name);
            $name = $cmd->getName();

            $cmd->setName(self::getUniqueCmdName($this->getId(), $name));

            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('R_' . $item_id); // le logical id est égal à 'R_' plus l'id du parametre
            $cmd->setConfiguration('infoId', $item_id);
            $cmd->setIsVisible(1);
            $cmd->setOrder(time());
            
            $dataType = $obj_detail["$item_id"]['dataType'];
            $cmd->setConfiguration('internal_type', $dataType);
            $cmd->setConfiguration('dataTypename', $obj_detail["$item_id"]['dataTypename']);

            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->save();

            $return = __('Commande refresh', __FILE__) . ' ' . $item_id;
            log::add('BSBLAN', 'debug', __FUNCTION__ . ' ' . $return);
            return 'OK ' . $return;
        }
    }


    private function create_action_command($item_id)
    // crée la commande type action
    {

        if (is_object(cmd::byEqLogicIdAndLogicalId($this->id, 'A_' . $item_id))) {
            $return = $this->getName() . '  ' . __('commande action déjà créée', __FILE__) . ' ' . $item_id;
            log::add('BSBLAN', 'info', __FUNCTION__ . ' ' . $return);
            return 'KO ' . $return;
        }

        // lit la description du parametre
        $obj_detail = $this->BSBLAN_api('JC=' . $item_id);
        if ($obj_detail == false) {
            return 'KO ' . __('Erreur accès BSBLAN', __FILE_);
        }
        if (isset($obj_detail["$item_id"]['name'])) {

            $name = $item_id . ' ' . str_replace(array('&', '#', ']', '[', '%', "'"), ' ', $obj_detail["$item_id"]['name']);
            if ($name == '') {
                $name = $item_id;
            }

            $name = $name . ' Action';

            log::add('BSBLAN', 'info', __FUNCTION__ . ' ' . $this->getName() . '  création commande ' . $name);

            $cmd = new BSBLANCmd();

            // BD: pour éviter les problèmes de conversion par exemple quand le nom contient le caractere /
            $cmd->setName($name);
            $name = $cmd->getName();

            $cmd->setName(self::getUniqueCmdName($this->getId(), $name));

            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('A_' . $item_id); // le logical id est égal à 'A_' plus l'id du parametre
            $cmd->setConfiguration('infoId', $item_id);
            $cmd->setIsVisible(1);
            $cmd_info = cmd::byEqLogicIdAndLogicalId($this->id, $item_id);
            if (is_object($cmd_info)) {
                $cmd->setValue($cmd_info->getID()); // cmmande info liée
            }
            $cmd->setOrder(time());

            $dataType = $obj_detail["$item_id"]['dataType'];
            $cmd->setConfiguration('internal_type', $dataType);
            $cmd->setConfiguration('dataTypename', $obj_detail["$item_id"]['dataTypename']);

            $cmd->setType('action');
            switch (true) {

                case $obj_detail["$item_id"]['possibleValues'] != '' and !empty($obj_detail["$item_id"]['possibleValues']):
                    $cmd->setSubType('select');
                    $list_value = array();
                    foreach ($obj_detail["$item_id"]['possibleValues'] as $item_enum) {
                        array_push($list_value, $item_enum['enumValue'] . '|' . $item_enum['desc']);
                    }
                    $cmd->setConfiguration('listValue', join(";", $list_value));
                    break;
                case $dataType == '0' || $dataType == '1':
                    $cmd->setSubType('slider');
                    break;
                default:
                    $cmd->setSubType('message');
                    $cmd->setDisplay('title_disable', 0);
                    break;
            }
            $cmd->save();
            $return = __('Commande action créée', __FILE__) . ' ' . $item_id;
            log::add('BSBLAN', 'debug', __FUNCTION__ . ' ' . $return);
            return 'OK ' . $return;
        }
    }

    public function preInsert()
    {
        if ($this->getConfiguration('type', '') == "") {
            $this->setConfiguration('type', 'BSBLAN');
        }
    }



    public function postInsert()
    {
        $this->postUpdate();
    }

    public function postUpdate()
    {
        unset($cmd);
        $cmd = $this->getCmd(null, 'updatetime');
        if (!is_object($cmd)) {
            $cmd = new BSBLANCmd();
            $cmd->setName('Dernier refresh');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('updatetime');
            $cmd->setUnite('');
            $cmd->setType('info');
            $cmd->setSubType('string');
            $cmd->setIsHistorized(0);
            $cmd->save();
        }

        unset($cmd);
        $cmd = $this->getCmd(null, 'Refresh');
        if (!is_object($cmd)) {
            $cmd = new BSBLANCmd();
            $cmd->setName('Refresh');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setLogicalId('refresh');
            $cmd->setIsVisible(1);
            $cmd->setDisplay('generic_type', 'GENERIC_INFO');
            $cmd->save();
        }
    }

    public static function cron()
    {
        $cron_BSBLAN = cron::byClassAndFunction('BSBLAN', 'update');
        if (!is_object($cron_BSBLAN)) {
            log::add('BSBLAN', 'info', __('Lancement de cron',__FILE_));
            BSBLAN::update();
        }
    }

    public static function update()
    {
        log::add('BSBLAN', 'info', __('Lancement de',__FILE__) . ' ' . __FUNCTION__);
        foreach (eqLogic::byTypeAndSearchConfiguration('BSBLAN', '"type":"BSBLAN"') as $eqLogic) {
            log::add('BSBLAN', 'info', __('Appel ',__FILE__) . ' BSBLAN_Update BSBLAN : ' . $eqLogic->getName());
            if ($eqLogic->getIsEnable()) {
                BSBLAN::BSBLAN_Update($eqLogic);
            }
        }
    }

    public static function BSBLAN_Update($_eqLogic, $_context = 'cron')
    {
        log::add('BSBLAN', 'info', __FUNCTION__ .' ' . __('Appareil',__FILE__) . ' : ' . $_eqLogic->getName() . ' ' .__('Contexte',__FILE__) . ' ' . $_context);

        foreach ($_eqLogic->getCmd() as $cmd) {
            if (is_numeric($cmd->getLogicalId()) && $cmd->getConfiguration('isCollected') == 1) {
                $run = false;
                if ($_context == 'refresh') {
                    $run = true;
                } else {
                    $autorefresh = '';
                    switch ($cmd->getConfiguration('cron')) {
                        case "cron":
                            $autorefresh = '*/1 * * * *';
                            break;
                        case "cron5":
                            $autorefresh = '*/5 * * * *';
                            break;
                        case "cron10":
                            $autorefresh = '*/10 * * * *';
                            break;
                        case "cron15":
                            $autorefresh = '*/15 * * * *';
                            break;
                        case "cron30":
                            $autorefresh = '*/30 * * * *';
                            break;
                        case "cronHourly":
                            $autorefresh = '0 * * * *';
                            break;
                        case "cronDaily":
                            $autorefresh = '0 0 * * *';
                            break;
                    }
                    if ($autorefresh != '') {
                        $c = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
                        if ($c->isDue()) {
                            $run = true;
                        }
                    }
                }

                if ($run == true) {
                    if ($_eqLogic->refresh_info_cmd($cmd) == true) {
                        $_eqLogic_refresh_cmd = $_eqLogic->getCmd(null, 'updatetime');
                        $_eqLogic->checkAndUpdateCmd($_eqLogic_refresh_cmd, date("d/m/Y H:i", (time())));
                    }
                }
            }
        }
    }

    function refresh_info_cmd($_cmd)
    {
        log::add('BSBLAN', 'debug', __('Lecture du paramètre',__FILE__) . ' ' . $_cmd->getLogicalId() . ' ' . $_cmd->getName());
        $item_id = $_cmd->getLogicalId();
        $obj_detail = $this->BSBLAN_api('JQ=' . $item_id);
        if ($obj_detail == false) {
            return false;
        }
        if (isset($obj_detail["$item_id"]['name'])) {
            log::add('BSBLAN', 'info', __('Valeur de',__FILE__) . ' ' . $item_id . ' ' . $_cmd->getName() . ' --> ' . $obj_detail["$item_id"]['value'] . ' ' . $obj_detail["$item_id"]['desc']);
            $value = $obj_detail["$item_id"]['value'];
            if (isset($obj_detail["$item_id"]['desc'])) {
                if ($obj_detail["$item_id"]['desc'] != '') {
                    $value = $obj_detail["$item_id"]['desc'];
                }
            }
            $eqLogic = $_cmd->getEqlogic();
            $eqLogic->checkAndUpdateCmd($_cmd, $value);
            return true;
        } else {
            return false;
        }
    }

    public static function FormatArrayForLog($value)
    {
        $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;
        $encoded = json_encode($value, $options);

        if ($encoded === false) {
            return json_encode((string) $value, $options);
        }

        return $encoded;
    }

    private function compactHtmlText($value)
    {
        return preg_replace('/\s+/', ' ', strip_tags($value));
    }
    private function compactJsonText($value)
    {
        return preg_replace('/\s+/', ' ', $value);
    }

    public static function getUniqueCmdName($eqLogicId, $name)
    {
        // teste si le nom de la commande est déjà attribué
        // si oui, ajoute à la fin un numéro afin d'avoir un nom unique
        if (!is_object(cmd::byEqLogicIdCmdName($eqLogicId, $name))) {
            return $name;
        }

        $count = 1;
        while (is_object(cmd::byEqLogicIdCmdName($eqLogicId, substr($name, 0, 100) . "..." . $count))) {
            $count++;
        }
        $name = substr($name, 0, 100) . "..." . $count;
        log::add('BSBLAN', 'info', __('Renomme en', __FILE__) . ' ' . $name);
        return $name;
    }
}
class BSBLANCmd extends cmd
{

    public function execute($_options = null)
    {

        $eqLogic = $this->getEqLogic();
        if (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1) {
            throw new \Exception(__('Equipement desactivé impossible d\éxecuter la commande : ' . $this->getHumanName(), __FILE__));
        }

        // Refresh toutes les infos
        if ($this->getLogicalId() == 'refresh') {
            log::add('BSBLAN', 'info', __('execute ', __FILE__) . '  refresh');
            BSBLAN::BSBLAN_Update($eqLogic, 'refresh');
            return true;
        }

        // Commande action
        if (substr($this->getLogicalId(), 0, 2) == 'A_') {
            $internalid = substr($this->getLogicalId(), 2); // remove 'A_'

            switch ($this->getSubType()) {
                case "select":
                    $value = $_options['select'];
                    break;
                case "slider":
                    $value = $_options['slider'];
                    break;
                case "message":
                    $value = $_options['message'];
                    break;
                default:
                    log::add('BSBLAN', 'info', __('Type d\'action non défini',__FILE__) . ' : ' . $this->getSubType());
                    die;
                    break;
            }
            $data_string = '';


            $update_method = $this->getConfiguration('set_method', 'Defaut');
            if ($update_method == 'Defaut') {
                $update_method = $eqLogic->getConfiguration('set_method', '');
            }

            if ($update_method == 'Set') {
                // /S<x>=<y> 	Set parameter <x> from controller at default destination address to value . 
                // To set a parameter to --- (off/deactivated), just send an empty value: S<x>=
                $url = 'S' . $internalid . '=' . $value;
            } else {
                /*
                        http://<ip-address>/JS  
                        Send: "Parameter", "Value", "Type" (0 = INF, 1 = SET)  
            */
                $Type = '1';
                if ($internalid >= 10000 and $internalid <= 10002) {
                    $Type = '0'; // type INF
                }

                $data = array(
                    "Parameter" => $internalid,
                    "Value" => $value,
                    "Type" => $Type
                );

                $data_string = json_encode($data);

                $url = 'JS';
            }
            log::add('BSBLAN', 'info', __('execute ', __FILE__) . '  url ' . $url . ' json ' . $data_string);
            $obj = $eqLogic->BSBLAN_api($url, $data_string);
            if ($obj == false) {
                return false;
            }

            return true;
        }

        //}

        // Commande refresh
        if (substr($this->getLogicalId(), 0, 2) == 'R_') {
            $internalid = substr($this->getLogicalId(), 2); // remove 'R_'

            $cmd = cmd::byEqLogicIdAndLogicalId($eqLogic->getId(), $internalid);
            if (!is_object($cmd)) {
                log::add('BSBLAN', 'debug', __('Commande non trouvée',__FILE__) . ' ' . $internalid);
                return false;
            }
            return $eqLogic->refresh_info_cmd($cmd);
        }
    }


    public function dontRemoveCmd()
    {
        $eqLogic = $this->getEqLogic();
        if (is_object($eqLogic)) {
            if ($eqLogic->getConfiguration('type', '') == 'BSBLAN') {
                if ($this->getLogicalId() == 'updatetime' || $this->getLogicalId() == 'refresh') {
                    return true;
                }
            }
            return false;
        }
    }
}
