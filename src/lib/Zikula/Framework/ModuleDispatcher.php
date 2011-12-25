<?php

/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Framework;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ModuleDispatcher
{
    public function dispatch()
    {
        $module = \FormUtil::getPassedValue('module', '', 'GETPOST', FILTER_SANITIZE_STRING);
        $type = \FormUtil::getPassedValue('type', '', 'GETPOST', FILTER_SANITIZE_STRING);
        $func = \FormUtil::getPassedValue('func', '', 'GETPOST', FILTER_SANITIZE_STRING);

        $arguments = array();

        // process the homepage
        if (!$module) {
            // set the start parameters
            $module = \System::getVar('startpage');
            $type = \System::getVar('starttype');
            $func = \System::getVar('startfunc');
            $args = explode(',', \System::getVar('startargs'));

            foreach ($args as $arg) {
                if (!empty($arg)) {
                    $argument = explode('=', $arg);
                    $arguments[$argument[0]] = $argument[1];
                }
            }
        }

        // get module information
        $modinfo = \ModUtil::getInfoFromName($module);

        // we need to force the mod load if we want to call a modules interactive init
        // function because the modules is not active right now
        if ($modinfo) {
            $module = $modinfo['url'];

            if ($type == 'init' || $type == 'interactiveinstaller') {
                \ModUtil::load($modinfo['name'], $type, true);
            }
        }

        $httpCode = 404;
        $message = '';
        $debug = null;
        $return = false;
        $e = null;

        try {
            if (empty($module)) {
                // we have a static homepage
                $return = ' ';
            } elseif ($modinfo) {
                // call the requested/homepage module
                $return = \ModUtil::func($modinfo['name'], $type, $func, $arguments);
            }

            if (!$return) {
                // hack for BC since modules currently use ModUtil::func without expecting exceptions - drak.
                throw new \Zikula\Framework\Exception\NotFoundException(__('Page not found.'));
            }
            $httpCode = 200;
        } catch (\Exception $e) {
            if ($e instanceof \Zikula\Framework\Exception\NotFoundException) {
                $httpCode = 404;
                $message = $e->getMessage();
                $debug = array_merge($e->getDebug(), $e->getTrace());
            } elseif ($e instanceof \Zikula\Framework\Exception\ForbiddenException) {
                $httpCode = 403;
                $message = $e->getMessage();
                $debug = array_merge($e->getDebug(), $e->getTrace());
            } elseif ($e instanceof \Zikula\Framework\Exception\RedirectException) {
                \System::redirect($e->getUrl(), array(), $e->getType());
                \System::shutDown();
            } elseif ($e instanceof \PDOException) {
                $httpCode = 500;
                $message = $e->getMessage();
                if (\System::getVar('Z_CONFIG_USE_TRANSACTIONS')) {
                    $return = __('Error! The transaction failed. Performing rollback.') . $return;
                    $dbConn->rollback();
                } else {
                    $return = __('Error! The transaction failed.') . $return;
                }
            } elseif ($e instanceof \Exception) {
                // general catch all
                $httpCode = 500;
                $message = $e->getMessage();
                $debug = $e->getTrace();
            }
        }

        if ($return instanceof RedirectResponse) {
            return $return;
        }

        if ($return instanceof Response) {
            return $return;
        }

        switch (true) {
            case ($return === true):
                // prevent rendering of the theme.
                \System::shutDown();
                break;

            case ($httpCode == 403):
                if (!\UserUtil::isLoggedIn()) {
                    $url = \ModUtil::url('Users', 'user', 'login', array('returnpage' => urlencode(\System::getCurrentUri())));
                    \LogUtil::registerError(LogUtil::getErrorMsgPermission(), $httpCode, $url);
                    \System::shutDown();
                }
            // there is no break here deliberately.
            case ($return === false):
                if (!\LogUtil::hasErrors()) {
                    \LogUtil::registerError(__f('Could not load the \'%1$s\' module at \'%2$s\'.', array($module, $func)), $httpCode, null);
                }
                $return = \ModUtil::func('Errors', 'user', 'main', array('message' => $message, 'exception' => $e));
                break;

            case ($httpCode == 200):

                break;

            default:
                \LogUtil::registerError(__f('The \'%1$s\' module returned an error in \'%2$s\'.', array($module, $func)), $httpCode, null);
                $return = \ModUtil::func('Errors', 'user', 'main', array('message' => $message, 'exception' => $e));
                break;
        }

        return new Response($return, $httpCode);
    }

}
