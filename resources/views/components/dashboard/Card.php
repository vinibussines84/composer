<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;

class Card extends Component
{
    public $title, $value, $icon, $color;

    public function __construct($title, $value, $icon, $color)
    {
        $this->title = $title;
        $this->value = $value;
        $this->icon = $icon;
        $this->color = $color;
    }

    public function render()
    {
        return view('components.dashboard.card');
    }
}
