<?php


if (!function_exists('getRouteListAdmin')) {
    /**
     * Return array list route name alias admin
     *
     * @return array
     * */
    function getRouteListAdmin(): array
    {
        $list_route = Route::getRoutes()->getRoutesByName();
        $array_route_admin = [];
        $array_ignore_route_permission = config('bo.permissionmanager.ignore_route_permission', []);

        foreach ($list_route as $route) {
            if ($route->getPrefix() == config('bo.base.route_prefix', 'admin') && !in_array($route->getName(), $array_ignore_route_permission) && checkEndsWith($route->getName())) {
                $array_route_admin[$route->getName()] = request()->getSchemeAndHttpHost() . '/' . $route->uri();
            }
        }

        return $array_route_admin;
    }
}

if (!function_exists('getRouteList')) {
    /**
     * Return array list route name alias admin
     *
     * @return array
     * */
    function getRouteList(): array
    {
        $list_route = Route::getRoutes()->getRoutesByName();
        $array_route = [];
        $array_ignore_route_permission = config('bo.permissionmanager.ignore_route_permission', []);

        foreach ($list_route as $route) {
            if (!in_array($route->getName(), $array_ignore_route_permission) && checkEndsWith($route->getName())) {
                $array_route[$route->getName()] = request()->getSchemeAndHttpHost() . '/' . $route->uri();
            }
        }

        return $array_route;
    }
}

if (!function_exists('checkEndsWith')) {
    /**
     * Check end with string route
     *
     * @param string $route_name
     * @return bool
     * */
    function checkEndsWith(string $route_name): bool
    {
        $array_ignore_by_regex = config('bo.permissionmanager.ignore_route_permission_by_regex', []);
        foreach ($array_ignore_by_regex as $value) {
            if (str_ends_with($route_name, $value)) return false;
        }
        return true;
    }
}
