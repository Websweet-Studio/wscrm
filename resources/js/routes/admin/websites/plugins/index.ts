import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::store
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:37
 * @route '/admin/websites/plugins'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/websites/plugins',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::store
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:37
 * @route '/admin/websites/plugins'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::store
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:37
 * @route '/admin/websites/plugins'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::store
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:37
 * @route '/admin/websites/plugins'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::store
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:37
 * @route '/admin/websites/plugins'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::update
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:66
 * @route '/admin/websites/plugins/{plugin}'
 */
export const update = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/admin/websites/plugins/{plugin}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::update
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:66
 * @route '/admin/websites/plugins/{plugin}'
 */
update.url = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { plugin: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { plugin: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    plugin: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        plugin: typeof args.plugin === 'object'
                ? args.plugin.id
                : args.plugin,
                }

    return update.definition.url
            .replace('{plugin}', parsedArgs.plugin.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::update
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:66
 * @route '/admin/websites/plugins/{plugin}'
 */
update.post = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::update
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:66
 * @route '/admin/websites/plugins/{plugin}'
 */
    const updateForm = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::update
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:66
 * @route '/admin/websites/plugins/{plugin}'
 */
        updateForm.post = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, options),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::deploy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:116
 * @route '/admin/websites/plugins/{plugin}/deploy'
 */
export const deploy = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deploy.url(args, options),
    method: 'post',
})

deploy.definition = {
    methods: ["post"],
    url: '/admin/websites/plugins/{plugin}/deploy',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::deploy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:116
 * @route '/admin/websites/plugins/{plugin}/deploy'
 */
deploy.url = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { plugin: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { plugin: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    plugin: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        plugin: typeof args.plugin === 'object'
                ? args.plugin.id
                : args.plugin,
                }

    return deploy.definition.url
            .replace('{plugin}', parsedArgs.plugin.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::deploy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:116
 * @route '/admin/websites/plugins/{plugin}/deploy'
 */
deploy.post = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deploy.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::deploy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:116
 * @route '/admin/websites/plugins/{plugin}/deploy'
 */
    const deployForm = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deploy.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::deploy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:116
 * @route '/admin/websites/plugins/{plugin}/deploy'
 */
        deployForm.post = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deploy.url(args, options),
            method: 'post',
        })
    
    deploy.form = deployForm
/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::destroy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:103
 * @route '/admin/websites/plugins/{plugin}'
 */
export const destroy = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/websites/plugins/{plugin}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::destroy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:103
 * @route '/admin/websites/plugins/{plugin}'
 */
destroy.url = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { plugin: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { plugin: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    plugin: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        plugin: typeof args.plugin === 'object'
                ? args.plugin.id
                : args.plugin,
                }

    return destroy.definition.url
            .replace('{plugin}', parsedArgs.plugin.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::destroy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:103
 * @route '/admin/websites/plugins/{plugin}'
 */
destroy.delete = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::destroy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:103
 * @route '/admin/websites/plugins/{plugin}'
 */
    const destroyForm = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::destroy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:103
 * @route '/admin/websites/plugins/{plugin}'
 */
        destroyForm.delete = (args: { plugin: string | number | { id: string | number } } | [plugin: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const plugins = {
    store: Object.assign(store, store),
update: Object.assign(update, update),
deploy: Object.assign(deploy, deploy),
destroy: Object.assign(destroy, destroy),
}

export default plugins