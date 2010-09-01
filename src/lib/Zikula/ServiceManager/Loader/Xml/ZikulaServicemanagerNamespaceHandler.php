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
 * Namespace handler for main xml namespace http://zikula.org/servicemanager/1.0/.
 */
class Zikula_ServiceManager_Loader_Xml_ZikulaServicemanagerNamespaceHandler implements Zikula_ServiceManager_Loader_Xml_NamespaceHandler
{
    const XML_NAMESPACE = "http://zikula.org/servicemanager/1.0/";

    const NODE_OBJECT = "object";
    const NODE_VALUE = "value";
    const NODE_REF = "ref";
    const NODE_MAP = "map";
    const NODE_LIST = "list";

    public function getSchema()
    {
        return dirname(__FILE__).DIRECTORY_SEPARATOR.'/schema.xsd';
    }

    public function handleElement(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext)
    {
        $this->_checkNamespace($node);

        switch ($node->localName) {
            case self::NODE_OBJECT:
                return $this->_parseObject($node, $parseContext);
                break;
            case self::NODE_VALUE:
                return $this->_parseValue($node, $parseContext);
                break;
            case self::NODE_REF:
                return $this->_parseRef($node, $parseContext);
                break;
            case self::NODE_MAP:
                return $this->_parseMap($node, $parseContext);
                break;
            case self::NODE_LIST:
                return $this->_parseList($node, $parseContext);
                break;

            default:
                throw new InvalidArgumentException(get_class($this).' can not handle element with name '.$node->nodeName);
                break;
        }
    }

    private function _parseChildObjects(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext)
    {
        $parsedNodes = array();

        $list = Zikula_ServiceManager_Loader_XmlLoader::getChildElements($node);
        foreach ($list as $snode) {
            $ret = $parseContext->handleElementViaNamespaceHandler($snode);

            // convert anonymous service to definition
            if ($ret instanceof Zikula_ServiceManager_Service && !$ret->getId() && $ret->getDefinition()) {
                $ret = $ret->getDefinition();
            }

            $parsedNodes[] = $ret;
        }

        return $parsedNodes;
    }

    private function _parseObject(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext)
    {
        $id = $node->getAttribute('id');
        $className = $node->getAttribute('class');
        $singleton = strtolower($node->getAttribute('singleton'));
        $singleton = empty($singleton) || $singleton == 'true'? true : ($singleton == 'false'? false : true);
        $constructorArgs = array();
        $methods = array();

        $constructNode = Zikula_ServiceManager_Loader_XmlLoader::getChildElementsByTagName($node, 'construct');
        if (count($constructNode) == 1) {
            $constructorArgs = $this->_parseChildObjects($constructNode[0], $parseContext);
        }

        $callNodes = Zikula_ServiceManager_Loader_XmlLoader::getChildElementsByTagName($node, 'call');
        foreach ($callNodes as $callNode) {
            $callNodeParameter = $this->_parseChildObjects($callNode, $parseContext);

            $methods[$callNode->getAttribute('name')] = $callNodeParameter;
        }


        $propertyNodes = Zikula_ServiceManager_Loader_XmlLoader::getChildElementsByTagName($node, 'property');
        foreach ($propertyNodes as $propertyNode) {
            $name = $propertyNode->getAttribute('name');
            $ref = $propertyNode->getAttribute('ref');
            $value = $propertyNode->getAttribute('value');

            if ($ref) {
                $methods['set'.ucwords($name)] = array(new Zikula_ServiceManager_Service($ref));
            } else if($value) {
                $methods['set'.ucwords($name)] = array($value);
            } else {
                $propertyNodeArgs = Zikula_ServiceManager_Loader_XmlLoader::getChildElements($propertyNode);
                $parameter = array();
                if (count($propertyNodeArgs) == 1) {
                    $parameter = $parseContext->handleElementViaNamespaceHandler($propertyNodeArgs[0]);
                } else {
                    throw new InvalidArgumentException('Property '.$name.' of object with id '.$id.' must have an value');
                }

                $methods['set'.ucwords($name)] = array($parameter);
            }
        }

        $definition = new Zikula_ServiceManager_Definition($className, $constructorArgs, $methods);
        return new Zikula_ServiceManager_Service($id, $definition, $singleton);
    }

    private function _parseValue(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext)
    {
        return $node->nodeValue;
    }

    private function _parseRef(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext)
    {
        return new Zikula_ServiceManager_Service($node->getAttribute('id'));
    }

    private function _parseMap(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext)
    {
        $map = array();

        $mapEntryNodes = Zikula_ServiceManager_Loader_XmlLoader::getChildElementsByTagName($node, 'entry');
        foreach ($mapEntryNodes as $mapEntryNode) {
            $key = $mapEntryNode->getAttribute('key');
            $ref = $mapEntryNode->getAttribute('ref');
            $value = $mapEntryNode->getAttribute('value');

            if ($ref) {
                $map[$key] = new Zikula_ServiceManager_Service($ref);
            } else if($value) {
                $map[$key] = $value;
            } else {
                $value = null;

                $childNodes = Zikula_ServiceManager_Loader_XmlLoader::getChildElements($mapEntryNode);;
                if (count($childNodes) == 1) {
                    $value = $parseContext->handleElementViaNamespaceHandler($childNodes[0]);
                } else {
                    throw new LogicException('Map entry node must conain exactly one sub element');
                }

                $map[$key] = $value;
            }
        }

        return $map;
    }

    private function _parseList(DOMElement $node, Zikula_ServiceManager_Loader_Xml_ParseContext $parseContext)
    {
        $list = $this->_parseChildObjects($node, $parseContext);
        
        return $list;
    }

    private function _checkNamespace(DOMElement $node)
    {
        if ($node->namespaceURI != self::XML_NAMESPACE) {
            throw new InvalidArgumentException("The xml node ".$node->localName." (namespace: '".
                            $node->namespaceURI."') is not in the namespace '".self::XML_NAMESPACE."'");
        }
    }
}
