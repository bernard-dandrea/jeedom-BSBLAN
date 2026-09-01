<?php

// Last Modified : 2026/09/01 06:54:07

/*
 * Copyright (C) 2026 Bernard Dandrea
 * SPDX-License-Identifier: GPL-3.0-or-later
 * https://www.gnu.org/licenses/gpl-3.0.html
 */

require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

if (!defined('__PLUGIN__')) {
    define('__PLUGIN__', 'BSBLAN');
}

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
        $cron = cron::byClassAndFunction(__PLUGIN__, 'update');
        $schedule = '* * * * *';
        if ($_enable == '1') {
            log::add(__PLUGIN__, 'debug', sprintf(__('Activation du cron de %1$s', __FILE__), __PLUGIN__));
            if (!is_object($cron)) {
                $cron = new cron();
                $cron->setClass(__PLUGIN__);
                $cron->setFunction('update');
                $cron->setEnable(1);
                $cron->setDeamon(0);
                $cron->setSchedule($schedule);
                $cron->setTimeout(1);
            } else {
                $cron->setEnable(1);
            }
            $cron->save();
        } else {
            log::add(__PLUGIN__, 'debug', sprintf(__('Désactivation du cron de %1$s', __FILE__), __PLUGIN__));
            if (is_object($cron)) {
                $cron->remove();
            }
        }
    }

    public function test_connexion()
    {
        log::add(__PLUGIN__, 'info', __FUNCTION__ . ' ' . $this->getName());

        $ip = $this->getConfiguration('ip');
        $url_api = 'http://' . $ip . '/';
        $passkey = $this->getConfiguration('passkey');
        if ($passkey != '') {
            $url_api = $url_api . $passkey . '/';
        }
        $url_api = $url_api . 'JI';
        log::add(__PLUGIN__, 'debug', __FUNCTION__ . '  url_api ' . $url_api);

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

            if ($http_code == 200) {
                $obj = json_decode($response, TRUE);
                log::add(__PLUGIN__, 'debug', 'curl_exec response : $http_code ' . $http_code . ' response ' . self::FormatArrayForLog($response));
                if (isset($obj['version'])) {
                    $return = __('Connexion OK : version de BSB-LAN', __FILE__) . ' ' . $obj['version'];
                    log::add(__PLUGIN__, 'debug', $return);
                    return 'OK ' . $return;
                } else {
                    $return = __('Connexion KO : pas un BSBLAN', __FILE__);
                    log::add(__PLUGIN__, 'debug', $return);
                    return 'KO ' . $return;
                }
            } else {
                if ($http_code == 0) {
                    $return = __('Connexion KO : erreur http', __FILE__) . ' ' . $http_code . ' ' . __('Pas de réponse de', __FILE__) . ' ' . $this->getConfiguration('ip');
                    log::add(__PLUGIN__, 'debug', $return);
                    return 'KO ' . $return;
                } else {
                    $return = __('Connexion KO : erreur http', __FILE__) . ' ' . $http_code . ' response --> ' . self::compactHtmlText($response);
                    log::add(__PLUGIN__, 'debug', $return);
                    return 'KO ' . $return;
                }
            }
        } catch (\Throwable $e) {
            $return = __('Connexion KO : exception', __FILE__) .  ' ' . $e->getCode() . ' ' . $e->getMessage();
            return 'KO ' . $return;
        } finally {
            curl_close($ch);
        }
    }

    public function https_file_get_contents($url, $json_data = '')
    {
        log::add(__PLUGIN__, 'info', __FUNCTION__ . '  url ' . $url . ' json ' . $json_data);

        $ip = $this->getConfiguration('ip');
        $url_api = 'http://' . $ip . '/';
        $passkey = $this->getConfiguration('passkey');
        if ($passkey != '') {
            $url_api = $url_api . $passkey . '/';
        }
        $url_api = $url_api . $url;
        log::add(__PLUGIN__, 'debug', __FUNCTION__ . '  url_api ' . $url_api);

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
                if ($http_code == 200) {
                    break;
                }
                log::add(__PLUGIN__, 'warning', 'curl_exec response : http_code ' . $http_code . ' Curl ' . __('erreur', __FILE__) . ': ' . curl_error($ch) . ' -> ' . __('nouvel essai', __FILE__));
                $essai = $essai + 1;
            }

            if ($http_code == 200) {
                log::add(__PLUGIN__, 'debug', 'curl_exec response : http_code ' . $http_code . ' ' . __('réponse', __FILE__) . ' --> ' . self::compactHtmlText($response));
            } else {
                if ($http_code == 0) {
                    $return = __('http erreur', __FILE__) . ' : ' . __('Pas de réponse de', __FILE__) . ' ' . $this->getConfiguration('ip') . ' Curl error: ' . curl_error($ch);
                    log::add(__PLUGIN__, 'error', $return);
                    return false;
                } else {
                    $return = __('http erreur', __FILE__) . ' : ' . $http_code . ' ' . __('réponse', __FILE__) . ' --> ' . self::compactHtmlText($response);
                    log::add(__PLUGIN__, 'debug', $return);
                    return false;
                }
            }
        } catch (\Throwable $e) {
            $return = __('http exception', __FILE__) .  ' ' . $e->getCode() . ' ' . $e->getMessage();
            log::add(__PLUGIN__, 'debug', $return);
            return false;
        } finally {
            curl_close($ch);
        }
        return $response;
    }

    function BSBLAN_api($_api, $json_data = '')
    {
        log::add(__PLUGIN__, 'debug', __FUNCTION__ . ' url ' . $_api . ' json ' . $json_data);

        $json = $this->https_file_get_contents($_api, $json_data);
        if ($json == false)
            return false;
        log::add(__PLUGIN__, 'debug', __FUNCTION__ . ' ' . __('Requete', __FILE__) . ' ' . $_api . ' json ' . self::compactJsonText($json));

        $obj = json_decode($json, TRUE);
        log::add(__PLUGIN__, 'debug', 'Data : ' . self::FormatArrayForLog($obj));

        return $obj;
    }

    public function create_command($id_commande, $info, $action, $refresh)
    {
        log::add(__PLUGIN__, 'info', __FUNCTION__ . ' ' . $this->getName() . ' ' . __('Commande', __FILE__) . ' ' . $id_commande . ' Info ' . $info . ' Action ' . $action . ' Refresh ' . $refresh);

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
            log::add(__PLUGIN__, 'info', __FUNCTION__ . ' ' . $return);
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
            log::add(__PLUGIN__, 'info', __FUNCTION__ . ' ' . $this->getName() . ' ' . __('création commande', __FILE__) . ' ' . $name);

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
            log::add(__PLUGIN__, 'debug', __FUNCTION__ . ' ' . $return);
            return 'OK ' . $return;
        }
    }

    private function create_refresh_command($item_id)
    // crée la commande type refresh
    {

        if (is_object(cmd::byEqLogicIdAndLogicalId($this->id, 'R_' . $item_id))) {
            $return = $this->getName() . '  ' . __('commande refresh déjà créée', __FILE__) . ' ' . $item_id;
            log::add(__PLUGIN__, 'info', __FUNCTION__ . ' ' . $return);
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

            log::add(__PLUGIN__, 'info', __FUNCTION__ . ' ' . $this->getName() . '  ' . __('création commande', __FILE__) . ' ' . $name);

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
            log::add(__PLUGIN__, 'debug', __FUNCTION__ . ' ' . $return);
            return 'OK ' . $return;
        }
    }


    private function create_action_command($item_id)
    // crée la commande type action
    {

        if (is_object(cmd::byEqLogicIdAndLogicalId($this->id, 'A_' . $item_id))) {
            $return = $this->getName() . '  ' . __('commande action déjà créée', __FILE__) . ' ' . $item_id;
            log::add(__PLUGIN__, 'info', __FUNCTION__ . ' ' . $return);
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

            $name = $name . ' Action';

            log::add(__PLUGIN__, 'info', __FUNCTION__ . ' ' . $this->getName() . '  ' . __('création commande', __FILE__) . ' ' . $name);

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
            log::add(__PLUGIN__, 'debug', __FUNCTION__ . ' ' . $return);
            return 'OK ' . $return;
        }
    }

    public function preInsert()
    {
        if ($this->getConfiguration('type', '') == "") {
            $this->setConfiguration('type', __PLUGIN__);
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
        $cron = cron::byClassAndFunction(__PLUGIN__, 'update');
        if (!is_object($cron)) {
            log::add(__PLUGIN__, 'info', __('Lancement de cron', __FILE__));
            BSBLAN::update();
        }
    }

    public static function update()
    {
        log::add(__PLUGIN__, 'info', __('Lancement de', __FILE__) . ' ' . __FUNCTION__);
        //       foreach (eqLogic::byTypeAndSearchConfiguration(__PLUGIN__, '"type":"BSBLAN"') as $eqLogic) {
        foreach (eqLogic::byType(__PLUGIN__) as $eqLogic) {
            log::add(__PLUGIN__, 'info', __('Appel ', __FILE__) . ' BSBLAN_Update appareil : ' . $eqLogic->getName());
            if ($eqLogic->getIsEnable()) {
                BSBLAN::BSBLAN_Update($eqLogic);
            }
        }
    }

    public static function BSBLAN_Update($_eqLogic, $_context = 'cron')
    {
        log::add(__PLUGIN__, 'info', __FUNCTION__ . ' ' . __('Equipement', __FILE__) . ' : ' . $_eqLogic->getName() . ' ' . __('Contexte', __FILE__) . ' ' . $_context);

        $max_errors = $_eqLogic->getConfiguration('max_errors', '3');
        if (is_numeric($max_errors) == false) {
            $max_errors = 3;
        } else {
            if ($max_errors <= 0) {
                $max_errors = 3;
            }
        }

        $error_number = 0;
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
                    if ($_eqLogic->BSBLAN_read_parameter($cmd) == true) {
                        $error_number = 0;
                        $_eqLogic_refresh_cmd = $_eqLogic->getCmd(null, 'updatetime');
                        $_eqLogic->checkAndUpdateCmd($_eqLogic_refresh_cmd, date("d/m/Y H:i", (time())));
                    } else
                        $error_number++;
                }
                if ($error_number >= $max_errors) {
                    log::add(__PLUGIN__, 'error', sprintf(__('Récupération des données de %1$s abandonnée car trop d\'erreurs (%2$s)', __FILE__), $_eqLogic->getName(), $error_number));
                    break;
                }
            }
        }
    }

    function BSBLAN_read_parameter($_cmd)
    {
        log::add(__PLUGIN__, 'debug', sprintf(__('Lecture du paramètre %s %s', __FILE__), $_cmd->getLogicalId(), $_cmd->getName()));
        $item_id = $_cmd->getLogicalId();
        $obj_detail = $this->BSBLAN_api('JQ=' . $item_id);
        if ($obj_detail == false) {
            return false;
        }

        $eqLogic = $_cmd->getEqlogic();
        $IgnoredErrors = $eqLogic->getConfiguration("IgnoredErrors", ''); //   exemple "260" -> Type de donnée inconnu (unknown type)

        // Initialiser le tableau vide par défaut
        $exceptions = [];

        // Si la chaîne n'est pas vide, on la découpe
        if (trim($IgnoredErrors) !== '') {
            $exceptions = array_map('intval', explode(';', $IgnoredErrors));
        }

        $BSBLAN_error = isset($obj_detail["$item_id"]["error"]) ? $obj_detail["$item_id"]["error"] : 0;

        if ($BSBLAN_error != 0 && !in_array($BSBLAN_error, $exceptions)) {
            log::add(__PLUGIN__, 'error', sprintf(__('Erreur de lecture du paramètre %1$s %2$s - code erreur BSBLAN %3$s', __FILE__), $item_id, $_cmd->getName(), $BSBLAN_error));
            return false;
        } else {
            if (isset($obj_detail["$item_id"]['name'])) {
                $value = $obj_detail["$item_id"]['value'];
                if (isset($obj_detail["$item_id"]['desc'])) {
                    if ($obj_detail["$item_id"]['desc'] != '') {
                        $value = $obj_detail["$item_id"]['desc'];
                    }
                }
                log::add(__PLUGIN__, 'info', sprintf(__('Valeur de %1$s %2$s --> %3$s', __FILE__), $item_id, $_cmd->getName(), $value));
                $eqLogic->checkAndUpdateCmd($_cmd, $value);
                return true;
            } else {
                return false;
            }
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
    public static function compactJsonText($value)
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
        log::add(__PLUGIN__, 'info', sprintf(__('Renomme en %1$s', __FILE__), $name));
        return $name;
    }
}
class BSBLANCmd extends cmd
{

