<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */



/**
 * Populate pntables array for modules module
 *
 * This function is called internally by the core whenever the module is
 * loaded. It delivers the table information to the core.
 * It can be loaded explicitly using the ModUtil::dbInfoLoad() API function.
 *
 * @author       Frank Schummertz
 * @return       array       The table information.
 */
function Modules_tables()
{
    // Initialise table array
    $dbtable = array();
    $prefix = System::getVar('prefix');

    // modules module
    $hooks = $prefix . '_hooks';
    $dbtable['hooks'] = $hooks;
    $dbtable['hooks_column'] = array (
            'id'        => 'pn_id',
            'object'    => 'pn_object',
            'action'    => 'pn_action',
            'smodule'   => 'pn_smodule',
            'stype'     => 'pn_stype',
            'tarea'     => 'pn_tarea',
            'tmodule'   => 'pn_tmodule',
            'ttype'     => 'pn_ttype',
            'tfunc'     => 'pn_tfunc',
            'sequence'  => 'pn_sequence');

    // column definition
    $dbtable['hooks_column_def'] = array(
            'id'        => 'I AUTO PRIMARY',
            'object'    => "C(64) NOTNULL DEFAULT ''",
            'action'    => "C(64) NOTNULL DEFAULT ''",
            'smodule'   => "C(64) NOTNULL DEFAULT ''",
            'stype'     => "C(64) NOTNULL DEFAULT ''",
            'tarea'     => "C(64) NOTNULL DEFAULT ''",
            'tmodule'   => "C(64) NOTNULL DEFAULT ''",
            'ttype'     => "C(64) NOTNULL DEFAULT ''",
            'tfunc'     => "C(64) NOTNULL DEFAULT ''",
            'sequence'  => "I NOTNULL DEFAULT 0");

    // additional indexes
    $dbtable['hooks_column_idx'] = array ('smodule'         => 'smodule',
            'smodule_tmodule' => array('smodule', 'tmodule'));

    $modules = $prefix . '_modules';
    $dbtable['modules'] = $modules;
    $dbtable['modules_column'] = array (
            'id'             => 'pn_id',
            'name'           => 'pn_name',
            'type'           => 'pn_type',
            'displayname'    => 'pn_displayname',
            'url'            => 'pn_url',
            'description'    => 'pn_description',
            'directory'      => 'pn_directory',
            'version'        => 'pn_version',
            'contact'        => 'pn_contact',
            'capabilities'   => 'capabilities',
            'state'          => 'pn_state',
            'securityschema' => 'pn_securityschema');

    // column definition
    $dbtable['modules_column_def'] = array (
            'id'             => "I PRIMARY AUTO",
            'name'           => "C(64) NOTNULL DEFAULT ''",
            'type'           => "I1 NOTNULL DEFAULT 0",
            'displayname'    => "C(64) NOTNULL DEFAULT ''",
            'url'            => "C(64) NOTNULL DEFAULT ''",
            'description'    => "C(255) NOTNULL DEFAULT ''",
            'directory'      => "C(64) NOTNULL DEFAULT ''",
            'version'        => "C(10) NOTNULL DEFAULT 0",
            'contact'        => "C(255) NOTNULL DEFAULT ''",
            'capabilities'   => "X NOTNULL DEFAULT ''",
            'state'          => "I2 NOTNULL DEFAULT 0",
            'securityschema' => "X NOTNULL DEFAULT ''");

    // additional indexes
    $dbtable['modules_column_idx'] = array ('state'        => 'state',
            'mod_state'    => array('name', 'state'));

    $module_vars = $prefix . '_module_vars';
    $dbtable['module_vars'] = $module_vars;
    $dbtable['module_vars_column'] = array (
            'id'      => 'pn_id',
            'modname' => 'pn_modname',
            'name'    => 'pn_name',
            'value'   => 'pn_value');

    // column definition
    $dbtable['module_vars_column_def'] = array(
            'id'      => "I PRIMARY AUTO",
            'modname' => "C(64) NOTNULL DEFAULT ''",
            'name'    => "C(64) NOTNULL DEFAULT ''",
            'value'   => "XL");

    // additional indexes
    $dbtable['module_vars_column_idx'] = array ('mod_var' => array('modname', 'name'));


    //$module_dependencies = DBUtil::getLimitedTablename('module_deps');
    $module_deps = $prefix . '_module_deps';
    $dbtable['module_deps'] = $module_deps;
    $dbtable['module_deps_column'] = array (
            'id'          => 'pn_id',
            'modid'       => 'pn_modid',
            'modname'     => 'pn_modname',
            'minversion'  => 'pn_minversion',
            'maxversion'  => 'pn_maxversion',
            'status'      => 'pn_status');

    // column definition
    $dbtable['module_deps_column_def'] = array(
            'id'         => "I4 PRIMARY AUTO",
            'modid'      => "I NOTNULL DEFAULT 0",
            'modname'    => "C(64) NOTNULL DEFAULT ''",
            'minversion' => "C(10) NOTNULL DEFAULT ''",
            'maxversion' => "C(10) NOTNULL DEFAULT ''",
            'status'     => "I1 NOTNULL DEFAULT 0");

    return $dbtable;
}



