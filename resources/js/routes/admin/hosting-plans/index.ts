import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::bulkDestroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:102
 * @route '/admin/hosting-plans/bulk'
 */
export const bulkDestroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

bulkDestroy.definition = {
    methods: ["delete"],
    url: '/admin/hosting-plans/bulk',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::bulkDestroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:102
 * @route '/admin/hosting-plans/bulk'
 */
bulkDestroy.url = (options?: RouteQueryOptions) => {
    return bulkDestroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::bulkDestroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:102
 * @route '/admin/hosting-plans/bulk'
 */
bulkDestroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::index
 * @see app/Http/Controllers/Admin/HostingPlanController.php:15
 * @route '/admin/hosting-plans'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/hosting-plans',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::index
 * @see app/Http/Controllers/Admin/HostingPlanController.php:15
 * @route '/admin/hosting-plans'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::index
 * @see app/Http/Controllers/Admin/HostingPlanController.php:15
 * @route '/admin/hosting-plans'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::index
 * @see app/Http/Controllers/Admin/HostingPlanController.php:15
 * @route '/admin/hosting-plans'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::create
 * @see app/Http/Controllers/Admin/HostingPlanController.php:31
 * @route '/admin/hosting-plans/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/hosting-plans/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::create
 * @see app/Http/Controllers/Admin/HostingPlanController.php:31
 * @route '/admin/hosting-plans/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::create
 * @see app/Http/Controllers/Admin/HostingPlanController.php:31
 * @route '/admin/hosting-plans/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::create
 * @see app/Http/Controllers/Admin/HostingPlanController.php:31
 * @route '/admin/hosting-plans/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::store
 * @see app/Http/Controllers/Admin/HostingPlanController.php:36
 * @route '/admin/hosting-plans'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/hosting-plans',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::store
 * @see app/Http/Controllers/Admin/HostingPlanController.php:36
 * @route '/admin/hosting-plans'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::store
 * @see app/Http/Controllers/Admin/HostingPlanController.php:36
 * @route '/admin/hosting-plans'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::show
 * @see app/Http/Controllers/Admin/HostingPlanController.php:58
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
export const show = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/hosting-plans/{hosting_plan}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::show
 * @see app/Http/Controllers/Admin/HostingPlanController.php:58
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
show.url = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { hosting_plan: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { hosting_plan: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    hosting_plan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        hosting_plan: typeof args.hosting_plan === 'object'
                ? args.hosting_plan.id
                : args.hosting_plan,
                }

    return show.definition.url
            .replace('{hosting_plan}', parsedArgs.hosting_plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::show
 * @see app/Http/Controllers/Admin/HostingPlanController.php:58
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
show.get = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::show
 * @see app/Http/Controllers/Admin/HostingPlanController.php:58
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
show.head = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::edit
 * @see app/Http/Controllers/Admin/HostingPlanController.php:65
 * @route '/admin/hosting-plans/{hosting_plan}/edit'
 */
export const edit = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/hosting-plans/{hosting_plan}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::edit
 * @see app/Http/Controllers/Admin/HostingPlanController.php:65
 * @route '/admin/hosting-plans/{hosting_plan}/edit'
 */
edit.url = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { hosting_plan: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { hosting_plan: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    hosting_plan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        hosting_plan: typeof args.hosting_plan === 'object'
                ? args.hosting_plan.id
                : args.hosting_plan,
                }

    return edit.definition.url
            .replace('{hosting_plan}', parsedArgs.hosting_plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::edit
 * @see app/Http/Controllers/Admin/HostingPlanController.php:65
 * @route '/admin/hosting-plans/{hosting_plan}/edit'
 */
edit.get = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::edit
 * @see app/Http/Controllers/Admin/HostingPlanController.php:65
 * @route '/admin/hosting-plans/{hosting_plan}/edit'
 */
edit.head = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::update
 * @see app/Http/Controllers/Admin/HostingPlanController.php:72
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
export const update = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/hosting-plans/{hosting_plan}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::update
 * @see app/Http/Controllers/Admin/HostingPlanController.php:72
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
update.url = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { hosting_plan: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { hosting_plan: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    hosting_plan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        hosting_plan: typeof args.hosting_plan === 'object'
                ? args.hosting_plan.id
                : args.hosting_plan,
                }

    return update.definition.url
            .replace('{hosting_plan}', parsedArgs.hosting_plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::update
 * @see app/Http/Controllers/Admin/HostingPlanController.php:72
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
update.put = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::update
 * @see app/Http/Controllers/Admin/HostingPlanController.php:72
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
update.patch = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::destroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:94
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
export const destroy = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/hosting-plans/{hosting_plan}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::destroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:94
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
destroy.url = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { hosting_plan: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { hosting_plan: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    hosting_plan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        hosting_plan: typeof args.hosting_plan === 'object'
                ? args.hosting_plan.id
                : args.hosting_plan,
                }

    return destroy.definition.url
            .replace('{hosting_plan}', parsedArgs.hosting_plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::destroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:94
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
destroy.delete = (args: { hosting_plan: number | { id: number } } | [hosting_plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})
const hostingPlans = {
    bulkDestroy: Object.assign(bulkDestroy, bulkDestroy),
index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
show: Object.assign(show, show),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default hostingPlans