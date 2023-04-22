<?php

namespace Bo\Base\Services;

use Exception;

trait LoadAndPublishDataTrait
{
    /** @var string|null $primary_key */
    private ?string $primary_key = null;

    /** @var string|null $dir_plugin */
    private ?string $dir_plugin = null;

    /**
     * Set primary key for plugin
     *
     * @param string $primary_key
     * @return LoadAndPublishDataTrait
     */
    public function setPrimaryKeyPlugin(string $primary_key): self
    {
        $this->primary_key = $primary_key;
        return $this;
    }

    /**
     * Set dir for plugin
     *
     * @param string $dir_plugin
     * @return LoadAndPublishDataTrait
     */
    public function setDirPlugin(string $dir_plugin): self
    {
        $this->dir_plugin = $dir_plugin;
        return $this;
    }

    /**
     * validate dir and primary key plugin not null
     *
     * @throws Exception
     */
    private function validateDirAndPrimaryKeyPlugin(): void
    {
        if (is_null($this->dir_plugin) && is_null($this->primary_key)) {
            throw new Exception("Please add dir plugin and primary_key plugin.");
        }
    }

    /**
     * load route service provider
     *
     * @param array $routes
     */
    public function loadRoutes(array $routes): self
    {
        $this->validateDirAndPrimaryKeyPlugin();

        foreach ($routes as $route) {
            $this->loadRoutesFrom(get_path_route_plugin($this->dir_plugin, $route));
        }

        return $this;
    }

    /**
     * Load helper
     *
     * */
    public function loadHelper(): self
    {
        $this->validateDirAndPrimaryKeyPlugin();
        BaseService::autoload(get_path_helper_plugin($this->primary_key));
        return $this;
    }

    /**
     * Load migration
     * */
    public function loadMigration(): self
    {
        $this->loadMigrationsFrom(get_path_database_plugin($this->primary_key));
        return $this;
    }

    /**
     * Load and publish translation
     */
    public function loadAndPublishTranslations(): self
    {
        $this->loadTranslationsFrom(get_path_resource_plugin($this->primary_key) . "/lang", $this->primary_key);
        $this->publishes(
            [get_path_resource_plugin($this->primary_key) . "/lang" => lang_path('vendor/' . $this->primary_key)],
            'cms-lang'
        );

        return $this;
    }

    /**
     * Load and publish view
     */
    public function loadAndPublishViews(): self
    {
        $this->loadViewsFrom(get_path_resource_plugin($this->primary_key) . "/views", $this->primary_key);
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [get_path_resource_plugin($this->primary_key) . "/views" => resource_path('views/vendor/' . $this->primary_key)],
                'cms-views'
            );
        }

        return $this;
    }
}
