<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Session
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Storage interface.
 */
interface Zikula_Session_StorageInterface
{
    /**
     * Start session.
     *
     * @return void
     */
    function start();

    /**
     * Expire this session gracefully.
     *
     * @return void
     */
    function expire();

    /**
     * Regenerate session.
     *
     * @param boolean $delete Whether to delete the session or leave it to GC.
     *
     * @return boolean
     */
    function regenerate($delete = false);

    /**
     * Open session.
     *
     * @param string $savePath    Save path.
     * @param string $sessionName Session Name.
     *
     * @return boolean
     */
    function open($savePath, $sessionName);

    /**
     * Close session.
     *
     * @return boolean
     */
    function close();

    /**
     * Read session.
     *
     * @param string $sessionId Session ID.
     *
     * @return boolean
     */
    function read($sessionId);

    /**
     * Commit session to storage.
     *
     * @param string $sessionId Session ID.
     * @param mixed  $vars      Variables to write.
     *
     * @return boolean
     */
    function write($sessionId, $vars);

    /**
     * Destroy this session.
     *
     * @param string $sessionId Session ID.
     *
     * @return void
     */
    function destroy($sessionId);

    /**
     * Garbage collection for storage.
     *
     * @param integer $lifetime Max lifetime to keep session stored.
     *
     * @return boolean
     */
    function gc($lifetime);
}
