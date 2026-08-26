import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\Ai\ComboController::index
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:15
 * @route '/admin/ai/combos'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/ai/combos',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\Ai\ComboController::index
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:15
 * @route '/admin/ai/combos'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\ComboController::index
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:15
 * @route '/admin/ai/combos'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\Ai\ComboController::index
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:15
 * @route '/admin/ai/combos'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\ComboController::index
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:15
 * @route '/admin/ai/combos'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\ComboController::index
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:15
 * @route '/admin/ai/combos'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\Ai\ComboController::index
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:15
 * @route '/admin/ai/combos'
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
* @see \App\Http\Controllers\Admin\Ai\ComboController::store
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:43
 * @route '/admin/ai/combos'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/ai/combos',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\Ai\ComboController::store
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:43
 * @route '/admin/ai/combos'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\ComboController::store
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:43
 * @route '/admin/ai/combos'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\ComboController::store
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:43
 * @route '/admin/ai/combos'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\ComboController::store
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:43
 * @route '/admin/ai/combos'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\Ai\ComboController::update
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:74
 * @route '/admin/ai/combos/{combo}'
 */
export const update = (args: { combo: number | { id: number } } | [combo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/ai/combos/{combo}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\Ai\ComboController::update
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:74
 * @route '/admin/ai/combos/{combo}'
 */
update.url = (args: { combo: number | { id: number } } | [combo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { combo: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { combo: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    combo: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        combo: typeof args.combo === 'object'
                ? args.combo.id
                : args.combo,
                }

    return update.definition.url
            .replace('{combo}', parsedArgs.combo.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\ComboController::update
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:74
 * @route '/admin/ai/combos/{combo}'
 */
update.put = (args: { combo: number | { id: number } } | [combo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\Ai\ComboController::update
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:74
 * @route '/admin/ai/combos/{combo}'
 */
update.patch = (args: { combo: number | { id: number } } | [combo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\ComboController::update
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:74
 * @route '/admin/ai/combos/{combo}'
 */
    const updateForm = (args: { combo: number | { id: number } } | [combo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\ComboController::update
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:74
 * @route '/admin/ai/combos/{combo}'
 */
        updateForm.put = (args: { combo: number | { id: number } } | [combo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\Ai\ComboController::update
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:74
 * @route '/admin/ai/combos/{combo}'
 */
        updateForm.patch = (args: { combo: number | { id: number } } | [combo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\Ai\ComboController::destroy
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:107
 * @route '/admin/ai/combos/{combo}'
 */
export const destroy = (args: { combo: number | { id: number } } | [combo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/ai/combos/{combo}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\Ai\ComboController::destroy
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:107
 * @route '/admin/ai/combos/{combo}'
 */
destroy.url = (args: { combo: number | { id: number } } | [combo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { combo: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { combo: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    combo: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        combo: typeof args.combo === 'object'
                ? args.combo.id
                : args.combo,
                }

    return destroy.definition.url
            .replace('{combo}', parsedArgs.combo.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\ComboController::destroy
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:107
 * @route '/admin/ai/combos/{combo}'
 */
destroy.delete = (args: { combo: number | { id: number } } | [combo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\ComboController::destroy
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:107
 * @route '/admin/ai/combos/{combo}'
 */
    const destroyForm = (args: { combo: number | { id: number } } | [combo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\ComboController::destroy
 * @see app/Http/Controllers/Admin/Ai/ComboController.php:107
 * @route '/admin/ai/combos/{combo}'
 */
        destroyForm.delete = (args: { combo: number | { id: number } } | [combo: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const combos = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default combos