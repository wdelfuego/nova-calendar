<?php

/*
 * © Copyright 2022 · Willem Vervuurt, Studio Delfuego
 * 
 * You can modify, use and distribute this package under one of two licenses:
 * 1. GNU AGPLv3
 * 2. A perpetual, non-revocable and 100% free (as in beer) do-what-you-want 
 *    license that allows both non-commercial and commercial use, under conditions.
 *    See LICENSE.md for details.
 * 
 *    (it boils down to: do what you want as long as you're building and/or
 *     using calendar views, but don't embed this package or a modified version
 *     of it in free or paid-for software libraries and packages aimed at developers).
 */
 
namespace Wdelfuego\NovaCalendar\EventGenerator;


use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laravel\Nova\Resource as NovaResource;

use Wdelfuego\NovaCalendar\Interface\CalendarDataProviderInterface;
use Wdelfuego\NovaCalendar\Event;

abstract class EventGenerator
{
    protected $dataProvider;
    protected $novaResourceClass;
    protected $toEventSpec;
    
    public static function from(CalendarDataProviderInterface $dataProvider, string $novaResourceClass, mixed $toEventSpec) : ?self
    {
        // Check validity of arguments
        if(!is_subclass_of($novaResourceClass, NovaResource::class))
        {
            throw new \Exception("Only Nova Resources can be automatically fetched for event generation ($novaResourceClass is not a Nova Resource)");
        }
        
        $eloquentModelClass = $novaResourceClass::$model;
        if(!is_subclass_of($eloquentModelClass, EloquentModel::class))
        {
            throw new \Exception("$eloquentModelClass is not an Eloquent model");
        }
    
        // Create appropriate EventGenerator
        if(is_string($toEventSpec) || (is_array($toEventSpec) && count($toEventSpec) == 1 && is_string($toEventSpec[0])))
        {
            // If a single string is supplied as the toEventSpec, it is assumed to
            // be the name of a datetime attribute on the underlying Eloquent model
            // that will be used as the starting date/time for a single-day event
            // Support single attributes supplied in an array, too, since it's bound to happen
            return new EventGeneratorSingleDay($dataProvider, $novaResourceClass, is_array($toEventSpec) ? $toEventSpec[0] : $toEventSpec);
        }
        else if(is_array($toEventSpec) && count($toEventSpec) == 2 && is_string($toEventSpec[0]) && is_string($toEventSpec[1]))
        {
            // If an array containing two strings is supplied as the toEventSpec, they are assumed to
            // be the name of two datetime attributes on the underlying Eloquent model
            // that will be used as the start and end datetime for a event
            // that can be either single or multi-day (depending on the values of each model instance)
            return new EventGeneratorMultiDay($dataProvider, $novaResourceClass, $toEventSpec);
        }
        else if(is_callable($toEventSpec))
        {
            return new EventGeneratorCallable($dataProvider, $novaResourceClass, $toEventSpec);
        }
        
        return null;
    }
    
    public function __construct(CalendarDataProviderInterface $dataProvider, string $novaResourceClass, mixed $toEventSpec)
    {
        $this->dataProvider = $dataProvider;
        $this->novaResourceClass = $novaResourceClass;
        $this->toEventSpec = $toEventSpec;
    }

    abstract public function generateEvents() : array;
}
