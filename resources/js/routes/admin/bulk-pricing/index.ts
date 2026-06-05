import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\BulkPricingController::index
 * @see app/Http/Controllers/Admin/BulkPricingController.php:15
 * @route '/admin/bulk-pricing'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/bulk-pricing',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::index
 * @see app/Http/Controllers/Admin/BulkPricingController.php:15
 * @route '/admin/bulk-pricing'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::index
 * @see app/Http/Controllers/Admin/BulkPricingController.php:15
 * @route '/admin/bulk-pricing'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BulkPricingController::index
 * @see app/Http/Controllers/Admin/BulkPricingController.php:15
 * @route '/admin/bulk-pricing'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\BulkPricingController::index
 * @see app/Http/Controllers/Admin/BulkPricingController.php:15
 * @route '/admin/bulk-pricing'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\BulkPricingController::index
 * @see app/Http/Controllers/Admin/BulkPricingController.php:15
 * @route '/admin/bulk-pricing'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\BulkPricingController::index
 * @see app/Http/Controllers/Admin/BulkPricingController.php:15
 * @route '/admin/bulk-pricing'
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
* @see \App\Http\Controllers\Admin\BulkPricingController::simulate
 * @see app/Http/Controllers/Admin/BulkPricingController.php:49
 * @route '/admin/bulk-pricing/simulate'
 */
export const simulate = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: simulate.url(options),
    method: 'post',
})

simulate.definition = {
    methods: ["post"],
    url: '/admin/bulk-pricing/simulate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::simulate
 * @see app/Http/Controllers/Admin/BulkPricingController.php:49
 * @route '/admin/bulk-pricing/simulate'
 */
simulate.url = (options?: RouteQueryOptions) => {
    return simulate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::simulate
 * @see app/Http/Controllers/Admin/BulkPricingController.php:49
 * @route '/admin/bulk-pricing/simulate'
 */
simulate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: simulate.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\BulkPricingController::simulate
 * @see app/Http/Controllers/Admin/BulkPricingController.php:49
 * @route '/admin/bulk-pricing/simulate'
 */
    const simulateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: simulate.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\BulkPricingController::simulate
 * @see app/Http/Controllers/Admin/BulkPricingController.php:49
 * @route '/admin/bulk-pricing/simulate'
 */
        simulateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: simulate.url(options),
            method: 'post',
        })
    
    simulate.form = simulateForm
/**
* @see \App\Http\Controllers\Admin\BulkPricingController::apply
 * @see app/Http/Controllers/Admin/BulkPricingController.php:92
 * @route '/admin/bulk-pricing/apply'
 */
export const apply = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: apply.url(options),
    method: 'post',
})

apply.definition = {
    methods: ["post"],
    url: '/admin/bulk-pricing/apply',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::apply
 * @see app/Http/Controllers/Admin/BulkPricingController.php:92
 * @route '/admin/bulk-pricing/apply'
 */
apply.url = (options?: RouteQueryOptions) => {
    return apply.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::apply
 * @see app/Http/Controllers/Admin/BulkPricingController.php:92
 * @route '/admin/bulk-pricing/apply'
 */
apply.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: apply.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\BulkPricingController::apply
 * @see app/Http/Controllers/Admin/BulkPricingController.php:92
 * @route '/admin/bulk-pricing/apply'
 */
    const applyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: apply.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\BulkPricingController::apply
 * @see app/Http/Controllers/Admin/BulkPricingController.php:92
 * @route '/admin/bulk-pricing/apply'
 */
        applyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: apply.url(options),
            method: 'post',
        })
    
    apply.form = applyForm
/**
* @see \App\Http\Controllers\Admin\BulkPricingController::saveConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:151
 * @route '/admin/bulk-pricing/save-config'
 */
export const saveConfig = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveConfig.url(options),
    method: 'post',
})

saveConfig.definition = {
    methods: ["post"],
    url: '/admin/bulk-pricing/save-config',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::saveConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:151
 * @route '/admin/bulk-pricing/save-config'
 */
saveConfig.url = (options?: RouteQueryOptions) => {
    return saveConfig.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::saveConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:151
 * @route '/admin/bulk-pricing/save-config'
 */
saveConfig.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveConfig.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\BulkPricingController::saveConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:151
 * @route '/admin/bulk-pricing/save-config'
 */
    const saveConfigForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: saveConfig.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\BulkPricingController::saveConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:151
 * @route '/admin/bulk-pricing/save-config'
 */
        saveConfigForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: saveConfig.url(options),
            method: 'post',
        })
    
    saveConfig.form = saveConfigForm
/**
* @see \App\Http\Controllers\Admin\BulkPricingController::loadConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:232
 * @route '/admin/bulk-pricing/load-config/{id}'
 */
export const loadConfig = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: loadConfig.url(args, options),
    method: 'get',
})

loadConfig.definition = {
    methods: ["get","head"],
    url: '/admin/bulk-pricing/load-config/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::loadConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:232
 * @route '/admin/bulk-pricing/load-config/{id}'
 */
loadConfig.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return loadConfig.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::loadConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:232
 * @route '/admin/bulk-pricing/load-config/{id}'
 */
loadConfig.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: loadConfig.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BulkPricingController::loadConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:232
 * @route '/admin/bulk-pricing/load-config/{id}'
 */
loadConfig.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: loadConfig.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\BulkPricingController::loadConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:232
 * @route '/admin/bulk-pricing/load-config/{id}'
 */
    const loadConfigForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: loadConfig.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\BulkPricingController::loadConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:232
 * @route '/admin/bulk-pricing/load-config/{id}'
 */
        loadConfigForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: loadConfig.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\BulkPricingController::loadConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:232
 * @route '/admin/bulk-pricing/load-config/{id}'
 */
        loadConfigForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: loadConfig.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    loadConfig.form = loadConfigForm
/**
* @see \App\Http\Controllers\Admin\BulkPricingController::deleteConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:244
 * @route '/admin/bulk-pricing/delete-config/{id}'
 */
export const deleteConfig = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteConfig.url(args, options),
    method: 'delete',
})

deleteConfig.definition = {
    methods: ["delete"],
    url: '/admin/bulk-pricing/delete-config/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::deleteConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:244
 * @route '/admin/bulk-pricing/delete-config/{id}'
 */
deleteConfig.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return deleteConfig.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::deleteConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:244
 * @route '/admin/bulk-pricing/delete-config/{id}'
 */
deleteConfig.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteConfig.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\BulkPricingController::deleteConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:244
 * @route '/admin/bulk-pricing/delete-config/{id}'
 */
    const deleteConfigForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deleteConfig.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\BulkPricingController::deleteConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:244
 * @route '/admin/bulk-pricing/delete-config/{id}'
 */
        deleteConfigForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deleteConfig.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    deleteConfig.form = deleteConfigForm
const bulkPricing = {
    index: Object.assign(index, index),
simulate: Object.assign(simulate, simulate),
apply: Object.assign(apply, apply),
saveConfig: Object.assign(saveConfig, saveConfig),
loadConfig: Object.assign(loadConfig, loadConfig),
deleteConfig: Object.assign(deleteConfig, deleteConfig),
}

export default bulkPricing