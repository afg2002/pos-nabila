<?php

namespace App\Livewire;

use Livewire\Component;

class TestAlpine extends Component
{
    public $testProperty = '';
    
    public function testMethod()
    {
        session()->flash('message', 'Test method called successfully!');
    }
    
    public function slowMethod()
    {
        // Simulate slow operation
        sleep(2);
        session()->flash('message', 'Slow method completed!');
    }
    
    public function render()
    {
        return view('test-alpine');
    }
}