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

class EventGeneratorMultiDay extends EventGenerator
{
    public function generateEvents() : array
    {
        $novaResourceClass = $this->novaResourceClass;
        $eloquentModelClass = $novaResourceClass::$model;
        $dateAttributeStart = $this->toEventSpec[0];
        $dateAttributeEnd = $this->toEventSpec[1];
        
        $afterFilter = new AfterOrOnDate('', $dateAttributeEnd);
        $beforeFilter = new BeforeOrOnDate('', $dateAttributeStart);

        // Since multi-day events are to be included, we have to query for
        // all models..
        $models = $eloquentModelClass::orderBy($dateAttributeStart);
        // a) that start before the end of the calendar, 
        $models = $beforeFilter->modulateQuery($models, $this->dataProvider->endOfCalendar());
        // and that..
        $models = $models->where(function($query) use ($dateAttributeStart, $dateAttributeEnd) {
            //    b) EITHER don't have an end date AND start on or after the calendar start
            $query->where(function($query) use ($dateAttributeStart, $dateAttributeEnd) {
                $query->whereNull($dateAttributeEnd)
                      ->whereDate($dateAttributeStart, '>=', $this->dataProvider->startOfCalendar());
            //    c) OR have an end date that lies on or after the start of the calendar
            })->orWhere(function($query) use ($dateAttributeStart, $dateAttributeEnd) {
                $query->whereDate($dateAttributeEnd, '>=', $this->dataProvider->startOfCalendar());
            });
        });

        $out = [];
        foreach($models->cursor() as $model)
        {
            $out[] = $this->resourceToEvent(new $novaResourceClass($model), $dateAttributeStart, $dateAttributeEnd);
        }

        return $out;
    }
    
    protected function resourceToEvent(NovaResource $resource, string $dateAttributeStart, string $dateAttributeEnd) : Event
    {
        return Event::fromResource($resource, $dateAttributeStart, $dateAttributeEnd);
    }
}
