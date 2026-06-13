import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Customer\SettingsController::index
 * @see app/Http/Controllers/Customer/SettingsController.php:16
 * @route '/customer/settings'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/customer/settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\SettingsController::index
 * @see app/Http/Controllers/Customer/SettingsController.php:16
 * @route '/customer/settings'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\SettingsController::index
 * @see app/Http/Controllers/Customer/SettingsController.php:16
 * @route '/customer/settings'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\SettingsController::index
 * @see app/Http/Controllers/Customer/SettingsController.php:16
 * @route '/customer/settings'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Customer\SettingsController::updateProfile
 * @see app/Http/Controllers/Customer/SettingsController.php:21
 * @route '/customer/settings/profile'
 */
export const updateProfile = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateProfile.url(options),
    method: 'patch',
})

updateProfile.definition = {
    methods: ["patch"],
    url: '/customer/settings/profile',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Customer\SettingsController::updateProfile
 * @see app/Http/Controllers/Customer/SettingsController.php:21
 * @route '/customer/settings/profile'
 */
updateProfile.url = (options?: RouteQueryOptions) => {
    return updateProfile.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\SettingsController::updateProfile
 * @see app/Http/Controllers/Customer/SettingsController.php:21
 * @route '/customer/settings/profile'
 */
updateProfile.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateProfile.url(options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Customer\SettingsController::updatePassword
 * @see app/Http/Controllers/Customer/SettingsController.php:42
 * @route '/customer/settings/password'
 */
export const updatePassword = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePassword.url(options),
    method: 'patch',
})

updatePassword.definition = {
    methods: ["patch"],
    url: '/customer/settings/password',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Customer\SettingsController::updatePassword
 * @see app/Http/Controllers/Customer/SettingsController.php:42
 * @route '/customer/settings/password'
 */
updatePassword.url = (options?: RouteQueryOptions) => {
    return updatePassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\SettingsController::updatePassword
 * @see app/Http/Controllers/Customer/SettingsController.php:42
 * @route '/customer/settings/password'
 */
updatePassword.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePassword.url(options),
    method: 'patch',
})
const settings = {
    index: Object.assign(index, index),
updateProfile: Object.assign(updateProfile, updateProfile),
updatePassword: Object.assign(updatePassword, updatePassword),
}

export default settings