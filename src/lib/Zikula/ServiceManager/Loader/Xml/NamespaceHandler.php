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
 * Interface to handle custom namesaces in xml files.
 */
interface Zikula_ServiceManager_Loader_Xml_NamespaceHandler
{
    public function handleElement(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext);

    public function getSchema();
}
