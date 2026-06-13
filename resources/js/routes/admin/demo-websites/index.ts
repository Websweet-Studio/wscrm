import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::index
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:16
 * @route '/admin/demo-websites'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/demo-websites',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::index
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:16
 * @route '/admin/demo-websites'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::index
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:16
 * @route '/admin/demo-websites'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::index
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:16
 * @route '/admin/demo-websites'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::store
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:49
 * @route '/admin/demo-websites'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/demo-websites',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::store
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:49
 * @route '/admin/demo-websites'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::store
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:49
 * @route '/admin/demo-websites'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::update
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:80
 * @route '/admin/demo-websites/{demo_website}'
 */
export const update = (args: { demo_website: number | { id: number } } | [demo_website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/demo-websites/{demo_website}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::update
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:80
 * @route '/admin/demo-websites/{demo_website}'
 */
update.url = (args: { demo_website: number | { id: number } } | [demo_website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { demo_website: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { demo_website: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    demo_website: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        demo_website: typeof args.demo_website === 'object'
                ? args.demo_website.id
                : args.demo_website,
                }

    return update.definition.url
            .replace('{demo_website}', parsedArgs.demo_website.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::update
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:80
 * @route '/admin/demo-websites/{demo_website}'
 */
update.put = (args: { demo_website: number | { id: number } } | [demo_website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::update
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:80
 * @route '/admin/demo-websites/{demo_website}'
 */
update.patch = (args: { demo_website: number | { id: number } } | [demo_website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::destroy
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:114
 * @route '/admin/demo-websites/{demo_website}'
 */
export const destroy = (args: { demo_website: number | { id: number } } | [demo_website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/demo-websites/{demo_website}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::destroy
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:114
 * @route '/admin/demo-websites/{demo_website}'
 */
destroy.url = (args: { demo_website: number | { id: number } } | [demo_website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { demo_website: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { demo_website: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    demo_website: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        demo_website: typeof args.demo_website === 'object'
                ? args.demo_website.id
                : args.demo_website,
                }

    return destroy.definition.url
            .replace('{demo_website}', parsedArgs.demo_website.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::destroy
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:114
 * @route '/admin/demo-websites/{demo_website}'
 */
destroy.delete = (args: { demo_website: number | { id: number } } | [demo_website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::toggleStatus
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:126
 * @route '/admin/demo-websites/{demoWebsite}/toggle-status'
 */
export const toggleStatus = (args: { demoWebsite: number | { id: number } } | [demoWebsite: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleStatus.url(args, options),
    method: 'patch',
})

toggleStatus.definition = {
    methods: ["patch"],
    url: '/admin/demo-websites/{demoWebsite}/toggle-status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::toggleStatus
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:126
 * @route '/admin/demo-websites/{demoWebsite}/toggle-status'
 */
toggleStatus.url = (args: { demoWebsite: number | { id: number } } | [demoWebsite: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return toggleStatus.definition.url
            .replace('{demoWebsite}', parsedArgs.demoWebsite.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoWebsiteController::toggleStatus
 * @see app/Http/Controllers/Admin/DemoWebsiteController.php:126
 * @route '/admin/demo-websites/{demoWebsite}/toggle-status'
 */
toggleStatus.patch = (args: { demoWebsite: number | { id: number } } | [demoWebsite: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleStatus.url(args, options),
    method: 'patch',
})
const demoWebsites = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
toggleStatus: Object.assign(toggleStatus, toggleStatus),
}

export default demoWebsites