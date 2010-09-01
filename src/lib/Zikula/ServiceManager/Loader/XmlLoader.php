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
 * Loader for building service manager via xml files.
 */
class Zikula_ServiceManager_Loader_XmlLoader implements Zikula_ServiceManager_Loader
{
    private $namespaceHandler = array();

    const NODE_IMPORT = "import";

    public function __construct() {
        $this->registerNamespaceHandler(Zikula_ServiceManager_Loader_Xml_ZikulaServicemanagerNamespaceHandler::XML_NAMESPACE,
                                        new Zikula_ServiceManager_Loader_Xml_ZikulaServicemanagerNamespaceHandler());

        $this->registerNamespaceHandler(Zikula_ServiceManager_Loader_Xml_ModuleNamespaceHandler::XML_NAMESPACE,
                                        new Zikula_ServiceManager_Loader_Xml_ModuleNamespaceHandler());
        
        $this->registerNamespaceHandler(Zikula_ServiceManager_Loader_Xml_EventNamespaceHandler::XML_NAMESPACE,
                                        new Zikula_ServiceManager_Loader_Xml_EventNamespaceHandler());
    }

    public function load($file, Zikula_ServiceManager $sm)
    {
        $file = realpath($file);

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $current = libxml_use_internal_errors(true);
        if ($dom->load(realpath($file), LIBXML_COMPACT) === false) {
            throw new InvalidArgumentException('Parsing the xml file '.$file.' faild:'.  implode("\n", $this->_getXmlErrors()));
        }
        $dom->normalizeDocument();
        libxml_use_internal_errors($current);

        $this->_validateFile($dom);

        $root = $dom->documentElement;
        $childs = $root->childNodes;
        $parseContext = new Zikula_ServiceManager_Loader_Xml_ParseContext($this->namespaceHandler, $sm);

        for ($i=0; $i < $childs->length; $i++) {
            $child = $childs->item($i);
            
            if (!($child instanceof DOMElement)) {
                continue;
            }

            if ($child->localName == self::NODE_IMPORT
                    && $child->namespaceURI == Zikula_ServiceManager_Loader_Xml_ZikulaServicemanagerNamespaceHandler::XML_NAMESPACE) {
                $relativeFile = $child->getAttribute("file");
                // remove leading / because we need an relative file
                if($relativeFile{0} == '/') {
                    $relativeFile = substr($relativeFile, 1);
                }
                $path = dirname($file) . DIRECTORY_SEPARATOR . $relativeFile;
                if(file_exists($path)) {
                    $this->load($path, $sm);
                } else {
                    throw new InvalidArgumentException('Can not import '.$path.': File not found');
                }
            } else {
                $return = $parseContext->handleElementViaNamespaceHandler($child);
                if ($return instanceof Zikula_ServiceManager_Service) {
                    $sm->registerService($return);
                } else if (is_array($return) && isset($return[0]) && isset($return[0]['id'])
                           && $return[0]['id'] instanceof Zikula_ServiceManager_Argument) {
                    foreach ($return as $argument) {
                        $sm->setArgument($argument['id']->getId(), $argument['value']);
                    }
                } else if(!empty($return)) {
                    throw new LogicException('Invalid return value of namespace handler: '.$return);
                }
            }
        }
    }

    public function registerNamespaceHandler($namespace, Zikula_ServiceManager_Loader_NamespaceHandler $handler)
    {
        $this->namespaceHandler[$namespace] = $handler;
    }

    private function _validateFile(DOMDocument $dom) {
        $imports = '';

        foreach ($this->namespaceHandler as $namespace => $handler) {
            $imports  .= '<xsd:import namespace="'.$namespace.'" schemaLocation="'.$handler->getSchema().'" />'."\n";
        }

        $source = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"
            targetNamespace="http://zikula.org/schema"
            elementFormDefault="qualified">

    $imports
</xsd:schema>
XML;

        $current = libxml_use_internal_errors(true);
        if (!$dom->schemaValidateSource($source)) {
            throw new InvalidArgumentException(implode("\n", $this->_getXmlErrors()));
        }
        libxml_use_internal_errors($current);
        
    }

    private function _getXmlErrors()
    {
        $errors = array();
        
        foreach (libxml_get_errors() as $error) {
            $errors[] = sprintf('[%s %s] %s (in %s - line %d, column %d)',
                LIBXML_ERR_WARNING == $error->level ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ? $error->file : 'n/a',
                $error->line,
                $error->column
            );
        }

        libxml_clear_errors();

        return $errors;
    }

    public static function getChildElements(DOMElement $dom)
    {
        $elementNodes = array();

        $cnodes = $dom->childNodes;
        for ($i = 0; $i < $cnodes->length; $i++) {
            $node = $cnodes->item($i);

            if (!($node instanceof DOMElement)) {
                continue;
            }

            $elementNodes[] = $node;
        }

        return $elementNodes;
    }

    public static function getChildElementsByTagName(DOMElement $dom, $name)
    {
        $elementNodes = array();

        $cnodes = $dom->childNodes;
        for ($i = 0; $i < $cnodes->length; $i++) {
            $node = $cnodes->item($i);

            if (!($node instanceof DOMElement) || $node->localName != $name) {
                continue;
            }

            $elementNodes[] = $node;
        }

        return $elementNodes;
    }
}
