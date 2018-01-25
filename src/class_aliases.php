<?php

// This file provides a backwards compatibility layer for old non-namespaced
// class names. Realistically this file will not be removed any time soon, but
// the use of non-namespaced classes should be considered depricated.

class_alias('Aether\Aether', 'Aether');
class_alias('Aether\AetherConfig', 'AetherConfig');
class_alias('Aether\Config', 'AetherAppConfig');
class_alias('Aether\ServiceLocator', 'AetherServiceLocator');
class_alias('Aether\Timer', 'AetherTimer');
class_alias('Aether\UrlParser', 'AetherUrlParser');
class_alias('Aether\Vector', 'AetherVector');

class_alias('Aether\Cache\MemcacheDriver', 'AetherCacheMemcache');
class_alias('Aether\Cache\Cache', 'AetherCache');
class_alias('Aether\Cache\FileDriver', 'AetherCacheFile');

class_alias('Aether\Exceptions\AetherException', 'AetherException');
class_alias('Aether\Exceptions\ServiceNotFound', 'AetherServiceNotFoundException');
class_alias('Aether\Exceptions\NoUrlRuleMatch', 'AetherNoUrlRuleMatchException');
class_alias('Aether\Exceptions\ConfigError', 'AetherConfigErrorException');
class_alias('Aether\Exceptions\MissingFile', 'AetherMissingFileException');

class_alias('Aether\Modules\ModuleFactory', 'AetherModuleFactory');
class_alias('Aether\Modules\PendingRender', 'AetherModulePendingRender');
class_alias('Aether\Modules\ModuleHeader', 'AetherModuleHeader');
class_alias('Aether\Modules\Module', 'AetherModule');

class_alias('Aether\Response\JsonCommentFiltered', 'AetherJsonCommentFilteredResponse');
class_alias('Aether\Response\Response', 'AetherResponse');
class_alias('Aether\Response\Action', 'AetherActionResponse');
class_alias('Aether\Response\Jsonp', 'AetherJSONPResponse');
class_alias('Aether\Response\Json', 'AetherJSONResponse');
class_alias('Aether\Response\Text', 'AetherTextResponse');
class_alias('Aether\Response\Xml', 'AetherXMLResponse');

class_alias('Aether\Sections\SectionFactory', 'AetherSectionFactory');
class_alias('Aether\Sections\Section', 'AetherSection');

class_alias('Aether\Templating\SmartyTemplate', 'AetherTemplateSmarty');
class_alias('Aether\Templating\Template', 'AetherTemplate');
