<?php

namespace App\Console\Commands\Jobs;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Throwable;
use ReflectionClass;

class ListCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'job:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists all jobs that can be dispatched';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * @var array
     */
    protected $jobs = [];

    /**
     * Create a new failed queue jobs table command instance.
     *
     * @param  Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;

        $this->jobs = [];

        $files = $this->files->allFiles(app_path('Jobs'));

        foreach ($files as $file) {
            $file = $file->getRelativePathname();
            $job = str_replace('/', '\\', str_replace('.php', '', $file));
            if ($this->isInstantiable($job)) {
                $this->jobs[] = $job;
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        foreach ($this->getJobs() as $job) {
            $this->line("   - $job");
        }
    }

    /**
     * @return array
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * Determines if a job class is instantiable.
     *
     * @param string $job
     * @return bool
     */
    protected function isInstantiable($job)
    {
        try {
            return (new ReflectionClass('App\Jobs\\'.$job))->isInstantiable();
        } catch (Throwable $ex) {
            return false;
        }
    }
}
