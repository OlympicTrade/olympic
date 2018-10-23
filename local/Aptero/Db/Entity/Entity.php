<?php
/**
 * Example
 * //Create entity and set properties
 * $entity = new Entity();
 * $entity->addProperties(array(
 *    array('name' => 'name'),
 *    array('name' => 'surname'),
 *    array('name' => 'gender'),
 * ));
 *
 * $entity->name = 'roman';
 * $entity->role = 'master';
 * $entity->save();
 * $entity->remove();
 */

namespace Aptero\Db\Entity;

use Aptero\Cache\CacheAwareInterface;
use Aptero\Db\AbstractDb;
use Aptero\Db\Entity\EntityCollection;

use Aptero\Db\Plugin\PluginInterface;
use Aptero\Exception\Exception;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;

use ArrayAccess;
use Iterator;
use Serializable;
use Zend\Json\Json;

class Entity extends AbstractDb implements EventManagerAwareInterface, ArrayAccess, Iterator, Serializable
{
    const PROPERTY_TYPE_TEXT = 1;
    const PROPERTY_TYPE_INT  = 2;
    const PROPERTY_TYPE_BOOL = 3;

    const EVENT_INITIALIZE  = 'initialize';
    const EVENT_PRE_UPDATE  = 'update.pre';
    const EVENT_POST_UPDATE = 'update.post';
    const EVENT_PRE_INSERT  = 'insert.pre';
    const EVENT_POST_INSERT = 'insert.post';
    const EVENT_PRE_DELETE  = 'delete.pre';
    const EVENT_POST_DELETE = 'delete.post';
    const EVENT_PRE_LOAD    = 'load.pre';
    const EVENT_POST_LOAD   = 'load.post';

    /**
     * @var array
     */
    protected static $types = array(
        self::PROPERTY_TYPE_TEXT,
        self::PROPERTY_TYPE_INT,
        self::PROPERTY_TYPE_BOOL,
    );

    /**
     * @var bool
     */
    protected $saved = false;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var array
     */
    protected  $properties = array();

    /**
     * @var array
     */
    protected $plugins  = array();

    /**
     * @var EventManager
     */
    protected $eventManager;

    public function initialize()
    {
        if(!$this->isInitialized && parent::initialize()) {
            $this->getEventManager()->trigger(self::EVENT_INITIALIZE, $this);
        }

        return false;
    }

    public function addPlugin($name, $obj, $options = array())
    {
        if(array_key_exists($name, $this->plugins)) {
            return false;
        }

        $independent = isset($options['independent']) ?  $options['independent'] : false;

        $this->plugins[$name] = array(
            'object'       => null,
            'factory'      => $obj,
            'independent'  => $independent
        );

        return $this;
    }

    /**
     * @param $name
     * @param array $options
     * @param bool $forced
     * @return mixed
     * @throws Exception
     */
    public function getPlugin($name, $options = [], $forced = false)
    {
        $this->load();

        if(!array_key_exists($name, $this->plugins)) {
            throw new Exception('Plugin "' . $name . '" not found');
        }

        if(empty($this->plugins[$name]['object']) || $forced) {
            $this->plugins[$name]['object'] =
                call_user_func_array (
                    $this->plugins[$name]['factory'],
                    [$this, $options]
                );
        }

        if ($this->getDbAdapter() && $this->plugins[$name]['object'] instanceof AdapterAwareInterface) {
            $this->plugins[$name]['object']->setDbAdapter($this->getDbAdapter());
        }

        if ($this->cache && $this->plugins[$name]['object'] instanceof CacheAwareInterface) {
            $this->plugins[$name]['object']->setCacheAdapter($this->cache);
        }

        if ($this->plugins[$name]['object'] instanceof PluginInterface) {
            $this->plugins[$name]['object']->setParent($this);
        }

        return $this->plugins[$name]['object'];
    }
    
    public function clearPlugin($name)
    {
        $this->plugins[$name]['object'] = null;
    }

