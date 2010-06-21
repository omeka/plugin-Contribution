<?php
/**
 * @version $Id$
 * @author CHNM
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 */
 
class ContributionTypeTable extends Omeka_Db_Table
{
    protected $_alias = 'ct';
    
    protected function _getColumnPairs()
    {
        return array($this->_alias . '.id', $this->_alias . '.alias');
    }
} 