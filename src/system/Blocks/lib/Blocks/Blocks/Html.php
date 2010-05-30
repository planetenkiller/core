<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 * @author Patric Kellum
 */

class Blocks_Block_Html extends AbstractBase
{
    /**
     * initialise block
     *
     * @author       The Zikula Development Team
     */
    public function init()
    {
        // Security
        SecurityUtil::registerPermissionSchema('HTMLblock::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @author       The Zikula Development Team
     * @return       array       The block information
     */
    public function info()
    {
        return array('module'         => 'Blocks',
                'text_type'      => $this->__('HTML'),
                'text_type_long' => $this->__('HTML'),
                'allow_multiple' => true,
                'form_content'   => true,
                'form_refresh'   => false,
                'show_preview'   => true);
    }

    /**
     * display block
     *
     * @author       The Zikula Development Team
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the rendered bock
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('HTMLblock::', "$blockinfo[title]::", ACCESS_OVERVIEW)) {
            return;
        }

        return BlockUtil::themeBlock($blockinfo);
    }
}