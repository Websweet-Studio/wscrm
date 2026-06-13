import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\DemoPackageController::index
 * @see app/Http/Controllers/Admin/DemoPackageController.php:13
 * @route '/admin/demo-packages'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/demo-packages',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::index
 * @see app/Http/Controllers/Admin/DemoPackageController.php:13
 * @route '/admin/demo-packages'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::index
 * @see app/Http/Controllers/Admin/DemoPackageController.php:13
 * @route '/admin/demo-packages'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DemoPackageController::index
 * @see app/Http/Controllers/Admin/DemoPackageController.php:13
 * @route '/admin/demo-packages'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::store
 * @see app/Http/Controllers/Admin/DemoPackageController.php:28
 * @route '/admin/demo-packages'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/demo-packages',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::store
 * @see app/Http/Controllers/Admin/DemoPackageController.php:28
 * @route '/admin/demo-packages'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::store
 * @see app/Http/Controllers/Admin/DemoPackageController.php:28
 * @route '/admin/demo-packages'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::update
 * @see app/Http/Controllers/Admin/DemoPackageController.php:47
 * @route '/admin/demo-packages/{demo_package}'
 */
export const update = (args: { demo_package: number | { id: number } } | [demo_package: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/demo-packages/{demo_package}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::update
 * @see app/Http/Controllers/Admin/DemoPackageController.php:47
 * @route '/admin/demo-packages/{demo_package}'
 */
update.url = (args: { demo_package: number | { id: number } } | [demo_package: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { demo_package: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { demo_package: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    demo_package: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        demo_package: typeof args.demo_package === 'object'
                ? args.demo_package.id
                : args.demo_package,
                }

    return update.definition.url
            .replace('{demo_package}', parsedArgs.demo_package.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::update
 * @see app/Http/Controllers/Admin/DemoPackageController.php:47
 * @route '/admin/demo-packages/{demo_package}'
 */
update.put = (args: { demo_package: number | { id: number } } | [demo_package: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\DemoPackageController::update
 * @see app/Http/Controllers/Admin/DemoPackageController.php:47
 * @route '/admin/demo-packages/{demo_package}'
 */
update.patch = (args: { demo_package: number | { id: number } } | [demo_package: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::destroy
 * @see app/Http/Controllers/Admin/DemoPackageController.php:66
 * @route '/admin/demo-packages/{demo_package}'
 */
export const destroy = (args: { demo_package: number | { id: number } } | [demo_package: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/demo-packages/{demo_package}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::destroy
 * @see app/Http/Controllers/Admin/DemoPackageController.php:66
 * @route '/admin/demo-packages/{demo_package}'
 */
destroy.url = (args: { demo_package: number | { id: number } } | [demo_package: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { demo_package: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { demo_package: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    demo_package: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        demo_package: typeof args.demo_package === 'object'
                ? args.demo_package.id
                : args.demo_package,
                }

    return destroy.definition.url
            .replace('{demo_package}', parsedArgs.demo_package.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::destroy
 * @see app/Http/Controllers/Admin/DemoPackageController.php:66
 * @route '/admin/demo-packages/{demo_package}'
 */
destroy.delete = (args: { demo_package: number | { id: number } } | [demo_package: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::toggleStatus
 * @see app/Http/Controllers/Admin/DemoPackageController.php:73
 * @route '/admin/demo-packages/{demoPackage}/toggle-status'
 */
export const toggleStatus = (args: { demoPackage: number | { id: number } } | [demoPackage: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleStatus.url(args, options),
    method: 'patch',
})

toggleStatus.definition = {
    methods: ["patch"],
    url: '/admin/demo-packages/{demoPackage}/toggle-status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::toggleStatus
 * @see app/Http/Controllers/Admin/DemoPackageController.php:73
 * @route '/admin/demo-packages/{demoPackage}/toggle-status'
 */
toggleStatus.url = (args: { demoPackage: number | { id: number } } | [demoPackage: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { demoPackage: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { demoPackage: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    demoPackage: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        demoPackage: typeof args.demoPackage === 'object'
                ? args.demoPackage.id
                : args.demoPackage,
                }

    return toggleStatus.definition.url
            .replace('{demoPackage}', parsedArgs.demoPackage.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoPackageController::toggleStatus
 * @see app/Http/Controllers/Admin/DemoPackageController.php:73
 * @route '/admin/demo-packages/{demoPackage}/toggle-status'
 */
toggleStatus.patch = (args: { demoPackage: number | { id: number } } | [demoPackage: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleStatus.url(args, options),
    method: 'patch',
})
const demoPackages = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
toggleStatus: Object.assign(toggleStatus, toggleStatus),
}

export default demoPackages