import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\AdminToolsController::index
 * @see app/Http/Controllers/Admin/AdminToolsController.php:31
 * @route '/admin/tools'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/tools',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AdminToolsController::index
 * @see app/Http/Controllers/Admin/AdminToolsController.php:31
 * @route '/admin/tools'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminToolsController::index
 * @see app/Http/Controllers/Admin/AdminToolsController.php:31
 * @route '/admin/tools'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AdminToolsController::index
 * @see app/Http/Controllers/Admin/AdminToolsController.php:31
 * @route '/admin/tools'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\AdminToolsController::index
 * @see app/Http/Controllers/Admin/AdminToolsController.php:31
 * @route '/admin/tools'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminToolsController::index
 * @see app/Http/Controllers/Admin/AdminToolsController.php:31
 * @route '/admin/tools'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\AdminToolsController::index
 * @see app/Http/Controllers/Admin/AdminToolsController.php:31
 * @route '/admin/tools'
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
* @see \App\Http\Controllers\Admin\AdminToolsController::execute
 * @see app/Http/Controllers/Admin/AdminToolsController.php:50
 * @route '/admin/tools/execute'
 */
export const execute = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: execute.url(options),
    method: 'post',
})

execute.definition = {
    methods: ["post"],
    url: '/admin/tools/execute',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\AdminToolsController::execute
 * @see app/Http/Controllers/Admin/AdminToolsController.php:50
 * @route '/admin/tools/execute'
 */
execute.url = (options?: RouteQueryOptions) => {
    return execute.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminToolsController::execute
 * @see app/Http/Controllers/Admin/AdminToolsController.php:50
 * @route '/admin/tools/execute'
 */
execute.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: execute.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\AdminToolsController::execute
 * @see app/Http/Controllers/Admin/AdminToolsController.php:50
 * @route '/admin/tools/execute'
 */
    const executeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: execute.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminToolsController::execute
 * @see app/Http/Controllers/Admin/AdminToolsController.php:50
 * @route '/admin/tools/execute'
 */
        executeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: execute.url(options),
            method: 'post',
        })
    
    execute.form = executeForm
const tools = {
    index: Object.assign(index, index),
execute: Object.assign(execute, execute),
}

export default tools