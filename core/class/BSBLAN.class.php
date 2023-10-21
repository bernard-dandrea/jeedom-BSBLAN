<?php

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
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */
    public function test_connexion()
    {

        log::add('BSBLAN', 'info', __('test_connexion ', __FILE__));

        $ip = $this->getConfiguration('ip');
        $url_api = 'http://' . $ip . '/';
        $passkey = $this->getConfiguration('passkey');
        if ($passkey != '') {
            $url_api = $url_api . $passkey . '/';
        }
        $url_api = $url_api . 'JI';
        log::add('BSBLAN', 'debug', __('https_file_get_contents ', __FILE__) . '  url_api ' . $url_api);

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
                log::add('BSBLAN', 'debug', 'curl_exec response : $http_code ' . $http_code . ' response ' . $response);

                if (isset($obj['version'])) {

                    log::add('BSBLAN', 'debug', 'Connexion OK : version de BSB-LAN ' . $obj['version']);

                    event::add(
                        'jeedom::alert',
                        array(
                            'level' => 'success',
                            'page' => 'BSBLAN',
                            'message' => __('Connexion OK : version de BSB-LAN ' . $obj['version'], __FILE__),
                        )
                    );
                } else {

                    log::add('BSBLAN', 'debug', 'Connexion KO : pas un BSBLAN');
                    event::add(
                        'jeedom::alert',
                        array(
                            'level' => 'warning',
                            'page' => 'BSBLAN',
                            'message' => __('Connexion KO : pas un BSBLAN', __FILE__),
                        )
                    );

                }
            } else {

                if ($http_code == intval(0)) {
                    log::add('BSBLAN', 'debug', 'No answer from ' . $this->getConfiguration('ip'));
                    event::add(
                        'jeedom::alert',
                        array(
                            'level' => 'error',
                            'page' => 'BSBLAN',
                            'message' => __('Connexion KO : erreur http ' . $http_code . ' No answer from ' . $this->getConfiguration('ip'), __FILE__),
                        )
                    );
                } else {
                    log::add('BSBLAN', 'debug', 'curl_exec http error ' . $http_code);
                    event::add(
                        'jeedom::alert',
                        array(
                            'level' => 'error',
                            'page' => 'BSBLAN',
                            'message' => __('Connexion KO : erreur http ' . $http_code . ' response --> ' . strip_tags($response), __FILE__),
                        )
                    );
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
        log::add('BSBLAN', 'info', __('https_file_get_contents ', __FILE__) . '  url ' . $url . ' json ' . $json_data);

        $ip = $this->getConfiguration('ip');
        $url_api = 'http://' . $ip . '/';
        $passkey = $this->getConfiguration('passkey');
        if ($passkey != '') {
            $url_api = $url_api . $passkey . '/';
        }
        $url_api = $url_api . $url;
        log::add('BSBLAN', 'debug', __('https_file_get_contents ', __FILE__) . '  url_api ' . $url_api);

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
            $response = curl_exec($ch);

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code == intval(200)) {
                log::add('BSBLAN', 'debug', 'curl_exec response : http_code ' . $http_code . ' response --> ' . strip_tags($response));
            } else {
                if ($http_code == intval(0)) {
                    log::add('BSBLAN', 'debug', 'No answer from ' . $this->getConfiguration('ip'));
                    throw new \Exception(__('BSBLAN http error : ', __FILE__) . 'No answer from ' . $this->getConfiguration('ip'));
                } else {
                    log::add('BSBLAN', 'debug', 'curl_exec http error ' . $http_code);
                    throw new \Exception(__('BSBLAN http error : ', __FILE__) . $http_code . ' response --> ' . strip_tags($response));
                }
            }

        } catch (\Throwable $th) {
            throw $th;
        } finally {
            curl_close($ch);
        }
        return $response;

    }

    function BSBLAN_api($_api, $json_data = '')
    {
        log::add('BSBLAN', 'debug', 'Execute BSBLAN_api  url ' . $_api . ' json ' . $json_data);

        $statuscmd = $this->getCmd(null, 'status');


        $json = $this->https_file_get_contents($_api, $json_data);
        log::add('BSBLAN', 'debug', 'Request ' . $_api, ' json ' . $json);

        $obj = json_decode($json, TRUE);
        log::add('BSBLAN', 'debug', 'Detail data : ' . print_r($obj, true));

        return $obj;
    }

    public function create_command($id_commande, $info, $action, $refresh)
    {
        log::add('BSBLAN', 'info', __('create_command', __FILE__) . ' ' . $this->name . ' Commande ' . $id_commande . ' Info ' . $info . ' Action ' . $action . ' Refresh ' . $refresh);

        //    $carte->getSessionId();
        if ($info != '') {
            $this->create_info_command($id_commande);
        }
        if ($action != '') {
            $this->create_action_command($id_commande);
        }
        if ($refresh != '') {
            $this->create_refresh_command($id_commande);
        }
    }

    private function create_info_command($item_id)
    // crée la commande type info
    {
        if (is_object(cmd::byEqLogicIdAndLogicalId($this->id, $item_id))) {
            log::add('BSBLAN', 'info', __('create_info_command ', __FILE__) . $this->name . '  commande déjà créée ' . $item_id);
            return '0';
        }

        // lit la description du datapoint
        $obj_detail = $this->BSBLAN_api('JC=' . $item_id);

        if (isset($obj_detail["$item_id"]['name'])) {

            $name = $item_id . ' ' . str_replace(array('&', '#', ']', '[', '%', "'"), ' ', $obj_detail["$item_id"]['name']);
            if ($name == '') {
                $name = $item_id;
            }
            log::add('BSBLAN', 'info', __('create_info_command ', __FILE__) . $this->name . '  création commande ' . $name);

            $cmd = new BSBLANCmd();

            // BD: pour éviter les problèmes de conversion par exemple quand le nom contient le caractere /
            $cmd->setName($name);
            $name = $cmd->getName();

            // teste si le nom de la commande est déjà attribué
            // si oui, ajoute à la fin un numéro afin d'avoir un nom unique
            if (is_object(cmd::byEqLogicIdCmdName($this->id, $name))) {
                $count = 1;
                while (is_object(cmd::byEqLogicIdCmdName($this->id, substr($name, 0, 40) . "..." . $count))) {
                    $count++;
                }
                $cmd->setName(substr($name, 0, 40) . "..." . $count);
                log::add('BSBLAN', 'info', 'Rename as ' . substr($name, 0, 40) . "..." . $count);
            } else {
                $cmd->setName($name);
            }
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
            $cmd->setLogicalId($item_id); // le logical id est égal à l'id du datapoint
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

        } else {

        }
    }

    private function create_refresh_command($item_id)
    // crée la commande type refresh
    {

        if (is_object(cmd::byEqLogicIdAndLogicalId($this->id, 'R_' . $item_id))) {
            log::add('BSBLAN', 'info', __('create_refresh_command ', __FILE__) . $this->name . '  commande refresh déjà créée ' . 'R_' . $item_id);
            return '0';
        }

        // lit la description du datapoint
        $obj_detail = $this->BSBLAN_api('JC=' . $item_id);

        if (isset($obj_detail["$item_id"]['name'])) {

            $name = $item_id . ' ' . str_replace(array('&', '#', ']', '[', '%', "'"), ' ', $obj_detail["$item_id"]['name']);
            if ($name == '') {
                $name = $item_id;
            }

            $name = $name . ' Refresh';

            log::add('BSBLAN', 'info', __('create_refresh_command ', __FILE__) . $this->name . '  création commande ' . $name);

            $cmd = new BSBLANCmd();

            // BD: pour éviter les problèmes de conversion par exemple quand le nom contient le caractere /
            $cmd->setName($name);
            $name = $cmd->getName();

            // teste si le nom de la commande est déjà attribué    
            // si oui, ajoute à la fin un numéro afin d'avoir un nom unique
            if (is_object(cmd::byEqLogicIdCmdName($this->id, $name))) {
                $count = 1;
                while (is_object(cmd::byEqLogicIdCmdName($this->id, substr($name, 0, 40) . "..." . $count))) {
                    $count++;
                }
                $cmd->setName(substr($name, 0, 40) . "..." . $count);
                log::add('BSBLAN', 'info', 'Rename as ' . substr($name, 0, 40) . "..." . $count);
            } else {
                $cmd->setName($name);
            }
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('R_' . $item_id); // le logical id est égal à 'R_' plus l'id du datapoint
            $cmd->setConfiguration('infoId', $item_id);
            $cmd->setIsVisible(1);
            $cmd->setOrder(time());
            $cmd->setConfiguration('internal_type', $type);
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->save();
        }
    }


    private function create_action_command($item_id)
    // crée la commande type action
    {

        if (is_object(cmd::byEqLogicIdAndLogicalId($this->id, 'A_' . $item_id))) {
            log::add('BSBLAN', 'info', __('create_action_command ', __FILE__) . $this->name . '  commande action déjà créée ' . 'A_' . $item_id);
            return '0';
        }


        // lit la description du datapoint
        $obj_detail = $this->BSBLAN_api('JC=' . $item_id);

        if (isset($obj_detail["$item_id"]['name'])) {

            $name = $item_id . ' ' . str_replace(array('&', '#', ']', '[', '%', "'"), ' ', $obj_detail["$item_id"]['name']);
            if ($name == '') {
                $name = $item_id;
            }

            $name = $name . ' Action';

            log::add('BSBLAN', 'info', __('create_action_command ', __FILE__) . $this->name . '  création commande ' . $name);

            $cmd = new BSBLANCmd();

            // BD: pour éviter les problèmes de conversion par exemple quand le nom contient le caractere /
            $cmd->setName($name);
            $name = $cmd->getName();

            // teste si le nom de la commande est déjà attribué    
            // si oui, ajoute à la fin un numéro afin d'avoir un nom unique
            if (is_object(cmd::byEqLogicIdCmdName($this->id, $name))) {
                $count = 1;
                while (is_object(cmd::byEqLogicIdCmdName($this->id, substr($name, 0, 40) . "..." . $count))) {
                    $count++;
                }
                $cmd->setName(substr($name, 0, 40) . "..." . $count);
                log::add('BSBLAN', 'info', 'Rename as ' . substr($name, 0, 40) . "..." . $count);
            } else {
                $cmd->setName($name);
            }

            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('A_' . $item_id); // le logical id est égal à 'A_' plus l'id du datapoint
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
        }
    }



    public function preInsert()
    {
        if ($this->getConfiguration('type', '') == "") {
            $this->setConfiguration('type', 'BSBLAN');
        }
    }

    public function preUpdate()
    {
        if ($this->getIsEnable()) {
            //    return $this->getSessionId();
        }
    }

    public function preSave()
    {
        if ($this->getIsEnable()) {
            //    return $this->getSessionId();
        }
    }

    public function preRemove()
    {

        return true;
    }


    public function postInsert()
    {
        $this->postUpdate();
    }

    public function postUpdate()
    {

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

    }



    public function cron()
    {
        log::add('BSBLAN', 'info', 'Lancement de cron');
        BSBLAN::cron_update(__FUNCTION__);
    }
    public function cron5()
    {
        sleep(5);
        log::add('BSBLAN', 'info', 'Lancement de cron5');
        BSBLAN::cron_update(__FUNCTION__);
    }
    public function cron10()
    {
        sleep(10);
        log::add('BSBLAN', 'info', 'Lancement de cron10');
        BSBLAN::cron_update(__FUNCTION__);
    }
    public function cron15()
    {
        sleep(15);
        log::add('BSBLAN', 'info', 'Lancement de cron15');
        BSBLAN::cron_update(__FUNCTION__);
    }
    public function cron30()
    {
        sleep(20);
        log::add('BSBLAN', 'info', 'Lancement de cron30');
        BSBLAN::cron_update(__FUNCTION__);
    }

    public function cronHourly()
    {
        sleep(25);
        log::add('BSBLAN', 'info', 'Lancement de cronHourly');
        BSBLAN::cron_update(__FUNCTION__);
    }

    public function cronDaily()
    {
        sleep(30);
        log::add('BSBLAN', 'info', 'Lancement de cronDaily');
        BSBLAN::cron_update(__FUNCTION__);
    }
    public function cron_update($_cron)
    {
        foreach (eqLogic::byTypeAndSearchConfiguration('BSBLAN', '"type":"BSBLAN"') as $eqLogic) {
            if ($eqLogic->getIsEnable()) {
                log::add('BSBLAN', 'info', 'cron Refresh Info Appareil : ' . $eqLogic->name);
                foreach ($eqLogic->getCmd() as $cmd) {
                    if (is_numeric($cmd->getLogicalId()) && $cmd->getConfiguration('isCollected') == 1 && $cmd->getConfiguration('cron') == $_cron) {
                        if ($eqLogic->refresh_info_cmd($cmd) == true) {
                            $eqLogic_refresh_cmd = $eqLogic->getCmd(null, 'updatetime');
                            $eqLogic_refresh_cmd->event(date("d/m/Y H:i", (time())));
                        }
                    }
                }
            }
        }
    }
    function refresh_info_cmd($_cmd)
    {
        log::add('BSBLAN', 'debug', 'Read datapoint ' . $_cmd->getLogicalId() . ' ' . $_cmd->getName());
        $item_id = $_cmd->getLogicalId();
        $obj_detail = $this->BSBLAN_api('JQ=' . $item_id);
        if (isset($obj_detail["$item_id"]['name'])) {
            log::add('BSBLAN', 'info', 'Read de ' . $item_id . ' ' . $_cmd->getName() . ' --> ' . $obj_detail["$item_id"]['value'] . ' ' . $obj_detail["$item_id"]['desc']);
            $value = $obj_detail["$item_id"]['value'];
            if (isset($obj_detail["$item_id"]['desc'])) {
                if ($obj_detail["$item_id"]['desc'] != '') {
                    $value = $obj_detail["$item_id"]['desc'];
                }
            }
            $_cmd->event($value);
            return true;
        } else {
            return false;
        }
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
                    log::add('BSBLAN', 'info', 'Type d action non défini : ' . $this->getSubType());
                    die;
                    break;
            }
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
            log::add('BSBLAN', 'info', __('execute ', __FILE__) . '  url ' . $url . ' json ' . $data_string);
            $obj = $eqLogic->BSBLAN_api($url, $data_string);

            return true;
        }

        //}

        // Commande refresh
        if (substr($this->getLogicalId(), 0, 2) == 'R_') {
            $internalid = substr($this->getLogicalId(), 2); // remove 'R_'

            $cmd = cmd::byEqLogicIdAndLogicalId($eqLogic->getId(), $internalid);
            if (!is_object($cmd)) {
                log::add('BSBLAN', 'debug', 'Commande non trouvée ' . $internalid);
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
                if ($this->getLogicalId() == 'updatetime') {
                    return true;
                }
            }
            return false;
        }
    }
}
