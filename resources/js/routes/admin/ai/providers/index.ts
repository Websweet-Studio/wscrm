import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::index
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:15
 * @route '/admin/ai/providers'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/ai/providers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::index
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:15
 * @route '/admin/ai/providers'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::index
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:15
 * @route '/admin/ai/providers'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::index
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:15
 * @route '/admin/ai/providers'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::index
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:15
 * @route '/admin/ai/providers'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::index
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:15
 * @route '/admin/ai/providers'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::index
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:15
 * @route '/admin/ai/providers'
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
* @see \App\Http\Controllers\Admin\Ai\ProviderController::store
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:39
 * @route '/admin/ai/providers'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/ai/providers',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::store
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:39
 * @route '/admin/ai/providers'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::store
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:39
 * @route '/admin/ai/providers'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::store
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:39
 * @route '/admin/ai/providers'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::store
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:39
 * @route '/admin/ai/providers'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::update
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:60
 * @route '/admin/ai/providers/{provider}'
 */
export const update = (args: { provider: number | { id: number } } | [provider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/ai/providers/{provider}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::update
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:60
 * @route '/admin/ai/providers/{provider}'
 */
update.url = (args: { provider: number | { id: number } } | [provider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { provider: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { provider: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    provider: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        provider: typeof args.provider === 'object'
                ? args.provider.id
                : args.provider,
                }

    return update.definition.url
            .replace('{provider}', parsedArgs.provider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::update
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:60
 * @route '/admin/ai/providers/{provider}'
 */
update.put = (args: { provider: number | { id: number } } | [provider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::update
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:60
 * @route '/admin/ai/providers/{provider}'
 */
update.patch = (args: { provider: number | { id: number } } | [provider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::update
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:60
 * @route '/admin/ai/providers/{provider}'
 */
    const updateForm = (args: { provider: number | { id: number } } | [provider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::update
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:60
 * @route '/admin/ai/providers/{provider}'
 */
        updateForm.put = (args: { provider: number | { id: number } } | [provider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::update
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:60
 * @route '/admin/ai/providers/{provider}'
 */
        updateForm.patch = (args: { provider: number | { id: number } } | [provider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\Ai\ProviderController::destroy
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:85
 * @route '/admin/ai/providers/{provider}'
 */
export const destroy = (args: { provider: number | { id: number } } | [provider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/ai/providers/{provider}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::destroy
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:85
 * @route '/admin/ai/providers/{provider}'
 */
destroy.url = (args: { provider: number | { id: number } } | [provider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { provider: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { provider: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    provider: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        provider: typeof args.provider === 'object'
                ? args.provider.id
                : args.provider,
                }

    return destroy.definition.url
            .replace('{provider}', parsedArgs.provider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::destroy
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:85
 * @route '/admin/ai/providers/{provider}'
 */
destroy.delete = (args: { provider: number | { id: number } } | [provider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::destroy
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:85
 * @route '/admin/ai/providers/{provider}'
 */
    const destroyForm = (args: { provider: number | { id: number } } | [provider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\ProviderController::destroy
 * @see app/Http/Controllers/Admin/Ai/ProviderController.php:85
 * @route '/admin/ai/providers/{provider}'
 */
        destroyForm.delete = (args: { provider: number | { id: number } } | [provider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const providers = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default providers