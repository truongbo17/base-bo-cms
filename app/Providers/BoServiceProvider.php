<?php

namespace Bo\Base\Providers;

use Bo\Base\Console\Commands\AddCustomRouteContent;
use Bo\Base\Console\Commands\CreateUser;
use Bo\Base\Console\Commands\Fix;
use Bo\Base\Console\Commands\Install;
use Bo\Base\Console\Commands\PublishView;
use Bo\Base\Console\Commands\Version;
use Bo\Base\Http\Middleware\ThrottlePasswordRecovery;
use Bo\Base\Library\CrudPanel\CrudPanel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class BoServiceProvider extends ServiceProvider
{

    public string $routeFilePath = '/routes/base.php';

    // Indicates if loading of the provider is deferred.
    public string $customRoutesFilePath = '/routes/custom.php';
    // Where the route file lives, both inside the package and in the app (if overwritten).
    protected array $commands = [
        Install::class,
        AddCustomRouteContent::class,
        Version::class,
        CreateUser::class,
        PublishView::class,
        Fix::class,
    ];
    // Where custom routes can be written, and will be registered by bo.
    protected bool $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadViewsWithFallbacks();
        $this->loadTranslationsFrom(realpath(__DIR__ . '/../../resources/lang'), 'bo');
        $this->loadConfigs();
        $this->registerMiddlewareGroup($this->app->router);
        $this->setupRoutes($this->app->router);
        $this->setupCustomRoutes($this->app->router);
        $this->publishFiles();
        $this->dynamicFieldHint();
    }

    public function dynamicFieldHint()
    {
        /**
         * Add a method to the CrudPanel object whose respoinsibility it will be to
         * get column comments from PostgreSQL connections
         */
        CrudPanel::macro('getPostgresColumnComments', function ($model) {

            /** @var Model $model */
            $connectionName = $model->getConnectionName();
            $dbSettings = config("database.connections.$connectionName");

            $database = Arr::get($dbSettings, 'database');
            $schema = Arr::get($dbSettings, 'schema');
            $table = $model->getTable();
            $query = "SELECT
                                cols.column_name,
                                (
                                    SELECT
                                        pg_catalog.col_description(c.oid, cols.ordinal_position::int)
                                    FROM
                                        pg_catalog.pg_class c
                                    WHERE
                                        c.oid = (SELECT ('\"' || cols.table_name || '\"')::regclass::oid)
                                        AND c.relname = cols.table_name
                                ) AS column_comment
                            FROM
                                information_schema.columns cols
                            WHERE
                                cols.table_catalog    = '$database'
                                AND cols.table_name   = '$table'
                                AND cols.table_schema = '$schema';";

            $normalizedDetails = [];
            if ($columnDetails = DB::connection($connectionName)->select(DB::raw($query))) {
                foreach ($columnDetails as $column) {
                    $normalizedDetails[$column->column_name] = trim($column->column_comment);
                }
            }
            return $normalizedDetails;
        });

        /**
         * Add a method to the CrudPanel object whose respoinsibility it will be to
         * get column comments from MySQL connections
         */
        CrudPanel::macro('getMysqlColumnComments', function ($model) {
            /** @var Model $model */
            $table = $model->getTable();
            $connection = $model->getConnectionName();
            $columns = DB::connection($connection)->select(DB::raw('SHOW FULL COLUMNS FROM ' . $table . ';'));

            $normalizedDetails = [];
            if (is_countable($columns)) {
                foreach ($columns as $column) {
                    $normalizedDetails[$column->Field] = trim($column->Comment);
                }
            }
            return $normalizedDetails;
        });

        /**
         * Add a method to the CrudPanel object whose respoinsibility it will be to
         * get column comments from MS SQL Server connections
         */
        CrudPanel::macro('getSqlserverColumnComments', function ($model) {
            /** @var Model $model */
            $table = $model->getTable();
            $query = "SELECT    T.name AS Table_Name ,
                                      C.name AS column_name ,
                                      EP.value AS column_comment
                            FROM      sys.tables AS T
                            JOIN      sys.columns C
                            ON        T.object_id = C.object_id
                            LEFT JOIN sys.extended_properties EP
                            ON        T.object_id = EP.major_id
                            AND       C.column_id = EP.minor_id
                            AND       T.name = '$table';";
            $columns = DB::connection($model->getConnectionName())->select(DB::raw($query));

            $normalizedDetails = [];
            if (is_countable($columns)) {
                foreach ($columns as $column) {
                    $normalizedDetails[$column->column_name] = trim($column->column_comment);
                }
            }
            return $normalizedDetails;
        });

        /**
         * Add a method to the CrudPanel object whose respoinsibility it will be to
         * get column comments from the current model's connection
         */
        CrudPanel::macro('getColumnComments', function () {

            /** @var $this CrudPanel */
            $model = $this->getModel();
            /** @var $instance Model */
            $instance = new $model;
            $dbSettings = config("database.connections.{$instance->getConnectionName()}");
            $columns = [];

            if ($driver = Arr::get($dbSettings, 'driver')) {
                switch ($driver) {
                    case 'mysql':
                        $columns = $this->getMysqlColumnComments($model);
                        break;
                    case 'sqlsrv':
                        $columns = $this->getSqlserverColumnComments($model);
                        break;
                    case 'pgsql':
                        $columns = $this->getPostgresColumnComments($model);
                        break;
                }
            }
            return $columns;
        });

        /**
         * Add a method to the CrudPanel object whose respoinsibility it will be to
         * get column comments from the current model's connection and add them as hints
         * on the currently configured _fields
         */
        CrudPanel::macro('setFieldHintsFromColumnComments', function () {
            /** @var $this CrudPanel */
            $columns = $this->getColumnComments();

            if (is_countable($columns)) {
                /** @var $this CrudPanel */
                $fields = $this->fields();
                foreach ($fields as $key => $field) {
                    if (!isset($field['hint']) && isset($field['name'])) {
                        $columnComment = Arr::get($columns, $field['name']);
                        if ($columnComment) {
                            /** @var $this CrudPanel */
                            $this->modifyField($field['name'], ['hint' => trim($columnComment)]);
                        }
                    }
                }
            }
        });
    }

    public function loadViewsWithFallbacks()
    {
        $customBaseFolder = resource_path('views/vendor/bo/base');
        $customCrudFolder = resource_path('views/vendor/bo/crud');

        // - first the published/overwritten views (in case they have any changes)
        if (file_exists($customBaseFolder)) {
            $this->loadViewsFrom($customBaseFolder, 'bo');
        }
        if (file_exists($customCrudFolder)) {
            $this->loadViewsFrom($customCrudFolder, 'crud');
        }
        // - then the stock views that come with the package, in case a published view might be missing
        $this->loadViewsFrom(realpath(__DIR__ . '/../../resources/views/base'), 'bo');
        $this->loadViewsFrom(realpath(__DIR__ . '/../../resources/views/crud'), 'crud');
    }

    public function loadConfigs()
    {
        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(__DIR__ . '/../../config/bo/crud.php', 'bo.crud');
        $this->mergeConfigFrom(__DIR__ . '/../../config/bo/base.php', 'bo.base');
        $this->mergeConfigFromOperationsDirectory();

        // add the root disk to filesystem configuration
        app()->config['filesystems.disks.' . config('bo.base.root_disk_name')] = [
            'driver' => 'local',
            'root'   => base_path(),
        ];

        // add the bo_users authentication provider to the configuration
        app()->config['auth.providers'] = app()->config['auth.providers'] +
            [
                'bo' => [
                    'driver' => 'eloquent',
                    'model'  => config('bo.base.user_model_fqn'),
                ],
            ];

        // add the bo_users password broker to the configuration
        app()->config['auth.passwords'] = app()->config['auth.passwords'] +
            [
                'bo' => [
                    'provider' => 'bo',
                    'table'    => 'password_resets',
                    'expire'   => 60,
                    'throttle' => config('bo.base.password_recovery_throttle_notifications'),
                ],
            ];

        // add the bo_users guard to the configuration
        app()->config['auth.guards'] = app()->config['auth.guards'] +
            [
                'bo' => [
                    'driver'   => 'session',
                    'provider' => 'bo',
                ],
            ];
    }

    protected function mergeConfigFromOperationsDirectory()
    {
        $operationConfigs = scandir(__DIR__ . '/../../config/bo/operations/');
        $operationConfigs = array_diff($operationConfigs, ['.', '..']);

        if (!count($operationConfigs)) {
            return;
        }

        foreach ($operationConfigs as $configFile) {
            $this->mergeConfigFrom(
                __DIR__ . '/../../config/bo/operations/' . $configFile,
                'bo.operations.' . substr($configFile, 0, strrpos($configFile, '.'))
            );
        }
    }

    public function registerMiddlewareGroup(Router $router)
    {
        $middleware_key = config('bo.base.middleware_key');
        $middleware_class = config('bo.base.middleware_class');

        if (!is_array($middleware_class)) {
            $router->pushMiddlewareToGroup($middleware_key, $middleware_class);

            return;
        }

        foreach ($middleware_class as $middleware) {
            $router->pushMiddlewareToGroup($middleware_key, $middleware);
        }

        // register internal bo middleware for throttling the password recovery functionality
        // but only if functionality is enabled by developer in config
        if (config('bo.base.setup_password_recovery_routes')) {
            $router->aliasMiddleware('bo.throttle.password.recovery', ThrottlePasswordRecovery::class);
        }
    }

    /**
     * Define the routes for the application.
     *
     * @param Router $router
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        $routeFilePathInUse = __DIR__ . "/../../" . $this->routeFilePath;

        if (file_exists($routeFilePathInUse)) {
            $this->loadRoutesFrom($routeFilePathInUse);
        }
    }

    /**
     * Load custom routes file.
     *
     * @param Router $router
     * @return void
     */
    public function setupCustomRoutes(Router $router)
    {
        $routeFilePathInUse = __DIR__ . "/../../" . $this->customRoutesFilePath;

        if (file_exists($routeFilePathInUse)) {
            $this->loadRoutesFrom($routeFilePathInUse);
        }
    }

    public function publishFiles()
    {
        $error_views = [__DIR__ . '/../../resources/error_views' => resource_path('views/errors')];
        $bo_views = [__DIR__ . '/../../resources/views' => resource_path('views/vendor/bo')];
        $bo_public_assets = [__DIR__ . '/../../public' => public_path()];
        $bo_lang_files = [__DIR__ . '/../../resources/lang' => app()->langPath() . '/vendor/bo'];

        // calculate the path from current directory to get the vendor path
        $vendorPath = dirname(__DIR__, 4);
        $gravatar_assets = [$vendorPath . '/vendor/creativeorange/gravatar/config' => config_path()];

        // establish the minimum amount of files that need to be published, for Bo to work; there are the files that will be published by the install command
        $minimum = array_merge(
//            $bo_views,
            $bo_lang_files,
            $error_views,
            $bo_public_assets,
            $gravatar_assets
        );

        // register all possible publish commands and assign tags to each
        $this->publishes($bo_lang_files, 'lang');
//        $this->publishes($bo_views, 'views');
        $this->publishes($error_views, 'errors');
        $this->publishes($bo_public_assets, 'public');
        $this->publishes($gravatar_assets, 'gravatar');
        $this->publishes($minimum, 'minimum');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // load the macros
        include_once __DIR__ . '/../../macros.php';

        // Bind the CrudPanel object to Laravel's service container
        $this->app->singleton('crud', function ($app) {
            return new CrudPanel($app);
        });

        // Bind the widgets collection object to Laravel's service container
        $this->app->singleton('widgets', function ($app) {
            return new Collection();
        });

        // register the helper functions
        $this->loadHelpers();

        // register the artisan commands
        $this->commands($this->commands);
    }

    /**
     * Load the Bo helper methods, for convenience.
     */
    public function loadHelpers()
    {
        require_once __DIR__ . '/../../helpers/helpers.php';
        require_once __DIR__ . '/../../helpers/core.php';
        require_once __DIR__ . '/../../helpers/router.php';
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['crud', 'widgets'];
    }
}