    public function execute($_options = null)
    {

        $eqLogic = $this->getEqLogic();
        if (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1) {
            throw new \Exception(__('Equipement desactivé impossible d\'éxecuter la commande :', __FILE__) . $this->getHumanName());
        }

        // Refresh toutes les infos
        if ($this->getLogicalId() == 'refresh') {
            log::add(__PLUGIN__, 'info', __('execute ', __FILE__) . '  refresh');
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
                    log::add(__PLUGIN__, 'info', __('Type d\'action non défini', __FILE__) . ' : ' . $this->getSubType());
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
            log::add(__PLUGIN__, 'info', __('execute ', __FILE__) . '  url ' . $url . ' json ' . $data_string);
            $obj = $eqLogic->BSBLAN_api($url, $data_string);
            if ($obj == false) {
                return false;
            }
            return true;
        }

        // Commande refresh
        if (substr($this->getLogicalId(), 0, 2) == 'R_') {
            $internalid = substr($this->getLogicalId(), 2); // remove 'R_'

            $cmd = cmd::byEqLogicIdAndLogicalId($eqLogic->getId(), $internalid);
            if (!is_object($cmd)) {
                log::add(__PLUGIN__, 'debug', __('Commande non trouvée', __FILE__) . ' ' . $internalid);
                return false;
            }
            return $eqLogic->BSBLAN_read_parameter($cmd);
        }
    }

    public function dontRemoveCmd()
    {
        $eqLogic = $this->getEqLogic();
        if (is_object($eqLogic)) {
            if ($this->getLogicalId() == 'updatetime' || $this->getLogicalId() == 'refresh') {
                return true;
            }
            return false;
        }
    }
}
