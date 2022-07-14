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
 
namespace Wdelfuego\NovaCalendar;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laravel\Nova\Resource as NovaResource;

class Event
{
    public static function fromResource(NovaResource $resource, string $dateAttributeStart, string $dateAttributeEnd = null) : self
    {
        return is_null($dateAttributeEnd)
            ? (new self($resource->title(), $resource->model()->$dateAttributeStart))->withResource($resource)
            : (new self($resource->title(), $resource->model()->$dateAttributeStart, $resource->model()->$dateAttributeEnd))->withResource($resource);
    }
    
    protected $name;
    protected $timezone = null;
    protected $start;
    protected $end;
    protected $notes;
    protected $badges;
    
    protected $novaResource = null;
    protected $displayTime = false;
    protected $url = null;
    protected $styles = [];
    protected $timeFormat = 'H:i';
    
    public function __construct(
        string $name, 
        DateTimeInterface $start,
        DateTimeInterface $end = null, 
        string $notes = '', 
        array $badges = [])
    {
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->notes = $notes;
        $this->badges = $badges;
    }

    public function toArray(Carbon $displayDate, Carbon $startOfRange, Carbon $endOfRange, int $firstDayOfWeek) : array
    {
        return [
            'name' => $this->name,
            'startDate' => $this->start->format("Y-m-d"),
            'startTime' => $this->start->format($this->timeFormat),
            'endDate' => $this->end ? $this->end->format("Y-m-d") : null,
            'endTime' => $this->end ? $this->end->format($this->timeFormat) : null,
            'isWithinRange' => $this->touchesRange($startOfRange, $endOfRange),
            'isSingleDayEvent' => $this->isSingleDayEvent() ? 1 : 0,
            'spansDaysN' => min($this->spansDaysFrom($displayDate), 7),
            'startsEvent' => $this->startsEvent($displayDate) ? 1 : 0,
            'endsEvent' => $this->endsEvent($displayDate, $firstDayOfWeek) ? 1 : 0,
            'notes' => $this->notes,
            'badges' => $this->badges,
            'url' => $this->url,
            'options' => [
                'styles' => $this->styles,
                'displayTime' => $this->displayTime ? 1 : 0,
            ],
        ];
    }
    
    public function isSingleDayEvent() : bool
    {
        return !$this->end || $this->end->isSameDay($this->start);
    }
    
    public function touchesRange(Carbon $startOfRange, Carbon $endOfRange)
    {
        return !$this->end 
            ? $this->start->gte($startOfRange) && $this->start->lte($endOfRange)
            : $this->start->lte($endOfRange) && $this->end->gte($startOfRange);
    }
    
    public function spansDaysFrom(Carbon $displayDate) : int
    {
        $out = 1;
        if($this->end)
        {
            $out += $displayDate->diffInDays($this->end);
        }
        return $out;
    }

    public function startsEvent(Carbon $displayDate)
    {
        return $this->start->isSameDay($displayDate);
    }
        
    public function endsEvent(Carbon $displayDate, int $firstDayOfWeek)
    {
        $daysLeft = $this->end() ? $displayDate->diffInDays($this->end) : 0;
        return $daysLeft <= 7 - CalendarDay::weekdayColumn($displayDate, $firstDayOfWeek);
    }
        
    public function resource(NovaResource $v = null) : ?NovaResource
    {
        if(!is_null($v))
        {
            $this->novaResource = $v;
        }
        
        return $this->novaResource;
    }
    
    public function withResource(NovaResource $v) : self
    {
        $this->resource($v);
        return $this;
    }
    
    public function hasNovaResource(string $class = null) : bool
    {
        if(is_null($class))
        {
            return !is_null($this->novaResource);
        }

        return ($this->novaResource instanceof $class);
    }
    
    // Deprecated; here for backwards compatibility with pre-1.2 releases,
    // when only a single style per event was supported
    public function style(string $v = null)
    {
        if(!is_null($v) && count($this->styles) == 0)
        {
            $this->addStyle($v);
        }
        
        if(count($this->styles) > 1)
        {
            throw new \Exception("The deprecated 'Event::style' method is only backwards compatible with events that have zero or one assigned styles. Use the new `Event::addStyle` method instead.");
        }
        else if(count($this->styles) == 0)
        {
            return null;
        }
        
        return $this->styles[0];
    }
    
