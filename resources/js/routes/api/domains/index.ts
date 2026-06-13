import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\DomainPriceController::availability
 * @see app/Http/Controllers/DomainPriceController.php:108
 * @route '/api/domains/availability'
 */
export const availability = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: availability.url(options),
    method: 'post',
})

availability.definition = {
    methods: ["post"],
    url: '/api/domains/availability',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DomainPriceController::availability
 * @see app/Http/Controllers/DomainPriceController.php:108
 * @route '/api/domains/availability'
 */
availability.url = (options?: RouteQueryOptions) => {
    return availability.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DomainPriceController::availability
 * @see app/Http/Controllers/DomainPriceController.php:108
 * @route '/api/domains/availability'
 */
availability.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: availability.url(options),
    method: 'post',
})
const domains = {
    availability: Object.assign(availability, availability),
}

export default domains