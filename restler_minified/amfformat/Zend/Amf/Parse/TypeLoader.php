<?php
 require_once 'Zend/Amf/Value/Messaging/AcknowledgeMessage.php'; require_once 'Zend/Amf/Value/Messaging/AsyncMessage.php'; require_once 'Zend/Amf/Value/Messaging/CommandMessage.php'; require_once 'Zend/Amf/Value/Messaging/ErrorMessage.php'; require_once 'Zend/Amf/Value/Messaging/RemotingMessage.php'; final class Zend_Amf_Parse_TypeLoader { public static $callbackClass; public static $classMap = array ( 'flex.messaging.messages.AcknowledgeMessage' => 'Zend_Amf_Value_Messaging_AcknowledgeMessage', 'flex.messaging.messages.ErrorMessage' => 'Zend_Amf_Value_Messaging_AsyncMessage', 'flex.messaging.messages.CommandMessage' => 'Zend_Amf_Value_Messaging_CommandMessage', 'flex.messaging.messages.ErrorMessage' => 'Zend_Amf_Value_Messaging_ErrorMessage', 'flex.messaging.messages.RemotingMessage' => 'Zend_Amf_Value_Messaging_RemotingMessage', 'flex.messaging.io.ArrayCollection' => 'Zend_Amf_Value_Messaging_ArrayCollection', ); protected static $_defaultClassMap = array( 'flex.messaging.messages.AcknowledgeMessage' => 'Zend_Amf_Value_Messaging_AcknowledgeMessage', 'flex.messaging.messages.ErrorMessage' => 'Zend_Amf_Value_Messaging_AsyncMessage', 'flex.messaging.messages.CommandMessage' => 'Zend_Amf_Value_Messaging_CommandMessage', 'flex.messaging.messages.ErrorMessage' => 'Zend_Amf_Value_Messaging_ErrorMessage', 'flex.messaging.messages.RemotingMessage' => 'Zend_Amf_Value_Messaging_RemotingMessage', 'flex.messaging.io.ArrayCollection' => 'Zend_Amf_Value_Messaging_ArrayCollection', ); protected static $_resourceLoader = null; public static function loadType($className) { $class = self::getMappedClassName($className); if(!$class) { $class = str_replace('.', '_', $className); } if (!class_exists($class)) { return "stdClass"; } return $class; } public static function getMappedClassName($className) { $mappedName = array_search($className, self::$classMap); if ($mappedName) { return $mappedName; } $mappedName = array_search($className, array_flip(self::$classMap)); if ($mappedName) { return $mappedName; } return false; } public static function setMapping($asClassName, $phpClassName) { self::$classMap[$asClassName] = $phpClassName; } public static function resetMap() { self::$classMap = self::$_defaultClassMap; } public static function setResourceLoader(Zend_Loader_PluginLoader_Interface $loader) { self::$_resourceLoader = $loader; } public static function addResourceDirectory($prefix, $dir) { if(self::$_resourceLoader) { self::$_resourceLoader->addPrefixPath($prefix, $dir); } } public static function getResourceParser($resource) { if(self::$_resourceLoader) { $type = preg_replace("/[^A-Za-z0-9_]/", " ", get_resource_type($resource)); $type = str_replace(" ","", ucwords($type)); return self::$_resourceLoader->load($type); } return false; } public static function handleResource($resource) { if(!self::$_resourceLoader) { require_once 'Zend/Amf/Exception.php'; throw new Zend_Amf_Exception('Unable to handle resources - resource plugin loader not set'); } try { while(is_resource($resource)) { $resclass = self::getResourceParser($resource); if(!$resclass) { require_once 'Zend/Amf/Exception.php'; throw new Zend_Amf_Exception('Can not serialize resource type: '. get_resource_type($resource)); } $parser = new $resclass(); if(is_callable(array($parser, 'parse'))) { $resource = $parser->parse($resource); } else { require_once 'Zend/Amf/Exception.php'; throw new Zend_Amf_Exception("Could not call parse() method on class $resclass"); } } return $resource; } catch(Zend_Amf_Exception $e) { throw new Zend_Amf_Exception($e->getMessage(), $e->getCode(), $e); } catch(Exception $e) { require_once 'Zend/Amf/Exception.php'; throw new Zend_Amf_Exception('Can not serialize resource type: '. get_resource_type($resource), 0, $e); } } } 