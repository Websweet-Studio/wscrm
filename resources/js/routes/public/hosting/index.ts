import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\HostingPlanController::index
 * @see app/Http/Controllers/HostingPlanController.php:31
 * @route '/hosting'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/hosting',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\HostingPlanController::index
 * @see app/Http/Controllers/HostingPlanController.php:31
 * @route '/hosting'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\HostingPlanController::index
 * @see app/Http/Controllers/HostingPlanController.php:31
 * @route '/hosting'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\HostingPlanController::index
 * @see app/Http/Controllers/HostingPlanController.php:31
 * @route '/hosting'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})
const hosting = {
    index: Object.assign(index, index),
}

export default hosting