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
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laravel\Nova\Resource as NovaResource;

use Wdelfuego\NovaCalendar\Event;

abstract class Custom extends NovaEventGenerator
{
    abstract protected function modelQuery(EloquentBuilder $queryBuilder, Carbon $startOfCalendar, Carbon $endOfCalendar) : EloquentBuilder;
    abstract protected function resourceToEvents(NovaResource $resource, Carbon $startOfCalendar, Carbon $endOfCalendar) : array;
    
    public function generateEvents(Carbon $rangeStart, Carbon $rangeEnd) : array
    {
        $novaResourceClass = $this->novaResourceClass();
        $eloquentModelClass = $novaResourceClass::$model;
        $query = $this->modelQuery($eloquentModelClass::query(), $rangeStart, $rangeEnd);
        
        $out = [];
        foreach($query->cursor() as $model)
        {
            $resource = new $novaResourceClass($model);
            foreach($this->resourceToEvents($resource, $rangeStart, $rangeEnd) as $event)
            {
                if(!$event instanceof Event)
                {
                    throw new \Exception(get_class($this) ."::resourceToEvents() returned at least one value that is not a valid calendar Event");
                }
                
                $out[] = $event->withResource($resource);
            }
        }
        return $out;
    }
}
