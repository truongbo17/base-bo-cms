<?php

if (!function_exists('bo_url')) {
    /**
     * Appends the configured BO prefix and returns
     * the URL using the standard Laravel helpers.
     *
     * @param null $path
     * @param array $parameters
     * @param null $secure
     * @return string
     */
    function bo_url($path = null, array $parameters = [], $secure = null): string
    {
        $path = !$path || (substr($path, 0, 1) == '/') ? $path : '/' . $path;

        return url(config('bo.base.route_prefix', 'admin') . $path, $parameters, $secure);
    }
}

if (!function_exists('bo_authentication_column')) {
    /**
     * Return the username column name.
     * The Laravel default (and Bo default) is 'email'.
     *
     * @return string
     */
    function bo_authentication_column(): string
    {
        return config('bo.base.authentication_column', 'email');
    }
}

if (!function_exists('bo_form_input')) {
    /**
     * Parse the submitted input in request('form') to an usable array.
     * Joins the multiple[] fields in a single key and transform the dot notation fields into arrayed ones.
     *
     *
     * @return array
     */
    function bo_form_input(): array
    {
        $input = request('form') ?? [];
        $result = [];

        foreach ($input as $row) {
            $repeatableRowKey = null;

            // regular fields don't need any aditional parsing
            if (strpos($row['name'], '[') === false) {
                $result[$row['name']] = $row['value'];
                continue;
            }

            // dot notation fields
            if (substr_count($row['name'], '[') === 1) {
                // start in the first occurence since it's HasOne/MorphOne with dot notation (address[street] in request) to get the input name (address)
                $inputNameStart = strpos($row['name'], '[') + 1;
            } else {
                // repeatable fields, we need to get the input name and the row number
                // start on the second occurence since it's a repeatable and we want to bypass the row number (repeatableName[rowNumber][inputName])
                $inputNameStart = strpos($row['name'], '[', strpos($row['name'], '[') + 1) + 1;

                // get the array key (aka repeatable row) from field name
                $startKey = strpos($row['name'], '[') + 1;
                $endKey = strpos($row['name'], ']', $startKey);
                $lengthKey = $endKey - $startKey;
                $repeatableRowKey = substr($row['name'], $startKey, $lengthKey);
            }

            $inputNameEnd = strpos($row['name'], ']', $inputNameStart);
            $inputNameLength = $inputNameEnd - $inputNameStart;
            $inputName = substr($row['name'], $inputNameStart, $inputNameLength);
            $parentInputName = substr($row['name'], 0, strpos($row['name'], '['));

            if (isset($repeatableRowKey)) {
                $result[$parentInputName][$repeatableRowKey][$inputName] = $row['value'];
                continue;
            }

            $result[$parentInputName][$inputName] = $row['value'];
        }

        return $result;
    }
}

if (!function_exists('bo_users_have_email')) {
    /**
     * Check if the email column is present on the user table.
     *
     * @return string
     */
    function bo_users_have_email(): string
    {
        $user_model_fqn = config('bo.base.user_model_fqn');
        $user = new $user_model_fqn();

        return \Schema::hasColumn($user->getTable(), 'email');
    }
}

if (!function_exists('bo_avatar_url')) {
    /**
     * Returns the avatar URL of a user.
     *
     * @param $user
     * @return string
     */
    function bo_avatar_url($user): string
    {
        switch (config('bo.base.avatar_type')) {
            case 'gravatar':
                if (bo_users_have_email()) {
                    return Gravatar::fallback(config('bo.base.gravatar_fallback'))->get($user->email);
                }
                break;
            default:
                return method_exists($user, config('bo.base.avatar_type')) ? $user->{config('bo.base.avatar_type')}() : $user->{config('bo.base.avatar_type')};
                break;
        }
    }
}

if (!function_exists('bo_middleware')) {
    /**
     * Return the key of the middleware used across Bo.
     * That middleware checks if the visitor is an admin.
     *
     * @return string
     */
    function bo_middleware(): string
    {
        return config('bo.base.middleware_key', 'admin');
    }
}

