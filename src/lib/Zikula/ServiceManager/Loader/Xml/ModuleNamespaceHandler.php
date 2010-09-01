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
 * Namespace handler for "module" xml namespace http://zikula.org/module/1.0/.
 */
class Zikula_ServiceManager_Loader_Xml_ModuleNamespaceHandler implements Zikula_ServiceManager_Loader_Xml_NamespaceHandler
{
    const XML_NAMESPACE = "http://zikula.org/module/1.0/";

    const NODE_API = "api";

    public function getSchema()
    {
        return dirname(__FILE__).DIRECTORY_SEPARATOR.'/module.xsd';
    }

    public function handleElement(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext)
    {
        switch ($node->localName) {
            case self::NODE_API:
                return $this->_parseApi($node, $parseContext);
                break;

            default:
                throw new InvalidArgumentException(get_class($this).' can not handle element with name '.$node->nodeName);
                break;
        }
    }

    private function _parseApi(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext)
    {
        $module = $node->getAttribute('module');
        $type = $node->getAttribute('type');

        return new Zikula_ServiceManager_Definition('Zikula_ServiceManager_Loader_Xml_ModuleNamespaceHandler_Proxy', array($module, $type));
    }
}


