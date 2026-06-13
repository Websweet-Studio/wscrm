import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\ServicePlanController::bulkDestroy
 * @see app/Http/Controllers/Admin/ServicePlanController.php:104
 * @route '/admin/service-plans/bulk'
 */
export const bulkDestroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

bulkDestroy.definition = {
    methods: ["delete"],
    url: '/admin/service-plans/bulk',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::bulkDestroy
 * @see app/Http/Controllers/Admin/ServicePlanController.php:104
 * @route '/admin/service-plans/bulk'
 */
bulkDestroy.url = (options?: RouteQueryOptions) => {
    return bulkDestroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::bulkDestroy
 * @see app/Http/Controllers/Admin/ServicePlanController.php:104
 * @route '/admin/service-plans/bulk'
 */
bulkDestroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::index
 * @see app/Http/Controllers/Admin/ServicePlanController.php:15
 * @route '/admin/service-plans'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/service-plans',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::index
 * @see app/Http/Controllers/Admin/ServicePlanController.php:15
 * @route '/admin/service-plans'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::index
 * @see app/Http/Controllers/Admin/ServicePlanController.php:15
 * @route '/admin/service-plans'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\ServicePlanController::index
 * @see app/Http/Controllers/Admin/ServicePlanController.php:15
 * @route '/admin/service-plans'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::store
 * @see app/Http/Controllers/Admin/ServicePlanController.php:51
 * @route '/admin/service-plans'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/service-plans',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::store
 * @see app/Http/Controllers/Admin/ServicePlanController.php:51
 * @route '/admin/service-plans'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::store
 * @see app/Http/Controllers/Admin/ServicePlanController.php:51
 * @route '/admin/service-plans'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::show
 * @see app/Http/Controllers/Admin/ServicePlanController.php:44
 * @route '/admin/service-plans/{service_plan}'
 */
export const show = (args: { service_plan: number | { id: number } } | [service_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/service-plans/{service_plan}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::show
 * @see app/Http/Controllers/Admin/ServicePlanController.php:44
 * @route '/admin/service-plans/{service_plan}'
 */
show.url = (args: { service_plan: number | { id: number } } | [service_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service_plan: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service_plan: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service_plan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service_plan: typeof args.service_plan === 'object'
                ? args.service_plan.id
                : args.service_plan,
                }

    return show.definition.url
            .replace('{service_plan}', parsedArgs.service_plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::show
 * @see app/Http/Controllers/Admin/ServicePlanController.php:44
 * @route '/admin/service-plans/{service_plan}'
 */
show.get = (args: { service_plan: number | { id: number } } | [service_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\ServicePlanController::show
 * @see app/Http/Controllers/Admin/ServicePlanController.php:44
 * @route '/admin/service-plans/{service_plan}'
 */
show.head = (args: { service_plan: number | { id: number } } | [service_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::update
 * @see app/Http/Controllers/Admin/ServicePlanController.php:74
 * @route '/admin/service-plans/{service_plan}'
 */
export const update = (args: { service_plan: number | { id: number } } | [service_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/service-plans/{service_plan}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::update
 * @see app/Http/Controllers/Admin/ServicePlanController.php:74
 * @route '/admin/service-plans/{service_plan}'
 */
update.url = (args: { service_plan: number | { id: number } } | [service_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service_plan: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service_plan: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service_plan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service_plan: typeof args.service_plan === 'object'
                ? args.service_plan.id
                : args.service_plan,
                }

    return update.definition.url
            .replace('{service_plan}', parsedArgs.service_plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::update
 * @see app/Http/Controllers/Admin/ServicePlanController.php:74
 * @route '/admin/service-plans/{service_plan}'
 */
update.put = (args: { service_plan: number | { id: number } } | [service_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\ServicePlanController::update
 * @see app/Http/Controllers/Admin/ServicePlanController.php:74
 * @route '/admin/service-plans/{service_plan}'
 */
update.patch = (args: { service_plan: number | { id: number } } | [service_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::destroy
 * @see app/Http/Controllers/Admin/ServicePlanController.php:97
 * @route '/admin/service-plans/{service_plan}'
 */
export const destroy = (args: { service_plan: number | { id: number } } | [service_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/service-plans/{service_plan}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::destroy
 * @see app/Http/Controllers/Admin/ServicePlanController.php:97
 * @route '/admin/service-plans/{service_plan}'
 */
destroy.url = (args: { service_plan: number | { id: number } } | [service_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service_plan: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service_plan: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service_plan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service_plan: typeof args.service_plan === 'object'
                ? args.service_plan.id
                : args.service_plan,
                }

    return destroy.definition.url
            .replace('{service_plan}', parsedArgs.service_plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ServicePlanController::destroy
 * @see app/Http/Controllers/Admin/ServicePlanController.php:97
 * @route '/admin/service-plans/{service_plan}'
 */
destroy.delete = (args: { service_plan: number | { id: number } } | [service_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})
const servicePlans = {
    bulkDestroy: Object.assign(bulkDestroy, bulkDestroy),
index: Object.assign(index, index),
store: Object.assign(store, store),
show: Object.assign(show, show),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default servicePlans