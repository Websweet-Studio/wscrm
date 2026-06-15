import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\OrderController::bulkDestroy
 * @see app/Http/Controllers/Admin/OrderController.php:448
 * @route '/admin/orders/bulk'
 */
export const bulkDestroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

bulkDestroy.definition = {
    methods: ["delete"],
    url: '/admin/orders/bulk',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::bulkDestroy
 * @see app/Http/Controllers/Admin/OrderController.php:448
 * @route '/admin/orders/bulk'
 */
bulkDestroy.url = (options?: RouteQueryOptions) => {
    return bulkDestroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::bulkDestroy
 * @see app/Http/Controllers/Admin/OrderController.php:448
 * @route '/admin/orders/bulk'
 */
bulkDestroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::bulkDestroy
 * @see app/Http/Controllers/Admin/OrderController.php:448
 * @route '/admin/orders/bulk'
 */
    const bulkDestroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkDestroy.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::bulkDestroy
 * @see app/Http/Controllers/Admin/OrderController.php:448
 * @route '/admin/orders/bulk'
 */
        bulkDestroyForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkDestroy.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    bulkDestroy.form = bulkDestroyForm
/**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:23
 * @route '/admin/orders'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:23
 * @route '/admin/orders'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:23
 * @route '/admin/orders'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:23
 * @route '/admin/orders'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:23
 * @route '/admin/orders'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:23
 * @route '/admin/orders'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:23
 * @route '/admin/orders'
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
* @see \App\Http\Controllers\Admin\OrderController::store
 * @see app/Http/Controllers/Admin/OrderController.php:148
 * @route '/admin/orders'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/orders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::store
 * @see app/Http/Controllers/Admin/OrderController.php:148
 * @route '/admin/orders'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::store
 * @see app/Http/Controllers/Admin/OrderController.php:148
 * @route '/admin/orders'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::store
 * @see app/Http/Controllers/Admin/OrderController.php:148
 * @route '/admin/orders'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::store
 * @see app/Http/Controllers/Admin/OrderController.php:148
 * @route '/admin/orders'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:111
 * @route '/admin/orders/{order}'
 */
export const show = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/orders/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:111
 * @route '/admin/orders/{order}'
 */
show.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return show.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:111
 * @route '/admin/orders/{order}'
 */
show.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:111
 * @route '/admin/orders/{order}'
 */
show.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:111
 * @route '/admin/orders/{order}'
 */
    const showForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:111
 * @route '/admin/orders/{order}'
 */
        showForm.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:111
 * @route '/admin/orders/{order}'
 */
        showForm.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:270
 * @route '/admin/orders/{order}'
 */
export const update = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/orders/{order}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:270
 * @route '/admin/orders/{order}'
 */
update.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return update.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:270
 * @route '/admin/orders/{order}'
 */
update.put = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:270
 * @route '/admin/orders/{order}'
 */
update.patch = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:270
 * @route '/admin/orders/{order}'
 */
    const updateForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:270
 * @route '/admin/orders/{order}'
 */
        updateForm.put = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\OrderController::update
 * @see app/Http/Controllers/Admin/OrderController.php:270
 * @route '/admin/orders/{order}'
 */
        updateForm.patch = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\OrderController::destroy
 * @see app/Http/Controllers/Admin/OrderController.php:427
 * @route '/admin/orders/{order}'
 */
export const destroy = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/orders/{order}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::destroy
 * @see app/Http/Controllers/Admin/OrderController.php:427
 * @route '/admin/orders/{order}'
 */
destroy.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return destroy.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::destroy
 * @see app/Http/Controllers/Admin/OrderController.php:427
 * @route '/admin/orders/{order}'
 */
destroy.delete = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::destroy
 * @see app/Http/Controllers/Admin/OrderController.php:427
 * @route '/admin/orders/{order}'
 */
    const destroyForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::destroy
 * @see app/Http/Controllers/Admin/OrderController.php:427
 * @route '/admin/orders/{order}'
 */
        destroyForm.delete = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\OrderController::simulateUpgradeDowngrade
 * @see app/Http/Controllers/Admin/OrderController.php:489
 * @route '/admin/orders/{order}/simulate-upgrade-downgrade'
 */
export const simulateUpgradeDowngrade = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: simulateUpgradeDowngrade.url(args, options),
    method: 'post',
})