if (!function_exists('bo_guard_name')) {
    /**
     * Returns the name of the guard defined
     * by the application config
     */
    function bo_guard_name()
    {
        return config('bo.base.guard', config('auth.defaults.guard'));
    }
}

if (!function_exists('bo_auth')) {
    /**
     * Returns the user instance if it exists
     * of the currently authenticated admin
     * based off the defined guard.
     */
    function bo_auth()
    {
        return \Auth::guard(bo_guard_name());
    }
}

if (!function_exists('bo_user')) {
    /**
     * Returns back a user instance without
     * the admin guard, however allows you
     * to pass in a custom guard if you like.
     */
    function bo_user()
    {
        return bo_auth()->user();
    }
}

if (!function_exists('mb_ucfirst')) {
    /**
     * Capitalize the first letter of a string,
     * even if that string is multi-byte (non-latin alphabet).
     *
     * @param string $string String to have its first letter capitalized.
     * @param encoding $encoding Character encoding
     * @return string String with first letter capitalized.
     */
    function mb_ucfirst(string $string, $encoding = false): string
    {
        $encoding = $encoding ? $encoding : mb_internal_encoding();

        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);

        return mb_strtoupper($firstChar, $encoding) . $then;
    }
}

if (!function_exists('bo_view')) {
    /**
     * Returns a new displayable view based on the configured Bo view namespace.
     * If that view doesn't exist, it will load the one from the original theme.
     *
     * @param string (see config/bo/base.php)
     * @return string
     */
    function bo_view($view): string
    {
        $originalTheme = 'bo::';
        $theme = config('bo.base.view_namespace');

        if (is_null($theme)) {
            $theme = $originalTheme;
        }

        $returnView = $theme . $view;

        if (!view()->exists($returnView)) {
            $returnView = $originalTheme . $view;
        }

        return $returnView;
    }
}

if (!function_exists('square_brackets_to_dots')) {
    /**
     * Turns a string from bracket-type array to dot-notation array.
     * Ex: array[0][property] turns into array.0.property.
     *
     * @param $string
     * @return string
     */
    function square_brackets_to_dots($string): string
    {
        return str_replace(['[', ']'], ['.', ''], $string);
    }
}

if (!function_exists('old_empty_or_null')) {
    /**
     * This method is an alternative to Laravel's old() helper, which mistakenly
     * returns NULL it two cases:
     * - if there is an old value, and it was empty or null
     * - if there is no old value
     * (this is because of the ConvertsEmptyStringsToNull middleware).
     *
     * In contrast, this method will return:
     * - the old value, if there actually is an old value for that key;
     * - the second parameter, if there is no old value for that key, but it was empty string or null;
     * - null, if there is no old value at all for that key;
     *
     * @param string $key
     * @param array|string $empty_value
     * @return mixed
     */
    function old_empty_or_null(string $key, $empty_value = '')
    {
        $key = square_brackets_to_dots($key);
        $old_inputs = session()->getOldInput();

        // if the input name is present in the old inputs we need to return earlier and not in a coalescing chain
        // otherwise `null` aka empty will not pass the condition and the field value would be returned.
        if (\Arr::has($old_inputs, $key)) {
            return \Arr::get($old_inputs, $key) ?? $empty_value;
        }

        return null;
    }
}

if (!function_exists('is_multidimensional_array')) {
    /**
     * If any of the items inside a given array is an array, the array is considered multidimensional.
     *
     * @param array $array
     * @return bool
     */
    function is_multidimensional_array(array $array): bool
    {
        foreach ($array as $item) {
            if (is_array($item)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('get_repeater_data_key_value')) {
    function get_repeater_data_key_value($data, $key_name, $value_name): array
    {
        $result = [];
        try {
            if ($data) {
                $data = json_decode($data, true);
                foreach ($data as $value) {
                    if (isset($value[$key_name]) && isset($value[$value_name])) {
                        $result[$value[$key_name]] = $value[$value_name];
                    }
                }

                return $result;
            }
        } catch (\Exception $exception) {
        }

        return $result;
    }
}