    /**
     * @return EntityCollection
     */
    public function getCollection()
    {
        $collection = new EntityCollection();
        $collection->setPrototype(clone $this);

        return $collection;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        if($id != $this->id) {
            $this->clear();
            $this->id = (int) $id;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isSaved()
    {
        return $this->saved;
    }

    /**
     * @param bool $transaction
     * @return bool
     */
    public function save($transaction = true)
    {
        $transaction = $transaction && $this->transaction;

        if($transaction) {
            $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
        }

        $isUpdate = $this->id;

        if ($isUpdate) {
            $commit = !$this->getEventManager()->trigger(self::EVENT_PRE_UPDATE, $this)->contains(false);
        } else {
            $commit = !$this->getEventManager()->trigger(self::EVENT_PRE_INSERT, $this)->contains(false);
        }

        $data = array();

        foreach ($this->properties as $name => $value) {
            if(!$value['updated'] || $value['virtual']) {
                continue;
            }

            $data[$name] = $value['value'];
        }

        if (!$commit && $transaction) {
            $this->getDbAdapter()->getDriver()->getConnection()->rollback();
            return false;
        }

        if(!empty($data)) {
            if ($isUpdate) {
                $update = $this->update();
                $update->where(array($this->primary => $this->getId()));
                $update->set($data);
                $this->execute($update);
            } else {
                if($this->hasProperty('time_create')) {
                    $data['time_create'] = new \Zend\Db\Sql\Expression('NOW()');
                }
                $insert = $this->insert();
                $insert->values($data);
                $this->execute($insert);
                //$this->getEventManager()->trigger(self::EVENT_POST_INSERT, $this);
                $this->id = $this->getDbAdapter()->getDriver()->getLastGeneratedValue();
            }
        }

        foreach($this->plugins as $plugin) {
            if(empty($plugin['object']) || $plugin['independent']) {
                continue;
            }

            $commit = $plugin['object']->save(false);

            if($transaction && !$commit) {
                $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                return false;
            }
        }

        if ($isUpdate) {
            $commit = !$this->getEventManager()->trigger(self::EVENT_POST_UPDATE, $this)->contains(false);
        } else {
            $commit = !$this->getEventManager()->trigger(self::EVENT_POST_INSERT, $this)->contains(false);
        }

        if($transaction) {
            if (!$commit) {
                $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                return false;
            } else {
                $this->getDbAdapter()->getDriver()->getConnection()->commit();
            }
        }

        $this->loaded = true;
        $this->saved = true;

        $this->cacheClear();

        return true;
    }

    /**
     * @param bool $forced
     * @return $this|bool
     */
    public function load($forced = false)
    {
        if($this->loaded) {
            return $this;
        }

        if($this->cacheLoad()) {
            $this->saved = true;
            return $this;
        }
		
        $this->getEventManager()->trigger(self::EVENT_PRE_LOAD, $this)->contains(false);

        $select = clone $this->select();
        $select->limit(1);

        if($this->id) {
            $select->where(array('t.' . $this->primary => $this->id));
        } elseif(!$forced && !$select->where->getPredicates()) {
            return false;
        }

        $result = $this->fetchRow($select);

        if(empty($result)) {
            return false;
        }

        $this->fill($result);

        $this->setId($result[$this->primary]);

        $this->saved = true;

        $this->cacheSave();

        $this->getEventManager()->trigger(self::EVENT_POST_LOAD, $this)->contains(false);

        return $this;
    }

    /**
     * @param bool $transaction
     * @return bool
     */
    public function remove($transaction = true)
    {
        /*if(!$this->id) {
            return false;
        }*/

        $this->load();

        $transaction = $transaction && $this->transaction;

        if($transaction) {
            $this->getDbAdapter()->getDriver()->getConnection()->beginTransaction();
        }

        $commit = !$this->getEventManager()->trigger(self::EVENT_PRE_DELETE, $this)->contains(false);

        if (!$commit) {
            if($transaction) {
                $this->getDbAdapter()->getDriver()->getConnection()->rollback();
            }

            return false;
        }

        foreach($this->plugins as $name => $options) {
            if(empty($options['object'])) {
                continue;
            }

            $plugin = $this->getPlugin($name);

            if($options['independent']) {
                $plugin->clear();
                continue;
            }

            $plugin = $this->getPlugin($name);

            $commit = $plugin->remove(false);

            if($transaction && !$commit) {
                $this->getDbAdapter()->getDriver()->getConnection()->rollback();
                return false;
            }
        }

        $delete = $this->delete();
        $delete->where(array(
            $this->primary => $this->id,
        ));

        $this->execute($delete);

        foreach($this->properties as $property) {
            $property['value'] = '';
            $property['updated'] = false;
        }

        $this->id = 0;

        $commit = !$this->getEventManager()->trigger(self::EVENT_POST_DELETE, $this)->contains(false);

        if ($transaction) {
            if ($commit) {
                $this->getDbAdapter()->getDriver()->getConnection()->commit();
            } else {
                $this->getDbAdapter()->getDriver()->getConnection()->rollback();
            }
        }

        $this->cacheClear();

        return $commit;
    }

    public function clear()
    {
        foreach($this->properties as $key => $property) {
            $this->properties[$key]['value'] = '';
            $this->properties[$key]['updated'] = false;
        }

        foreach($this->plugins as $key => $plugin) {
            $this->plugins[$key]['object'] = null;
        }
        
        $this->clearSelect();

        $this->id = 0;
        $this->loaded = false;
        $this->saved = false;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param $properties
     * @return $this
     */
    public function addProperties($properties)
    {
        foreach($properties as $name => $options) {
            $this->addProperty($name, $options);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array $options
     * @return $this
     * @throws \Aptero\Db\Exception\RuntimeException
     */
    public function addProperty($name, $options = [])
    {
        $default = [
            'name'      => '',
            'type'      => self::PROPERTY_TYPE_TEXT,
            'default'   => '',
            'virtual'   => false, //true - если колонка не существует в базе данных
            'filter'    => null,
            'filterIn'  => null,
            'filterOut' => null,
            'updated'   => false
        ];

        $options = array_merge($default, $options);

        if(array_key_exists($name, $this->properties)) {
            return false;
        }

        if($name == '' || $name == $this->primary) {
            throw new \Aptero\Db\Exception\RuntimeException('Invalid property name "' . $options['name'] . '"');
        }

        $this->properties[$name] = $options;
        $this->properties[$name]['value'] = $options['default'];

        return $this;
    }

    public function __toString()
    {
        return \Zend\Debug\Debug::dump($this->properties, null ,false);
    }

    public function select()
    {
        $this->select = parent::select();

        $cols = array($this->primary);
        foreach($this->properties as $field => $options) {
            if(!$options['virtual']) {
                $cols[] = $field;
            }
        }

        return $this->select;
    }

    /**
     * @param string $property
     * @param $filter
     * @return $this
     */
    public function addPropertyFilterIn($property, $filter) {
        if(isset($this->properties[$property]) && is_callable($filter)) {
            $this->properties[$property]['filterIn'] = $filter;
        }

        return $this;
    }

    /**
     * @param string $property
     * @param $filter
     * @return $this
     */
    public function addPropertyFilterOut($property, $filter) {
        if(isset($this->properties[$property]) && is_callable($filter)) {
            $this->properties[$property]['filterOut'] = $filter;
        }

        return $this;
    }

    public function rFill($data)
    {
        if(empty($data)) {
            return false;
        }

        $dataPlugins = array();
        $dataThis    = array();

        foreach($data as $name => $value) {
            $sepPos = strpos($name, '-');
            $pluginName = substr($name, 0, $sepPos);

            if($pluginName) {
                if(!isset($dataPlugins[$pluginName])) {
                    $dataPlugins[$pluginName] = array();
                }

                $key = substr($name, $sepPos + 1);
                $dataPlugins[$pluginName][$key] = $value;
            } else {
                $dataThis[$name] = $value;
            }
        }

        $this->fill($dataThis);

        foreach($dataPlugins as $pluginName => $pluginData) {
            $this->getPlugin($pluginName)->rFill($pluginData);
        }

        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function fill($data)
    {
        foreach($data as $name => $value) {
            if (!array_key_exists($name, $this->properties)) {
                if($name == $this->primary && $value) {
                    $this->setId($value);
                    $this->loaded = true;
                }
                continue;
            }

            $this->properties[$name]['value']    = $value;

            $this->properties[$name]['updated']  = (bool) !$this->getId();
        }

        return $this;
    }

    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * @param $name
     * @param $value
     * @return Entity
     * @throws \Aptero\Db\Exception\RuntimeException
     */
    public function set($name, $value)
    {
        if($name == 'id') {
            $this->setId($value);
            return $this;
        }

        if (!array_key_exists($name, $this->properties) || $value === null) {
            return $this;
        }

        if($this->properties[$name]['filterIn'] !== null) {
            $value = call_user_func_array($this->properties[$name]['filterIn'], array($this, $value));
        }

        $this->properties[$name]['value']    = $value;
        $this->properties[$name]['updated']  = true;

        return $this;
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function setVariables($variables)
    {
        foreach ($variables as $key => $val) {
            $this->set($key, $val);
        }

        return $this;
    }

    /**
     * @param $name
     * @param bool $clear
     * @return mixed
     */
    public function get($name, $clear = false)
    {
        $this->load();

        if($name == 'id') {
            return $this->getId();
        } else {
            if(!array_key_exists($name, $this->properties)) {
                return null;
            }

            $value = $this->properties[$name]['value'];

            if(!$clear && $this->properties[$name]['filterOut'] !== null) {
                $value = call_user_func_array($this->properties[$name]['filterOut'], array($this, $value));
            }

            return $value;
        }
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __clone()
    {
        $this->clear();
    }

    /**
     * @param EventManagerInterface $events
     * @return $this
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));

        $this->eventManager = $events;

        return $this;
    }

    /**
     * @return EventManager
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->setEventManager(new EventManager());
        }

        return $this->eventManager;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getCacheName($name = '') {
        return 'entity-' . str_replace('_', '-', $this->table()) . ($name ? '-' . $name : '');
    }

    /**
     * @return bool
     */
    protected function cacheLoad()
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $cacheName = $this->getCacheName(crc32($this->getSql()->buildSqlString($this->select())));

        if($data = $this->getCacheAdapter()->getItem($cacheName)) {
            $this->fill($data);
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function cacheSave()
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $cacheName = $this->getCacheName(crc32($this->getSql()->buildSqlString($this->select())));
        $this->getCacheAdapter()->setItem($cacheName, $this->serializeArray());
        $this->getCacheAdapter()->setTags($cacheName, [$this->table()]);

        return true;
    }

    protected function cacheClear()
    {
        if(!$this->getCacheAdapter()) {
            return false;
        }

        $this->getCacheAdapter()->clearByTags([$this->table()]);
        return true;
    }

    /**
     * @param array $data
     */
    public function unserializeArray($data)
    {
        $dataPlugins = array();

        foreach($data as $name => $value) {

            $sepPos = strpos($name, '-');
            $pluginName = substr($name, 0, $sepPos);

            if($pluginName) {
                if(!isset($dataPlugins[$pluginName])) {
                    $dataPlugins[$pluginName] = array();
                }

                $key = substr($name, $sepPos + 1);
                $dataPlugins[$pluginName][$key] = $value;
            } else {
                $this->set($name, $value);
            }
        }

        foreach($dataPlugins as $pluginName => $pluginData) {
            $this->getPlugin($pluginName)->unserializeArray($pluginData);
        }
    }

    /**
     * For admin side forms
     *
     * @param array $result
     * @param string $prefix
     * @param bool $fullSerialize
     * @return array
     */
    public function serializeArray($result = [], $prefix = '', $fullSerialize = true)
    {
        $this->load();
        $result[$prefix . 'id'] = $this->getId();
        foreach($this->properties as $key => $val) {
            $result[$prefix . $key] = $this->get($key);
        }

        if($fullSerialize) {
            foreach (array_keys($this->plugins) as $name) {
                $plugin = $this->getPlugin($name);
                $result = $plugin->serializeArray($result, $prefix . $name . '-');
            }
        }

        return $result;
    }

    /* ArrayAccess */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

    /* Iterator */
    public function rewind()
    {
        $this->load();

        return reset($this->properties);
    }

    public function current()
    {
        $prop = current($this->properties);

        return $prop['value'];
    }

    public function key()
    {
        return key($this->properties);
    }

    public function next()
    {
        return next($this->properties);
    }

    public function valid()
    {
        $key = key($this->properties);
        return ($key !== null && $key !== false);
    }

    /**
     * @return EntityCollection
     */
    static public function getEntityCollection()
    {
        $class = get_called_class();

        $entity = new $class;
        return $entity->getCollection();
    }

    public function serialize($options = []) {
        $options = $options + [
            'fullSerialize' => true
        ];

        return Json::encode($this->serializeArray([], '', $options['fullSerialize']));
    }

    public function unserialize($data) {
        $this->__construct();
        $this->rFill(Json::decode($data));
    }
}