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
    protected $signature = 'nova-calendar:create-default-calendar-data-provider';

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
