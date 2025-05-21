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

use Wdelfuego\NovaCalendar\Event;

class MultiDay extends NovaEventGenerator
{
    public function generateEvents(Carbon $rangeStart, Carbon $rangeEnd) : array
    {
        $novaResourceClass = $this->novaResourceClass();
        $eloquentModelClass = $novaResourceClass::$model;
        $toEventSpec = $this->toEventSpec();
        
        if(!is_array($toEventSpec) || 2 != count($toEventSpec) || !is_string($toEventSpec[0]) || !is_string($toEventSpec[1]))
        {
            throw new \Exception("Invalid toEventSpec: expected the names of two datetime attributes that start and end the event as an array of exactly two strings");
        }
        
        $dateAttributeStart = $toEventSpec[0];
        $dateAttributeEnd = $toEventSpec[1];

        // Since multi-day events are to be included, we have to query for
        // all models..
        $models = $eloquentModelClass::orderBy($dateAttributeStart);
        // a) that start before the end of the calendar, 
        $models = $models->whereDate($dateAttributeStart, '<=', $rangeEnd);
        // and that..
        $models = $models->where(function($query) use ($dateAttributeStart, $dateAttributeEnd, $rangeStart, $rangeEnd) {
            //    b) EITHER don't have an end date AND start on or after the calendar start
            $query->where(function($query) use ($dateAttributeStart, $dateAttributeEnd, $rangeStart, $rangeEnd) {
                $query->whereNull($dateAttributeEnd)
                      ->whereDate($dateAttributeStart, '>=', $rangeStart);
            //    c) OR have an end date that lies on or after the start of the calendar
            })->orWhere(function($query) use ($dateAttributeStart, $dateAttributeEnd, $rangeStart, $rangeEnd) {
                $query->whereDate($dateAttributeEnd, '>=', $rangeStart);
            });
        });

        $out = [];
        foreach($models->cursor() as $model)
        {
            $out[] = $this->resourceToEvent(new $novaResourceClass($model), $dateAttributeStart, $dateAttributeEnd);
        }

        return $out;
    }
}
