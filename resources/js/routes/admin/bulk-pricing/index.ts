import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\BulkPricingController::index
 * @see app/Http/Controllers/Admin/BulkPricingController.php:19
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
 * @see app/Http/Controllers/Admin/BulkPricingController.php:19
 * @route '/admin/bulk-pricing'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::index
 * @see app/Http/Controllers/Admin/BulkPricingController.php:19
 * @route '/admin/bulk-pricing'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BulkPricingController::index
 * @see app/Http/Controllers/Admin/BulkPricingController.php:19
 * @route '/admin/bulk-pricing'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::simulate
 * @see app/Http/Controllers/Admin/BulkPricingController.php:44
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
 * @see app/Http/Controllers/Admin/BulkPricingController.php:44
 * @route '/admin/bulk-pricing/simulate'
 */
simulate.url = (options?: RouteQueryOptions) => {
    return simulate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::simulate
 * @see app/Http/Controllers/Admin/BulkPricingController.php:44
 * @route '/admin/bulk-pricing/simulate'
 */
simulate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: simulate.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::apply
 * @see app/Http/Controllers/Admin/BulkPricingController.php:81
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
 * @see app/Http/Controllers/Admin/BulkPricingController.php:81
 * @route '/admin/bulk-pricing/apply'
 */
apply.url = (options?: RouteQueryOptions) => {
    return apply.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::apply
 * @see app/Http/Controllers/Admin/BulkPricingController.php:81
 * @route '/admin/bulk-pricing/apply'
 */
apply.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: apply.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::saveConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:162
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
 * @see app/Http/Controllers/Admin/BulkPricingController.php:162
 * @route '/admin/bulk-pricing/save-config'
 */
saveConfig.url = (options?: RouteQueryOptions) => {
    return saveConfig.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::saveConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:162
 * @route '/admin/bulk-pricing/save-config'
 */
saveConfig.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveConfig.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::loadConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:243
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
 * @see app/Http/Controllers/Admin/BulkPricingController.php:243
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
 * @see app/Http/Controllers/Admin/BulkPricingController.php:243
 * @route '/admin/bulk-pricing/load-config/{id}'
 */
loadConfig.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: loadConfig.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BulkPricingController::loadConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:243
 * @route '/admin/bulk-pricing/load-config/{id}'
 */
loadConfig.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: loadConfig.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\BulkPricingController::deleteConfig
 * @see app/Http/Controllers/Admin/BulkPricingController.php:255
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
 * @see app/Http/Controllers/Admin/BulkPricingController.php:255
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
 * @see app/Http/Controllers/Admin/BulkPricingController.php:255
 * @route '/admin/bulk-pricing/delete-config/{id}'
 */
deleteConfig.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteConfig.url(args, options),
    method: 'delete',
})
const bulkPricing = {
    index: Object.assign(index, index),
simulate: Object.assign(simulate, simulate),
apply: Object.assign(apply, apply),
saveConfig: Object.assign(saveConfig, saveConfig),
loadConfig: Object.assign(loadConfig, loadConfig),
deleteConfig: Object.assign(deleteConfig, deleteConfig),
}

export default bulkPricing