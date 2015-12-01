<?php // vim:set ts=4 sw=4 et:

/**
 * 
 * A list of exceptions for Aether
 * allows to differ between various exceptional errors
 * 
 * Created: 2007-09-28
 * @author Raymond Julin
 * @package aether
 */

class AetherException extends Exception {}
class AetherNoUrlRuleMatchException extends AetherException {}
class AetherMissingFileException extends AetherException {}
class AetherFinalRuleFoundException extends AetherException {}
class AetherConfigErrorException extends AetherException {}
class AetherServiceNotFoundException extends AetherException {}
