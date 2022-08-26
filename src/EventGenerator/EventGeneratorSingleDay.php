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

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laravel\Nova\Resource as NovaResource;

use Wdelfuego\Nova\DateTime\Filters\BeforeOrOnDate;
use Wdelfuego\Nova\DateTime\Filters\AfterOrOnDate;
use Wdelfuego\NovaCalendar\Interface\CalendarDataProviderInterface;
use Wdelfuego\NovaCalendar\Event;

class EventGeneratorSingleDay extends EventGenerator
{
    public function generateEvents() : array
    {
        $novaResourceClass = $this->novaResourceClass;
        $eloquentModelClass = $novaResourceClass::$model;
        
        $afterFilter = new AfterOrOnDate('', $this->toEventSpec);
        $beforeFilter = new BeforeOrOnDate('', $this->toEventSpec);

        // Since these are single-day events by definition, we only query for the models
        // that have the date attribute within the current calendar range
        $models = $eloquentModelClass::orderBy($this->toEventSpec);
        $models = $afterFilter->modulateQuery($models, $this->dataProvider->startOfCalendar());
        $models = $beforeFilter->modulateQuery($models, $this->dataProvider->endOfCalendar());

        $out = [];
        foreach($models->cursor() as $model)
        {
            $out[] = $this->resourceToEvent(new $novaResourceClass($model), $this->toEventSpec);
        }
        
        return $out;
    }
    
    protected function resourceToEvent(NovaResource $resource, string $dateAttributeStart) : Event
    {
        return Event::fromResource($resource, $dateAttributeStart);
    }
}
