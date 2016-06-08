<?php namespace App\Exceptions;

/**
 * An exception that should be thrown if a Eloquent relationship is required to be loaded prior to
 * calling a method.
 *
 * @package App\Exceptions
 * @since 0.0.1
 */
class NonLoadedRelationException extends \Exception
{

}