import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\DemoWebsiteController::index
 * @see app/Http/Controllers/DemoWebsiteController.php:11
 * @route '/api/demos'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/api/demos',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DemoWebsiteController::index
 * @see app/Http/Controllers/DemoWebsiteController.php:11
 * @route '/api/demos'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DemoWebsiteController::index
 * @see app/Http/Controllers/DemoWebsiteController.php:11
 * @route '/api/demos'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DemoWebsiteController::index
 * @see app/Http/Controllers/DemoWebsiteController.php:11
 * @route '/api/demos'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DemoWebsiteController::show
 * @see app/Http/Controllers/DemoWebsiteController.php:50
 * @route '/api/demos/{demoWebsite}'
 */
export const show = (args: { demoWebsite: number | { id: number } } | [demoWebsite: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/api/demos/{demoWebsite}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DemoWebsiteController::show
 * @see app/Http/Controllers/DemoWebsiteController.php:50
 * @route '/api/demos/{demoWebsite}'
 */
show.url = (args: { demoWebsite: number | { id: number } } | [demoWebsite: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { demoWebsite: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { demoWebsite: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    demoWebsite: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        demoWebsite: typeof args.demoWebsite === 'object'
                ? args.demoWebsite.id
                : args.demoWebsite,
                }

    return show.definition.url
            .replace('{demoWebsite}', parsedArgs.demoWebsite.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DemoWebsiteController::show
 * @see app/Http/Controllers/DemoWebsiteController.php:50
 * @route '/api/demos/{demoWebsite}'
 */
show.get = (args: { demoWebsite: number | { id: number } } | [demoWebsite: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DemoWebsiteController::show
 * @see app/Http/Controllers/DemoWebsiteController.php:50
 * @route '/api/demos/{demoWebsite}'
 */
show.head = (args: { demoWebsite: number | { id: number } } | [demoWebsite: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})
const demos = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
}

export default demos