simulateUpgradeDowngrade.definition = {
    methods: ["post"],
    url: '/admin/orders/{order}/simulate-upgrade-downgrade',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::simulateUpgradeDowngrade
 * @see app/Http/Controllers/Admin/OrderController.php:489
 * @route '/admin/orders/{order}/simulate-upgrade-downgrade'
 */
simulateUpgradeDowngrade.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return simulateUpgradeDowngrade.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::simulateUpgradeDowngrade
 * @see app/Http/Controllers/Admin/OrderController.php:489
 * @route '/admin/orders/{order}/simulate-upgrade-downgrade'
 */
simulateUpgradeDowngrade.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: simulateUpgradeDowngrade.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::simulateUpgradeDowngrade
 * @see app/Http/Controllers/Admin/OrderController.php:489
 * @route '/admin/orders/{order}/simulate-upgrade-downgrade'
 */
    const simulateUpgradeDowngradeForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: simulateUpgradeDowngrade.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::simulateUpgradeDowngrade
 * @see app/Http/Controllers/Admin/OrderController.php:489
 * @route '/admin/orders/{order}/simulate-upgrade-downgrade'
 */
        simulateUpgradeDowngradeForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: simulateUpgradeDowngrade.url(args, options),
            method: 'post',
        })
    
    simulateUpgradeDowngrade.form = simulateUpgradeDowngradeForm
/**
* @see \App\Http\Controllers\Admin\OrderController::processUpgradeDowngrade
 * @see app/Http/Controllers/Admin/OrderController.php:536
 * @route '/admin/orders/{order}/process-upgrade-downgrade'
 */
export const processUpgradeDowngrade = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: processUpgradeDowngrade.url(args, options),
    method: 'post',
})

processUpgradeDowngrade.definition = {
    methods: ["post"],
    url: '/admin/orders/{order}/process-upgrade-downgrade',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::processUpgradeDowngrade
 * @see app/Http/Controllers/Admin/OrderController.php:536
 * @route '/admin/orders/{order}/process-upgrade-downgrade'
 */
processUpgradeDowngrade.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return processUpgradeDowngrade.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::processUpgradeDowngrade
 * @see app/Http/Controllers/Admin/OrderController.php:536
 * @route '/admin/orders/{order}/process-upgrade-downgrade'
 */
processUpgradeDowngrade.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: processUpgradeDowngrade.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::processUpgradeDowngrade
 * @see app/Http/Controllers/Admin/OrderController.php:536
 * @route '/admin/orders/{order}/process-upgrade-downgrade'
 */
    const processUpgradeDowngradeForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: processUpgradeDowngrade.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::processUpgradeDowngrade
 * @see app/Http/Controllers/Admin/OrderController.php:536
 * @route '/admin/orders/{order}/process-upgrade-downgrade'
 */
        processUpgradeDowngradeForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: processUpgradeDowngrade.url(args, options),
            method: 'post',
        })
    
    processUpgradeDowngrade.form = processUpgradeDowngradeForm
const orders = {
    bulkDestroy: Object.assign(bulkDestroy, bulkDestroy),
index: Object.assign(index, index),
store: Object.assign(store, store),
show: Object.assign(show, show),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
simulateUpgradeDowngrade: Object.assign(simulateUpgradeDowngrade, simulateUpgradeDowngrade),
processUpgradeDowngrade: Object.assign(processUpgradeDowngrade, processUpgradeDowngrade),
}

export default orders