import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\ServicePlanController::show
 * @see app/Http/Controllers/ServicePlanController.php:30
 * @route '/services/{servicePlan}'
 */
export const show = (args: { servicePlan: number | { id: number } } | [servicePlan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/services/{servicePlan}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ServicePlanController::show
 * @see app/Http/Controllers/ServicePlanController.php:30
 * @route '/services/{servicePlan}'
 */
show.url = (args: { servicePlan: number | { id: number } } | [servicePlan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { servicePlan: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { servicePlan: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    servicePlan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        servicePlan: typeof args.servicePlan === 'object'
                ? args.servicePlan.id
                : args.servicePlan,
                }

    return show.definition.url
            .replace('{servicePlan}', parsedArgs.servicePlan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServicePlanController::show
 * @see app/Http/Controllers/ServicePlanController.php:30
 * @route '/services/{servicePlan}'
 */
show.get = (args: { servicePlan: number | { id: number } } | [servicePlan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ServicePlanController::show
 * @see app/Http/Controllers/ServicePlanController.php:30
 * @route '/services/{servicePlan}'
 */
show.head = (args: { servicePlan: number | { id: number } } | [servicePlan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ServicePlanController::show
 * @see app/Http/Controllers/ServicePlanController.php:30
 * @route '/services/{servicePlan}'
 */
    const showForm = (args: { servicePlan: number | { id: number } } | [servicePlan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ServicePlanController::show
 * @see app/Http/Controllers/ServicePlanController.php:30
 * @route '/services/{servicePlan}'
 */
        showForm.get = (args: { servicePlan: number | { id: number } } | [servicePlan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ServicePlanController::show
 * @see app/Http/Controllers/ServicePlanController.php:30
 * @route '/services/{servicePlan}'
 */
        showForm.head = (args: { servicePlan: number | { id: number } } | [servicePlan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const servicePlans = {
    show: Object.assign(show, show),
}

export default servicePlans