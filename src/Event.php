<?php

/*
 * © Copyright 2022 · Willem Vervuurt, Studio Delfuego
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, 
 * and/or sell copies of the Software, and to permit persons to whom the 
 * Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included 
 * in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL 
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
 * THE SOFTWARE.
 * 
 * YOU ASSUME ALL RISK ASSOCIATED WITH THE INSTALLATION AND USE OF THE SOFTWARE. 
 * LICENSE HOLDERS ARE SOLELY RESPONSIBLE FOR DETERMINING THE APPROPRIATENESS OF 
 * USE AND ASSUME ALL RISKS ASSOCIATED WITH ITS USE, INCLUDING BUT NOT LIMITED TO
 * THE RISKS OF PROGRAM ERRORS, DAMAGE TO EQUIPMENT, LOSS OF DATA OR SOFTWARE 
 * PROGRAMS, OR UNAVAILABILITY OR INTERRUPTION OF OPERATIONS.
 */
 
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
    protected $style = null;
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

    public function toArray() : array
    {
        return [
            'name' => $this->name,
            'start_date' => $this->start->format("Y-m-d"),
            'start_time' => $this->start->format($this->timeFormat),
            'end_date' => $this->end ? $this->end->format("Y-m-d") : null,
            'end_time' => $this->end ? $this->end->format($this->timeFormat) : null,
            'notes' => $this->notes,
            'badges' => $this->badges,
            'url' => $this->url,
            'options' => [
                'style' => $this->style,
                'displayTime' => $this->displayTime ? 1 : 0,
            ],
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
    
    public function hasNovaResource(string $class = null) : bool
    {
        if(is_null($class))
        {
            return !is_null($this->novaResource);
        }

        return ($this->novaResource instanceof $class);
    }
    
    public function style(string $v = null)
    {
        if(!is_null($v))
        {
            $this->style = $v;
        }
        
        return $this->style;
    }
    
    public function withStyle(string $v)
    {
        $this->style($v);
        return $this;
    }
    
    
    public function timeFormat(string $v = null)
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
