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

class NovaResourceFilter extends AbstractEventFilter
{
    protected $novaResourceClasses = [];
    
    public function __construct(string $label, $novaResourceClasses, $customFilter = null)
    {
        if(is_string($novaResourceClasses))
        {
            $novaResourceClasses = [$novaResourceClasses];
        }
        else if(!is_array($novaResourceClasses))
        {
            throw new \InvalidArgumentException("NovaResourceFilter constructor takes a single Nnova resource class as string or an array of them as second argument");
        }
        
        $hashBase = [get_called_class(), $label];
        foreach($novaResourceClasses as $novaResourceClass)
        {
            if(!is_subclass_of($novaResourceClass, NovaResource::class))
            {
                throw new \Exception("A NovaResourceFilter can only filter Nova resources ($novaResourceClass is not a Nova resource)");
            }
            $hashBase[] = preg_replace('/[^a-z]/', '', strtolower($novaResourceClass));
        }
        
        parent::__construct($label);
        $this->setKey(md5(implode('-',$hashBase)));
        $this->novaResourceClasses = $novaResourceClasses;
        $this->setCustomFilter($customFilter);
    }
    
    public function showEvent(Event $event): bool
    {
        foreach($this->novaResourceClasses as $novaResourceClass)
        {
            if($event->hasNovaResource($novaResourceClass))
            {
                return $this->passesCustomFilter($event);
            }
        }
        
        return false;
    }
}
