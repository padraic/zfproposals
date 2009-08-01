<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to padraic dot brady at yahoo dot com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Pubsubhubbub
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * NOTE: Interface requires the setting of sufficient data to create a tuple
 * to uniquely identify each entry in a filename. The type is used as a postfix
 * depending on what context the Storage class implementation is being used,
 * e.g. subscription, unsubscription, etc. At a later date, if feasible, can
 * migrate to using Zend_Cache as an alternative - but this interface will
 * remain enforced.
 */

/**
 * @category   Zend
 * @package    Zend_Pubsubhubbub
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Pubsubhubbub_StorageInterface
{

    /**
     * Store data which is associated with the given Hub Server URL and Topic
     * URL and where that data relates to the given Type. The Types supported
     * include: "subscription", "unsubscription". These Type strings may also
     * be referenced by constants on the Zend_Pubsubhubbub class.
     *
     * @param string|integer $data
     * @param string $hubUrl The Hub Server URL
     * @param string $topicUrl The Topic (RSS or Atom feed) URL
     * @param string $type
     */
    public function store($data, $type, $topicUrl, $hubUrl = null);

    /**
     * Get data which is associated with the given Hub Server URL and Topic
     * URL and where that data relates to the given Type. The Types supported
     * include: "subscription", "unsubscription". These Type strings may also
     * be referenced by constants on the Zend_Pubsubhubbub class.
     *
     * @param string $hubUrl The Hub Server URL
     * @param string $topicUrl The Topic (RSS or Atom feed) URL
     * @param string $type
     * @return string
     */
    public function get($type, $topicUrl, $hubUrl = null);

    /**
     * Checks for the existence of a record agreeing with the given parameters
     *
     * @param string $hubUrl The Hub Server URL
     * @param string $topicUrl The Topic (RSS or Atom feed) URL
     * @param string $type
     * @return bool
     */
    public function exists($type, $topicUrl, $hubUrl = null);

    /**
     * If implemented: deletes all records for any given valid Type
     *
     * @param string $type
     */
    public function cleanup($type);

}
