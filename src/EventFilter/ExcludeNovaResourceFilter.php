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
 
namespace Wdelfuego\NovaCalendar\EventFilter;

use Laravel\Nova\Resource as NovaResource;
use Wdelfuego\NovaCalendar\Event;

class ExcludeNovaResourceFilter extends NovaResourceFilter
{
    public function showEvent(Event $event): bool
    {
        foreach($this->novaResourceClasses as $novaResourceClass)
        {
            if($event->hasNovaResource($novaResourceClass))
            {
                return false;
            }
        }
        
        return $this->passesCustomFilter($event);
    }
}
