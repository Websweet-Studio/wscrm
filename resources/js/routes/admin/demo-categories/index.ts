import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::index
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:13
 * @route '/admin/demo-categories'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/demo-categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::index
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:13
 * @route '/admin/demo-categories'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::index
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:13
 * @route '/admin/demo-categories'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::index
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:13
 * @route '/admin/demo-categories'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\DemoCategoryController::index
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:13
 * @route '/admin/demo-categories'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\DemoCategoryController::index
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:13
 * @route '/admin/demo-categories'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\DemoCategoryController::index
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:13
 * @route '/admin/demo-categories'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::store
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:28
 * @route '/admin/demo-categories'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/demo-categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::store
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:28
 * @route '/admin/demo-categories'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::store
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:28
 * @route '/admin/demo-categories'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\DemoCategoryController::store
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:28
 * @route '/admin/demo-categories'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DemoCategoryController::store
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:28
 * @route '/admin/demo-categories'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::update
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:47
 * @route '/admin/demo-categories/{demo_category}'
 */
export const update = (args: { demo_category: number | { id: number } } | [demo_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/demo-categories/{demo_category}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::update
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:47
 * @route '/admin/demo-categories/{demo_category}'
 */
update.url = (args: { demo_category: number | { id: number } } | [demo_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { demo_category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { demo_category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    demo_category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        demo_category: typeof args.demo_category === 'object'
                ? args.demo_category.id
                : args.demo_category,
                }

    return update.definition.url
            .replace('{demo_category}', parsedArgs.demo_category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::update
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:47
 * @route '/admin/demo-categories/{demo_category}'
 */
update.put = (args: { demo_category: number | { id: number } } | [demo_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::update
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:47
 * @route '/admin/demo-categories/{demo_category}'
 */
update.patch = (args: { demo_category: number | { id: number } } | [demo_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\DemoCategoryController::update
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:47
 * @route '/admin/demo-categories/{demo_category}'
 */
    const updateForm = (args: { demo_category: number | { id: number } } | [demo_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DemoCategoryController::update
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:47
 * @route '/admin/demo-categories/{demo_category}'
 */
        updateForm.put = (args: { demo_category: number | { id: number } } | [demo_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\DemoCategoryController::update
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:47
 * @route '/admin/demo-categories/{demo_category}'
 */
        updateForm.patch = (args: { demo_category: number | { id: number } } | [demo_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::destroy
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:66
 * @route '/admin/demo-categories/{demo_category}'
 */
export const destroy = (args: { demo_category: number | { id: number } } | [demo_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/demo-categories/{demo_category}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::destroy
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:66
 * @route '/admin/demo-categories/{demo_category}'
 */
destroy.url = (args: { demo_category: number | { id: number } } | [demo_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { demo_category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { demo_category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    demo_category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        demo_category: typeof args.demo_category === 'object'
                ? args.demo_category.id
                : args.demo_category,
                }

    return destroy.definition.url
            .replace('{demo_category}', parsedArgs.demo_category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::destroy
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:66
 * @route '/admin/demo-categories/{demo_category}'
 */
destroy.delete = (args: { demo_category: number | { id: number } } | [demo_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\DemoCategoryController::destroy
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:66
 * @route '/admin/demo-categories/{demo_category}'
 */
    const destroyForm = (args: { demo_category: number | { id: number } } | [demo_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DemoCategoryController::destroy
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:66
 * @route '/admin/demo-categories/{demo_category}'
 */
        destroyForm.delete = (args: { demo_category: number | { id: number } } | [demo_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::toggleStatus
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:73
 * @route '/admin/demo-categories/{demoCategory}/toggle-status'
 */
export const toggleStatus = (args: { demoCategory: number | { id: number } } | [demoCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleStatus.url(args, options),
    method: 'patch',
})

toggleStatus.definition = {
    methods: ["patch"],
    url: '/admin/demo-categories/{demoCategory}/toggle-status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::toggleStatus
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:73
 * @route '/admin/demo-categories/{demoCategory}/toggle-status'
 */
toggleStatus.url = (args: { demoCategory: number | { id: number } } | [demoCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { demoCategory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { demoCategory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    demoCategory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        demoCategory: typeof args.demoCategory === 'object'
                ? args.demoCategory.id
                : args.demoCategory,
                }

    return toggleStatus.definition.url
            .replace('{demoCategory}', parsedArgs.demoCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoCategoryController::toggleStatus
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:73
 * @route '/admin/demo-categories/{demoCategory}/toggle-status'
 */
toggleStatus.patch = (args: { demoCategory: number | { id: number } } | [demoCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleStatus.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\DemoCategoryController::toggleStatus
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:73
 * @route '/admin/demo-categories/{demoCategory}/toggle-status'
 */
    const toggleStatusForm = (args: { demoCategory: number | { id: number } } | [demoCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleStatus.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DemoCategoryController::toggleStatus
 * @see app/Http/Controllers/Admin/DemoCategoryController.php:73
 * @route '/admin/demo-categories/{demoCategory}/toggle-status'
 */
        toggleStatusForm.patch = (args: { demoCategory: number | { id: number } } | [demoCategory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleStatus.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggleStatus.form = toggleStatusForm
const demoCategories = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
toggleStatus: Object.assign(toggleStatus, toggleStatus),
}

export default demoCategories