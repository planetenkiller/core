<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_ServiceManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Proxy to route all method calls to ModUtil::apiFunc call.
 */
class Zikula_ServiceManager_Loader_Xml_ModuleNamespaceHandler_Proxy
{
    private $module;
    private $type;

    public function __construct($module, $type) {
        $this->module = $module;
        $this->type = $type;
    }

    public function __call($func, $arguments) {
        return ModUtil::apiFunc($this->module, $this->type, $func, (count($arguments) == 1 && is_array($arguments[0]))? $arguments[0] : $arguments);
    }
}

