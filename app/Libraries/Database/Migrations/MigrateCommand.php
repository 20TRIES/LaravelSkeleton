<?php namespace App\Libraries\CustomLaravelComponents\Database\Migrations;


use Illuminate\Contracts\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class MigrateCommand extends \Illuminate\Database\Console\Migrations\MigrateCommand
{
    /**
     * Create a new migration command instance.
     *
     * @param  \Illuminate\Database\Migrations\Migrator  $migrator
     */
    /**
     * @param Migrator $migrator
     * @param Filesystem $filesystem
     */
    public function __construct(Migrator $migrator, Filesystem $filesystem = null)
    {
        parent::__construct($migrator);

        $this->filesystem = is_null($filesystem) ? new \Illuminate\Filesystem\Filesystem() : $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->prepareDatabase();

        // The pretend option can be used for "simulating" the migration and grabbing
        // the SQL queries that would fire if the migration were to be run against
        // a database for real, which is helpful for double checking migrations.
        $pretend = $this->input->getOption('pretend');

        // Next, we will check to see if a path option has been defined. If it has
        // we will use the path relative to the root of this installation folder
        // so that migrations may be run for any path within the applications.
        if (! is_null($path = $this->input->getOption('path'))) {
            $path = $this->laravel->basePath().'/'.$path;
        } else {
            $path = $this->getMigrationPath();
        }

        // Now check to see if a file option has been defined.
        if(! is_null($this->input->getOption('file')))
        {
            $file = $this->getMigrationFile($this->input->getOption('file'), $path);

            if(empty($file))
            {
                $this->info('Nothing to migrate.');
                return;
            }

            $path = "$path/$file";
        }

        // Now check to see if a logging option has been defined
        $logging = ! is_null($this->input->getOption('logging'))
            ? to_boolean($this->input->getOption('logging'))
            : true;

        $this->migrator->run($path, [
            'pretend' => $pretend,
            'logging' => $logging,
            'step'    => $this->input->getOption('step')
        ]);

        // Once the migrator has run we will grab the note output and send it out to
        // the console screen, since the migrator itself functions without having
        // any instances of the OutputInterface contract passed into the class.
        foreach ($this->migrator->getNotes() as $note) {
            $this->output->writeln($note);
        }

        // Finally, if the "seed" option has been given, we will re-run the database
        // seed task to re-populate the database, which is convenient when adding
        // a migration and a seed at the same time, as it is only this command.
        if ($this->input->getOption('seed')) {
            $this->call('db:seed', ['--force' => true]);
        }


    }


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        return array_merge($options, [

            ['logging', null, InputOption::VALUE_OPTIONAL, 'Whether logging is enabled; default of 1 (true)'],
            ['file', null, InputOption::VALUE_OPTIONAL, ' The migration file that should be run'],

        ]);
    }

    /**
     * Resolves a migration file from a specified path.
     *
     * @param $migration
     * @param $path
     * @return mixed
     */
    protected function getMigrationFile($migration, $path)
    {
        // Ensure that migration has a file extension
        $migration = preg_replace('/(?<!\.php)$/', '.php', $migration);

        if(file_exists("$path/$migration"))
        {
            return $migration;
        }
        else
        {
            $file = collect($this->filesystem->files($path));

            $file = $file->first(function($key, $file) use($migration)
            {
                $matches   = [];
                $file_name = last(explode('/', $file));
                preg_match("/^\d*_\d*_\d*_\d*_$migration$/", $file_name, $matches);
                return ! empty($matches);
            });

            $file_name = last(explode('/', $file));

            return $file_name;
        }
    }
}