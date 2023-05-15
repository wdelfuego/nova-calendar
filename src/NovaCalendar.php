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

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaCalendar extends Tool
{
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;
    
    private $calendarKey = null;
    private $calendarConfig = [];
    
    private $menuLabel = 'Calendar';
    private $menuIcon = 'calendar';
    
    public function __construct(string $calendarKey)
    {
        if(!isset(config('nova-calendar')[$calendarKey]))
        {
            throw new \Exception("Missing calendar config for calendar key '$calendarKey' in config/nova-calendar.php");
        }
        else if(!isset(config('nova-calendar')[$calendarKey]['provider']) || !trim(config('nova-calendar')[$calendarKey]['provider']))
        {
            throw new \Exception("Invalid config for calendar key '$calendarKey', key 'provider' in config/nova-calendar.php; a data provider class is required");
        }
        else if(!class_exists(config('nova-calendar')[$calendarKey]['provider'], false))
        {
            $setting = config('nova-calendar')[$calendarKey]['provider'];
            throw new \Exception("Invalid config for calendar key '$calendarKey', key 'provider' in config/nova-calendar.php; the supplied data provider class '$setting' does not seem to exist");
        }
        else if(!isset(config('nova-calendar')[$calendarKey]['uri']) || !trim(config('nova-calendar')[$calendarKey]['uri']))
        {
            throw new \Exception("Invalid config for calendar key '$calendarKey', key 'uri' in config/nova-calendar.php; a uri is required");
        }
        
        $this->calendarKey = $calendarKey;
        $this->calendarConfig = config('nova-calendar')[$calendarKey];
        
        if(isset($this->calendarConfig['menu-icon']) && trim($this->calendarConfig['menu-icon']))
        {
            $this->menuIcon = $this->calendarConfig['menu-icon'];
        }
        
        if(isset($this->calendarConfig['menu-label']) && trim($this->calendarConfig['menu-label']))
        {
            $this->menuLabel = $this->calendarConfig['menu-label'];
        }
    }
    
    public function boot()
    {
        Nova::script('nova-calendar', __DIR__.'/../dist/js/tool.js');
        Nova::style('nova-calendar', __DIR__.'/../dist/css/tool.css');
    }

    public function menu(Request $request)
    {
        // TODO v2 make label and icon configurable through calendar config?
        return MenuSection::make($this->menuLabel)
            ->icon($this->menuIcon)
            ->path($this->calendarConfig['uri']);
        
    }
    
    public function withMenuLabel(string $label)
    {
        $this->menuLabel = $label;
        return $this;
    }
    
    public function withMenuIcon(string $icon)
    {
        $this->menuIcon = $icon;
        return $this;
    }
}
