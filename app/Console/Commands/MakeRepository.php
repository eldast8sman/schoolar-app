<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeRepository extends Command
{
    protected $repo_name;
    protected $inter_name;
    protected $repo_path;
    protected $repo_stub;
    protected $inter_path;
    protected $inter_stub;
    protected $files;

    public function __construct(Filesystem $file)
    {
        parent::__construct();
        $this->files = $file;
    }
    protected $signature = 'make:repository {name}';

    protected function makeDirectory(){
        if(!$this->files->isDirectory(dirname($this->repo_path))){
            $this->files->makeDirectory(dirname($this->repo_path));
        }
        if(!$this->files->isDirectory(dirname($this->inter_path))){
            $this->files->makeDirectory(dirname($this->inter_path));
        }
    }

    protected function buildRepoClass(){
        $repo_stub = $this->files->get($this->repo_stub);
        return str_replace(['{{ class }}', '{{ interface }}'], [$this->repo_name, $this->inter_name], $repo_stub);
    }

    protected function buildInterClass(){
        $stub = $this->files->get($this->inter_stub);
        return str_replace('{{ class }}', $this->inter_name, $stub);
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Repository alongside the interface';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->repo_name = $this->argument('name');
        $this->inter_name = $this->repo_name.'Interface';
        $this->repo_path = app_path('Repositories/'.$this->repo_name.'.php');
        $this->inter_path = app_path('Repositories/Interfaces/'.$this->inter_name.'.php');
        $this->repo_stub = __DIR__.'/stubs/repository.stub';
        $this->inter_stub = __DIR__.'/stubs/interface.stub';

        if($this->files->exists($this->repo_path)){
            $this->error('Repository already exist');
            return;
        }

        $this->makeDirectory();
        $this->files->put($this->repo_path, $this->buildRepoClass());
        $this->files->put($this->inter_path, $this->buildInterClass());

        $this->info("Repository created successfully");
    }
}
