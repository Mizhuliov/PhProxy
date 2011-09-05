<?PHP
/** 
 * Phproxy Client
 * 
 * PHP 5.3
 * 
 * @package   PhProxy_Client
 * @author    Alex Shcneider <alex.shcneider at gmail dot com>
 * @copyright 2010-2011 (c) Alex Shcneider
 * @license   license.txt
 * @link      http://github.com/Shcneider/PhProxy (sources, binares)
 * @link      http://vk.shcneider.in/forum (binares, support)
 * @link      http://alex.shcneider.in/ (author)
 **/


/**
 *  Config manager
 */
final class PhProxy_Config extends PhProxy_Storage_INI {
    
    /**
     * Constructor. Call a parent contruct from PhProxy_Storage_INI
     * 
     * 
     * @param type $file path to config file
     */
    public function __construct($file) 
    {
        PhProxy::event(__CLASS__ . ' new instance with file ['.$file.']!');
        parent::__construct($file);
    }
    
}


?>