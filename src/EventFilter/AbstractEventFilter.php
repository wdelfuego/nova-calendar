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

use Wdelfuego\NovaCalendar\Contracts\EventFilterInterface;
use Wdelfuego\NovaCalendar\Event;

abstract class AbstractEventFilter implements EventFilterInterface
{
    private string $key = '';
    private string $label = '';
    private bool $isDefault = false;
    private $customFilter = null;
    
    abstract public function showEvent(Event $event): bool;
    
    public function __construct(string $label)
    {
        $this->label = $label;
        $this->setKey(md5(implode('-',[get_called_class(), $label])));
    }

    protected function setKey(string $filterKey) : void
    {
        $this->key = $filterKey;
    }
    
    public function getKey() : string
    {
        if(!strlen(trim($this->key)))
        {
            throw new \Exception("Event filter has no filter key");
        }
        
        return $this->key ?? '';
    }
    
    public function hasKey(string $filterKey) : bool
    {
        return $this->getKey() == $filterKey;
    }
    
    public function getLabel() : string
    {
        return $this->label;
    }
    
    public function useAsDefaultFilter(bool $set = true) : self
    {
        $this->isDefault = $set;
        return $this;
    }
    
    public function isDefaultFilter() : bool
    {
        return $this->isDefault;
    }
    
    public function setCustomFilter($callable)
    {
        $this->customFilter = $callable;
    }

    public function passesCustomFilter(Event $event) : bool
    {
        if($this->customFilter)
        {
            return ($this->customFilter)($event);
        }
        
        return true;
    }
}
