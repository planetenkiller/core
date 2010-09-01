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
 * Namespace handler for "module" xml namespace http://zikula.org/event/1.0/.
 */
class Zikula_ServiceManager_Loader_Xml_EventNamespaceHandler implements Zikula_ServiceManager_Loader_Xml_NamespaceHandler
{
    const XML_NAMESPACE = "http://zikula.org/event/1.0/";

    const NODE_ATTACH = "attach";
    const NODE_MANAGER = "manager";

    /**
     * @var Zikula_EventManager
     */
    private $eventmanager;

    public function getSchema()
    {
        return dirname(__FILE__).DIRECTORY_SEPARATOR.'/event.xsd';
    }

    public function handleElement(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext)
    {
        switch ($node->localName) {
            case self::NODE_ATTACH:
                return $this->_parseAttach($node, $parseContext);
                break;
            case self::NODE_MANAGER:
                $this->_parseManager($node, $parseContext);
                return null;
                break;

            default:
                throw new InvalidArgumentException(get_class($this).' can not handle element with name '.$node->nodeName);
                break;
        }
    }

    private function _parseAttach(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext)
    {
        $name = $node->getAttribute('name');
        $class = $node->getAttribute('class');
        $ref = $node->getAttribute('ref');
        $method = $node->getAttribute('method');

        if (($class && $ref) || (!$class && !$ref)) {
            throw new InvalidArgumentException('An attach element must have a clas OR an ref attribute');
        }

        if ($this->eventmanager == null) {
            throw new InvalidArgumentException('You need to specify an eventmanager via manager element');
        }

        $em = null;
        if ($class) {
            $this->eventmanager->attach($name, array($class, $method));
        } else {
            $this->eventmanager->attach($name, new Zikula_ServiceHandler($ref, $method));
        }
    }

    private function _parseManager(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext)
    {
        $ref = $node->getAttribute('ref');

        $this->eventmanager = $parseContext->getService($ref);
    }
}


