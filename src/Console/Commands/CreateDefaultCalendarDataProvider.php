<?php

namespace Wdelfuego\NovaCalendar\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateDefaultCalendarDataProvider extends Command
{
    const TARGET_PATH = 'Providers/CalendarDataProvider.php';
    const STUB_PATH = __DIR__.'/../../../resources/CalendarDataProvider.default.php';
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:default-calendar-data-provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates an empty CalendarDataProvider at the default location';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $source = self::STUB_PATH;
        $target = app_path(self::TARGET_PATH);
        
        if(file_exists($target))
        {
            $this->error("A file already exists at '$target'");
            return 1;
        }
        if(!file_exists($source))
        {
            $this->error("Source file not found at '$source'");
            return 1;
        }
        
        $result = File::copy($source, $target);
        if(!$result)
        {
            $this->error("Unknown error copying default calendar data provider to '$target'");
            return 1;
        }
        
        $this->info("Succesfully created default calendar data provider at '$target'");
        return 0;
    }
}
