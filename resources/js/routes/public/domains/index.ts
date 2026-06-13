import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\DomainPriceController::index
 * @see app/Http/Controllers/DomainPriceController.php:39
 * @route '/domains'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/domains',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DomainPriceController::index
 * @see app/Http/Controllers/DomainPriceController.php:39
 * @route '/domains'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DomainPriceController::index
 * @see app/Http/Controllers/DomainPriceController.php:39
 * @route '/domains'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DomainPriceController::index
 * @see app/Http/Controllers/DomainPriceController.php:39
 * @route '/domains'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DomainPriceController::search
 * @see app/Http/Controllers/DomainPriceController.php:54
 * @route '/domains/search'
 */
export const search = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: search.url(options),
    method: 'get',
})

search.definition = {
    methods: ["get","head"],
    url: '/domains/search',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DomainPriceController::search
 * @see app/Http/Controllers/DomainPriceController.php:54
 * @route '/domains/search'
 */
search.url = (options?: RouteQueryOptions) => {
    return search.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DomainPriceController::search
 * @see app/Http/Controllers/DomainPriceController.php:54
 * @route '/domains/search'
 */
search.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: search.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DomainPriceController::search
 * @see app/Http/Controllers/DomainPriceController.php:54
 * @route '/domains/search'
 */
search.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: search.url(options),
    method: 'head',
})
const domains = {
    index: Object.assign(index, index),
search: Object.assign(search, search),
}

export default domains