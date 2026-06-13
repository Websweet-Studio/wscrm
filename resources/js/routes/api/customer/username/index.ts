import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\CustomerUsernameController::check
 * @see app/Http/Controllers/Api/CustomerUsernameController.php:12
 * @route '/api/customer/username/check'
 */
export const check = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: check.url(options),
    method: 'get',
})

check.definition = {
    methods: ["get","head"],
    url: '/api/customer/username/check',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\CustomerUsernameController::check
 * @see app/Http/Controllers/Api/CustomerUsernameController.php:12
 * @route '/api/customer/username/check'
 */
check.url = (options?: RouteQueryOptions) => {
    return check.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\CustomerUsernameController::check
 * @see app/Http/Controllers/Api/CustomerUsernameController.php:12
 * @route '/api/customer/username/check'
 */
check.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: check.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\CustomerUsernameController::check
 * @see app/Http/Controllers/Api/CustomerUsernameController.php:12
 * @route '/api/customer/username/check'
 */
check.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: check.url(options),
    method: 'head',
})
const username = {
    check: Object.assign(check, check),
}

export default username