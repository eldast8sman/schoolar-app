<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeTrait extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:trait {name}';

    protected $name;
    protected $path;
    protected $stub;
    protected $files;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Trait';

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    protected function makeDirectory(){
        if(!$this->files->isDirectory(dirname($this->path))){
            $this->files->makeDirectory(dirname($this->path), 0777, true, false);
        }
    }

    protected function buildClass(){
        $stub = $this->files->get($this->stub);
        
        return str_replace('{{ trait }}', $this->name, $stub);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->name = $this->argument('name');
        $this->path = app_path('Traits/'.$this->name.'.php');
        $this->stub = __DIR__.'/stubs/trait.stub';

        if($this->files->exists($this->path)){
            $this->error('Trait already exists!');
            return;
        }

        $this->makeDirectory();
        $this->files->put($this->path, $this->buildClass());

        $this->info('Trait created successfully');
    }
}
