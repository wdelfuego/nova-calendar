<?php

namespace Wdelfuego\NovaCalendar;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laravel\Nova\Resource as NovaResource;

class Event
{
    public static function fromResource(NovaResource $resource, string $dateAttribute) : self
    {
        return (new self($resource->title(), $resource->resource->$dateAttribute))->withResource($resource);
    }
            
    protected $name;
    protected $start;
    protected $end;
    protected $notes;
    protected $badges;
    
    protected $novaResource = null;
    protected $displayTime = true;
    protected $url = null;
    
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
            'start' => $this->start->format("H:i"),
            'end' => $this->end ? $this->end->format("H:i") : null,
            'notes' => $this->notes,
            'badges' => $this->badges,
            'url' => $this->url,
            'options' => [
                'displayTime' => $this->displayTime ? 1 : 0
            ]
        ];
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
    
    public function url(string $v = null)
    {
        if(!is_null($v))
        {
            $this->url = $v;
        }
        
        return $this->url;
    }
    
    public function withUrl(string $v)
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
        return $this->novaResource ? $this->novaResource->resource : null;
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
    
    public function addBadges(string ...$v) : self
    {
        foreach($v as $badge)
        {
            $this->badges[] = $badge;            
        }
        
        return $this;
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
