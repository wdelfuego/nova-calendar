<?php

namespace Wdelfuego\NovaCalendar;

use DateTimeInterface;

class Event
{
    protected $name;
    protected $start;
    protected $end;
    protected $notes;
    protected $badges;
    
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

    public function toArray() : array
    {
        return [
            'name' => $this->name,
            'time' => $this->start->format("H:i"),
            'timeEnd' => $this->end ? $this->end->format("H:i") : null,
            'notes' => $this->notes,
            'badges' => $this->badges
        ];
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
    
    public function start(DateTimeInterface $v = null) : ?DateTimeInterface
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
    
    public function badges(array $v = null) : array
    {
        if(!is_null($v)) 
        {
            $this->badges = $v;
        }
        
        return $this->badges;
    }
    
    public function withBadges(array $v) : self
    {
        $this->badges($v);
        return $this;
    }
    
    public function addBadge(string $v) : self
    {
        $this->badges[] = $v;
        return $this;
    }
    
    public function removeBadge(string $v) : self
    {
        $this->badges = array_filter($this->badges, function($b) use ($v) {
            return $b != $v;
        });
        return $this;
    }
}
