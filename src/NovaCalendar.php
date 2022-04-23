<?php

namespace Wdelfuego\NovaCalendar;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaCalendar extends Tool
{
    private $menuLabel = 'Nova Calendar';
    private $menuIcon = 'calendar';
    
    public function boot()
    {
        Nova::script('nova-calendar', __DIR__.'/../dist/js/tool.js');
        Nova::style('nova-calendar', __DIR__.'/../dist/css/tool.css');
    }

    public function menu(Request $request)
    {
        return MenuSection::make($this->menuLabel)
            ->icon($this->menuIcon)
            ->path('/wdelfuego/nova-calendar');
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
