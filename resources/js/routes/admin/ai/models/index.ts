import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\Ai\ModelController::index
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:15
 * @route '/admin/ai/models'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/ai/models',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\Ai\ModelController::index
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:15
 * @route '/admin/ai/models'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\ModelController::index
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:15
 * @route '/admin/ai/models'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\Ai\ModelController::index
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:15
 * @route '/admin/ai/models'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\ModelController::index
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:15
 * @route '/admin/ai/models'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\ModelController::index
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:15
 * @route '/admin/ai/models'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\Ai\ModelController::index
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:15
 * @route '/admin/ai/models'
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
* @see \App\Http\Controllers\Admin\Ai\ModelController::store
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:46
 * @route '/admin/ai/models'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/ai/models',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\Ai\ModelController::store
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:46
 * @route '/admin/ai/models'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\ModelController::store
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:46
 * @route '/admin/ai/models'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\ModelController::store
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:46
 * @route '/admin/ai/models'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\ModelController::store
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:46
 * @route '/admin/ai/models'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\Ai\ModelController::update
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:71
 * @route '/admin/ai/models/{model}'
 */
export const update = (args: { model: number | { id: number } } | [model: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/ai/models/{model}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\Ai\ModelController::update
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:71
 * @route '/admin/ai/models/{model}'
 */
update.url = (args: { model: number | { id: number } } | [model: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { model: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { model: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    model: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        model: typeof args.model === 'object'
                ? args.model.id
                : args.model,
                }

    return update.definition.url
            .replace('{model}', parsedArgs.model.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\ModelController::update
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:71
 * @route '/admin/ai/models/{model}'
 */
update.put = (args: { model: number | { id: number } } | [model: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\Ai\ModelController::update
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:71
 * @route '/admin/ai/models/{model}'
 */
update.patch = (args: { model: number | { id: number } } | [model: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\ModelController::update
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:71
 * @route '/admin/ai/models/{model}'
 */
    const updateForm = (args: { model: number | { id: number } } | [model: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\ModelController::update
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:71
 * @route '/admin/ai/models/{model}'
 */
        updateForm.put = (args: { model: number | { id: number } } | [model: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\Ai\ModelController::update
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:71
 * @route '/admin/ai/models/{model}'
 */
        updateForm.patch = (args: { model: number | { id: number } } | [model: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\Ai\ModelController::destroy
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:96
 * @route '/admin/ai/models/{model}'
 */
export const destroy = (args: { model: number | { id: number } } | [model: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/ai/models/{model}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\Ai\ModelController::destroy
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:96
 * @route '/admin/ai/models/{model}'
 */
destroy.url = (args: { model: number | { id: number } } | [model: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { model: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { model: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    model: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        model: typeof args.model === 'object'
                ? args.model.id
                : args.model,
                }

    return destroy.definition.url
            .replace('{model}', parsedArgs.model.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\ModelController::destroy
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:96
 * @route '/admin/ai/models/{model}'
 */
destroy.delete = (args: { model: number | { id: number } } | [model: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\ModelController::destroy
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:96
 * @route '/admin/ai/models/{model}'
 */
    const destroyForm = (args: { model: number | { id: number } } | [model: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\ModelController::destroy
 * @see app/Http/Controllers/Admin/Ai/ModelController.php:96
 * @route '/admin/ai/models/{model}'
 */
        destroyForm.delete = (args: { model: number | { id: number } } | [model: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const models = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default models