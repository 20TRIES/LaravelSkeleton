<?php namespace App\Libraries\Database\Migrations;

use Illuminate\Support\Arr;

class Migrator extends \Illuminate\Database\Migrations\Migrator
{
    /**
     * Get all of the migration files in a given path.
     *
     * @param  string  $path
     * @return array
     */
    public function getMigrationFiles($path)
    {
        if(is_dir($path))
        {
            $files = $this->files->glob($path.'/*_*.php');
        }
        else
        {
            $files = empty($path) ? [] : [$path];
        }

        // Once we have the array of files in the directory we will just remove the
        // extension and take the basename of the file which is all we need when
        // finding the migrations that haven't been run against the databases.
        if ($files === false) {
            return [];
        }

        $files = array_map(function ($file) {
            return str_replace('.php', '', basename($file));

        }, $files);

        // Once we have all of the formatted file names we will sort them and since
        // they all start with a timestamp this should give us the migrations in
        // the order they were actually created by the application developers.
        sort($files);

        return $files;
    }

    /**
     * Run the migrations indicated by a given path.
     *
     * @param string $path
     * @param array $options
     */
    public function run($path, array $options = [])
    {
        $this->notes = [];

        $files = $this->getMigrationFiles($path);

        // If the logging option has not been set, or has been set to true, then once we grab all of
        // the migration files for the path, we will compare them against the migrations that have
        // already been run for this package then run each of the outstanding migrations against a
        // database connection.
        $ran = isset($options['logging']) && $options['logging'] === false ? [] : $this->repository->getRan();

        $migrations = array_diff($files, $ran);

        $this->requireFiles(is_dir($path) ? $path : dirname($path), $migrations);

        $this->runMigrationList($migrations, $options);
    }

    /**
     * Run an array of migrations.
     *
     * @param  array  $migrations
     * @param  array  $options
     * @return void
     */
    public function runMigrationList($migrations, array $options = [])
    {
        // First we will just make sure that there are any migrations to run. If there
        // aren't, we will just make a note of it to the developer so they're aware
        // that all of the migrations have been run against this database system.
        if (count($migrations) == 0) {
            $this->note('<info>Nothing to migrate.</info>');

            return;
        }

        $batch = $this->repository->getNextBatchNumber();

        $step = Arr::get($options, 'step', false);

        // Once we have the array of migrations, we will spin through them and run the
        // migrations "up" so the changes are made to the databases. We'll then log
        // that the migration was run so we don't repeat it next time we execute.
        foreach ($migrations as $file) {
            $this->runUp($file, $batch, $options);

            // If we are stepping through the migrations, then we will increment the
            // batch value for each individual migration that is run. That way we
            // can run "artisan migrate:rollback" and undo them one at a time.
            if ($step) {
                $batch++;
            }
        }
    }

    /**
     * Run "up" a migration instance.
     *
     * @param string $file
     * @param int $batch
     * @param array $options
     */
    protected function runUp($file, $batch, $options = [])
    {
        // First we will resolve a "real" instance of the migration class from this
        // migration file name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
        $migration = $this->resolve($file);

        $pretend = Arr::get($options, 'pretend', false);

        if ($pretend) {
            return $this->pretendToRun($migration, 'up');
        }

        $migration->up();

        $logging = Arr::get($options, 'logging', true);

        if($logging)
        {
            // Once we have run a migrations class, we will log that it was run in this
            // repository so that we don't try to run it next time we do a migration
            // in the application. A migration repository keeps the migrate order.
            $this->repository->log($file, $batch);
        }

        $this->note("<info>Migrated:</info> $file");
    }

    /**
     * Require in all the migration files in a given path.
     *
     * @param  string  $path
     * @param  array   $files
     * @return void
     */
    public function requireFiles($path, array $files)
    {
        $dir = is_dir($path) ? $path : dirname($path);

        foreach ($files as $file) {
            $this->files->requireOnce($dir .'/'.$file.'.php');
        }
    }
}