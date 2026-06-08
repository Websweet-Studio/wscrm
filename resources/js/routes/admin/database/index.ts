import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\DatabaseController::index
 * @see app/Http/Controllers/Admin/DatabaseController.php:15
 * @route '/admin/database'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/database',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DatabaseController::index
 * @see app/Http/Controllers/Admin/DatabaseController.php:15
 * @route '/admin/database'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DatabaseController::index
 * @see app/Http/Controllers/Admin/DatabaseController.php:15
 * @route '/admin/database'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DatabaseController::index
 * @see app/Http/Controllers/Admin/DatabaseController.php:15
 * @route '/admin/database'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\DatabaseController::index
 * @see app/Http/Controllers/Admin/DatabaseController.php:15
 * @route '/admin/database'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\DatabaseController::index
 * @see app/Http/Controllers/Admin/DatabaseController.php:15
 * @route '/admin/database'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\DatabaseController::index
 * @see app/Http/Controllers/Admin/DatabaseController.php:15
 * @route '/admin/database'
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
* @see \App\Http\Controllers\Admin\DatabaseController::exportMethod
 * @see app/Http/Controllers/Admin/DatabaseController.php:20
 * @route '/admin/database/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/admin/database/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DatabaseController::exportMethod
 * @see app/Http/Controllers/Admin/DatabaseController.php:20
 * @route '/admin/database/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DatabaseController::exportMethod
 * @see app/Http/Controllers/Admin/DatabaseController.php:20
 * @route '/admin/database/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DatabaseController::exportMethod
 * @see app/Http/Controllers/Admin/DatabaseController.php:20
 * @route '/admin/database/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\DatabaseController::exportMethod
 * @see app/Http/Controllers/Admin/DatabaseController.php:20
 * @route '/admin/database/export'
 */
    const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\DatabaseController::exportMethod
 * @see app/Http/Controllers/Admin/DatabaseController.php:20
 * @route '/admin/database/export'
 */
        exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\DatabaseController::exportMethod
 * @see app/Http/Controllers/Admin/DatabaseController.php:20
 * @route '/admin/database/export'
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
/**
* @see \App\Http\Controllers\Admin\DatabaseController::importMethod
 * @see app/Http/Controllers/Admin/DatabaseController.php:111
 * @route '/admin/database/import'
 */
export const importMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

importMethod.definition = {
    methods: ["post"],
    url: '/admin/database/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DatabaseController::importMethod
 * @see app/Http/Controllers/Admin/DatabaseController.php:111
 * @route '/admin/database/import'
 */
importMethod.url = (options?: RouteQueryOptions) => {
    return importMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DatabaseController::importMethod
 * @see app/Http/Controllers/Admin/DatabaseController.php:111
 * @route '/admin/database/import'
 */
importMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\DatabaseController::importMethod
 * @see app/Http/Controllers/Admin/DatabaseController.php:111
 * @route '/admin/database/import'
 */
    const importMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importMethod.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DatabaseController::importMethod
 * @see app/Http/Controllers/Admin/DatabaseController.php:111
 * @route '/admin/database/import'
 */
        importMethodForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importMethod.url(options),
            method: 'post',
        })
    
    importMethod.form = importMethodForm
const database = {
    index: Object.assign(index, index),
export: Object.assign(exportMethod, exportMethod),
import: Object.assign(importMethod, importMethod),
}

export default database