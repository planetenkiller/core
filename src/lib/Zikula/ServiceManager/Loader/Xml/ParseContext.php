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
 * Holder for parse informations and usefull helper methods.
 */
class Zikula_ServiceManager_Loader_Xml_ParseContext
{
    private $namespaceHandler = array();
    private $serviceManager;

    public function __construct(array &$namespaceHandler, Zikula_ServiceManager $sm) {
        $this->namespaceHandler = $namespaceHandler;
        $this->serviceManager = $sm;
    }

    public function handleElementViaNamespaceHandler(DOMElement $node)
    {
        if (!isset($this->namespaceHandler[$node->namespaceURI])) {
            throw new LogicException('The namespace '.$node->namespaceURI.' contains no handler');
        }

        return $this->namespaceHandler[$node->namespaceURI]->handleElement($node, $this);
    }

    public function getService($id) {
        return $this->serviceManager->getService($id);
    }
}
