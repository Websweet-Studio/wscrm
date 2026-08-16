import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Customer\MaintenanceController::index
 * @see app/Http/Controllers/Customer/MaintenanceController.php:13
 * @route '/customer/maintenance'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/customer/maintenance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\MaintenanceController::index
 * @see app/Http/Controllers/Customer/MaintenanceController.php:13
 * @route '/customer/maintenance'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\MaintenanceController::index
 * @see app/Http/Controllers/Customer/MaintenanceController.php:13
 * @route '/customer/maintenance'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\MaintenanceController::index
 * @see app/Http/Controllers/Customer/MaintenanceController.php:13
 * @route '/customer/maintenance'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Customer\MaintenanceController::index
 * @see app/Http/Controllers/Customer/MaintenanceController.php:13
 * @route '/customer/maintenance'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Customer\MaintenanceController::index
 * @see app/Http/Controllers/Customer/MaintenanceController.php:13
 * @route '/customer/maintenance'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Customer\MaintenanceController::index
 * @see app/Http/Controllers/Customer/MaintenanceController.php:13
 * @route '/customer/maintenance'
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
* @see \App\Http\Controllers\Customer\MaintenanceController::exportMethod
 * @see app/Http/Controllers/Customer/MaintenanceController.php:52
 * @route '/customer/maintenance/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/customer/maintenance/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\MaintenanceController::exportMethod
 * @see app/Http/Controllers/Customer/MaintenanceController.php:52
 * @route '/customer/maintenance/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\MaintenanceController::exportMethod
 * @see app/Http/Controllers/Customer/MaintenanceController.php:52
 * @route '/customer/maintenance/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\MaintenanceController::exportMethod
 * @see app/Http/Controllers/Customer/MaintenanceController.php:52
 * @route '/customer/maintenance/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Customer\MaintenanceController::exportMethod
 * @see app/Http/Controllers/Customer/MaintenanceController.php:52
 * @route '/customer/maintenance/export'
 */
    const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Customer\MaintenanceController::exportMethod
 * @see app/Http/Controllers/Customer/MaintenanceController.php:52
 * @route '/customer/maintenance/export'
 */
        exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Customer\MaintenanceController::exportMethod
 * @see app/Http/Controllers/Customer/MaintenanceController.php:52
 * @route '/customer/maintenance/export'
 */
        exportMethodForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportMethod.form = exportMethodForm
const maintenance = {
    index: Object.assign(index, index),
export: Object.assign(exportMethod, exportMethod),
}

export default maintenance