    // Deprecated; here for backwards compatibility with pre-1.2 releases,
    // when only a single style per event was supported
    public function withStyle(string $v)
    {
        return $this->addStyle($v);
    }
    
    public function url(string $v = null) : ?string
    {
        if(!is_null($v))
        {
            $this->url = $v;
        }
        
        return $this->url;
    }
    
    public function withUrl(string $v) : self
    {
        $this->url($v);
        return $this;
    }
    
    public function displayTime(bool $v = true)
    {
        $this->displayTime = $v;
        return $this;
    }
    
    public function hideTime()
    {
        return $this->displayTime(false);
    }
    
    public function model() : ?EloquentModel
    {
        return $this->novaResource ? $this->novaResource->model() : null;
    }

    public function name(string $v = null) : string
    {
        if(!is_null($v)) 
        {
            $this->name = $v;
        }
        
        return $this->name;
    }
    
    public function withName(string $v) : self
    {
        $this->name($v);
        return $this;
    }
    
    public function timezone(string $v = null) : string
    {
        if(!is_null($v)) 
        {
            $this->timezone = $v;
            $this->start = $this->start->setTimezone($v);
            $this->end = $this->end() ? $this->end->setTimezone($v) : $this->end;
        }
        
        return $this->timezone;
    }
    
    public function withTimezone(string $v) : self
    {
        $this->timezone($v);
        return $this;
    }
    
    public function timeFormat(string $v = null) : string
    {
        if(!is_null($v))
        {
            $this->timeFormat = $v;
        }
        
        return $this->timeFormat;
    }
    
    public function withTimeFormat(string $v)
    {
        $this->timeFormat($v);
        return $this;
    }
    
    public function start(DateTimeInterface $v = null) : DateTimeInterface
    {
        if(!is_null($v)) {
            $this->start = $v;
        }
        
        return $this->start;
    }
    
    public function withStart(DateTimeInterface $v) : self
    {
        $this->start($v);
        return $this;
    }

    public function end(DateTimeInterface $v = null) : ?DateTimeInterface
    {
        if(!is_null($v)) {
            $this->end = $v;
        }
        
        return $this->end;
    }
    
    public function withEnd(DateTimeInterface $v) : self
    {
        $this->end($v);
        return $this;
    }
    
    public function notes(string $v = null) : string
    {
        if(!is_null($v)) 
        {
            $this->notes = $v;
        }
        
        return $this->notes;
    }
    
    public function withNotes(string $v) : self
    {
        $this->notes($v);
        return $this;
    }
    
    public function styles(array $v = null) : array
    {
        if(!is_null($v)) 
        {
            $this->styles = $v;
        }
        
        return $this->styles;
    }
    
    public function addStyle(string $v) : self
    {
        return $this->addStyles($v);
    }
    
    public function addStyles(string ...$v) : self
    {
        foreach($v as $style)
        {
            $this->styles[] = $style;            
        }
        
        return $this;
    }
    
    public function removeStyle(string $v) : self
    {
        return $this->removeStyles($v);
    }
    
    public function removeStyles(string ...$v) : self
    {
        foreach($v as $style)
        {
            $this->styles = array_filter($this->styles, function($s) use ($style) {
                return $s != $style;
            });
        }
        return $this;
    }
    
    public function badges(array $v = null) : array
    {
        if(!is_null($v)) 
        {
            $this->badges = $v;
        }
        
        return $this->badges;
    }
    
    public function addBadge(string $v) : self
    {
        return $this->addBadges($v);
    }
    
    public function addBadges(string ...$v) : self
    {
        foreach($v as $badge)
        {
            $this->badges[] = $badge;            
        }
        
        return $this;
    }
    
    public function removeBadge(string $v) : self
    {
        return $this->removeBadges($v);
    }
    
    public function removeBadges(string ...$v) : self
    {
        foreach($v as $badge)
        {
            $this->badges = array_filter($this->badges, function($b) use ($badge) {
                return $b != $badge;
            });
        }
        return $this;
    }
}
